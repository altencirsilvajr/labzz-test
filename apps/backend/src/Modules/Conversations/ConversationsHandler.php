<?php

declare(strict_types=1);

namespace App\Modules\Conversations;

use App\Infrastructure\Security\AuthContext;
use App\Modules\Auth\CurrentUserService;
use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ConversationsHandler
{
    public function __construct(private readonly ConversationsRepository $conversations, private readonly CurrentUserService $currentUser)
    {
    }

    public function list(ServerRequestInterface $request, array $params): ResponseInterface
    {
        /** @var AuthContext $auth */
        $auth = $request->getAttribute('auth');
        $user = $this->currentUser->resolve($auth);

        return Json::response(['data' => $this->conversations->listForUser((string) $user['id'])]);
    }

    public function create(ServerRequestInterface $request, array $params): ResponseInterface
    {
        /** @var AuthContext $auth */
        $auth = $request->getAttribute('auth');
        $user = $this->currentUser->resolve($auth);
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];

        $type = isset($body['type']) && in_array($body['type'], ['dm', 'group'], true) ? $body['type'] : 'dm';
        $title = isset($body['title']) && is_string($body['title']) ? trim($body['title']) : null;
        $members = isset($body['members']) && is_array($body['members'])
            ? array_values(array_filter($body['members'], static fn ($member): bool => is_string($member) && $member !== ''))
            : [];

        $conversation = $this->conversations->create($type, $title, (string) $user['id'], $members);

        return Json::response(['data' => $conversation], 201);
    }

    public function delete(ServerRequestInterface $request, array $params): ResponseInterface
    {
        /** @var AuthContext $auth */
        $auth = $request->getAttribute('auth');
        $user = $this->currentUser->resolve($auth);

        $conversationId = isset($params['id']) ? (string) $params['id'] : '';
        if ($conversationId === '') {
            return Json::response(['error' => 'Conversation id is required.'], 422);
        }

        if (!$this->conversations->deleteForUser($conversationId, (string) $user['id'])) {
            return Json::response(['error' => 'Conversation not found.'], 404);
        }

        return Json::response(['data' => ['deleted' => true]]);
    }
}
