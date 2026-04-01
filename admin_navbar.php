<!-- Admin Navigation Component -->
<nav class="navbar">
    <div class="nav-content">
        <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>admin/dashboard.php"><img src="<?php echo htmlspecialchars($base_path ?? '../'); ?>assets/logo.png" alt="MindPlay Logo" style="height: 50px;"></a>
        <div class="nav-links">
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>admin/dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>admin/manage_subjects.php" class="nav-link">📋 Manage</a>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>admin/manage_students.php" class="nav-link">👥 Students</a>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>student/leaderboard.php" class="nav-link">🏆 Leaderboard</a>
            <a href="<?php echo htmlspecialchars($base_path ?? '../'); ?>logout.php" class="nav-link btn-danger">🚪 Logout</a>
        </div>
    </div>
</nav>
