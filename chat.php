<?php
session_start();
require_once 'db.php';
require_once 'ai_data.php';

// Prevent access without login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle AJAX Chat Request
if (isset($_POST['ajax_chat'])) {
    $user_msg = $_POST['message'];
    
    // Get AI response
    $ai_reply = get_ai_response($user_msg);
    
    // Store in database
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, response) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $user_msg, $ai_reply]);
    
    // Return JSON
    echo json_encode([
        'status' => 'success',
        'response' => $ai_reply
    ]);
    exit;
}

// Fetch chat history
$stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$chat_history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeonCloud AI - Chat</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="bg-animation">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>History</h2>
            </div>
            <div class="chat-history" id="sidebarHistory">
                <?php foreach ($chat_history as $msg): ?>
                    <div class="history-item">
                        <p><?php echo htmlspecialchars(substr($msg['message'], 0, 40)) . '...'; ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($chat_history)): ?>
                    <p style="color: var(--text-dim); text-align: center; margin-top: 20px;">No messages yet.</p>
                <?php endif; ?>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Chat -->
        <main class="chat-main">
            <header class="chat-header">
                <div class="user-info">
                    <span style="color: var(--text-dim);">Welcome back,</span>
                    <strong style="color: var(--neon-blue);"> <?php echo htmlspecialchars($username); ?></strong>
                </div>
                <div class="status-indicator">
                    <span style="display: inline-block; width: 10px; height: 10px; background: #00ff00; border-radius: 50%; box-shadow: 0 0 10px #00ff00;"></span>
                    <span style="margin-left: 8px; font-size: 0.9rem;">AI System Online</span>
                </div>
            </header>

            <div class="chat-messages" id="chatWindow">
                <div class="message-bubble message-ai">
                    Hello! I am your <strong>NeonCloud Freelance Assistant</strong>. Ask me anything about Fiverr, Upwork, Digital Skills, or Productivity. How can I help you today?
                </div>

                <?php foreach ($chat_history as $msg): ?>
                    <div class="message-bubble message-user">
                        <?php echo htmlspecialchars($msg['message']); ?>
                    </div>
                    <div class="message-bubble message-ai">
                        <?php 
                            // AI responses are prepared in Markdown-like format, we'll convert some basic parts
                            $re = $msg['response'];
                            $re = preg_replace('/### (.*)/', '<h3>$1</h3>', $re);
                            $re = preg_replace('/\*\*(.*)\*\*/', '<strong>$1</strong>', $re);
                            $re = preg_replace('/^- (.*)/m', '<li>$1</li>', $re);
                            $re = str_replace("\n", "<br>", $re);
                            echo $re;
                        ?>
                    </div>
                <?php endforeach; ?>

                <!-- Typing indicator -->
                <div class="typing-indicator" id="typingIndicator">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            </div>

            <div class="chat-input-area">
                <form id="chatForm" class="input-wrapper">
                    <input type="text" id="userMessage" placeholder="Type your message here..." autocomplete="off">
                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane" style="color: black;"></i>
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>
