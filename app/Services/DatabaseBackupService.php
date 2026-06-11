<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use SplFileObject;

class DatabaseBackupService
{
    private const BACKUP_DIRECTORY = 'backups/database';

    public function create(?int $keep = null): array
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            throw new RuntimeException("Database backups are not implemented for the [{$driver}] driver.");
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.'database-backup-'.now()->format('Ymd-His').'.sql';
        $file = new SplFileObject($path, 'w');

        $this->writeHeader($file, $driver);

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $file->fwrite("SET FOREIGN_KEY_CHECKS=0;\n\n");
        }

        foreach ($this->tables($driver) as $table) {
            $this->dumpTable($file, $driver, $table);
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $file->fwrite("SET FOREIGN_KEY_CHECKS=1;\n");
        }

        $this->cleanup($keep);

        return $this->backupInfo($path);
    }

    public function backups(): Collection
    {
        $directory = $this->directory();

        return collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'sql')
            ->map(fn ($file) => $this->backupInfo($file->getPathname()))
            ->sortByDesc('created_at')
            ->values();
    }

    public function cleanup(?int $keep = null): array
    {
        $keep ??= (int) config('sanfaani.backups.retention_count', 10);
        $keep = max(1, $keep);
        $deleted = [];

        $this->backups()
            ->skip($keep)
            ->each(function (array $backup) use (&$deleted) {
                if (File::exists($backup['path'])) {
                    File::delete($backup['path']);
                    $deleted[] = $backup['file_name'];
                }
            });

        return $deleted;
    }

    public function pathFor(string $fileName): string
    {
        $fileName = basename($fileName);
        $path = $this->directory().DIRECTORY_SEPARATOR.$fileName;
        $realDirectory = realpath($this->directory());
        $realPath = realpath($path);

        if (
            ! $realDirectory
            || ! $realPath
            || ! str_starts_with($realPath, $realDirectory.DIRECTORY_SEPARATOR)
            || pathinfo($realPath, PATHINFO_EXTENSION) !== 'sql'
        ) {
            throw new RuntimeException('Backup file was not found.');
        }

        return $realPath;
    }

    private function writeHeader(SplFileObject $file, string $driver): void
    {
        $file->fwrite("-- Sanfaani Schools database backup\n");
        $file->fwrite('-- Created at: '.now()->toDateTimeString()."\n");
        $file->fwrite("-- Driver: {$driver}\n");
        $file->fwrite("-- Restore into a clean database after verifying the target environment.\n\n");
    }

    private function tables(string $driver): array
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return collect(DB::select('SHOW FULL TABLES'))
                ->map(function ($row) {
                    $values = array_values((array) $row);

                    return ($values[1] ?? 'BASE TABLE') === 'BASE TABLE' ? $values[0] : null;
                })
                ->filter()
                ->values()
                ->all();
        }

        return collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
            ->pluck('name')
            ->values()
            ->all();
    }

    private function dumpTable(SplFileObject $file, string $driver, string $table): void
    {
        $identifier = $this->quoteIdentifier($table, $driver);
        $file->fwrite("--\n-- Table: {$table}\n--\n");
        $file->fwrite("DROP TABLE IF EXISTS {$identifier};\n");
        $file->fwrite($this->createStatement($driver, $table).";\n\n");

        $columns = Schema::getColumnListing($table);

        if ($columns === []) {
            return;
        }

        $quotedColumns = collect($columns)
            ->map(fn ($column) => $this->quoteIdentifier($column, $driver))
            ->implode(', ');

        $writeRows = function ($rows) use ($file, $driver, $table, $columns, $identifier, $quotedColumns): void {
            foreach ($rows as $row) {
                $row = (array) $row;
                $values = collect($columns)
                    ->map(fn ($column) => $this->sqlValue($row[$column] ?? null))
                    ->implode(', ');

                $file->fwrite("INSERT INTO {$identifier} ({$quotedColumns}) VALUES ({$values});\n");
            }
        };

        $query = DB::table($table)->select($columns);
        $primaryKey = $this->singlePrimaryKey($driver, $table);
        $chunkSize = max(50, (int) config('sanfaani.backups.chunk_size', 500));

        if ($primaryKey && in_array($primaryKey, $columns, true)) {
            $query->orderBy($primaryKey)->chunkById($chunkSize, $writeRows, $primaryKey);
        } else {
            $query->orderBy($columns[0])->chunk($chunkSize, $writeRows);
        }

        $file->fwrite("\n");
    }

    private function createStatement(string $driver, string $table): string
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $row = (array) DB::selectOne('SHOW CREATE TABLE '.$this->quoteIdentifier($table, $driver));

            return (string) array_values($row)[1];
        }

        $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?", [$table]);

        if (! $row || ! isset($row->sql)) {
            throw new RuntimeException("Could not read schema for table [{$table}].");
        }

        return (string) $row->sql;
    }

    private function singlePrimaryKey(string $driver, string $table): ?string
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $keys = DB::select('SHOW KEYS FROM '.$this->quoteIdentifier($table, $driver)." WHERE Key_name = 'PRIMARY'");

            return count($keys) === 1 ? (string) $keys[0]->Column_name : null;
        }

        $keys = collect(DB::select('PRAGMA table_info('.$this->quoteIdentifier($table, $driver).')'))
            ->filter(fn ($column) => (int) $column->pk === 1)
            ->values();

        return $keys->count() === 1 ? (string) $keys->first()->name : null;
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return '`'.str_replace('`', '``', $identifier).'`';
        }

        return '"'.str_replace('"', '""', $identifier).'"';
    }

    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return DB::connection()->getPdo()->quote((string) $value);
    }

    private function directory(): string
    {
        $directory = storage_path('app/private/'.self::BACKUP_DIRECTORY);

        File::ensureDirectoryExists($directory, 0750);

        return $directory;
    }

    private function backupInfo(string $path): array
    {
        $createdAt = File::lastModified($path);
        $size = File::size($path);

        return [
            'file_name' => basename($path),
            'path' => $path,
            'size' => $size,
            'size_for_humans' => $this->humanSize($size),
            'created_at' => $createdAt,
            'created_at_for_humans' => date('d M Y, H:i', $createdAt),
        ];
    }

    private function humanSize(int $bytes): string
    {
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024 || $unit === 'GB') {
                return number_format($bytes, $unit === 'B' ? 0 : 2).' '.$unit;
            }

            $bytes /= 1024;
        }

        return number_format($bytes, 2).' GB';
    }
}
