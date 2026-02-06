<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - Contacts</h1>
            <div class="tenant-info">
                Tenant: <strong><?php echo htmlspecialchars($tenant['name'] ?? 'N/A'); ?></strong>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </header>

        <nav class="main-nav">
            <a href="?action=dashboard">Dashboard</a>
            <a href="?module=contacts" class="active">Contacts</a>
        </nav>

        <main>
            <div class="welcome">
                <h2>Contact Management</h2>
                <p>Manage your contacts and customer relationships.</p>
                <a href="?module=contacts&action=add" class="btn-primary" style="display:inline-block;margin-top:10px;padding:10px 20px;text-decoration:none;">Add New Contact</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:20px;">
                    Operation completed successfully!
                </div>
            <?php endif; ?>

            <div class="module-card">
                <h3>All Contacts (<?php echo count($contacts); ?>)</h3>
                
                <?php if (empty($contacts)): ?>
                    <p>No contacts yet. <a href="?module=contacts&action=add">Add your first contact</a></p>
                <?php else: ?>
                    <table style="width:100%;border-collapse:collapse;margin-top:15px;">
                        <thead>
                            <tr style="background:#f8f9fa;border-bottom:2px solid #dee2e6;">
                                <th style="padding:12px;text-align:left;">Name</th>
                                <th style="padding:12px;text-align:left;">Email</th>
                                <th style="padding:12px;text-align:left;">Phone</th>
                                <th style="padding:12px;text-align:left;">Company</th>
                                <th style="padding:12px;text-align:left;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr style="border-bottom:1px solid #dee2e6;">
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($contact['email'] ?? '-'); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($contact['phone'] ?? '-'); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($contact['company'] ?? '-'); ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <a href="?module=contacts&action=edit&id=<?php echo $contact['id']; ?>" style="color:#007bff;margin-right:10px;">Edit</a>
                                        <form method="POST" action="?module=contacts&action=delete" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this contact?');" 
                                                    style="background:none;border:none;color:#dc3545;cursor:pointer;padding:0;text-decoration:underline;">Delete</button>
                                        </form>
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
