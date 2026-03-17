<?php

declare(strict_types=1);

namespace App\Modules\Messages;

use App\Support\Cursor;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Ramsey\Uuid\Uuid;

final class MessagesRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<string, mixed> */
    public function create(string $conversationId, string $senderId, string $body): array
    {
        $id = Uuid::uuid7()->toString();
        $now = gmdate('Y-m-d H:i:s');

        $this->db->insert('messages', [
            'id' => $id,
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'created_at' => $now,
        ]);

        return [
            'id' => $id,
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'created_at' => $now,
        ];
    }

    /** @return array{data: list<array<string, mixed>>, next_cursor: string|null} */
    public function listByConversation(string $conversationId, int $limit = 30, ?string $cursor = null): array
    {
        $decoded = Cursor::decode($cursor);

        if ($decoded !== null) {
            $sql = <<<'SQL'
SELECT id, conversation_id, sender_id, body, created_at
FROM messages
WHERE conversation_id = ?
  AND (created_at < ? OR (created_at = ? AND id < ?))
ORDER BY created_at DESC, id DESC
LIMIT ?
SQL;
            $rows = $this->db->fetchAllAssociative(
                $sql,
                [$conversationId, $decoded['created_at'], $decoded['created_at'], $decoded['id'], $limit],
                [ParameterType::STRING, ParameterType::STRING, ParameterType::STRING, ParameterType::STRING, ParameterType::INTEGER]
            );
        } else {
            $sql = <<<'SQL'
SELECT id, conversation_id, sender_id, body, created_at
FROM messages
WHERE conversation_id = ?
ORDER BY created_at DESC, id DESC
LIMIT ?
SQL;
            $rows = $this->db->fetchAllAssociative($sql, [$conversationId, $limit], [ParameterType::STRING, ParameterType::INTEGER]);
        }

        $nextCursor = null;
        if (!empty($rows)) {
            $last = end($rows);
            if (is_array($last)) {
                $nextCursor = Cursor::encode((string) $last['created_at'], (string) $last['id']);
            }
        }

        return [
            'data' => array_map(static fn (array $row): array => [
                'id' => $row['id'],
                'conversation_id' => $row['conversation_id'],
                'sender_id' => $row['sender_id'],
                'body' => $row['body'],
                'created_at' => $row['created_at'],
            ], $rows),
            'next_cursor' => $nextCursor,
        ];
    }
}
