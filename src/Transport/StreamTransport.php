<?php

namespace LeonardoDev\Monitoring\Transport;

/**
 * Transporte por defecto basado en streams de PHP (`file_get_contents` +
 * `stream_context_create`), sin depender de la extensión `curl`.
 */
class StreamTransport implements Transport
{
    /** @var int */
    private $timeoutSeconds;

    public function __construct(int $timeoutSeconds = 2)
    {
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: string}|null
     */
    public function send(string $url, string $body, array $headers): ?array
    {
        $headerLines = '';
        foreach ($headers as $name => $value) {
            $headerLines .= "{$name}: {$value}\r\n";
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headerLines,
                'content' => $body,
                'timeout' => $this->timeoutSeconds,
                // Para poder leer el status incluso en respuestas 4xx/5xx
                // en vez de que file_get_contents devuelva false.
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        return [
            'status' => $this->parseStatus($http_response_header ?? []),
            'body' => $response,
        ];
    }

    /**
     * @param string[] $responseHeaders
     */
    private function parseStatus(array $responseHeaders): int
    {
        foreach ($responseHeaders as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }
}
