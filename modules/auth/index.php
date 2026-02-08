<?php
/**
 * Authentication Module
 * Provides secure user authentication and management
 */

class AuthModule {
    private $db;
    private $tenant;
    private $moduleManager;

    public function __construct(Database $db, Tenant $tenant, Module $moduleManager) {
        $this->db = $db;
        $this->tenant = $tenant;
        $this->moduleManager = $moduleManager;
        $this->initDatabase();
    }

    /**
     * Initialize module-specific database tables
     */
    private function initDatabase() {
        try {
            // Users table is already created in core schema
            // Add password reset tokens table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS password_reset_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(255) NOT NULL UNIQUE,
                    expires_at DATETIME NOT NULL,
                    used TINYINT(1) DEFAULT 0,
                    created_at DATETIME NOT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_token (token),
                    INDEX idx_expires (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Login attempts table for security
            $this->db->query("
                CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    attempted_at DATETIME NOT NULL,
                    successful TINYINT(1) DEFAULT 0,
                    tenant_id INT NULL,
                    INDEX idx_email (email),
                    INDEX idx_ip (ip_address),
                    INDEX idx_attempted (attempted_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Auth module DB init error: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle module requests
     */
    public function handleRequest($action) {
        switch ($action) {
            case 'register':
                $this->register();
                break;
            case 'users':
                $this->listUsers();
                break;
            case 'add_user':
                $this->addUser();
                break;
            case 'edit_user':
                $this->editUser();
                break;
            case 'delete_user':
                $this->deleteUser();
                break;
            case 'change_password':
                $this->changePassword();
                break;
            case 'profile':
                $this->showProfile();
                break;
            default:
                $this->listUsers();
        }
    }

    /**
     * Authenticate user with email and password
     */
    public function authenticate($email, $password) {
        $tenantId = $this->tenant->getTenantId();
        
        if (!$tenantId) {
            $this->logLoginAttempt($email, false, null);
            return false;
        }

        // Check for too many failed attempts
        if ($this->hasExceededLoginAttempts($email)) {
            return [
                'success' => false,
                'error' => 'Too many failed login attempts. Please try again in 15 minutes.'
            ];
        }

        // Fetch user from database
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = :email AND tenant_id = :tenant_id AND active = 1",
            ['email' => $email, 'tenant_id' => $tenantId]
        );

        if (!$user) {
            $this->logLoginAttempt($email, false, $tenantId);
            return [
                'success' => false,
                'error' => 'Invalid email or password'
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            $this->logLoginAttempt($email, false, $tenantId);
            return [
                'success' => false,
                'error' => 'Invalid email or password'
            ];
        }

        // Authentication successful
        $this->logLoginAttempt($email, true, $tenantId);
        
        // Update last login
        $this->db->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $user['id']]
        );

        return [
            'success' => true,
            'user' => $user
        ];
    }

    /**
     * Check if email has exceeded login attempts
     */
    private function hasExceededLoginAttempts($email) {
        $attempts = $this->db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM login_attempts 
             WHERE email = :email 
             AND successful = 0 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            ['email' => $email]
        );

        return isset($attempts['count']) && $attempts['count'] >= 5;
    }

    /**
     * Log login attempt
     */
    private function logLoginAttempt($email, $successful, $tenantId) {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $data = [
                'email' => $email,
                'ip_address' => $ipAddress,
                'attempted_at' => date('Y-m-d H:i:s'),
                'successful' => $successful ? 1 : 0,
                'tenant_id' => $tenantId
            ];
            
            // Insert without tenant isolation
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare(
                "INSERT INTO login_attempts (email, ip_address, attempted_at, successful, tenant_id) 
                 VALUES (:email, :ip_address, :attempted_at, :successful, :tenant_id)"
            );
            $stmt->execute($data);
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Failed to log login attempt: " . $e->getMessage());
            }
        }
    }

    /**
     * Get all users for current tenant
     */
    public function getUsers() {
        return $this->db->fetchAll(
            "SELECT id, name, email, role, active, created_at, last_login 
             FROM users 
             WHERE tenant_id = :tenant_id 
             ORDER BY name",
            ['tenant_id' => $this->tenant->getTenantId()]
        );
    }

    /**
     * List all users (admin only)
     */
    private function listUsers() {
        $users = $this->getUsers();
        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/users.php';
    }

    /**
     * Add new user (admin function)
     */
    private function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrDie();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';

            $errors = [];

            if (empty($name)) $errors[] = "Name is required";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Valid email is required";
            }
            if (empty($password) || strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters";
            }

            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                try {
                    $this->db->insert('users', [
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'role' => $role,
                        'active' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    header('Location: ?module=auth&action=users&success=1');
                    exit;
                } catch (Exception $e) {
                    $error = "Failed to create user: " . $e->getMessage();
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/user_form.php';
    }

    /**
     * Edit user
     */
    private function editUser() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=auth&action=users');
            exit;
        }

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = :id AND tenant_id = :tenant_id",
            ['id' => $id, 'tenant_id' => $this->tenant->getTenantId()]
        );

        if (!$user) {
            header('Location: ?module=auth&action=users');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrDie();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $active = isset($_POST['active']) ? 1 : 0;

            $updateData = [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'active' => $active,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update password if provided
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) >= 8) {
                    $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                } else {
                    $error = "Password must be at least 8 characters";
                }
            }

            if (!isset($error)) {
                try {
                    $this->db->update('users', $updateData, 'id = :id', ['id' => $id]);
                    header('Location: ?module=auth&action=users&success=1');
                    exit;
                } catch (Exception $e) {
                    $error = "Failed to update user: " . $e->getMessage();
                }
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/user_form.php';
    }

    /**
     * Delete user
     */
    private function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?module=auth&action=users');
            exit;
        }
        
        CSRF::validateOrDie();

        $id = $_POST['id'] ?? null;
        if ($id) {
            // Don't allow deleting yourself
            if ($id != $_SESSION['user_id']) {
                $this->db->delete('users', 'id = :id', ['id' => $id]);
            }
        }
        
        header('Location: ?module=auth&action=users');
        exit;
    }

    /**
     * Show user profile
     */
    private function showProfile() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: ?action=login');
            exit;
        }

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $userId]
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrDie();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $updateData = [
                'name' => $name,
                'email' => $email,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            try {
                $this->db->update('users', $updateData, 'id = :id', ['id' => $userId]);
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = "Profile updated successfully";
                $user = array_merge($user, $updateData);
            } catch (Exception $e) {
                $error = "Failed to update profile: " . $e->getMessage();
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/profile.php';
    }

    /**
     * Change password
     */
    private function changePassword() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: ?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrDie();
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $user = $this->db->fetchOne(
                "SELECT password FROM users WHERE id = :id",
                ['id' => $userId]
            );

            $errors = [];

            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = "Current password is incorrect";
            }
            if (strlen($newPassword) < 8) {
                $errors[] = "New password must be at least 8 characters";
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = "Passwords do not match";
            }

            if (empty($errors)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->db->update(
                    'users',
                    ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')],
                    'id = :id',
                    ['id' => $userId]
                );

                header('Location: ?module=auth&action=profile&password_changed=1');
                exit;
            } else {
                $error = implode('<br>', $errors);
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/change_password.php';
    }

    /**
     * Register new user
     */
    private function register() {
        $tenant = $this->tenant->getCurrentTenant();
        if (!$tenant) {
            header('Location: ?action=login&error=no_tenant');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateOrDie();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            if (empty($name)) $errors[] = "Name is required";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Valid email is required";
            }
            if (empty($password) || strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters";
            }
            if ($password !== $confirmPassword) {
                $errors[] = "Passwords do not match";
            }

            if (empty($errors)) {
                $existing = $this->db->fetchOne(
                    "SELECT id FROM users WHERE email = :email AND tenant_id = :tenant_id",
                    ['email' => $email, 'tenant_id' => $this->tenant->getTenantId()]
                );

                if ($existing) {
                    $errors[] = "Email already registered";
                }
            }

            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $userId = $this->db->insert('users', [
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role' => 'user',
                    'active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $name;
                $_SESSION['authenticated'] = true;

                header('Location: ?action=dashboard&registered=1');
                exit;
            }

            $error = implode('<br>', $errors);
        }

        include __DIR__ . '/views/register.php';
    }

    /**
     * Get user count (for other modules)
     */
    public function getUserCount() {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE tenant_id = :tenant_id",
            ['tenant_id' => $this->tenant->getTenantId()]
        );
        return $result['count'] ?? 0;
    }
}
