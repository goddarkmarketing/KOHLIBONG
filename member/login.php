<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

$user = current_user();
if ($user) {
    login_redirect($user);
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = attempt_login($email, $password);
    if (!$user) {
        $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
    } else {
        login_redirect($user);
    }
}

member_header('เข้าสู่ระบบ');
?>
<section class="member-card">
  <h1>เข้าสู่ระบบ</h1>
  <p class="member-lead">สมาชิกและผู้ดูแลระบบใช้หน้าเข้าสู่ระบบเดียวกัน — ระบบจะพาไปหน้าที่เหมาะกับบัญชีของคุณ</p>
  <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" class="member-form" id="loginForm">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
    <label>อีเมล<input type="email" name="email" id="loginEmail" required autocomplete="email" /></label>
    <label>รหัสผ่าน<input type="password" name="password" id="loginPassword" required autocomplete="current-password" /></label>
    <button type="submit" class="btn btn--login btn--block">เข้าสู่ระบบ</button>
  </form>

  <div class="demo-accounts" aria-label="บัญชีทดสอบสำหรับตรวจงาน">
    <p class="demo-accounts__title">บัญชีทดสอบ (สำหรับตรวจงาน)</p>

    <div class="demo-account">
      <div class="demo-account__body">
        <span class="demo-account__role">แอดมิน</span>
        <span class="demo-account__line">
          <span class="demo-account__label">อีเมล</span>
          <code>admin@kohlibong.com</code>
        </span>
        <span class="demo-account__line">
          <span class="demo-account__label">รหัสผ่าน</span>
          <code>admin123</code>
        </span>
      </div>
      <button type="button" class="demo-account__btn" data-demo-fill data-email="admin@kohlibong.com" data-password="admin123" title="กรอกอีเมลและรหัสผ่าน">
        <i data-lucide="copy" class="icon"></i><span>คัดลอก</span>
      </button>
    </div>

    <div class="demo-account">
      <div class="demo-account__body">
        <span class="demo-account__role">สมาชิก</span>
        <span class="demo-account__line">
          <span class="demo-account__label">อีเมล</span>
          <code>member@kohlibong.com</code>
        </span>
        <span class="demo-account__line">
          <span class="demo-account__label">รหัสผ่าน</span>
          <code>member123</code>
        </span>
      </div>
      <button type="button" class="demo-account__btn" data-demo-fill data-email="member@kohlibong.com" data-password="member123" title="กรอกอีเมลและรหัสผ่าน">
        <i data-lucide="copy" class="icon"></i><span>คัดลอก</span>
      </button>
    </div>
  </div>

  <p class="member-foot">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a> · <a href="renew.php">ต่ออายุสมาชิก</a></p>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const emailInput = document.getElementById('loginEmail');
  const passwordInput = document.getElementById('loginPassword');

  document.querySelectorAll('[data-demo-fill]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const email = btn.dataset.email || '';
      const password = btn.dataset.password || '';
      if (emailInput) emailInput.value = email;
      if (passwordInput) passwordInput.value = password;
      emailInput?.focus();

      try {
        await navigator.clipboard.writeText(`อีเมล: ${email}\nรหัสผ่าน: ${password}`);
      } catch (_) {}

      const label = btn.querySelector('span');
      if (!label) return;
      const original = label.textContent;
      label.textContent = 'กรอกแล้ว';
      btn.classList.add('is-done');
      setTimeout(() => {
        label.textContent = original;
        btn.classList.remove('is-done');
      }, 1600);
    });
  });

  window.lucide?.createIcons?.();
});
</script>
<?php member_footer(); ?>
