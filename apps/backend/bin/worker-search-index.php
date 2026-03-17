<?php

declare(strict_types=1);

use App\Config\Settings;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Redis\RedisClient;
use App\Infrastructure\Search\SearchClient;

require_once __DIR__ . '/../vendor/autoload.php';

$settings = Settings::load(dirname(__DIR__));
$db = Database::fromSettings($settings['database'])->connection();
$redis = (new RedisClient($settings['redis']))->client();
$elastic = SearchClient::fromSettings($settings['elasticsearch'])->client();

$lastId = '0-0';

echo "Search index worker started...\n";

while (true) {
    $streams = $redis->xread(['search-index' => $lastId], 20, 5000);

    if (!is_array($streams) || !isset($streams['search-index'])) {
        usleep(150000);
        continue;
    }

    foreach ($streams['search-index'] as $entryId => $fields) {
        if (!is_array($fields)) {
            $lastId = (string) $entryId;
            continue;
        }

        $entity = $fields['entity'] ?? null;
        $id = $fields['id'] ?? null;

        if (!is_string($entity) || !is_string($id)) {
            $lastId = (string) $entryId;
            continue;
        }

        if ($entity === 'message') {
            $message = $db->fetchAssociative('SELECT id, conversation_id, sender_id, body, created_at FROM messages WHERE id = ?', [$id]);
            if ($message !== false) {
                $elastic->index(['index' => 'messages_v1', 'id' => $id, 'body' => $message]);
            }
        }

        if ($entity === 'user') {
            $user = $db->fetchAssociative('SELECT id, email, display_name, locale FROM users WHERE id = ?', [$id]);
            if ($user !== false) {
                $elastic->index(['index' => 'users_v1', 'id' => $id, 'body' => $user]);
            }
        }

        $lastId = (string) $entryId;
    }
}
