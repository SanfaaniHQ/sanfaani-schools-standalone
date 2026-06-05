<?php

namespace App\Support\Marketplace;

use RuntimeException;

class StoredZipWriter
{
    private const UTF8_FLAG = 0x0800;
    private const STORE_METHOD = 0;
    private const ZIP_VERSION = 20;
    private const MAX_ZIP32_SIZE = 0xffffffff;

    /**
     * @param  array<int, array{absolute_path: string, relative_path: string}>  $files
     */
    public function write(string $zipPath, array $files): void
    {
        $handle = fopen($zipPath, 'wb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open ZIP output [{$zipPath}].");
        }

        $centralDirectory = [];

        try {
            foreach ($files as $file) {
                $centralDirectory[] = $this->writeFile($handle, $file['absolute_path'], $file['relative_path']);
            }

            $centralDirectoryOffset = $this->position($handle);

            foreach ($centralDirectory as $entry) {
                $this->writeBytes($handle, $this->centralDirectoryHeader($entry).$entry['name']);
            }

            $centralDirectorySize = $this->position($handle) - $centralDirectoryOffset;

            $this->writeBytes(
                $handle,
                pack(
                    'VvvvvVVv',
                    0x06054b50,
                    0,
                    0,
                    count($centralDirectory),
                    count($centralDirectory),
                    $centralDirectorySize,
                    $centralDirectoryOffset,
                    0,
                ),
            );
        } catch (RuntimeException $exception) {
            fclose($handle);
            @unlink($zipPath);

            throw $exception;
        }

        fclose($handle);
    }

    /**
     * @return array{name: string, crc32: int, size: int, offset: int, dos_time: int, dos_date: int}
     */
    private function writeFile(mixed $handle, string $absolutePath, string $relativePath): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException("Package file [{$relativePath}] is not readable.");
        }

        $size = filesize($absolutePath);

        if ($size === false || $size > self::MAX_ZIP32_SIZE) {
            throw new RuntimeException("Package file [{$relativePath}] cannot be represented in a ZIP32 archive.");
        }

        $crc = hash_file('crc32b', $absolutePath);

        if ($crc === false) {
            throw new RuntimeException("Unable to hash package file [{$relativePath}].");
        }

        [$dosTime, $dosDate] = $this->dosTimestamp(filemtime($absolutePath) ?: time());

        $entry = [
            'name' => $relativePath,
            'crc32' => (int) hexdec($crc),
            'size' => (int) $size,
            'offset' => $this->position($handle),
            'dos_time' => $dosTime,
            'dos_date' => $dosDate,
        ];

        $this->writeBytes($handle, $this->localFileHeader($entry).$relativePath);

        $input = fopen($absolutePath, 'rb');

        if ($input === false) {
            throw new RuntimeException("Unable to read package file [{$relativePath}].");
        }

        stream_copy_to_stream($input, $handle);
        fclose($input);

        return $entry;
    }

    /**
     * @param  array{name: string, crc32: int, size: int, offset: int, dos_time: int, dos_date: int}  $entry
     */
    private function localFileHeader(array $entry): string
    {
        return pack(
            'VvvvvvVVVvv',
            0x04034b50,
            self::ZIP_VERSION,
            self::UTF8_FLAG,
            self::STORE_METHOD,
            $entry['dos_time'],
            $entry['dos_date'],
            $entry['crc32'],
            $entry['size'],
            $entry['size'],
            strlen($entry['name']),
            0,
        );
    }

    /**
     * @param  array{name: string, crc32: int, size: int, offset: int, dos_time: int, dos_date: int}  $entry
     */
    private function centralDirectoryHeader(array $entry): string
    {
        return pack(
            'VvvvvvvVVVvvvvvVV',
            0x02014b50,
            self::ZIP_VERSION,
            self::ZIP_VERSION,
            self::UTF8_FLAG,
            self::STORE_METHOD,
            $entry['dos_time'],
            $entry['dos_date'],
            $entry['crc32'],
            $entry['size'],
            $entry['size'],
            strlen($entry['name']),
            0,
            0,
            0,
            0,
            0,
            $entry['offset'],
        );
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function dosTimestamp(int $timestamp): array
    {
        $parts = getdate($timestamp);
        $year = max(1980, (int) $parts['year']);

        return [
            (((int) $parts['hours']) << 11) | (((int) $parts['minutes']) << 5) | intdiv((int) $parts['seconds'], 2),
            (($year - 1980) << 9) | (((int) $parts['mon']) << 5) | (int) $parts['mday'],
        ];
    }

    private function position(mixed $handle): int
    {
        $position = ftell($handle);

        if ($position === false) {
            throw new RuntimeException('Unable to determine ZIP stream position.');
        }

        return $position;
    }

    private function writeBytes(mixed $handle, string $bytes): void
    {
        while ($bytes !== '') {
            $written = fwrite($handle, $bytes);

            if ($written === false || $written === 0) {
                throw new RuntimeException('Unable to write ZIP data.');
            }

            $bytes = substr($bytes, $written);
        }
    }
}
