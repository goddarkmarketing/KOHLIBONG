<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

echo json_encode([
    'ok' => true,
    'settings' => site_settings_public(),
], JSON_UNESCAPED_UNICODE);
