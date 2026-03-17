<?php

declare(strict_types=1);

namespace App\Modules\LLM;

use App\Infrastructure\Redis\QueuePublisher;
use App\Infrastructure\Security\AuthContext;
use App\Modules\Auth\CurrentUserService;
use App\Modules\Messages\MessagesRepository;
use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class LlmHandler
{
    public function __construct(
        private readonly LlmService $llm,
        private readonly MessagesRepository $messages,
        private readonly CurrentUserService $currentUser,
        private readonly QueuePublisher $queue
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $params): ResponseInterface
    {
        /** @var AuthContext $auth */
        $auth = $request->getAttribute('auth');
        $this->currentUser->resolve($auth);

        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        $conversationId = isset($body['conversation_id']) ? (string) $body['conversation_id'] : '';
        $prompt = isset($body['prompt']) ? trim((string) $body['prompt']) : '';

        if ($conversationId === '' || $prompt === '') {
            return Json::response(['error' => 'conversation_id and prompt are required.'], 422);
        }

        try {
            $reply = $this->llm->generateReply($prompt);
        } catch (RuntimeException $exception) {
            return Json::response(['error' => $exception->getMessage()], 503);
        }

        $botMessage = $this->messages->create($conversationId, 'assistant-bot', $reply);

        $this->queue->publish('search-index', [
            'entity' => 'message',
            'id' => (string) $botMessage['id'],
            'conversation_id' => (string) $botMessage['conversation_id'],
        ]);

        $this->queue->publishChannel('ws:conversation:' . $conversationId, (string) json_encode([
            'event' => 'llm.response',
            'payload' => $botMessage,
        ]));

        return Json::response(['data' => $botMessage], 201);
    }
}
