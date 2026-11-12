<?php
/**
 * Admin → Package Details: কোন টিচার / শিক্ষার্থী কোন প্যাকেজ কিনেছে, কবে পর্যন্ত চালু — আর দরকারে মেয়াদ বাড়ানো (শুধু Super Admin)।
 */
$ViewData['Title'] = 'Package Details';
$rows = is_array($Model) ? $Model : [];
$aud = (string)$ViewBag->Aud; $st = (string)$ViewBag->St; $q = (string)$ViewBag->Q; $counts = (array)$ViewBag->Counts;
$audBn = ['Teacher' => 'টিচার', 'Student' => 'শিক্ষার্থী'];
$stBn = ['active' => ['on', 'চালু'], 'expiring' => ['soon', 'শীঘ্রই শেষ'], 'credit' => ['on', 'চালু'], 'expired' => ['off', 'মেয়াদ শেষ']];
$link = fn(array $x) => '/Admin/PackageDetails?' . http_build_query(array_filter(array_merge(['aud' => $aud !== 'all' ? $aud : null, 'st' => $st !== 'all' ? $st : null, 'q' => $q !== '' ? $q : null], $x), fn($v) => $v !== null && $v !== ''));
$days = ['7' => '৭ দিন', '30' => '১ মাস', '90' => '৩ মাস', '180' => '৬ মাস', '365' => '১ বছর', '0' => 'নিজে লিখুন'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .pd h2 { color: #08224A; margin: 0; font-size: 24px; font-weight: 800; }
    .pd .sub { color: #64748B; margin: 4px 0 16px; font-size: 14px; }
    .pd-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 12px; }
    .pd-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .pd-tabs a { display: inline-flex; gap: 7px; align-items: center; padding: 8px 14px; border-radius: 11px; background: #fff; border: 1.5px solid #DCECF5; color: #334155; font-weight: 700; font-size: 13.5px; text-decoration: none; }
    .pd-tabs a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .pd-tabs a .c { font-size: 11.5px; padding: 1px 8px; border-radius: 9px; background: #F1F5F9; color: #475569; }
    .pd-tabs a.on .c { background: rgba(255,255,255,.2); color: #fff; }
    .pd-tabs.small a { padding: 6px 11px; font-size: 12.5px; }
    .pd-search { display: flex; gap: 6px; margin-left: auto; flex: 1; min-width: 220px; max-width: 380px; }
    .pd-search input { flex: 1; height: 40px; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 14px; }
    .pd-search button { height: 40px; padding: 0 14px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; }
    .pd-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 16px; margin-bottom: 14px; }
    .pd-card h3 { margin: 0 0 10px; font-size: 15.5px; font-weight: 800; color: #08224A; }
    .pd-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .pd-tbl th { text-align: left; font-size: 11.5px; color: #64748B; padding: 8px; border-bottom: 1px solid #E2E8F0; white-space: nowrap; text-transform: uppercase; }
    .pd-tbl td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
    .pd-u { display: flex; gap: 10px; align-items: center; min-width: 180px; }
    .pd-av { width: 38px; height: 38px; border-radius: 50%; background: #E0F2FE; color: #0369A1; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0; }
    .pd-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .pd-u a { color: #08224A; font-weight: 800; text-decoration: none; } .pd-u a:hover { text-decoration: underline; }
    .pd-u small { display: block; color: #64748B; font-size: 12px; }
    .pd-tag { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; white-space: nowrap; }
    .pd-tag.Teacher { background: #DBEAFE; color: #1D4ED8; } .pd-tag.Student { background: #DCFCE7; color: #166534; }
    .pd-tag.on { background: #DCFCE7; color: #166534; } .pd-tag.soon { background: #FEF3C7; color: #92400E; } .pd-tag.off { background: #FEE2E2; color: #991B1B; }
    .pd-hist { margin: 0; padding: 0; list-style: none; font-size: 12.5px; color: #334155; min-width: 220px; }
    .pd-hist li { padding: 2px 0; } .pd-hist li b { color: #08224A; } .pd-hist li small { color: #64748B; }
    .pd-ext { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; min-width: 230px; }
    .pd-ext select, .pd-ext input { height: 34px; border: 1.5px solid #DCE7F0; border-radius: 8px; padding: 0 6px; font-family: inherit; font-size: 13px; background: #fff; }
    .pd-ext input { width: 70px; }
    .pd-ext button { height: 34px; padding: 0 12px; border: none; border-radius: 8px; background: #7C3AED; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13px; white-space: nowrap; }
    .pd-msg { flex-basis: 100%; font-size: 12px; font-weight: 700; }
    .pd-new { display: grid; grid-template-columns: minmax(220px, 1.4fr) auto; gap: 10px; align-items: start; }
    .pd-new input[type=search] { width: 100%; height: 40px; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 14px; }
    .pd-res { margin-top: 6px; border: 1px solid #E2E8F0; border-radius: 10px; max-height: 240px; overflow: auto; }
    .pd-res:empty { display: none; }
    .pd-res button { display: flex; width: 100%; gap: 8px; align-items: center; text-align: left; border: none; background: #fff; padding: 8px 10px; border-bottom: 1px solid #F1F5F9; cursor: pointer; font-family: inherit; font-size: 13.5px; }
    .pd-res button:hover, .pd-res button.on { background: #F5F3FF; }
    .pd-res small { color: #64748B; }
    .pd-empty { text-align: center; color: #94A3B8; padding: 26px 0; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .pd-search { margin-left: 0; max-width: none; } .pd-new { grid-template-columns: 1fr; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content pd">
    <?= csrf_field() ?>
    <h2><i class="fas fa-box-open" style="color:#7C3AED;"></i> Package Details</h2>
    <p class="sub">কোন টিচার / শিক্ষার্থী কোন প্যাকেজ কিনেছে, কবে কিনেছে, কবে পর্যন্ত চালু। ডান পাশের "মেয়াদ বাড়ান" থেকে যেকোনো জনের প্যাকেজের মেয়াদ বাড়াতে পারবেন (ইউজার নোটিফিকেশন পাবে)।</p>

    <div class="pd-card">
        <h3><i class="fas fa-gift" style="color:#7C3AED;"></i> প্যাকেজ নেই এমন কাউকে প্যাকেজ দিন</h3>
        <div class="pd-new">
            <div>
                <input type="search" id="pdFind" placeholder="নাম, ইউজারনেম, ইমেইল বা মোবাইল লিখে খুঁজুন..." autocomplete="off" />
                <div class="pd-res" id="pdRes"></div>
            </div>
            <div class="pd-ext" id="pdNewExt" data-uid="">
                <select data-k="aud"><option value="Student">শিক্ষার্থী প্যাকেজ</option><option value="Teacher">টিচার প্যাকেজ</option></select>
                <select data-k="preset"><?php foreach ($days as $v => $l): ?><option value="<?= $v ?>"<?= (string)$v === "30" ? " selected" : "" ?>><?= $l ?></option><?php endforeach; ?></select>
                <input type="number" data-k="days" min="1" max="3650" placeholder="দিন" hidden />
                <button type="button" data-ext><i class="fas fa-plus"></i> প্যাকেজ দিন</button>
                <div class="pd-msg" data-msg>উপরে খুঁজে একজনকে বাছাই করুন।</div>
            </div>
        </div>
    </div>

    <div class="pd-bar">
        <div class="pd-tabs">
            <?php foreach (['all' => 'সব', 'Teacher' => 'টিচার', 'Student' => 'শিক্ষার্থী'] as $k => $lb): ?>
                <a href="<?= e($link(['aud' => $k !== 'all' ? $k : ''])) ?>" class="<?= $aud === $k ? 'on' : '' ?>"><?= $lb ?> <span class="c"><?= (int)($counts[$k] ?? 0) ?></span></a>
            <?php endforeach; ?>
        </div>
        <div class="pd-tabs small">
            <?php foreach (['all' => 'সব অবস্থা', 'active' => 'চালু', 'expiring' => 'শীঘ্রই শেষ', 'expired' => 'মেয়াদ শেষ'] as $k => $lb): ?>
                <a href="<?= e($link(['st' => $k !== 'all' ? $k : ''])) ?>" class="<?= $st === $k ? 'on' : '' ?>"><?= $lb ?></a>
            <?php endforeach; ?>
        </div>
        <form class="pd-search" method="get" action="/Admin/PackageDetails">
            <?php if ($aud !== 'all'): ?><input type="hidden" name="aud" value="<?= e($aud) ?>" /><?php endif; ?>
            <?php if ($st !== 'all'): ?><input type="hidden" name="st" value="<?= e($st) ?>" /><?php endif; ?>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="নাম বা ইউজারনেম দিয়ে খুঁজুন..." />
            <button type="submit"><i class="fas fa-magnifying-glass"></i></button>
        </form>
    </div>

    <div class="pd-card">
        <h3><i class="fas fa-list"></i> প্যাকেজ কেনা ইউজার (<?= count($rows) ?>)</h3>
        <?php if (!$rows): ?><div class="pd-empty"><?= $q !== '' ? '"' . e($q) . '" দিয়ে কাউকে পাওয়া যায়নি।' : 'এখনো কেউ প্যাকেজ কেনেনি।' ?></div><?php else: ?>
        <div style="overflow-x:auto;"><table class="pd-tbl">
            <tr><th>ইউজার</th><th>প্যাকেজ</th><th>কেনা প্যাকেজগুলো</th><th>মোট খরচ</th><th>এখনকার অবস্থা</th><th>মেয়াদ বাড়ান</th></tr>
            <?php foreach ($rows as $o): [$sc, $sl] = $stBn[$o->State] ?? ['off', $o->State]; ?>
                <tr>
                    <td><div class="pd-u"><span class="pd-av"><?= e(initials($o->Name, 'U')) ?><?php if ($o->Photo !== ''): ?><img src="<?= e($o->Photo) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                        <div><a href="/Profile/Show/<?= (int)$o->UserId ?>" target="_blank"><?= e($o->Name) ?></a><small>@<?= e($o->UserName) ?> · <?= e(role_label($o->Role, $o->UserId)) ?></small></div></div></td>
                    <td><span class="pd-tag <?= e($o->Audience) ?>"><?= e($audBn[$o->Audience] ?? $o->Audience) ?></span></td>
                    <td><ul class="pd-hist"><?php foreach (array_slice($o->History, 0, 6) as $h): ?>
                        <li><b><?= e((string)$h->PackageName) ?></b><?= $h->Amount !== null ? ' · ' . e(taka((int)$h->Amount)) : ($h->PaymentId ? '' : ' · <small>ফ্রি (অ্যাডমিন)</small>') ?><br><small>কেনা <?= e(date('d M Y', strtotime((string)$h->CreatedAt))) ?><?= $h->EndsAt ? ' → ' . e(date('d M Y', strtotime((string)$h->EndsAt))) : ' · মেয়াদহীন' ?></small></li>
                    <?php endforeach; ?><?= count($o->History) > 6 ? '<li><small>আরো ' . (count($o->History) - 6) . 'টি...</small></li>' : '' ?></ul></td>
                    <td><b><?= e(taka((int)$o->Total)) ?></b><br><small style="color:#64748B;"><?= (int)$o->Count ?>টি প্যাকেজ</small></td>
                    <td><span class="pd-tag <?= $sc ?>"><?= $sl ?></span><br><small style="color:#64748B;"><?= $o->EndsAt ? e(date('d M Y', strtotime($o->EndsAt))) . ($o->DaysLeft > 0 ? ' (আর ' . (int)$o->DaysLeft . ' দিন)' : '') : 'মেয়াদহীন' ?></small></td>
                    <td><div class="pd-ext" data-uid="<?= (int)$o->UserId ?>" data-aud="<?= e($o->Audience) ?>">
                        <select data-k="preset"><?php foreach ($days as $v => $l): ?><option value="<?= $v ?>"<?= (string)$v === "30" ? " selected" : "" ?>><?= $l ?></option><?php endforeach; ?></select>
                        <input type="number" data-k="days" min="1" max="3650" placeholder="দিন" hidden />
                        <button type="button" data-ext><i class="fas fa-plus"></i> বাড়ান</button>
                        <div class="pd-msg" data-msg></div>
                    </div></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<?php section_start('Scripts'); ?>
<script>
(function () {
    function tok() { return document.querySelector('input[name="__RequestVerificationToken"]').value; }
    async function post(url, data) {
        var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); }); fd.append('__RequestVerificationToken', tok());
        return (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
    }
    document.addEventListener('change', function (e) {
        if (e.target.matches('.pd-ext [data-k="preset"]')) { var d = e.target.parentNode.querySelector('[data-k="days"]'); d.hidden = e.target.value !== '0'; if (!d.hidden) d.focus(); }
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-ext]'); if (!b) return;
        var box = b.closest('.pd-ext'), msg = box.querySelector('[data-msg]');
        var uid = box.dataset.uid, aud = box.dataset.aud || box.querySelector('[data-k="aud"]').value;
        if (!uid) { msg.style.color = '#DC2626'; msg.textContent = 'আগে উপরে খুঁজে একজনকে বাছাই করুন।'; return; }
        var p = box.querySelector('[data-k="preset"]').value, d = p === '0' ? box.querySelector('[data-k="days"]').value : p;
        b.disabled = true; msg.style.color = '#64748B'; msg.textContent = '...';
        try {
            var r = await post('/Admin/ExtendPackage', { userId: uid, aud: aud, days: d });
            msg.style.color = r.success ? '#15803D' : '#DC2626'; msg.textContent = r.message || '';
            if (r.success) setTimeout(function () { location.reload(); }, 1300); else b.disabled = false;
        } catch (x) { msg.style.color = '#DC2626'; msg.textContent = 'সমস্যা হয়েছে, আবার চেষ্টা করুন।'; b.disabled = false; }
    });
    // প্যাকেজ নেই এমন কাউকে খোঁজা
    var f = document.getElementById('pdFind'), res = document.getElementById('pdRes'), nb = document.getElementById('pdNewExt'), t = null;
    f.addEventListener('input', function () {
        clearTimeout(t); var v = f.value.trim(); if (v.length < 2) { res.innerHTML = ''; return; }
        t = setTimeout(async function () {
            var r = await post('/Admin/RoleUserSearch', { q: v });
            res.innerHTML = (r.users || []).map(function (u) {
                var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
                return '<button type="button" data-pick="' + u.id + '" data-name="' + esc(u.name) + '"><b>' + esc(u.name) + '</b> <small>@' + esc(u.userName) + ' · ' + esc(u.role) + '</small></button>';
            }).join('') || '<div style="padding:10px;color:#94A3B8;font-size:13px;">কাউকে পাওয়া যায়নি</div>';
        }, 250);
    });
    res.addEventListener('click', function (e) {
        var b = e.target.closest('[data-pick]'); if (!b) return;
        res.querySelectorAll('.on').forEach(function (x) { x.classList.remove('on'); }); b.classList.add('on');
        nb.dataset.uid = b.dataset.pick; var m = nb.querySelector('[data-msg]'); m.style.color = '#5B21B6'; m.textContent = 'বাছাই: ' + b.dataset.name;
    });
})();
</script>
<?php section_end(); ?>
