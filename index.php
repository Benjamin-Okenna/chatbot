<?php
// index.php
session_start();
require_once 'db_connect.php';

// Security Gate: Kick unauthenticated traffic out straight to the login form
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.php');
    exit;
}

// Fallback profile avatar image if the user doesn't have a biometric photo stored
$profile_photo = 'images/default-avatar.png'; 

if (!empty($_SESSION['face_token'])) {
    $profile_photo = $_SESSION['face_token']; 
}

// Track what the current session_id is from the URL string
$current_session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Espoly AI Chatbot</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="generalBox">
<aside class="sidebar">
    
    <!-- 1. Logo/Title -->
    <header class="sidebar-header">
        <h2>EspolyBot AI</h2>
    </header>

    <!-- 2. Action Button -->
    <div class="action-wrapper">
        <button type="button" class="new-chat-btn" id="new-chat-btn">+ New Chat</button>
    </div>

    <!-- 3. Dynamic History Container -->
    <nav class="history-container">
        <p class="section-title">Recent Chats</p>
        <ul class="history-list" id="history-list">
            <?php
            try {
                // Query your database for the 5 most recent conversations matching this student's ID
                $history_stmt = $pdo->prepare("
                    SELECT id, title 
                    FROM chat_sessions 
                    WHERE student_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $history_stmt->execute([$_SESSION['student_id']]);
                $chat_sessions = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($chat_sessions)) {
                    // Clean text fallback if they haven't sent a message yet
                    echo '<li style="font-size: 12px; color: #64748b; padding: 10px; font-style: italic;">No recent chats.</li>';
                } else {
                    foreach ($chat_sessions as $chat) {
                        $chat_title = htmlspecialchars($chat['title']);
                        $session_id = intval($chat['id']);
                        
                        // Check if this item is the one the student is currently viewing
                        $is_active = ($session_id === $current_session_id) ? ' active' : '';
                        
                        // We wrap the item in an anchor tag so clicking it loads that specific chat history
                        echo '<li class="history-item' . $is_active . '" onclick="location.href=\'index.php?session_id=' . $session_id . '\'">';
                        echo $chat_title;
                        echo '</li>';
                    }
                }
            } catch (PDOException $e) {
                echo '<li style="font-size: 11px; color: #ef4444; padding: 10px;">Failed to load history</li>';
            }
            ?>
        </ul>
    </nav>

   
    <div class="user-profile-card">
    <!-- Displays the webcam capture base64 string or the default asset avatar -->
    <img src="<?php echo $profile_photo; ?>" alt="Student Profile Photo" class="profile-avatar">
    
    <div class="user-info">
        <!-- Dynamically renders the authenticated student's full name -->
        <span class="user-name"><?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
        <!-- Displays the student's active matric number underneath -->
        <span class="user-matric"><?php echo htmlspecialchars($_SESSION['matric_no']); ?></span>
        
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
</div>

</aside>

<main class="chat-area">
    
    <!-- A. WELCOME SCREEN -->
    <div class="welcome-screen" id="welcome-screen">
        <div class="welcome-header">
            <div class="uni-logo-placeholder">🏫</div>
            <h1>Welcome to EspolyBot Portal</h1>
            <p>Your instant AI assistant for academic queries, registration rules, and deadlines.</p>
        </div>
        
        <div class="suggestions-grid">
            <button type="button" class="suggestion-card" onclick="setPresetQuery('How do I register for exams?')">
                <h5>Exam Registration</h5>
                <p>Learn step-by-step how to enroll for the semester assessments.</p>
            </button>
            <button type="button" class="suggestion-card" onclick="setPresetQuery('Check course prerequisites')">
                <h5>Prerequisites</h5>
                <p>Find out what courses you need to pass before taking advanced choices.</p>
            </button>
            <button type="button" class="suggestion-card" onclick="setPresetQuery('What is the deadline for fee payment?')">
                <h5>Deadlines</h5>
                <p>Check the active calendar for tuition payments and portal closures.</p>
            </button>
        </div>
    </div>

    <!-- B. THE MESSAGE FEED WINDOW -->
    <div class="message-window" id="chat-box">
        <div class="chat-divider">Today</div>

        <!-- Bot Message Layout -->
        <div class="message-wrapper bot">
            <div class="message-bubble">
                Hello! I am your AI Academic Advisor. Feel free to ask me anything about your portal configuration or registration tracks.
            </div>
            <span class="message-time">14:02</span>
        </div>

        <!-- User Message Layout -->
        <div class="message-wrapper user">
            <div class="message-bubble">
                How do I check my exam timetable?
            </div>
            <span class="message-time">14:03</span>
        </div>
    </div>

    <!-- C. INPUT DOCK CONTAINER -->
    <div class="input-container">
        <div class="input-box-wrapper">
            <input type="text" id="user-input" placeholder="Ask EspolyBot something..." autocomplete="off">
            <button type="button" id="send-btn" onclick="sendMessage()">Send</button>
        </div>
        <span class="disclaimer">EspolyBot may provide inaccurate data. Verify critical dates.</span>
    </div>

</main>
</div>

  <script src="script.js"></script>
  
</body>
</html>