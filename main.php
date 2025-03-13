<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$edit_task = null;

$stmt_username = $conn->prepare("SELECT username FROM users WHERE id = :user_id");
$stmt_username->bindParam(':user_id', $user_id);
$stmt_username->execute();
$user = $stmt_username->fetch(PDO::FETCH_ASSOC);
$username = $user['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task_id'])) {
        $task_id = $_POST['task_id'];
        $task_name = $_POST['task_name'];

        $stmt = $conn->prepare("UPDATE tasks SET task_name = :task_name WHERE id = :task_id AND user_id = :user_id");
        $stmt->bindParam(':task_name', $task_name);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    } else {
        $task_name = $_POST['task_name'];

        $stmt = $conn->prepare("INSERT INTO tasks (task_name, user_id, is_completed) VALUES (:task_name, :user_id, 0)");
        $stmt->bindParam(':task_name', $task_name);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }
    header('Location: main.php');
    exit();
}

$tasks_per_page = 5;
$incomplete_page = isset($_GET['incomplete_page']) ? (int)$_GET['incomplete_page'] : 1;
$completed_page = isset($_GET['completed_page']) ? (int)$_GET['completed_page'] : 1;

$incomplete_offset = ($incomplete_page - 1) * $tasks_per_page;

$stmt_incomplete = $conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id AND is_completed = 0 LIMIT :limit OFFSET :offset");
$stmt_incomplete->bindParam(':user_id', $user_id);
$stmt_incomplete->bindParam(':limit', $tasks_per_page, PDO::PARAM_INT);
$stmt_incomplete->bindParam(':offset', $incomplete_offset, PDO::PARAM_INT);
$stmt_incomplete->execute();
$incomplete_tasks = $stmt_incomplete->fetchAll(PDO::FETCH_ASSOC);

$stmt_incomplete_count = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND is_completed = 0");
$stmt_incomplete_count->bindParam(':user_id', $user_id);
$stmt_incomplete_count->execute();
$total_incomplete_tasks = $stmt_incomplete_count->fetchColumn();
$total_incomplete_pages = ceil($total_incomplete_tasks / $tasks_per_page);

$completed_offset = ($completed_page - 1) * $tasks_per_page;

$stmt_completed = $conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id AND is_completed = 1 LIMIT :limit OFFSET :offset");
$stmt_completed->bindParam(':user_id', $user_id);
$stmt_completed->bindParam(':limit', $tasks_per_page, PDO::PARAM_INT);
$stmt_completed->bindParam(':offset', $completed_offset, PDO::PARAM_INT);
$stmt_completed->execute();
$completed_tasks = $stmt_completed->fetchAll(PDO::FETCH_ASSOC);

$stmt_completed_count = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND is_completed = 1");
$stmt_completed_count->bindParam(':user_id', $user_id);
$stmt_completed_count->execute();
$total_completed_tasks = $stmt_completed_count->fetchColumn();
$total_completed_pages = ceil($total_completed_tasks / $tasks_per_page);

if (isset($_GET['edit_id'])) {
    $task_id = $_GET['edit_id'];

    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :task_id AND user_id = :user_id");
    $stmt->bindParam(':task_id', $task_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $edit_task = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="title">
        <h1>TO-DO LIST</h1>
    </div>
    <div class="greetings">
        <h1>Hello, <?php echo htmlspecialchars($username); ?>!</h1>
    </div>
    <form action="main.php" method="POST">
        <?php if ($edit_task): ?>
            <input type="hidden" name="task_id" value="<?php echo $edit_task['id']; ?>">
        <?php endif; ?>
        <input type="text" name="task_name" placeholder="Enter a new task" value="<?php echo $edit_task ? htmlspecialchars($edit_task['task_name']) : ''; ?>" required>
        <button type="submit"><?php echo $edit_task ? 'Save' : 'Add Task'; ?></button>
    </form>
    <div class="task-list">
        <h2>Task List</h2>
        <ul>
            <?php foreach ($incomplete_tasks as $task): ?>
                <li>
                    <span><?php echo htmlspecialchars($task['task_name']); ?></span>
                    <div class="actions">
                        <a href="main.php?edit_id=<?php echo $task['id']; ?>&incomplete_page=<?php echo $incomplete_page; ?>&completed_page=<?php echo $completed_page; ?>" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="complete_task.php?id=<?php echo $task['id']; ?>" title="Complete">
                            <i class="fa-solid fa-check"></i>
                        </a>
                        <a href="delete_task.php?id=<?php echo $task['id']; ?>" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_incomplete_pages; $i++): ?>
                <a href="main.php?incomplete_page=<?php echo $i; ?>&completed_page=<?php echo $completed_page; ?>" class="<?php echo $i == $incomplete_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>

        <h2>Completed Tasks</h2>
        <ul>
            <?php foreach ($completed_tasks as $task): ?>
                <li class="completed">
                    <span><?php echo htmlspecialchars($task['task_name']); ?></span>
                    <div class="actions">
                        <a href="delete_task.php?id=<?php echo $task['id']; ?>" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_completed_pages; $i++): ?>
                <a href="main.php?completed_page=<?php echo $i; ?>&incomplete_page=<?php echo $incomplete_page; ?>" class="<?php echo $i == $completed_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <a href="logout.php" class="logout-button">Log Out</a>
</body>
</html>