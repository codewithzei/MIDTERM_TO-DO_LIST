<?php
session_start();
include 'db.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :task_id AND user_id = :user_id");
    $stmt->bindParam(':task_id', $task_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

header('Location: main.php');
?>