<?php
/**
 * CSRF Protection Helper
 * Provides Cross-Site Request Forgery protection for forms
 */

class CSRF {
    /**
     * Generate and store CSRF token
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token']) || self::shouldRegenerateToken()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Get current CSRF token
     */
    public static function getToken() {
        if (!isset($_SESSION['csrf_token'])) {
            return self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Check if token has expired (1 hour)
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
            self::regenerateToken();
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if token should be regenerated
     */
    private static function shouldRegenerateToken() {
        if (!isset($_SESSION['csrf_token_time'])) {
            return true;
        }
        // Regenerate token every hour
        return (time() - $_SESSION['csrf_token_time']) > 3600;
    }

    /**
     * Regenerate token
     */
    public static function regenerateToken() {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return self::generateToken();
    }

    /**
     * Get hidden input field for forms
     */
    public static function getInputField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Validate token from POST data and die if invalid
     */
    public static function validateOrDie() {
        $token = $_POST['csrf_token'] ?? '';
        if (!self::validateToken($token)) {
            http_response_code(403);
            die('CSRF validation failed. Please refresh the page and try again.');
        }
    }

    /**
     * Validate token from POST data and return boolean
     */
    public static function validate() {
        $token = $_POST['csrf_token'] ?? '';
        return self::validateToken($token);
    }
}
