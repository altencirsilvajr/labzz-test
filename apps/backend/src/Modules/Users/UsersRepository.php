<?php

declare(strict_types=1);

namespace App\Modules\Users;

use App\Infrastructure\Security\Encryptor;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Ramsey\Uuid\Uuid;

final class UsersRepository
{
    public function __construct(private readonly Connection $db, private readonly Encryptor $encryptor)
    {
    }

    /** @return list<array<string, mixed>> */
    public function list(int $limit = 50): array
    {
        $rows = $this->db->fetchAllAssociative(
            'SELECT id, auth0_sub, email, display_name, locale, created_at, updated_at FROM users ORDER BY created_at DESC LIMIT ?',
            [$limit],
            [ParameterType::INTEGER]
        );

        return array_map(fn (array $row): array => $this->mapUser($row), $rows);
    }

    /** @return array<string, mixed>|null */
    public function findById(string $id): ?array
    {
        $row = $this->db->fetchAssociative(
            'SELECT id, auth0_sub, email, display_name, locale, created_at, updated_at FROM users WHERE id = ?',
            [$id]
        );

        return $row === false ? null : $this->mapUser($row);
    }

    /** @return array<string, mixed>|null */
    public function findByAuth0Subject(string $sub): ?array
    {
        $row = $this->db->fetchAssociative(
            'SELECT id, auth0_sub, email, display_name, locale, created_at, updated_at FROM users WHERE auth0_sub = ?',
            [$sub]
        );

        return $row === false ? null : $this->mapUser($row);
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    public function create(array $payload): array
    {
        $id = Uuid::uuid7()->toString();
        $now = gmdate('Y-m-d H:i:s');

        $this->db->insert('users', [
            'id' => $id,
            'auth0_sub' => $payload['auth0_sub'] ?? null,
            'email' => $payload['email'],
            'display_name' => $payload['display_name'],
            'locale' => $payload['locale'] ?? 'pt-BR',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->db->insert('user_profiles', [
            'id' => Uuid::uuid7()->toString(),
            'user_id' => $id,
            'phone_encrypted' => isset($payload['phone']) ? $this->encryptor->encrypt((string) $payload['phone']) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (array) $this->findById($id);
    }

    /** @param array<string, mixed> $payload @return array<string, mixed>|null */
    public function update(string $id, array $payload): ?array
    {
        $existing = $this->findById($id);
        if ($existing === null) {
            return null;
        }

        $this->db->update('users', [
            'email' => $payload['email'] ?? $existing['email'],
            'display_name' => $payload['display_name'] ?? $existing['display_name'],
            'locale' => $payload['locale'] ?? $existing['locale'],
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ], ['id' => $id]);

        if (array_key_exists('phone', $payload)) {
            $this->db->update('user_profiles', [
                'phone_encrypted' => $payload['phone'] === null ? null : $this->encryptor->encrypt((string) $payload['phone']),
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ], ['user_id' => $id]);
        }

        return $this->findById($id);
    }

    public function delete(string $id): bool
    {
        return $this->db->delete('users', ['id' => $id]) > 0;
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function mapUser(array $row): array
    {
        $profile = $this->db->fetchAssociative('SELECT phone_encrypted FROM user_profiles WHERE user_id = ?', [$row['id']]);

        return [
            'id' => $row['id'],
            'auth0_sub' => $row['auth0_sub'],
            'email' => $row['email'],
            'display_name' => $row['display_name'],
            'locale' => $row['locale'],
            'phone' => ($profile !== false && !empty($profile['phone_encrypted'])) ? $this->encryptor->decrypt((string) $profile['phone_encrypted']) : null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }
}
