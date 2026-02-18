<?php
session_start();

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Generate CSRF token for backup operation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../includes/database.php';

$message = '';
$error = '';

// Only allow POST requests for backup
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $backup_dir = __DIR__ . '/backups/';
        
        // Create backups directory if it doesn't exist
        if (!file_exists($backup_dir)) {
            if (!mkdir($backup_dir, 0755, true)) {
                $error = 'Failed to create backups directory';
            }
        }
        
        if (empty($error)) {
            $backup_file = $backup_dir . 'gwps_backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            try {
                // Get all tables
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                
                if (empty($tables)) {
                    $error = 'No tables found in database';
                } else {
                    $sql = "-- GWPS Database Backup\n";
                    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
                    $sql .= "-- Tables: " . implode(', ', $tables) . "\n\n";
                    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
                    
                    foreach ($tables as $table) {
                        // Get create table syntax
                        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
                        $sql .= "\n-- Table structure for table `$table`\n";
                        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                        $sql .= $create['Create Table'] . ";\n\n";
                        
                        // Get table data
                        $rows = $pdo->query("SELECT * FROM `$table`");
                        $row_count = $rows->rowCount();
                        
                        if ($row_count > 0) {
                            $sql .= "-- Dumping data for table `$table` - $row_count rows\n";
                            
                            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                                $columns = array_keys($row);
                                $values = array_values($row);
                                
                                // Quote values properly
                                $quoted_values = array_map(function($value) use ($pdo) {
                                    return $value === null ? 'NULL' : $pdo->quote($value);
                                }, $values);
                                
                                $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . 
                                        implode(', ', $quoted_values) . ");\n";
                            }
                            $sql .= "\n";
                        }
                    }
                    
                    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
                    
                    if (file_put_contents($backup_file, $sql)) {
                        $message = "Backup created successfully!";
                        $message .= "<br><strong>File:</strong> " . basename($backup_file);
                        $message .= "<br><strong>Size:</strong> " . round(filesize($backup_file) / 1024, 2) . " KB";
                        $message .= "<br><strong>Tables:</strong> " . count($tables);
                    } else {
                        $error = "Failed to write backup file";
                    }
                }
                
            } catch (Exception $e) {
                $error = "Backup failed: " . $e->getMessage();
                error_log("Backup error: " . $e->getMessage());
            }
        }
    }
}

// Regenerate CSRF token for next backup
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Backup - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
$base_path = '../';
include '../includes/navigation.php';
?>
    
    <div class="backup-container">
        <div class="backup-icon">💾</div>
        <h1>Database Backup</h1>
        <p>Create a secure backup of your entire database</p>
        
        <?php if ($message): ?>
            <div class="success" style="padding: 20px; margin: 20px 0;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error" style="padding: 20px; margin: 20px 0;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <div class="warning">
            <strong>⚠️ Important:</strong>
            <ul style="margin-top: 10px; margin-left: 20px;">
                <li>Backup files are saved in the <code>admin/backups/</code> directory</li>
                <li>Each backup is a complete SQL dump of your database</li>
                <li>Download and store backups in a safe location</li>
                <li>Old backups are not automatically deleted</li>
            </ul>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="btn-backup" onclick="return confirm('Start database backup? This may take a few seconds.')">
                🔵 Start New Backup
            </button>
        </form>
        
        <?php
        // List existing backups
        $backup_dir = __DIR__ . '/backups/';
        if (file_exists($backup_dir)) {
            $backups = glob($backup_dir . '*.sql');
            if (!empty($backups)) {
                echo '<div class="backup-info">';
                echo '<h3>📁 Recent Backups</h3>';
                echo '<ul style="list-style: none; padding: 0; margin-top: 15px;">';
                
                // Sort by newest first
                usort($backups, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                $count = 0;
                foreach ($backups as $backup) {
                    if ($count++ >= 5) break; // Show only 5 most recent
                    $filename = basename($backup);
                    $filesize = round(filesize($backup) / 1024, 2);
                    $date = date('M d, Y H:i', filemtime($backup));
                    echo "<li style='padding: 8px 0; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;'>";
                    echo "<span>📄 $filename</span>";
                    echo "<span style='color: #666;'>$filesize KB • $date</span>";
                    echo "</li>";
                }
                echo '</ul>';
                echo '</div>';
            }
        }
        ?>
        
        <p style="margin-top: 30px;">
            <a href="dashboard.php" style="color: #3498db;">← Back to Dashboard</a>
        </p>
    </div>
</body>
</html>