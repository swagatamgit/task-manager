<?php
require_once 'functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        $taskName = trim($_POST['task-name']);
        if (!empty($taskName)) {
            addTask($taskName);
        }
    } elseif (isset($_POST['email'])) {
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        if ($email) {
            subscribeEmail($email);
            $subscriptionMessage = "A verification email has been sent to $email";
        }
    }
}

// Handle task actions via GET
if (isset($_GET['action'])) {
    $taskId = $_GET['id'] ?? '';
    switch ($_GET['action']) {
        case 'complete':
            markTaskAsCompleted($taskId, true);
            break;
        case 'incomplete':
            markTaskAsCompleted($taskId, false);
            break;
        case 'delete':
            deleteTask($taskId);
            break;
    }
    header("Location: index.php");
    exit();
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <style>
        .task-item.completed {
            text-decoration: line-through;
            opacity: 0.7;
        }
        .tasks-list {
            list-style: none;
            padding: 0;
        }
        .task-item {
            margin: 5px 0;
            padding: 5px;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <h1>Task Management System</h1>
    
    <h2>Add New Task</h2>
    <form method="post">
        <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
        <button type="submit" id="add-task">Add Task</button>
    </form>
    
    <h2>Tasks</h2>
    <ul class="tasks-list">
        <?php foreach ($tasks as $task): ?>
            <li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
                <input type="checkbox" class="task-status" 
                    <?= $task['completed'] ? 'checked' : '' ?>
                    onclick="window.location.href='index.php?action=<?= $task['completed'] ? 'incomplete' : 'complete' ?>&id=<?= $task['id'] ?>'">
                <?= htmlspecialchars($task['name']) ?>
                <button class="delete-task" 
                    onclick="window.location.href='index.php?action=delete&id=<?= $task['id'] ?>'">Delete</button>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <h2>Email Subscription</h2>
    <form method="post">
        <input type="email" name="email" required placeholder="Enter your email">
        <button id="submit-email">Subscribe to Hourly Reminders</button>
    </form>
    <?php if (isset($subscriptionMessage)): ?>
        <p><?= $subscriptionMessage ?></p>
    <?php endif; ?>
</body>
</html>
