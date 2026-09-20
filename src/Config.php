<?php

namespace LeonardoDev\Monitoring;

/**
 * Client configuration. Groups the connection parameters that previously
 * lived as separate arguments in the MonitoringClient constructor.
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

    /** @var string */
    public $path;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        string $platform = 'php',
        ?string $version = null,
        int $timeoutSeconds = 2,
        string $path = '/api/v1/events'
    ) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->platform = $platform;
        $this->version = $version;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->path = $path;
    }

    /**
     * Build the configuration from an associative array (keys: url, key,
     * platform, version, timeout, path). Useful for framework adapters.
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
            isset($config['timeout']) ? (int) $config['timeout'] : 2,
            isset($config['path']) ? (string) $config['path'] : '/api/v1/events'
        );
    }
}
