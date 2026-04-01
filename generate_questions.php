<?php
// Initialize session and require admin authentication
require_once __DIR__ . '/../config/session.php';
requireAdmin('../login.php');

require_once __DIR__ . '/../config/db.php';

$message = "";
$error = "";
$syllabus_id = isset($_GET['syllabus_id']) ? intval($_GET['syllabus_id']) : 0;

// Get syllabus details
$syllabus_data = null;
if($syllabus_id > 0){
    $sql = "SELECT sy.*, s.subject_name FROM syllabus sy 
            JOIN subjects s ON sy.subject_id = s.id 
            WHERE sy.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $syllabus_id);
    $stmt->execute();
    $syllabus_data = $stmt->get_result()->fetch_assoc();
}

if(isset($_POST['generate']) && $syllabus_data){
    // Check if API key is configured
    if(!defined('OPENROUTER_API_KEY') || OPENROUTER_API_KEY === 'sk-or-v1-YOUR_API_KEY_HERE'){
        $error = "Please configure your OpenRouter API key in config/config.php";
    } else {
        $content = $syllabus_data['content'];
        $num_questions = intval($_POST['num_questions']);
        
        // Prepare API request
        $apiKey = OPENROUTER_API_KEY;
    
    $prompt = "Generate {$num_questions} multiple-choice revision questions from the following content. 
    Format each question as follows:
    Q: [Question text]
    A) [Option A]
    B) [Option B]
    C) [Option C]
    D) [Option D]
    Correct: [A/B/C/D]
    
    Content: {$content}";
    
    $data = [
        "model" => AI_MODEL,
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];
    
    $ch = curl_init(OPENROUTER_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $apiKey,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if($curl_error){
        $error = "Network error: " . $curl_error;
    } else if($http_code == 429){
        $error = "API rate limit exceeded. Please wait a few minutes and try again with fewer questions.";
    } else if($http_code == 401){
        $error = "Invalid API key. Please check your configuration.";
    } else if($http_code == 200){
        $json = json_decode($response, true);
        $text = $json['choices'][0]['message']['content'];
        
        // Start transaction for cascade deletion and insertion
        $conn->begin_transaction();
        
        try {
            // First, delete quiz_attempts for questions in this syllabus
            $delete_attempts = $conn->prepare("DELETE qa FROM quiz_attempts qa 
                                               INNER JOIN questions q ON qa.question_id = q.id 
                                               WHERE q.syllabus_id = ?");
            $delete_attempts->bind_param("i", $syllabus_id);
            $delete_attempts->execute();
            
            // Then delete the questions
            $delete_stmt = $conn->prepare("DELETE FROM questions WHERE syllabus_id = ?");
            $delete_stmt->bind_param("i", $syllabus_id);
            $delete_stmt->execute();
            $deleted_count = $delete_stmt->affected_rows;
        
        // Parse questions
        $questions_generated = 0;
        $lines = explode("\n", $text);
        $current_question = "";
        $current_answer = "";
        
        foreach($lines as $line){
            $line = trim($line);
            if(strpos($line, 'Q:') === 0 || strpos($line, 'Question') === 0){
                if($current_question && $current_answer){
                    // Save previous question
                    $stmt = $conn->prepare("INSERT INTO questions(syllabus_id, question, answer) VALUES(?, ?, ?)");
                    $stmt->bind_param("iss", $syllabus_id, $current_question, $current_answer);
                    if($stmt->execute()) $questions_generated++;
                }
                $current_question = $line;
                $current_answer = "";
            } else if(strpos($line, 'Correct:') === 0){
                $current_answer = str_replace('Correct:', '', $line);
                $current_answer = trim($current_answer);
            } else if(!empty($line) && preg_match('/^[A-D][\):]/', $line)){
                $current_question .= "\n" . $line;
            }
        }
        
        // Save last question
        if($current_question && $current_answer){
            $stmt = $conn->prepare("INSERT INTO questions(syllabus_id, question, answer) VALUES(?, ?, ?)");
            $stmt->bind_param("iss", $syllabus_id, $current_question, $current_answer);
            if($stmt->execute()) $questions_generated++;
        }
        
        // Commit the transaction
        $conn->commit();
        
        if($deleted_count > 0){
            $message = "Replaced {$deleted_count} old questions with {$questions_generated} new questions!";
        } else {
            $message = "Successfully generated {$questions_generated} questions!";
        }
        
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to save questions: " . $e->getMessage();
        }
    } else {
        $json_response = json_decode($response, true);
        $api_error = isset($json_response['error']) ? $json_response['error']['message'] : 'Unknown error';
        $error = "API Error (HTTP {$http_code}): " . $api_error;
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Questions - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .generate-btn:hover { animation: pulse 0.6s ease-in-out; }
    </style>
</head>
<body>
    <?php 
    $base_path = '../';
    include __DIR__ . '/../includes/admin_navbar.php'; 
    ?>

    <div class="container">
        <div class="container-md">
            <?php if(!$syllabus_data): ?>
                <div class="card fade-in text-center" style="padding: 3rem; border: 2px solid var(--danger); border-radius: 12px;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">❌</div>
                    <h2 style="color: var(--danger); margin-bottom: 1rem;">Invalid Topic</h2>
                    <p style="color: var(--gray); font-size: 1.125rem; margin-bottom: 2rem;">The selected syllabus topic was not found</p>
                    <a href="dashboard.php" class="btn btn-primary" style="padding: 0.875rem 1.75rem;">⬅️ Return to Dashboard</a>
                </div>
            <?php else: ?>
            
            <?php if($message): ?>
                <div class="alert-card success-alert" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 2px solid #34d399; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); animation: slideDown 0.4s ease-out;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="background: #10b981; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">✓</div>
                        <div style="flex: 1; padding-top: 0.25rem;">
                            <h3 style="color: #059669; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Generation Complete!</h3>
                            <p style="color: #065f46; margin-bottom: 0; font-size: 1rem; line-height: 1.5;"><?php echo $message; ?></p>
                        </div>
                        <button onclick="this.closest('.alert-card').remove()" style="background: #10b981; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">✕</button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert-card error-alert" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 2px solid #f87171; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15); animation: slideDown 0.4s ease-out;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="background: #dc2626; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);">❌</div>
                        <div style="flex: 1; padding-top: 0.25rem;">
                            <h3 style="color: #dc2626; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Generation Failed</h3>
                            <p style="color: #991b1b; margin-bottom: 0; font-size: 1rem; line-height: 1.5;"><?php echo $error; ?></p>
                        </div>
                        <button onclick="this.closest('.alert-card').remove()" style="background: #dc2626; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">✕</button>
                    </div>
                </div>
            <?php endif; ?>
                
            <div class="card fade-in">
                <h1 style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 3px solid var(--primary);">🤖 AI Question Generator</h1>
                
                <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 2px solid #60a5fa;">
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-size: 1.5rem;">📚</span>
                        <div>
                            <p style="color: var(--dark); font-weight: 600; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($syllabus_data['subject_name']); ?></p>
                            <p style="color: var(--gray); font-size: 0.9rem;">Topic: <strong><?php echo htmlspecialchars($syllabus_data['topic']); ?></strong></p>
                        </div>
                    </div>
                </div>
                
                <div style="background: #fef3c7; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border-left: 4px solid #f59e0b;">
                    <h3 style="font-weight: 600; margin-bottom: 0.75rem; color: #92400e; display: flex; align-items: center; gap: 0.5rem;">
                        <span>📝</span> Topic Content
                    </h3>
                    <p style="color: #78350f; line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($syllabus_data['content']); ?></p>
                </div>
                
                <form method="POST" id="generateForm" style="margin-bottom: 2.5rem; background: var(--light); padding: 2rem; border-radius: 12px; border: 2px solid var(--gray-light);">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; color: var(--dark); margin-bottom: 0.75rem; display: block;">🔢 Number of Questions</label>
                        <select name="num_questions" required class="form-select" style="padding: 0.875rem; font-size: 1rem; border: 2px solid var(--gray-light); border-radius: 8px;">
                            <option value="5">5 Questions</option>
                            <option value="10" selected>10 Questions</option>
                            <option value="15">15 Questions</option>
                            <option value="20">20 Questions</option>
                        </select>
                        <p class="form-hint" style="margin-top: 0.5rem; color: var(--gray); font-size: 0.875rem;">⚠️ Generating new questions will replace all existing questions for this topic</p>
                    </div>
                    
                    <button type="submit" name="generate" class="btn btn-success btn-block generate-btn" style="font-size: 1.25rem; padding: 1.125rem; font-weight: 700; border-radius: 10px;">
                        🤖 Generate Questions with AI
                    </button>
                </form>
                
                <!-- Existing Questions -->
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--dark); margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--gray-light); display: flex; align-items: center; justify-content: space-between;">
                        <span>📚 Existing Questions</span>
                        <?php
                        $stmt_count = $conn->prepare("SELECT COUNT(*) as count FROM questions WHERE syllabus_id = ?");
                        $stmt_count->bind_param("i", $syllabus_id);
                        $stmt_count->execute();
                        $total_count = $stmt_count->get_result()->fetch_assoc()['count'];
                        ?>
                        <span style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem;"><?php echo $total_count; ?> Total</span>
                    </h2>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM questions WHERE syllabus_id = ?");
                    $stmt->bind_param("i", $syllabus_id);
                    $stmt->execute();
                    $questions = $stmt->get_result();
                    if($questions->num_rows > 0){
                        $count = 1;
                        while($q = $questions->fetch_assoc()){
                            echo "<div class='question-card' style='border: 2px solid var(--gray-light); border-radius: 10px; padding: 1.5rem; margin-bottom: 1.25rem; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s;' onmouseover='this.style.boxShadow=\"0 4px 12px rgba(0,0,0,0.1)\"' onmouseout='this.style.boxShadow=\"0 2px 8px rgba(0,0,0,0.05)\"'>
                                <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;'>
                                    <span style='background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem;'>Question {$count}</span>
                                </div>
                                <p style='color: var(--dark); white-space: pre-wrap; margin-bottom: 1.25rem; line-height: 1.6; font-size: 1rem;'>{$q['question']}</p>
                                <div style='background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--secondary);'>
                                    <p style='color: #065f46; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; margin: 0;'>
                                        <span style='font-size: 1.25rem;'>✓</span> Correct Answer: <strong>{$q['answer']}</strong>
                                    </p>
                                </div>
                            </div>";
                            $count++;
                        }
                    } else {
                        echo "<div style='text-align: center; padding: 3rem; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 12px; border: 2px dashed var(--gray-light);'>
                            <div style='font-size: 4rem; margin-bottom: 1rem;'>❓</div>
                            <h3 style='color: var(--dark); font-size: 1.25rem; margin-bottom: 0.5rem;'>No Questions Yet</h3>
                            <p style='color: var(--gray); font-size: 1rem;'>Click the generate button above to create AI-powered questions</p>
                        </div>";
                    }
                    ?>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="ai-generating-badge" style="margin-bottom: 1.5rem;">
                <span class="ai-icon">🤖</span>
                <span>AI is Working</span>
            </div>
            <div class="loading-spinner loading-spinner-lg" style="margin: 0 auto;"></div>
            <div class="generating-text">Generating Questions</div>
            <p style="color: var(--gray); margin-top: 1rem; margin-bottom: 1.5rem;">
                Our AI is analyzing the content and creating high-quality questions for you...
            </p>
            <div class="progress-bar">
                <div class="progress-bar-fill" id="progressBarFill" style="width: 0%;"></div>
            </div>
            <p style="color: var(--gray); font-size: 0.875rem; margin-top: 1rem;">
                ⏱️ This typically takes 10-30 seconds depending on the number of questions
            </p>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Auto-hide alerts after 6 seconds
        document.querySelectorAll('.alert-card').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 6000);
        });
        
        // Show loading overlay when generating questions
        const form = document.getElementById('generateForm');
        if(form) {
            form.addEventListener('submit', function(e) {
                // Show loading overlay
                document.getElementById('loadingOverlay').style.display = 'flex';
                
                // Animate progress bar
                let progress = 0;
                const progressBar = document.getElementById('progressBarFill');
                
                const interval = setInterval(() => {
                    progress += Math.random() * 10;
                    if(progress > 90) progress = 90;
                    progressBar.style.width = progress + '%';
                }, 800);
                
                // Store interval ID to clear it if page reloads
                window.progressInterval = interval;
            });
        }
    </script>
</body>
</html>
