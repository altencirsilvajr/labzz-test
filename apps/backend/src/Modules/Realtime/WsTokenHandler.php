<?php

declare(strict_types=1);

namespace App\Modules\Realtime;

use App\Infrastructure\Security\AuthContext;
use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class WsTokenHandler
{
    public function __construct(private readonly WsTokenService $tokens)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $params): ResponseInterface
    {
        /** @var AuthContext $auth */
        $auth = $request->getAttribute('auth');

        return Json::response(['data' => ['token' => $this->tokens->issue($auth), 'expires_in' => 300]]);
    }
}
