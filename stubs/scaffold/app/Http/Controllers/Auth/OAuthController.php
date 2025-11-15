<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Cookie;

class OAuthController extends Controller
{
    private const SUPPORTED_SOCIAL_PROVIDERS = ['google'];

    public function redirect(Request $request)
    {
        [$state, $stateCookie] = $this->prepareState($request);
        $url = $this->oauthAuthorizeUrl($state);

        return $this->inertiaAwareRedirect($request, $url, $stateCookie);
    }

    public function social(Request $request, string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED_SOCIAL_PROVIDERS, true), 404);

        [$state, $stateCookie] = $this->prepareState($request);

        $intended = $this->oauthAuthorizeUrl($state);
        $providerPath = 'login/social/' . urlencode($provider);
        $url = $this->authServerEndpoint($this->publicBase(), $providerPath) . '?' . http_build_query([
            'intended' => $intended,
        ]);

        return $this->inertiaAwareRedirect($request, $url, $stateCookie);
    }

    public function callback(Request $request)
    {
        $stateFromSession = $request->session()->pull('oauth_state');
        $stateFromCookie = $request->cookie($this->stateCookieName());
        $incomingState = (string) $request->query('state');

        $stateCleanupCookie = Cookie::create($this->stateCookieName())
            ->withValue('')
            ->withExpires(time() - 3600);

        $isValidState = (
            $stateFromSession && hash_equals($stateFromSession, $incomingState)
        ) || (
            $stateFromCookie && hash_equals($stateFromCookie, $incomingState)
        );

        abort_unless($isValidState, 400, 'Invalid state');

        // Use base() (internal URL) for server-to-server token exchange
        // Laravel Passport registers /oauth/token at the root level, not under /api/v1
        // Note: This is a server-to-server call, so we use the internal Docker URL
        $tokenUrl = $this->authServerEndpoint($this->base(), 'oauth/token');

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'code' => $request->query('code'),
        ]);

        if ($response->failed()) {
            Log::error('OAuth token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $tokenUrl,
            ]);
            abort(401, 'Token exchange failed');
        }

        $json = $response->json();
        $accessToken = $json['access_token'] ?? null;
        $expiresIn = (int) ($json['expires_in'] ?? 1800);

        $request->session()->put('access_token', $accessToken);
        $request->session()->put('token_expires_at', now()->addSeconds($expiresIn));

        $cookie = Cookie::create($this->storageKey())
            ->withValue((string) $accessToken)
            ->withHttpOnly(false)
            ->withSecure($this->secureCookies())
            ->withSameSite('lax')
            ->withExpires(time() + $expiresIn);

        session(['x_app_key' => $this->appKey()]);

        return redirect('/')->withCookie($cookie)->withCookie($stateCleanupCookie);
    }

    public function logout(Request $request)
    {
        if ($token = $request->session()->pull('access_token')) {
            Http::withToken($token)->post($this->authServerEndpoint($this->base(), 'logout'));
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $cookie = Cookie::create($this->storageKey())
            ->withValue('')
            ->withExpires(time() - 3600);

        $stateCleanupCookie = Cookie::create($this->stateCookieName())
            ->withValue('')
            ->withExpires(time() - 3600);

        return redirect('/login')->withCookie($cookie)->withCookie($stateCleanupCookie);
    }

    private function base(): string
    {
        return rtrim((string) config('auth-bridge.base_url'), '/');
    }

    private function publicBase(): string
    {
        return rtrim((string) config('auth-bridge.public_url'), '/');
    }

    private function clientId(): string
    {
        return (string) config('auth-bridge.oauth.client_id');
    }

    private function clientSecret(): string
    {
        return (string) config('auth-bridge.oauth.client_secret');
    }

    private function redirectUri(): string
    {
        return route('oauth.callback', absolute: true);
    }

    private function appKey(): string
    {
        return (string) config('auth-bridge.app_key');
    }

    private function storageKey(): string
    {
        return (string) config('auth-bridge.guard.storage_key', 'api_token');
    }

    private function stateCookieName(): string
    {
        return $this->storageKey() . '_state';
    }

    private function secureCookies(): bool
    {
        return app()->environment('production');
    }

    /**
     * @return array{0: string, 1: Cookie}
     */
    private function prepareState(Request $request): array
    {
        $state = Str::random(32);
        $request->session()->put('oauth_state', $state);

        $stateCookie = Cookie::create($this->stateCookieName())
            ->withValue($state)
            ->withHttpOnly(true)
            ->withSecure($this->secureCookies())
            ->withSameSite('lax')
            ->withExpires(time() + 300);

        return [$state, $stateCookie];
    }

    private function oauthParameters(string $state): array
    {
        return [
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ];
    }

    private function oauthAuthorizeUrl(string $state): string
    {
        $base = $this->authServerEndpoint($this->publicBase(), 'oauth/authorize');

        return $base . '?' . http_build_query($this->oauthParameters($state));
    }

    private function inertiaAwareRedirect(Request $request, string $url, Cookie $stateCookie)
    {
        if ($request->header('X-Inertia')) {
            return Inertia::location($url)->withCookie($stateCookie);
        }

        return redirect()->away($url)->withCookie($stateCookie);
    }

    private function authServerEndpoint(string $base, string $path): string
    {
        $root = $this->authServerBase($base);

        if ($root === '') {
            return '/' . ltrim($path, '/');
        }

        return rtrim($root, '/') . '/' . ltrim($path, '/');
    }

    private function authServerBase(string $url): string
    {
        $trimmed = rtrim($url ?? '', '/');

        if ($trimmed === '') {
            return '';
        }

        $normalized = preg_replace('#/api(?:/v[\d\.]+)?$#', '', $trimmed);

        return $normalized !== '' ? $normalized : $trimmed;
    }
}
