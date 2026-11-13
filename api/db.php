
<?php

function getDatabaseConnection(): PDO
{
    $config = require dirname(__DIR__, 2)
        . '/private_config/database.php';

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['dbname']
    );

    // Create the database connection.
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Set the database connection's time zone to UTC.
    $pdo->exec("SET time_zone = '+00:00'");

    // Return the configured connection.
    return $pdo;
}
