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
    }
}
