<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - <?php echo APP_NAME; ?></title>
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
                <h2>Task Management</h2>
                <p>Track and manage your tasks and to-dos.</p>
                <a href="?module=tasks&action=add" class="btn-primary" style="display:inline-block;margin-top:10px;padding:10px 20px;text-decoration:none;">Add New Task</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:20px;">
                    Operation completed successfully!
                </div>
            <?php endif; ?>

            <div style="margin-bottom:20px;">
                <a href="?module=tasks&action=list&filter=all" style="padding:8px 16px;margin-right:10px;background:<?php echo (!isset($_GET['filter']) || $_GET['filter'] === 'all') ? '#007bff' : '#f8f9fa'; ?>;color:<?php echo (!isset($_GET['filter']) || $_GET['filter'] === 'all') ? 'white' : '#333'; ?>;text-decoration:none;border-radius:4px;">All</a>
                <a href="?module=tasks&action=list&filter=todo" style="padding:8px 16px;margin-right:10px;background:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'todo') ? '#007bff' : '#f8f9fa'; ?>;color:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'todo') ? 'white' : '#333'; ?>;text-decoration:none;border-radius:4px;">To Do</a>
                <a href="?module=tasks&action=list&filter=in_progress" style="padding:8px 16px;margin-right:10px;background:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'in_progress') ? '#007bff' : '#f8f9fa'; ?>;color:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'in_progress') ? 'white' : '#333'; ?>;text-decoration:none;border-radius:4px;">In Progress</a>
                <a href="?module=tasks&action=list&filter=done" style="padding:8px 16px;margin-right:10px;background:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'done') ? '#007bff' : '#f8f9fa'; ?>;color:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'done') ? 'white' : '#333'; ?>;text-decoration:none;border-radius:4px;">Done</a>
                <a href="?module=tasks&action=list&filter=overdue" style="padding:8px 16px;background:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'overdue') ? '#dc3545' : '#f8f9fa'; ?>;color:<?php echo (isset($_GET['filter']) && $_GET['filter'] === 'overdue') ? 'white' : '#333'; ?>;text-decoration:none;border-radius:4px;">Overdue</a>
            </div>

            <div class="module-card">
                <h3>Tasks (<?php echo count($tasks); ?>)</h3>
                
                <?php if (empty($tasks)): ?>
                    <p>No tasks found. <a href="?module=tasks&action=add">Add your first task</a></p>
                <?php else: ?>
                    <table style="width:100%;border-collapse:collapse;margin-top:15px;">
                        <thead>
                            <tr style="background:#f8f9fa;border-bottom:2px solid #dee2e6;">
                                <th style="padding:12px;text-align:left;">Title</th>
                                <th style="padding:12px;text-align:left;">Priority</th>
                                <th style="padding:12px;text-align:left;">Status</th>
                                <th style="padding:12px;text-align:left;">Due Date</th>
                                <th style="padding:12px;text-align:left;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr style="border-bottom:1px solid #dee2e6;">
                                    <td style="padding:12px;">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                        <?php if ($task['description']): ?>
                                            <br><small style="color:#6c757d;"><?php echo htmlspecialchars(substr($task['description'], 0, 60)); ?><?php echo strlen($task['description']) > 60 ? '...' : ''; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <span style="padding:4px 8px;border-radius:4px;font-size:12px;background:<?php echo $task['priority'] === 'high' ? '#dc3545' : ($task['priority'] === 'medium' ? '#ffc107' : '#28a745'); ?>;color:white;">
                                            <?php echo ucfirst($task['priority']); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <span style="padding:4px 8px;border-radius:4px;font-size:12px;background:<?php echo $task['status'] === 'done' ? '#28a745' : ($task['status'] === 'in_progress' ? '#007bff' : '#6c757d'); ?>;color:white;">
                                            <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php 
                                        if ($task['due_date']) {
                                            $isOverdue = $task['status'] !== 'done' && strtotime($task['due_date']) < time();
                                            echo '<span style="color:' . ($isOverdue ? '#dc3545' : '#333') . ';">';
                                            echo htmlspecialchars(date('M j, Y', strtotime($task['due_date'])));
                                            echo '</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td style="padding:12px;">
                                        <?php if ($task['status'] !== 'done'): ?>
                                            <a href="?module=tasks&action=complete&id=<?php echo $task['id']; ?>" style="color:#28a745;margin-right:10px;">✓ Complete</a>
                                        <?php endif; ?>
                                        <a href="?module=tasks&action=edit&id=<?php echo $task['id']; ?>" style="color:#007bff;margin-right:10px;">Edit</a>
                                        <form method="POST" action="?module=tasks&action=delete" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this task?');" 
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
