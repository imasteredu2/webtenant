<?php
/**
 * Database Setup Script
 * Run this file to create the necessary database tables
 */

require_once 'config/config.php';
require_once 'core/Database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    // Create tenants table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS tenants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            subdomain VARCHAR(100) NOT NULL UNIQUE,
            slug VARCHAR(100) NOT NULL,
            admin_email VARCHAR(255) NOT NULL,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            INDEX idx_subdomain (subdomain),
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create modules table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(100) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            version VARCHAR(20) NOT NULL,
            description TEXT,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create tenant_modules table (many-to-many relationship)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS tenant_modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            module_id INT NOT NULL,
            active TINYINT(1) DEFAULT 1,
            settings JSON,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
            UNIQUE KEY unique_tenant_module (tenant_id, module_id),
            INDEX idx_tenant (tenant_id),
            INDEX idx_module (module_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create users table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'user',
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            last_login DATETIME NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            UNIQUE KEY unique_email_per_tenant (tenant_id, email),
            INDEX idx_tenant (tenant_id),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "Database tables created successfully!<br>";
    echo "<a href='index.php'>Go to Application</a>";

} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
