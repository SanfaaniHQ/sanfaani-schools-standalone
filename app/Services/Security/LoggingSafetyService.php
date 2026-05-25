<?php

namespace App\Services\Security;

use App\Support\Security\SecurityCheckResult;

class LoggingSafetyService
{
    public function checks(): array
    {
        $channel = (string) config('logging.default');
        $level = (string) config('logging.channels.'.config('logging.default').'.level', config('app.debug') ? 'debug' : 'info');
        $queueFailedDriver = (string) config('queue.failed.driver');

        return array_map(fn (SecurityCheckResult $check): array => $check->toArray(), [
            filled($channel)
                ? SecurityCheckResult::pass('log_channel', 'Log channel', "Log channel is [{$channel}].")
                : SecurityCheckResult::warning('log_channel', 'Log channel', 'LOG_CHANNEL is not configured.'),
            strtolower($level) === 'debug' && app()->environment('production')
                ? SecurityCheckResult::warning('log_level', 'Log level', 'Avoid debug logs in production because they can capture sensitive context.')
                : SecurityCheckResult::info('log_level', 'Log level', "Configured log level guidance is [{$level}]."),
            (bool) config('security.secret_redaction_enabled', true)
                ? SecurityCheckResult::pass('secret_redaction', 'Secret redaction', 'Secret redaction is enabled for diagnostics and safety helpers.')
                : SecurityCheckResult::warning('secret_redaction', 'Secret redaction', 'Secret redaction should be enabled before production.'),
            filled($queueFailedDriver)
                ? SecurityCheckResult::pass('queue_failed_driver', 'Queue failure logging', "Failed queue driver is [{$queueFailedDriver}]. Keep failed payloads redacted.")
                : SecurityCheckResult::warning('queue_failed_driver', 'Queue failure logging', 'Configure QUEUE_FAILED_DRIVER for production queue failure review.'),
            SecurityCheckResult::info('backup_update_logs', 'Backup/update logs', 'Backup and update log services redact secrets and server paths before storage/display.'),
        ]);
    }
}
