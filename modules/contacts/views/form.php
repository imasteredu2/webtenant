<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($contact) ? 'Edit' : 'Add'; ?> Contact - <?php echo APP_NAME; ?></title>
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
                <h2><?php echo isset($contact) ? 'Edit Contact' : 'Add New Contact'; ?></h2>
                <p><a href="?module=contacts&action=list">&larr; Back to Contacts</a></p>
            </div>

            <?php if (isset($error)): ?>
                <div style="background:#f8d7da;color:#dc3545;padding:12px;border-radius:6px;margin-bottom:20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="module-card">
                <form method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($contact['first_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($contact['last_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($contact['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($contact['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="company">Company</label>
                        <input type="text" id="company" name="company" 
                               value="<?php echo htmlspecialchars($contact['company'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" 
                               value="<?php echo htmlspecialchars($contact['position'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="4" 
                                  style="width:100%;padding:12px;border:1px solid #dee2e6;border-radius:6px;"><?php echo htmlspecialchars($contact['notes'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary">
                        <?php echo isset($contact) ? 'Update Contact' : 'Add Contact'; ?>
                    </button>
                    <a href="?module=contacts&action=list" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </footer>
    </div>
</body>
</html>
