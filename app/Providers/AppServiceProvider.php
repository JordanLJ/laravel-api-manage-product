<?php

namespace App\Providers;

use RuntimeException;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $issuer = (string) config('services.identity.issuer');

        // Mandatory logical issuer for all OAuth/OIDC integrations.
        if (trim($issuer) === '') {
            throw new RuntimeException('IDENTITY_ISSUER is required and must be a stable logical issuer URL.');
        }
    }
}
