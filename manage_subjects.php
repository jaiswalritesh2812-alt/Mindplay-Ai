<?php
// Initialize session and require admin authentication
require_once __DIR__ . '/../config/session.php';
requireAdmin('../login.php');

require_once __DIR__ . '/../config/db.php';

$message = "";
$error = "";

// Handle subject deletion
if(isset($_GET['delete_subject']) && isset($_GET['id'])){
    $subject_id = intval($_GET['id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete quiz attempts for questions related to this subject
        $stmt = $conn->prepare("DELETE qa FROM quiz_attempts qa 
                                INNER JOIN questions q ON qa.question_id = q.id 
                                INNER JOIN syllabus sy ON q.syllabus_id = sy.id 
                                WHERE sy.subject_id = ?");
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        // Delete results for this subject
        $stmt = $conn->prepare("DELETE FROM results WHERE subject_id = ?");
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        // Delete weak topics for this subject
        $stmt = $conn->prepare("DELETE wt FROM weak_topics wt 
                                INNER JOIN syllabus sy ON wt.syllabus_id = sy.id 
                                WHERE sy.subject_id = ?");
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        // Delete questions for this subject
        $stmt = $conn->prepare("DELETE q FROM questions q 
                                INNER JOIN syllabus sy ON q.syllabus_id = sy.id 
                                WHERE sy.subject_id = ?");
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        // Delete syllabus for this subject
        $stmt = $conn->prepare("DELETE FROM syllabus WHERE subject_id = ?");
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        // Finally delete the subject
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        $conn->commit();
        $message = "Subject and all related data deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to delete subject: " . $e->getMessage();
    }
}

// Handle syllabus deletion
if(isset($_GET['delete_syllabus']) && isset($_GET['syllabus_id'])){
    $syllabus_id = intval($_GET['syllabus_id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete quiz attempts for questions related to this syllabus
        $stmt = $conn->prepare("DELETE qa FROM quiz_attempts qa 
                                INNER JOIN questions q ON qa.question_id = q.id 
                                WHERE q.syllabus_id = ?");
        $stmt->bind_param("i", $syllabus_id);
        $stmt->execute();
        
        // Delete results for this syllabus
        $stmt = $conn->prepare("DELETE FROM results WHERE syllabus_id = ?");
        $stmt->bind_param("i", $syllabus_id);
        $stmt->execute();
        
        // Delete weak topics for this syllabus
        $stmt = $conn->prepare("DELETE FROM weak_topics WHERE syllabus_id = ?");
        $stmt->bind_param("i", $syllabus_id);
        $stmt->execute();
        
        // Delete questions for this syllabus
        $stmt = $conn->prepare("DELETE FROM questions WHERE syllabus_id = ?");
        $stmt->bind_param("i", $syllabus_id);
        $stmt->execute();
        
        // Finally delete the syllabus
        $stmt = $conn->prepare("DELETE FROM syllabus WHERE id = ?");
        $stmt->bind_param("i", $syllabus_id);
        $stmt->execute();
        
        $conn->commit();
        $message = "Syllabus topic deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to delete syllabus: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php 
    $base_path = '../';
    include __DIR__ . '/../includes/admin_navbar.php'; 
    ?>

    <div class="container">
        <?php if($message): ?>
            <div class="alert-card success-alert" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 2px solid #34d399; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); animation: slideDown 0.4s ease-out;">
                <div style="display: flex; align-items: flex-start; gap: 1rem;">
                    <div style="background: #10b981; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">✓</div>
                    <div style="flex: 1; padding-top: 0.25rem;">
                        <h3 style="color: #059669; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Success</h3>
                        <p style="color: #065f46; margin-bottom: 0; font-size: 1rem; line-height: 1.5;"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                    <button onclick="this.closest('.alert-card').remove()" style="background: #10b981; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">✕</button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert-card error-alert" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 2px solid #f87171; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15); animation: slideDown 0.4s ease-out;">
                <div style="display: flex; align-items: flex-start; gap: 1rem;">
                    <div style="background: #dc2626; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.5rem; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);">❌</div>
                    <div style="flex: 1; padding-top: 0.25rem;">
                        <h3 style="color: #dc2626; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem; margin-top: 0;">Error</h3>
                        <p style="color: #991b1b; margin-bottom: 0; font-size: 1rem; line-height: 1.5;"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                    <button onclick="this.closest('.alert-card').remove()" style="background: #dc2626; border: none; color: white; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">✕</button>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card fade-in">
            <div class="card-header" style="border-bottom: 3px solid var(--primary); margin-bottom: 2rem;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">📚 Manage Subjects & Topics</h1>
                    <p style="color: var(--gray); font-size: 1rem;">Create, edit, and organize your quiz subjects and syllabus topics</p>
                </div>
                <div class="action-buttons">
                    <a href="add_subject.php" class="btn btn-primary" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                        ➕ Add Subject
                    </a>
                </div>
            </div>
        
        <?php
        $subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_name");
        
        if($subjects->num_rows > 0){
            while($subject = $subjects->fetch_assoc()){
                // Get syllabus count
                $stmt_syl = $conn->prepare("SELECT COUNT(*) as count FROM syllabus WHERE subject_id = ?");
                $stmt_syl->bind_param("i", $subject['id']);
                $stmt_syl->execute();
                $syllabus_count = $stmt_syl->get_result()->fetch_assoc()['count'];
                
                $stmt_q = $conn->prepare("SELECT COUNT(*) as count FROM questions q JOIN syllabus sy ON q.syllabus_id = sy.id WHERE sy.subject_id = ?");
                $stmt_q->bind_param("i", $subject['id']);
                $stmt_q->execute();
                $questions_count = $stmt_q->get_result()->fetch_assoc()['count'];
                
                echo "<div class='card mb-3' style='border: 2px solid var(--gray-light); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s;' onmouseover='this.style.boxShadow=\"0 8px 20px rgba(0,0,0,0.12)\"' onmouseout='this.style.boxShadow=\"0 4px 12px rgba(0,0,0,0.08)\"'>
                    <div class='card-header' style='background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-bottom: 2px solid var(--gray-light); padding: 1.5rem;'>
                        <div>
                            <h2 style='font-size: 1.5rem; font-weight: 700; color: var(--dark); margin-bottom: 0.75rem;'>{$subject['subject_name']}</h2>
                            <div style='display: flex; gap: 1.5rem; align-items: center;'>
                                <span style='background: #dbeafe; color: #1e40af; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem;'>
                                    📑 {$syllabus_count} Topics
                                </span>
                                <span style='background: #fef3c7; color: #92400e; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem;'>
                                    ❓ {$questions_count} Questions
                                </span>
                            </div>
                        </div>
                        <div class='action-buttons' style='gap: 0.75rem;'>
                            <a href='add_syllabus.php?subject_id={$subject['id']}' 
                               class='btn btn-primary btn-sm' style='padding: 0.625rem 1.25rem;'>
                                ➕ Add Topic
                            </a>
                            <a href='edit_subject.php?id={$subject['id']}' 
                               class='btn btn-info btn-sm' style='padding: 0.625rem 1.25rem;'>
                                ✏️ Edit
                            </a>
                            <a href='manage_subjects.php?delete_subject=1&id={$subject['id']}' 
                               onclick='return confirmDelete(\"⚠️ Delete this subject? This will remove ALL topics, questions, and student results!\")' 
                               class='btn btn-danger btn-sm' style='padding: 0.625rem 1.25rem;'>
                                🗑️ Delete
                            </a>
                        </div>
                    </div>
                    
                    <div style='padding: 1.5rem;'>
                        <h3 style='font-weight: 600; color: var(--dark); margin-bottom: 1.25rem; font-size: 1.125rem;'>📚 Syllabus Topics</h3>";
                
                $stmt_list = $conn->prepare("SELECT * FROM syllabus WHERE subject_id = ? ORDER BY topic");
                $stmt_list->bind_param("i", $subject['id']);
                $stmt_list->execute();
                $syllabus_list = $stmt_list->get_result();
                
                if($syllabus_list->num_rows > 0){
                    echo "<div class='table-container'>
                            <table class='table'>
                                <thead>
                                    <tr>
                                        <th>Topic</th>
                                        <th>Questions</th>
                                        <th>Content Preview</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>";
                    
                    while($syl = $syllabus_list->fetch_assoc()){
                        $stmt_qc = $conn->prepare("SELECT COUNT(*) as count FROM questions WHERE syllabus_id = ?");
                        $stmt_qc->bind_param("i", $syl['id']);
                        $stmt_qc->execute();
                        $q_count = $stmt_qc->get_result()->fetch_assoc()['count'];
                        $content_preview = substr($syl['content'], 0, 100);
                        if(strlen($syl['content']) > 100) $content_preview .= '...';
                        
                        echo "<tr style='border-bottom: 1px solid var(--gray-light);'>
                            <td style='padding: 1rem;'><strong style='color: var(--dark);'>" . htmlspecialchars($syl['topic']) . "</strong></td>
                            <td style='padding: 1rem;'><span class='badge badge-primary' style='padding: 0.5rem 1rem; border-radius: 8px;'>{$q_count} questions</span></td>
                            <td style='padding: 1rem;'><small style='color: var(--gray);'>" . htmlspecialchars($content_preview) . "</small></td>
                            <td style='padding: 1rem;'>
                                <div class='action-buttons' style='gap: 0.5rem; justify-content: flex-end;'>
                                    <a href='generate_questions.php?syllabus_id={$syl['id']}' 
                                       class='btn btn-success btn-sm' title='Generate AI Questions'>🤖</a>
                                    <a href='edit_syllabus.php?id={$syl['id']}' 
                                       class='btn btn-info btn-sm' title='Edit Topic'>✏️</a>
                                    <a href='manage_subjects.php?delete_syllabus=1&syllabus_id={$syl['id']}' 
                                       onclick='return confirmDelete(\"⚠️ Delete this topic and all questions?\")' 
                                       class='btn btn-danger btn-sm' title='Delete Topic'>🗑️</a>
                                </div>
                            </td>
                        </tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<div style='text-align: center; padding: 2.5rem; background: var(--light); border-radius: 8px;'>
                            <div style='font-size: 2.5rem; margin-bottom: 0.75rem;'>📝</div>
                            <p style='color: var(--gray); font-size: 1rem; margin-bottom: 1rem;'>No topics yet</p>
                            <a href='add_syllabus.php?subject_id={$subject['id']}' class='btn btn-primary btn-sm'>➕ Add First Topic</a>
                          </div>";
                }
                
                echo "</div></div>";
            }
        } else {
            echo "<div class='text-center' style='padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);'>
                <div style='font-size: 5rem; margin-bottom: 1.5rem;'>📚</div>
                <h2 style='color: var(--dark); font-size: 1.75rem; margin-bottom: 0.75rem;'>No Subjects Yet</h2>
                <p style='color: var(--gray); font-size: 1rem; margin-bottom: 2rem;'>Create your first subject to start building quizzes</p>
                <a href='add_subject.php' class='btn btn-primary' style='padding: 1rem 2rem; font-size: 1.125rem;'>➕ Create First Subject</a>
            </div>";
        }
        ?>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert-card').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
