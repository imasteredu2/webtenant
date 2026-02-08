<?php
/**
 * Main Entry Point for Tenant-Based Modular Web Application
 */

// Start session
session_start();

// Load configuration
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'core/Tenant.php';
require_once 'core/Module.php';
require_once 'core/Router.php';
require_once 'core/CSRF.php';

// Initialize application
$db = new Database();
$tenant = new Tenant($db);
$moduleManager = new Module($db, $tenant);
$router = new Router($moduleManager, $tenant);

// Get current tenant (from subdomain or parameter)
$currentTenant = $tenant->getCurrentTenant();

if (!$currentTenant && !in_array($_GET['action'] ?? '', ['setup', 'install'])) {
    // Redirect to setup if no tenant exists
    header('Location: setup.php');
    exit;
}

// Route the request
$router->route();
