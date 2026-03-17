<?php

declare(strict_types=1);

namespace App\Modules\Conversations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Ramsey\Uuid\Uuid;

final class ConversationsRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function listForUser(string $userId, int $limit = 50): array
    {
        $sql = <<<'SQL'
SELECT c.id, c.type, c.title, c.created_by, c.created_at
FROM conversations c
INNER JOIN conversation_members cm ON cm.conversation_id = c.id
WHERE cm.user_id = ?
ORDER BY c.created_at DESC
LIMIT ?
SQL;

        $rows = $this->db->fetchAllAssociative($sql, [$userId, $limit], [ParameterType::STRING, ParameterType::INTEGER]);

        return array_map(static fn (array $row): array => [
            'id' => $row['id'],
            'type' => $row['type'],
            'title' => $row['title'],
            'created_by' => $row['created_by'],
            'created_at' => $row['created_at'],
        ], $rows);
    }

    /** @param list<string> $memberIds @return array<string, mixed> */
    public function create(string $type, ?string $title, string $createdBy, array $memberIds): array
    {
        $conversationId = Uuid::uuid7()->toString();
        $now = gmdate('Y-m-d H:i:s');

        $this->db->insert('conversations', [
            'id' => $conversationId,
            'type' => $type,
            'title' => $title,
            'created_by' => $createdBy,
            'created_at' => $now,
        ]);

        $allMembers = array_values(array_unique(array_merge($memberIds, [$createdBy])));
        foreach ($allMembers as $memberId) {
            $this->db->insert('conversation_members', [
                'id' => Uuid::uuid7()->toString(),
                'conversation_id' => $conversationId,
                'user_id' => $memberId,
                'role' => $memberId === $createdBy ? 'owner' : 'member',
                'created_at' => $now,
            ]);
        }

        return [
            'id' => $conversationId,
            'type' => $type,
            'title' => $title,
            'created_by' => $createdBy,
            'members' => $allMembers,
            'created_at' => $now,
        ];
    }

    public function userBelongsToConversation(string $conversationId, string $userId): bool
    {
        $exists = $this->db->fetchOne(
            'SELECT COUNT(1) FROM conversation_members WHERE conversation_id = ? AND user_id = ?',
            [$conversationId, $userId]
        );

        return (int) $exists > 0;
    }

    public function deleteForUser(string $conversationId, string $userId): bool
    {
        $deleted = $this->db->executeStatement(
            'DELETE c FROM conversations c INNER JOIN conversation_members cm ON cm.conversation_id = c.id WHERE c.id = ? AND cm.user_id = ?',
            [$conversationId, $userId],
            [ParameterType::STRING, ParameterType::STRING]
        );

        return $deleted > 0;
    }
}
