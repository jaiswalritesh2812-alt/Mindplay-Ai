<?php
// Initialize session and require admin authentication
require_once __DIR__ . '/../config/session.php';
requireAdmin('../login.php');

require_once __DIR__ . '/../config/db.php';

$message = "";
$error = "";
$selected_subject = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : "";

if(isset($_POST['save'])){
    $subject_id = intval($_POST['sid']);
    $topic = trim($_POST['topic']);
    $content = trim($_POST['content']);
    
    if(!empty($subject_id) && !empty($topic) && !empty($content)){
        $sql = "INSERT INTO syllabus(subject_id, topic, content) VALUES(?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $subject_id, $topic, $content);
        
        if($stmt->execute()){
            $message = "Syllabus topic added successfully!";
        } else {
            $error = "Failed to add syllabus topic";
        }
    } else {
        $error = "Please fill all fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Syllabus - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php 
    $base_path = '../';
    include __DIR__ . '/../includes/admin_navbar.php'; 
    ?>

    <div class="container-md">
        <div class="card fade-in">
            <h1 class="card-header">Add Syllabus Topic</h1>
            
            <?php if($message): ?>
                <div class="alert alert-success">
                    ✓ <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    ✗ <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">📚 Select Subject</label>
                    <select name="sid" required class="form-select">
                        <option value="">Choose a subject</option>
                        <?php
                        $subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_name");
                        while($subject = $subjects->fetch_assoc()){
                            $selected = ($subject['id'] == $selected_subject) ? 'selected' : '';
                            $subject_name = htmlspecialchars($subject['subject_name']);
                            $subject_id = intval($subject['id']);
                            echo "<option value='{$subject_id}' {$selected}>{$subject_name}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">📝 Topic Name</label>
                    <input type="text" name="topic" required class="form-input"
                        placeholder="e.g., Quadratic Equations, Newton's Laws">
                </div>
                
                <div class="form-group">
                    <label class="form-label">📚 Topic Content</label>
                    <textarea name="content" required rows="8" class="form-textarea"
                        placeholder="Enter detailed content about this topic. This will be used to generate AI questions."></textarea>
                    <p class="form-hint">Provide comprehensive content for better AI question generation</p>
                </div>
                
                <button type="submit" name="save" class="btn btn-primary btn-block">
                    💾 Save Syllabus Topic
                </button>
            </form>

            <!-- Recent Syllabus Topics -->
            <div class="mt-3">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Recent Syllabus Topics</h2>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php
                    $result = $conn->query("SELECT sy.*, s.subject_name FROM syllabus sy 
                                          JOIN subjects s ON sy.subject_id = s.id 
                                          ORDER BY sy.id DESC LIMIT 5");
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $subject_name = htmlspecialchars($row['subject_name']);
                            $topic = htmlspecialchars($row['topic']);
                            $syllabus_id = intval($row['id']);
                            echo "<div style='background: var(--light); padding: 1rem; border-radius: var(--radius);'>
                                <div class='flex justify-between items-center'>
                                    <div>
                                        <p style='font-weight: 600; color: var(--primary); margin-bottom: 0.25rem;'>{$subject_name}</p>
                                        <p style='color: var(--dark);'>{$topic}</p>
                                    </div>
                                    <a href='generate_questions.php?syllabus_id={$syllabus_id}' class='btn btn-success btn-sm'>
                                        🤖 Generate Questions
                                    </a>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<p style='color: var(--gray);'>No syllabus topics added yet</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
