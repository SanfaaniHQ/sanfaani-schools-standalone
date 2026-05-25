<?php

namespace App\Services\Demo;

use App\Models\DemoActivity;
use App\Models\DemoSession;
use App\Models\User;

class DemoActivityService
{
    public function log(
        DemoSession $session,
        string $event,
        ?string $description = null,
        ?User $user = null,
        array $context = []
    ): DemoActivity {
        $activity = DemoActivity::create([
            'demo_session_id' => $session->id,
            'user_id' => $user?->id,
            'event' => $event,
            'description' => $description,
            'context' => array_filter($context, fn (mixed $value): bool => $value !== null && $value !== ''),
        ]);

        $session->forceFill(['last_activity_at' => now()])->save();

        return $activity;
    }
}
