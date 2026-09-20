<?php

namespace LeonardoDev\Monitoring\Laravel;

use Illuminate\Support\ServiceProvider;
use LeonardoDev\Monitoring\Config;
use LeonardoDev\Monitoring\MonitoringClient;

/**
 * Registra MonitoringClient como singleton en apps Laravel que instalen
 * este paquete. Auto-descubierta por Laravel (ver `extra.laravel` en
 * composer.json de este paquete) — no requiere registro manual.
 *
 * Config esperada (publicable vía `php artisan vendor:publish`):
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
