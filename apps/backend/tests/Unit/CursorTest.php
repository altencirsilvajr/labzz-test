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

    public function testDecodeReturnsNullWhenCursorIsNullOrEmpty(): void
    {
        self::assertNull(Cursor::decode(null));
        self::assertNull(Cursor::decode(''));
    }

    public function testDecodeReturnsNullWhenBase64IsInvalid(): void
    {
        self::assertNull(Cursor::decode('***not-base64***'));
    }

    public function testDecodeReturnsNullWhenDecodedValueHasInvalidShape(): void
    {
        $invalidCursor = base64_encode('missing-separator');

        self::assertNull(Cursor::decode($invalidCursor));
    }
}
