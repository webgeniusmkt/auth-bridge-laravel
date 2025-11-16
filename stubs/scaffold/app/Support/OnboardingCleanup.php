<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\File;

final class OnboardingCleanup
{
    /**
     * Remove installation-time artifacts that are no longer needed after onboarding.
     */
    public static function run(): void
    {
        $files = [
            base_path('install.sh'),
            app_path('Jobs/RunOnboardingJob.php'),
            base_path('stubs/app/Jobs/RunOnboardingJob.php'),
            app_path('Http/Controllers/OnboardingController.php'),
            app_path('Http/Middleware/RedirectIfNotOnboarded.php'),
            app_path('Support/OnboardingState.php'),
        ];

        foreach ($files as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $directories = [
            resource_path('js/Pages/Onboarding'),
            resource_path('js/Pages/AuthBridge/Onboarding'),
        ];

        foreach ($directories as $directory) {
            if (File::isDirectory($directory)) {
                File::deleteDirectory($directory);
            }
        }

        self::removeOnboardingRoutes();
    }

    private static function removeOnboardingRoutes(): void
    {
        $routes = base_path('routes/web.php');

        if (! File::exists($routes)) {
            return;
        }

        $contents = File::get($routes);

        $updated = preg_replace('/^[^\n]*onboarding[^\n]*\n?/mi', '', $contents);

        if ($updated !== null && $updated !== $contents) {
            File::put($routes, $updated);
        }
    }
}
