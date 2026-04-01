<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$user_id = intval(currentUserId() ?? 0);
$syllabus_id = isset($_POST['syllabus_id']) ? intval($_POST['syllabus_id']) : 0;

if ($syllabus_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid topic ID']);
    exit();
}

// Verify that the summary belongs to the current user
$stmt = $conn->prepare("SELECT id FROM topic_summaries WHERE syllabus_id = ? AND user_id = ?");
$stmt->bind_param("ii", $syllabus_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Summary not found or unauthorized']);
    exit();
}

// Delete the summary
$stmt = $conn->prepare("DELETE FROM topic_summaries WHERE syllabus_id = ? AND user_id = ?");
$stmt->bind_param("ii", $syllabus_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Summary deleted successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete summary']);
}
