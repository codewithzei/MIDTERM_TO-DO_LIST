<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = $_POST['task_name'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO tasks (task_name, user_id) VALUES (:task_name, :user_id)");
    $stmt->bindParam(':task_name', $task_name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

header(header: 'Location: main.php');
?>