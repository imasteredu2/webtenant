<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1><?php echo APP_NAME; ?></h1>
            <h2>Login</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php 
                    $errorMsg = $_GET['error'];
                    if ($errorMsg === 'empty') {
                        echo 'Please enter both email and password.';
                    } elseif ($errorMsg === 'no_tenant') {
                        echo 'No tenant found. Please check your URL.';
                    } else {
                        echo htmlspecialchars(urldecode($errorMsg));
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:20px;">
                    Registration successful! Please log in.
                </div>
            <?php endif; ?>

            <form method="POST" action="?action=login">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <div style="text-align:center;margin-top:15px;">
                <a href="?module=auth&action=register" style="color:#007bff;">Don't have an account? Register</a>
            </div>

            <div class="login-footer">
                <p>Tenant-based modular application</p>
                <p><small>Version <?php echo APP_VERSION; ?></small></p>
            </div>
        </div>
    </div>
</body>
</html>
