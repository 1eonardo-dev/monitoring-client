<?php

namespace LeonardoDev\Monitoring\Transport;

interface Transport
{
    /**
     * Envía `$body` (JSON) por POST a `$url` con `$headers`. Devuelve
     * `null` si la petición no pudo completarse (timeout, DNS, conexión
     * rechazada) — nunca lanza excepciones.
     *
     * @param array<string, string> $headers
     * @return array{status: int, body: string}|null
     */
    public function send(string $url, string $body, array $headers): ?array;
}
