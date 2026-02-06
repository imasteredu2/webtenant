<?php
/**
 * Task Management Module
 * Provides task tracking and management functionality
 */

class TasksModule {
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
            $this->db->query("
                CREATE TABLE IF NOT EXISTS tasks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
                    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                    due_date DATE NULL,
                    assigned_to INT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL,
                    completed_at DATETIME NULL,
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_tenant (tenant_id),
                    INDEX idx_status (status),
                    INDEX idx_due_date (due_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Tasks module DB init error: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle module requests
     */
    public function handleRequest($action) {
        switch ($action) {
            case 'list':
                $this->listTasks();
                break;
            case 'add':
                $this->addTask();
                break;
            case 'edit':
                $this->editTask();
                break;
            case 'delete':
                $this->deleteTask();
                break;
            case 'complete':
                $this->completeTask();
                break;
            default:
                $this->listTasks();
        }
    }

    /**
     * List all tasks
     */
    private function listTasks() {
        $filter = $_GET['filter'] ?? 'all';
        $tasks = $this->getTasks($filter);
        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/list.php';
    }

    /**
     * Get tasks for current tenant
     */
    public function getTasks($filter = 'all') {
        $sql = "SELECT * FROM tasks WHERE tenant_id = :tenant_id";
        
        switch ($filter) {
            case 'todo':
                $sql .= " AND status = 'todo'";
                break;
            case 'in_progress':
                $sql .= " AND status = 'in_progress'";
                break;
            case 'done':
                $sql .= " AND status = 'done'";
                break;
            case 'overdue':
                $sql .= " AND status != 'done' AND due_date < CURDATE()";
                break;
        }
        
        $sql .= " ORDER BY FIELD(priority, 'high', 'medium', 'low'), due_date ASC";
        
        return $this->db->fetchAll($sql, ['tenant_id' => $this->tenant->getTenantId()]);
    }

    /**
     * Add a new task
     */
    private function addTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'status' => $_POST['status'] ?? 'todo',
                'priority' => $_POST['priority'] ?? 'medium',
                'due_date' => $_POST['due_date'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            try {
                $this->db->insert('tasks', $data);
                header('Location: ?module=tasks&action=list&success=1');
                exit;
            } catch (Exception $e) {
                $error = "Error adding task: " . $e->getMessage();
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/form.php';
    }

    /**
     * Edit a task
     */
    private function editTask() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=tasks&action=list');
            exit;
        }

        $task = $this->db->fetchOne(
            "SELECT * FROM tasks WHERE id = :id AND tenant_id = :tenant_id",
            ['id' => $id, 'tenant_id' => $this->tenant->getTenantId()]
        );

        if (!$task) {
            header('Location: ?module=tasks&action=list');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'status' => $_POST['status'] ?? 'todo',
                'priority' => $_POST['priority'] ?? 'medium',
                'due_date' => $_POST['due_date'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Set completed_at if status changed to done
            if ($data['status'] === 'done' && $task['status'] !== 'done') {
                $data['completed_at'] = date('Y-m-d H:i:s');
            }

            try {
                $this->db->update('tasks', $data, 'id = :id', ['id' => $id]);
                header('Location: ?module=tasks&action=list&success=1');
                exit;
            } catch (Exception $e) {
                $error = "Error updating task: " . $e->getMessage();
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/form.php';
    }

    /**
     * Delete a task
     */
    private function deleteTask() {
        $id = $_GET['id'] ?? null;
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->db->delete('tasks', 'id = :id', ['id' => $id]);
        }
        header('Location: ?module=tasks&action=list');
        exit;
    }

    /**
     * Mark task as complete
     */
    private function completeTask() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->db->update(
                'tasks',
                [
                    'status' => 'done',
                    'completed_at' => date('Y-m-d H:i:s')
                ],
                'id = :id',
                ['id' => $id]
            );
        }
        header('Location: ?module=tasks&action=list');
        exit;
    }

    /**
     * Get task statistics (can be used by other modules)
     */
    public function getTaskStats() {
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
            FROM tasks WHERE tenant_id = :tenant_id",
            ['tenant_id' => $this->tenant->getTenantId()]
        );
        return $stats ?? ['total' => 0, 'completed' => 0, 'todo' => 0, 'in_progress' => 0];
    }
}
