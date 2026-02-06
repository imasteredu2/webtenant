# Module Development Guide

This guide explains how to create new modules for the WebTenant system.

## Module Principles

Each module in WebTenant must follow these core principles:

1. **Independence**: The module must function on its own without requiring other modules
2. **Tenant Isolation**: All data must be isolated per tenant using tenant_id
3. **Problem-Solving**: Each module must solve a specific problem, no matter how small
4. **Self-Contained**: Include all necessary code, views, and database tables
5. **Optional Collaboration**: Can use other modules when available, but must have fallbacks

## Module Structure

```
modules/
  └── your_module/
      ├── module.json         # Module metadata and configuration
      ├── index.php          # Main module class
      ├── views/             # View templates
      │   ├── list.php       # List view
      │   ├── form.php       # Add/Edit form
      │   └── detail.php     # Detail view (optional)
      └── README.md          # Module-specific documentation (optional)
```

## Step-by-Step Module Creation

### 1. Create Module Directory

```bash
mkdir -p modules/your_module/views
```

### 2. Create module.json

This file defines your module's metadata:

```json
{
    "name": "Your Module Name",
    "version": "1.0.0",
    "description": "Brief description of what your module does",
    "main": "index.php",
    "dependencies": [],
    "provides": ["feature1", "feature2"],
    "author": "Your Name",
    "category": "business"
}
```

**Fields:**
- `name`: Display name of the module
- `version`: Semantic version (e.g., 1.0.0)
- `description`: What problem this module solves
- `main`: Entry point file (usually index.php)
- `dependencies`: Array of module slugs this module can use (optional)
- `provides`: Array of features/capabilities this module provides
- `author`: Module creator
- `category`: business, productivity, utility, personal, etc.

### 3. Create Main Module Class (index.php)

```php
<?php
/**
 * Your Module Name
 * Description of what it does
 */

class YourModuleModule {
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
            // Create your module's tables
            // ALWAYS include tenant_id for isolation
            $this->db->query("
                CREATE TABLE IF NOT EXISTS your_module_data (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id INT NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                    INDEX idx_tenant (tenant_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Your module DB init error: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle module requests
     */
    public function handleRequest($action) {
        switch ($action) {
            case 'list':
                $this->listItems();
                break;
            case 'add':
                $this->addItem();
                break;
            case 'edit':
                $this->editItem();
                break;
            case 'delete':
                $this->deleteItem();
                break;
            default:
                $this->listItems();
        }
    }

    /**
     * List all items
     */
    private function listItems() {
        $items = $this->getItems();
        $tenant = $this->tenant->getCurrentTenant();
        include __DIR__ . '/views/list.php';
    }

    /**
     * Get all items for current tenant
     * This respects tenant isolation automatically via Database class
     */
    public function getItems() {
        return $this->db->fetchAll(
            "SELECT * FROM your_module_data WHERE tenant_id = :tenant_id ORDER BY created_at DESC",
            ['tenant_id' => $this->tenant->getTenantId()]
        );
    }

    // Add more methods as needed
    
    /**
     * Public method that other modules can use
     * Other modules can call this, but should have fallbacks
     */
    public function getItemCount() {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM your_module_data WHERE tenant_id = :tenant_id",
            ['tenant_id' => $this->tenant->getTenantId()]
        );
        return $result['count'] ?? 0;
    }
}
```

### 4. Create Views

#### views/list.php
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Module - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - Your Module</h1>
            <div class="tenant-info">
                Tenant: <strong><?php echo htmlspecialchars($tenant['name']); ?></strong>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </header>

        <nav class="main-nav">
            <a href="?action=dashboard">Dashboard</a>
            <a href="?module=your_module" class="active">Your Module</a>
        </nav>

        <main>
            <!-- Your content here -->
        </main>
    </div>
</body>
</html>
```

### 5. Register the Module

After creating your module, register it in the database:

```sql
INSERT INTO modules (slug, name, version, description, active, created_at)
VALUES ('your_module', 'Your Module Name', '1.0.0', 'Description', 1, NOW());
```

### 6. Enable for a Tenant

```sql
INSERT INTO tenant_modules (tenant_id, module_id, active, created_at)
SELECT 1, id, 1, NOW() FROM modules WHERE slug = 'your_module';
```

## Using Other Modules

Modules can use other modules but must work without them:

```php
// Check if another module is loaded
if ($this->moduleManager->isModuleLoaded('contacts')) {
    $contactsModule = $this->moduleManager->getModule('contacts');
    $contactCount = $contactsModule->getContactCount();
    // Use the data
} else {
    // Fallback when module isn't available
    $contactCount = 0;
}
```

## Database Best Practices

1. **Always use tenant_id**: Every table must have a tenant_id column
2. **Foreign keys**: Link to tenants table with ON DELETE CASCADE
3. **Indexes**: Add indexes on tenant_id and frequently queried columns
4. **Use prepared statements**: Never concatenate user input into SQL
5. **Let Database class handle isolation**: Use the Database class methods

## Module Categories

- **business**: CRM, invoicing, project management
- **productivity**: tasks, notes, calendars
- **personal**: diary, budget tracker, fitness log
- **utility**: calculators, converters, generators
- **communication**: messaging, notifications, email
- **reporting**: analytics, dashboards, reports

## Testing Your Module

1. Test with multiple tenants to ensure data isolation
2. Test without dependent modules to ensure independence
3. Test all CRUD operations (Create, Read, Update, Delete)
4. Test with invalid input and edge cases
5. Test module enablement/disablement

## Module Checklist

Before considering your module complete:

- [ ] module.json is properly configured
- [ ] Module class follows naming convention (ModuleNameModule)
- [ ] Database tables include tenant_id
- [ ] All queries respect tenant isolation
- [ ] Views use proper HTML escaping (htmlspecialchars)
- [ ] Module works without dependent modules
- [ ] Public methods are documented
- [ ] Module solves a specific problem
- [ ] Tested with multiple tenants

## Examples

See the included modules for reference:
- `modules/contacts/` - Contact/CRM management
- `modules/tasks/` - Task tracking

## Common Patterns

### CRUD Operations
Most modules follow this pattern:
1. List view showing all items
2. Add/Edit form for creating/updating items
3. Delete functionality with confirmation
4. Detail view (optional)

### Form Handling
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form
    $data = [
        'field1' => $_POST['field1'],
        'field2' => $_POST['field2'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    try {
        $this->db->insert('your_table', $data);
        header('Location: ?module=your_module&success=1');
        exit;
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
```

### Tenant Data Queries
```php
// The Database class automatically adds tenant_id
$this->db->insert('your_table', $data);

// For queries, always filter by tenant_id
$items = $this->db->fetchAll(
    "SELECT * FROM your_table WHERE tenant_id = :tenant_id",
    ['tenant_id' => $this->tenant->getTenantId()]
);
```

## Support

For questions about module development, refer to:
- Core system documentation in README.md
- Existing module examples
- Database.php and Module.php source code
