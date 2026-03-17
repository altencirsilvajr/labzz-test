<?php

declare(strict_types=1);

use App\Config\Settings;
use App\Http\Handlers\HealthHandler;
use App\Http\Kernel;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\JsonBodyMiddleware;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Redis\QueuePublisher;
use App\Infrastructure\Redis\RedisClient;
use App\Infrastructure\Search\SearchClient;
use App\Infrastructure\Security\Auth0JwtVerifier;
use App\Infrastructure\Security\Encryptor;
use App\Modules\Auth\CurrentUserService;
use App\Modules\Conversations\ConversationsHandler;
use App\Modules\Conversations\ConversationsRepository;
use App\Modules\LLM\LlmHandler;
use App\Modules\LLM\LlmService;
use App\Modules\Messages\MessagesHandler;
use App\Modules\Messages\MessagesRepository;
use App\Modules\Realtime\WsTokenHandler;
use App\Modules\Realtime\WsTokenService;
use App\Modules\Search\SearchHandler;
use App\Modules\Users\UsersHandler;
use App\Modules\Users\UsersRepository;

require_once __DIR__ . '/../vendor/autoload.php';

$basePath = dirname(__DIR__);
$settings = Settings::load($basePath);

$database = Database::fromSettings($settings['database']);
$redis = (new RedisClient($settings['redis']))->client();
$search = SearchClient::fromSettings($settings['elasticsearch']);
$encryptor = new Encryptor($settings['security']['encryption_key_base64']);
$authVerifier = new Auth0JwtVerifier($settings['auth0']);
$queue = new QueuePublisher($redis);

$usersRepository = new UsersRepository($database->connection(), $encryptor);
$currentUser = new CurrentUserService($usersRepository);
$conversationsRepository = new ConversationsRepository($database->connection());
$messagesRepository = new MessagesRepository($database->connection());

$usersHandler = new UsersHandler($usersRepository);
$conversationsHandler = new ConversationsHandler($conversationsRepository, $currentUser);
$messagesHandler = new MessagesHandler($messagesRepository, $conversationsRepository, $currentUser, $queue);
$searchHandler = new SearchHandler($search, $database->connection());
$wsTokenHandler = new WsTokenHandler(
    new WsTokenService((string) ($_ENV['APP_KEY'] ?? 'fallback-app-key'), (int) $settings['security']['ws_token_ttl'])
);
$llmHandler = new LlmHandler(new LlmService($settings['llm']), $messagesRepository, $currentUser, $queue);

$routes = [
    'health' => [
        'method' => 'GET',
        'path' => '/health',
        'handler' => new HealthHandler(),
        'requires_auth' => false,
        'requires_csrf' => false,
    ],
    'users.list' => [
        'method' => 'GET',
        'path' => '/v1/users',
        'handler' => [$usersHandler, 'list'],
        'requires_auth' => true,
        'requires_csrf' => false,
    ],
    'users.create' => [
        'method' => 'POST',
        'path' => '/v1/users',
        'handler' => [$usersHandler, 'create'],
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
    'users.show' => [
        'method' => 'GET',
        'path' => '/v1/users/{id}',
        'handler' => [$usersHandler, 'show'],
        'requires_auth' => true,
        'requires_csrf' => false,
    ],
    'users.update' => [
        'method' => 'PUT',
        'path' => '/v1/users/{id}',
        'handler' => [$usersHandler, 'update'],
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
    'users.delete' => [
        'method' => 'DELETE',
        'path' => '/v1/users/{id}',
        'handler' => [$usersHandler, 'delete'],
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
    'conversations.list' => [
        'method' => 'GET',
        'path' => '/v1/conversations',
        'handler' => [$conversationsHandler, 'list'],
        'requires_auth' => true,
        'requires_csrf' => false,
    ],
    'conversations.create' => [
        'method' => 'POST',
        'path' => '/v1/conversations',
        'handler' => [$conversationsHandler, 'create'],
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
    'conversations.delete' => [
        'method' => 'DELETE',
        'path' => '/v1/conversations/{id}',
        'handler' => [$conversationsHandler, 'delete'],
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
    'messages.list' => [
        'method' => 'GET',
        'path' => '/v1/conversations/{id}/messages',
        'handler' => [$messagesHandler, 'list'],
        'requires_auth' => true,
        'requires_csrf' => false,
    ],
    'messages.create' => [
        'method' => 'POST',
        'path' => '/v1/messages',
        'handler' => [$messagesHandler, 'create'],
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
    'search.messages' => [
        'method' => 'GET',
        'path' => '/v1/search/messages',
        'handler' => [$searchHandler, 'messages'],
        'requires_auth' => true,
        'requires_csrf' => false,
    ],
    'search.users' => [
        'method' => 'GET',
        'path' => '/v1/search/users',
        'handler' => [$searchHandler, 'users'],
        'requires_auth' => true,
        'requires_csrf' => false,
    ],
    'ws.token' => [
        'method' => 'POST',
        'path' => '/v1/ws/token',
        'handler' => $wsTokenHandler,
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
    'llm.reply' => [
        'method' => 'POST',
        'path' => '/v1/llm/reply',
        'handler' => $llmHandler,
        'requires_auth' => true,
        'requires_csrf' => true,
    ],
];

return new Kernel([
    new RequestIdMiddleware(),
    new JsonBodyMiddleware(),
    new SecurityHeadersMiddleware(),
    new AuthMiddleware($authVerifier),
    new CsrfMiddleware($settings['app']['csrf_cookie_name']),
    new RateLimitMiddleware($redis, (int) $settings['security']['rate_limit_per_minute']),
], $routes);
