<?php

namespace LeonardoDev\Monitoring\Tests;

use LeonardoDev\Monitoring\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function test_to_array_filters_null_values(): void
    {
        $event = new Event('log', 'info', 'mensaje');

        $this->assertSame([
            'type' => 'log',
            'level' => 'info',
            'message' => 'mensaje',
        ], $event->toArray());
    }

    public function test_to_array_includes_context_and_fingerprint(): void
    {
        $event = new Event('exception', 'error', 'boom', 'php', '1.0.0', 'abc123', ['extra' => 1]);

        $array = $event->toArray();

        $this->assertSame('abc123', $array['fingerprint']);
        $this->assertSame(1, $array['context']['extra']);
    }
}
