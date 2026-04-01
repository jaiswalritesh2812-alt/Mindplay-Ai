<?php
// Initialize session and require student authentication
require_once __DIR__ . '/../config/session.php';
requireStudent('../login.php');

require_once __DIR__ . '/../config/db.php';

$user_id = intval(currentUserId() ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Learning Assistant - MindPlay</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .hero-section h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
        }
        .hero-section p {
            font-size: 1.25rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
        }
        .input-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .topic-input {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: border-color 0.3s;
        }
        .topic-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .generate-btn-large {
            width: 100%;
            padding: 1.25rem;
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .generate-btn-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
        .generate-btn-large:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .result-section {
            display: none;
        }
        .result-section.show {
            display: block;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--gray-light);
        }
        .tab {
            padding: 1rem 1.5rem;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray);
            position: relative;
            transition: color 0.3s;
        }
        .tab.active {
            color: var(--primary);
        }
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .mind-map-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow-x: auto;
            min-height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .mind-map-container svg {
            max-width: 100%;
            height: auto;
        }
        .summary-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            line-height: 1.8;
        }
        .summary-card h3 {
            color: var(--primary);
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .summary-card h4 {
            color: var(--secondary);
            margin-top: 1rem;
            margin-bottom: 0.75rem;
        }
        .summary-card ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        .summary-card li {
            margin-bottom: 0.5rem;
        }
        .summary-card strong {
            color: var(--dark);
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .feature-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #bae6fd;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .feature-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .recent-topics {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }
        .recent-topic-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-light);
            transition: background 0.2s;
        }
        .recent-topic-item:hover {
            background: var(--light);
            cursor: pointer;
        }
        .recent-topic-item:last-child {
            border-bottom: none;
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
    </style>
</head>
<body>
    <?php 
    $base_path = '../';
    include __DIR__ . '/../includes/student_navbar.php'; 
    ?>

    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1>🧠 AI Learning Assistant</h1>
            <p>Enter any topic and get an instant mind map with detailed summary to boost your learning!</p>
        </div>

        <!-- Features Grid -->
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🗺️</div>
                <div class="feature-title">Visual Mind Maps</div>
                <p style="color: var(--gray); font-size: 0.875rem;">Interactive diagrams showing topic relationships</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <div class="feature-title">Detailed Summaries</div>
                <p style="color: var(--gray); font-size: 0.875rem;">Comprehensive breakdowns of key concepts</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Instant Generation</div>
                <p style="color: var(--gray); font-size: 0.875rem;">AI-powered content in seconds</p>
            </div>
        </div>

        <!-- Input Section -->
        <div class="input-section">
            <label for="topicInput" style="display: block; font-size: 1.125rem; font-weight: 600; color: var(--dark); margin-bottom: 1rem;">
                📚 What would you like to learn about?
            </label>
            <input 
                type="text" 
                id="topicInput" 
                class="topic-input" 
                placeholder="E.g., Photosynthesis, Machine Learning, French Revolution, Quantum Physics..."
                autocomplete="off"
            >
            <button onclick="generateContent()" id="generateBtn" class="generate-btn-large">
                <span id="btnIcon">✨</span>
                <span id="btnText">Generate Mind Map & Summary</span>
            </button>
        </div>

        <!-- Result Section -->
        <div id="resultSection" class="result-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 id="resultTitle" style="color: var(--dark); margin: 0;"></h2>
                <button onclick="resetForm()" class="btn btn-primary">
                    🔄 New Topic
                </button>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="switchTab('mindmap')">
                    🗺️ Mind Map
                </button>
                <button class="tab" onclick="switchTab('summary')">
                    📝 Summary
                </button>
            </div>

            <!-- Mind Map Tab -->
            <div id="mindmapTab" class="tab-content active">
                <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
                    <button onclick="downloadMindmap()" class="btn btn-success" id="downloadMindmapBtn" style="display: none; gap: 0.5rem;">
                        <span>💾</span>
                        <span>Download Mind Map</span>
                    </button>
                </div>
                <div class="mind-map-container">
                    <div id="mindMapContent"></div>
                </div>
            </div>

            <!-- Summary Tab -->
            <div id="summaryTab" class="tab-content">
                <div class="summary-card">
                    <div id="summaryContent"></div>
                </div>
            </div>
        </div>

        <!-- Recent Topics -->
        <div class="recent-topics">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0;">📜 Your Recent Topics</h3>
                <button onclick="clearAllHistory()" id="clearAllBtn" style="background: var(--danger); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='var(--danger-dark)'" onmouseout="this.style.background='var(--danger)'">
                    🗑️ Clear All
                </button>
            </div>
            <div id="recentTopics">
                <p style="color: var(--gray); text-align: center;">No topics explored yet. Start learning now!</p>
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
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem;" id="spinnerEmoji">🧠</div>
            </div>
            <div class="generating-text" id="generatingText">Analyzing Your Topic</div>
            <p style="color: var(--gray); margin-top: 1rem; margin-bottom: 1.5rem;" id="statusMessage">
                Initializing AI learning assistant...
            </p>
            <div class="progress-bar">
                <div class="progress-bar-fill" id="progressBarFill" style="width: 0%;">
                    <span style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: white; font-size: 0.75rem; font-weight: 600;" id="progressPercent">0%</span>
                </div>
            </div>
            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <div class="status-step" id="step1" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem;">
                    <span class="step-icon">⏳</span>
                    <span>Processing topic...</span>
                </div>
                <div class="status-step" id="step2" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem; opacity: 0.5;">
                    <span class="step-icon">⏳</span>
                    <span>Creating mind map structure...</span>
                </div>
                <div class="status-step" id="step3" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem; opacity: 0.5;">
                    <span class="step-icon">⏳</span>
                    <span>Generating detailed summary...</span>
                </div>
                <div class="status-step" id="step4" style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray); font-size: 0.875rem; opacity: 0.5;">
                    <span class="step-icon">⏳</span>
                    <span>Finalizing content...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        mermaid.initialize({ 
            startOnLoad: false,
            theme: 'default',
            securityLevel: 'loose',
            themeVariables: {
                primaryColor: '#2563eb',
                primaryTextColor: '#fff',
                primaryBorderColor: '#1d4ed8',
                lineColor: '#6b7280',
                secondaryColor: '#10b981',
                tertiaryColor: '#f59e0b'
            }
        });

        // Allow Enter key to submit
        document.getElementById('topicInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                generateContent();
            }
        });

        function generateContent() {
            const topic = document.getElementById('topicInput').value.trim();
            
            if (!topic) {
                alert('Please enter a topic to learn about!');
                return;
            }

            // Update button state
            const btn = document.getElementById('generateBtn');
            const btnIcon = document.getElementById('btnIcon');
            const btnText = document.getElementById('btnText');
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
            btnIcon.textContent = '⏳';
            btnText.textContent = 'Generating with AI...';

            showLoading();

            fetch('generate_custom_summary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'topic=' + encodeURIComponent(topic)
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    displayResults(topic, data.mindmap, data.summary);
                    saveToRecent(topic);
                } else {
                    alert('Error: ' + (data.error || 'Failed to generate content'));
                }
            })
            .catch(error => {
                hideLoading();
                resetButton();
                alert('Network error. Please try again.');
                console.error('Error:', error);
            });
        }
        
        function resetButton() {
            const btn = document.getElementById('generateBtn');
            const btnIcon = document.getElementById('btnIcon');
            const btnText = document.getElementById('btnText');
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btnIcon.textContent = '✨';
            btnText.textContent = 'Generate Mind Map & Summary';
        }

        async function displayResults(topic, mindmapCode, summary) {
            // Update title
            document.getElementById('resultTitle').textContent = '📚 ' + topic;

            // Render mind map
            const mindMapDiv = document.getElementById('mindMapContent');
            mindMapDiv.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--gray);"><div class="loading-spinner" style="margin: 0 auto 1rem;"></div>Rendering mind map...</div>';
            
            try {
                // Clean up the mindmap code
                const cleanCode = mindmapCode.trim();
                console.log('Rendering mindmap code:', cleanCode);
                
                // Generate unique ID for this render
                const graphId = 'mermaid-diagram-' + Date.now();
                
                // Use mermaid.render for better compatibility
                const { svg } = await mermaid.render(graphId, cleanCode);
                
                // Clear and insert SVG
                mindMapDiv.innerHTML = svg;
                
                // Show download button
                document.getElementById('downloadMindmapBtn').style.display = 'inline-flex';
                
                console.log('Mindmap rendered successfully');
            } catch (err) {
                console.error('Mermaid rendering error:', err);
                mindMapDiv.innerHTML = '<div style="background: #fef2f2; border: 2px solid #ef4444; border-radius: 8px; padding: 1.5rem; color: #991b1b; margin-bottom: 1rem;"><strong>⚠️ Mind Map Error:</strong> ' + err.message + '</div><div style="background: #fffbeb; border: 2px solid #f59e0b; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;"><strong>📝 Raw Code:</strong></div><pre style="overflow-x: auto; background: #f3f4f6; padding: 1rem; border-radius: 8px; white-space: pre-wrap; word-wrap: break-word;">' + 
                    cleanCode + '</pre>';
            }

            // Display summary
            document.getElementById('summaryContent').innerHTML = summary;

            // Show result section with celebration
            document.getElementById('resultSection').classList.add('show');
            
            // Add celebration effect
            showCelebration();
            
            // Scroll to results
            setTimeout(() => {
                document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth' });
            }, 300);
        }

        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabName + 'Tab').classList.add('active');
        }
        
        function downloadMindmap() {
            const svg = document.querySelector('#mindMapContent svg');
            if (!svg) {
                alert('No mind map available to download!');
                return;
            }
            
            // Clone SVG to avoid modifying the original
            const svgClone = svg.cloneNode(true);
            
            // Add XML namespace if not present
            svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            
            // Convert SVG to string
            const svgData = new XMLSerializer().serializeToString(svgClone);
            
            // Create blob and download
            const blob = new Blob([svgData], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            
            // Get topic name for filename
            const topicName = document.getElementById('topicInput').value.trim() || 'mindmap';
            const filename = topicName.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '_mindmap.svg';
            
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            // Show success toast
            const toast = document.createElement('div');
            toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); z-index: 10001; font-weight: 600; animation: slideDown 0.3s ease-out;';
            toast.textContent = '✓ Mind map downloaded!';
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }

        function resetForm() {
            document.getElementById('topicInput').value = '';
            document.getElementById('resultSection').classList.remove('show');
            document.getElementById('topicInput').focus();
            document.getElementById('downloadMindmapBtn').style.display = 'none';
        }

        function saveToRecent(topic) {
            // Get recent topics from localStorage
            let recent = JSON.parse(localStorage.getItem('recentTopics') || '[]');
            
            // Add to beginning, remove duplicates
            recent = recent.filter(t => t !== topic);
            recent.unshift(topic);
            
            // Keep only last 5
            recent = recent.slice(0, 5);
            
            localStorage.setItem('recentTopics', JSON.stringify(recent));
            updateRecentTopics();
        }

        function updateRecentTopics() {
            const recent = JSON.parse(localStorage.getItem('recentTopics') || '[]');
            const container = document.getElementById('recentTopics');
            
            if (recent.length === 0) {
                container.innerHTML = '<p style="color: var(--gray); text-align: center;">No topics explored yet. Start learning now!</p>';
                return;
            }

            container.innerHTML = recent.map((topic, index) => `
                <div class="recent-topic-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--gray-light); transition: background 0.2s;">
                    <span style="font-weight: 600; cursor: pointer; flex: 1;" onclick="loadRecentTopic('${topic.replace(/'/g, "\\'")}')">
                        📚 ${topic}
                    </span>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button onclick="loadRecentTopic('${topic.replace(/'/g, "\\'")}'); event.stopPropagation();" 
                                style="background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 600; transition: background 0.2s;"
                                onmouseover="this.style.background='var(--primary-dark)'"
                                onmouseout="this.style.background='var(--primary)'">
                            📖 Load
                        </button>
                        <button onclick="deleteRecentTopic(${index}); event.stopPropagation();" 
                                style="background: var(--danger); color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 0.875rem; transition: background 0.2s;"
                                onmouseover="this.style.background='var(--danger-dark)'"
                                onmouseout="this.style.background='var(--danger)'"
                                title="Delete this topic">
                            🗑️
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        function deleteRecentTopic(index) {
            if(confirm('Are you sure you want to delete this topic from your history?')) {
                let recent = JSON.parse(localStorage.getItem('recentTopics') || '[]');
                recent.splice(index, 1);
                localStorage.setItem('recentTopics', JSON.stringify(recent));
                updateRecentTopics();
                
                // Show success message
                const toast = document.createElement('div');
                toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); z-index: 10001; font-weight: 600; animation: slideDown 0.3s ease-out;';
                toast.textContent = '✓ Topic deleted from history';
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.transition = 'opacity 0.3s';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }
        }
        
        function clearAllHistory() {
            if(confirm('Are you sure you want to clear all recent topics?')) {
                localStorage.removeItem('recentTopics');
                updateRecentTopics();
                
                const toast = document.createElement('div');
                toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); z-index: 10001; font-weight: 600; animation: slideDown 0.3s ease-out;';
                toast.textContent = '✓ All history cleared';
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.transition = 'opacity 0.3s';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }
        }

        function loadRecentTopic(topic) {
            document.getElementById('topicInput').value = topic;
            document.getElementById('topicInput').focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('generateBtn').disabled = true;
            
            // Animate progress bar with realistic stages
            let progress = 0;
            const progressBar = document.getElementById('progressBarFill');
            const progressPercent = document.getElementById('progressPercent');
            const generatingText = document.getElementById('generatingText');
            const statusMessage = document.getElementById('statusMessage');
            const aiStatusBadge = document.getElementById('aiStatusBadge');
            const spinnerEmoji = document.getElementById('spinnerEmoji');
            
            const stages = [
                { progress: 20, text: 'Analyzing Your Topic', message: 'Understanding the subject matter...', badge: 'AI is Analyzing', emoji: '🧠', step: 1 },
                { progress: 45, text: 'Creating Mind Map', message: 'Structuring key concepts and relationships...', badge: 'Building Structure', emoji: '🗺️', step: 2 },
                { progress: 70, text: 'Generating Summary', message: 'Writing comprehensive explanations...', badge: 'Generating Content', emoji: '📝', step: 3 },
                { progress: 90, text: 'Finalizing Content', message: 'Adding study tips and examples...', badge: 'Almost Ready', emoji: '✨', step: 4 }
            ];
            
            let currentStage = 0;
            
            window.progressInterval = setInterval(() => {
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
                
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
                progressPercent.textContent = Math.round(progress) + '%';
            }, 400);
        }

        function hideLoading() {
            document.getElementById('progressBarFill').style.width = '100%';
            document.getElementById('progressPercent').textContent = '100%';
            document.getElementById('generatingText').textContent = 'Complete!';
            document.getElementById('statusMessage').textContent = 'Your learning materials are ready!';
            document.getElementById('aiStatusBadge').textContent = '✓ Ready';
            
            if (window.progressInterval) {
                clearInterval(window.progressInterval);
            }
            
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
        
        function showCelebration() {
            // Create confetti effect
            const colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
            for(let i = 0; i < 30; i++) {
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

        // Load recent topics on page load
        updateRecentTopics();
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
