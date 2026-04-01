<?php
// Initialize session and require admin authentication
require_once __DIR__ . '/../config/session.php';
requireAdmin('../login.php');

require_once __DIR__ . '/../config/db.php';

$message = "";
$error = "";

if(isset($_POST['add'])){
    $subject = trim($_POST['subject']);
    
    if(!empty($subject)){
        $sql = "INSERT INTO subjects(subject_name) VALUES(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $subject);
        
        if($stmt->execute()){
            $message = "Subject added successfully!";
        } else {
            $error = "Failed to add subject";
        }
    } else {
        $error = "Please enter a subject name";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - MindPlay</title>
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
            <h1 class="card-header">Add New Subject</h1>
            
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
                    <label class="form-label">📚 Subject Name</label>
                    <input type="text" name="subject" required class="form-input"
                        placeholder="e.g., Mathematics, Physics, Chemistry">
                </div>
                
                <button type="submit" name="add" class="btn btn-primary btn-block">
                    ➕ Add Subject
                </button>
            </form>

            <!-- Existing Subjects List -->
            <div class="mt-3">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Existing Subjects</h2>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php
                    $result = $conn->query("SELECT * FROM subjects ORDER BY subject_name");
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $subject_name = htmlspecialchars($row['subject_name']);
                            $subject_id = intval($row['id']);
                            echo "<div style='background: var(--light); padding: 1rem; border-radius: var(--radius); display: flex; justify-content: space-between; align-items: center;'>
                                <span style='font-weight: 600;'>{$subject_name}</span>
                                <a href='add_syllabus.php?subject_id={$subject_id}' class='btn btn-primary btn-sm'>Add Syllabus →</a>
                            </div>";
                        }
                    } else {
                        echo "<p style='color: var(--gray);'>No subjects added yet</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
