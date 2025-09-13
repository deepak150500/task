<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-tasks"></i> Task Manager
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tasks.php">
                        <i class="fas fa-list"></i> Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
