<?php
// Initialize session and require authentication (both admin and student)
require_once __DIR__ . '/../config/session.php';
requireAuth('../login.php');

require_once __DIR__ . '/../config/db.php';

// Check user role
$is_admin = (currentUserRole() === 'admin');

// Get subject and syllabus IDs from URL
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$syllabus_id = isset($_GET['syllabus_id']) ? intval($_GET['syllabus_id']) : 0;

// Get all subjects for dropdown
$subjects_query = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name");

// If no subject selected, use first subject
if($subject_id == 0 && $subjects_query->num_rows > 0){
    $first_subject = $subjects_query->fetch_assoc();
    $subject_id = $first_subject['id'];
    $subjects_query->data_seek(0); // Reset pointer
}

// Get subject details
$stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$subject_result = $stmt->get_result();

$topics = [];
$topic_name = 'No Topic';

if($subject_result->num_rows == 0){
    $subject = ['subject_name' => 'No Subject'];
    $leaderboard_data = [];
} else {
    $subject = $subject_result->fetch_assoc();

    // Get topics for selected subject
    $topics_stmt = $conn->prepare("SELECT id, topic FROM syllabus WHERE subject_id = ? ORDER BY topic");
    $topics_stmt->bind_param("i", $subject_id);
    $topics_stmt->execute();
    $topics_result = $topics_stmt->get_result();

    while($row = $topics_result->fetch_assoc()){
        $topics[] = $row;
    }

    if(count($topics) > 0){
        if($syllabus_id == 0){
            $syllabus_id = $topics[0]['id'];
        }

        foreach($topics as $t){
            if($t['id'] == $syllabus_id){
                $topic_name = $t['topic'];
                break;
            }
        }

        // Get leaderboard data - top scores with time consideration
        $leaderboard_query = "
            SELECT 
                u.id,
                u.name,
                MAX(r.score) as best_score,
                COUNT(r.id) as total_attempts,
                AVG(r.score) as avg_score,
                MIN(CASE WHEN r.score = (SELECT MAX(score) FROM results WHERE user_id = u.id AND syllabus_id = ?) 
                         THEN NULLIF(r.time_taken, 0) END) as best_time,
                MAX(r.created_at) as last_attempt
            FROM results r
            JOIN users u ON r.user_id = u.id
            WHERE r.syllabus_id = ? AND u.role = 'student'
            GROUP BY u.id, u.name
            ORDER BY best_score DESC, 
                     CASE WHEN MIN(CASE WHEN r.score = (SELECT MAX(score) FROM results WHERE user_id = u.id AND syllabus_id = ?) 
                                        THEN NULLIF(r.time_taken, 0) END) IS NULL THEN 1 ELSE 0 END,
                     MIN(CASE WHEN r.score = (SELECT MAX(score) FROM results WHERE user_id = u.id AND syllabus_id = ?) 
                         THEN NULLIF(r.time_taken, 0) END) ASC, 
                     avg_score DESC
            LIMIT 50
        ";

        $stmt = $conn->prepare($leaderboard_query);
        $stmt->bind_param("iiii", $syllabus_id, $syllabus_id, $syllabus_id, $syllabus_id);
        $stmt->execute();
        $leaderboard_result = $stmt->get_result();

        $leaderboard_data = [];
        while($row = $leaderboard_result->fetch_assoc()){
            $leaderboard_data[] = $row;
        }
    } else {
        $leaderboard_data = [];
    }
}

// Get current user's rank and best time
$user_id = currentUserId();
$user_rank = 0;
$user_best_score = 0;
$user_best_time = 0;
foreach($leaderboard_data as $index => $student){
    if($student['id'] == $user_id){
        $user_rank = $index + 1;
        $user_best_score = $student['best_score'];
        $user_best_time = $student['best_time'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - <?php echo htmlspecialchars($subject['subject_name']); ?><?php echo $topic_name ? ' - ' . htmlspecialchars($topic_name) : ''; ?> - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php 
    $base_path = '../';
    if($is_admin) {
        include __DIR__ . '/../includes/admin_navbar.php';
    } else {
        include __DIR__ . '/../includes/student_navbar.php';
    }
    ?>

    <div class="container">
            <!-- Header -->
            <div class="card fade-in">
                <div class="flex justify-between items-center" style="flex-wrap: wrap; gap: 1.5rem;">
                    <div>
                        <h1 style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">🏆 Leaderboard</h1>
                        <p style="color: var(--gray);">Top performers in <?php echo htmlspecialchars($subject['subject_name']); ?><?php echo $topic_name ? ' • ' . htmlspecialchars($topic_name) : ''; ?></p>
                    </div>
                    
                    <!-- Subject Selector -->
                    <div>
                        <form method="GET" action="leaderboard.php" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <select name="subject_id" onchange="this.form.submit()" class="form-select" style="min-width: 200px;">
                                <?php while($subj = $subjects_query->fetch_assoc()): ?>
                                    <option value="<?php echo $subj['id']; ?>" <?php echo $subj['id'] == $subject_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($subj['subject_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <select name="syllabus_id" onchange="this.form.submit()" class="form-select" style="min-width: 220px;" <?php echo count($topics) == 0 ? 'disabled' : ''; ?>>
                                <?php if(count($topics) == 0): ?>
                                    <option value="">No topics available</option>
                                <?php else: ?>
                                    <?php foreach($topics as $topic): ?>
                                        <option value="<?php echo $topic['id']; ?>" <?php echo $topic['id'] == $syllabus_id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($topic['topic']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Your Rank Card (Students Only) -->
            <?php if(!$is_admin && $user_rank > 0): ?>
            <div class="card fade-in" style="background: linear-gradient(135deg, #8b5cf6 0%, var(--primary) 100%); color: white;">
                <div class="flex justify-between items-center" style="flex-wrap: wrap; gap: 1.5rem;">
                    <div>
                        <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Your Rank</p>
                        <p style="font-size: 2.5rem; font-weight: 700;">#<?php echo $user_rank; ?></p>
                    </div>
                    <div style="text-align: center;">
                        <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Best Score</p>
                        <p style="font-size: 2.5rem; font-weight: 700;"><?php echo round($user_best_score, 1); ?>%</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">⏱️ Best Time</p>
                        <p style="font-size: 2.5rem; font-weight: 700;">
                            <?php 
                            if($user_best_time && $user_best_time > 0) {
                                $minutes = floor($user_best_time / 60);
                                $seconds = $user_best_time % 60;
                                echo sprintf("%02d:%02d", $minutes, $seconds);
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Leaderboard Table -->
            <div class="card fade-in">
                <?php if(count($leaderboard_data) > 0): ?>
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student</th>
                            <th style="text-align: center;">Best Score</th>
                            <th style="text-align: center;">⏱️ Time</th>
                            <th style="text-align: center;">Avg Score</th>
                            <th style="text-align: center;">Attempts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach($leaderboard_data as $student): 
                            $is_current_user = ($student['id'] == $user_id);
                            $row_style = $is_current_user ? "background: #fef3c7; font-weight: 600;" : "";
                            
                            // Medal for top 3
                            $medal = '';
                            if($rank == 1) $medal = '🥇';
                            else if($rank == 2) $medal = '🥈';
                            else if($rank == 3) $medal = '🥉';
                        ?>
                        <tr <?php if($row_style) echo "style='$row_style'"; ?>>
                            <td style="font-size: 1.5rem;">
                                <?php echo $medal ?: "#{$rank}"; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($student['name']); ?>
                                <?php if($is_current_user): ?>
                                    <span class="badge badge-primary" style="margin-left: 0.5rem; font-size: 0.75rem;">You</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="font-weight: 700; color: var(--secondary); font-size: 1.125rem;">
                                    <?php echo round($student['best_score'], 1); ?>%
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span style="font-weight: 600; color: var(--primary);">
                                    <?php 
                                    if($student['best_time'] && $student['best_time'] > 0) {
                                        $minutes = floor($student['best_time'] / 60);
                                        $seconds = $student['best_time'] % 60;
                                        echo sprintf("%02d:%02d", $minutes, $seconds);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td style="text-align: center; color: var(--gray);">
                                <?php echo round($student['avg_score'], 1); ?>%
                            </td>
                            <td style="text-align: center; color: var(--gray);">
                                <?php echo $student['total_attempts']; ?>
                            </td>
                        </tr>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="text-center" style="padding: 3rem; color: var(--gray);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🏆</div>
                    <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">No quiz attempts yet for this subject</p>
                    <p style="margin-bottom: 1.5rem;">Be the first to take the quiz!</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        🏠 Go to Dashboard
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Take Quiz Button (Students Only) -->
            <?php if(!$is_admin && $subject_id > 0 && $syllabus_id > 0): ?>
            <div class="text-center mt-3">
                <a href="quiz.php?subject_id=<?php echo $subject_id; ?>&syllabus_id=<?php echo $syllabus_id; ?>" class="btn btn-success" style="font-size: 1.125rem; padding: 1rem 2rem;">
                    📝 Take Quiz in <?php echo htmlspecialchars($topic_name); ?>
                </a>
            </div>
            <?php endif; ?>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
