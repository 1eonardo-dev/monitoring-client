<?php

namespace LeonardoDev\Monitoring;

use LeonardoDev\Monitoring\Transport\StreamTransport;
use LeonardoDev\Monitoring\Transport\Transport;
use Throwable;

/**
 * Cliente PHP genérico para Monitoring Platform. Habla el esquema propio
 * de la plataforma directamente contra `POST /api/v1/events`.
 *
 * Nunca lanza excepciones: un fallo al reportar un error no debe generar
 * un error nuevo en la aplicación que lo usa.
 */
class MonitoringClient
{
    /** @var Transport */
    private $transport;

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $platform;

    /** @var string|null */
    private $version;

    /** @var int */
    private $timeoutSeconds;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        string $platform = 'php',
        ?string $version = null,
        int $timeoutSeconds = 2,
        ?Transport $transport = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->platform = $platform;
        $this->version = $version;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->transport = $transport ?? new StreamTransport($this->timeoutSeconds);
    }

    public static function fromConfig(Config $config, ?Transport $transport = null): self
    {
        return new self(
            $config->baseUrl,
            $config->apiKey,
            $config->platform,
            $config->version,
            $config->timeoutSeconds,
            $transport
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function captureException(Throwable $e, array $context = [], ?string $level = null): bool
    {
        return $this->captureEvent(new Event(
            'exception',
            $level ?? 'error',
            $e->getMessage(),
            null,
            null,
            null,
            array_merge([
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stack' => $e->getTraceAsString(),
            ], $context)
        ));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function captureMessage(string $message, string $level = 'info', array $context = []): bool
    {
        return $this->captureEvent(new Event(
            'log',
            $level,
            $message,
            null,
            null,
            null,
            $context
        ));
    }

    public function captureEvent(Event $event): bool
    {
        return $this->send($event->toArray());
    }

    /**
     * Envío de bajo nivel para casos que no encajan en captureException/
     * captureMessage/captureEvent — `$data` sigue el esquema de la API
     * (`type`, `level`, `message`, `fingerprint`, `context`, ...).
     *
     * @param array<string, mixed> $data
     */
    public function send(array $data): bool
    {
        $payload = array_filter(array_merge([
            'platform' => $this->platform,
            'version' => $this->version,
        ], $data), static function ($value) {
            return $value !== null && $value !== [];
        });

        $body = json_encode($payload);

        if ($body === false) {
            error_log('[MonitoringClient] no se pudo serializar el payload a JSON.');

            return false;
        }

        $response = $this->transport->send($this->endpoint(), $body, [
            'Content-Type' => 'application/json',
            'X-API-KEY' => $this->apiKey,
        ]);

        if ($response === null) {
            error_log('[MonitoringClient] la petición falló (timeout o conexión rechazada).');

            return false;
        }

        if ($response['status'] < 200 || $response['status'] >= 300) {
            error_log("[MonitoringClient] respuesta inesperada: HTTP {$response['status']}.");

            return false;
        }

        return true;
    }

    private function endpoint(): string
    {
        return rtrim($this->baseUrl, '/') . '/api/v1/events';
    }
}
