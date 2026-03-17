<?php

declare(strict_types=1);

namespace App\Support;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final class Json
{
    /** @param array<string, mixed> $payload */
    public static function response(array $payload, int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response(
            $status,
            array_merge([
                'content-type' => 'application/json; charset=utf-8',
                'cache-control' => 'no-store',
            ], $headers),
            (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
