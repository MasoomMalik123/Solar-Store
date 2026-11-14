<?php

// Load local .env into array if available
$env = [];
$local_env_file = __DIR__ . '/../.env';
if (is_file($local_env_file) && is_readable($local_env_file)) {
    foreach (file($local_env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $env_line) {
        $env_line = trim($env_line);
        if ($env_line === '' || str_starts_with($env_line, '#') || !str_contains($env_line, '=')) continue;
        [$env_name, $env_value] = explode('=', $env_line, 2);
        $env[trim($env_name)] = trim($env_value, " \t\"'");
    }
}

// Credentials: .env first, fallback to InfinityFree production DB
$db_host = $env['SHOP_DB_HOST'] ?? 'sql105.infinityfree.com';
$db_port = (int)($env['SHOP_DB_PORT'] ?? 3306);
$db_name = $env['SHOP_DB_NAME'] ?? 'if0_43370438_shop1_db_solar';
$db_user = $env['SHOP_DB_USER'] ?? 'if0_43370438';
$db_pass = $env['SHOP_DB_PASS'] ?? 'Abbas6434';

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db_host, $db_port, $db_name);

try {
    $conn = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
<!-- building connection using PDO (replacement of mysqli) -->
