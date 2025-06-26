<?php
require_once 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$task_id = $_POST['task_id'];
$status = $_POST['status'];

// Verify the task is assigned to the current user
$stmt = $pdo->prepare("SELECT assigned_to FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task || $task['assigned_to'] != $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit;
}

// Update task status
$stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
$stmt->execute([$status, $task_id]);

header("Location: view_task.php?id=$task_id&updated=1");
exit;
?>
