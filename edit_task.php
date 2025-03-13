<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $task_name = $_POST['task_name'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE tasks SET task_name = :task_name WHERE id = :task_id AND user_id = :user_id");
    $stmt->bindParam(':task_name', $task_name);
    $stmt->bindParam(':task_id', $task_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header('Location: main.php');
    exit();
}

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :task_id AND user_id = :user_id");
    $stmt->bindParam(':task_id', $task_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        die("Task not found or you don't have permission to edit it.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <h1>Edit Task</h1>
    <form action="edit_task.php" method="POST">
        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
        <input type="text" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
        <button type="submit">Save</button>
    </form>
</body>
</html>