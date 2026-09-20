<?php

namespace LeonardoDev\Monitoring\Tests;

use LeonardoDev\Monitoring\Event;
use LeonardoDev\Monitoring\MonitoringClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MonitoringClientTest extends TestCase
{
    public function test_capture_exception_builds_expected_payload(): void
    {
        $transport = new FakeTransport();
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', '1.0.0', 2, '/api/v1/events', $transport);

        $result = $client->captureException(new RuntimeException('boom'), ['extra' => 1]);

        $this->assertTrue($result);
        $this->assertSame('https://monitoring.example/api/v1/events', $transport->lastUrl);
        $this->assertSame('mp_test', $transport->lastHeaders['X-API-KEY']);
        $this->assertSame('exception', $transport->lastPayload['type']);
        $this->assertSame('error', $transport->lastPayload['level']);
        $this->assertSame('boom', $transport->lastPayload['message']);
        $this->assertSame('php', $transport->lastPayload['platform']);
        $this->assertSame('1.0.0', $transport->lastPayload['version']);
        $this->assertSame(RuntimeException::class, $transport->lastPayload['context']['exception']);
        $this->assertSame(1, $transport->lastPayload['context']['extra']);
    }

    public function test_capture_exception_allows_overriding_level(): void
    {
        $transport = new FakeTransport();
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', null, 2, '/api/v1/events', $transport);

        $client->captureException(new RuntimeException('boom'), [], 'critical');

        $this->assertSame('critical', $transport->lastPayload['level']);
    }

    public function test_capture_message_builds_log_payload(): void
    {
        $transport = new FakeTransport();
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', null, 2, '/api/v1/events', $transport);

        $client->captureMessage('hello', 'warning', ['foo' => 'bar']);

        $this->assertSame('log', $transport->lastPayload['type']);
        $this->assertSame('warning', $transport->lastPayload['level']);
        $this->assertSame('hello', $transport->lastPayload['message']);
        $this->assertSame('bar', $transport->lastPayload['context']['foo']);
    }

    public function test_capture_event_sends_event_payload(): void
    {
        $transport = new FakeTransport();
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', null, 2, '/api/v1/events', $transport);

        $client->captureEvent(new Event('custom', 'warning', 'something happened', 'android', '2.5.1'));

        $this->assertSame('custom', $transport->lastPayload['type']);
        $this->assertSame('warning', $transport->lastPayload['level']);
        $this->assertSame('something happened', $transport->lastPayload['message']);
        $this->assertSame('android', $transport->lastPayload['platform']);
        $this->assertSame('2.5.1', $transport->lastPayload['version']);
    }

    public function test_custom_path_is_used_in_endpoint(): void
    {
        $transport = new FakeTransport();
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', null, 2, '/api/custom/ingest', $transport);

        $client->captureMessage('hello');

        $this->assertSame('https://monitoring.example/api/custom/ingest', $transport->lastUrl);
    }

    public function test_send_returns_false_when_transport_fails(): void
    {
        $transport = new FakeTransport(null);
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', null, 2, '/api/v1/events', $transport);

        $this->assertFalse($client->send(['type' => 'log', 'message' => 'x']));
    }

    public function test_send_returns_false_on_non_2xx_status(): void
    {
        $transport = new FakeTransport(['status' => 422, 'body' => '{}']);
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', null, 2, '/api/v1/events', $transport);

        $this->assertFalse($client->send(['type' => 'log', 'message' => 'x']));
    }

    public function test_send_does_not_throw_on_transport_failure(): void
    {
        $transport = new FakeTransport(null);
        $client = new MonitoringClient('https://monitoring.example', 'mp_test', 'php', null, 2, '/api/v1/events', $transport);

        $client->send(['type' => 'crash', 'message' => 'x']);
        $this->addToAssertionCount(1);
    }
}
