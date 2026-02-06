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
                    Invalid credentials. Please try again.
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

            <div class="login-footer">
                <p>Tenant-based modular application</p>
                <p><small>Version <?php echo APP_VERSION; ?></small></p>
            </div>
        </div>
    </div>
</body>
</html>
