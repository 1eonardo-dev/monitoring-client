<?php

namespace LeonardoDev\Monitoring\Transport;

/**
 * Default transport based on PHP streams (`file_get_contents` +
 * `stream_context_create`), without depending on the `curl` extension.
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
                // Allows reading the status even on 4xx/5xx responses
                // instead of file_get_contents returning false.
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
