<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    
    if (empty($title)) {
        $error = 'Task title is required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $title, $description, $due_date ?: null])) {
            $success = 'Task created successfully!';
        } else {
            $error = 'Failed to create task.';
        }
    }
}

// Handle task update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $task_id = $_POST['task_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    
    if (empty($title)) {
        $error = 'Task title is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$title, $description, $due_date ?: null, $task_id, $user_id])) {
            $success = 'Task updated successfully!';
        } else {
            $error = 'Failed to update task.';
        }
    }
}

// Handle task deletion
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$task_id, $user_id])) {
        $success = 'Task deleted successfully!';
    } else {
        $error = 'Failed to delete task.';
    }
}

// Handle task completion toggle
if (isset($_GET['toggle'])) {
    $task_id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE tasks SET is_completed = NOT is_completed WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$task_id, $user_id])) {
        $success = 'Task status updated!';
    } else {
        $error = 'Failed to update task status.';
    }
}

// Get all tasks for the current user
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get task for editing
$edit_task = null;
if (isset($_GET['edit'])) {
    $task_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $edit_task = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $edit_task ? 'Edit Task' : 'Add New Task'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="taskForm">
                            <input type="hidden" name="action" value="<?php echo $edit_task ? 'update' : 'create'; ?>">
                            <?php if ($edit_task): ?>
                                <input type="hidden" name="task_id" value="<?php echo $edit_task['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Task Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo $edit_task ? htmlspecialchars($edit_task['title']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_task ? htmlspecialchars($edit_task['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" 
                                       value="<?php echo $edit_task ? $edit_task['due_date'] : ''; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <?php echo $edit_task ? 'Update Task' : 'Add Task'; ?>
                            </button>
                            
                            <?php if ($edit_task): ?>
                                <a href="tasks.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>My Tasks (<?php echo count($tasks); ?>)</h5>
                        <div>
                            <a href="tasks.php" class="btn btn-sm btn-outline-primary">All</a>
                            <a href="tasks.php?filter=pending" class="btn btn-sm btn-outline-warning">Pending</a>
                            <a href="tasks.php?filter=completed" class="btn btn-sm btn-outline-success">Completed</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tasks)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-tasks fa-3x mb-3"></i>
                                <p>No tasks found. Create your first task!</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($tasks as $task): ?>
                                    <div class="list-group-item <?php echo $task['is_completed'] ? 'list-group-item-success' : ''; ?>">
                                        <div class="d-flex w-100 justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 <?php echo $task['is_completed'] ? 'text-decoration-line-through' : ''; ?>">
                                                    <?php echo htmlspecialchars($task['title']); ?>
                                                </h6>
                                                <?php if ($task['description']): ?>
                                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($task['description']); ?></p>
                                                <?php endif; ?>
                                                <small class="text-muted">
                                                    Created: <?php echo date('M j, Y', strtotime($task['created_at'])); ?>
                                                    <?php if ($task['due_date']): ?>
                                                        | Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="tasks.php?toggle=<?php echo $task['id']; ?>" 
                                                   class="btn btn-outline-<?php echo $task['is_completed'] ? 'warning' : 'success'; ?>"
                                                   title="<?php echo $task['is_completed'] ? 'Mark as Pending' : 'Mark as Complete'; ?>">
                                                    <i class="fas fa-<?php echo $task['is_completed'] ? 'undo' : 'check'; ?>"></i>
                                                </a>
                                                <a href="tasks.php?edit=<?php echo $task['id']; ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="tasks.php?delete=<?php echo $task['id']; ?>" 
                                                   class="btn btn-outline-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this task?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/validation.js"></script>
</body>
</html>
