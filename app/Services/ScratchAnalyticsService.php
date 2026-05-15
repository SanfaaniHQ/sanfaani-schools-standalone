<?php

namespace App\Services;

use App\Models\ScratchCard;
use App\Models\ScratchCardBatch;
use App\Models\ScratchCardUsage;
use Illuminate\Support\Carbon;

class ScratchAnalyticsService
{
    public function summary(?int $schoolId = null): array
    {
        return [
            'requests' => $this->batchQuery($schoolId)->count(),
            'pending_requests' => $this->batchQuery($schoolId)->whereIn('status', ['pending_payment', 'pending_approval'])->count(),
            'generated_batches' => $this->batchQuery($schoolId)->where('status', 'generated')->count(),
            'revenue' => (float) $this->batchQuery($schoolId)->where('payment_status', 'paid')->sum('amount'),
            'cards_total' => $this->cardQuery($schoolId)->count(),
            'cards_unused' => $this->cardQuery($schoolId)->where('status', 'unused')->count(),
            'cards_used' => $this->cardQuery($schoolId)->where('status', 'used')->count(),
            'cards_revoked' => $this->cardQuery($schoolId)->where('status', 'revoked')->count(),
            'cards_expiring_soon' => $this->cardQuery($schoolId)
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [now(), now()->addDays(14)])
                ->count(),
            'usage_last_7_days' => $this->usageCount($schoolId, now()->subDays(7)),
            'usage_last_30_days' => $this->usageCount($schoolId, now()->subDays(30)),
        ];
    }

    public function usageTrend(?int $schoolId = null, int $days = 14): array
    {
        $start = now()->subDays(max(1, $days - 1))->startOfDay();
        $rows = ScratchCardUsage::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->where('used_at', '>=', $start)
            ->selectRaw('DATE(used_at) as day, COUNT(*) as aggregate')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('aggregate', 'day');

        return collect(range(0, $days - 1))
            ->mapWithKeys(function (int $offset) use ($start, $rows) {
                $day = $start->copy()->addDays($offset)->toDateString();

                return [$day => (int) ($rows[$day] ?? 0)];
            })
            ->all();
    }

    private function batchQuery(?int $schoolId)
    {
        return ScratchCardBatch::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId));
    }

    private function cardQuery(?int $schoolId)
    {
        return ScratchCard::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId));
    }

    private function usageCount(?int $schoolId, Carbon $from): int
    {
        return ScratchCardUsage::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->where('used_at', '>=', $from)
            ->count();
    }
}
