<?php
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'statis' => 'ok',
    'message' => 'Gaming API is running',
    'timestamp' => gmdate('c')
]);