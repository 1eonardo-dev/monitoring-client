<?php

namespace LeonardoDev\Monitoring;

/**
 * Value object representing an event to report, following the
 * POST /api/v1/events schema (type, level, message, platform, version,
 * fingerprint, context).
 */
final class Event
{
    /** @var string */
    public $type;

    /** @var string|null */
    public $level;

    /** @var string|null */
    public $message;

    /** @var string|null */
    public $platform;

    /** @var string|null */
    public $version;

    /** @var string|null */
    public $fingerprint;

    /** @var array<string, mixed> */
    public $context;

    /**
     * @param string               $type
     * @param string|null          $level
     * @param string|null          $message
     * @param string|null          $platform
     * @param string|null          $version
     * @param string|null          $fingerprint
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $type,
        ?string $level = null,
        ?string $message = null,
        ?string $platform = null,
        ?string $version = null,
        ?string $fingerprint = null,
        array $context = []
    ) {
        $this->type = $type;
        $this->level = $level;
        $this->message = $message;
        $this->platform = $platform;
        $this->version = $version;
        $this->fingerprint = $fingerprint;
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'level' => $this->level,
            'message' => $this->message,
            'platform' => $this->platform,
            'version' => $this->version,
            'fingerprint' => $this->fingerprint,
            'context' => $this->context,
        ], static function ($value) {
            return $value !== null && $value !== [];
        });
    }
}
