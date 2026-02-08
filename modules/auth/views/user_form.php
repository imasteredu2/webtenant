<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($user) ? 'Edit' : 'Add'; ?> User - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - User Management</h1>
            <div class="tenant-info">
                Tenant: <strong><?php echo htmlspecialchars($tenant['name']); ?></strong>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </header>

        <nav class="main-nav">
            <a href="?action=dashboard">Dashboard</a>
            <a href="?module=auth&action=users" class="active">Users</a>
            <a href="?module=auth&action=profile">Profile</a>
        </nav>

        <main>
            <div class="welcome">
                <h2><?php echo isset($user) ? 'Edit User' : 'Add New User'; ?></h2>
                <p><a href="?module=auth&action=users">&larr; Back to Users</a></p>
            </div>

            <?php if (isset($error)): ?>
                <div style="background:#f8d7da;color:#dc3545;padding:12px;border-radius:6px;margin-bottom:20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="module-card">
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password <?php echo isset($user) ? '(leave blank to keep current)' : '*'; ?></label>
                        <input type="password" id="password" name="password" minlength="8"
                               <?php echo isset($user) ? '' : 'required'; ?>>
                        <small style="color:#6c757d;">Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" style="width:100%;padding:12px;border:1px solid #dee2e6;border-radius:6px;">
                            <option value="user" <?php echo (!isset($user) || $user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo (isset($user) && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <?php if (isset($user)): ?>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="active" value="1" <?php echo $user['active'] ? 'checked' : ''; ?>>
                                Active
                            </label>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-primary">
                        <?php echo isset($user) ? 'Update User' : 'Create User'; ?>
                    </button>
                    <a href="?module=auth&action=users" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </footer>
    </div>
</body>
</html>
