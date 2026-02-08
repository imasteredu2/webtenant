<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - <?php echo APP_NAME; ?></title>
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
                <h2>User Management</h2>
                <p>Manage users and their access.</p>
                <a href="?module=auth&action=add_user" class="btn-primary" 
                   style="display:inline-block;margin-top:10px;padding:10px 20px;text-decoration:none;">
                    Add New User
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:20px;">
                    Operation completed successfully!
                </div>
            <?php endif; ?>

            <div class="module-card">
                <h3>All Users (<?php echo count($users); ?>)</h3>
                
                <?php if (empty($users)): ?>
                    <p>No users yet. <a href="?module=auth&action=add_user">Add your first user</a></p>
                <?php else: ?>
                    <table style="width:100%;border-collapse:collapse;margin-top:15px;">
                        <thead>
                            <tr style="background:#f8f9fa;border-bottom:2px solid #dee2e6;">
                                <th style="padding:12px;text-align:left;">Name</th>
                                <th style="padding:12px;text-align:left;">Email</th>
                                <th style="padding:12px;text-align:left;">Role</th>
                                <th style="padding:12px;text-align:left;">Status</th>
                                <th style="padding:12px;text-align:left;">Last Login</th>
                                <th style="padding:12px;text-align:left;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr style="border-bottom:1px solid #dee2e6;">
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <span style="padding:4px 8px;border-radius:4px;font-size:12px;background:<?php echo $user['role'] === 'admin' ? '#007bff' : '#6c757d'; ?>;color:white;">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <span style="padding:4px 8px;border-radius:4px;font-size:12px;background:<?php echo $user['active'] ? '#28a745' : '#dc3545'; ?>;color:white;">
                                            <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php echo $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <a href="?module=auth&action=edit_user&id=<?php echo $user['id']; ?>" style="color:#007bff;margin-right:10px;">Edit</a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" action="?module=auth&action=delete_user" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" onclick="return confirm('Delete this user?');" 
                                                        style="background:none;border:none;color:#dc3545;cursor:pointer;padding:0;text-decoration:underline;">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </footer>
    </div>
</body>
</html>
