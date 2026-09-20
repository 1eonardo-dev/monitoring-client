<?php

namespace LeonardoDev\Monitoring\Laravel;

use Illuminate\Support\ServiceProvider;
use LeonardoDev\Monitoring\Config;
use LeonardoDev\Monitoring\MonitoringClient;

/**
 * Registers MonitoringClient as a singleton in Laravel applications that
 * install this package. Auto-discovered by Laravel (see `extra.laravel` in
 * this package's composer.json) — no manual registration required.
 *
 * Expected config (publishable via `php artisan vendor:publish`):
 *   config/monitoring.php -> ['url' => env('MONITORING_URL'), 'key' => env('MONITORING_API_KEY')]
 */
class MonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/monitoring.php', 'monitoring');

        $this->app->singleton(MonitoringClient::class, function ($app) {
            $config = Config::fromArray(array_merge(
                (array) $app['config']->get('monitoring', []),
                [
                    'platform' => 'laravel',
                    'version' => $app['config']->get('app.version'),
                ]
            ));

            return MonitoringClient::fromConfig($config);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/monitoring.php' => config_path('monitoring.php'),
            ], 'monitoring-config');
        }
    }
}
