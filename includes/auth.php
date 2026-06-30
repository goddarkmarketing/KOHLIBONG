<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user = null;
    if ($user !== null) {
        return $user;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
    if (!$user) {
        unset($_SESSION['user_id']);
    }
    return $user;
}

function is_admin(): bool
{
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function is_member(): bool
{
    $u = current_user();
    return $u && $u['role'] === 'member';
}

function subscription_valid(array $user): bool
{
    if ($user['role'] !== 'member') {
        return false;
    }
    if ($user['status'] !== 'active' || empty($user['subscription_end'])) {
        return false;
    }
    return $user['subscription_end'] >= date('Y-m-d');
}

function attempt_login(string $email, string $password): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([trim(strtolower($email))]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }
    $_SESSION['user_id'] = (int) $user['id'];
    return $user;
}

function logout_user(): void
{
    unset($_SESSION['user_id'], $_SESSION['csrf']);
    session_regenerate_id(true);
}

/** หลังล็อกอินสำเร็จ — ส่งไปหน้าที่เหมาะกับ role */
function login_redirect(array $user): never
{
    if ($user['role'] === 'admin') {
        redirect(ADMIN_BASE . '/index.php');
    }
    if ($user['status'] === 'pending_approval') {
        flash('info', 'บัญชีรอแอดมินตรวจสอบสลิป — เข้าแดชบอร์ดเพื่อดูสถานะ');
        redirect(MEMBER_BASE . '/dashboard.php');
    }
    if ($user['status'] === 'rejected') {
        flash('error', 'บัญชีถูกปฏิเสธ กรุณาติดต่อแอดมิน');
        logout_user();
        redirect(MEMBER_BASE . '/login.php');
    }
    if (!subscription_valid($user)) {
        flash('error', 'สมาชิกหมดอายุแล้ว กรุณาชำระเงินต่ออายุ');
        redirect(MEMBER_BASE . '/renew.php');
    }
    redirect(MEMBER_BASE . '/dashboard.php');
}

/** เข้าได้เฉพาะสมาชิก (role = member) */
function require_member(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'กรุณาเข้าสู่ระบบสมาชิก');
        redirect(MEMBER_BASE . '/login.php');
    }
    if ($user['role'] === 'admin') {
        flash('error', 'บัญชีแอดมินไม่สามารถเข้าพื้นที่สมาชิกได้');
        redirect(ADMIN_BASE . '/index.php');
    }
    return $user;
}

/** @deprecated ใช้ require_member() แทน */
function require_login(): array
{
    return require_member();
}

function require_active_member(): array
{
    $user = require_member();
    if ($user['status'] === 'pending_approval') {
        flash('info', 'บัญชีของคุณรอแอดมินตรวจสอบสลิปชำระเงิน');
        redirect(MEMBER_BASE . '/dashboard.php');
    }
    if ($user['status'] === 'rejected') {
        flash('error', 'บัญชีถูกปฏิเสธ กรุณาติดต่อแอดมิน');
        redirect(MEMBER_BASE . '/login.php');
    }
    if (!subscription_valid($user)) {
        db()->prepare("UPDATE users SET status = 'expired' WHERE id = ? AND role = 'member'")->execute([$user['id']]);
        flash('error', 'สมาชิกหมดอายุแล้ว กรุณาชำระเงินต่ออายุ');
        redirect(MEMBER_BASE . '/renew.php');
    }
    return $user;
}

/** เข้าได้เฉพาะแอดมิน (role = admin) */
function require_admin(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'กรุณาเข้าสู่ระบบ');
        redirect(MEMBER_BASE . '/login.php');
    }
    if ($user['role'] !== 'admin') {
        flash('error', 'ไม่มีสิทธิ์เข้าหน้าแอดมิน');
        redirect(MEMBER_BASE . '/dashboard.php');
    }
    return $user;
}
