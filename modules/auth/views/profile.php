<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - My Profile</h1>
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
                <h2>My Profile</h2>
                <p>Update your account information.</p>
            </div>

            <?php if (isset($success)): ?>
                <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:20px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div style="background:#f8d7da;color:#dc3545;padding:12px;border-radius:6px;margin-bottom:20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['password_changed'])): ?>
                <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:20px;">
                    Password changed successfully!
                </div>
            <?php endif; ?>

            <div class="modules-grid">
                <div class="module-card">
                    <h3>Profile Information</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn-primary">Update Profile</button>
                    </form>
                </div>

                <div class="module-card">
                    <h3>Account Details</h3>
                    <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
                    <p><strong>Status:</strong> <span style="color:<?php echo $user['active'] ? '#28a745' : '#dc3545'; ?>">
                        <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                    </span></p>
                    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    <?php if ($user['last_login']): ?>
                        <p><strong>Last Login:</strong> <?php echo date('F j, Y g:i A', strtotime($user['last_login'])); ?></p>
                    <?php endif; ?>
                    
                    <div style="margin-top:20px;">
                        <a href="?module=auth&action=change_password" class="btn-primary" 
                           style="display:inline-block;padding:10px 20px;text-decoration:none;">
                            Change Password
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </footer>
    </div>
</body>
</html>
