# leonardo-dev/monitoring-client

Cliente PHP para reportar eventos (crash/exception/log) a **Monitoring
Platform**. Habla el esquema propio de la plataforma directamente contra
`POST /api/v1/events`.

- **Sin dependencias obligatorias.** Funciona en cualquier proyecto PHP 7.4+.
- **Nunca lanza excepciones**: un fallo de red al reportar se registra con
  `error_log()` y el método devuelve `false`.
- Transporte HTTP por streams de PHP (sin `curl`).
- Adaptadores opcionales para **Laravel** y **Yii2**.

> Existe una versión legacy compatible con PHP 5.6 en
> [`leonardo-dev/monitoring-client-legacy`](https://github.com/leonardo-dev/monitoring-client-legacy).

## Instalación

```bash
composer require leonardo-dev/monitoring-client
```

## Uso (PHP puro)

```php
use LeonardoDev\Monitoring\MonitoringClient;

$monitoring = new MonitoringClient(
    baseUrl: 'https://monitoring.tu-empresa.com',
    apiKey: 'mp_...',
    platform: 'php',
    version: '1.0.0',
);

try {
    // tu código
} catch (\Throwable $e) {
    $monitoring->captureException($e);
}

$monitoring->captureMessage('Algo raro pasó', 'warning');
```

### Con DTO `Event` y `Config`

```php
use LeonardoDev\Monitoring\Config;
use LeonardoDev\Monitoring\Event;
use LeonardoDev\Monitoring\MonitoringClient;

$client = MonitoringClient::fromConfig(new Config(
    'https://monitoring.tu-empresa.com',
    'mp_...',
    'php',
    '1.0.0'
));

$client->captureEvent(new Event('custom', 'warning', 'algo pasó'));
```

## Laravel

1. Publicá la config (opcional):

   ```bash
   php artisan vendor:publish --tag=monitoring-config
   ```

2. Configurá en `.env`:

   ```env
   MONITORING_URL=https://monitoring.tu-empresa.com
   MONITORING_API_KEY=mp_...
   ```

3. Usá la facade (auto-descubierta):

   ```php
   use LeonardoDev\Monitoring\Laravel\Monitoring;

   try {
       // tu código
   } catch (\Throwable $e) {
       Monitoring::captureException($e);
   }
   ```

## Yii2

```php
'components' => [
    'monitoring' => [
        'class' => \LeonardoDev\Monitoring\Yii2\MonitoringComponent::class,
        'url' => getenv('MONITORING_URL'),
        'apiKey' => getenv('MONITORING_API_KEY'),
    ],
],

// Uso:
Yii::$app->monitoring->exception($e);
```

## Tests

```bash
composer install
composer test
```

## Licencia

MIT. Ver [LICENSE](LICENSE).
