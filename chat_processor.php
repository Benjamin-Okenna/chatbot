<?php
// chat_processor.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Headers: Content-Type');

// 1. LINK THE DATABASE CONNECTION FILE
require_once 'db_connect.php'; 

$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

$userMessage = isset($inputData['message']) ? trim($inputData['message']) : '';

if (empty($userMessage)) {
    echo json_encode(['status' => 'error', 'reply' => 'Input message cannot be blank.']);
    exit;
}

// 2. CHECK OR CREATE CONVERSATION ID
// To keep things simple before building a full login screen, we check if an active session exists
// or create a default ongoing conversation record.
$conversation_id = isset($inputData['conversation_id']) ? intval($inputData['conversation_id']) : 0;

if ($conversation_id === 0) {
    // Generate a temporary conversational log summary title from the first 30 characters of input
    $conversationTitle = substr($userMessage, 0, 30) . "...";
    
    // Securely insert new conversation session via PDO Prepared Statements
    $stmt = $pdo->prepare("INSERT INTO conversations (user_id, title) VALUES (?, ?)");
    $stmt->execute([1, $conversationTitle]);
    $conversation_id = $pdo->lastInsertId(); // Pull the generated ID out
}

// 3. SECURELY LOG USER QUESTION INTO DATABASE
$stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender, message_text) VALUES (?, ?, ?)");
$stmt->execute([$conversation_id, 'user', $userMessage]);


// ... Keep sections 1, 2, and 3 exactly the same at the top ...

// 4. CHATBOT CLOUD API GATEWAY ROUTING
$apiKey = "AIzaSyD0NGairJnL0olYLEFFgt9ahSTFzOScJC0"; // Make sure to paste your actual working API key string back here!
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$systemContext = "You are an automated academic advisor chatbot for our university named EspolyBot. You must only answer questions relating to academic calendars, course selection, exam regulations, and student portal assistance. Keep answers brief and supportive.";
$fullPrompt = $systemContext . "\n\nUser Question: " . $userMessage;

$payload = [
    "contents" => [["parts" => [["text" => $fullPrompt]]]]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['status' => 'error', 'reply' => 'Network Connection Error: ' . $curlError]);
    exit;
}

$responseData = json_decode($response, true);

// --- DIAGNOSTIC ERROR HANDLER ---
if (isset($responseData['error'])) {
    echo json_encode([
        'status' => 'error',
        'reply' => 'Google Cloud API Error: ' . $responseData['error']['message']
    ]);
    exit;
}

// 5. EXTRACT THE AI REPLY AND SAVE IT BEFORE REPLIES RETURN TO THE FRONTEND
if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiReply = $responseData['candidates'][0]['content']['parts'][0]['text'];
    
    // Securely log the Bot response into the database table
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender, message_text) VALUES (?, ?, ?)");
    $stmt->execute([$conversation_id, 'bot', $aiReply]);
    
    // Return both the answer AND the current conversation ID back to JavaScript
    echo json_encode([
        'status' => 'success', 
        'reply' => $aiReply,
        'conversation_id' => $conversation_id
    ]);
    exit;
} else {
    // If it fails structurally, print out a snippet of what Google sent back so we can see it
    echo json_encode([
        'status' => 'error', 
        'reply' => 'Structural mismatch. Raw response snippet: ' . substr($response, 0, 150)
    ]);
    exit;
}
?>