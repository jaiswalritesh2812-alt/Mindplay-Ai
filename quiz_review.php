<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

// Get result ID
$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = currentUserId();
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

if($result_id == 0){
    header("Location: dashboard.php");
    exit();
}

// Get result details with subject info
$stmt = $conn->prepare("SELECT r.*, s.subject_name, sy.topic as topic_name 
                        FROM results r 
                        JOIN subjects s ON r.subject_id = s.id 
                        LEFT JOIN syllabus sy ON r.syllabus_id = sy.id
                        WHERE r.id = ? AND r.user_id = ?");
$stmt->bind_param("ii", $result_id, $user_id);
$stmt->execute();
$result_data = $stmt->get_result()->fetch_assoc();

if(!$result_data){
    header("Location: dashboard.php");
    exit();
}

$score = $result_data['score'];
$subject_name = $result_data['subject_name'];
$subject_id = $result_data['subject_id'];
$topic_name = $result_data['topic_name'] ?? '';
$pass_status = $score >= (defined('PASSING_SCORE') ? PASSING_SCORE : 60);

// Get quiz attempts for this result (questions answered in this quiz session)
$attempts_query = "SELECT qa.*, q.question, q.answer as correct_answer, sy.topic 
                   FROM quiz_attempts qa
                   JOIN questions q ON qa.question_id = q.id
                   JOIN syllabus sy ON q.syllabus_id = sy.id
                   WHERE qa.user_id = ? 
                   AND DATE(qa.attempted_at) = DATE(?)";

if(!empty($result_data['syllabus_id'])){
    $attempts_query .= " AND sy.id = ?";
    $stmt = $conn->prepare($attempts_query);
    $stmt->bind_param("isi", $user_id, $result_data['created_at'], $result_data['syllabus_id']);
} else {
    $attempts_query .= " AND sy.subject_id = ?";
    $stmt = $conn->prepare($attempts_query);
    $stmt->bind_param("isi", $user_id, $result_data['created_at'], $subject_id);
}

$attempts_query .= " ORDER BY qa.attempted_at DESC";
$stmt->execute();
$attempts = $stmt->get_result();

$total_questions = $attempts->num_rows;
$correct_count = 0;
$questions_data = [];

while($attempt = $attempts->fetch_assoc()){
    if($attempt['is_correct']){
        $correct_count++;
    }
    $questions_data[] = $attempt;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Review - <?php echo htmlspecialchars($subject_name); ?><?php echo $topic_name ? ' - ' . htmlspecialchars($topic_name) : ''; ?> - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
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
    </style>
</head>
<body>
    <?php 
    $base_path = '../';
    $show_leaderboard = true;
    include __DIR__ . '/../includes/student_navbar.php'; 
    ?>

    <div class="container">
        <div class="container-md">
            <?php if(!empty($success)): ?>
                <div class="alert-card success-alert" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 2px solid #34d399; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); animation: slideDown 0.4s ease-out;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="background: #10b981; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                            🎉
                        </div>
                        <div style="flex: 1; padding-top: 0.25rem;">
                            <h3 style="color: #059669; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Quiz Submitted Successfully!</h3>
                            <p style="color: #065f46; margin-bottom: 0; font-size: 1rem; line-height: 1.5;">
                                Your answers have been recorded. Review your performance below.
                            </p>
                        </div>
                        <button onclick="this.closest('.alert-card').style.display='none'" style="background: #10b981; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">✕</button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($error)): ?>
                <div class="alert-card error-alert" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 2px solid #f87171; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15); animation: slideDown 0.4s ease-out;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="background: #dc2626; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);">
                            ❌
                        </div>
                        <div style="flex: 1; padding-top: 0.25rem;">
                            <h3 style="color: #dc2626; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Error</h3>
                            <p style="color: #991b1b; margin-bottom: 0; font-size: 1rem; line-height: 1.5;">
                                <?php echo $error; ?>
                            </p>
                        </div>
                        <button onclick="this.closest('.alert-card').style.display='none'" style="background: #dc2626; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">✕</button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Score Card -->
            <div class="card fade-in text-center">
                <?php if($pass_status): ?>
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🎉</div>
                    <h1 style="font-size: 2.5rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.5rem;">Congratulations!</h1>
                    <p style="font-size: 1.25rem; color: var(--dark); margin-bottom: 2rem;">You passed the quiz!</p>
                <?php else: ?>
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
                    <h1 style="font-size: 2.5rem; font-weight: 700; color: var(--warning); margin-bottom: 0.5rem;">Keep Practicing!</h1>
                    <p style="font-size: 1.25rem; color: var(--dark); margin-bottom: 2rem;">Review the answers and try again.</p>
                <?php endif; ?>
                
                <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: var(--radius); padding: 2rem; margin-bottom: 1.5rem;">
                    <p style="color: var(--gray); font-size: 1.125rem; margin-bottom: 0.5rem;">Your Score</p>
                    <p style="font-size: 4rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;"><?php echo $score; ?>%</p>
                    <p style="color: var(--dark); font-size: 1.125rem;">
                        <?php echo $correct_count; ?> out of <?php echo $total_questions; ?> questions correct
                    </p>
                </div>

                <div class="grid grid-4">
                    <div style="background: #d1fae5; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: var(--secondary); font-weight: 600; margin-bottom: 0.5rem;">✓ Correct</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #065f46;"><?php echo $correct_count; ?></p>
                    </div>
                    <div style="background: #fee2e2; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: var(--danger); font-weight: 600; margin-bottom: 0.5rem;">✗ Incorrect</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #991b1b;"><?php echo $total_questions - $correct_count; ?></p>
                    </div>
                    <div style="background: #dbeafe; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: var(--primary); font-weight: 600; margin-bottom: 0.5rem;">📊 Total</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #1e40af;"><?php echo $total_questions; ?></p>
                    </div>
                    <div style="background: #fef3c7; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: #92400e; font-weight: 600; margin-bottom: 0.5rem;">⏱️ Time</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #78350f;">
                            <?php 
                            $time_taken = isset($result_data['time_taken']) ? intval($result_data['time_taken']) : 0;
                            if($time_taken > 0) {
                                $minutes = floor($time_taken / 60);
                                $seconds = $time_taken % 60;
                                echo sprintf("%02d:%02d", $minutes, $seconds);
                            } else {
                                echo "N/A";
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Answers Review -->
            <div class="card fade-in">
                <h2 class="card-header">📝 Review Your Answers</h2>
                
                <?php 
                $question_num = 1;
                foreach($questions_data as $qa): 
                    $is_correct = $qa['is_correct'];
                ?>
                <div class="question-card" style="<?php echo $is_correct ? 'border-left: 4px solid var(--secondary); background: #d1fae5;' : 'border-left: 4px solid var(--danger); background: #fee2e2;'; ?>">
                    <div class="question-header">
                        <span style="font-weight: 700; color: var(--dark);">Question <?php echo $question_num; ?></span>
                        <span class="badge <?php echo $is_correct ? 'badge-success' : 'badge-danger'; ?>" style="padding: 0.5rem 1rem;">
                            <?php echo $is_correct ? '✓ Correct' : '✗ Incorrect'; ?>
                        </span>
                    </div>
                    
                    <p style="color: var(--dark); font-weight: 600; margin-bottom: 1rem; white-space: pre-wrap;">
                        <?php echo htmlspecialchars($qa['question']); ?>
                    </p>
                    
                    <div style="margin-left: 1rem; margin-top: 1rem;">
                        <p style="color: var(--gray); margin-bottom: 0.75rem;"><strong>📚 Topic:</strong> <?php echo htmlspecialchars($qa['topic']); ?></p>
                        <p style="margin-bottom: 0.75rem;"><strong>Your Answer:</strong> 
                            <span style="<?php echo $is_correct ? 'color: #065f46;' : 'color: #991b1b;'; ?> font-weight: 600;">
                                <?php echo htmlspecialchars($qa['user_answer']); ?>
                            </span>
                        </p>
                        <?php if(!$is_correct): ?>
                        <p style="margin-bottom: 0.75rem;"><strong>Correct Answer:</strong> 
                            <span style="color: #065f46; font-weight: 600;">
                                <?php echo htmlspecialchars($qa['correct_answer']); ?>
                            </span>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    $question_num++;
                endforeach; 
                ?>
            </div>

            <!-- Action Buttons -->
            <div class="card text-center">
                <div class="action-buttons" style="justify-content: center;">
                    <a href="dashboard.php" class="btn btn-primary">
                        🏠 Back to Dashboard
                    </a>
                    <a href="leaderboard.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-info" style="background: #8b5cf6;">
                        🏆 View Leaderboard
                    </a>
                    <a href="quiz.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-success">
                        🔄 Take Quiz Again
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.card[style*="background"]').forEach(alert => {
            if(alert.style.background.includes('d1fae5') || alert.style.background.includes('fee2e2')) {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>
