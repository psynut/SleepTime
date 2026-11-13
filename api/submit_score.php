
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

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}

// Retrieve the player's session token.
$token = getBearerToken();

if ($token === null) {
    respond(401, [
        'status' => 'error',
        'message' => 'Missing authentication token'
    ]);
}

// Read the JSON request body.
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, [
        'status' => 'error',
        'message' => 'Invalid JSON request'
    ]);
}

// A score must be an integer.
if (
    !array_key_exists('score', $input) ||
    !is_int($input['score'])
) {
    respond(400, [
        'status' => 'error',
        'message' => 'Score must be an integer'
    ]);
}

$score = $input['score'];

// For now, scores must be zero or greater.
if ($score < 0) {
    respond(400, [
        'status' => 'error',
        'message' => 'Score cannot be negative'
    ]);
}

// Optional game version.
$gameVersion = $input['game_version'] ?? null;

if (
    $gameVersion !== null &&
    (!is_string($gameVersion) || strlen($gameVersion) > 20)
) {
    respond(400, [
        'status' => 'error',
        'message' => 'Invalid game version'
    ]);
}

try {
    $pdo = getDatabaseConnection();

    // Verify the session and identify the player/game.
    $session = validateSessionToken($pdo, $token);

    if ($session === null) {
        respond(401, [
            'status' => 'error',
            'message' => 'Invalid or expired session'
        ]);
    }

    $userId = (int)$session['user_id'];
    $gameId = (int)$session['game_id'];

    // Save the score.
    $stmt = $pdo->prepare(
        'INSERT INTO scores
            (user_id, game_id, score, game_version)
         VALUES
            (:user_id, :game_id, :score, :game_version)'
    );

    $stmt->execute([
        'user_id' => $userId,
        'game_id' => $gameId,
        'score' => $score,
        'game_version' => $gameVersion
    ]);

    $scoreId = (int)$pdo->lastInsertId();

    respond(201, [
        'status' => 'ok',
        'message' => 'Score submitted',
        'score_id' => $scoreId,
        'user_id' => $userId,
        'game_id' => $gameId,
        'score' => $score,
        'game_version' => $gameVersion
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    respond(500, [
        'status' => 'error',
        'message' => 'Server error'
    ]);
}
