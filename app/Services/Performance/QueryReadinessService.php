<?php

namespace App\Services\Performance;

use App\Support\Performance\PerformanceCheckResult;

class QueryReadinessService
{
    public function checks(): array
    {
        $checks = [
            PerformanceCheckResult::info('pagination_standard', 'Pagination standard', 'Use pagination for dashboards and admin tables; default guidance is '.((int) config('performance.default_page_size', 25)).' rows.'),
            PerformanceCheckResult::info('export_limit_standard', 'Export limit standard', 'Use queued or chunked exports beyond '.((int) config('performance.max_export_rows', 5000)).' rows.'),
            PerformanceCheckResult::info('tenant_scoping_review', 'Tenant scoping review', 'Keep school_id filters explicit in school-scoped queries and preserve Super Admin global visibility.'),
            PerformanceCheckResult::info('eager_loading_review', 'Eager loading review', 'Use with/load for dashboard rows that display related school, student, user, class, term, support, update, or backup records.'),
        ];

        foreach ((array) config('performance.recommended_indexes', []) as $table => $recommendations) {
            foreach ((array) $recommendations as $recommendation) {
                $checks[] = $this->indexRecommendation((string) $table, (array) $recommendation);
            }
        }

        return array_map(fn (PerformanceCheckResult $check): array => $check->toArray(), $checks);
    }

    private function indexRecommendation(string $table, array $recommendation): PerformanceCheckResult
    {
        $columns = array_values((array) ($recommendation['columns'] ?? []));
        $reason = (string) ($recommendation['reason'] ?? 'Review query usage before adding an index.');

        return PerformanceCheckResult::warning(
            'index_'.$table.'_review',
            "{$table} index review",
            "Review index coverage for [{$table}] on columns [".implode(', ', $columns).']. Live schema inspection is intentionally skipped so diagnostics do not require a database connection.',
            ['table' => $table, 'columns' => $columns, 'reason' => $reason, 'migration_applied' => false],
        );
    }
}
