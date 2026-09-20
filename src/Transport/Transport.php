<?php

namespace LeonardoDev\Monitoring\Transport;

interface Transport
{
    /**
     * Sends `$body` (JSON) via POST to `$url` with `$headers`. Returns
     * `null` if the request could not be completed (timeout, DNS,
     * connection refused) — never throws exceptions.
     *
     * @param array<string, string> $headers
     * @return array{status: int, body: string}|null
     */
    public function send(string $url, string $body, array $headers): ?array;
}
