<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../includes/database.php';

$success = '';
$error = '';

// Handle role changes
if (isset($_POST['change_role'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission";
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $new_role = $_POST['role'] ?? '';
        
        if ($user_id && in_array($new_role, ['user', 'admin'])) {
            // Don't allow changing own role from admin to user
            if ($user_id == $_SESSION['user_id'] && $new_role == 'user') {
                $error = "You cannot remove your own admin privileges!";
            } else {
                // Prevent removing the last admin
                if ($new_role == 'user') {
                    $check_stmt = $pdo->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
                    $admin_count = $check_stmt->fetch()['admin_count'];
                    
                    if ($admin_count <= 1) {
                        $error = "Cannot remove the last admin!";
                    } else {
                        try {
                            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                            $stmt->execute([$new_role, $user_id]);
                            $success = "User role updated successfully!";
                        } catch (PDOException $e) {
                            $error = "Failed to update user role";
                            error_log("Role update error: " . $e->getMessage());
                        }
                    }
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                        $stmt->execute([$new_role, $user_id]);
                        $success = "User role updated successfully!";
                    } catch (PDOException $e) {
                        $error = "Failed to update user role";
                        error_log("Role update error: " . $e->getMessage());
                    }
                }
            }
        } else {
            $error = "Invalid user ID or role";
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
$base_path = '../';
include '../includes/navigation.php';
?>
    
    <div class="manage-container">
        <h1>Manage Users</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-success' : 'badge-info'; ?>">
                            <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="role" onchange="this.form.submit()" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <input type="hidden" name="change_role" value="1">
                        </form>
                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <small style="color: #666;">(Current user)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>