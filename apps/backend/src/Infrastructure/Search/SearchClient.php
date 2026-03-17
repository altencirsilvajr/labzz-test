<?php

declare(strict_types=1);

namespace App\Infrastructure\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

final class SearchClient
{
    public function __construct(private readonly Client $client)
    {
    }

    /** @param array<string, mixed> $settings */
    public static function fromSettings(array $settings): self
    {
        $client = ClientBuilder::create()->setHosts([$settings['host']])->build();

        return new self($client);
    }

    public function client(): Client
    {
        return $this->client;
    }
}
