<?php

namespace App\Http\Middleware;

use App\Services\CurrentSchoolService;
use App\Services\PlatformSettingService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Cookie as HttpCookie;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        Carbon::setLocale($locale);
        $request->session()->put('locale', $locale);

        $this->persistUserPreference($request, $locale);

        $response = $next($request);
        $response->headers->setCookie(new HttpCookie(
            'sanfaani_locale',
            $locale,
            now()->addYear(),
            '/',
            null,
            $request->isSecure(),
            false,
            false,
            'lax'
        ));

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        $supported = $this->supportedLocales();
        $requested = $this->normalizeLocale($request->query('lang') ?: $request->input('lang') ?: $request->query('locale'));

        if (is_string($requested) && in_array($requested, $supported, true)) {
            return $requested;
        }

        $sessionLocale = $this->normalizeLocale($request->session()->get('locale'));

        if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
            return $sessionLocale;
        }

        $cookieLocale = $this->normalizeLocale($request->cookies->get('sanfaani_locale'));

        if (is_string($cookieLocale) && in_array($cookieLocale, $supported, true)) {
            return $cookieLocale;
        }

        $userLocale = $this->normalizeLocale($request->user()?->preferred_locale);

        if (is_string($userLocale) && in_array($userLocale, $supported, true)) {
            return $userLocale;
        }

        $schoolLocale = $this->normalizeLocale($this->schoolDefaultLocale($request));

        if (is_string($schoolLocale) && in_array($schoolLocale, $supported, true)) {
            return $schoolLocale;
        }

        $platformLocale = $this->normalizeLocale($this->platformDefaultLocale());

        if (is_string($platformLocale) && in_array($platformLocale, $supported, true)) {
            return $platformLocale;
        }

        return config('app.fallback_locale', 'en');
    }

    private function normalizeLocale(mixed $locale): ?string
    {
        if (! is_string($locale) || blank($locale)) {
            return null;
        }

        return str($locale)
            ->lower()
            ->replace('_', '-')
            ->before('-')
            ->toString();
    }

    private function supportedLocales(): array
    {
        $configured = config('sanfaani.supported_languages', ['en', 'ar', 'fr']);
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

        if (! $user || ! $this->usersTableHasPreferredLocale()) {
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
