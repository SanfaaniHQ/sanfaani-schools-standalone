<?php

namespace App\Services\Demo;

use App\Models\DemoCredential;
use App\Models\DemoSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoExpiryService
{
    public function __construct(private DemoActivityService $activity) {}

    public function expireDueSessions(): int
    {
        $count = 0;

        DemoSession::expiredOrDue()
            ->with('credentials.user')
            ->chunkById(50, function ($sessions) use (&$count): void {
                foreach ($sessions as $session) {
                    $this->expire($session);
                    $count++;
                }
            });

        return $count;
    }

    public function expire(DemoSession $session, bool $manual = false, ?User $actor = null): DemoSession
    {
        return DB::transaction(function () use ($session, $manual, $actor): DemoSession {
            $session = DemoSession::whereKey($session->id)->lockForUpdate()->firstOrFail();

            if ($session->status === DemoSession::STATUS_EXPIRED) {
                return $session->fresh(['credentials.user']);
            }

            $session->forceFill([
                'status' => DemoSession::STATUS_EXPIRED,
                'expired_at' => now(),
            ])->save();

            $session->credentials()->with('user')->get()->each(function (DemoCredential $credential): void {
                $credential->forceFill([
                    'status' => DemoCredential::STATUS_EXPIRED,
                    'temporary_password_encrypted' => null,
                    'expires_at' => now(),
                ])->save();

                $credential->user?->forceFill([
                    'password' => Hash::make(Str::random(64)),
                    'must_change_password' => true,
                ])->save();
            });

            $this->activity->log(
                $session,
                $manual ? 'demo.manually_expired' : 'demo.expired',
                $manual ? 'Demo session was manually expired.' : 'Demo session expired automatically.',
                $actor
            );

            return $session->fresh(['credentials.user', 'activities']);
        });
    }
}
