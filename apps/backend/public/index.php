<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

$kernel = require __DIR__ . '/../src/bootstrap.php';

$factory = new Psr17Factory();
$requestCreator = new ServerRequestCreator($factory, $factory, $factory, $factory);
$request = $requestCreator->fromGlobals();
$response = $kernel->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo (string) $response->getBody();
