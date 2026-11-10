
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/auth.php';

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Read the JSON request body.
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON request'
    ]);
    exit;
}

// Validate field types before using them.
foreach (['api_key', 'username', 'password'] as $field) {
    if (!isset($input[$field]) || !is_string($input[$field])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => "Missing or invalid field: $field"
        ]);
        exit;
    }
}

$apiKey = $input['api_key'];
$username = trim($input['username']);
$password = $input['password'];
$email = $input['email'] ?? null;

// Validate username: 3-50 characters.
if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Username must be 3-50 characters (letters, numbers, underscores)'
    ]);
    exit;
}

// Validate password length.
if (strlen($password) < 12 || strlen($password) > 1024) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Password must be at least 12 characters'
    ]);
    exit;
}

// Email is optional.
if ($email !== null) {
    if (!is_string($email)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email address'
        ]);
        exit;
    }

    $email = trim($email);

    if ($email === '') {
        $email = null;
    } elseif (
        strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email address'
        ]);
        exit;
    }
}

try {
    $pdo = getDatabaseConnection();

    // Verify which game is making the request.
    $gameId = validateGameApiKey($pdo, $apiKey);

    if ($gameId === null) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid game API key'
        ]);
        exit;
    }

    // Hash password using Argon2id.
    $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

    if ($passwordHash === false) {
        throw new RuntimeException('Password hashing failed');
    }

    // Insert the user.
    $stmt = $pdo->prepare(
        'INSERT INTO users
            (username, password_hash, email)
         VALUES
            (:username, :password_hash, :email)'
    );

    $stmt->execute([
        'username' => $username,
        'password_hash' => $passwordHash,
        'email' => $email
    ]);

    http_response_code(201);

    echo json_encode([
        'status' => 'ok',
        'message' => 'Account created',
        'user_id' => (int)$pdo->lastInsertId(),
        'game_id' => $gameId
    ]);

} catch (PDOException $e) {

    // MySQL/MariaDB error 1062: duplicate unique value.
    if (($e->errorInfo[1] ?? null) == 1062) {
        http_response_code(409);
        echo json_encode([
            'status' => 'error',
            'message' => 'Username or email already registered'
        ]);
    } else {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Server error'
        ]);
    }

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error'
    ]);
}
