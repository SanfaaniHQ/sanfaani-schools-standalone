<?php

namespace App\Http\Middleware;

use App\Services\CurrentSchoolService;
use App\Services\PlatformSettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        $this->persistUserPreference($request, $locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = $this->supportedLocales();
        $requested = $request->query('lang');

        if (is_string($requested) && in_array($requested, $supported, true)) {
            return $requested;
        }

        $userLocale = $request->user()?->preferred_locale ?? null;

        if (is_string($userLocale) && in_array($userLocale, $supported, true)) {
            return $userLocale;
        }

        $sessionLocale = $request->session()->get('locale');

        if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
            return $sessionLocale;
        }

        $schoolLocale = $this->schoolDefaultLocale($request);

        if (is_string($schoolLocale) && in_array($schoolLocale, $supported, true)) {
            return $schoolLocale;
        }

        $platformLocale = $this->platformDefaultLocale();

        if (is_string($platformLocale) && in_array($platformLocale, $supported, true)) {
            return $platformLocale;
        }

        return config('app.fallback_locale', 'en');
    }

    private function supportedLocales(): array
    {
        $configured = config('sanfaani.supported_languages', ['en', 'ar', 'fr', 'yo', 'ha']);
        $languages = array_keys(config('sanfaani.languages', []));

        return collect($configured)
            ->intersect($languages)
            ->values()
            ->whenEmpty(fn ($collection) => $collection->push('en'))
            ->all();
    }

    private function schoolDefaultLocale(Request $request): ?string
    {
        if (! $request->user()) {
            return null;
        }

        try {
            return app(CurrentSchoolService::class)->get($request->user())?->default_language;
        } catch (Throwable) {
            return null;
        }
    }

    private function platformDefaultLocale(): ?string
    {
        try {
            return app(PlatformSettingService::class)->get()->default_language;
        } catch (Throwable) {
            return config('sanfaani.default_language', 'en');
        }
    }

    private function persistUserPreference(Request $request, string $locale): void
    {
        $user = $request->user();

        if (! $user || $request->query('lang') !== $locale || ! $this->usersTableHasPreferredLocale()) {
            return;
        }

        if ($user->preferred_locale === $locale) {
            return;
        }

        $user->forceFill(['preferred_locale' => $locale])->saveQuietly();
    }

    private function usersTableHasPreferredLocale(): bool
    {
        static $hasColumn = null;

        if ($hasColumn !== null) {
            return $hasColumn;
        }

        try {
            return $hasColumn = Schema::hasColumn('users', 'preferred_locale');
        } catch (Throwable) {
            return $hasColumn = false;
        }
    }
}
