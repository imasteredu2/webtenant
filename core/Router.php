<?php
/**
 * Router - Routes requests to appropriate modules or pages
 */

class Router {
    private $moduleManager;
    private $tenant;

    public function __construct(Module $moduleManager, Tenant $tenant) {
        $this->moduleManager = $moduleManager;
        $this->tenant = $tenant;
    }

    /**
     * Route the current request
     */
    public function route() {
        $action = $_GET['action'] ?? 'dashboard';
        $module = $_GET['module'] ?? null;

        // If module is specified, route to module
        if ($module && $this->moduleManager->isModuleLoaded($module)) {
            $this->routeToModule($module, $action);
        } else {
            // Route to core pages
            $this->routeToCore($action);
        }
    }

    /**
     * Route to a module
     */
    private function routeToModule($moduleSlug, $action) {
        $module = $this->moduleManager->getModule($moduleSlug);
        
        if (is_object($module) && method_exists($module, 'handleRequest')) {
            $module->handleRequest($action);
        } else {
            // Load module view file
            $viewFile = MODULES_DIR . '/' . $moduleSlug . '/views/' . $action . '.php';
            if (file_exists($viewFile)) {
                $this->loadView($viewFile);
            } else {
                $this->show404();
            }
        }
    }

    /**
     * Route to core pages
     */
    private function routeToCore($action) {
        switch ($action) {
            case 'dashboard':
                $this->showDashboard();
                break;
            case 'login':
                $this->showLogin();
                break;
            case 'logout':
                $this->handleLogout();
                break;
            default:
                $this->show404();
        }
    }

    /**
     * Show dashboard
     */
    private function showDashboard() {
        if (!$this->isAuthenticated()) {
            header('Location: ?action=login');
            exit;
        }

        $tenant = $this->tenant->getCurrentTenant();
        $modules = $this->moduleManager->getLoadedModules();
        
        include __DIR__ . '/../views/dashboard.php';
    }

    /**
     * Show login page
     */
    private function showLogin() {
        if ($this->isAuthenticated()) {
            header('Location: ?action=dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            include __DIR__ . '/../views/login.php';
        }
    }

    /**
     * Handle login
     * TODO: Implement proper authentication with user management module
     * SECURITY WARNING: This is a basic demo authentication for testing only
     */
    private function handleLogin() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($email) || empty($password)) {
            header('Location: ?action=login&error=1');
            exit;
        }

        // TODO: Replace with proper database authentication
        // For demo purposes only - accepts any credentials
        // In production, implement proper password verification:
        // 1. Query users table for email and tenant
        // 2. Verify password with password_verify()
        // 3. Check user is active
        // 4. Set proper session variables
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = $email;
        $_SESSION['authenticated'] = true;

        header('Location: ?action=dashboard');
        exit;
    }

    /**
     * Handle logout
     */
    private function handleLogout() {
        session_destroy();
        header('Location: ?action=login');
        exit;
    }

    /**
     * Check if user is authenticated
     */
    private function isAuthenticated() {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    /**
     * Load a view file
     */
    private function loadView($viewFile) {
        if (file_exists($viewFile)) {
            $tenant = $this->tenant->getCurrentTenant();
            include $viewFile;
        } else {
            $this->show404();
        }
    }

    /**
     * Show 404 page
     */
    private function show404() {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        exit;
    }
}
