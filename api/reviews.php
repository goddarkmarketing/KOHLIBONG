<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

try {
    $out = array_map('map_review_api_row', get_approved_reviews());
    echo json_encode(['ok' => true, 'reviews' => $out], JSON_UNESCAPED_UNICODE);
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'reviews' => []], JSON_UNESCAPED_UNICODE);
}
