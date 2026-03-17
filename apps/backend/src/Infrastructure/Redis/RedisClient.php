<?php

declare(strict_types=1);

namespace App\Infrastructure\Redis;

use Predis\Client;

final class RedisClient
{
    private Client $client;

    /** @param array<string, mixed> $settings */
    public function __construct(array $settings)
    {
        $parameters = [
            'scheme' => $settings['scheme'],
            'host' => $settings['host'],
            'port' => $settings['port'],
        ];

        if (!empty($settings['password'])) {
            $parameters['password'] = $settings['password'];
        }

        $this->client = new Client($parameters);
    }

    public function client(): Client
    {
        return $this->client;
    }
}
