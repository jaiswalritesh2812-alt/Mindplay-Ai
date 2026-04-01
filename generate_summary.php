<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Get POST parameters
$syllabus_id = isset($_POST['syllabus_id']) ? intval($_POST['syllabus_id']) : 0;
$user_id = intval(currentUserId() ?? 0);

if($syllabus_id <= 0 || $user_id <= 0){
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

// Get syllabus content
$stmt = $conn->prepare("SELECT sy.*, s.subject_name 
                        FROM syllabus sy 
                        JOIN subjects s ON sy.subject_id = s.id 
                        WHERE sy.id = ?");
$stmt->bind_param("i", $syllabus_id);
$stmt->execute();
$syllabus = $stmt->get_result()->fetch_assoc();

if(!$syllabus){
    echo json_encode(['success' => false, 'error' => 'Topic not found']);
    exit();
}

// Check if API key is configured
if(!defined('OPENROUTER_API_KEY') || OPENROUTER_API_KEY === 'sk-or-v1-YOUR_API_KEY_HERE'){
    echo json_encode(['success' => false, 'error' => 'AI service not configured']);
    exit();
}

// Check if summary already exists
$stmt = $conn->prepare("SELECT summary, generated_at FROM topic_summaries WHERE syllabus_id = ? AND user_id = ?");
$stmt->bind_param("ii", $syllabus_id, $user_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

// If summary exists and is less than 7 days old, return it
if($existing && (time() - strtotime($existing['generated_at'])) < 604800){
    echo json_encode([
        'success' => true, 
        'summary' => $existing['summary'],
        'cached' => true
    ]);
    exit();
}

// Generate summary using AI
$content = $syllabus['content'];
$topic_name = $syllabus['topic'];
$subject_name = $syllabus['subject_name'];

$prompt = "You are an educational assistant helping students learn. Create a comprehensive but concise summary of the following topic.

Subject: {$subject_name}
Topic: {$topic_name}

Content to summarize:
{$content}

Please provide:
1. A brief overview (2-3 sentences)
2. Key concepts (bullet points, 4-6 main points)
3. Important points to remember (3-5 points)
4. Study tips specific to this topic (2-3 tips)

Format the response in clean HTML with appropriate headings and styling. Use <h3> for section titles, <ul> for lists, and <p> for paragraphs.";

$data = [
    "model" => AI_MODEL,
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ]
];

$ch = curl_init(OPENROUTER_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . OPENROUTER_API_KEY,
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (PHP_VERSION_ID < 80500) { curl_close($ch); }

if($curl_error){
    echo json_encode(['success' => false, 'error' => 'Network error: ' . $curl_error]);
    exit();
} else if($http_code == 429){
    echo json_encode(['success' => false, 'error' => 'AI service is busy. Please try again in a moment.']);
    exit();
} else if($http_code == 401){
    echo json_encode(['success' => false, 'error' => 'AI service authentication failed.']);
    exit();
} else if($http_code == 200){
    $json = json_decode($response, true);
    $summary = $json['choices'][0]['message']['content'];
    
    // Save summary to database
    if($existing){
        // Update existing summary
        $stmt = $conn->prepare("UPDATE topic_summaries SET summary = ?, generated_at = NOW() WHERE syllabus_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $summary, $syllabus_id, $user_id);
    } else {
        // Insert new summary
        $stmt = $conn->prepare("INSERT INTO topic_summaries (syllabus_id, user_id, summary, generated_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $syllabus_id, $user_id, $summary);
    }
    
    if($stmt->execute()){
        echo json_encode([
            'success' => true, 
            'summary' => $summary,
            'cached' => false
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'summary' => $summary,
            'warning' => 'Summary generated but not saved to database'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'AI service error: HTTP ' . $http_code]);
}
?>

