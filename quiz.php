<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

// Check if constants are defined
if(!defined('QUESTIONS_PER_QUIZ')){
    define('QUESTIONS_PER_QUIZ', 10);
}
if(!defined('PASSING_SCORE')){
    define('PASSING_SCORE', 60);
}

// Initialize error and success messages
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

// Get and validate user ID
$user_id = intval(currentUserId() ?? 0);
if($user_id <= 0){
    header("Location: dashboard.php?error=Session expired");
    exit();
}

$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$syllabus_id = isset($_GET['syllabus_id']) ? intval($_GET['syllabus_id']) : 0;

if($subject_id <= 0){
    header("Location: dashboard.php");
    exit();
}

// Get subject details using prepared statement
$stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$subject_result = $stmt->get_result();

if($subject_result->num_rows == 0){
    header("Location: dashboard.php");
    exit();
}
$subject = $subject_result->fetch_assoc();

// Resolve topic for this quiz
$topic_name = '';
if($syllabus_id > 0){
    $stmt = $conn->prepare("SELECT id, topic FROM syllabus WHERE id = ? AND subject_id = ?");
    $stmt->bind_param("ii", $syllabus_id, $subject_id);
    $stmt->execute();
    $topic_result = $stmt->get_result();

    if($topic_result->num_rows == 0){
        header("Location: dashboard.php");
        exit();
    }

    $topic_row = $topic_result->fetch_assoc();
    $syllabus_id = intval($topic_row['id']);
    $topic_name = $topic_row['topic'];
} else {
    $stmt = $conn->prepare("SELECT id, topic FROM syllabus WHERE subject_id = ? ORDER BY topic LIMIT 1");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $topic_result = $stmt->get_result();

    if($topic_result->num_rows == 0){
        $no_questions = true;
    } else {
        $topic_row = $topic_result->fetch_assoc();
        $syllabus_id = intval($topic_row['id']);
        $topic_name = $topic_row['topic'];
    }
}

// Handle quiz submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers']) && is_array($_POST['answers'])){
    $answers = $_POST['answers'];
    $total_questions = count($answers);
    $correct_answers = 0;
    $time_taken = isset($_POST['time_taken']) ? intval($_POST['time_taken']) : 0;
    
    // Validate user_id
    if(!$user_id || $user_id <= 0){
        header("Location: dashboard.php?error=Invalid user session");
        exit();
    }
    
    if($total_questions == 0){
        header("Location: quiz.php?subject_id={$subject_id}&syllabus_id={$syllabus_id}&error=No answers provided");
        exit();
    }
    
    // Start transaction for safe batch insertion
    $conn->begin_transaction();
    
    try {
        foreach($answers as $question_id => $user_answer){
            $question_id = intval($question_id);
            
            // Get correct answer using prepared statement
            $stmt = $conn->prepare("SELECT answer, syllabus_id FROM questions WHERE id = ?");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $q_result = $stmt->get_result();
            
            if($q_result->num_rows == 0) continue;
            
            $question_data = $q_result->fetch_assoc();
            $correct_answer = trim($question_data['answer']);
            $question_syllabus_id = $question_data['syllabus_id'];
            $is_correct = (strtoupper(trim($user_answer)) == strtoupper($correct_answer));
            
            if($is_correct){
                $correct_answers++;
            } else {
                // Track weak topic using prepared statements
                $check_stmt = $conn->prepare("SELECT id FROM weak_topics WHERE user_id = ? AND syllabus_id = ?");
                $check_stmt->bind_param("ii", $user_id, $question_syllabus_id);
                $check_stmt->execute();
                $check_weak = $check_stmt->get_result();
                
                if($check_weak->num_rows > 0){
                    $update_stmt = $conn->prepare("UPDATE weak_topics SET mistake_count = mistake_count + 1, last_attempt = NOW() WHERE user_id = ? AND syllabus_id = ?");
                    $update_stmt->bind_param("ii", $user_id, $question_syllabus_id);
                    $update_stmt->execute();
                } else {
                    $insert_stmt = $conn->prepare("INSERT INTO weak_topics (user_id, syllabus_id, mistake_count) VALUES (?, ?, 1)");
                    $insert_stmt->bind_param("ii", $user_id, $question_syllabus_id);
                    $insert_stmt->execute();
                }
            }
            
            // Record quiz attempt - verify user exists first
            $verify_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
            $verify_user->bind_param("i", $user_id);
            $verify_user->execute();
            
            if($verify_user->get_result()->num_rows == 0){
                throw new Exception("User not found in database");
            }
            
            $stmt = $conn->prepare("INSERT INTO quiz_attempts (user_id, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $user_id, $question_id, $user_answer, $is_correct);
            $stmt->execute();
        }
        
        // Calculate score
        $score = round(($correct_answers / $total_questions) * 100, 2);
        
        // Save result with time_taken and get the inserted ID
        $stmt = $conn->prepare("INSERT INTO results (user_id, subject_id, syllabus_id, score, time_taken) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidi", $user_id, $subject_id, $syllabus_id, $score, $time_taken);
        $stmt->execute();
        $result_id = $conn->insert_id;
        
        $conn->commit();
        
        // Redirect to quiz review page with success message
        header("Location: quiz_review.php?id={$result_id}&success=Quiz submitted successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error submitting quiz: " . $e->getMessage();
        // Store error and reload page with error message
        header("Location: quiz.php?subject_id={$subject_id}&syllabus_id={$syllabus_id}&error=" . urlencode($error));
        exit();
    }
}

// Get random questions for quiz using prepared statement
$questions_query = "SELECT q.*, sy.topic 
                   FROM questions q 
                   JOIN syllabus sy ON q.syllabus_id = sy.id 
                   WHERE q.syllabus_id = ? 
                   ORDER BY RAND() 
                   LIMIT ?";
$stmt = $conn->prepare($questions_query);
$questions_limit = QUESTIONS_PER_QUIZ;
$stmt->bind_param("ii", $syllabus_id, $questions_limit);
$stmt->execute();
$questions = $stmt->get_result();

if($questions->num_rows == 0){
    $no_questions = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo htmlspecialchars($subject['subject_name']); ?> - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php 
    $base_path = '../';
    $show_leaderboard = true;
    include __DIR__ . '/../includes/student_navbar.php'; 
    ?>

    <div class="container">
        <div class="container-md">
            <?php if(!empty($error)): ?>
                <div class="alert-card error-alert" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 2px solid #f87171; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15); animation: slideDown 0.4s ease-out;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="background: #dc2626; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);">
                            ❌
                        </div>
                        <div style="flex: 1; padding-top: 0.25rem;">
                            <h3 style="color: #dc2626; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Quiz Submission Error</h3>
                            <p style="color: #991b1b; margin-bottom: 0; font-size: 1rem; line-height: 1.5;">
                                <?php echo $error; ?>
                            </p>
                        </div>
                        <button onclick="this.closest('.alert-card').style.display='none'" style="background: #dc2626; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">✕</button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="alert-card success-alert" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 2px solid #34d399; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); animation: slideDown 0.4s ease-out;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="background: #10b981; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                            ✓
                        </div>
                        <div style="flex: 1; padding-top: 0.25rem;">
                            <h3 style="color: #059669; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Success</h3>
                            <p style="color: #065f46; margin-bottom: 0; font-size: 1rem; line-height: 1.5;">
                                <?php echo $success; ?>
                            </p>
                        </div>
                        <button onclick="this.closest('.alert-card').style.display='none'" style="background: #10b981; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">✕</button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card fade-in text-center">
                <h1 style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">
                    <?php echo htmlspecialchars($subject['subject_name']); ?><?php echo $topic_name ? ' - ' . htmlspecialchars($topic_name) : ''; ?>
                </h1>
                <p style="color: var(--gray);">Answer all questions to the best of your ability. Good luck!</p>
                
                <!-- Timer Display -->
                <div id="timer" style="font-size: 1.5rem; font-weight: 700; color: var(--secondary); margin-top: 1rem; padding: 1rem; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: var(--radius);">
                    <span style="font-size: 1rem; opacity: 0.8;">⏱️ Time Elapsed:</span>
                    <span id="timer-display">00:00</span>
                </div>
            </div>

            <?php if(isset($no_questions)): ?>
                <div class="card text-center">
                    <p style="color: var(--danger); font-size: 1.25rem; margin-bottom: 1.5rem;">❌ No questions available for this subject yet.</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        ⬅️ Back to Dashboard
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" id="quizForm" action="">
                    <input type="hidden" name="time_taken" id="time_taken" value="0">
                    <input type="hidden" name="quiz_submitted" value="1">
                    <?php 
                    $question_number = 1;
                    while($q = $questions->fetch_assoc()): 
                        // Parse question to extract options
                        $question_text = $q['question'];
                        $lines = explode("\n", $question_text);
                        $main_question = "";
                        $options = [];
                        
                        foreach($lines as $line){
                            $line = trim($line);
                            if(preg_match('/^[A-D][\):]/', $line)){
                                $options[] = $line;
                            } else if(!empty($line) && strpos($line, 'Q:') !== 0){
                                $main_question .= $line . " ";
                            } else if(strpos($line, 'Q:') === 0){
                                $main_question = str_replace('Q:', '', $line);
                            }
                        }
                        
                        if(empty($options)){
                            // If no options parsed, it's a short answer question
                            $is_mcq = false;
                        } else {
                            $is_mcq = true;
                        }
                    ?>
                    <div class="question-card">
                        <div class="question-header">
                            <span class="question-number">
                                Question <?php echo $question_number; ?>
                            </span>
                            <span class="question-topic">Topic: <?php echo htmlspecialchars($q['topic']); ?></span>
                        </div>
                        
                        <p class="question-text">
                            <?php echo htmlspecialchars($main_question ?: $question_text); ?>
                        </p>
                        
                        <?php if($is_mcq): ?>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php foreach($options as $option): ?>
                                    <label class="option-label">
                                        <input type="radio" name="answers[<?php echo $q['id']; ?>]" 
                                               value="<?php echo substr($option, 0, 1); ?>" required>
                                        <span><?php echo htmlspecialchars($option); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <textarea name="answers[<?php echo $q['id']; ?>]" required rows="3" class="form-textarea"
                                placeholder="Enter your answer here..."></textarea>
                        <?php endif; ?>
                    </div>
                    <?php 
                        $question_number++;
                    endwhile; 
                    ?>
                    
                    <div class="card text-center">
                        <button type="submit" name="submit_quiz" id="submitQuizBtn" class="btn btn-success btn-block" style="font-size: 1.125rem; padding: 1rem 1.5rem;">
                            ✅ Submit Quiz
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Loading Overlay for Quiz Submission -->
    <div id="quizLoadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner loading-spinner-lg" style="margin: 0 auto;"></div>
            <div class="generating-text">Submitting Your Quiz</div>
            <p style="color: var(--gray); margin-top: 1rem;">
                Please wait while we process your answers...
            </p>
        </div>
    </div>

    <script>
        // Quiz Timer
        let startTime = Date.now();
        let timerInterval;
        
        function updateTimer() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            document.getElementById('timer-display').textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            document.getElementById('time_taken').value = elapsed;
        }
        
        // Update timer every second
        timerInterval = setInterval(updateTimer, 1000);
        
        // Track if form is being submitted to avoid validation loop
        let isSubmitting = false;
        
        // Form submission with validation
        const quizForm = document.getElementById('quizForm');
        if(quizForm) {
            quizForm.addEventListener('submit', function(e) {
                // Allow submission if already validated
                if(isSubmitting) {
                    return true;
                }
                
                e.preventDefault();
                clearInterval(timerInterval);
                updateTimer(); // Final update
                
                // Validate that all questions have answers
                const inputs = this.querySelectorAll('input[type="radio"], textarea');
                let unansweredCount = 0;
                const questionIds = new Set();
                
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if(name && name.includes('answers[')) {
                        const qId = name.match(/\d+/)[0];
                        questionIds.add(qId);
                    }
                });
                
                questionIds.forEach(qId => {
                    const answered = this.querySelector(`input[name="answers[${qId}]"]:checked, textarea[name="answers[${qId}]"]`);
                    if(!answered || (!answered.value && answered.tagName === 'TEXTAREA')) {
                        unansweredCount++;
                    }
                });
                
                if(unansweredCount > 0) {
                    // Show styled error message
                    showAlert(`Please answer all questions before submitting!\n\n${unansweredCount} question(s) remaining.`, 'error');
                    timerInterval = setInterval(updateTimer, 1000); // Restart timer
                    return false;
                }
                
                // Show confirmation dialog
                if(confirm('✅ Are you sure you want to submit your quiz?\n\nYou cannot change your answers after submission.')) {
                    // Show loading overlay
                    document.getElementById('quizLoadingOverlay').style.display = 'flex';
                    
                    // Disable submit button to prevent double submission
                    const submitBtn = this.querySelector('button[name="submit_quiz"]');
                    if(submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '⏳ Submitting...';
                        submitBtn.style.opacity = '0.6';
                        submitBtn.style.cursor = 'not-allowed';
                    }
                    
                    // Mark as submitting and submit
                    isSubmitting = true;
                    
                    // Use HTMLFormElement.submit() to bypass validation and submit directly
                    HTMLFormElement.prototype.submit.call(this);
                    return true;
                } else {
                    // Resume timer if cancelled
                    timerInterval = setInterval(updateTimer, 1000);
                }
                
                return false;
            });
        }
        
        // Warn before leaving page if form not submitted
        window.addEventListener('beforeunload', function(e) {
            const form = document.getElementById('quizForm');
            if(form && !form.submitted && form.querySelectorAll('input[type="radio"]:checked, textarea:not(:empty)').length > 0) {
                e.preventDefault();
                e.returnValue = 'Your progress will be lost if you leave this page.';
                return e.returnValue;
            }
        });
        
        if(quizForm) {
            quizForm.addEventListener('submit', function() {
                this.submitted = true;
            });
        }
        
        // Function to show styled alerts
        function showAlert(message, type = 'error') {
            const container = document.querySelector('.container-md');
            const firstCard = container.querySelector('.card');
            
            // Remove any existing custom alerts
            document.querySelectorAll('.custom-alert').forEach(el => el.remove());
            
            const alertDiv = document.createElement('div');
            alertDiv.className = 'custom-alert alert-card';
            
            if(type === 'error') {
                alertDiv.style.cssText = `
                    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
                    border: 2px solid #f87171;
                    border-radius: 12px;
                    padding: 1.5rem;
                    margin-bottom: 2rem;
                    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
                    animation: slideDown 0.4s ease-out;
                `;
                alertDiv.innerHTML = `
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="background: #dc2626; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);">
                            ⚠️
                        </div>
                        <div style="flex: 1; padding-top: 0.25rem;">
                            <h3 style="color: #dc2626; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Validation Error</h3>
                            <p style="color: #991b1b; margin-bottom: 0; font-size: 1rem; line-height: 1.5; white-space: pre-line;">${message}</p>
                        </div>
                        <button onclick="this.closest('.custom-alert').remove()" style="background: #dc2626; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">✕</button>
                    </div>
                `;
            }
            
            container.insertBefore(alertDiv, firstCard);
            
            // Auto remove after 6 seconds
            setTimeout(() => {
                alertDiv.style.transition = 'opacity 0.3s ease-out';
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 6000);
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
