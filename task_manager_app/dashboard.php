<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

$user = getCurrentUser();
$user_id = getCurrentUserId();

// Get task statistics
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) as pending_tasks,
    SUM(CASE WHEN due_date < CURDATE() AND is_completed = 0 THEN 1 ELSE 0 END) as overdue_tasks
    FROM tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming tasks (due in next 7 days)
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND is_completed = 0 ORDER BY due_date ASC");
$stmt->execute([$user_id]);
$upcoming_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                <p class="text-muted">Here's an overview of your tasks and progress.</p>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $stats['total_tasks']; ?></h4>
                                <p class="mb-0">Total Tasks</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tasks fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $stats['completed_tasks']; ?></h4>
                                <p class="mb-0">Completed</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $stats['pending_tasks']; ?></h4>
                                <p class="mb-0">Pending</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $stats['overdue_tasks']; ?></h4>
                                <p class="mb-0">Overdue</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Tasks -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Recent Tasks</h5>
                        <a href="tasks.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_tasks)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-tasks fa-2x mb-2"></i>
                                <p>No tasks yet. <a href="tasks.php">Create your first task!</a></p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_tasks as $task): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 <?php echo $task['is_completed'] ? 'text-decoration-line-through text-muted' : ''; ?>">
                                                <?php echo htmlspecialchars($task['title']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($task['created_at'])); ?>
                                                <?php if ($task['due_date']): ?>
                                                    | Due: <?php echo date('M j', strtotime($task['due_date'])); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?php echo $task['is_completed'] ? 'success' : 'warning'; ?>">
                                            <?php echo $task['is_completed'] ? 'Completed' : 'Pending'; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Tasks -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Upcoming Tasks</h5>
                        <a href="tasks.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_tasks)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-calendar fa-2x mb-2"></i>
                                <p>No upcoming tasks in the next 7 days.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcoming_tasks as $task): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h6>
                                            <small class="text-muted">
                                                Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-info">
                                            <?php 
                                            $days_left = (strtotime($task['due_date']) - time()) / (60 * 60 * 24);
                                            echo ceil($days_left) . ' days left';
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="tasks.php" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-plus"></i> Add New Task
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="tasks.php" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-list"></i> View All Tasks
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="profile.php" class="btn btn-outline-secondary w-100 mb-2">
                                    <i class="fas fa-user"></i> Manage Profile
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="logout.php" class="btn btn-outline-danger w-100 mb-2">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/validation.js"></script>
</body>
</html>
