<?php
/**
 * Tenant Management System
 */

class Tenant {
    private $db;
    private $currentTenant = null;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->identifyTenant();
    }

    /**
     * Identify the current tenant based on configuration
     */
    private function identifyTenant() {
        $identifier = null;

        switch (TENANT_MODE) {
            case 'subdomain':
                $identifier = $this->getTenantFromSubdomain();
                break;
            case 'parameter':
                $identifier = $_GET['tenant'] ?? $_POST['tenant'] ?? null;
                break;
            case 'header':
                $identifier = $_SERVER['HTTP_X_TENANT'] ?? null;
                break;
        }

        if ($identifier) {
            $this->loadTenant($identifier);
        }
    }

    /**
     * Extract tenant from subdomain
     */
    private function getTenantFromSubdomain() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $parts = explode('.', $host);
        
        // If there are more than 2 parts, first part is subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }
        
        return null;
    }

    /**
     * Load tenant information from database
     */
    private function loadTenant($identifier) {
        try {
            $sql = "SELECT * FROM tenants WHERE (subdomain = :identifier OR slug = :identifier) AND active = 1";
            $tenant = $this->db->fetchOne($sql, ['identifier' => $identifier]);
            
            if ($tenant) {
                $this->currentTenant = $tenant;
                $this->db->setTenantId($tenant['id']);
                
                // Store in session
                $_SESSION['tenant_id'] = $tenant['id'];
                $_SESSION['tenant_name'] = $tenant['name'];
            }
        } catch (Exception $e) {
            // Tenant table might not exist yet
            if (DEBUG_MODE) {
                error_log("Tenant loading error: " . $e->getMessage());
            }
        }
    }

    /**
     * Get current tenant
     */
    public function getCurrentTenant() {
        return $this->currentTenant;
    }

    /**
     * Get tenant ID
     */
    public function getTenantId() {
        return $this->currentTenant['id'] ?? null;
    }

    /**
     * Create a new tenant
     */
    public function createTenant($data) {
        $requiredFields = ['name', 'subdomain', 'admin_email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }

        // Check if subdomain is already taken
        $existing = $this->db->fetchOne(
            "SELECT id FROM tenants WHERE subdomain = :subdomain",
            ['subdomain' => $data['subdomain']]
        );

        if ($existing) {
            throw new Exception("Subdomain already exists");
        }

        $tenantData = [
            'name' => $data['name'],
            'subdomain' => $data['subdomain'],
            'slug' => $data['slug'] ?? $data['subdomain'],
            'admin_email' => $data['admin_email'],
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('tenants', $tenantData);
    }

    /**
     * Get all tenants
     */
    public function getAllTenants() {
        return $this->db->fetchAll("SELECT * FROM tenants ORDER BY name");
    }

    /**
     * Update tenant
     */
    public function updateTenant($id, $data) {
        $allowedFields = ['name', 'subdomain', 'slug', 'admin_email', 'active'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('tenants', $updateData, 'id = :id', ['id' => $id]);
    }

    /**
     * Delete tenant
     */
    public function deleteTenant($id) {
        // This should also clean up all tenant-specific data
        return $this->db->delete('tenants', 'id = :id', ['id' => $id]);
    }
}
