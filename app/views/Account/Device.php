<?php
/** এক ডিভাইসে লগইন — অন্য ডিভাইসে অ্যাকাউন্ট চালু থাকলে এই পেজ: অ্যাডমিনের কাছে অনুরোধ / অপেক্ষা / বাতিল */
$ViewData['Title'] = 'নতুন ডিভাইস';
$ViewData['Robots'] = 'noindex, nofollow';
$st = (string)$ViewBag->State;
?>
<?php partial('Account/_AuthStyles'); ?>
<style>
    .dv-devs { display: grid; gap: 10px; margin: 4px 0 14px; }
    .dv-d { display: flex; align-items: center; gap: 12px; border: 1px solid #E2E8F0; border-radius: 12px; padding: 11px 14px; font-size: 14px; }
    .dv-d i { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .dv-d.old i { background: #FEF3C7; color: #B45309; } .dv-d.new i { background: #E0F7FE; color: #0369A1; }
    .dv-d small { display: block; color: #64748B; font-size: 12px; } .dv-d b { color: #0F172A; }
    .dv-note { width: 100%; min-height: 80px; border: 1.5px solid #DCECF5; border-radius: 12px; padding: 10px 12px; font-size: 14px; font-family: inherit; background: #F5FBFF; resize: vertical; outline: none; }
    .dv-note:focus { border-color: #02B8F5; background: #fff; }
    .dv-wait { text-align: center; padding: 6px 0 2px; color: #475569; font-size: 14px; line-height: 1.7; }
    .dv-wait i { font-size: 30px; color: #F59E0B; display: block; margin-bottom: 8px; }
</style>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <div class="auth-ico<?= $st === 'rejected' ? ' bad' : '' ?>"><i class="fas fa-<?= $st === 'pending' ? 'hourglass-half' : ($st === 'rejected' ? 'ban' : 'mobile-screen') ?>"></i></div>
            <h4><?= $st === 'pending' ? 'অনুরোধ অপেক্ষায় আছে' : ($st === 'rejected' ? 'অনুরোধ বাতিল হয়েছে' : 'অ্যাকাউন্টটি অন্য ডিভাইসে চালু আছে') ?></h4>
            <p>হ্যালো <b><?= e($ViewBag->Name) ?></b> — নিরাপত্তার জন্য একটা অ্যাকাউন্ট একবারে শুধু একটা ডিভাইসে চালু থাকে।</p>
        </div>
        <div class="auth-body">
            <?php if ($TempData['Success'] !== null): ?><div class="auth-alert ok"><i class="fas fa-paper-plane"></i><div><?= e($TempData['Success']) ?></div></div><?php endif; ?>
            <div class="dv-devs">
                <?php if ($ViewBag->Other !== ''): ?>
                    <div class="dv-d old"><i class="fas fa-laptop"></i><div><small>এখন চালু আছে</small><b><?= e($ViewBag->Other) ?></b><small>শেষ ব্যবহার: <?= e(time_ago($ViewBag->OtherSeen)) ?></small></div></div>
                <?php endif; ?>
                <div class="dv-d new"><i class="fas fa-mobile-screen-button"></i><div><small>এই ডিভাইস</small><b><?= e($ViewBag->This) ?></b></div></div>
            </div>

            <?php if ($st === 'pending'): ?>
                <div class="dv-wait"><i class="fas fa-clock"></i>অ্যাডমিন আপনার অনুরোধ দেখছেন। Accept হলে এই পেজ রিফ্রেশ করুন বা আবার লগইন করুন — তখন আগের ডিভাইস থেকে নিজে থেকেই লগআউট হয়ে যাবে।</div>
                <a class="auth-btn" href="/Account/Device" style="text-decoration:none;"><i class="fas fa-rotate"></i> আবার দেখুন</a>
            <?php else: ?>
                <?php if ($st === 'rejected'): ?><div class="auth-alert err"><i class="fas fa-circle-xmark"></i><div>অ্যাডমিন এই ডিভাইসের অনুরোধ বাতিল করেছেন। দরকার হলে কারণ লিখে আবার অনুরোধ পাঠান বা <a href="/Helpdesk">Helpdesk</a> এ যোগাযোগ করুন।</div></div><?php endif; ?>
                <form method="post" action="/Account/Device">
                    <?= csrf_field() ?>
                    <label class="auth-label" for="dvNote">কেন নতুন ডিভাইস? <span style="font-weight:500;color:#94A3B8;">(ঐচ্ছিক)</span></label>
                    <textarea class="dv-note" id="dvNote" name="note" maxlength="300" placeholder="যেমন: আগের মোবাইল নষ্ট হয়ে গেছে, নতুন ফোন কিনেছি"></textarea>
                    <button type="submit" class="auth-btn"><i class="fas fa-paper-plane"></i> অ্যাডমিনের কাছে অনুরোধ পাঠান</button>
                </form>
            <?php endif; ?>
            <div class="auth-foot"><a href="/Account/Login"><i class="fas fa-arrow-left"></i> লগইনে ফিরে যান</a></div>
        </div>
    </div>
</div>
