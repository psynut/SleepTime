
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    $pdo = getDatabaseConnection();

    $stmt = $pdo->query('SELECT 1');
    $stmt->fetch();

    echo json_encode([
        'status' => 'ok',
        'message' => 'Database connection successful'
    ]);

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
}
