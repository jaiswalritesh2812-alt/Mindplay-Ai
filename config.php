<?php
/**
 * MindPlay Application Configuration
 */

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Application Settings
define("APP_NAME", "MindPlay");

// OpenRouter AI API Configuration
define("OPENROUTER_API_KEY", "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");
define("OPENROUTER_API_URL", "https://openrouter.ai/api/v1/chat/completions");
define("AI_MODEL", "xiaomi/mimo-v2-flash");

// Database Configuration
// define("DB_HOST", "sql102.infinityfree.com");
// define("DB_USER", "if0_41129013");
// define("DB_PASS", "Ritesh9090");
// define("DB_NAME", "if0_41129013_mindply");

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "123456");
define("DB_NAME", "if0_41129013_mindply");

// Timezone
date_default_timezone_set("Asia/Kolkata");
