
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

// Only accept GET requests.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, [
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}

// Get the API key from the request header.
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($apiKey === '') {
    respond(401, [
        'status' => 'error',
        'message' => 'Missing game API key'
    ]);
}

// Determine which leaderboard is requested.
$type = $_GET['type'] ?? 'players';

if (!in_array($type, ['players', 'all'], true)) {
    respond(400, [
        'status' => 'error',
        'message' => 'Invalid leaderboard type'
    ]);
}

try {
    $pdo = getDatabaseConnection();

    // Identify the game using its API key.
    $gameId = validateGameApiKey($pdo, $apiKey);

    if ($gameId === null) {
        respond(401, [
            'status' => 'error',
            'message' => 'Invalid game API key'
        ]);
    }

    if ($type === 'players') {

        // Each player's personal best, ranked highest first.
        $sql = '
            SELECT
                u.username,
                MAX(s.score) AS score
            FROM scores AS s
            JOIN users AS u ON u.user_id = s.user_id
            WHERE s.game_id = :game_id
              AND u.is_active = 1
            GROUP BY u.user_id, u.username
            ORDER BY score DESC, u.username ASC
            LIMIT 10
        ';

    } else {

        // Top 10 individual scores, including repeat players.
        $sql = '
            SELECT
                u.username,
                s.score,
                s.achieved_at
            FROM scores AS s
            JOIN users AS u ON u.user_id = s.user_id
            WHERE s.game_id = :game_id
              AND u.is_active = 1
            ORDER BY s.score DESC, s.achieved_at ASC, s.score_id ASC
            LIMIT 10
        ';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['game_id' => $gameId]);

    $scores = $stmt->fetchAll();

    // Add ranking positions and normalize numeric values.
    $leaderboard = [];

    foreach ($scores as $index => $row) {
        $entry = [
            'rank' => $index + 1,
            'username' => $row['username'],
            'score' => (int)$row['score']
        ];

        if (isset($row['achieved_at'])) {
            $entry['achieved_at'] = $row['achieved_at'] . ' UTC';
        }

        $leaderboard[] = $entry;
    }

    respond(200, [
        'status' => 'ok',
        'game_id' => $gameId,
        'type' => $type,
        'count' => count($leaderboard),
        'leaderboard' => $leaderboard
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    respond(500, [
        'status' => 'error',
        'message' => 'Server error'
    ]);
}
