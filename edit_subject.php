<?php
// Initialize session and require admin authentication
require_once __DIR__ . '/../config/session.php';
requireAdmin('../login.php');

require_once __DIR__ . '/../config/db.php';

// Get subject ID
$subject_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($subject_id == 0) {
    header("Location: manage_subjects.php");
    exit();
}

// Get subject details
$stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc();

if(!$subject) {
    header("Location: manage_subjects.php");
    exit();
}

$success = "";
$error = "";

// Handle form submission
if(isset($_POST['update_subject'])) {
    $subject_name = trim($_POST['subject_name']);
    $description = trim($_POST['description']);
    
    if(empty($subject_name)) {
        $error = "Subject name is required";
    } else {
        // Check if name already exists (excluding current subject)
        $check_stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_name = ? AND id != ?");
        $check_stmt->bind_param("si", $subject_name, $subject_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $error = "A subject with this name already exists";
        } else {
            $update_stmt = $conn->prepare("UPDATE subjects SET subject_name = ?, description = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $subject_name, $description, $subject_id);
            
            if($update_stmt->execute()) {
                $success = "Subject updated successfully!";
                // Refresh subject data
                $stmt->execute();
                $subject = $stmt->get_result()->fetch_assoc();
            } else {
                $error = "Failed to update subject";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject - MindPlay</title>
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
                    <h1 class="card-title">✏️ Edit Subject</h1>
                    <p class="card-subtitle">Update subject information</p>
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

            <form method="POST" id="editSubjectForm">
                <div class="form-group">
                    <label class="form-label">Subject Name *</label>
                    <input type="text" name="subject_name" class="form-input" 
                           value="<?php echo htmlspecialchars($subject['subject_name']); ?>" 
                           required placeholder="e.g., Mathematics, Physics, Chemistry">
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" 
                              placeholder="Enter subject description (optional)"><?php echo htmlspecialchars($subject['description']); ?></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit" name="update_subject" class="btn btn-primary">
                        💾 Update Subject
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
