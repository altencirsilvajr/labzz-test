<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Cursor;
use PHPUnit\Framework\TestCase;

final class CursorTest extends TestCase
{
    public function testCursorEncodeDecodeRoundTrip(): void
    {
        $cursor = Cursor::encode('2026-03-12 12:00:00', 'abc123');
        $decoded = Cursor::decode($cursor);

        self::assertSame('2026-03-12 12:00:00', $decoded['created_at']);
        self::assertSame('abc123', $decoded['id']);
    }
}
