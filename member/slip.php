<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'รหัสสลิปไม่ถูกต้อง';
    exit;
}

// อนุญาตทั้งสมาชิกเจ้าของสลิปและแอดมิน
if (!current_user()) {
    flash('error', 'กรุณาเข้าสู่ระบบเพื่อดูสลิป');
    redirect('login.php');
}

serve_payment_slip($id);
