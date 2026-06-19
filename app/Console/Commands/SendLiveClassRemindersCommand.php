<?php

namespace App\Console\Commands;

use App\Models\LiveClass;
use App\Models\LiveClassParticipant;
use App\Notifications\LiveClassReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SendLiveClassRemindersCommand extends Command
{
    protected $signature = 'live-classes:send-reminders {--dry-run : Count due reminders without sending}';

    protected $description = 'Send due live-class in-app reminders for resolved participants.';

    public function handle(): int
    {
        if (! $this->participantsAreReady()) {
            $this->warn('live_class_participants table is not available yet.');

            return self::SUCCESS;
        }

        $now = now();
        $sent = 0;
        $dueQuery = LiveClassParticipant::query()
            ->with(['user', 'liveClass'])
            ->whereIn('status', LiveClassParticipant::ACTIVE_STATUSES)
            ->whereNull('reminder_sent_at')
            ->whereNotNull('reminder_due_at')
            ->where('reminder_due_at', '<=', $now)
            ->whereHas('liveClass', function ($query) use ($now): void {
                $query->whereIn('status', [LiveClass::STATUS_SCHEDULED, LiveClass::STATUS_LIVE])
                    ->where('starts_at', '>=', $now->copy()->subMinutes(15));
            });

        if ($this->option('dry-run')) {
            $this->info('Due live-class reminders: '.$dueQuery->count());

            return self::SUCCESS;
        }

        $notificationsReady = $this->notificationsAreReady();

        $dueQuery
            ->orderBy('id')
            ->chunkById(100, function ($participants) use (&$sent, $notificationsReady): void {
                foreach ($participants as $participant) {
                    $participant->loadMissing('user', 'liveClass');

                    if ($participant->user && $participant->liveClass && $notificationsReady) {
                        try {
                            $participant->user->notify(new LiveClassReminderNotification(
                                $participant->liveClass,
                                $participant
                            ));
                        } catch (Throwable) {
                            continue;
                        }
                    }

                    $participant->forceFill([
                        'reminder_sent_at' => now(),
                    ])->save();

                    $sent++;
                }
            });

        $this->info('Live-class reminders processed: '.$sent);

        return self::SUCCESS;
    }

    private function participantsAreReady(): bool
    {
        try {
            return Schema::hasTable('live_class_participants');
        } catch (Throwable) {
            return false;
        }
    }

    private function notificationsAreReady(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (Throwable) {
            return false;
        }
    }
}
