<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\MiddlewareInterface;
use App\Support\Json;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function FastRoute\simpleDispatcher;

final class Kernel
{
    private Dispatcher $dispatcher;

    /** @var array<string, array{handler: callable, requires_auth: bool, requires_csrf: bool}> */
    private array $routesByName = [];

    /**
     * @param list<MiddlewareInterface> $middlewares
     * @param array<string, array{method: string, path: string, handler: callable, requires_auth: bool, requires_csrf: bool}> $routes
     */
    public function __construct(private readonly array $middlewares, array $routes)
    {
        $this->routesByName = $routes;

        $this->dispatcher = simpleDispatcher(function (RouteCollector $collector) use ($routes): void {
            foreach ($routes as $name => $route) {
                $collector->addRoute($route['method'], $route['path'], $name);
            }
        });
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = rawurldecode($request->getUri()->getPath());
        $dispatch = $this->dispatcher->dispatch($request->getMethod(), $path);

        if ($dispatch[0] === Dispatcher::NOT_FOUND) {
            return Json::response(['error' => 'Route not found.'], 404);
        }

        if ($dispatch[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return Json::response(['error' => 'Method not allowed.'], 405);
        }

        $routeName = (string) $dispatch[1];
        $params = is_array($dispatch[2]) ? $dispatch[2] : [];
        $routeMeta = $this->routesByName[$routeName];

        $request = $request->withAttribute('route_meta', $routeMeta);

        $runner = array_reduce(
            array_reverse($this->middlewares),
            static fn (callable $next, MiddlewareInterface $middleware): callable => static fn (ServerRequestInterface $r): ResponseInterface => $middleware->process($r, $next),
            function (ServerRequestInterface $r) use ($routeMeta, $params): ResponseInterface {
                $handler = $routeMeta['handler'];

                return $handler($r, $params);
            }
        );

        return $runner($request);
    }
}
