<?php

namespace LeonardoDev\Monitoring\Tests;

use LeonardoDev\Monitoring\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test_from_array_maps_keys(): void
    {
        $config = Config::fromArray([
            'url' => 'https://monitoring.example',
            'key' => 'mp_test',
            'platform' => 'laravel',
            'version' => '1.2.3',
            'timeout' => 5,
        ]);

        $this->assertSame('https://monitoring.example', $config->baseUrl);
        $this->assertSame('mp_test', $config->apiKey);
        $this->assertSame('laravel', $config->platform);
        $this->assertSame('1.2.3', $config->version);
        $this->assertSame(5, $config->timeoutSeconds);
    }

    public function test_from_array_defaults(): void
    {
        $config = Config::fromArray([
            'url' => 'https://monitoring.example',
            'key' => 'mp_test',
        ]);

        $this->assertSame('php', $config->platform);
        $this->assertNull($config->version);
        $this->assertSame(2, $config->timeoutSeconds);
    }
}
