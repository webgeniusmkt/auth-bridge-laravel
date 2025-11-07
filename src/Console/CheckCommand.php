<?php

declare(strict_types=1);

namespace AuthBridge\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckCommand extends Command
{
    protected $signature = 'auth-bridge:check {--auth-base=} {--token=} {--user-endpoint=}';

    protected $description = 'Verify connectivity to the Auth API /health endpoint and optionally /user.';

    public function handle(): int
    {
        $authBase = rtrim((string) ($this->option('auth-base') ?: config('auth-bridge.base_url')), '/');

        if ($authBase === '') {
            $this->error('Auth base URL is required. Provide --auth-base or set AUTH_BRIDGE_BASE_URL.');
            return self::FAILURE;
        }
        $healthUrl = $authBase . '/health';

        $health = Http::get($healthUrl);
        $this->line("GET {$healthUrl} -> {$health->status()}");

        if ($health->failed()) {
            $this->error('Auth API health check failed.');
            return self::FAILURE;
        }

        $token = (string) ($this->option('token') ?: env('AUTH_BRIDGE_CHECK_TOKEN', ''));

        if ($token === '') {
            $this->comment('No token detected (--token or AUTH_BRIDGE_CHECK_TOKEN). Skipping /user check.');
            return self::SUCCESS;
        }

        $userEndpoint = '/' . ltrim((string) ($this->option('user-endpoint') ?: config('auth-bridge.user_endpoint', '/user')), '/');
        $userUrl = $authBase . $userEndpoint;
        $user = Http::withToken($token)->get($userUrl);
        $this->line("GET {$userUrl} -> {$user->status()}");

        if ($user->failed()) {
            $this->error('Auth API /user check failed.');
            return self::FAILURE;
        }

        $this->info('Auth API checks passed.');

        return self::SUCCESS;
    }
}
