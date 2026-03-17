<?php

declare(strict_types=1);

namespace App\Config;

use Symfony\Component\Dotenv\Dotenv;

final class Settings
{
    /** @return array<string, mixed> */
    public static function load(string $basePath): array
    {
        if (is_file($basePath . '/.env')) {
            (new Dotenv())->load($basePath . '/.env');
        }

        return [
            'app' => [
                'env' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
                'name' => $_ENV['APP_NAME'] ?? 'labzz-chat',
                'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
                'csrf_cookie_name' => $_ENV['CSRF_COOKIE_NAME'] ?? 'csrf_token',
            ],
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
                'name' => $_ENV['DB_NAME'] ?? 'chat',
                'user' => $_ENV['DB_USER'] ?? 'chat',
                'password' => $_ENV['DB_PASSWORD'] ?? 'chat',
            ],
            'redis' => [
                'scheme' => $_ENV['REDIS_SCHEME'] ?? 'tcp',
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
                'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            ],
            'elasticsearch' => [
                'host' => $_ENV['ELASTICSEARCH_HOST'] ?? 'http://127.0.0.1:9200',
            ],
            'auth0' => [
                'domain' => $_ENV['AUTH0_DOMAIN'] ?? '',
                'audience' => $_ENV['AUTH0_AUDIENCE'] ?? '',
            ],
            'security' => [
                'encryption_key_base64' => $_ENV['ENCRYPTION_KEY_BASE64'] ?? '',
                'rate_limit_per_minute' => (int) ($_ENV['RATE_LIMIT_PER_MINUTE'] ?? 120),
                'ws_token_ttl' => (int) ($_ENV['WS_TOKEN_TTL_SECONDS'] ?? 300),
            ],
            'llm' => [
                'enabled' => filter_var($_ENV['LLM_ENABLED'] ?? false, FILTER_VALIDATE_BOOL),
                'api_url' => $_ENV['LLM_API_URL'] ?? '',
                'api_key' => $_ENV['LLM_API_KEY'] ?? '',
                'model' => $_ENV['LLM_MODEL'] ?? 'gpt-4.1-mini',
            ],
        ];
    }
}
