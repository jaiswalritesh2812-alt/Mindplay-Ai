# 🎓 MindPlay: AI-Powered Student Learning Platform

MindPlay is a comprehensive web-based application designed to help students perform **quick revision, learn new topics, and track progress** using AI-powered features including quiz generation, mind maps, and topic summaries.

## 🎯 Key Highlights

- 🤖 **AI-Powered Learning** - Generate quizzes, summaries, and mind maps instantly
- 🗺️ **Visual Mind Maps** - Interactive topic breakdowns with Mermaid.js
- 📊 **Performance Tracking** - Comprehensive analytics and leaderboards
- ⚡ **Fast & Efficient** - Using xiaomi/mimo-v2-flash AI model
- 🎨 **Modern UI** - Smooth animations and responsive design
- 🔒 **Secure** - Password hashing, SQL injection protection, session management
- 📱 **Responsive** - Works seamlessly on desktop, tablet, and mobile

## ✨ Features

### 👨‍💼 Admin Module

- ✅ **Subject Management** - Add, edit, and delete subjects
- ✅ **Syllabus Management** - Create detailed topic content with rich text
- ✅ **AI Question Generation** - Auto-generate quiz questions (5-20 questions)
- ✅ **Student Management** - View and manage registered students
- ✅ **Dashboard Analytics** - Track platform statistics and performance
- ✅ **Batch Operations** - Edit/delete subjects and syllabus topics

### 👨‍🎓 Student Module

- ✅ **User Registration & Authentication** - Secure account creation
- ✅ **Interactive Quiz System** - 10 random questions per quiz with timer
- ✅ **Quiz Review** - Review answers and correct solutions
- ✅ **AI Topic Summary** - Generate comprehensive summaries for any syllabus topic
- ✅ **AI Learning Assistant** - Learn any topic with mind maps and detailed explanations
- ✅ **Leaderboard** - Compare performance with other students
- ✅ **Performance Analytics** - Track quiz history and weak topics
- ✅ **Progress Tracking** - Monitor improvement over time

### 🤖 AI-Powered Features

- ✅ **Smart Question Generation** - Auto-generate MCQs from syllabus content
- ✅ **Mind Map Creation** - Visual topic breakdown using Mermaid.js
- ✅ **Topic Summaries** - AI-generated comprehensive summaries with key concepts
- ✅ **Custom Learning** - Enter any topic to get instant mind maps and explanations
- ✅ **Download Mind Maps** - Export mind maps as SVG files
- ✅ **Multi-stage Generation** - Real-time progress tracking during AI content creation

## 🛠️ Technology Stack

| Component     | Technology                                 |
| ------------- | ------------------------------------------ |
| **Frontend**  | HTML5, CSS3, JavaScript (Vanilla)          |
| **Styling**   | Custom CSS with CSS Variables & Animations |
| **Backend**   | PHP 8.0+                                   |
| **Database**  | MySQL 8.0+                                 |
| **AI API**    | OpenRouter (xiaomi/mimo-v2-flash)          |
| **Mind Maps** | Mermaid.js v10                             |
| **Icons**     | Unicode Emoji                              |

## 📋 Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- OpenRouter API key ([Get it here](https://openrouter.ai/))
- Modern web browser (Chrome, Firefox, Safari, Edge)

## 🚀 Installation Guide

### Step 1: Clone/Download the Project

```bash
git clone <repository-url>
cd mindplay
```

### Step 2: Database Setup

1. Create a new database in MySQL:

```sql
CREATE DATABASE mindplay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the base schema:

```bash
mysql -u root -p mindplay_db < database/schema.sql
```

3. Run additional migrations:

```bash
mysql -u root -p mindplay_db < database/add_topic_summaries.sql
mysql -u root -p mindplay_db < database/add_custom_topic_summaries.sql
mysql -u root -p mindplay_db < database/add_login_history.sql
mysql -u root -p mindplay_db < database/add_timer_feature.sql
mysql -u root -p mindplay_db < database/add_topic_results_migration.sql
```

Or use the migration script:

```bash
php database/run_migration.php
```

### Step 3: Configure the Application

1. Open `config/config.php`
2. Update the OpenRouter API key:

```php
define("OPENROUTER_API_KEY", "sk-or-v1-YOUR_API_KEY_HERE");
```

3. Update database credentials:

```php
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "your_password");
define("DB_NAME", "mindplay_db");
```

4. Configure AI model (optional):

```php
define("AI_MODEL", "xiaomi/mimo-v2-flash"); // Fast and efficient
// Other options: "openai/gpt-3.5-turbo", "anthropic/claude-2"
```

5. Set timezone:

```php
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone
```

### Step 4: Set Up Web Server

#### Using PHP Built-in Server (Development)

```bash
cd public
php -S localhost:8000
```

Or from root directory:

```bash
php -S localhost:8000 -t public
```

#### Using XAMPP/WAMP

1. Copy the project to `htdocs` or `www` folder
2. Access via: `http://localhost/mindplay/public/`

### Step 5: Access the Application

**Login Page:** `http://localhost:8000/login.php`

**Default Admin Credentials:**

- Email: `admin@mindplay.com`
- Password: `admin123`

## ⚡ Quick Start Guide

Once installed, follow these steps to get started:

### For First-Time Admin Setup

1. **Login as Admin** → Use default credentials above
2. **Add a Subject** → Dashboard → "Add Subject" → Enter subject name (e.g., "Mathematics")
3. **Add Syllabus** → "Add Syllabus" → Select subject → Add topic and content
4. **Generate Questions** → "Generate Questions" → Select topic → Choose count → Generate
5. **Done!** Students can now take quizzes on this topic

### For Students

1. **Register** → Click "Register" → Fill details → Submit
2. **Login** → Use your credentials
3. **Take Quiz** → Dashboard → Select subject → Start quiz
4. **AI Learning** → Try "AI Learning" for custom topics with mind maps
5. **Check Leaderboard** → See your ranking among peers

### Testing AI Features

1. **Topic Summary**: Go to any quiz → Click "📚 Topic" → Generate Summary
2. **AI Learning**: Click "🧠 AI Learning" → Enter "Photosynthesis" → Generate
3. **Mind Map**: View the generated mind map → Download as SVG
4. **Quiz**: Take a quiz and review your answers

## 📁 Project Structure

```
mindplay/
├── admin/                          # Admin module
│   ├── dashboard.php              # Admin dashboard with analytics
│   ├── add_subject.php            # Add new subjects
│   ├── edit_subject.php           # Edit existing subjects
│   ├── add_syllabus.php           # Add syllabus topics
│   ├── edit_syllabus.php          # Edit syllabus content
│   ├── generate_questions.php     # AI question generation
│   ├── manage_subjects.php        # Manage all subjects
│   └── manage_students.php        # View and manage students
├── assets/                         # Static assets
│   ├── css/
│   │   └── style.css             # Custom styles and animations
│   └── js/
│       └── main.js               # JavaScript utilities
├── config/                        # Configuration files
│   ├── config.php                # App configuration & API keys
│   ├── db.php                    # Database connection
│   └── session.php               # Session management & auth
├── database/                      # Database files
│   ├── schema.sql                # Main database schema
│   ├── add_topic_summaries.sql   # Topic summaries migration
│   ├── add_custom_topic_summaries.sql  # Custom summaries migration
│   ├── add_login_history.sql     # Login tracking migration
│   ├── add_timer_feature.sql     # Quiz timer migration
│   ├── add_topic_results_migration.sql # Results tracking
│   └── run_migration.php         # Migration runner script
├── includes/                      # Shared components
│   ├── admin_navbar.php          # Admin navigation bar
│   └── student_navbar.php        # Student navigation bar
├── student/                       # Student module
│   ├── dashboard.php             # Student dashboard
│   ├── quiz.php                  # Quiz interface with timer
│   ├── quiz_result.php           # Quiz results & scoring
│   ├── quiz_review.php           # Review answers
│   ├── topic_summary.php         # Syllabus-based AI summaries
│   ├── generate_summary.php      # Summary generation API
│   ├── delete_summary.php        # Delete summary endpoint
│   ├── ai_learning.php           # Custom topic learning with mind maps
│   ├── generate_custom_summary.php  # Custom summary API
│   └── leaderboard.php           # Student rankings
├── index.php                      # Landing page
├── login.php                      # Login page
├── register.php                   # Registration page
├── logout.php                     # Logout handler
└── README.md                      # Documentation
```

## 🗄️ Database Schema

### Core Tables

1. **users** - User accounts and authentication
   - `id`, `name`, `email`, `password`, `role` (admin/student)
   - `created_at`, `last_login`

2. **subjects** - Academic subjects
   - `id`, `subject_name`, `created_at`

3. **syllabus** - Topic content for each subject
   - `id`, `subject_id`, `topic`, `content`, `created_at`

4. **questions** - Quiz questions (AI-generated)
   - `id`, `syllabus_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`
   - `correct_answer`, `created_at`

5. **quiz_attempts** - Individual quiz sessions
   - `id`, `user_id`, `subject_id`, `score`, `total_questions`
   - `time_taken`, `created_at`

6. **user_answers** - Track student responses
   - `id`, `user_id`, `question_id`, `selected_option`, `is_correct`
   - `answered_at`

7. **topic_summaries** - AI-generated syllabus summaries
   - `id`, `syllabus_id`, `user_id`, `summary`, `generated_at`
   - Unique per user-topic pair

8. **custom_topic_summaries** - Custom learning content
   - `id`, `user_id`, `topic`, `mindmap`, `summary`, `generated_at`
   - Cached for 7 days

9. **topic_results** - Performance tracking per topic
   - `id`, `user_id`, `syllabus_id`, `correct_answers`, `total_attempts`
   - `last_attempt`, `updated_at`

10. **login_history** - Security tracking
    - `id`, `user_id`, `login_time`, `ip_address`, `user_agent`

## 🎯 How to Use

### For Administrators

1. **Login** with admin credentials at `/login.php`
2. **Dashboard** - View platform statistics and recent activity
3. **Add Subjects** - Create academic subjects via "Add Subject"
4. **Add Syllabus** - Add topics with detailed content for each subject
5. **Generate Questions** - Use AI to auto-generate quiz questions (5-20)
6. **Manage Subjects** - Edit or delete subjects and syllabus topics
7. **Manage Students** - View registered students and their performance

### For Students

1. **Register** a new account at `/register.php`
2. **Login** to access your dashboard at `/login.php`
3. **Dashboard** - View your statistics and recent quizzes
4. **Take Quiz** - Select subject → Start quiz → Answer 10 questions
5. **Review Answers** - Check correct answers after completing quiz
6. **AI Learning**:
   - **Topic Summary** - Generate AI summaries for syllabus topics
   - **AI Learning Assistant** - Learn any custom topic with mind maps
   - **Download Mind Maps** - Export mind maps as SVG files
7. **Leaderboard** - Compare your performance with other students
8. **Track Progress** - Monitor quiz history and identify weak topics

## 🔒 Security Features

- ✅ Password hashing using PHP's `password_hash()` with bcrypt
- ✅ Prepared statements throughout to prevent SQL injection
- ✅ Secure session management with IP/UA validation
- ✅ Role-based access control (Admin/Student)
- ✅ Input validation and sanitization (XSS protection)
- ✅ CSRF protection infrastructure (token functions ready)
- ✅ Security headers via `.htaccess` (X-Frame-Options, CSP, etc.)
- ✅ Session hijacking prevention with auto-regeneration
- ✅ Environment-based configuration (dev/production modes)

## 🤖 AI Configuration

The application uses **OpenRouter API** with **xiaomi/mimo-v2-flash** model for fast, efficient AI generation.

### Getting Your API Key

1. Visit [OpenRouter](https://openrouter.ai/)
2. Sign up for an account
3. Navigate to API Keys section
4. Generate a new API key
5. Update in `config/config.php`:

```php
define("OPENROUTER_API_KEY", "sk-or-v1-YOUR_KEY_HERE");
```

### AI Model Options

**Current Model:** `xiaomi/mimo-v2-flash` (Fast, efficient, cost-effective)

**Alternative Models:**

- `openai/gpt-3.5-turbo` - More accurate, higher cost
- `openai/gpt-4` - Best quality, highest cost
- `anthropic/claude-2` - Strong reasoning abilities
- `google/palm-2` - Fast and reliable

### AI Feature Customization

**Question Generation:**

```php
define("QUESTIONS_PER_QUIZ", 10);  // Number of questions per quiz
```

**Topic Summary:**

- Generates comprehensive summaries with key concepts
- Includes study tips and important points
- Cached per user-topic for 7 days

**Mind Map Creation:**

- Uses Mermaid.js for visual representation
- Hierarchical topic breakdown
- Downloadable as SVG
- Real-time rendering

### AI Generation Process

1. **Multi-stage Progress Tracking:**
   - 📚 Analyzing Content (0-25%)
   - 🔍 Extracting Key Concepts (25-50%)
   - ✍️ Creating Summary/Questions (50-75%)
   - ✨ Finalizing (75-100%)

2. **Loading Animations:**
   - Progress bars with percentage
   - Stage-specific messages
   - Emoji indicators
   - Celebration effects on completion

## 📊 Features Breakdown

### Quiz System

- **Random Question Selection** - 10 questions per quiz
- **Multiple Choice Questions** - 4 options (A, B, C, D)
- **Timer Feature** - Track time taken for each quiz
- **Instant Scoring** - Real-time result calculation
- **Answer Review** - Review correct/incorrect answers
- **Performance Tracking** - Quiz history and statistics

### AI Learning Assistant

- **Custom Topic Input** - Learn any topic, not just syllabus
- **Mind Map Generation** - Visual topic breakdown using Mermaid.js
- **Detailed Summaries** - Comprehensive explanations with examples
- **Download Feature** - Export mind maps as SVG files
- **Recent Topics** - Quick access to last 5 topics
- **Tabbed Interface** - Switch between mind map and summary views

### Topic Summary Generator

- **Syllabus-Based** - Generate summaries for curriculum topics
- **AI-Powered Content** - Key concepts, definitions, and study tips
- **One-Click Generation** - Fast summary creation
- **Delete & Regenerate** - Update summaries anytime
- **Cached Results** - Instant retrieval of previously generated summaries

### Leaderboard System

- **Real-time Rankings** - Compare performance with peers
- **Multiple Metrics**:
  - Total quizzes attempted
  - Questions answered
  - Average score percentage
  - Overall rank
- **Visual Indicators** - 🥇 🥈 🥉 for top 3 performers

### Performance Analytics

- **Dashboard Statistics**:
  - Total quizzes taken
  - Questions attempted
  - Average score
  - Total subjects
- **Weak Topic Identification** - Automatic tracking of mistakes
- **Quiz History** - Recent performance with scores
- **Subject-wise Analysis** - Performance per subject
- **Progress Tracking** - Improvement over time

### UI/UX Features

- **Responsive Design** - Works on all device sizes
- **Loading Animations** - Multi-stage progress indicators
- **Toast Notifications** - User-friendly feedback messages
- **Celebration Effects** - Confetti on successful completion
- **Button State Management** - Visual feedback during operations
- **Smooth Transitions** - CSS animations throughout
- **Emoji Integration** - Visual appeal and clarity

## 🎨 Customization

### Theme Colors

Edit `assets/css/style.css` CSS variables:

```css
:root {
  --primary: #2563eb; /* Blue - Main actions */
  --secondary: #10b981; /* Green - Success states */
  --danger: #ef4444; /* Red - Delete/Error */
  --warning: #f59e0b; /* Orange - Warnings */
  --info: #3b82f6; /* Light Blue - Info */
  --dark: #1f2937; /* Dark Gray - Text */
  --gray: #6b7280; /* Gray - Secondary text */
  --gray-light: #e5e7eb; /* Light Gray - Borders */
}
```

### Quiz Configuration

Edit `config/config.php`:

```php
// Number of questions per quiz
define("QUESTIONS_PER_QUIZ", 10);

// Minimum passing percentage
define("PASSING_SCORE", 60);

// Timezone for timestamps
date_default_timezone_set('Asia/Kolkata');
```

### AI Model Selection

Change the AI model in `config/config.php`:

```php
// Fast and efficient (Recommended)
define("AI_MODEL", "xiaomi/mimo-v2-flash");

// High quality
define("AI_MODEL", "openai/gpt-3.5-turbo");

// Best quality (expensive)
define("AI_MODEL", "openai/gpt-4");
```

### Animations & Effects

Modify animation durations in `assets/css/style.css`:

```css
/* Loading animation speed */
.loading-spinner {
  animation: spin 1s linear infinite;
}

/* Celebration effect count */
/* Edit showCelebration() in JavaScript files */
for(let i = 0; i < 25; i++) {
  /* Change 25 to desired count */
}
```

## 🐛 Troubleshooting

### Database Connection Error

**Issue:** `Could not connect to database`

**Solutions:**

- Verify MySQL service is running
- Check credentials in `config/config.php`
- Ensure database `mindplay_db` exists
- Verify user has proper permissions
- Check for typos in DB_HOST, DB_USER, DB_PASS

### AI Generation Failures

**Issue:** Questions/Summaries not generating

**Solutions:**

- Verify OpenRouter API key is correct and active
- Check API quota/limits on OpenRouter dashboard
- Ensure `curl` extension is enabled in PHP
- Test internet connectivity
- Check error logs in browser console (F12)
- Try a different AI model

### Mind Map Not Rendering

**Issue:** Mind map shows raw code instead of diagram

**Solutions:**

- Clear browser cache and reload
- Check browser console for JavaScript errors
- Verify Mermaid.js CDN is accessible
- Ensure ad-blockers aren't blocking CDN
- Try a different browser

### Login/Session Issues

**Issue:** Can't login or session expires immediately

**Solutions:**

- Clear browser cookies and cache
- Check `session_start()` is enabled in PHP
- Verify user exists in database
- Check password using correct hash method
- Ensure `config/session.php` is included properly

### Performance Issues

**Issue:** Slow page loading or AI generation

**Solutions:**

- Optimize database with indexes
- Clear old cached summaries
- Check server resources (CPU, RAM)
- Use faster AI model (xiaomi/mimo-v2-flash)
- Enable PHP OPcache
- Optimize images and assets

### File Permission Errors

**Issue:** Cannot write to files or directories

**Solutions:**

```bash
# Linux/Mac
chmod 755 assets/
chmod 644 assets/css/*.css

# Windows
# Right-click folder → Properties → Security → Edit permissions
```

## 📈 Future Enhancements

- [ ] **PDF/Image Export** - Export summaries and quiz results
- [ ] **Email Notifications** - Quiz reminders and result sharing
- [ ] **Mobile App** - Native Android/iOS applications
- [ ] **Voice Features** - Text-to-speech for summaries
- [ ] **Flashcard Mode** - Spaced repetition learning
- [ ] **Study Groups** - Collaborative learning features
- [ ] **Practice Mode** - Unlimited practice without scoring
- [ ] **Question Bank** - Manual question adding by admins
- [ ] **Advanced Analytics** - Detailed performance insights
- [ ] **Gamification** - Badges, streaks, and achievements
- [ ] **Multi-language** - Support for multiple languages
- [ ] **Video Summaries** - AI-generated video explanations
- [ ] **Offline Mode** - Download content for offline access
- [ ] **API Integration** - REST API for mobile apps
- [ ] **Social Features** - Share achievements and compete

## � Production Deployment

MindPlay is **production-ready** with enterprise-grade security and deployment features.

### Quick Production Setup

1. **Configure Environment Variables**

   ```bash
   cp .env.example .env
   nano .env  # Update with production values
   ```

2. **Set Production Mode**

   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

3. **Follow Deployment Guide**
   - See [DEPLOYMENT.md](DEPLOYMENT.md) for complete instructions
   - See [SECURITY.md](SECURITY.md) for security best practices

### Production Features

- ✅ **Environment Detection** - Auto-switch between dev/production modes
- ✅ **Environment Variables** - All secrets in `.env` file (not in code)
- ✅ **Error Handling** - Graceful errors in production, detailed in dev
- ✅ **Security Headers** - X-Frame-Options, CSP, HSTS, XSS protection
- ✅ **Apache Configuration** - Complete `.htaccess` with security rules
- ✅ **Custom Error Pages** - Branded 404, 500, 403 pages
- ✅ **Rate Limiting** - Login attempt tracking (constants defined)
- ✅ **Session Security** - IP/UA validation, auto-regeneration
- ✅ **CSRF Protection** - Token generation and validation infrastructure
- ✅ **Database Security** - Safe query helpers, error suppression
- ✅ **Backup Scripts** - Automated database and file backups
- ✅ **Restore Scripts** - Quick rollback capability
- ✅ **SEO Ready** - robots.txt and sitemap support

### Deployment Documentation

📚 **Complete Guides Available:**

- [DEPLOYMENT.md](DEPLOYMENT.md) - Step-by-step production deployment
- [SECURITY.md](SECURITY.md) - Security features and best practices
- [README.md](README.md) - This file

## 🔒 Security Features

- ✅ **Password Hashing** - Using `password_hash()` with bcrypt
- ✅ **Prepared Statements** - SQL injection prevention throughout
- ✅ **Session Security** - IP/UA validation + auto-regeneration every 30min
- ✅ **Role-Based Access** - Admin/Student separation with middleware
- ✅ **Input Validation** - Client and server-side validation
- ✅ **XSS Protection** - Output sanitization with `htmlspecialchars()`
- ✅ **CSRF Protection** - Token generation/validation functions ready
- ✅ **Login History** - Track authentication attempts (table ready)
- ✅ **Secure API Calls** - Environment-based API key management
- ✅ **Session Timeout** - 24-hour inactivity auto-logout
- ✅ **Session Hijacking Prevention** - IP and User Agent validation
- ✅ **Security Headers** - X-Frame-Options, CSP, HSTS, XSS protection
- ✅ **Directory Protection** - `.htaccess` blocks config/database access
- ✅ **Error Suppression** - No sensitive data exposed in production
- ✅ **Safe Query Helper** - `safeQuery()` function for prepared statements

## 🚀 Performance Optimizations

- ✅ **Database Caching** - Cached AI-generated content (7 days)
- ✅ **Efficient Queries** - Indexed columns for fast lookups
- ✅ **Lazy Loading** - Load content only when needed
- ✅ **Optimized AI Model** - Using fast xiaomi/mimo-v2-flash
- ✅ **Async Operations** - Non-blocking AI generation
- ✅ **LocalStorage Usage** - Client-side caching for recent topics
- ✅ **CSS Animations** - Hardware-accelerated transitions

## 📱 Browser Compatibility

| Browser | Minimum Version | Status             |
| ------- | --------------- | ------------------ |
| Chrome  | 90+             | ✅ Fully Supported |
| Firefox | 88+             | ✅ Fully Supported |
| Safari  | 14+             | ✅ Fully Supported |
| Edge    | 90+             | ✅ Fully Supported |
| Opera   | 76+             | ✅ Fully Supported |

**Note:** Mermaid.js requires a modern browser with SVG support.

## 📝 License

This project is open source and available under the MIT License.

## 💡 Technologies & Credits

### Core Technologies

- **Backend:** PHP 8.0+ with MySQLi
- **Frontend:** Vanilla JavaScript, HTML5, CSS3
- **Database:** MySQL 8.0+
- **AI API:** [OpenRouter](https://openrouter.ai/) - Multi-model AI gateway
- **Mind Maps:** [Mermaid.js](https://mermaid.js.org/) v10 - Diagram rendering
- **Icons:** Unicode Emoji

### AI Models

- **Primary:** Xiaomi Mimo-v2-Flash (Fast & Efficient)
- **Alternatives:** GPT-3.5-turbo, Claude-2, PaLM-2

### Design Principles

- Custom CSS with CSS Variables
- Mobile-first responsive design
- Smooth animations and transitions
- User-friendly feedback with toasts
- Accessibility considerations

## 👥 Contributing

Contributions are welcome! To contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📧 Support

For issues, questions, or suggestions:

- Open an issue on GitHub
- Check existing documentation
- Review troubleshooting section

## 🌟 Show Your Support

Give a ⭐️ if this project helped you!

---

**Made with ❤️ for students worldwide**

_Empowering education through AI-powered learning tools_
