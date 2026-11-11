<?php
/** Admin → Courses: সব টিচারের কোর্স (লুকানো / দেখানো), কোর্স বিক্রির টাকার ভাগ, টিচারকে পাওনা পাঠানো */
$isLv = ($ViewBag->Kind ?? 'course') === 'live'; $kl = $isLv ? 'লাইভ ক্লাস' : 'কোর্স';
$ViewData['Title'] = $isLv ? 'Live Classes' : 'Courses';
$list = is_array($Model) ? $Model : []; $ledger = $ViewBag->Ledger ?? []; $pct = (int)$ViewBag->Pct; $pend = (int)$ViewBag->PendingPay;
$gross = 0; $adm = 0; $tch = 0; $due = 0;
foreach ($ledger as $r) { $gross += (int)$r->Gross; $adm += (int)$r->AShare; $tch += (int)$r->TShare; $due += (int)$r->Due; }
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 90px; min-height: calc(100vh - 80px); }
    .ac h2 { color: #08224A; margin: 0; font-size: 24px; }
    .ac .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 900px; line-height: 1.6; }
    .ac-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .ac-st { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); }
    .ac-st small { display: block; color: #64748B; font-weight: 700; font-size: 12px; } .ac-st b { display: block; font-size: 22px; color: #0F172A; margin-top: 3px; }
    .ac-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .ac-card h3 { margin: 0 0 14px; font-size: 17px; color: #0F172A; font-weight: 800; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ac-tw { overflow-x: auto; }
    .ac-t { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 760px; }
    .ac-t th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 8px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .ac-t td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
    .ac-t td a { color: #0F172A; font-weight: 700; text-decoration: none; } .ac-t td a:hover { text-decoration: underline; }
    .ac-th { width: 96px; border-radius: 9px; overflow: hidden; }
    .ac-th .cx-thumb { aspect-ratio: 16/9; background: linear-gradient(135deg, #0B3B6B, #0EA5E9); color: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .ac-th .cx-thumb img { width: 100%; height: 100%; object-fit: cover; } .ac-th .cx-thumb .ini { font-size: 11px; font-weight: 800; padding: 0 4px; text-align: center; }
    .ac-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .ac-pill.ok { background: #DCFCE7; color: #166534; } .ac-pill.w { background: #FEF3C7; color: #92400E; } .ac-pill.no { background: #FEE2E2; color: #991B1B; }
    .ac-btn { height: 34px; padding: 0 12px; border-radius: 9px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; text-decoration: none; }
    .ac-btn.l { background: #F1F5F9; color: #334155; } .ac-btn.r { background: #FEE2E2; color: #B91C1C; } .ac-btn.g { background: #15803D; }
    .ac-btn:hover { filter: brightness(1.05); color: inherit; }
    .ac-pay { display: flex; gap: 6px; flex-wrap: wrap; }
    .ac-pay input { height: 34px; border: 1.5px solid #DCECF5; border-radius: 9px; padding: 0 10px; font-size: 13px; font-family: inherit; min-width: 0; outline: none; }
    .ac-pay input[name=amount] { width: 92px; } .ac-pay input[name=method] { width: 110px; } .ac-pay input[name=note] { width: 150px; }
    .ac-pct { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; font-size: 14px; color: #334155; }
    .ac-pct input { width: 80px; height: 38px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 10px; font-size: 15px; font-family: inherit; }
    .ac-alert { display: flex; align-items: center; gap: 12px; background: #FFFBEB; border: 1.5px solid #FDE68A; border-radius: 14px; padding: 12px 16px; margin-bottom: 16px; text-decoration: none; color: #92400E; font-weight: 700; }
    .ac-empty { text-align: center; color: #94A3B8; padding: 30px 10px; }
    .ac-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 80px; } .ac-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content ac">
    <?= csrf_field() ?>
    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
        <div><h2><?= $isLv ? '<i class="fas fa-tower-broadcast" style="color:#DC2626;"></i> Live Classes' : '<i class="fas fa-graduation-cap" style="color:#0EA5E9;"></i> Courses' ?></h2>
            <p class="sub">টিচাররা নিজের কোর্স বানিয়ে সাথে সাথে প্রকাশ করতে পারেন — অনুপযুক্ত মনে হলে <b>লুকান</b> (কেনা শিক্ষার্থীরা তবুও ক্লাস করতে পারবে)। <?= $kl ?>ের পেমেন্ট <a href="<?= $isLv ? '/Admin/LivePayments' : '/Admin/CoursePayments' ?>"><b><?= $isLv ? 'Live Class Payment Requests' : 'Course Payment Requests' ?></b></a> এ Approve করলে শিক্ষার্থী ভর্তি হয়, আর টাকা <?= $pct ?>% অ্যাডমিন / <?= 100 - $pct ?>% টিচার ভাগ হয়।</p></div>
        <a class="ac-btn g" href="/Course/Edit?kind=<?= $isLv ? 'live' : 'course' ?>" style="height:40px;"><i class="fas fa-plus"></i> অ্যাডমিনের <?= $kl ?> বানান</a>
    </div>
    <?php if ($pend > 0): ?><a class="ac-alert" href="<?= $isLv ? '/Admin/LivePayments' : '/Admin/CoursePayments' ?>"><i class="fas fa-clock"></i> <?= $pend ?>টা <?= $kl ?> পেমেন্ট যাচাইয়ের অপেক্ষায় — দেখুন →</a><?php endif; ?>

    <div class="ac-stats">
        <div class="ac-st" style="--c:#0EA5E9;"><small>মোট <?= $kl ?> বিক্রি</small><b>৳<?= number_format($gross) ?></b></div>
        <div class="ac-st" style="--c:#7C3AED;"><small>অ্যাডমিনের ভাগ (আপনার আয়)</small><b>৳<?= number_format($adm) ?></b></div>
        <div class="ac-st" style="--c:#16A34A;"><small>টিচারদের ভাগ</small><b>৳<?= number_format($tch) ?></b></div>
        <div class="ac-st" style="--c:#F59E0B;"><small>টিচারদের পাওনা (বাকি)</small><b>৳<?= number_format($due) ?></b></div>
    </div>

    <div class="ac-card">
        <h3><i class="fas fa-hand-holding-dollar" style="color:#16A34A;"></i> টিচারদের হিসাব ও টাকা পাঠানো — শুধু <?= $kl ?></h3>
        <?php if (!$ledger): ?><div class="ac-empty">এখনো কোনো <?= $kl ?> বিক্রি হয়নি।</div><?php else: ?>
        <div class="ac-tw"><table class="ac-t">
            <thead><tr><th>টিচার</th><th>বিক্রি</th><th>মোট</th><th>অ্যাডমিন</th><th>টিচার</th><th>পাঠানো</th><th>বাকি</th><th>টাকা পাঠান</th></tr></thead>
            <tbody>
            <?php foreach ($ledger as $r): ?>
                <tr>
                    <td><a href="/Admin/UserDetails/<?= (int)$r->TeacherId ?>"><?= e($r->FullName ?: $r->UserName) ?></a><?= $r->Role === 'Admin' ? ' <span class="ac-pill w">Admin</span>' : '' ?><br><small style="color:#64748B;"><?= e($r->MobileNumber ?? '') ?></small></td>
                    <td><?= (int)$r->Sales ?>টা</td>
                    <td>৳<?= number_format((int)$r->Gross) ?></td>
                    <td>৳<?= number_format((int)$r->AShare) ?></td>
                    <td>৳<?= number_format((int)$r->TShare) ?></td>
                    <td>৳<?= number_format((int)$r->Paid) ?></td>
                    <td><b style="color:<?= $r->Due > 0 ? '#B45309' : '#15803D' ?>;">৳<?= number_format((int)$r->Due) ?></b></td>
                    <td><?php if ($r->Due > 0): ?>
                        <form class="ac-pay" data-pay="<?= (int)$r->TeacherId ?>">
                            <input type="number" name="amount" min="1" max="<?= (int)$r->Due ?>" value="<?= (int)$r->Due ?>" />
                            <input type="text" name="method" maxlength="40" placeholder="bKash / Nagad" />
                            <input type="text" name="note" maxlength="300" placeholder="TxnID / নোট" />
                            <button class="ac-btn g" type="submit"><i class="fas fa-check"></i> পাঠিয়েছি</button>
                        </form>
                    <?php else: ?><span class="ac-pill ok">পরিশোধিত</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>

    <div class="ac-card">
        <h3><i class="fas fa-layer-group" style="color:#0EA5E9;"></i> সব <?= $kl ?> <span class="ac-pill ok" style="background:#F1F5F9;color:#475569;"><?= count($list) ?>টা</span></h3>
        <?php if (!$list): ?><div class="ac-empty">এখনো কেউ <?= $kl ?> বানাননি।</div><?php else: ?>
        <div class="ac-tw"><table class="ac-t">
            <thead><tr><th></th><th>কোর্স</th><th>টিচার</th><th>দাম</th><th>ক্লাস</th><th>শিক্ষার্থী</th><th>অবস্থা</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($list as $c): ?>
                <tr>
                    <td><div class="ac-th"><?= course_thumb_html($c) ?></div></td>
                    <td><a href="/Course/Details/<?= (int)$c->Id ?>" target="_blank" rel="noopener"><?= e($c->Title) ?></a><br><small style="color:#94A3B8;"><?= e($c->Category ?: '') ?></small></td>
                    <td><a href="/Admin/UserDetails/<?= (int)$c->TeacherId ?>"><?= e($c->TeacherName ?: ($c->TeacherUser ?? '')) ?></a></td>
                    <td>৳<?= number_format($c->Price) ?></td>
                    <td><?= (int)($c->LessonCount ?? 0) ?></td>
                    <td><?= (int)($c->StudentCount ?? 0) ?></td>
                    <td><?= $c->AdminHidden ? '<span class="ac-pill no">লুকানো</span>' : ($c->Status === 'published' ? '<span class="ac-pill ok">প্রকাশিত</span>' : '<span class="ac-pill w">Draft</span>') ?></td>
                    <td style="white-space:nowrap;">
                        <a class="ac-btn l" href="/Course/Build/<?= (int)$c->Id ?>" title="ক্লাস ও কাজ"><i class="fas fa-layer-group"></i></a>
                        <a class="ac-btn l" href="/Course/Students/<?= (int)$c->Id ?>" title="শিক্ষার্থী"><i class="fas fa-users"></i></a>
                        <button class="ac-btn <?= $c->AdminHidden ? 'g' : 'r' ?>" type="button" data-hide="<?= (int)$c->Id ?>" data-h="<?= $c->AdminHidden ? 0 : 1 ?>"><i class="fas <?= $c->AdminHidden ? 'fa-eye' : 'fa-eye-slash' ?>"></i> <?= $c->AdminHidden ? 'দেখান' : 'লুকান' ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>

    <?php if (admin_is_super()): ?>
    <div class="ac-card">
        <h3><i class="fas fa-percent" style="color:#7C3AED;"></i> টাকার ভাগ</h3>
        <form class="ac-pct" id="acPct">কোর্স ও লাইভ ক্লাসের দামের <input type="number" name="pct" min="0" max="100" value="<?= $pct ?>" />% অ্যাডমিন রাখবে (বাকিটা টিচার)। <button class="ac-btn" type="submit"><i class="fas fa-floppy-disk"></i> সেভ</button>
            <small style="color:#94A3B8;width:100%;">বদলালে শুধু এরপরের বিক্রিতে লাগবে — আগের বিক্রির হিসাব যেমন আছে তেমনই থাকবে। অ্যাডমিনের নিজের কোর্সের পুরো টাকা অ্যাডমিনের।</small></form>
    </div>
    <?php $fc = course_publish_fee('course'); $fl = course_publish_fee('live'); ?>
    <div class="ac-card" id="pubFee">
        <h3><i class="fas fa-rocket" style="color:#DC2626;"></i> প্রকাশ ফি — টিচার কোর্স / লাইভ ক্লাস ছাড়তে কত দেবে</h3>
        <p style="font-size:13px;color:#64748B;margin:0 0 12px;">টিচার নিজের কোর্স বা লাইভ ক্লাস প্রকাশ করার আগে এই ফি একবার দেবে (প্রতিটা কোর্স / লাইভ ক্লাসের জন্য আলাদা)। <b>ঘর ফাঁকা রাখলে টিচাররা প্রকাশ করতে পারবে না</b> (বানিয়ে Draft এ রাখতে পারবে)। <b>0</b> দিলে ফ্রিতে প্রকাশ।</p>
        <form class="ac-pct" id="acFee">
            কোর্স প্রকাশ ফি ৳<input type="number" name="feeCourse" min="0" value="<?= $fc === null ? '' : $fc ?>" placeholder="বন্ধ" style="width:110px;" />
            &nbsp; লাইভ ক্লাস প্রকাশ ফি ৳<input type="number" name="feeLive" min="0" value="<?= $fl === null ? '' : $fl ?>" placeholder="বন্ধ" style="width:110px;" />
            <button class="ac-btn" type="submit"><i class="fas fa-floppy-disk"></i> সেভ</button>
            <small style="width:100%;font-weight:700;color:<?= $fc === null || $fl === null ? '#B45309' : '#15803D' ?>;">এখন: কোর্স — <?= $fc === null ? 'বন্ধ (প্রকাশ করা যাবে না)' : ($fc === 0 ? 'ফ্রি' : '৳' . $fc) ?> · লাইভ ক্লাস — <?= $fl === null ? 'বন্ধ (প্রকাশ করা যাবে না)' : ($fl === 0 ? 'ফ্রি' : '৳' . $fl) ?></small>
        </form>
    </div>
    <?php endif; ?>
</div>
<script>
(function () {
    function tok() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    async function post(url, data) {
        var fd = data instanceof FormData ? data : new FormData(); if (!(data instanceof FormData)) Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', tok());
        try { return await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); } catch (e) { return { success: false, message: 'সার্ভারে সমস্যা' }; }
    }
    function toast(m) { var t = document.querySelector('.ac-toast'); if (!t) { t = document.createElement('div'); t.className = 'ac-toast'; document.body.appendChild(t); } t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 3000); }
    document.querySelectorAll('[data-hide]').forEach(function (b) { b.addEventListener('click', async function () {
        if (b.dataset.h === '1' && !confirm('কোর্সটা Course পেজ থেকে লুকাবেন? টিচার নোটিফিকেশন পাবেন।')) return;
        var d = await post('/Admin/ToggleCourse', { id: b.dataset.hide, hidden: b.dataset.h }); toast(d.message || ''); if (d.success) setTimeout(function () { location.reload(); }, 800);
    }); });
    document.querySelectorAll('[data-pay]').forEach(function (f) { f.addEventListener('submit', async function (e) {
        e.preventDefault(); if (!confirm('টাকা পাঠানো হয়েছে হিসেবে রেকর্ড করবেন?')) return;
        var fd = new FormData(f); fd.append('teacherId', f.dataset.pay); fd.append('kind', <?= js($isLv ? 'live' : 'course') ?>);
        var d = await post('/Admin/AddCoursePayout', fd); toast(d.message || ''); if (d.success) setTimeout(function () { location.reload(); }, 900);
    }); });
    var p = document.getElementById('acPct');
    if (p) p.addEventListener('submit', async function (e) { e.preventDefault(); var d = await post('/Admin/SaveCourseSettings', new FormData(p)); toast(d.message || ''); });
    var fe = document.getElementById('acFee');
    if (fe) fe.addEventListener('submit', async function (e) { e.preventDefault(); var d = await post('/Admin/SavePublishFee', new FormData(fe)); toast(d.message || ''); if (d.success) setTimeout(function () { location.reload(); }, 900); });
})();
</script>
