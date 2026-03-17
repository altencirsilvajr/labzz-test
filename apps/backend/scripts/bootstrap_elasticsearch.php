<?php

declare(strict_types=1);

use App\Config\Settings;
use App\Infrastructure\Search\SearchClient;

require_once __DIR__ . '/../vendor/autoload.php';

$settings = Settings::load(dirname(__DIR__));
$client = SearchClient::fromSettings($settings['elasticsearch'])->client();

$indices = [
    'users_v1' => [
        'mappings' => [
            'properties' => [
                'id' => ['type' => 'keyword'],
                'email' => ['type' => 'keyword'],
                'display_name' => ['type' => 'text'],
                'locale' => ['type' => 'keyword'],
            ],
        ],
    ],
    'messages_v1' => [
        'mappings' => [
            'properties' => [
                'id' => ['type' => 'keyword'],
                'conversation_id' => ['type' => 'keyword'],
                'sender_id' => ['type' => 'keyword'],
                'body' => ['type' => 'text'],
                'created_at' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||strict_date_optional_time||epoch_millis'],
            ],
        ],
    ],
];

foreach ($indices as $name => $definition) {
    $exists = $client->indices()->exists(['index' => $name]);
    if ($exists) {
        echo "Index {$name} already exists.\n";
        continue;
    }

    $client->indices()->create(['index' => $name, 'body' => $definition]);
    echo "Created index {$name}.\n";
}
