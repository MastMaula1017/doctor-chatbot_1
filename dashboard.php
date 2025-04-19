<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's medical history count
$history_query = "SELECT COUNT(*) as count FROM medical_history WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $history_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$history_result = mysqli_stmt_get_result($stmt);
$history_count = mysqli_fetch_assoc($history_result)['count'];

// Get user's chat history count
$chat_query = "SELECT COUNT(*) as count FROM chat_history WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $chat_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$chat_result = mysqli_stmt_get_result($stmt);
$chat_count = mysqli_fetch_assoc($chat_result)['count'];

// Get user's recent chat
$recent_chat_query = "SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $recent_chat_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$recent_chats = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Doctor Chatbot</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Doctor Chatbot Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                <a href="chat.php" class="btn-small">Chat</a>
                <a href="logout.php" class="btn-small">Logout</a>
            </div>
        </header>
        
        <div class="dashboard-container">
            <div class="dashboard-card">
                <h2>Your Health Summary</h2>
                <p>Medical conditions recorded: <?php echo $history_count; ?></p>
                <p>Chat interactions: <?php echo $chat_count; ?></p>
                <a href="chat.php" class="btn">Start New Chat</a>
            </div>
            
            <div class="dashboard-card">
                <h2>Health Tips</h2>
                <p>• Stay hydrated by drinking at least 8 glasses of water daily</p>
                <p>• Aim for 7-9 hours of sleep each night</p>
                <p>• Include fruits and vegetables in every meal</p>
                <p>• Exercise for at least 30 minutes, 5 days a week</p>
                <p>• Practice mindfulness or meditation to reduce stress</p>
            </div>
            
            <div class="dashboard-card">
                <h2>Recent Conversations</h2>
                <?php if (mysqli_num_rows($recent_chats) > 0): ?>
                    <?php while ($chat = mysqli_fetch_assoc($recent_chats)): ?>
                        <div class="chat-preview">
                            <p><strong>You:</strong> <?php echo htmlspecialchars(substr($chat['message'], 0, 50)) . (strlen($chat['message']) > 50 ? '...' : ''); ?></p>
                            <p><strong>Bot:</strong> <?php echo htmlspecialchars(substr($chat['response'], 0, 50)) . (strlen($chat['response']) > 50 ? '...' : ''); ?></p>
                            <p class="chat-time">On: <?php echo date('M d, Y H:i', strtotime($chat['created_at'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No conversations yet. Start chatting with the bot!</p>
                <?php endif; ?>
                <a href="chat.php" class="btn">View All Conversations</a>
            </div>
        </div>
    </div>
</body>
</html>