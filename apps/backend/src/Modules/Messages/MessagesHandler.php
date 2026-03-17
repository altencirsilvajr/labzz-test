<?php

declare(strict_types=1);

namespace App\Modules\Messages;

use App\Infrastructure\Redis\QueuePublisher;
use App\Infrastructure\Security\AuthContext;
use App\Modules\Auth\CurrentUserService;
use App\Modules\Conversations\ConversationsRepository;
use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MessagesHandler
{
    public function __construct(
        private readonly MessagesRepository $messages,
        private readonly ConversationsRepository $conversations,
        private readonly CurrentUserService $currentUser,
        private readonly QueuePublisher $queue
    ) {
    }

    public function list(ServerRequestInterface $request, array $params): ResponseInterface
    {
        /** @var AuthContext $auth */
        $auth = $request->getAttribute('auth');
        $user = $this->currentUser->resolve($auth);

        $conversationId = (string) $params['id'];
        if (!$this->conversations->userBelongsToConversation($conversationId, (string) $user['id'])) {
            return Json::response(['error' => 'Forbidden.'], 403);
        }

        $query = $request->getQueryParams();
        $limit = isset($query['limit']) ? max(1, min(100, (int) $query['limit'])) : 30;
        $cursor = isset($query['cursor']) && is_string($query['cursor']) ? $query['cursor'] : null;

        return Json::response($this->messages->listByConversation($conversationId, $limit, $cursor));
    }

    public function create(ServerRequestInterface $request, array $params): ResponseInterface
    {
        /** @var AuthContext $auth */
        $auth = $request->getAttribute('auth');
        $user = $this->currentUser->resolve($auth);
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];

        $conversationId = isset($body['conversation_id']) ? (string) $body['conversation_id'] : '';
        $messageBody = isset($body['body']) ? trim((string) $body['body']) : '';

        if ($conversationId === '' || $messageBody === '') {
            return Json::response(['error' => 'conversation_id and body are required.'], 422);
        }

        if (!$this->conversations->userBelongsToConversation($conversationId, (string) $user['id'])) {
            return Json::response(['error' => 'Forbidden.'], 403);
        }

        $message = $this->messages->create($conversationId, (string) $user['id'], $messageBody);

        $this->queue->publish('search-index', [
            'entity' => 'message',
            'id' => (string) $message['id'],
            'conversation_id' => (string) $message['conversation_id'],
        ]);

        $this->queue->publishChannel('ws:conversation:' . $conversationId, (string) json_encode([
            'event' => 'message.created',
            'payload' => $message,
        ]));

        return Json::response(['data' => $message], 201);
    }
}
