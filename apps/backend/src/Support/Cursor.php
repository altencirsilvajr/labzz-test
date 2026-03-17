<?php

declare(strict_types=1);

namespace App\Support;

final class Cursor
{
    /** @return array{created_at: string, id: string}|null */
    public static function decode(?string $cursor): ?array
    {
        if ($cursor === null || $cursor === '') {
            return null;
        }

        $decoded = base64_decode($cursor, true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 2) {
            return null;
        }

        return ['created_at' => $parts[0], 'id' => $parts[1]];
    }

    public static function encode(string $createdAt, string $id): string
    {
        return base64_encode($createdAt . '|' . $id);
    }
}
