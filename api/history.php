<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST request to add new medical history
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $condition = $data['condition'] ?? '';
    $diagnosis_date = $data['diagnosis_date'] ?? null;
    $notes = $data['notes'] ?? '';
    
    if (empty($condition)) {
        echo json_encode(['success' => false, 'message' => 'Condition name is required']);
        exit();
    }
    
    // Insert new medical history
    $insert_query = "INSERT INTO medical_history (user_id, condition_name, diagnosis_date, notes) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $condition, $diagnosis_date, $notes);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Medical history added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add medical history']);
    }
    exit();
}

// Handle GET request to retrieve medical history
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM medical_history WHERE user_id = ? ORDER BY diagnosis_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
    }
    
    echo json_encode(['success' => true, 'history' => $history]);
    exit();
}

// If not POST or GET, return error
echo json_encode(['success' => false, 'message' => 'Invalid request method']);