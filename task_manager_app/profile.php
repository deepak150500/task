<?php
require_once 'config/database.php';
require_once 'includes/session.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        
        if ($stmt->fetch()) {
            $error = 'Email already taken by another user.';
        } else {
            // Update user profile
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $user['id']])) {
                $success = 'Profile updated successfully!';
                // Update session data
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                // Refresh user data
                $user = getCurrentUser();
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}

// Handle profile photo upload
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['profile_photo']['type'];
    
    if (in_array($file_type, $allowed_types)) {
        $upload_dir = 'uploads/';
        $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user['id'] . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
            // Delete old profile photo if exists
            if ($user['profile_photo'] && file_exists($user['profile_photo'])) {
                unlink($user['profile_photo']);
            }
            
            // Update database
            $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            if ($stmt->execute([$upload_path, $user['id']])) {
                $success = 'Profile photo updated successfully!';
                $user['profile_photo'] = $upload_path;
            } else {
                $error = 'Failed to update profile photo.';
            }
        } else {
            $error = 'Failed to upload profile photo.';
        }
    } else {
        $error = 'Invalid file type. Please upload JPEG, PNG, or GIF images only.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>Profile Management</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="profile-photo-section">
                                    <?php if ($user['profile_photo'] && file_exists($user['profile_photo'])): ?>
                                        <img src="<?php echo $user['profile_photo']; ?>" alt="Profile Photo" class="profile-photo mb-3">
                                    <?php else: ?>
                                        <div class="profile-photo-placeholder mb-3">
                                            <i class="fas fa-user fa-5x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="POST" enctype="multipart/form-data" class="mb-3">
                                        <div class="mb-3">
                                            <input type="file" class="form-control" name="profile_photo" accept="image/*" required>
                                        </div>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">Update Photo</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="assets/js/validation.js"></script>
</body>
</html>
