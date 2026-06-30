<?php
declare(strict_types=1);

/**
 * สร้าง Full Backup จาก command line
 * C:\xampp\php\php.exe create-backup.php
 */

require_once __DIR__ . '/includes/backup_helpers.php';

$result = backup_create_full();

if ($result['ok']) {
    echo "OK: {$result['file']} ({$result['size_label']})\n";
    echo $result['message'] . "\n";
    echo "Download: " . ADMIN_BASE . "/backup.php?download=" . urlencode($result['file']) . "\n";
    exit(0);
}

echo "ERROR: {$result['message']}\n";
exit(1);
