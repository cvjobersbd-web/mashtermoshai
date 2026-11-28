<?php
/** Admin → ফ্রি চ্যাট ও ক্লাস: কোন টিচার কোন শিক্ষার্থীর সাথে প্যাকেজ ছাড়া কথা বলতে আর লাইভ ক্লাস নিতে পারবে */
$ViewData['Title'] = 'ফ্রি চ্যাট ও ক্লাস';
$pairs = $ViewBag->Pairs ?? [];
$av = function ($name, $photo) {
    return '<span class="cf-av">' . e(initials((string)$name, 'U')) . ($photo ? '<img src="' . e($photo) . '" alt="" onerror="this.remove()" />' : '') . '</span>';
};
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 100px; min-height: calc(100vh - 80px); }
    .cf { max-width: 980px; }
    .cf input { box-sizing: border-box; }
    .cf h2 { color: #0F172A; margin: 0; font-size: 26px; font-weight: 800; }
    .cf .sub { color: #64748B; margin: 6px 0 18px; font-size: 14.5px; line-height: 1.6; }
    .cf-card { background: #fff; border: 1px solid #E8EEF4; border-radius: 18px; padding: 22px 24px; margin-bottom: 18px; box-shadow: 0 4px 16px rgba(15,23,42,.04); }
    .cf-card h3 { margin: 0 0 14px; font-size: 17px; font-weight: 800; color: #0F172A; }
    .cf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .cf-pick label { display: block; font-size: 13px; font-weight: 800; color: #334155; margin-bottom: 6px; }
    .cf-pick input { width: 100%; height: 44px; border: 1.5px solid #DCE5EE; border-radius: 11px; padding: 0 12px; font-size: 14.5px; font-family: inherit; }
    .cf-pick input:focus { outline: none; border-color: #00CFFF; }
    .cf-res { margin-top: 6px; display: flex; flex-direction: column; gap: 4px; max-height: 230px; overflow-y: auto; }
    .cf-res button { display: flex; align-items: center; gap: 9px; border: 1px solid #EEF2F7; background: #fff; border-radius: 10px; padding: 6px 8px; text-align: left; cursor: pointer; font-family: inherit; font-size: 13.5px; }
    .cf-res button:hover { border-color: #00B5E0; background: #F0FBFF; }
    .cf-res small, .cf-sel small { display: block; color: #64748B; font-size: 11.5px; }
    .cf-res .none { font-size: 12.5px; color: #94A3B8; padding: 4px 2px; }
    .cf-sel { display: none; align-items: center; gap: 10px; margin-top: 8px; border: 1.5px solid #A7F3D0; background: #ECFDF5; border-radius: 12px; padding: 8px 10px; }
    .cf-sel.on { display: flex; }
    .cf-sel b { flex: 1; min-width: 0; color: #0F172A; }
    .cf-sel .x { border: none; background: #fff; color: #B91C1C; border-radius: 8px; height: 30px; padding: 0 10px; cursor: pointer; font-weight: 700; font-family: inherit; }
    .cf-av { width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #08224A, #0A5A86); color: #fff; font-weight: 700; font-size: 12px; }
    .cf-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .cf-note { margin-top: 14px; } .cf-note input { width: 100%; height: 42px; border: 1.5px solid #DCE5EE; border-radius: 11px; padding: 0 12px; font-size: 14px; font-family: inherit; }
    .cf-a { display: flex; gap: 12px; align-items: center; margin-top: 14px; flex-wrap: wrap; }
    .cf-btn { height: 44px; padding: 0 20px; border-radius: 11px; border: none; background: #08224A; color: #fff; font-weight: 800; cursor: pointer; font-family: inherit; font-size: 14.5px; display: inline-flex; align-items: center; gap: 8px; }
    .cf-btn:disabled { opacity: .5; cursor: default; }
    .cf-msg { font-size: 14px; font-weight: 600; } .cf-msg.ok { color: #047857; } .cf-msg.err { color: #B91C1C; }
    .cf-tbl { width: 100%; border-collapse: collapse; font-size: 14px; }
    .cf-tbl th { text-align: left; font-size: 12px; color: #64748B; text-transform: uppercase; padding: 8px 10px; border-bottom: 1px solid #E5ECF3; }
    .cf-tbl td { padding: 10px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
    .cf-who { display: flex; align-items: center; gap: 9px; } .cf-who b { color: #0F172A; display: block; } .cf-who small { color: #64748B; font-size: 12px; }
    .cf-rm { border: 1px solid #FECACA; background: #fff; color: #B91C1C; border-radius: 9px; height: 34px; padding: 0 12px; font-weight: 700; cursor: pointer; font-family: inherit; }
    .cf-empty { text-align: center; color: #94A3B8; padding: 20px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 90px; } }
    @media (max-width: 700px) { .cf-grid { grid-template-columns: 1fr; } .cf-tbl .hide-sm { display: none; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content cf">
    <?= csrf_field() ?>
    <h2><i class="fas fa-handshake" style="color:#00B5E0;"></i> ফ্রি চ্যাট ও ক্লাস</h2>
    <p class="sub">একজন টিচার আর একজন শিক্ষার্থী বাছাই করুন — এই দুজন প্যাকেজ ছাড়াই চ্যাটে কথা বলতে পারবে, আর টিচার এই শিক্ষার্থীকে লাইভ ক্লাস নিতে পারবে। দুজনকেই নোটিফিকেশন যাবে, আর চ্যাট নিজে থেকে খুলে যাবে। মোবাইল নম্বর / লিংক ব্লক আগের মতোই থাকবে।</p>

    <div class="cf-card">
        <h3><i class="fas fa-plus-circle" style="color:#00B5E0;"></i> নতুন জোড়া যোগ করুন</h3>
        <div class="cf-grid">
            <div class="cf-pick" data-role="Teacher">
                <label>টিচার</label>
                <input type="search" placeholder="নাম / ইউজারনেম / ইমেইল / মোবাইল / TCH-1234" autocomplete="off" />
                <div class="cf-res"></div>
                <div class="cf-sel"></div>
            </div>
            <div class="cf-pick" data-role="User">
                <label>শিক্ষার্থী</label>
                <input type="search" placeholder="নাম / ইউজারনেম / ইমেইল / মোবাইল" autocomplete="off" />
                <div class="cf-res"></div>
                <div class="cf-sel"></div>
            </div>
        </div>
        <div class="cf-note"><input id="cfNote" maxlength="200" placeholder="নোট (ঐচ্ছিক) — যেমন: ডেমো ক্লাস, স্পেশাল অফার" /></div>
        <div class="cf-a"><button type="button" class="cf-btn" id="cfAdd" disabled><i class="fas fa-check"></i> ফ্রি করে দিন</button><span class="cf-msg" id="cfMsg"></span></div>
    </div>

    <div class="cf-card">
        <h3><i class="fas fa-list" style="color:#00B5E0;"></i> ফ্রি জোড়ার তালিকা (<?= count($pairs) ?>)</h3>
        <?php if (!$pairs): ?>
            <div class="cf-empty">এখনো কোনো জোড়া যোগ করা হয়নি।</div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="cf-tbl">
            <thead><tr><th>টিচার</th><th>শিক্ষার্থী</th><th class="hide-sm">নোট</th><th class="hide-sm">কবে</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pairs as $p): $tn = !is_blank($p->TName) ? $p->TName : (string)$p->TUser; $sn = !is_blank($p->SName) ? $p->SName : (string)$p->SUser; ?>
                <tr>
                    <td><div class="cf-who"><?= $av($tn, $p->TPhoto) ?><div><b><?= e($tn) ?></b><small><?= e(trim(teacher_code((int)$p->TeacherId) . ' · @' . $p->TUser, ' ·')) ?></small></div></div></td>
                    <td><div class="cf-who"><?= $av($sn, $p->SPhoto) ?><div><b><?= e($sn) ?></b><small>@<?= e((string)$p->SUser) ?></small></div></div></td>
                    <td class="hide-sm"><?= e((string)($p->Note ?? '')) ?></td>
                    <td class="hide-sm"><?= e(date('d M Y', strtotime((string)$p->CreatedAt))) ?></td>
                    <td style="text-align:right;"><button type="button" class="cf-rm" data-rm="<?= (int)$p->Id ?>"><i class="fas fa-xmark"></i> রিমুভ</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var token = function () { var i = document.querySelector('input[name="__RequestVerificationToken"]'); return i ? i.value : ''; };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
    var picked = { Teacher: 0, User: 0 }, addBtn = document.getElementById('cfAdd'), msg = document.getElementById('cfMsg');
    var avatar = function (u) { return '<span class="cf-av">' + esc(u.initials) + (u.photo ? '<img src="' + esc(u.photo) + '" alt="" onerror="this.remove()" />' : '') + '</span>'; };
    var sub = function (u) { return esc((u.code ? u.code + ' · ' : '') + '@' + u.user + (u.email ? ' · ' + u.email : '')); };
    var check = function () { addBtn.disabled = !(picked.Teacher && picked.User); };
    document.querySelectorAll('.cf-pick').forEach(function (box) {
        var role = box.getAttribute('data-role'), inp = box.querySelector('input'), res = box.querySelector('.cf-res'), sel = box.querySelector('.cf-sel'), tm = null, seq = 0;
        inp.addEventListener('input', function () {
            clearTimeout(tm); var q = inp.value.trim();
            if (q.length < 2) { res.innerHTML = ''; return; }
            tm = setTimeout(async function () {
                var my = ++seq;
                try {
                    var r = await (await fetch('/Admin/ChatFreeFind?role=' + role + '&q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                    if (my !== seq) return;
                    var list = (r && r.users) || [];
                    res.innerHTML = list.length ? list.map(function (u) { return '<button type="button" data-u=\'' + esc(JSON.stringify(u)).replace(/'/g, '&#39;') + '\'>' + avatar(u) + '<span><b>' + esc(u.name) + '</b><small>' + sub(u) + '</small></span></button>'; }).join('') : '<div class="none">কাউকে পাওয়া যায়নি।</div>';
                } catch (e) { res.innerHTML = '<div class="none">খোঁজা যায়নি।</div>'; }
            }, 250);
        });
        res.addEventListener('click', function (e) {
            var b = e.target.closest('button[data-u]'); if (!b) return;
            var u = JSON.parse(b.getAttribute('data-u')); picked[role] = u.id;
            sel.innerHTML = avatar(u) + '<b>' + esc(u.name) + '<small>' + sub(u) + '</small></b><button type="button" class="x">বদলান</button>';
            sel.classList.add('on'); res.innerHTML = ''; inp.value = ''; inp.style.display = 'none'; check();
        });
        sel.addEventListener('click', function (e) {
            if (!e.target.closest('.x')) return;
            picked[role] = 0; sel.classList.remove('on'); sel.innerHTML = ''; inp.style.display = ''; inp.focus(); check();
        });
    });
    addBtn.addEventListener('click', async function () {
        addBtn.disabled = true; msg.className = 'cf-msg'; msg.textContent = '';
        var fd = new FormData(); fd.append('teacherId', picked.Teacher); fd.append('studentId', picked.User); fd.append('note', document.getElementById('cfNote').value); fd.append('__RequestVerificationToken', token());
        try {
            var r = await (await fetch('/Admin/ChatFreeAdd', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            msg.className = 'cf-msg ' + (r.success ? 'ok' : 'err'); msg.textContent = r.message || (r.success ? 'যোগ হয়েছে।' : 'হয়নি।');
            if (r.success) setTimeout(function () { location.reload(); }, 700); else check();
        } catch (e) { msg.className = 'cf-msg err'; msg.textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।'; check(); }
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-rm]'); if (!b) return;
        if (!confirm('এই জোড়ার ফ্রি সুবিধা বন্ধ করবেন? চ্যাটের মেসেজ থাকবে, কিন্তু আবার প্যাকেজের নিয়ম চালু হবে।')) return;
        b.disabled = true;
        var fd = new FormData(); fd.append('id', b.getAttribute('data-rm')); fd.append('__RequestVerificationToken', token());
        try { var r = await (await fetch('/Admin/ChatFreeRemove', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); if (r.success) b.closest('tr').remove(); else { alert(r.message || 'হয়নি।'); b.disabled = false; } }
        catch (x) { b.disabled = false; }
    });
})();
</script>
<?php section_end(); ?>
