<?php

function getDataFromFile($filename) {
    if (!file_exists($filename)) {
        file_put_contents($filename, '');
        return [];
    }
    $content = file_get_contents($filename);
    return $content ? json_decode($content, true) : [];
}

function saveDataToFile($filename, $data) {
    file_put_contents($filename, json_encode($data));
}

function addTask($task_name) {
    $tasks = getDataFromFile('tasks.txt');
    
    // Check for duplicates
    foreach ($tasks as $task) {
        if (strtolower($task['name']) === strtolower($task_name)) {
            return false;
        }
    }
    
    $newTask = [
        'id' => uniqid(),
        'name' => $task_name,
        'completed' => false
    ];
    
    $tasks[] = $newTask;
    saveDataToFile('tasks.txt', $tasks);
    return true;
}

function getAllTasks() {
    return getDataFromFile('tasks.txt');
}

function markTaskAsCompleted($task_id, $is_completed) {
    $tasks = getDataFromFile('tasks.txt');
    
    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            break;
        }
    }
    
    saveDataToFile('tasks.txt', $tasks);
}

function deleteTask($task_id) {
    $tasks = getDataFromFile('tasks.txt');
    
    $tasks = array_filter($tasks, function($task) use ($task_id) {
        return $task['id'] !== $task_id;
    });
    
    saveDataToFile('tasks.txt', array_values($tasks));
}

function generateVerificationCode() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function subscribeEmail($email) {
    $pending = getDataFromFile('pending_subscriptions.txt');
    $subscribers = getDataFromFile('subscribers.txt');
    
    // Check if already verified
    if (in_array($email, $subscribers)) {
        return false;
    }
    
    $code = generateVerificationCode();
    $pending[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    
    saveDataToFile('pending_subscriptions.txt', $pending);
    
    // Send verification email
    $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify.php?email=" . urlencode($email) . "&code=$code";
    
    $subject = "Verify subscription to Task Planner";
    $message = '<p>Click the link below to verify your subscription to Task Planner:</p>';
    $message .= '<p><a id="verification-link" href="' . $verificationLink . '">Verify Subscription</a></p>';
    
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    mail($email, $subject, $message, $headers);
    
    return true;
}

function verifySubscription($email, $code) {
    $pending = getDataFromFile('pending_subscriptions.txt');
    $subscribers = getDataFromFile('subscribers.txt');
    
    if (isset($pending[$email]) && $pending[$email]['code'] === $code) {
        // Add to subscribers
        $subscribers[] = $email;
        saveDataToFile('subscribers.txt', array_unique($subscribers));
        
        // Remove from pending
        unset($pending[$email]);
        saveDataToFile('pending_subscriptions.txt', $pending);
        
        return true;
    }
    
    return false;
}

function unsubscribeEmail($email) {
    $subscribers = getDataFromFile('subscribers.txt');
    
    $subscribers = array_filter($subscribers, function($subscriber) use ($email) {
        return $subscriber !== $email;
    });
    
    saveDataToFile('subscribers.txt', array_values($subscribers));
}

function sendTaskReminders() {
    $subscribers = getDataFromFile('subscribers.txt');
    $tasks = getDataFromFile('tasks.txt');
    
    $pendingTasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });
    
    if (empty($pendingTasks)) {
        return;
    }
    
    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pendingTasks);
    }
}

function sendTaskEmail($email, $pending_tasks) {
    $subject = "Task Planner - Pending Tasks Reminder";
    
    $message = '<h2>Pending Tasks Reminder</h2>';
    $message .= '<p>Here are the current pending tasks:</p>';
    $message .= '<ul>';
    
    foreach ($pending_tasks as $task) {
        $message .= '<li>' . htmlspecialchars($task['name']) . '</li>';
    }
    
    $message .= '</ul>';
    
    $unsubscribeLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/unsubscribe.php?email=" . urlencode($email);
    $message .= '<p><a id="unsubscribe-link" href="' . $unsubscribeLink . '">Unsubscribe from notifications</a></p>';
    
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    mail($email, $subject, $message, $headers);
}
