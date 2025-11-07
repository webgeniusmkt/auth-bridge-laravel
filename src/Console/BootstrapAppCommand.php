<?php

declare(strict_types=1);

namespace AuthBridge\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BootstrapAppCommand extends Command
{
    protected $signature = 'auth-bridge:bootstrap-app
        {app-key : applications.key value}
        {app-name : Display name for the app}
        {redirect : Redirect URI registered with the Auth API}
        {--accounts=* : Account IDs to enable}
        {--auth-base= : Override AUTH_BRIDGE_BASE_URL}
        {--bootstrap-token= : Admin bearer token for the Auth API internal bootstrap}
    ';

    protected $description = 'Call the Auth API internal bootstrap endpoint to create/link an OAuth client.';

    public function handle(): int
    {
        $authBase = rtrim((string) ($this->option('auth-base') ?: config('auth-bridge.base_url')), '/');
        $token = (string) $this->option('bootstrap-token');
        $path = (string) config('auth-bridge.internal_bootstrap_path', '/internal/apps/bootstrap');

        if ($authBase === '') {
            $this->error('Auth base URL is required. Provide --auth-base or set AUTH_BRIDGE_BASE_URL.');
            return self::FAILURE;
        }

        if ($token === '') {
            $this->error('--bootstrap-token is required');
            return self::FAILURE;
        }

        $response = Http::withToken($token)->post($authBase . $path, [
            'app_key' => $this->argument('app-key'),
            'app_name' => $this->argument('app-name'),
            'redirect_uri' => $this->argument('redirect'),
            'account_ids' => $this->option('accounts') ?: [],
        ]);

        if ($response->failed()) {
            $this->error('Auth API bootstrap failed: ' . $response->body());
            return self::FAILURE;
        }

        $json = $response->json();
        $this->info('client_id: ' . ($json['client_id'] ?? ''));
        $this->info('client_secret: ' . ($json['client_secret'] ?? ''));

        return self::SUCCESS;
    }
}
