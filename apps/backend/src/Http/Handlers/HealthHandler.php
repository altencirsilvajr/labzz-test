<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthHandler
{
    public function __invoke(ServerRequestInterface $request, array $params): ResponseInterface
    {
        return Json::response(['status' => 'ok', 'timestamp' => gmdate(DATE_ATOM)]);
    }
}
