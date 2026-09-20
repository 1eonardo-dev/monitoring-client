<?php

namespace LeonardoDev\Monitoring\Laravel;

use Illuminate\Support\Facades\Facade;
use LeonardoDev\Monitoring\Event;
use LeonardoDev\Monitoring\MonitoringClient;
use Throwable;

/**
 * @method static bool captureException(Throwable $e, array $context = [], ?string $level = null)
 * @method static bool captureMessage(string $message, string $level = 'info', array $context = [])
 * @method static bool captureEvent(Event $event)
 * @method static bool send(array $data)
 *
 * @see MonitoringClient
 */
class Monitoring extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MonitoringClient::class;
    }
}
