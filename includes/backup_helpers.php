<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

define('BACKUP_DIR', BASE_PATH . '/backups');

/** @return list<string> */
function backup_exclude_relative_paths(): array
{
    return [
        'backups',
        '.git',
        'node_modules',
        '.cursor',
        'agent-transcripts',
    ];
}

function backup_ensure_dir(): void
{
    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }
    $htaccess = BACKUP_DIR . '/.htaccess';
    if (!is_file($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n");
    }
    $gitkeep = BACKUP_DIR . '/.gitkeep';
    if (!is_file($gitkeep)) {
        file_put_contents($gitkeep, '');
    }
}

function backup_timestamp_label(): string
{
    return date('d-m-Y_H-i-s');
}

function backup_build_filename(): string
{
    return 'kohlibong_full_' . backup_timestamp_label() . '.zip';
}

function backup_format_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }

    return $bytes . ' B';
}

function backup_format_datetime(string $path): string
{
    $ts = filemtime($path) ?: time();

    return date('j/n/Y H:i:s', $ts);
}

/** @return list<array{file:string,name:string,size:int,size_label:string,created:string}> */
function backup_list_archives(): array
{
    backup_ensure_dir();
    $files = glob(BACKUP_DIR . '/*.zip') ?: [];
    rsort($files);
    $out = [];
    foreach ($files as $path) {
        $name = basename($path);
        $size = (int) filesize($path);
        $out[] = [
            'file' => $name,
            'name' => $name,
            'size' => $size,
            'size_label' => backup_format_size($size),
            'created' => backup_format_datetime($path),
        ];
    }

    return $out;
}

function backup_resolve_archive(string $filename): ?string
{
    $filename = basename($filename);
    if (!preg_match('/^kohlibong_full_[\d\-_]+\.zip$/', $filename)) {
        return null;
    }
    $path = BACKUP_DIR . '/' . $filename;
    if (!is_file($path)) {
        return null;
    }

    return $path;
}

function backup_should_skip(string $relative): bool
{
    $relative = str_replace('\\', '/', $relative);
    foreach (backup_exclude_relative_paths() as $ex) {
        $ex = str_replace('\\', '/', $ex);
        if ($relative === $ex || str_starts_with($relative, $ex . '/')) {
            return true;
        }
    }
    if (str_ends_with(strtolower($relative), '.zip') && str_starts_with($relative, 'backups/')) {
        return true;
    }

    return false;
}

/** @return list<string> */
function backup_collect_files(string $base, string $prefix = ''): array
{
    $items = [];
    $entries = scandir($base);
    if ($entries === false) {
        return $items;
    }
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $base . DIRECTORY_SEPARATOR . $entry;
        $rel = $prefix === '' ? $entry : $prefix . '/' . $entry;
        if (backup_should_skip($rel)) {
            continue;
        }
        if (is_dir($full)) {
            $items = array_merge($items, backup_collect_files($full, $rel));
        } elseif (is_file($full)) {
            $items[] = $rel;
        }
    }

    return $items;
}

function backup_try_mysqldump(string $targetSql): bool
{
    $bins = [
        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        'mysqldump',
    ];

    foreach ($bins as $bin) {
        if ($bin !== 'mysqldump' && !is_file($bin)) {
            continue;
        }

        $args = [
            $bin,
            '--host=' . DB_HOST,
            '--user=' . DB_USER,
            '--single-transaction',
            '--routines',
            '--triggers',
            DB_NAME,
        ];
        if (DB_PASS !== '') {
            array_splice($args, 3, 0, ['--password=' . DB_PASS]);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $targetSql, 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($args, $descriptors, $pipes, null, null, ['bypass_shell' => true]);
        if (!is_resource($proc)) {
            continue;
        }
        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $code = proc_close($proc);

        if ($code === 0 && is_file($targetSql) && filesize($targetSql) > 50) {
            return true;
        }
    }

    return backup_export_database_pdo($targetSql);
}

function backup_export_database_pdo(string $targetSql): bool
{
    try {
        $pdo = db();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if (!$tables) {
            return false;
        }

        $sql = "-- Koh Libong DB export\n-- " . date('c') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach ($tables as $table) {
            $create = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_NUM);
            if (!$create) {
                continue;
            }
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$create[1]};\n\n";
            $rows = $pdo->query('SELECT * FROM `' . str_replace('`', '``', $table) . '`')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $cols = array_map(static fn ($c) => '`' . str_replace('`', '``', $c) . '`', array_keys($row));
                $vals = array_map(static function ($v) use ($pdo) {
                    if ($v === null) {
                        return 'NULL';
                    }

                    return $pdo->quote((string) $v);
                }, array_values($row));
                $sql .= 'INSERT INTO `' . $table . '` (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return file_put_contents($targetSql, $sql) !== false && filesize($targetSql) > 50;
    } catch (Throwable) {
        return false;
    }
}

/** @return array{ok:bool, file?:string, message:string, size?:int, size_label?:string} */
function backup_create_full(): array
{
    if (!class_exists(ZipArchive::class)) {
        return ['ok' => false, 'message' => 'เซิร์ฟเวอร์ไม่รองรับ ZipArchive — เปิด extension zip ใน PHP'];
    }

    backup_ensure_dir();

    $filename = backup_build_filename();
    $zipPath = BACKUP_DIR . '/' . $filename;
    if (is_file($zipPath)) {
        return ['ok' => false, 'message' => 'มีไฟล์สำรองชื่อเดียวกันอยู่แล้ว กรุณารอสักครู่แล้วลองใหม่'];
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return ['ok' => false, 'message' => 'ไม่สามารถสร้างไฟล์ ZIP ได้'];
    }

    $files = backup_collect_files(BASE_PATH);
    $added = 0;
    foreach ($files as $rel) {
        $abs = BASE_PATH . '/' . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (is_file($abs)) {
            $zip->addFile($abs, 'site/' . str_replace('\\', '/', $rel));
            $added++;
        }
    }

    $sqlTmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'kohlibong_' . uniqid('', true) . '.sql';
    $dbDumped = backup_try_mysqldump($sqlTmp);
    if ($dbDumped) {
        $zip->addFile($sqlTmp, 'database/kohlibong_' . date('d-m-Y_H-i-s') . '.sql');
    } else {
        $zip->addFromString(
            'database/README.txt',
            "ไม่สามารถ export MySQL อัตโนมัติได้\n"
            . "กรุณา backup ฐานข้อมูล '" . DB_NAME . "' แยกผ่าน phpMyAdmin หรือ mysqldump\n"
            . 'สร้างเมื่อ: ' . date('c') . "\n"
        );
    }

    $manifest = [
        'site' => SITE_NAME,
        'created_at' => date('c'),
        'created_label' => date('j/n/Y H:i:s'),
        'files_count' => $added,
        'database_dump' => $dbDumped,
        'php_version' => PHP_VERSION,
    ];
    $zip->addFromString('BACKUP_INFO.json', json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $zip->close();

    if (is_file($sqlTmp)) {
        @unlink($sqlTmp);
    }

    if (!is_file($zipPath) || filesize($zipPath) < 100) {
        @unlink($zipPath);

        return ['ok' => false, 'message' => 'สร้างไฟล์สำรองไม่สำเร็จ'];
    }

    $size = (int) filesize($zipPath);

    return [
        'ok' => true,
        'file' => $filename,
        'message' => 'สร้างไฟล์สำรองเรียบร้อย' . ($dbDumped ? ' (รวมฐานข้อมูล MySQL)' : ' (ไม่มี dump DB — ดู database/README.txt)'),
        'size' => $size,
        'size_label' => backup_format_size($size),
    ];
}

function backup_send_download(string $filename): never
{
    $path = backup_resolve_archive($filename);
    if (!$path) {
        http_response_code(404);
        exit('ไม่พบไฟล์สำรอง');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    header('Content-Length: ' . (string) filesize($path));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    readfile($path);
    exit;
}
