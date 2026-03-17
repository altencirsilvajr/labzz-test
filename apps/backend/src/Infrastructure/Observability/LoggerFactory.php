<?php

declare(strict_types=1);

namespace App\Infrastructure\Observability;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class LoggerFactory
{
    public static function create(string $name = 'app'): Logger
    {
        $logger = new Logger($name);
        $handler = new StreamHandler('php://stdout');
        $handler->setFormatter(new JsonFormatter());
        $logger->pushHandler($handler);

        return $logger;
    }
}
