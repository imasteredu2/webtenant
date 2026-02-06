# WebTenant - Modular Multi-Tenant Web Application

A PHP-based, modular, tenant-isolated web application framework that allows you to build and manage multiple independent applications (modules) that share a common infrastructure while maintaining strict data isolation between tenants.

## Features

- **Multi-Tenancy**: Full tenant isolation with support for subdomain, parameter, or header-based tenant identification
- **Modular Architecture**: Each module operates independently and can function with or without other modules
- **Database-Driven**: MySQL/MariaDB-based with automatic tenant data isolation
- **Extensible**: Easy to add new modules without affecting existing functionality
- **Business & Personal Use**: Suitable for both business applications (CRM, tasks) and personal tools

## Architecture

### Core Components

1. **Tenant System**: Manages multiple tenants with complete data isolation
2. **Module System**: Dynamic module loading and management
3. **Database Layer**: Automatic tenant filtering and data isolation
4. **Router**: Request routing to appropriate modules or core pages

### Included Modules

- **Contacts/CRM Module**: Manage contacts, companies, and customer relationships
- **Task Management Module**: Track tasks, to-dos, priorities, and deadlines

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- mod_rewrite enabled (for Apache)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/imasteredu2/webtenant.git
   cd webtenant
   ```

2. **Configure Database**
   
   Create a MySQL database:
   ```sql
   CREATE DATABASE webtenant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Update `config/config.php` with your database credentials or set environment variables:
   ```bash
   export DB_HOST=localhost
   export DB_NAME=webtenant
   export DB_USER=your_user
   export DB_PASS=your_password
   ```

3. **Run Setup**
   
   Navigate to `http://yourdomain.com/setup.php` to create database tables.

4. **Create First Tenant**
   
   Use the database to create your first tenant:
   ```sql
   INSERT INTO tenants (name, subdomain, slug, admin_email, active, created_at)
   VALUES ('Demo Company', 'demo', 'demo', 'admin@demo.com', 1, NOW());
   ```

5. **Register Modules**
   
   Register the included modules:
   ```sql
   INSERT INTO modules (slug, name, version, description, active, created_at)
   VALUES 
   ('contacts', 'Contacts/CRM Module', '1.0.0', 'Contact management module', 1, NOW()),
   ('tasks', 'Task Management Module', '1.0.0', 'Task tracking module', 1, NOW());
   ```

6. **Enable Modules for Tenant**
   ```sql
   INSERT INTO tenant_modules (tenant_id, module_id, active, created_at)
   SELECT 1, id, 1, NOW() FROM modules WHERE active = 1;
   ```

7. **Access Application**
   - Main site: `http://yourdomain.com`
   - Tenant site (subdomain mode): `http://demo.yourdomain.com`

## Usage

### Creating a New Module

Each module must:
1. Work independently of other modules
2. Respect tenant isolation
3. Solve a specific problem
4. Be self-contained with its own database tables

#### Module Structure

```
modules/
  └── your_module/
      ├── module.json          # Module configuration
      ├── index.php           # Main module class
      └── views/              # Module views
          ├── list.php
          └── form.php
```

#### module.json Example

```json
{
    "name": "Your Module Name",
    "version": "1.0.0",
    "description": "What this module does",
    "main": "index.php",
    "dependencies": [],
    "provides": ["feature1", "feature2"],
    "author": "Your Name",
    "category": "business"
}
```

#### Module Class Example

```php
<?php
class YourModule {
    private $db;
    private $tenant;
    private $moduleManager;

    public function __construct(Database $db, Tenant $tenant, Module $moduleManager) {
        $this->db = $db;
        $this->tenant = $tenant;
        $this->moduleManager = $moduleManager;
        $this->initDatabase();
    }

    private function initDatabase() {
        // Create module-specific tables
        // Always include tenant_id for isolation
    }

    public function handleRequest($action) {
        // Route module requests
    }

    // Public methods that other modules can use
    public function getDataForOtherModules() {
        // Modules can use each other's public methods
        // But must work without them
    }
}
```

### Module Communication

Modules can use other modules but must still function independently:

```php
// In your module
if ($this->moduleManager->isModuleLoaded('contacts')) {
    $contactsModule = $this->moduleManager->getModule('contacts');
    $contactCount = $contactsModule->getContactCount();
    // Use the data but have a fallback if module isn't loaded
} else {
    $contactCount = 0; // Fallback value
}
```

## Configuration

### Tenant Mode

Edit `config/config.php`:

```php
// Options: subdomain, parameter, header
define('TENANT_MODE', 'subdomain');
```

- **subdomain**: `demo.yourdomain.com` (recommended)
- **parameter**: `yourdomain.com?tenant=demo`
- **header**: Custom HTTP header `X-Tenant: demo`

### Debug Mode

```php
define('DEBUG_MODE', true); // Enable for development
```

## Security Features

- Automatic tenant data isolation
- Prepared statements for all queries
- Password hashing (bcrypt)
- XSS protection via htmlspecialchars
- CSRF protection (implement as needed)

## Module Development Guidelines

1. **Independence**: Module must work without other modules
2. **Tenant Isolation**: Always use tenant_id in database queries
3. **Self-Contained**: Include all necessary database tables
4. **Problem-Solving**: Each module must solve a specific problem
5. **Documentation**: Document what the module does and how to use it

## Database Schema

### Core Tables

- `tenants`: Tenant information
- `modules`: Available modules
- `tenant_modules`: Which modules are enabled for which tenants
- `users`: User accounts (tenant-specific)

### Module Tables

Each module creates its own tables with `tenant_id` for isolation.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Follow the module development guidelines
4. Test with multiple tenants
5. Submit a pull request

## License

This software is released into the public domain under the Unlicense. See LICENSE file for details.

## Support

For issues, questions, or contributions, please visit:
https://github.com/imasteredu2/webtenant

## Roadmap

- [ ] Authentication module with proper user management
- [ ] Role-based access control (RBAC)
- [ ] Module marketplace/registry
- [ ] API endpoints for modules
- [ ] Module installer wizard
- [ ] Tenant administration dashboard
- [ ] Module dependency resolution
- [ ] Automated testing framework