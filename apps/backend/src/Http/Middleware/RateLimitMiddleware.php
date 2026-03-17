<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Security\AuthContext;
use App\Support\Json;
use Predis\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Client $redis, private readonly int $maxPerMinute)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $identity = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $auth = $request->getAttribute('auth');
        if ($auth instanceof AuthContext) {
            $identity = $auth->subject;
        }

        $bucket = gmdate('YmdHi');
        $key = sprintf('rl:%s:%s', $identity, $bucket);
        $count = (int) $this->redis->incr($key);
        if ($count === 1) {
            $this->redis->expire($key, 61);
        }

        if ($count > $this->maxPerMinute) {
            return Json::response(['error' => 'Rate limit exceeded.'], 429, ['retry-after' => '60']);
        }

        return $next($request);
    }
}
