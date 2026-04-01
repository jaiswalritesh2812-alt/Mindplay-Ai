<?php
// Initialize session and require admin authentication
require_once __DIR__ . '/../config/session.php';
requireAdmin('../login.php');

require_once __DIR__ . '/../config/db.php';

// Get syllabus ID
$syllabus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($syllabus_id == 0) {
    header("Location: manage_subjects.php");
    exit();
}

// Get syllabus details with subject info
$stmt = $conn->prepare("SELECT sy.*, s.subject_name 
                        FROM syllabus sy 
                        JOIN subjects s ON sy.subject_id = s.id 
                        WHERE sy.id = ?");
$stmt->bind_param("i", $syllabus_id);
$stmt->execute();
$syllabus = $stmt->get_result()->fetch_assoc();

if(!$syllabus) {
    header("Location: manage_subjects.php");
    exit();
}

$success = "";
$error = "";

// Handle form submission
if(isset($_POST['update_syllabus'])) {
    $topic = trim($_POST['topic']);
    $content = trim($_POST['content']);
    
    if(empty($topic) || empty($content)) {
        $error = "All fields are required";
    } else {
        $update_stmt = $conn->prepare("UPDATE syllabus SET topic = ?, content = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $topic, $content, $syllabus_id);
        
        if($update_stmt->execute()) {
            $success = "Syllabus updated successfully!";
            // Refresh syllabus data
            $stmt->execute();
            $syllabus = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Failed to update syllabus";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Syllabus - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php 
    $base_path = '../';
    include __DIR__ . '/../includes/admin_navbar.php'; 
    ?>

    <div class="container">
        <div class="card fade-in">
            <div class="card-header">
                <div>
                    <h1 class="card-title">✏️ Edit Syllabus</h1>
                    <p class="card-subtitle">Subject: <?php echo htmlspecialchars($syllabus['subject_name']); ?></p>
                </div>
                <a href="manage_subjects.php" class="btn btn-secondary">← Back to Subjects</a>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-error">
                    ✗ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="editSyllabusForm">
                <div class="form-group">
                    <label class="form-label">Topic *</label>
                    <input type="text" name="topic" class="form-input" 
                           value="<?php echo htmlspecialchars($syllabus['topic']); ?>" 
                           required placeholder="e.g., Algebra, Mechanics, Organic Chemistry">
                </div>

                <div class="form-group">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-textarea" 
                              required placeholder="Enter detailed content for this topic..." 
                              rows="10"><?php echo htmlspecialchars($syllabus['content']); ?></textarea>
                    <small class="text-gray">Include key concepts, formulas, definitions, and examples</small>
                </div>

                <div class="flex gap-2">
                    <button type="submit" name="update_syllabus" class="btn btn-success">
                        💾 Update Syllabus
                    </button>
                    <a href="manage_subjects.php" class="btn btn-secondary">
                        ❌ Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
