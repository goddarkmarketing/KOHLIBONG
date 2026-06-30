<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

require_member();

member_header('ช่วยเหลือ', 'help', 'คำถามที่พบบ่อยและช่องทางติดต่อ');
?>
<div class="mapp-grid mapp-grid--help">
  <section class="mapp-panel">
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title">วิธีใช้งาน</h2>
    </div>
    <div class="help-faq">
      <details class="help-faq__item" open>
        <summary>สมัครสมาชิกอย่างไร?</summary>
        <p>กดสมัครสมาชิก → เลือกประเภท (รีวิว หรือ ผู้ประกอบการ) → กรอกข้อมูล → โอนเงิน <?= number_format(MEMBERSHIP_FEE) ?> บาท → อัปโหลดสลิป → รอแอดมินอนุมัติ</p>
      </details>
      <details class="help-faq__item">
        <summary>เขียนรีวิว / โพสต์ธุรกิจ</summary>
        <p>หลังบัญชี active แล้ว ใช้เมนู «เขียนรีวิว» หรือ «โพสต์ข้อมูล» ทุกโพสต์รอแอดมินอนุมัติก่อนแสดงบนเว็บ</p>
      </details>
      <details class="help-faq__item">
        <summary>ต่ออายุสมาชิก</summary>
        <p>เมื่อใกล้หมดอายุหรือหมดแล้ว ไปหน้า «ต่ออายุสมาชิก» อัปโหลดสลิปใหม่ แอดมินอนุมัติแล้วได้อีก <?= SUBSCRIPTION_DAYS ?> วัน</p>
      </details>
      <details class="help-faq__item">
        <summary>โพสต์ถูกปฏิเสธทำอย่างไร?</summary>
        <p>ดูหมายเหตุจากแอดมินใน «โพสต์ของฉัน» → กดแก้ไข → ส่งใหม่ ระบบจะรอตรวจอีกครั้ง</p>
      </details>
    </div>
  </section>

  <section class="mapp-panel">
    <div class="mapp-panel__head">
      <h2 class="mapp-panel__title">ติดต่อแอดมิน</h2>
      <p class="mapp-panel__desc">สอบถามเรื่องสลิป การอนุมัติ หรือปัญหาการใช้งาน</p>
    </div>
    <dl class="mapp-dl">
      <div><dt>LINE</dt><dd><?= e(ADMIN_CONTACT_LINE) ?></dd></div>
      <div><dt>อีเมล</dt><dd><a href="mailto:<?= e(ADMIN_CONTACT_EMAIL) ?>"><?= e(ADMIN_CONTACT_EMAIL) ?></a></dd></div>
      <div><dt>ค่าสมาชิก</dt><dd><?= number_format(MEMBERSHIP_FEE) ?> บาท / <?= SUBSCRIPTION_DAYS ?> วัน</dd></div>
      <div><dt>โอนเงิน</dt><dd><?= e(BANK_INFO) ?></dd></div>
    </dl>
    <div class="mapp-actions" style="margin-bottom:0">
      <a href="renew.php" class="btn btn--primary">หน้าต่ออายุ / อัปสลิป</a>
      <a href="../index.html" class="btn btn--ghost-dark">หน้าเว็บหลัก</a>
    </div>
  </section>
</div>
<?php member_footer(); ?>
