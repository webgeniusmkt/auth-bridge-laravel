<?php

declare(strict_types=1);

namespace AuthBridge\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ScaffoldCommand extends Command
{
    protected $signature = 'auth-bridge:scaffold {--force : Overwrite existing scaffolding files}';

    protected $description = 'Add minimal OAuth controller, routes, and middleware for the Auth Bridge';

    public function handle(Filesystem $filesystem): int
    {
        $this->scaffoldController($filesystem);
        $this->scaffoldMiddleware($filesystem);
        $this->scaffoldRoutes($filesystem);

        $this->info('Scaffolding complete.');

        return self::SUCCESS;
    }

    private function scaffoldController(Filesystem $filesystem): void
    {
        $path = app_path('Http/Controllers/Auth/OAuthController.php');

        if ($filesystem->exists($path) && ! $this->option('force')) {
            $this->line('OAuthController already exists. Use --force to overwrite.');
            return;
        }

        $filesystem->ensureDirectoryExists(dirname($path));
        $filesystem->put($path, $this->controllerStub());
        $this->info('Created OAuthController.');
    }

    private function scaffoldRoutes(Filesystem $filesystem): void
    {
        $path = base_path('routes/web.php');

        if (! $filesystem->exists($path)) {
            $filesystem->put($path, "<?php\n\n");
        }

        $contents = $filesystem->get($path);

        if (Str::contains($contents, 'AuthBridge scaffolding')) {
            $this->line('Routes already contain Auth Bridge scaffolding.');
            return;
        }

        $filesystem->append($path, PHP_EOL . $this->routesStub());
        $this->info('Appended routes to routes/web.php.');
    }

    private function scaffoldMiddleware(Filesystem $filesystem): void
    {
        $path = app_path('Http/Middleware/InjectAuthBridgeContext.php');

        if (! $filesystem->exists($path) || $this->option('force')) {
            $filesystem->ensureDirectoryExists(dirname($path));
            $filesystem->put($path, $this->middlewareStub());
            $this->info('Created InjectAuthBridgeContext middleware.');
        }

        $this->registerMiddlewareAlias($filesystem);
    }

    private function registerMiddlewareAlias(Filesystem $filesystem): void
    {
        $kernel = app_path('Http/Kernel.php');

        if (! $filesystem->exists($kernel)) {
            return;
        }

        $contents = $filesystem->get($kernel);

        if (Str::contains($contents, 'inject-auth-ctx')) {
            return;
        }

        if (Str::contains($contents, 'protected $middlewareAliases = [')) {
            $updated = str_replace(
                'protected $middlewareAliases = [',
                "protected \\$middlewareAliases = [\n        'inject-auth-ctx' => \\App\\Http\\Middleware\\InjectAuthBridgeContext::class,",
                $contents,
                $count
            );
        } else {
            $updated = str_replace(
                'protected $routeMiddleware = [',
                "protected \\$routeMiddleware = [\n        'inject-auth-ctx' => \\App\\Http\\Middleware\\InjectAuthBridgeContext::class,",
                $contents,
                $count
            );
        }

        if (($count ?? 0) === 0) {
            $this->warn('Could not automatically register inject-auth-ctx middleware. Add it manually in app/Http/Kernel.php.');
            return;
        }

        $filesystem->put($kernel, $updated);
        $this->info('Registered route middleware alias: inject-auth-ctx.');
    }

    private function controllerStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class OAuthController extends Controller
{
    private function base(): string
    {
        return rtrim((string) config('auth-bridge.base_url'), '/');
    }

    private function clientId(): string
    {
        return (string) env('OAUTH_CLIENT_ID');
    }

    private function clientSecret(): string
    {
        return (string) env('OAUTH_CLIENT_SECRET');
    }

    private function redirectUri(): string
    {
        return route('oauth.callback', absolute: true);
    }

    private function appKey(): string
    {
        return (string) env('APP_KEY_SLUG', 'myapp');
    }

    private function storageKey(): string
    {
        return (string) config('auth-bridge.guard.storage_key', 'api_token');
    }

    public function redirect(Request $request)
    {
        $state = Str::random(32);
        $request->session()->put('oauth_state', $state);

        $url = $this->base() . '/oauth/authorize?' . http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $state = $request->session()->pull('oauth_state');
        abort_unless($state && $state === $request->query('state'), 400, 'Invalid state');

        $response = Http::asForm()->post($this->base() . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'code' => $request->query('code'),
        ]);

        abort_if($response->failed(), 401, 'Token exchange failed');

        $json = $response->json();
        $accessToken = $json['access_token'] ?? null;
        $expiresIn = (int) ($json['expires_in'] ?? 1800);

        $request->session()->put('access_token', $accessToken);
        $request->session()->put('token_expires_at', now()->addSeconds($expiresIn));

        $cookie = Cookie::create($this->storageKey())
            ->withValue((string) $accessToken)
            ->withHttpOnly(false)
            ->withSecure(app()->environment('production'))
            ->withSameSite('lax')
            ->withExpires(time() + $expiresIn);

        session(['x_app_key' => $this->appKey()]);

        return redirect('/')->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        if ($token = $request->session()->pull('access_token')) {
            Http::withToken($token)->post($this->base() . '/logout');
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $cookie = Cookie::create($this->storageKey())
            ->withValue('')
            ->withExpires(time() - 3600);

        return redirect('/login')->withCookie($cookie);
    }
}
PHP;
    }

    private function routesStub(): string
    {
        return <<<'PHP'

// --- AuthBridge scaffolding ---
Route::get('/login', [\App\Http\Controllers\Auth\OAuthController::class, 'redirect'])->name('login');
Route::get('/oauth/callback', [\App\Http\Controllers\Auth\OAuthController::class, 'callback'])->name('oauth.callback');
Route::post('/logout', [\App\Http\Controllers\Auth\OAuthController::class, 'logout'])->name('logout');

Route::middleware(['inject-auth-ctx', 'auth:auth-bridge'])->group(function () {
    Route::get('/', fn () => view('welcome'));
    Route::get('/me', fn () => request()->user());
});
// --- /AuthBridge scaffolding ---
PHP;
    }

    private function middlewareStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InjectAuthBridgeContext
{
    public function handle(Request $request, Closure $next)
    {
        if ($account = session('x_account_id')) {
            $request->headers->set(env('AUTH_BRIDGE_ACCOUNT_HEADER', 'X-Account-ID'), $account);
        }

        $request->headers->set(
            env('AUTH_BRIDGE_APP_HEADER', 'X-App-Key'),
            env('APP_KEY_SLUG', 'myapp')
        );

        return $next($request);
    }
}
PHP;
    }
}
