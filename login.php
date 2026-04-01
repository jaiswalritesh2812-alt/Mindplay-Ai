<?php
// Initialize session
require_once __DIR__ . '/config/session.php';

// If already logged in, redirect to dashboard
if(isLoggedIn()){
    redirectToDashboard();
}

require_once __DIR__ . '/config/db.php';

$error = "";

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)){
        $error = "Please enter both email and password";
    } else {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            
            // Verify password using proper hash function
            if(password_verify($password, $user['password'])){
                // Update last_login timestamp
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                // Track login in history
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $history_stmt = $conn->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
                $history_stmt->bind_param("iss", $user['id'], $ip_address, $user_agent);
                $history_stmt->execute();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Force session write before redirect
                session_write_close();
                
                if($user['role'] == 'admin'){
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: student/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "No user found with this email";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="card" style="max-width: 500px; width: 90%; margin: 2rem;">
        <div class="text-center mb-3">
            <img src="assets/logo.png" alt="MindPlay Logo" style="height: 60px; margin: 0 auto 1rem;">
         
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                ✗ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">📧 Email</label>
                <input type="email" name="email" required class="form-input"
                    placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label class="form-label">🔒 Password</label>
                <input type="password" name="password" required class="form-input"
                    placeholder="Enter your password">
            </div>
            
            <button type="submit" name="login" class="btn btn-primary w-full">
                🚀 Login
            </button>
        </form>
        
        <div class="mt-3 text-center">
            <p style="color: var(--gray);">Don't have an account? 
                <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Register here</a>
            </p>
        </div>
        
        <div class="mt-3" style="padding: 1rem; background: #dbeafe; border-radius: var(--radius); font-size: 0.875rem;">
            <p style="font-weight: 600; color: #1e40af;">💡 Demo Credentials:</p>
            <p style="color: var(--gray);">Admin: admin@mindplay.com / admin123</p>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>