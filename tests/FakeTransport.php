<?php

namespace LeonardoDev\Monitoring\Tests;

use LeonardoDev\Monitoring\Transport\Transport;

/**
 * Fake transport for tests: captures the last payload sent without hitting
 * the network, and allows simulating a response (or a failure, with `null`).
 */
class FakeTransport implements Transport
{
    /** @var string|null */
    public $lastUrl;

    /** @var array<string, mixed>|null */
    public $lastPayload;

    /** @var array<string, string>|null */
    public $lastHeaders;

    /** @var array{status: int, body: string}|null */
    private $response;

    /**
     * @param array{status: int, body: string}|null $response
     */
    public function __construct(?array $response = ['status' => 201, 'body' => '{"accepted":true}'])
    {
        $this->response = $response;
    }

    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: string}|null
     */
    public function send(string $url, string $body, array $headers): ?array
    {
        $this->lastUrl = $url;
        $this->lastPayload = json_decode($body, true);
        $this->lastHeaders = $headers;

        return $this->response;
    }
}
