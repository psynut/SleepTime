
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/auth.php';

function respond(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, [
        'status' => 'error',
        'message' => 'Invalid JSON request'
    ]);
}

foreach (['api_key', 'username', 'password'] as $field) {
    if (!isset($input[$field]) || !is_string($input[$field])) {
        respond(400, [
            'status' => 'error',
            'message' => "Missing or invalid field: $field"
        ]);
    }
}

$apiKey = $input['api_key'];
$username = trim($input['username']);
$password = $input['password'];

try {
    $pdo = getDatabaseConnection();

    // Identify the game.
    $gameId = validateGameApiKey($pdo, $apiKey);

    if ($gameId === null) {
        respond(403, [
            'status' => 'error',
            'message' => 'Invalid game API key'
        ]);
    }

    // Find the player.
    $stmt = $pdo->prepare(
        'SELECT user_id, username, password_hash, is_active
         FROM users
         WHERE username = :username
         LIMIT 1'
    );

    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Don't reveal whether the username exists.
    if (
        !$user ||
        !$user['is_active'] ||
        !password_verify($password, $user['password_hash'])
    ) {
        respond(401, [
            'status' => 'error',
            'message' => 'Invalid username or password'
        ]);
    }

    // Generate a random session token.
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
        ->modify('+30 days')
        ->format('Y-m-d H:i:s');

    // Keep the session and login event consistent.
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO sessions
            (user_id, game_id, token_hash, expires_at)
         VALUES
            (:user_id, :game_id, :token_hash, :expires_at)'
    );

    $stmt->execute([
        'user_id' => $user['user_id'],
        'game_id' => $gameId,
        'token_hash' => $tokenHash,
        'expires_at' => $expiresAt
    ]);

    $sessionId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO login_events
            (user_id, game_id, session_id)
         VALUES
            (:user_id, :game_id, :session_id)'
    );

    $stmt->execute([
        'user_id' => $user['user_id'],
        'game_id' => $gameId,
        'session_id' => $sessionId
    ]);

    $stmt = $pdo->prepare(
        'UPDATE users
         SET last_login_at = CURRENT_TIMESTAMP
         WHERE user_id = :user_id'
    );

    $stmt->execute(['user_id' => $user['user_id']]);

    $pdo->commit();

    respond(200, [
        'status' => 'ok',
        'message' => 'Login successful',
        'user_id' => (int)$user['user_id'],
        'username' => $user['username'],
        'game_id' => $gameId,
        'session_token' => $token,
        'expires_at' => $expiresAt . ' UTC'
    ]);

} catch (Throwable $e) {

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    respond(500, [
        'status' => 'error',
        'message' => 'Server error'
    ]);
}
