<?php

namespace LeonardoDev\Monitoring;

use LeonardoDev\Monitoring\Transport\StreamTransport;
use LeonardoDev\Monitoring\Transport\Transport;
use Throwable;

/**
 * Generic PHP client for Monitoring Platform. Speaks the platform's own
 * schema directly against `POST /api/v1/events`.
 *
 * Never throws exceptions: a failure to report an error must not produce
 * a new error in the host application.
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
     * Low-level send for cases that do not fit into captureException/
     * captureMessage/captureEvent — `$data` follows the API schema
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
            error_log('[MonitoringClient] failed to serialize the payload to JSON.');

            return false;
        }

        $response = $this->transport->send($this->endpoint(), $body, [
            'Content-Type' => 'application/json',
            'X-API-KEY' => $this->apiKey,
        ]);

        if ($response === null) {
            error_log('[MonitoringClient] request failed (timeout or connection refused).');

            return false;
        }

        if ($response['status'] < 200 || $response['status'] >= 300) {
            error_log("[MonitoringClient] unexpected response: HTTP {$response['status']}.");

            return false;
        }

        return true;
    }

    private function endpoint(): string
    {
        return rtrim($this->baseUrl, '/') . '/api/v1/events';
    }
}
