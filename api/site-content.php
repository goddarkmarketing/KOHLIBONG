<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/site_content_helpers.php';

try {
    site_content_ensure_table();
    $payload = ['ok' => true];
    foreach (site_content_export_section_keys() as $section) {
        $rows = site_content_list($section, true);
        $payload[$section] = array_map('map_site_content_api_row', $rows);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
}
