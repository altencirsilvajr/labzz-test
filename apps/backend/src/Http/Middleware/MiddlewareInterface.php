<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    /** @param callable(ServerRequestInterface): ResponseInterface $next */
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface;
}
