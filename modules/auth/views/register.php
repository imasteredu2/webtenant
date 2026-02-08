<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1><?php echo APP_NAME; ?></h1>
            <h2>Create Account</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="?module=auth&action=register">
                <?php echo CSRF::getInputField(); ?>
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password * (min 8 characters)</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>

                <button type="submit" class="btn-primary">Create Account</button>
            </form>

            <div style="text-align:center;margin-top:15px;">
                <a href="?action=login" style="color:#007bff;">Already have an account? Login</a>
            </div>

            <div class="login-footer">
                <p>Tenant: <strong><?php echo htmlspecialchars($tenant['name'] ?? 'N/A'); ?></strong></p>
                <p><small>Version <?php echo APP_VERSION; ?></small></p>
            </div>
        </div>
    </div>
</body>
</html>
