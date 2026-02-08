<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($task) ? 'Edit' : 'Add'; ?> Task - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?> - Tasks</h1>
            <div class="tenant-info">
                Tenant: <strong><?php echo htmlspecialchars($tenant['name'] ?? 'N/A'); ?></strong>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </header>

        <nav class="main-nav">
            <a href="?action=dashboard">Dashboard</a>
            <a href="?module=tasks" class="active">Tasks</a>
        </nav>

        <main>
            <div class="welcome">
                <h2><?php echo isset($task) ? 'Edit Task' : 'Add New Task'; ?></h2>
                <p><a href="?module=tasks&action=list">&larr; Back to Tasks</a></p>
            </div>

            <?php if (isset($error)): ?>
                <div style="background:#f8d7da;color:#dc3545;padding:12px;border-radius:6px;margin-bottom:20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="module-card">
                <form method="POST">
                    <?php echo CSRF::getInputField(); ?>
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo htmlspecialchars($task['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" 
                                  style="width:100%;padding:12px;border:1px solid #dee2e6;border-radius:6px;"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" style="width:100%;padding:12px;border:1px solid #dee2e6;border-radius:6px;">
                            <option value="low" <?php echo (isset($task) && $task['priority'] === 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo (!isset($task) || $task['priority'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo (isset($task) && $task['priority'] === 'high') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" style="width:100%;padding:12px;border:1px solid #dee2e6;border-radius:6px;">
                            <option value="todo" <?php echo (!isset($task) || $task['status'] === 'todo') ? 'selected' : ''; ?>>To Do</option>
                            <option value="in_progress" <?php echo (isset($task) && $task['status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="done" <?php echo (isset($task) && $task['status'] === 'done') ? 'selected' : ''; ?>>Done</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date" 
                               value="<?php echo htmlspecialchars($task['due_date'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn-primary">
                        <?php echo isset($task) ? 'Update Task' : 'Add Task'; ?>
                    </button>
                    <a href="?module=tasks&action=list" style="margin-left:10px;">Cancel</a>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
        </footer>
    </div>
</body>
</html>
