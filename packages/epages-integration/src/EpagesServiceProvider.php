<?php

namespace EpagesIntegration;

use Illuminate\Support\ServiceProvider;
use EpagesIntegration\Services\EpagesApiClient;
use EpagesIntegration\Services\OAuthService;

class EpagesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__ . '/Config/epages.php',
            'epages'
        );

        // Register services as singletons
        $this->app->singleton(OAuthService::class, function ($app) {
            return new OAuthService(
                config('epages.client_id'),
                config('epages.client_secret'),
                config('epages.redirect_uri')
            );
        });

        $this->app->singleton(EpagesApiClient::class, function ($app) {
            return new EpagesApiClient();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/Config/epages.php' => config_path('epages.php'),
        ], 'epages-config');

        // Load and publish views
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'epages');

        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/epages'),
        ], 'epages-views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/epages.php');
    }
}
