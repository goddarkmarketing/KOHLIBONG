<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

try {
    echo json_encode([
        'ok' => true,
        'hotels' => array_map('map_listing_api_row', get_approved_listings('hotel')),
        'restaurants' => array_map('map_listing_api_row', get_approved_listings('restaurant')),
        'tours' => array_map('map_listing_api_row', get_approved_listings('tour')),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'hotels' => [], 'restaurants' => [], 'tours' => []], JSON_UNESCAPED_UNICODE);
}
