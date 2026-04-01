<?php
// Initialize session and require admin authentication
require_once __DIR__ . '/../config/session.php';
requireAdmin('../login.php');

require_once __DIR__ . '/../config/db.php';

$success = "";
$error = "";
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle Delete Student
if($action == 'delete' && $student_id > 0){
    // Start transaction to ensure all related data is deleted
    $conn->begin_transaction();
    
    try {
        // Delete weak topics
        $stmt = $conn->prepare("DELETE FROM weak_topics WHERE user_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();

        // Delete login history
        $stmt = $conn->prepare("DELETE FROM login_history WHERE user_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        // Delete quiz attempts
        $stmt = $conn->prepare("DELETE FROM quiz_attempts WHERE user_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        // Delete results
        $stmt = $conn->prepare("DELETE FROM results WHERE user_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        // Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        $conn->commit();
        $success = "Student and all related data deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error deleting student: " . $e->getMessage();
    }
    
    $action = 'list';
}

// Handle Add/Edit Student Form Submission
if(isset($_POST['save_student'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    if(empty($name) || empty($email)){
        $error = "Name and email are required!";
    } else {
        if($edit_id > 0){
            // Update existing student
            if(!empty($password)){
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ? AND role = 'student'");
                $stmt->bind_param("sssi", $name, $email, $hashed_password, $edit_id);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'student'");
                $stmt->bind_param("ssi", $name, $email, $edit_id);
            }
            
            if($stmt->execute()){
                $success = "Student updated successfully!";
                $action = 'list';
            } else {
                $error = "Error updating student: " . $conn->error;
            }
        } else {
            // Add new student
            if(empty($password)){
                $error = "Password is required for new students!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $stmt->bind_param("sss", $name, $email, $hashed_password);
                
                if($stmt->execute()){
                    $success = "Student added successfully!";
                    $action = 'list';
                } else {
                    if(strpos($conn->error, 'Duplicate') !== false){
                        $error = "Email already exists!";
                    } else {
                        $error = "Error adding student: " . $conn->error;
                    }
                }
            }
        }
    }
}

// Get student details for editing
$edit_student = null;
if($action == 'edit' && $student_id > 0){
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $edit_student = $result->fetch_assoc();
    } else {
        $error = "Student not found!";
        $action = 'list';
    }
}

// Get all students with their quiz statistics
if($action == 'list'){
    $students_query = "
        SELECT 
            u.id,
            u.name,
            u.email,
            u.last_login,
            u.created_at,
            COUNT(DISTINCT r.id) as total_quizzes,
            COUNT(DISTINCT r.subject_id) as total_subjects,
            AVG(r.score) as avg_score,
            MAX(r.created_at) as last_quiz_date,
            (SELECT COUNT(*) FROM login_history WHERE user_id = u.id) as login_count
        FROM users u
        LEFT JOIN results r ON u.id = r.user_id
        WHERE u.role = 'student'
        GROUP BY u.id, u.name, u.email, u.last_login, u.created_at
        ORDER BY u.created_at DESC
    ";
    
    $students_result = $conn->query($students_query);
}

// Get detailed quiz history for a specific student
$quiz_history = [];
$login_history = [];
if($action == 'view' && $student_id > 0){
    $stmt = $conn->prepare("SELECT name, email, last_login, created_at FROM users WHERE id = ? AND role = 'student'");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student_info = $stmt->get_result()->fetch_assoc();
    
    if($student_info){
        // Get quiz history
        $history_query = "
            SELECT 
                r.id,
                r.score,
                r.time_taken,
                r.created_at,
                s.subject_name,
                sy.topic as syllabus_topic
            FROM results r
            JOIN subjects s ON r.subject_id = s.id
            LEFT JOIN syllabus sy ON r.syllabus_id = sy.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $conn->prepare($history_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($row = $result->fetch_assoc()){
            $quiz_history[] = $row;
        }
        
        // Get login history
        $login_query = "
            SELECT 
                login_time,
                ip_address,
                user_agent
            FROM login_history
            WHERE user_id = ?
            ORDER BY login_time DESC
            LIMIT 50
        ";
        
        $stmt = $conn->prepare($login_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($row = $result->fetch_assoc()){
            $login_history[] = $row;
        }
    } else {
        $error = "Student not found!";
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php 
    $base_path = '../';
    include __DIR__ . '/../includes/admin_navbar.php'; 
    ?>

    <div class="container">
        <!-- Header -->
        <div class="card fade-in">
            <div class="flex justify-between items-center" style="flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">
                        👥 Manage Students
                    </h1>
                    <p style="color: var(--gray);">View and manage all student accounts</p>
                </div>
                <?php if($action == 'list'): ?>
                <a href="?action=add" class="btn btn-success">
                    ➕ Add New Student
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success fade-in">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-error fade-in">
                ✗ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if($action == 'list'): ?>
            <!-- Statistics -->
            <?php
            $total_students = $students_result->num_rows;
            $active_students = 0;
            $total_quiz_attempts = 0;
            $students_result->data_seek(0);
            
            while($s = $students_result->fetch_assoc()){
                if($s['last_login']) $active_students++;
                $total_quiz_attempts += $s['total_quizzes'];
            }
            $students_result->data_seek(0);
            ?>
            
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $active_students; ?></div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_quiz_attempts; ?></div>
                    <div class="stat-label">Total Quiz Attempts</div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="card fade-in">
                <?php if($total_students > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th style="text-align: center;">Last Login</th>
                                <th style="text-align: center;">Total Logins</th>
                                <th style="text-align: center;">Quiz Attempts</th>
                                <th style="text-align: center;">Avg Score</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                    <?php if(!$student['last_login']): ?>
                                        <span class="badge" style="background: #fbbf24; color: #78350f; margin-left: 0.5rem; font-size: 0.75rem;">New</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td style="text-align: center; color: var(--gray);">
                                    <?php 
                                    if($student['last_login']){
                                        echo date('M d, Y H:i', strtotime($student['last_login']));
                                    } else {
                                        echo 'Never';
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 600; color: var(--secondary);">
                                        <?php echo $student['login_count']; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 600; color: var(--primary);">
                                        <?php echo $student['total_quizzes']; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <?php 
                                    if($student['total_quizzes'] > 0){
                                        echo '<span style="font-weight: 600; color: var(--secondary);">' . round($student['avg_score'], 1) . '%</span>';
                                    } else {
                                        echo '<span style="color: var(--gray);">N/A</span>';
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-buttons" style="justify-content: center;">
                                        <a href="?action=view&id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm">
                                            👁️ View
                                        </a>
                                        <a href="?action=edit&id=<?php echo $student['id']; ?>" class="btn btn-secondary btn-sm">
                                            ✏️ Edit
                                        </a>
                                        <a href="?action=delete&id=<?php echo $student['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center" style="padding: 3rem; color: var(--gray);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">👥</div>
                    <p style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">No students yet</p>
                    <p style="margin-bottom: 1.5rem;">Add your first student to get started!</p>
                    <a href="?action=add" class="btn btn-primary">
                        ➕ Add New Student
                    </a>
                </div>
                <?php endif; ?>
            </div>

        <?php elseif($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Student Form -->
            <div class="card fade-in">
                <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 1.5rem;">
                    <?php echo $action == 'edit' ? '✏️ Edit Student' : '➕ Add New Student'; ?>
                </h2>
                
                <form method="POST">
                    <?php if($action == 'edit'): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_student['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">👤 Full Name</label>
                        <input type="text" name="name" required class="form-input"
                            value="<?php echo $edit_student ? htmlspecialchars($edit_student['name']) : ''; ?>"
                            placeholder="Enter student's full name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📧 Email</label>
                        <input type="email" name="email" required class="form-input"
                            value="<?php echo $edit_student ? htmlspecialchars($edit_student['email']) : ''; ?>"
                            placeholder="Enter student's email">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            🔒 Password 
                            <?php if($action == 'edit'): ?>
                                <span style="color: var(--gray); font-weight: normal;">(Leave blank to keep current)</span>
                            <?php endif; ?>
                        </label>
                        <input type="password" name="password" class="form-input"
                            <?php echo $action == 'add' ? 'required' : ''; ?>
                            placeholder="Enter password">
                    </div>
                    
                    <div class="flex" style="gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" name="save_student" class="btn btn-success">
                            ✓ <?php echo $action == 'edit' ? 'Update Student' : 'Add Student'; ?>
                        </button>
                        <a href="?action=list" class="btn btn-secondary">
                            ← Back to List
                        </a>
                    </div>
                </form>
            </div>

        <?php elseif($action == 'view'): ?>
            <!-- View Student Details -->
            <div class="card fade-in">
                <div class="flex justify-between items-center" style="margin-bottom: 1.5rem;">
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                            <?php echo htmlspecialchars($student_info['name']); ?>
                        </h2>
                        <p style="color: var(--gray);"><?php echo htmlspecialchars($student_info['email']); ?></p>
                    </div>
                    <a href="?action=list" class="btn btn-secondary">
                        ← Back to List
                    </a>
                </div>
                
                <!-- Account Information -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                    <div>
                        <p style="font-size: 0.875rem; color: var(--gray); margin-bottom: 0.25rem;">📅 Registered</p>
                        <p style="font-weight: 600; color: var(--primary);">
                            <?php echo $student_info['created_at'] ? date('M d, Y', strtotime($student_info['created_at'])) : 'N/A'; ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; color: var(--gray); margin-bottom: 0.25rem;">🔐 Last Login</p>
                        <p style="font-weight: 600; color: var(--primary);">
                            <?php echo $student_info['last_login'] ? date('M d, Y H:i', strtotime($student_info['last_login'])) : 'Never'; ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; color: var(--gray); margin-bottom: 0.25rem;">🔢 Total Logins</p>
                        <p style="font-weight: 600; color: var(--secondary);">
                            <?php echo count($login_history); ?>
                        </p>
                    </div>
                </div>
                
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                    📊 Quiz History (<?php echo count($quiz_history); ?> attempts)
                </h3>
                
                <?php if(count($quiz_history) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Topic</th>
                                <th style="text-align: center;">Score</th>
                                <th style="text-align: center;">Time Taken</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($quiz_history as $quiz): ?>
                            <tr>
                                <td style="color: var(--gray);">
                                    <?php echo date('M d, Y H:i', strtotime($quiz['created_at'])); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($quiz['subject_name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo $quiz['syllabus_topic'] ? htmlspecialchars($quiz['syllabus_topic']) : '<span style="color: var(--gray);">General</span>'; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 700; color: var(--secondary); font-size: 1.125rem;">
                                        <?php echo round($quiz['score'], 1); ?>%
                                    </span>
                                </td>
                                <td style="text-align: center; color: var(--primary); font-weight: 600;">
                                    <?php 
                                    if($quiz['time_taken'] > 0){
                                        $minutes = floor($quiz['time_taken'] / 60);
                                        $seconds = $quiz['time_taken'] % 60;
                                        echo sprintf("%02d:%02d", $minutes, $seconds);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="../student/quiz_review.php?id=<?php echo $quiz['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        📝 Review
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center" style="padding: 2rem; color: var(--gray);">
                    <p>No quiz attempts yet</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Login History Section -->
            <div class="card fade-in" style="margin-top: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                    🔐 Login History (<?php echo count($login_history); ?> logins)
                </h3>
                
                <?php if(count($login_history) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Login Date & Time</th>
                                <th>IP Address</th>
                                <th>Device / Browser</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($login_history as $login): ?>
                            <tr>
                                <td style="color: var(--gray);">
                                    <strong><?php echo date('M d, Y H:i:s', strtotime($login['login_time'])); ?></strong>
                                </td>
                                <td>
                                    <span style="font-family: monospace; color: var(--primary);">
                                        <?php echo htmlspecialchars($login['ip_address']); ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.875rem; color: var(--gray);">
                                    <?php 
                                    $ua = htmlspecialchars($login['user_agent']);
                                    // Extract browser and OS info
                                    if(preg_match('/\((.*?)\)/', $ua, $matches)){
                                        echo $matches[1];
                                    } else {
                                        echo substr($ua, 0, 80) . (strlen($ua) > 80 ? '...' : '');
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center" style="padding: 2rem; color: var(--gray);">
                    <p>No login history available</p>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
