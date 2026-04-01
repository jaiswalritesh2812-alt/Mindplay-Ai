<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

$user_id = intval(currentUserId() ?? 0);
$syllabus_id = isset($_GET['syllabus_id']) ? intval($_GET['syllabus_id']) : 0;

if($syllabus_id <= 0){
    header("Location: dashboard.php?error=Invalid topic");
    exit();
}

// Get syllabus details
$stmt = $conn->prepare("SELECT sy.*, s.subject_name, s.id as subject_id 
                        FROM syllabus sy 
                        JOIN subjects s ON sy.subject_id = s.id 
                        WHERE sy.id = ?");
$stmt->bind_param("i", $syllabus_id);
$stmt->execute();
$syllabus = $stmt->get_result()->fetch_assoc();

if(!$syllabus){
    header("Location: dashboard.php?error=Topic not found");
    exit();
}

// Check if summary exists
$stmt = $conn->prepare("SELECT summary, generated_at FROM topic_summaries WHERE syllabus_id = ? AND user_id = ?");
$stmt->bind_param("ii", $syllabus_id, $user_id);
$stmt->execute();
$summary_data = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($syllabus['topic']); ?> - Summary - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .summary-content {
            line-height: 1.8;
            color: var(--dark);
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .summary-content h3 {
            color: var(--primary);
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: 700;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e0f2fe;
        }
        .summary-content h4 {
            color: var(--secondary);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        .summary-content ul {
            margin-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .summary-content li {
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }
        .summary-content p {
            margin-bottom: 1.25rem;
            line-height: 1.8;
        }
        .summary-content strong {
            color: var(--primary);
            font-weight: 600;
        }
        .summary-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            text-align: center;
        }
        .summary-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            color: white;
        }
        .summary-header p {
            font-size: 1.25rem;
            opacity: 0.95;
        }
        .action-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .action-bar .btn {
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .summary-card {
            animation: slideIn 0.6s ease-out;
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            word-wrap: break-word;
            position: relative;
        }
        .summary-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid var(--primary);
            border-radius: 9999px;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        .no-summary-container {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 16px;
            border: 2px dashed #60a5fa;
        }
        .no-summary-container .icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .no-summary-container h3 {
            color: var(--dark);
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        .no-summary-container p {
            color: var(--gray);
            font-size: 1.125rem;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .loading-spinner {
            position: relative;
        }
        .status-step {
            transition: all 0.3s ease;
        }
        .status-step .step-icon {
            display: inline-block;
            min-width: 20px;
            text-align: center;
        }
        @keyframes checkmark {
            0% { transform: scale(0.5) rotate(-45deg); }
            50% { transform: scale(1.1) rotate(5deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        .progress-bar-fill {
            position: relative;
        }
        @keyframes sparkle {
            0% {
                transform: translateY(0) scale(0) rotate(0deg);
                opacity: 1;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(60vh) scale(1) rotate(360deg);
                opacity: 0;
            }
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php 
    $base_path = '../';
    $show_leaderboard = false;
    include __DIR__ . '/../includes/student_navbar.php'; 
    ?>

    <div class="container">
        <div class="summary-header">
            <h1>📚 <?php echo htmlspecialchars($syllabus['topic']); ?></h1>
            <p><?php echo htmlspecialchars($syllabus['subject_name']); ?></p>
        </div>

        <div class="action-bar">
            <a href="dashboard.php" class="btn btn-primary">
                ← Back to Dashboard
            </a>
            <a href="quiz.php?subject_id=<?php echo $syllabus['subject_id']; ?>&syllabus_id=<?php echo $syllabus_id; ?>" class="btn btn-success">
                🎯 Take Quiz
            </a>
            <?php if($summary_data): ?>
            <button onclick="regenerateSummary()" class="btn btn-info" id="regenerateBtn">
                <span id="regenerateBtnIcon">🔄</span>
                <span id="regenerateBtnText">Regenerate</span>
            </button>
            <button onclick="deleteSummary()" class="btn btn-danger" id="deleteBtn" style="background: var(--danger);">
                <span id="deleteBtnIcon">🗑️</span>
                <span id="deleteBtnText">Delete</span>
            </button>
            <?php endif; ?>
        </div>

        <div class="card summary-card">
            <div id="summaryContainer">
                <?php if($summary_data): ?>
                    <div class="summary-badge">
                        <span>✨</span>
                        <span>AI-Generated Summary</span>
                    </div>
                    <div class="summary-content">
                        <?php echo $summary_data['summary']; ?>
                    </div>
                    <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 2px solid var(--gray-light); color: var(--gray); font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🕐</span>
                        <span>Generated on <?php echo date('F j, Y \a\t g:i A', strtotime($summary_data['generated_at'])); ?></span>
                    </div>
                <?php else: ?>
                    <div id="noSummary" class="no-summary-container">
                        <div class="icon">🤖</div>
                        <h3>Ready to Learn?</h3>
                        <p>
                            Generate an AI-powered summary of this topic with key concepts, important points, and study tips!
                        </p>
                        <button onclick="generateSummary()" id="generateBtn" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.125rem;">
                            <span id="btnIcon">✨</span>
                            <span id="btnText">Generate Summary with AI</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="ai-generating-badge" style="margin-bottom: 1.5rem;">
                <span class="ai-icon">🤖</span>
                <span id="aiStatusBadge">AI is Analyzing</span>
            </div>
            <div class="loading-spinner loading-spinner-lg" style="margin: 0 auto;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem;" id="spinnerEmoji">📚</div>
            </div>
            <div class="generating-text" id="generatingText">Analyzing Content</div>
            <p style="color: var(--gray); margin-top: 1rem; margin-bottom: 1.5rem;" id="statusMessage">
                Reading and understanding the topic...
            </p>
            <div class="progress-bar">
                <div class="progress-bar-fill" id="progressBarFill" style="width: 0%;">
                    <span style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: white; font-size: 0.75rem; font-weight: 600;" id="progressPercent">0%</span>
                </div>
            </div>
            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <div class="status-step" id="step1" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem;">
                    <span class="step-icon">⏳</span>
                    <span>Reading topic content...</span>
                </div>
                <div class="status-step" id="step2" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem; opacity: 0.5;">
                    <span class="step-icon">⏳</span>
                    <span>Extracting key concepts...</span>
                </div>
                <div class="status-step" id="step3" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem; opacity: 0.5;">
                    <span class="step-icon">⏳</span>
                    <span>Creating comprehensive summary...</span>
                </div>
                <div class="status-step" id="step4" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem; opacity: 0.5;">
                    <span class="step-icon">⏳</span>
                    <span>Adding study tips...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const syllabusId = <?php echo $syllabus_id; ?>;
        
        function generateSummary() {
            // Update button state
            const btn = document.getElementById('generateBtn');
            const btnIcon = document.getElementById('btnIcon');
            const btnText = document.getElementById('btnText');
            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.7';
                btn.style.cursor = 'not-allowed';
                btnIcon.textContent = '⏳';
                btnText.textContent = 'Generating with AI...';
            }
            
            showLoading();
            
            fetch('generate_summary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'syllabus_id=' + syllabusId
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if(data.success) {
                    displaySummary(data.summary, data.cached);
                } else {
                    showError(data.error || 'Failed to generate summary');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Network error. Please try again.');
                console.error('Error:', error);
            });
        }
        
        function regenerateSummary() {
            if(confirm('Are you sure you want to regenerate the summary? This will replace the current summary.')) {
                // Update regenerate button state
                const btn = document.getElementById('regenerateBtn');
                const btnIcon = document.getElementById('regenerateBtnIcon');
                const btnText = document.getElementById('regenerateBtnText');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                    btnIcon.textContent = '⏳';
                    btnText.textContent = 'Regenerating...';
                }
                
                generateSummary();
            }
        }
        
        function deleteSummary() {
            if(confirm('Are you sure you want to delete this summary? This action cannot be undone.')) {
                // Update delete button state
                const btn = document.getElementById('deleteBtn');
                const btnIcon = document.getElementById('deleteBtnIcon');
                const btnText = document.getElementById('deleteBtnText');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                    btnIcon.textContent = '⏳';
                    btnText.textContent = 'Deleting...';
                }
                
                fetch('delete_summary.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'syllabus_id=' + syllabusId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        showSuccess('Summary deleted successfully!');
                        // Reload page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError(data.error || 'Failed to delete summary');
                        // Reset button on error
                        if (btn) {
                            btn.disabled = false;
                            btn.style.opacity = '1';
                            btn.style.cursor = 'pointer';
                            btnIcon.textContent = '🗑️';
                            btnText.textContent = 'Delete';
                        }
                    }
                })
                .catch(error => {
                    showError('Network error. Please try again.');
                    console.error('Error:', error);
                    // Reset button on error
                    if (btn) {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                        btnIcon.textContent = '🗑️';
                        btnText.textContent = 'Delete';
                    }
                });
            }
        }
        
        function resetButton() {
            // Reset generate button
            const btn = document.getElementById('generateBtn');
            const btnIcon = document.getElementById('btnIcon');
            const btnText = document.getElementById('btnText');
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btnIcon.textContent = '✨';
                btnText.textContent = 'Generate Summary with AI';
            }
            
            // Reset regenerate button
            const regenerateBtn = document.getElementById('regenerateBtn');
            const regenerateBtnIcon = document.getElementById('regenerateBtnIcon');
            const regenerateBtnText = document.getElementById('regenerateBtnText');
            if (regenerateBtn) {
                regenerateBtn.disabled = false;
                regenerateBtn.style.opacity = '1';
                regenerateBtn.style.cursor = 'pointer';
                regenerateBtnIcon.textContent = '🔄';
                regenerateBtnText.textContent = 'Regenerate';
            }
            
            // Reset delete button
            const deleteBtn = document.getElementById('deleteBtn');
            const deleteBtnIcon = document.getElementById('deleteBtnIcon');
            const deleteBtnText = document.getElementById('deleteBtnText');
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.style.opacity = '1';
                deleteBtn.style.cursor = 'pointer';
                deleteBtnIcon.textContent = '🗑️';
                deleteBtnText.textContent = 'Delete';
            }
        }
        
        function displaySummary(summary, cached) {
            const container = document.getElementById('summaryContainer');
            const timestamp = new Date().toLocaleString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            });
            
            container.innerHTML = `
                <div class="summary-badge">
                    <span>✨</span>
                    <span>AI-Generated Summary</span>
                </div>
                <div class="summary-content">
                    ${summary}
                </div>
                <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 2px solid var(--gray-light); color: var(--gray); font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🕐</span>
                    <span>${cached ? 'Retrieved' : 'Generated'} on ${timestamp}</span>
                </div>
            `;
            
            // Show regenerate and delete buttons if they don't exist
            const actionBar = document.querySelector('.action-bar');
            if (actionBar && !document.getElementById('regenerateBtn')) {
                const regenerateBtn = document.createElement('button');
                regenerateBtn.onclick = regenerateSummary;
                regenerateBtn.className = 'btn btn-info';
                regenerateBtn.id = 'regenerateBtn';
                regenerateBtn.innerHTML = '<span id="regenerateBtnIcon">🔄</span> <span id="regenerateBtnText">Regenerate</span>';
                actionBar.appendChild(regenerateBtn);
                
                const deleteBtn = document.createElement('button');
                deleteBtn.onclick = deleteSummary;
                deleteBtn.className = 'btn btn-danger';
                deleteBtn.id = 'deleteBtn';
                deleteBtn.style.background = 'var(--danger)';
                deleteBtn.innerHTML = '<span id="deleteBtnIcon">🗑️</span> <span id="deleteBtnText">Delete</span>';
                actionBar.appendChild(deleteBtn);
            }
            
            // Show success message with celebration
            if(!cached) {
                showSuccess('Summary generated successfully!');
                showCelebration();
            }
        }
        
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Animate progress bar with realistic stages
            let progress = 0;
            const progressBar = document.getElementById('progressBarFill');
            const progressPercent = document.getElementById('progressPercent');
            const generatingText = document.getElementById('generatingText');
            const statusMessage = document.getElementById('statusMessage');
            const aiStatusBadge = document.getElementById('aiStatusBadge');
            const spinnerEmoji = document.getElementById('spinnerEmoji');
            
            const stages = [
                { progress: 25, text: 'Analyzing Content', message: 'Reading and understanding the topic...', badge: 'AI is Analyzing', emoji: '📚', step: 1 },
                { progress: 50, text: 'Extracting Key Concepts', message: 'Identifying main ideas and concepts...', badge: 'Processing Content', emoji: '🔍', step: 2 },
                { progress: 75, text: 'Creating Summary', message: 'Writing comprehensive explanations...', badge: 'Generating Summary', emoji: '✍️', step: 3 },
                { progress: 90, text: 'Finalizing', message: 'Adding study tips and examples...', badge: 'Almost Ready', emoji: '✨', step: 4 }
            ];
            
            let currentStage = 0;
            
            const interval = setInterval(() => {
                if (currentStage < stages.length && progress < stages[currentStage].progress) {
                    progress += Math.random() * 3 + 1;
                    if (progress >= stages[currentStage].progress) {
                        progress = stages[currentStage].progress;
                        generatingText.textContent = stages[currentStage].text;
                        statusMessage.textContent = stages[currentStage].message;
                        aiStatusBadge.textContent = stages[currentStage].badge;
                        spinnerEmoji.textContent = stages[currentStage].emoji;
                        
                        // Update step status
                        const stepEl = document.getElementById('step' + stages[currentStage].step);
                        if (stepEl) {
                            stepEl.style.opacity = '1';
                            stepEl.style.color = 'var(--secondary)';
                            stepEl.querySelector('.step-icon').textContent = '✓';
                        }
                        
                        // Activate next step
                        if (currentStage + 1 < 4) {
                            const nextStep = document.getElementById('step' + (stages[currentStage].step + 1));
                            if (nextStep) nextStep.style.opacity = '1';
                        }
                        
                        currentStage++;
                    }
                }
                
                if(progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
                progressPercent.textContent = Math.round(progress) + '%';
            }, 400);
            
            // Store interval ID to clear it later
            window.progressInterval = interval;
        }
        
        function hideLoading() {
            // Complete progress bar
            document.getElementById('progressBarFill').style.width = '100%';
            document.getElementById('progressPercent').textContent = '100%';
            document.getElementById('generatingText').textContent = 'Complete!';
            document.getElementById('statusMessage').textContent = 'Your summary is ready!';
            document.getElementById('aiStatusBadge').textContent = '✓ Ready';
            
            // Clear interval
            if(window.progressInterval) {
                clearInterval(window.progressInterval);
            }
            
            // Hide overlay after a short delay
            setTimeout(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
                document.getElementById('progressBarFill').style.width = '0%';
                document.getElementById('progressPercent').textContent = '0%';
                resetButton();
                
                // Reset steps
                for(let i = 1; i <= 4; i++) {
                    const step = document.getElementById('step' + i);
                    if(step) {
                        step.style.opacity = i === 1 ? '1' : '0.5';
                        step.style.color = 'var(--gray)';
                        step.querySelector('.step-icon').textContent = '⏳';
                    }
                }
            }, 800);
        }
        
        function showError(message) {
            alert('Error: ' + message);
        }
        
        function showSuccess(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert-card success-alert';
            alertDiv.style.cssText = 'position: fixed; top: 20px;  z-index: 10000; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 2px solid #34d399; border-radius: 12px; padding: 1rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); animation: slideDown 0.4s ease-out; max-width: 400px; pointer-events: auto;';
            alertDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="background: #10b981; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">✓</div>
                    <p style="color: #065f46; margin: 0; font-weight: 600; word-break: break-word;">${message}</p>
                </div>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
        }
        
        function showCelebration() {
            // Create confetti effect
            const colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
            for(let i = 0; i < 25; i++) {
                setTimeout(() => {
                    const spark = document.createElement('div');
                    spark.textContent = ['✨', '🎉', '⭐', '💫'][Math.floor(Math.random() * 4)];
                    spark.style.position = 'fixed';
                    spark.style.left = Math.random() * 100 + 'vw';
                    spark.style.top = '20vh';
                    spark.style.fontSize = '2rem';
                    spark.style.zIndex = '10001';
                    spark.style.pointerEvents = 'none';
                    spark.style.animation = 'sparkle 2s ease-out forwards';
                    document.body.appendChild(spark);
                    
                    setTimeout(() => spark.remove(), 2000);
                }, i * 50);
            }
        }
        
        // Auto-generate if no summary exists
        <?php if(!$summary_data): ?>
        // Don't auto-generate, let user trigger it
        <?php endif; ?>
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
