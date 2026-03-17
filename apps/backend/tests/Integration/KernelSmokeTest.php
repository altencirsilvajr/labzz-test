<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Http\Kernel;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class KernelSmokeTest extends TestCase
{
    public function testHealthEndpointReturns200(): void
    {
        if (($_ENV['RUN_INTEGRATION_TESTS'] ?? '') !== '1') {
            self::markTestSkipped('Integration tests are disabled by default. Set RUN_INTEGRATION_TESTS=1.');
        }

        /** @var Kernel $kernel */
        $kernel = require dirname(__DIR__, 2) . '/src/bootstrap.php';
        $response = $kernel->handle(new ServerRequest('GET', '/health'));

        self::assertSame(200, $response->getStatusCode());
    }
}
