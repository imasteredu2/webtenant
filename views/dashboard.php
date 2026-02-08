<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - Dashboard</h1>
            <div class="tenant-info">
                <?php if (isset($_SESSION['user_name'])): ?>
                    User: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> |
                <?php endif; ?>
                Tenant: <strong><?php echo htmlspecialchars($tenant['name'] ?? 'N/A'); ?></strong>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </header>

        <nav class="main-nav">
            <a href="?action=dashboard" class="active">Dashboard</a>
            <?php if (!empty($modules)): ?>
                <?php foreach ($modules as $slug => $module): ?>
                    <?php if (is_object($module)): ?>
                        <a href="?module=<?php echo htmlspecialchars($slug); ?>">
                            <?php echo htmlspecialchars(ucfirst($slug)); ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>

        <main>
            <div class="welcome">
                <h2>Welcome<?php if (isset($_SESSION['user_name'])): ?>, <?php echo htmlspecialchars($_SESSION['user_name']); ?><?php endif; ?>!</h2>
                <p>This is a modular, tenant-based web application.</p>
                <?php if (isset($_GET['registered'])): ?>
                    <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-top:10px;">
                        Welcome! Your account has been created successfully.
                    </div>
                <?php endif; ?>
            </div>

            <div class="modules-grid">
                <div class="module-card">
                    <h3>Tenant Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($tenant['name'] ?? 'N/A'); ?></p>
                    <p><strong>Subdomain:</strong> <?php echo htmlspecialchars($tenant['subdomain'] ?? 'N/A'); ?></p>
                    <p><strong>Admin Email:</strong> <?php echo htmlspecialchars($tenant['admin_email'] ?? 'N/A'); ?></p>
                </div>

                <div class="module-card">
                    <h3>Active Modules</h3>
                    <?php if (empty($modules)): ?>
                        <p>No modules currently active.</p>
                    <?php else: ?>
                        <ul class="module-list">
                            <?php foreach ($modules as $slug => $module): ?>
                                <li>
                                    <a href="?module=<?php echo htmlspecialchars($slug); ?>">
                                        <?php echo htmlspecialchars(ucfirst($slug)); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="module-card">
                    <h3>Quick Actions</h3>
                    <ul class="action-list">
                        <li><a href="?module=auth&action=profile">My Profile</a></li>
                        <li><a href="?module=auth&action=users">Manage Users</a></li>
                        <li><a href="?module=auth&action=change_password">Change Password</a></li>
                    </ul>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></p>
        </footer>
    </div>
</body>
</html>
