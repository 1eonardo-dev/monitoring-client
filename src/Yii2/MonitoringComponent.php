<?php

namespace LeonardoDev\Monitoring\Yii2;

use LeonardoDev\Monitoring\MonitoringClient;
use Throwable;
use yii\base\Component;

/**
 * Componente de Yii2 para reportar eventos a Monitoring Platform.
 *
 * Configuración típica (config/web.php de la app anfitriona):
 *
 *   'components' => [
 *       'monitoring' => [
 *           'class' => \LeonardoDev\Monitoring\Yii2\MonitoringComponent::class,
 *           'url' => getenv('MONITORING_URL'),
 *           'apiKey' => getenv('MONITORING_API_KEY'),
 *       ],
 *   ],
 *
 * Uso: `Yii::$app->monitoring->exception($e);`
 */
class MonitoringComponent extends Component
{
    public string $url = '';

    public string $apiKey = '';

    public ?string $version = null;

    private ?MonitoringClient $client = null;

    /**
     * @param array<string, mixed> $context
     */
    public function exception(Throwable $e, array $context = [], ?string $level = null): bool
    {
        return $this->client()->captureException($e, $context, $level);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function message(string $message, string $level = 'info', array $context = []): bool
    {
        return $this->client()->captureMessage($message, $level, $context);
    }

    private function client(): MonitoringClient
    {
        return $this->client ??= new MonitoringClient($this->url, $this->apiKey, 'yii2', $this->version);
    }
}
