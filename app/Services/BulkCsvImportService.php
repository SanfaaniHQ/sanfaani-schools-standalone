<?php

namespace App\Services;

class BulkCsvImportService
{
    public array $errors = [];

    public array $rows = [];

    public function read(string $filePath, array $requiredHeaders): bool
    {
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            $this->errors[] = 'Could not open the uploaded CSV file.';

            return false;
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            $this->errors[] = 'The CSV file is empty or missing a header row.';
            fclose($handle);

            return false;
        }

        $headers = $this->normalizeHeaders($headers);

        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                $this->errors[] = "Missing required column: {$requiredHeader}.";
            }
        }

        if ($this->errors !== []) {
            fclose($handle);

            return false;
        }

        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $values = array_slice(array_pad($row, count($headers), null), 0, count($headers));

            $this->rows[] = [
                'row_number' => $rowNumber,
                'data' => array_map(fn ($value) => trim((string) $value), array_combine($headers, $values)),
            ];
        }

        fclose($handle);

        return true;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            $header = strtolower(trim($header));

            return preg_replace('/\s+/', '_', $header);
        }, $headers);
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
