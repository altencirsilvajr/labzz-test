<?php

declare(strict_types=1);

namespace App\Modules\Search;

use App\Infrastructure\Search\SearchClient;
use App\Support\Json;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class SearchHandler
{
    public function __construct(private readonly SearchClient $search, private readonly Connection $db)
    {
    }

    public function users(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $query = trim((string) ($request->getQueryParams()['query'] ?? ''));
        if ($query === '') {
            return Json::response(['data' => []]);
        }

        try {
            $response = $this->search->client()->search([
                'index' => 'users_v1',
                'body' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['display_name^2', 'email'],
                        ],
                    ],
                    'size' => 20,
                ],
            ]);

            $hits = $response['hits']['hits'] ?? [];
            $data = array_map(static fn (array $hit): array => (array) ($hit['_source'] ?? []), $hits);

            return Json::response(['data' => $data]);
        } catch (Throwable) {
            $rows = $this->db->fetchAllAssociative(
                'SELECT id, email, display_name, locale FROM users WHERE email LIKE ? OR display_name LIKE ? LIMIT 20',
                ['%' . $query . '%', '%' . $query . '%']
            );

            return Json::response(['data' => $rows]);
        }
    }

    public function messages(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $query = trim((string) ($request->getQueryParams()['query'] ?? ''));
        if ($query === '') {
            return Json::response(['data' => []]);
        }

        try {
            $response = $this->search->client()->search([
                'index' => 'messages_v1',
                'body' => [
                    'query' => ['match' => ['body' => $query]],
                    'size' => 50,
                ],
            ]);

            $hits = $response['hits']['hits'] ?? [];
            $data = array_map(static fn (array $hit): array => (array) ($hit['_source'] ?? []), $hits);

            return Json::response(['data' => $data]);
        } catch (Throwable) {
            $rows = $this->db->fetchAllAssociative(
                'SELECT id, conversation_id, sender_id, body, created_at FROM messages WHERE body LIKE ? ORDER BY created_at DESC LIMIT 50',
                ['%' . $query . '%']
            );

            return Json::response(['data' => $rows]);
        }
    }
}
