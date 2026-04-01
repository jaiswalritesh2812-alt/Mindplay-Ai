<!-- Student Navigation Component -->
<nav class="navbar">
    <div class="nav-content">
        <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>student/dashboard.php" ><img src="<?php echo htmlspecialchars($base_path ?? '../'); ?>assets/logo.png" alt="MindPlay Logo" style="height: 50px;"></a>
        <div class="nav-links">
            <span style="color: white; opacity: 0.9;">👤 <?php echo htmlspecialchars(currentUserName()); ?></span>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>student/dashboard.php" class="nav-link">🏠 Dashboard</a>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>student/ai_learning.php" class="nav-link">🧠 AI Learning</a>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>student/leaderboard.php" class="nav-link">🏆 Leaderboard</a>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>logout.php" class="nav-link btn-danger">🚪 Logout</a>
        </div>
    </div>
</nav>
