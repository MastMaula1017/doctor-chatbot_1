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

// Get user's medical history
$history_query = "SELECT * FROM medical_history WHERE user_id = ? ORDER BY diagnosis_date DESC";
$stmt = mysqli_prepare($conn, $history_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$medical_history = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Doctor Chatbot</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Doctor Chatbot</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                <a href="dashboard.php" class="btn-small">Dashboard</a>
                <a href="logout.php" class="btn-small">Logout</a>
            </div>
        </header>
        
        <div class="chat-container">
            <div class="sidebar">
                <h3>Your Medical History</h3>
                <div class="history-list">
                    <?php if (mysqli_num_rows($medical_history) > 0): ?>
                        <?php while ($history = mysqli_fetch_assoc($medical_history)): ?>
                            <div class="history-item">
                                <h4><?php echo htmlspecialchars($history['condition_name']); ?></h4>
                                <p>Diagnosed: <?php echo htmlspecialchars($history['diagnosis_date']); ?></p>
                                <p><?php echo htmlspecialchars($history['notes']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No medical history recorded yet.</p>
                    <?php endif; ?>
                </div>
                
                <div class="add-history">
                    <h3>Add Medical Condition</h3>
                    <form id="add-history-form">
                        <div class="form-group">
                            <label for="condition">Condition</label>
                            <input type="text" id="condition" name="condition" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="diagnosis_date">Diagnosis Date</label>
                            <input type="date" id="diagnosis_date" name="diagnosis_date">
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes"></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Add Condition</button>
                    </form>
                </div>
            </div>
            
            <div class="chat-box">
                <div class="chat-messages" id="chat-messages">
                    <div class="message bot">
                        <div class="message-content">
                            <p>Hello! I'm your health assistant. How can I help you today?</p>
                        </div>
                    </div>
                </div>
                
                <div class="chat-input">
                    <form id="chat-form">
                        <input type="text" id="user-message" placeholder="Type your health question here..." required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/chat.js"></script>
</body>
</html>