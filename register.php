<?php
// Initialize session
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';

$error = "";
$success = "";

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if($password !== $confirm_password){
        $error = "Passwords do not match";
    } else if(strlen($password) < 6){
        $error = "Password must be at least 6 characters";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $error = "Email already registered";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if($stmt->execute()){
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>Register - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="card" style="max-width: 500px; width: 90%; margin: 2rem;">
        <div class="text-center mb-3">
            <img src="assets/logo.png" alt="MindPlay Logo" style="height: 60px; margin: 0 auto 1rem;">
            <p class="card-subtitle">Create Your Account</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                ✗ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">👤 Full Name</label>
                <input type="text" name="name" required class="form-input"
                    placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label class="form-label">📧 Email</label>
                <input type="email" name="email" required class="form-input"
                    placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label class="form-label">🔒 Password</label>
                <input type="password" name="password" required class="form-input"
                    placeholder="Enter your password (min 6 characters)">
            </div>
            
            <div class="form-group">
                <label class="form-label">🔒 Confirm Password</label>
                <input type="password" name="confirm_password" required class="form-input"
                    placeholder="Confirm your password">
            </div>
            
            <button type="submit" name="register" class="btn btn-success w-full">
                ✨ Create Account
            </button>
        </form>
        
        <div class="mt-3 text-center">
            <p style="color: var(--gray);">Already have an account? 
                <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Login here</a>
            </p>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
</html>