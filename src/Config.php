<?php

namespace LeonardoDev\Monitoring;

/**
 * Configuración del cliente. Agrupa los parámetros de conexión que antes
 * vivían sueltos en el constructor de MonitoringClient.
 */
final class Config
{
    /** @var string */
    public $baseUrl;

    /** @var string */
    public $apiKey;

    /** @var string */
    public $platform;

    /** @var string|null */
    public $version;

    /** @var int */
    public $timeoutSeconds;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        string $platform = 'php',
        ?string $version = null,
        int $timeoutSeconds = 2
    ) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->platform = $platform;
        $this->version = $version;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Construye la config desde un array asociativo (claves: url, key,
     * platform, version, timeout). Útil para adaptadores de framework.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            (string) ($config['url'] ?? ''),
            (string) ($config['key'] ?? ''),
            (string) ($config['platform'] ?? 'php'),
            isset($config['version']) ? (string) $config['version'] : null,
            isset($config['timeout']) ? (int) $config['timeout'] : 2
        );
    }
}
