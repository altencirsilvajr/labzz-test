<?php

declare(strict_types=1);

use App\Config\Settings;
use App\Infrastructure\Database\Database;

require_once __DIR__ . '/../vendor/autoload.php';

$basePath = dirname(__DIR__);
$settings = Settings::load($basePath);
$db = Database::fromSettings($settings['database'])->connection();

$sql = file_get_contents($basePath . '/src/Infrastructure/Database/Migrations/20260312190000_initial.sql');
if ($sql === false) {
    throw new RuntimeException('Migration SQL file not found.');
}

$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $statement) {
    if ($statement === '') {
        continue;
    }

    $db->executeStatement($statement);
}

echo "Migrations applied successfully.\n";
