<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

// Define passing score constant
if(!defined('PASSING_SCORE')){
    define('PASSING_SCORE', 60);
}

// Get student statistics
$user_id = currentUserId();
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

$stmt = $conn->prepare("SELECT COUNT(DISTINCT subject_id) as count FROM results WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_quizzes = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM quiz_attempts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_questions_attempted = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT AVG(score) as avg FROM results WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avg_score = $stmt->get_result()->fetch_assoc()['avg'];
$avg_score = $avg_score ? round($avg_score, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - MindPlay</title>
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
        
        <!-- Statistics Cards -->
        <div class="grid grid-3 fade-in">
            <div class="stats-card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white;">
                <div class="stats-content">
                    <h4>Quizzes Taken</h4>
                    <div class="value" style="color: white;"><?php echo $total_quizzes; ?></div>
                </div>
                <div class="stats-icon">📝</div>
            </div>
            
            <div class="stats-card" style="background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%); color: white;">
                <div class="stats-content">
                    <h4>Questions Attempted</h4>
                    <div class="value" style="color: white;"><?php echo $total_questions_attempted; ?></div>
                </div>
                <div class="stats-icon">❓</div>
            </div>
            
            <div class="stats-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                <div class="stats-content">
                    <h4>Average Score</h4>
                    <div class="value" style="color: white;"><?php echo $avg_score; ?>%</div>
                </div>
                <div class="stats-icon">⭐</div>
            </div>
        </div>

        <!-- Available Subjects -->
        <div class="card fade-in">
            <h2 class="card-header">📚 Available Subjects for Revision</h2>
            
            <div class="grid grid-3">
                <?php
                $subjects = $conn->query("SELECT s.* FROM subjects s ORDER BY s.subject_name");
                
                if($subjects->num_rows > 0){
                    while($subject = $subjects->fetch_assoc()){
                        $subject_id = intval($subject['id']);
                        $topic_stmt = $conn->prepare("SELECT sy.id, sy.topic, (SELECT COUNT(*) FROM questions q WHERE q.syllabus_id = sy.id) as question_count 
                                                     FROM syllabus sy 
                                                     WHERE sy.subject_id = ? 
                                                     HAVING question_count > 0
                                                     ORDER BY sy.topic");
                        $topic_stmt->bind_param("i", $subject_id);
                        $topic_stmt->execute();
                        $topics = $topic_stmt->get_result();

                        echo "<div style='background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 1.5rem; border-radius: var(--radius); transition: all 0.3s;' class='card' onmouseover='this.style.boxShadow=\"var(--shadow-lg)\"' onmouseout='this.style.boxShadow=\"var(--shadow)\"'>
                            <h3 style='font-size: 1.25rem; font-weight: 700; color: #1e40af; margin-bottom: 0.5rem;'>{$subject['subject_name']}</h3>";

                        if($topics->num_rows > 0){
                            echo "<div style='display: flex; flex-direction: column; gap: 0.5rem;'>";
                            while($topic = $topics->fetch_assoc()){
                                $topic_name = htmlspecialchars($topic['topic']);
                                $topic_id = intval($topic['id']);
                                $question_count = intval($topic['question_count']);
                                echo "<div style='display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.7); padding: 0.75rem; border-radius: 8px; gap: 0.75rem;'>
                                    <div style='flex: 1;'>
                                        <strong>{$topic_name}</strong>
                                        <span style='color: var(--gray); font-size: 0.875rem; margin-left: 0.5rem;'>({$question_count} questions)</span>
                                    </div>
                                    <div style='display: flex; gap: 0.5rem;'>
                                        <a href='topic_summary.php?syllabus_id={$topic_id}' class='btn btn-info btn-sm' title='View AI-generated summary'>
                                            📝 Summary
                                        </a>
                                        <a href='quiz.php?subject_id={$subject_id}&syllabus_id={$topic_id}' class='btn btn-primary btn-sm'>
                                            ▶️ Start Quiz
                                        </a>
                                    </div>
                                </div>";
                            }
                            echo "</div>";
                        } else {
                            echo "<p style='color: var(--gray);'>No topics with questions available yet.</p>";
                        }

                        echo "</div>";
                    }
                } else {
                    echo "<p style='color: var(--gray); grid-column: 1 / -1; text-align: center; padding: 2rem;'>No subjects available yet. Please check back later.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Recent Quiz Results -->
        <div class="card fade-in">
            <h2 class="card-header">📊 Recent Quiz Results</h2>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $results = $conn->query("SELECT r.*, s.subject_name 
                                                FROM results r 
                                                JOIN subjects s ON r.subject_id = s.id 
                                                WHERE r.user_id = {$user_id} 
                                                ORDER BY r.created_at DESC LIMIT 10");
                        
                        if($results->num_rows > 0){
                            while($result = $results->fetch_assoc()){
                                $pass_class = $result['score'] >= PASSING_SCORE ? 'badge-success' : 'badge-danger';
                                $pass_text = $result['score'] >= PASSING_SCORE ? '✓ Passed' : '⚠ Needs Review';
                                $score_color = $result['score'] >= PASSING_SCORE ? 'color: var(--secondary)' : 'color: var(--danger)';
                                $date = date('M d, Y', strtotime($result['created_at']));
                                
                                echo "<tr>
                                    <td><strong>{$result['subject_name']}</strong></td>
                                    <td><span style='font-weight: 700; {$score_color}'>{$result['score']}%</span></td>
                                    <td>{$date}</td>
                                    <td><span class='badge {$pass_class}'>{$pass_text}</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center; padding: 2rem; color: var(--gray);'>No quiz attempts yet. Start your first quiz!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Weak Topics -->
        <?php
        $weak_topics = $conn->query("SELECT wt.*, sy.topic, s.subject_name 
                                    FROM weak_topics wt 
                                    JOIN syllabus sy ON wt.syllabus_id = sy.id 
                                    JOIN subjects s ON sy.subject_id = s.id 
                                    WHERE wt.user_id = {$user_id} 
                                    ORDER BY wt.mistake_count DESC LIMIT 5");
        
        if($weak_topics->num_rows > 0):
        ?>
        <div class="card fade-in">
            <h2 class="card-header" style="color: var(--danger);">⚠️ Topics That Need More Practice</h2>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php while($topic = $weak_topics->fetch_assoc()): ?>
                    <div class="alert alert-warning">
                        <div>
                            <p style="font-weight: 600; color: #92400e;"><?php echo htmlspecialchars($topic['subject_name']); ?> - <?php echo htmlspecialchars($topic['topic']); ?></p>
                            <p style="font-size: 0.875rem; color: var(--gray);">Mistakes: <?php echo intval($topic['mistake_count']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="../assets/js/main.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert-card').forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
