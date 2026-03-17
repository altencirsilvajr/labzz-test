<?php

declare(strict_types=1);

namespace App\Infrastructure\Redis;

use Predis\Client;

final class QueuePublisher
{
    public function __construct(private readonly Client $redis)
    {
    }

    /** @param array<string, scalar|null> $payload */
    public function publish(string $stream, array $payload): string
    {
        $arguments = [$stream, '*'];

        foreach ($payload as $field => $value) {
            $arguments[] = (string) $field;
            $arguments[] = $value === null ? '' : (string) $value;
        }

        /** @var string $id */
        $id = $this->redis->executeRaw(['XADD', ...$arguments]);

        return $id;
    }

    public function publishChannel(string $channel, string $message): int
    {
        return (int) $this->redis->publish($channel, $message);
    }
}
