
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


/**
 * Validates a player's session token.
 *
 * Returns the authenticated user and game information,
 * or null if the token is invalid, expired, or revoked.
 */
function validateSessionToken(PDO $pdo, string $token): ?array
{
    // Session tokens are 64-character hexadecimal strings.
    if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
        return null;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare(
        'SELECT
            s.session_id,
            s.user_id,
            s.game_id,
            u.username,
            s.expires_at
         FROM sessions AS s
         JOIN users AS u ON u.user_id = s.user_id
         JOIN games AS g ON g.game_id = s.game_id
         WHERE s.token_hash = :token_hash
           AND s.revoked_at IS NULL
           AND s.expires_at > UTC_TIMESTAMP()
           AND u.is_active = 1
           AND g.is_active = 1
         LIMIT 1'
    );

    $stmt->execute([
        'token_hash' => $tokenHash
    ]);

    $session = $stmt->fetch();

    return $session === false ? null : $session;
}

/**
 * Extracts a Bearer token from the Authorization header.
 */
function getBearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if (!preg_match('/^Bearer\s+(\S+)$/i', trim($header), $matches)) {
        return null;
    }

    return $matches[1];
}

