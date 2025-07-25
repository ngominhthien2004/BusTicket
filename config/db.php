<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Ct27501Project\Models\PDOFactory;
use Dotenv\Dotenv as Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $config = [
        'dbhost' => $_ENV['DB_HOST'],
        'dbname' => $_ENV['DB_NAME'],
        'dbuser' => $_ENV['DB_USER'],
        'dbpass' => $_ENV['DB_PASS'],
    ];
    $pdoFactory = new PDOFactory();
    $PDO = $pdoFactory->create($config);
} catch (PDOException $ex) {
    echo "Không thể kết nối đến PostgreSQL,
    Vui lòng kiểm tra lại các thông tin kết nối";
    exit("<pre>{$ex}</pre>");
}
