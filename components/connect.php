<?php

// Load a local .env for XAMPP development when present. Production should use
// real process environment variables from the web server configuration.
$local_env_file = __DIR__ . '/../.env';
if (is_file($local_env_file) && is_readable($local_env_file)) {
    foreach (file($local_env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $env_line) {
        $env_line = trim($env_line);
        if ($env_line === '' || str_starts_with($env_line, '#') || !str_contains($env_line, '=')) continue;
        [$env_name, $env_value] = explode('=', $env_line, 2);
        $env_name = trim($env_name);
        $env_value = trim($env_value);
        if ($env_name !== '' && getenv($env_name) === false) putenv($env_name . '=' . trim($env_value, " \t\"'"));
    }
}

$app_env = strtolower((string)(getenv('APP_ENV') ?: 'development'));
$show_errors = $app_env !== 'production';
$db_host = getenv('SHOP_DB_HOST') ?: 'localhost';
$db_port = (int)(getenv('SHOP_DB_PORT') ?: 3306);
$db_name = getenv('SHOP_DB_NAME') ?: 'shop_db';
$db_user = getenv('SHOP_DB_USER') ?: 'root';
$db_pass = getenv('SHOP_DB_PASS');
$db_pass = $db_pass === false ? '' : $db_pass;
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db_host, $db_port, $db_name);

ini_set('display_errors', $show_errors ? '1' : '0');
ini_set('display_startup_errors', $show_errors ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function pdo_connect_with_retry($dsn, $user, $pass, $opts = [], $retries = 2)
{
	$default = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
		// small timeout for connect attempts (seconds)
		PDO::ATTR_TIMEOUT => 5,
	];
	$options = $default + $opts;

	for ($i = 0; $i <= $retries; $i++) {
		try {
			$pdo = new PDO($dsn, $user, $pass, $options);
			// quick ping to ensure connection is alive
			$pdo->query('SELECT 1');
			return $pdo;
		} catch (PDOException $e) {
			error_log("DB connect attempt {$i} failed: " . $e->getMessage());
			// if it's the last attempt, rethrow so caller can decide
			if ($i === $retries) {
				throw $e;
			}
			// small sleep before retrying
			usleep(200000); // 200ms
		}
	}
}

try {
	$conn = pdo_connect_with_retry($dsn, $db_user, $db_pass, [
		// ensure commands use utf8mb4 on connect
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
	], 2);
} catch (PDOException $e) {
	error_log('DB connection error: ' . $e->getMessage());
	if ($show_errors) {
		$connection_message = 'Database connection failed: ' . $e->getMessage();
	} else {
		$connection_message = 'Database connection unavailable. Check the server configuration.';
	}
	die(htmlspecialchars($connection_message, ENT_QUOTES, 'UTF-8'));
}

?>
<!-- building connection using PDO (replacement of mysqli) -->
