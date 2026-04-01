<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

// Check if constants are defined
if(!defined('PASSING_SCORE')){
    define('PASSING_SCORE', 60);
}

$score = isset($_GET['score']) ? floatval($_GET['score']) : 0;
$correct = isset($_GET['correct']) ? intval($_GET['correct']) : 0;
$total = isset($_GET['total']) ? intval($_GET['total']) : 0;
$time_taken = isset($_GET['time']) ? intval($_GET['time']) : 0;
$result_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = currentUserId();

if($result_id > 0){
    // Load result data when available
    $stmt = $conn->prepare("SELECT r.*, s.subject_name FROM results r JOIN subjects s ON r.subject_id = s.id WHERE r.id = ? AND r.user_id = ?");
    $stmt->bind_param("ii", $result_id, $user_id);
    $stmt->execute();
    $result_data = $stmt->get_result()->fetch_assoc();

    if($result_data){
        $score = floatval($result_data['score']);
        $time_taken = intval($result_data['time_taken']);
        
        // Recalculate correct/total from attempts on the quiz date
        $attempts_query = "SELECT qa.is_correct 
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
            $stmt->bind_param("isi", $user_id, $result_data['created_at'], $result_data['subject_id']);
        }

        $stmt->execute();
        $attempts = $stmt->get_result();

        $total = $attempts->num_rows;
        $correct = 0;
        while($row = $attempts->fetch_assoc()){
            if($row['is_correct']){
                $correct++;
            }
        }
    }
}

$pass_status = $score >= PASSING_SCORE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - MindPlay</title>
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
        <div class="container-sm">
            <!-- Result Card -->
            <div class="card fade-in text-center">
                <?php if($pass_status): ?>
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🎉</div>
                    <h1 style="font-size: 2.5rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.5rem;">Congratulations!</h1>
                    <p style="font-size: 1.25rem; color: var(--dark); margin-bottom: 2rem;">You passed the quiz!</p>
                <?php else: ?>
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
                    <h1 style="font-size: 2.5rem; font-weight: 700; color: var(--warning); margin-bottom: 0.5rem;">Keep Practicing!</h1>
                    <p style="font-size: 1.25rem; color: var(--dark); margin-bottom: 2rem;">Review the topics and try again.</p>
                <?php endif; ?>
                
                <!-- Score Display -->
                <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: var(--radius); padding: 2rem; margin-bottom: 1.5rem;">
                    <p style="color: var(--gray); font-size: 1.125rem; margin-bottom: 0.5rem;">Your Score</p>
                    <p style="font-size: 4rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;"><?php echo $score; ?>%</p>
                    <p style="color: var(--dark); font-size: 1.125rem;">
                        <?php echo $correct; ?> out of <?php echo $total; ?> questions correct
                    </p>
                </div>

                <!-- Performance Analysis -->
                <div class="grid grid-4" style="margin-bottom: 1.5rem;">
                    <div style="background: #d1fae5; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: var(--secondary); font-weight: 600; margin-bottom: 0.5rem;">✓ Correct</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #065f46;"><?php echo $correct; ?></p>
                    </div>
                    <div style="background: #fee2e2; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: var(--danger); font-weight: 600; margin-bottom: 0.5rem;">✗ Incorrect</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #991b1b;"><?php echo $total - $correct; ?></p>
                    </div>
                    <div style="background: #dbeafe; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: var(--primary); font-weight: 600; margin-bottom: 0.5rem;">📊 Total</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #1e40af;"><?php echo $total; ?></p>
                    </div>
                    <div style="background: #fef3c7; padding: 1.5rem; border-radius: var(--radius); text-align: center;">
                        <p style="color: #92400e; font-weight: 600; margin-bottom: 0.5rem;">⏱️ Time</p>
                        <p style="font-size: 2.5rem; font-weight: 700; color: #78350f;">
                            <?php 
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

                <!-- Action Buttons -->
                <div class="action-buttons" style="justify-content: center;">
                    <a href="dashboard.php" class="btn btn-primary">
                        🏠 Back to Dashboard
                    </a>
                    <?php if(!$pass_status): ?>
                    <a href="javascript:history.back()" class="btn btn-warning">
                        🔄 Try Again
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Weak Topics Reminder -->
            <?php if(!$pass_status): ?>
            <div class="alert alert-warning" style="margin-top: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #92400e; margin-bottom: 0.5rem;">💡 Study Tip</h3>
                <p style="color: #92400e;">
                    Check your dashboard to see which topics need more practice. 
                    Focus on reviewing those topics before attempting the quiz again.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
