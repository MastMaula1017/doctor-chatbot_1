<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

if (empty($message)) {
    echo json_encode(['error' => 'No message provided']);
    exit();
}

// Get user's medical history for context
$history_query = "SELECT condition_name, diagnosis_date, notes FROM medical_history WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $history_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$medical_history = [];
while ($row = mysqli_fetch_assoc($result)) {
    $medical_history[] = $row;
}

// Prepare context for AI
$context = "User's medical history: ";
if (count($medical_history) > 0) {
    foreach ($medical_history as $history) {
        $context .= "Condition: " . $history['condition_name'] . ", ";
        $context .= "Diagnosed: " . $history['diagnosis_date'] . ", ";
        $context .= "Notes: " . $history['notes'] . "; ";
    }
} else {
    $context .= "No medical history available.";
}

// Simple rule-based responses (in a real app, you'd use a proper AI model)
$response = getAIResponse($message, $context);

// Save chat history
$insert_query = "INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $message, $response);
mysqli_stmt_execute($stmt);

echo json_encode(['response' => $response]);

// Simple rule-based AI function (replace with actual AI integration)
function getAIResponse($message, $context) {
    $message = strtolower($message);
    
    // Check for common health questions
    if (strpos($message, 'headache') !== false) {
        return "Based on your message about headaches, I recommend staying hydrated and getting enough rest. If headaches persist for more than a few days or are severe, please consult a doctor.";
    } elseif (strpos($message, 'fever') !== false) {
        return "Fever can be a sign of infection. Rest, stay hydrated, and take over-the-counter fever reducers if needed. If fever is high (above 103°F/39.4°C) or lasts more than 3 days, seek medical attention.";
    } elseif (strpos($message, 'cold') !== false || strpos($message, 'flu') !== false) {
        return "For cold and flu symptoms, rest, stay hydrated, and consider over-the-counter medications for symptom relief. If symptoms worsen or don't improve after a week, consult a healthcare provider.";
    } elseif (strpos($message, 'diet') !== false || strpos($message, 'nutrition') !== false) {
        return "A balanced diet rich in fruits, vegetables, whole grains, and lean proteins is essential for good health. Based on your medical history, I recommend consulting a nutritionist for personalized advice.";
    } elseif (strpos($message, 'exercise') !== false || strpos($message, 'workout') !== false) {
        return "Regular exercise is important for overall health. Aim for at least 150 minutes of moderate activity per week. Start slowly and gradually increase intensity, especially if you have existing health conditions.";
    } elseif (strpos($message, 'sleep') !== false) {
        return "Good sleep is crucial for health. Aim for 7-9 hours per night. Establish a regular sleep schedule, create a relaxing bedtime routine, and avoid screens before bed.";
    } elseif (strpos($message, 'stress') !== false || strpos($message, 'anxiety') !== false) {
        return "Stress management is important for both mental and physical health. Consider techniques like deep breathing, meditation, physical activity, or talking to a mental health professional.";
    } else {
        return "Thank you for your question. Based on the information you've provided, I recommend maintaining a healthy lifestyle with regular exercise, balanced diet, and adequate rest. If you have specific health concerns, please consult with a healthcare professional for personalized advice.";
    }
}