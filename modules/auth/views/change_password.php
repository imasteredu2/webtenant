<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - Change Password</h1>
            <div class="tenant-info">
                Tenant: <strong><?php echo htmlspecialchars($tenant['name']); ?></strong>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </header>

        <nav class="main-nav">
            <a href="?action=dashboard">Dashboard</a>
            <a href="?module=auth&action=users">Users</a>
            <a href="?module=auth&action=profile" class="active">Profile</a>
        </nav>

        <main>
            <div class="welcome">
                <h2>Change Password</h2>
                <p><a href="?module=auth&action=profile">&larr; Back to Profile</a></p>
            </div>

            <?php if (isset($error)): ?>
                <div style="background:#f8d7da;color:#dc3545;padding:12px;border-radius:6px;margin-bottom:20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="module-card" style="max-width:500px;">
                <form method="POST">
                    <?php echo CSRF::getInputField(); ?>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password (min 8 characters)</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>

                    <button type="submit" class="btn-primary">Change Password</button>
                    <a href="?module=auth&action=profile" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </footer>
    </div>
</body>
</html>
