<?php

declare(strict_types=1);

namespace App\Modules\Users;

use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UsersHandler
{
    public function __construct(private readonly UsersRepository $users)
    {
    }

    public function list(ServerRequestInterface $request, array $params): ResponseInterface
    {
        return Json::response(['data' => $this->users->list()]);
    }

    public function create(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];

        foreach (['email', 'display_name'] as $field) {
            if (empty($body[$field])) {
                return Json::response(['error' => sprintf('Field %s is required.', $field)], 422);
            }
        }

        return Json::response(['data' => $this->users->create($body)], 201);
    }

    public function show(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $user = $this->users->findById((string) $params['id']);

        return $user === null ? Json::response(['error' => 'User not found.'], 404) : Json::response(['data' => $user]);
    }

    public function update(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        $updated = $this->users->update((string) $params['id'], $body);

        return $updated === null ? Json::response(['error' => 'User not found.'], 404) : Json::response(['data' => $updated]);
    }

    public function delete(ServerRequestInterface $request, array $params): ResponseInterface
    {
        return $this->users->delete((string) $params['id']) ? Json::response(['status' => 'deleted']) : Json::response(['error' => 'User not found.'], 404);
    }
}
