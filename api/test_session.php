
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit;
}

$token = getBearerToken();

if ($token === null) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing authentication token'
    ]);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    $session = validateSessionToken($pdo, $token);

    if ($session === null) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid or expired session'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'ok',
        'message' => 'Session valid',
        'user_id' => (int)$session['user_id'],
        'username' => $session['username'],
        'game_id' => (int)$session['game_id'],
        'expires_at' => $session['expires_at'] . ' UTC'
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error'
    ]);
}
