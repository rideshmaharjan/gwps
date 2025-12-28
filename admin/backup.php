<?php
// Backup database
$backup_file = 'backups/gwps_backup_' . date('Y-m-d') . '.sql';
exec("mysqldump -u root -p gwps > $backup_file");
echo "Backup created: $backup_file";
?>