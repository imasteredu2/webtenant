<?php
/**
 * Contacts/CRM Module
 * Provides contact and customer relationship management functionality
 */

class ContactsModule {
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
                CREATE TABLE IF NOT EXISTS contacts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id INT NOT NULL,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    email VARCHAR(255),
                    phone VARCHAR(50),
                    company VARCHAR(255),
                    position VARCHAR(100),
                    notes TEXT,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                    INDEX idx_tenant (tenant_id),
                    INDEX idx_email (email)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Contacts module DB init error: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle module requests
     */
    public function handleRequest($action) {
        switch ($action) {
            case 'list':
                $this->listContacts();
                break;
            case 'add':
                $this->addContact();
                break;
            case 'edit':
                $this->editContact();
                break;
            case 'delete':
                $this->deleteContact();
                break;
            default:
                $this->listContacts();
        }
    }

    /**
     * List all contacts
     */
    private function listContacts() {
        $contacts = $this->getContacts();
        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/list.php';
    }

    /**
     * Get all contacts for current tenant
     */
    public function getContacts() {
        return $this->db->fetchAll(
            "SELECT * FROM contacts WHERE tenant_id = :tenant_id ORDER BY last_name, first_name",
            ['tenant_id' => $this->tenant->getTenantId()]
        );
    }

    /**
     * Add a new contact
     */
    private function addContact() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'company' => $_POST['company'] ?? null,
                'position' => $_POST['position'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            try {
                $this->db->insert('contacts', $data);
                header('Location: ?module=contacts&action=list&success=1');
                exit;
            } catch (Exception $e) {
                $error = "Error adding contact: " . $e->getMessage();
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/form.php';
    }

    /**
     * Edit a contact
     */
    private function editContact() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=contacts&action=list');
            exit;
        }

        $contact = $this->db->fetchOne(
            "SELECT * FROM contacts WHERE id = :id AND tenant_id = :tenant_id",
            ['id' => $id, 'tenant_id' => $this->tenant->getTenantId()]
        );

        if (!$contact) {
            header('Location: ?module=contacts&action=list');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'company' => $_POST['company'] ?? null,
                'position' => $_POST['position'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            try {
                $this->db->update('contacts', $data, 'id = :id', ['id' => $id]);
                header('Location: ?module=contacts&action=list&success=1');
                exit;
            } catch (Exception $e) {
                $error = "Error updating contact: " . $e->getMessage();
            }
        }

        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/form.php';
    }

    /**
     * Delete a contact
     */
    private function deleteContact() {
        $id = $_GET['id'] ?? null;
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->db->delete('contacts', 'id = :id', ['id' => $id]);
        }
        header('Location: ?module=contacts&action=list');
        exit;
    }

    /**
     * Get contact count (can be used by other modules)
     */
    public function getContactCount() {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM contacts WHERE tenant_id = :tenant_id",
            ['tenant_id' => $this->tenant->getTenantId()]
        );
        return $result['count'] ?? 0;
    }
}
