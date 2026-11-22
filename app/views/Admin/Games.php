<?php
/** Admin → Games: ব্রেইন গেম চালু / বন্ধ আর আজকের হিসাব */
$ViewData['Title'] = 'Games';
$on = (bool)$ViewBag->On; $st = $ViewBag->St; $top = $ViewBag->Top ?: [];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 110px; min-height: calc(100vh - 80px); }
    .ga { max-width: 900px; }
    .ga h2 { color: #0F172A; margin: 0; font-size: 26px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
    .ga .sub { color: #64748B; margin: 6px 0 18px; font-size: 14.5px; line-height: 1.6; }
    .ga-card { background: #fff; border: 1px solid #E8EEF4; border-radius: 18px; padding: 22px 24px; margin-bottom: 18px; box-shadow: 0 4px 16px rgba(15,23,42,.04); }
    .ga-row { display: flex; align-items: center; justify-content: space-between; gap: 18px; }
    .ga-row b { display: block; font-size: 17px; color: #0F172A; } .ga-row small { color: #64748B; font-size: 13.5px; line-height: 1.6; }
    .ga-state { display: inline-flex; align-items: center; gap: 8px; font-weight: 800; font-size: 13px; padding: 4px 12px; border-radius: 999px; margin-top: 8px; }
    .ga-state.on { background: #DCFCE7; color: #166534; } .ga-state.off { background: #FEE2E2; color: #991B1B; }
    .ga-sw { position: relative; width: 60px; height: 34px; flex-shrink: 0; }
    .ga-sw input { opacity: 0; width: 0; height: 0; position: absolute; }
    .ga-sw span { position: absolute; inset: 0; border-radius: 999px; background: #CBD5E1; cursor: pointer; transition: background .2s; }
    .ga-sw span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 28px; height: 28px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
    .ga-sw input:checked + span { background: #16A34A; } .ga-sw input:checked + span::after { transform: translateX(26px); }
    .ga-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .ga-stats div { background: #F8FAFC; border-radius: 14px; padding: 14px; }
    .ga-stats b { display: block; font-size: 24px; color: #0F172A; } .ga-stats small { color: #64748B; font-size: 12.5px; }
    .ga-top { width: 100%; border-collapse: collapse; font-size: 14px; }
    .ga-top td, .ga-top th { padding: 9px 8px; border-bottom: 1px solid #F1F5F9; text-align: left; }
    .ga-top th { color: #64748B; font-size: 12.5px; font-weight: 700; }
    .ga-links { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
    .ga-links a { padding: 9px 14px; border-radius: 10px; border: 1px solid #DCE5EE; color: #334155; font-weight: 700; text-decoration: none; font-size: 13.5px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; } .ga-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content">
    <div class="ga">
        <?= csrf_field() ?>
        <h2><i class="fas fa-gamepad" style="color:#F97316;"></i> Games</h2>
        <p class="sub">ব্রেইন গেম (Zip, Mini Sudoku, Patches) চালু বা বন্ধ করুন। বন্ধ করলে নেভবার থেকে "গেম" বাটন সরে যাবে, গেম আর লিডারবোর্ড পেইজ খুলবে না, প্রোফাইলের ব্রেইন স্কোরও লুকানো থাকবে। খেলার সব হিসাব জমা থাকবে — আবার চালু করলে সব ফিরে আসবে।</p>

        <div class="ga-card">
            <div class="ga-row">
                <div>
                    <b>গেম পেইজ</b>
                    <small>চালু থাকলে সবাই নেভবারে "গেম" দেখবে আর লগইন করে খেলতে পারবে।</small>
                    <div><span class="ga-state <?= $on ? 'on' : 'off' ?>" id="gaState"><?= $on ? '● চালু আছে' : '● বন্ধ আছে' ?></span></div>
                </div>
                <label class="ga-sw" title="চালু / বন্ধ"><input type="checkbox" id="gaOn" <?= $on ? 'checked' : '' ?> /><span></span></label>
            </div>
        </div>

        <div class="ga-card">
            <div class="ga-stats">
                <div><b><?= (int)$st['today'] ?></b><small>আজ সমাধান হওয়া পাজল</small></div>
                <div><b><?= (int)$st['players'] ?></b><small>আজ খেলেছে (জন)</small></div>
                <div><b><?= (int)$st['all'] ?></b><small>মোট সমাধান</small></div>
                <div><b><?= (int)$st['users'] ?></b><small>মোট প্লেয়ার</small></div>
            </div>
            <div class="ga-links">
                <a href="/Game" target="_blank"><i class="fas fa-up-right-from-square"></i> গেম পেইজ দেখুন</a>
                <a href="/Game/Leaderboard" target="_blank"><i class="fas fa-trophy" style="color:#F59E0B;"></i> লিডারবোর্ড</a>
            </div>
            <?php if (!$on): ?><p class="sub" style="margin:10px 0 0;font-size:13px;"><i class="fas fa-circle-info"></i> বন্ধ থাকলেও অ্যাডমিন হিসেবে আপনি পেইজগুলো দেখতে পারবেন — অন্য কেউ না।</p><?php endif; ?>
        </div>

        <div class="ga-card">
            <b style="font-size:16px;color:#0F172A;">আজকের টপ ১০</b>
            <?php if (!$top): ?>
                <p class="sub" style="margin:10px 0 0;">আজ এখনো কেউ খেলেনি।</p>
            <?php else: ?>
                <table class="ga-top" style="margin-top:10px;">
                    <tr><th>#</th><th>নাম</th><th>রোল</th><th>সমাধান</th><th>পয়েন্ট</th></tr>
                    <?php foreach ($top as $r): ?>
                        <tr><td><?= (int)$r->Rank ?></td><td><a href="/Profile/Show/<?= (int)$r->UserId ?>" target="_blank"><?= e($r->FullName ?: $r->UserName) ?></a></td>
                            <td><?= $r->Role === 'Teacher' ? 'টিচার' : 'শিক্ষার্থী' ?></td><td><?= (int)$r->Solves ?></td><td><b><?= (int)$r->Pts ?></b></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php section_start('Scripts'); ?>
<script>
(function () {
    var sw = document.getElementById('gaOn'), st = document.getElementById('gaState');
    sw.addEventListener('change', function () {
        var on = sw.checked;
        if (!on && !confirm('গেম বন্ধ করবেন? নেভবার থেকে "গেম" সরে যাবে আর কেউ খেলতে পারবে না।')) { sw.checked = true; return; }
        var fd = new FormData(); fd.append('enabled', on ? '1' : '0');
        fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        sw.disabled = true;
        fetch('/Admin/SaveGames', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); }).then(function (d) {
            sw.disabled = false;
            if (!d.success) { alert(d.message || 'সেভ হয়নি'); sw.checked = !on; return; }
            st.className = 'ga-state ' + (d.on ? 'on' : 'off'); st.textContent = d.on ? '● চালু আছে' : '● বন্ধ আছে';
            alert(d.message);
        }).catch(function () { sw.disabled = false; sw.checked = !on; alert('সেভ হয়নি — আবার চেষ্টা করুন।'); });
    });
})();
</script>
<?php section_end(); ?>
