<?php

namespace App\Services\Security;

use App\Services\Marketing\UnsubscribeService;
use App\Support\Security\SecurityCheckResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class EmailSafetyService
{
    public function __construct(private SecretRedactionService $redactor) {}

    public function checks(): array
    {
        $mailer = (string) config('mail.default');
        $fromAddress = (string) data_get(config('mail.from'), 'address', '');
        $unsubscribeRoute = Route::has('marketing.unsubscribe.public');
        $marketingViews = $this->marketingViewsContainUnsubscribe();
        $riskyTemplates = $this->riskyTemplateMatches();

        return array_map(fn (SecurityCheckResult $check): array => $check->toArray(), [
            filled($mailer)
                ? SecurityCheckResult::pass('mail_mailer', 'Mail transport', "Mail transport is configured as [{$mailer}].")
                : SecurityCheckResult::warning('mail_mailer', 'Mail transport', 'MAIL_MAILER is not configured.'),
            filter_var($fromAddress, FILTER_VALIDATE_EMAIL)
                ? SecurityCheckResult::pass('mail_from', 'From address', 'MAIL_FROM_ADDRESS is configured.')
                : SecurityCheckResult::warning('mail_from', 'From address', 'Configure a valid MAIL_FROM_ADDRESS before sending production mail.'),
            (bool) config('security.email_safety_enabled', true)
                ? SecurityCheckResult::pass('email_safety', 'Email safety', 'Outbound email safety diagnostics are enabled.')
                : SecurityCheckResult::warning('email_safety', 'Email safety', 'Email safety diagnostics are disabled.'),
            $unsubscribeRoute
                ? SecurityCheckResult::pass('unsubscribe_route', 'Unsubscribe route', 'Public unsubscribe route is available and records without exposing contact existence.')
                : SecurityCheckResult::fail('unsubscribe_route', 'Unsubscribe route', 'Marketing unsubscribe route is missing.'),
            $marketingViews
                ? SecurityCheckResult::pass('marketing_footer', 'Marketing footer', 'Marketing email templates include unsubscribe/footer copy.')
                : SecurityCheckResult::warning('marketing_footer', 'Marketing footer', 'Review marketing templates for unsubscribe/footer copy.'),
            empty($riskyTemplates)
                ? SecurityCheckResult::pass('email_template_audit', 'Email template audit', 'No obvious raw secret/path tokens were found in email Blade templates.')
                : SecurityCheckResult::warning('email_template_audit', 'Email template audit', 'Review email templates with risky words before launch.', ['matches' => $riskyTemplates]),
        ]);
    }

    public function canSendMarketingEmail(?string $email): bool
    {
        return ! app(UnsubscribeService::class)->isUnsubscribed($email);
    }

    public function riskyTemplateMatches(): array
    {
        $matches = [];

        foreach ((array) config('security.mail_views', []) as $directory) {
            $path = base_path($directory);

            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                $contents = File::get($file->getPathname());

                if (preg_match('/APP_KEY|DB_PASSWORD|MAIL_PASSWORD|SANFAANI_LICENSE_KEY|base_path\(|storage_path\(|\.env/i', $contents) === 1) {
                    $matches[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        return $this->redactor->redactArray($matches);
    }

    private function marketingViewsContainUnsubscribe(): bool
    {
        $path = resource_path('views/emails/marketing');

        if (! File::isDirectory($path)) {
            return false;
        }

        foreach (File::allFiles($path) as $file) {
            if (str_contains(strtolower(File::get($file->getPathname())), 'unsubscribe')) {
                return true;
            }
        }

        return false;
    }
}
