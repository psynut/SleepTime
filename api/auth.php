
<?php

require_once __DIR__ . '/db.php';

/**
 * Validates a game's API key.
 *
 * Returns the game_id if valid, or null otherwise.
 */
function validateGameApiKey(
    PDO $pdo,
    string $apiKey
): ?int {

    $keyHash = hash('sha256', $apiKey);

    $stmt = $pdo->prepare(
        'SELECT g.game_id
         FROM game_api_keys AS k
         JOIN games AS g ON g.game_id = k.game_id
         WHERE k.key_hash = :key_hash
           AND k.revoked_at IS NULL
           AND (k.expires_at IS NULL
                OR k.expires_at > CURRENT_TIMESTAMP)
           AND g.is_active = 1
         LIMIT 1'
    );

    $stmt->execute([
        'key_hash' => $keyHash
    ]);

    $gameId = $stmt->fetchColumn();

    return $gameId === false ? null : (int)$gameId;
}
