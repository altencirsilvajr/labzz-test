<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    public function testResponseBuildsDefaultJsonHeadersAndBody(): void
    {
        $response = Json::response(['ok' => true, 'message' => 'Olá']);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertSame('no-store', $response->getHeaderLine('cache-control'));
        self::assertSame('{"ok":true,"message":"Olá"}', (string) $response->getBody());
    }

    public function testResponseAllowsStatusAndHeaderOverride(): void
    {
        $response = Json::response(['error' => 'bad request'], 400, [
            'cache-control' => 'public, max-age=60',
            'x-request-id' => 'req-123',
        ]);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('public, max-age=60', $response->getHeaderLine('cache-control'));
        self::assertSame('req-123', $response->getHeaderLine('x-request-id'));
        self::assertSame('{"error":"bad request"}', (string) $response->getBody());
    }
}
