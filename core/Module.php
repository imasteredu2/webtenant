<?php
/**
 * Module Management System
 * Handles loading, activation, and communication between modules
 */

class Module {
    private $db;
    private $tenant;
    private $loadedModules = [];
    private $moduleRegistry = [];

    public function __construct(Database $db, Tenant $tenant) {
        $this->db = $db;
        $this->tenant = $tenant;
        $this->discoverModules();
        $this->loadActiveModules();
    }

    /**
     * Discover all available modules
     */
    private function discoverModules() {
        if (!is_dir(MODULES_DIR)) {
            mkdir(MODULES_DIR, 0755, true);
            return;
        }

        $modules = scandir(MODULES_DIR);
        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') continue;
            
            $modulePath = MODULES_DIR . '/' . $module;
            $configFile = $modulePath . '/module.json';
            
            if (is_dir($modulePath) && file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                if ($config) {
                    $this->moduleRegistry[$module] = [
                        'name' => $config['name'] ?? $module,
                        'version' => $config['version'] ?? '1.0.0',
                        'description' => $config['description'] ?? '',
                        'path' => $modulePath,
                        'main' => $config['main'] ?? 'index.php',
                        'dependencies' => $config['dependencies'] ?? [],
                        'provides' => $config['provides'] ?? []
                    ];
                }
            }
        }
    }

    /**
     * Load active modules for current tenant
     */
    private function loadActiveModules() {
        if (!$this->tenant->getTenantId()) {
            return;
        }

        try {
            $sql = "SELECT m.* FROM modules m 
                    JOIN tenant_modules tm ON m.id = tm.module_id 
                    WHERE tm.tenant_id = :tenant_id AND m.active = 1 AND tm.active = 1";
            
            $modules = $this->db->fetchAll($sql, ['tenant_id' => $this->tenant->getTenantId()]);
            
            foreach ($modules as $moduleData) {
                $this->loadModule($moduleData['slug']);
            }
        } catch (Exception $e) {
            // Module tables might not exist yet
            if (DEBUG_MODE) {
                error_log("Module loading error: " . $e->getMessage());
            }
        }
    }

    /**
     * Load a specific module
     */
    public function loadModule($slug) {
        if (isset($this->loadedModules[$slug])) {
            return $this->loadedModules[$slug];
        }

        if (!isset($this->moduleRegistry[$slug])) {
            throw new Exception("Module {$slug} not found");
        }

        $config = $this->moduleRegistry[$slug];
        
        // Check dependencies
        foreach ($config['dependencies'] as $dependency) {
            if (!isset($this->loadedModules[$dependency])) {
                $this->loadModule($dependency);
            }
        }

        // Load the module
        $modulePath = $config['path'] . '/' . $config['main'];
        if (file_exists($modulePath)) {
            require_once $modulePath;
            
            // Try to instantiate module class
            $className = ucfirst($slug) . 'Module';
            if (class_exists($className)) {
                $this->loadedModules[$slug] = new $className($this->db, $this->tenant, $this);
            } else {
                $this->loadedModules[$slug] = true; // Mark as loaded even if no class
            }
        }

        return $this->loadedModules[$slug] ?? null;
    }

    /**
     * Get loaded module
     */
    public function getModule($slug) {
        return $this->loadedModules[$slug] ?? null;
    }

    /**
     * Get all available modules
     */
    public function getAvailableModules() {
        return $this->moduleRegistry;
    }

    /**
     * Get all loaded modules
     */
    public function getLoadedModules() {
        return $this->loadedModules;
    }

    /**
     * Check if module is loaded
     */
    public function isModuleLoaded($slug) {
        return isset($this->loadedModules[$slug]);
    }

    /**
     * Enable module for tenant
     */
    public function enableModuleForTenant($moduleSlug, $tenantId = null) {
        $tenantId = $tenantId ?? $this->tenant->getTenantId();
        
        // Get module ID
        $module = $this->db->fetchOne("SELECT id FROM modules WHERE slug = :slug", ['slug' => $moduleSlug]);
        
        if (!$module) {
            throw new Exception("Module not found");
        }

        // Check if already enabled
        $existing = $this->db->fetchOne(
            "SELECT id FROM tenant_modules WHERE tenant_id = :tenant_id AND module_id = :module_id",
            ['tenant_id' => $tenantId, 'module_id' => $module['id']]
        );

        if ($existing) {
            // Update to active
            return $this->db->update(
                'tenant_modules',
                ['active' => 1],
                'id = :id',
                ['id' => $existing['id']]
            );
        } else {
            // Insert new
            return $this->db->insert('tenant_modules', [
                'tenant_id' => $tenantId,
                'module_id' => $module['id'],
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Disable module for tenant
     */
    public function disableModuleForTenant($moduleSlug, $tenantId = null) {
        $tenantId = $tenantId ?? $this->tenant->getTenantId();
        
        $module = $this->db->fetchOne("SELECT id FROM modules WHERE slug = :slug", ['slug' => $moduleSlug]);
        
        if (!$module) {
            throw new Exception("Module not found");
        }

        return $this->db->update(
            'tenant_modules',
            ['active' => 0],
            'tenant_id = :tenant_id AND module_id = :module_id',
            ['tenant_id' => $tenantId, 'module_id' => $module['id']]
        );
    }

    /**
     * Register a module in the database
     */
    public function registerModule($slug) {
        if (!isset($this->moduleRegistry[$slug])) {
            throw new Exception("Module {$slug} not found in filesystem");
        }

        $config = $this->moduleRegistry[$slug];

        // Check if already registered
        $existing = $this->db->fetchOne("SELECT id FROM modules WHERE slug = :slug", ['slug' => $slug]);
        
        if ($existing) {
            return $existing['id'];
        }

        return $this->db->insert('modules', [
            'slug' => $slug,
            'name' => $config['name'],
            'version' => $config['version'],
            'description' => $config['description'],
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
