<?php
// get_messages.php
header('Content-Type: application/json; charset=utf-8');

// Disable raw HTML error dumps from breaking the JavaScript JSON parser
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'db_connect.php';

// TYPO FIXED: Cleanly grab the conversation_id parameter out of the URL query string
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

if ($conversation_id === 0) {
    echo json_encode([
        'status' => 'error', 
        'reply' => 'Missing target conversation transaction token.'
    ]);
    exit;
}

try {
    // Select all chat log strings belonging to this session ordered from oldest to newest
    $stmt = $pdo->prepare("SELECT sender, message_text, created_at FROM messages WHERE conversation_id = ? ORDER BY id ASC");
    $stmt->execute([$conversation_id]);
    
    // FORCE ASSOCIATIVE ARRAY: Ensures JSON keys exactly match what script.js expects
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'messages' => $messages
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'reply' => 'Database failure loading history log contents.',
        'debug' => $e->getMessage() // Remove or comment this out before deploying to production
    ]);
}
?>