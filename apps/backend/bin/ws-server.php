<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

if (!extension_loaded('openswoole')) {
    fwrite(STDERR, "OpenSwoole extension is required to run WebSocket server.\n");
    exit(1);
}

$host = $_ENV['WS_HOST'] ?? '0.0.0.0';
$port = (int) ($_ENV['WS_PORT'] ?? 9502);
$appKey = $_ENV['APP_KEY'] ?? 'fallback-app-key';

$secret = str_starts_with($appKey, 'base64:') ? (base64_decode(substr($appKey, 7), true) ?: $appKey) : $appKey;

$server = new OpenSwoole\WebSocket\Server($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
$server->set([
    'worker_num' => 1,
    'max_connection' => 10000,
    'package_max_length' => 2 * 1024 * 1024,
]);

$connections = [];

$server->on('open', function (OpenSwoole\WebSocket\Server $server, OpenSwoole\Http\Request $request) use (&$connections, $secret): void {
    $token = $request->get['token'] ?? null;

    if (!is_string($token) || $token === '') {
        $server->disconnect($request->fd, 4001, 'Missing token');
        return;
    }

    try {
        $decoded = (array) JWT::decode($token, new Key($secret, 'HS256'));
    } catch (Throwable) {
        $server->disconnect($request->fd, 4003, 'Invalid token');
        return;
    }

    $connections[$request->fd] = [
        'sub' => (string) ($decoded['sub'] ?? ''),
        'conversation_id' => null,
    ];

    $server->push($request->fd, json_encode([
        'event' => 'presence.updated',
        'payload' => ['status' => 'connected'],
    ]));
});

$server->on('message', function (OpenSwoole\WebSocket\Server $server, OpenSwoole\WebSocket\Frame $frame) use (&$connections): void {
    $message = json_decode($frame->data, true);
    if (!is_array($message)) {
        $server->push($frame->fd, json_encode(['event' => 'error', 'payload' => ['message' => 'Invalid payload']]));
        return;
    }

    $event = $message['event'] ?? null;
    $payload = isset($message['payload']) && is_array($message['payload']) ? $message['payload'] : [];

    if (!is_string($event)) {
        return;
    }

    if ($event === 'subscribe') {
        $conversationId = isset($payload['conversation_id']) ? (string) $payload['conversation_id'] : '';
        if ($conversationId !== '') {
            $connections[$frame->fd]['conversation_id'] = $conversationId;
            $server->push($frame->fd, json_encode(['event' => 'presence.updated', 'payload' => ['subscribed_to' => $conversationId]]));
        }
        return;
    }

    if (in_array($event, ['typing.start', 'typing.stop', 'message.send'], true)) {
        $conversationId = isset($payload['conversation_id']) ? (string) $payload['conversation_id'] : ($connections[$frame->fd]['conversation_id'] ?? '');
        if ($conversationId === '') {
            return;
        }

        foreach ($connections as $fd => $connection) {
            if ($fd === $frame->fd) {
                continue;
            }
            if (($connection['conversation_id'] ?? null) === $conversationId && $server->isEstablished((int) $fd)) {
                $server->push((int) $fd, json_encode([
                    'event' => $event === 'message.send' ? 'message.created' : 'typing.updated',
                    'payload' => $payload,
                ]));
            }
        }
    }
});

$server->on('close', function (OpenSwoole\WebSocket\Server $server, int $fd) use (&$connections): void {
    unset($connections[$fd]);
});

echo "WebSocket server listening on {$host}:{$port}\n";
$server->start();
