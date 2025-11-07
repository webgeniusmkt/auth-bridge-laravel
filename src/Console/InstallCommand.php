<?php

declare(strict_types=1);

namespace AuthBridge\Laravel\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'auth-bridge:install';

    protected $description = 'Publish Auth Bridge config & migrations, then run database migrations.';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--tag' => 'auth-bridge-config', '--force' => true]);
        $this->call('vendor:publish', ['--tag' => 'auth-bridge-migrations', '--force' => true]);
        $this->call('migrate');
        $this->info('Auth Bridge installed.');

        return self::SUCCESS;
    }
}
