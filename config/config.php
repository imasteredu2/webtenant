<?php
/**
 * Main Configuration File
 */

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'webtenant');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'WebTenant');
define('APP_VERSION', '1.0.0');
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost');
define('TIMEZONE', 'UTC');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);

// Modules Configuration
define('MODULES_DIR', __DIR__ . '/../modules');
define('MODULES_ENABLED', true);

// Tenant Configuration
define('TENANT_MODE', 'subdomain'); // Options: subdomain, parameter, header
define('TENANT_ISOLATION', true); // Enable data isolation between tenants

// Error Reporting (disable in production)
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set(TIMEZONE);
