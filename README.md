# Monitoring Platform Client

A lightweight PHP client for reporting events (crashes, exceptions, logs) to
**Monitoring Platform**.

[![Packagist Version](https://img.shields.io/packagist/v/1eonardo-dev/monitoring-client.svg)](https://packagist.org/packages/1eonardo-dev/monitoring-client)
[![PHP Version](https://img.shields.io/packagist/php-v/1eonardo-dev/monitoring-client.svg)](https://packagist.org/packages/1eonardo-dev/monitoring-client)
[![License](https://img.shields.io/packagist/l/1eonardo-dev/monitoring-client.svg)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/1eonardo-dev/monitoring-client.svg)](https://packagist.org/packages/1eonardo-dev/monitoring-client)

- **No mandatory dependencies** — runs in any PHP 7.4+ project.
- **Never throws** — a network failure is logged via `error_log()` and the
  method returns `false`, so your application never breaks while reporting.
- **Stream-based HTTP transport** — no `curl` required.
- **Optional framework adapters** for Laravel and Yii2.

> The Laravel/Yii2 adapter classes are only loaded if the corresponding
> framework is installed (lazy autoloading + `suggest` in `composer.json`),
> so they never break plain-PHP projects.

## The two packages

This package targets modern PHP projects. If you still run PHP 5, use the
legacy package instead:

| Package | PHP | Framework adapters | Type hints |
|---|---|---|---|
| `1eonardo-dev/monitoring-client` | 7.4+ | Laravel & Yii2 | Yes |
| [`1eonardo-dev/monitoring-client-legacy`](https://github.com/1eonardo-dev/monitoring-client-legacy) | 5.6+ | None (pure PHP) | No |

## Requirements

- PHP 7.4 or later.

## Installation

```bash
composer require 1eonardo-dev/monitoring-client
```

## Quick start

```php
use LeonardoDev\Monitoring\MonitoringClient;

$monitoring = new MonitoringClient(
    'https://monitoring.your-company.com',
    'mp_...',
    'php',
    '1.0.0',
);

try {
    // your code
} catch (\Throwable $e) {
    $monitoring->captureException($e);
}

$monitoring->captureMessage('Something weird happened', 'warning');
```

## API

### `MonitoringClient`

| Method | Description |
|---|---|
| `captureException(\Throwable $e, array $context = [], ?string $level = null): bool` | Report an exception. |
| `captureMessage(string $message, string $level = 'info', array $context = []): bool` | Report a log message. |
| `captureEvent(Event $event): bool` | Report a custom event. |
| `send(array $data): bool` | Low-level send with a raw payload. |

### DTOs

- `Event` — value object for the event payload (`type`, `level`, `message`,
  `platform`, `version`, `fingerprint`, `context`).
- `Config` — groups the connection parameters (`baseUrl`, `apiKey`,
  `platform`, `version`, `timeoutSeconds`, `path`).

```php
use LeonardoDev\Monitoring\Config;
use LeonardoDev\Monitoring\Event;
use LeonardoDev\Monitoring\MonitoringClient;

$client = MonitoringClient::fromConfig(new Config(
    'https://monitoring.your-company.com',
    'mp_...',
    'php',
    '1.0.0',
));

$client->captureEvent(new Event('custom', 'warning', 'something happened'));
```

### Custom endpoint path

By default events are sent to `{baseUrl}/api/v1/events`. You can change the
path via the `path` argument (or the `path` key in `Config::fromArray`):

```php
$monitoring = new MonitoringClient(
    'https://monitoring.your-company.com',
    'mp_...',
    'php',
    '1.0.0',
    2,
    '/api/custom/ingest',
);
```

## Laravel integration

The package auto-discovers its service provider and facade. Optionally publish
the config:

```bash
php artisan vendor:publish --tag=monitoring-config
```

Add to your `.env`:

```env
MONITORING_URL=https://monitoring.your-company.com
MONITORING_API_KEY=mp_...
MONITORING_API_PATH=/api/v1/events
```

Then use the facade:

```php
use LeonardoDev\Monitoring\Laravel\Monitoring;

try {
    // your code
} catch (\Throwable $e) {
    Monitoring::captureException($e);
}
```

## Yii2 integration

```php
'components' => [
    'monitoring' => [
        'class' => \LeonardoDev\Monitoring\Yii2\MonitoringComponent::class,
        'url' => getenv('MONITORING_URL'),
        'apiKey' => getenv('MONITORING_API_KEY'),
    ],
],

// Usage:
Yii::$app->monitoring->exception($e);
```

## Custom transport

Implement `LeonardoDev\Monitoring\Transport\Transport` to use your own HTTP
client (Guzzle, cURL, etc.):

```php
use LeonardoDev\Monitoring\MonitoringClient;
use LeonardoDev\Monitoring\Transport\Transport;

class GuzzleTransport implements Transport
{
    public function send(string $url, string $body, array $headers): ?array
    {
        // ... return ['status' => int, 'body' => string], or null on failure.
    }
}

$client = new MonitoringClient(
    'https://monitoring.your-company.com',
    'mp_...',
    'php',
    null,
    2,
    '/api/v1/events',
    new GuzzleTransport(),
);
```

## Tests

```bash
composer install
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT. See [LICENSE](LICENSE).
