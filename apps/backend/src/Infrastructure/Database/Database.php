<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

final class Database
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @param array<string, mixed> $settings */
    public static function fromSettings(array $settings): self
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => $settings['host'],
            'port' => $settings['port'],
            'dbname' => $settings['name'],
            'user' => $settings['user'],
            'password' => $settings['password'],
            'charset' => 'utf8mb4',
        ]);

        return new self($connection);
    }

    public function connection(): Connection
    {
        return $this->connection;
    }
}
