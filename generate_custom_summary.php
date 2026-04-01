<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Get POST parameters
$topic = isset($_POST['topic']) ? trim($_POST['topic']) : '';
$user_id = intval(currentUserId() ?? 0);

if(empty($topic) || $user_id <= 0){
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

// Check if API key is configured
if(!defined('OPENROUTER_API_KEY') || OPENROUTER_API_KEY === 'sk-or-v1-YOUR_API_KEY_HERE'){
    echo json_encode(['success' => false, 'error' => 'AI service not configured']);
    exit();
}

// Check if content already exists in cache (within last 7 days)
$stmt = $conn->prepare("SELECT mindmap, summary, generated_at FROM custom_topic_summaries WHERE topic = ? AND user_id = ? AND generated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->bind_param("si", $topic, $user_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if($existing){
    echo json_encode([
        'success' => true, 
        'mindmap' => $existing['mindmap'],
        'summary' => $existing['summary'],
        'cached' => true
    ]);
    exit();
}

// Generate mind map and summary using AI
$prompt = "You are an educational AI assistant helping students learn any topic.

Topic: {$topic}

Please provide:

1. A Mermaid.js mindmap diagram code showing the topic breakdown. Use this exact format:
```mermaid
mindmap
  root((Topic Name))
    Main Concept 1
      Sub-concept 1.1
      Sub-concept 1.2
    Main Concept 2
      Sub-concept 2.1
      Sub-concept 2.2
    Main Concept 3
      Sub-concept 3.1
```

2. A comprehensive HTML-formatted summary with:
   - Brief Overview (2-3 sentences)
   - Key Concepts (5-7 bullet points)
   - Detailed Explanation (3-4 paragraphs)
   - Important Points to Remember (4-5 bullet points)
   - Real-World Applications or Examples (3-4 points)
   - Study Tips (2-3 practical tips)

Use proper HTML tags: <h3> for section headings, <p> for paragraphs, <ul> and <li> for lists, <strong> for emphasis.

Separate the mindmap code and summary with '---SEPARATOR---'.
Format: [Mermaid Code]---SEPARATOR---[HTML Summary]";

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
    $content = $json['choices'][0]['message']['content'];
    
    // Parse the response
    $parts = explode('---SEPARATOR---', $content);
    
    if(count($parts) >= 2){
        $mindmap_raw = trim($parts[0]);
        $summary = trim($parts[1]);
        
        // Extract mermaid code from markdown code blocks if present
        if(preg_match('/```mermaid\s*(.*?)\s*```/s', $mindmap_raw, $matches)){
            $mindmap = trim($matches[1]);
        } else {
            $mindmap = $mindmap_raw;
        }
        
        // Clean up summary (remove markdown code blocks if present)
        $summary = preg_replace('/```html\s*(.*?)\s*```/s', '$1', $summary);
        $summary = trim($summary);
        
    } else {
        // Fallback parsing
        $mindmap = "mindmap\n  root((" . $topic . "))\n    Key Concepts\n      Concept 1\n      Concept 2\n    Applications\n      Example 1\n      Example 2";
        $summary = $content;
    }
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO custom_topic_summaries (user_id, topic, mindmap, summary, generated_at) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE mindmap = VALUES(mindmap), summary = VALUES(summary), generated_at = NOW()");
    $stmt->bind_param("isss", $user_id, $topic, $mindmap, $summary);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'mindmap' => $mindmap,
        'summary' => $summary,
        'cached' => false
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'AI service error: HTTP ' . $http_code]);
}
?>

