<?php
/** Admin → Delete User (শুধু Super Admin): মুছে ফেলা ইউজারের রয়ে যাওয়া ডাটা মোছা + ইউজার খুঁজে পুরোপুরি মোছা */
$ViewData['Title'] = 'Delete User';
$orph = is_array($Model) ? $Model : [];
$q = (string)$ViewBag->Q; $found = $ViewBag->Found ?? [];
$totalRows = array_sum(array_map(fn($o) => $o['total'], $orph));
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .du h2 { color: #08224A; margin: 0; font-size: 24px; }
    .du .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 900px; }
    .du-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .du-card h3 { margin: 0 0 4px; font-size: 17px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .du-card .hint { color: #64748B; font-size: 13px; margin: 0 0 14px; }
    .du-warn { display: flex; gap: 10px; align-items: flex-start; background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 10px 14px; font-size: 13.5px; font-weight: 600; margin-bottom: 18px; }
    .du-btn { height: 38px; padding: 0 15px; border-radius: 10px; border: none; background: #DC2626; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; text-decoration: none; }
    .du-btn.dark { background: #08224A; } .du-btn.ghost { background: #F1F5F9; color: #334155; } .du-btn:disabled { opacity: .6; cursor: default; }
    .du-row { display: flex; gap: 14px; align-items: center; border: 1px solid #EEF2F7; border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; flex-wrap: wrap; }
    .du-av { width: 42px; height: 42px; border-radius: 50%; background: #FEE2E2; color: #B91C1C; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
    .du-av.u { background: #E0F2FE; color: #0369A1; font-weight: 800; font-size: 14px; }
    .du-who { flex: 1; min-width: 200px; }
    .du-who b { color: #0F172A; font-size: 15px; } .du-who small { display: block; color: #64748B; font-size: 12.5px; margin-top: 2px; }
    .du-who b a { color: inherit; text-decoration: none; } .du-who b a:hover { text-decoration: underline; }
    .du-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }
    .du-tags span { font-size: 11.5px; font-weight: 700; background: #F1F5F9; color: #475569; border-radius: 999px; padding: 2px 9px; }
    .du-tags span.m { background: #DCFCE7; color: #166534; }
    .du-srch { display: flex; gap: 8px; flex-wrap: wrap; }
    .du-srch input { flex: 1 1 280px; height: 42px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; outline: none; }
    .du-srch input:focus { border-color: #02B8F5; }
    .du-role { font-size: 11px; font-weight: 800; border-radius: 999px; padding: 2px 8px; margin-left: 6px; }
    .du-role.t { background: #E0F2FE; color: #0369A1; } .du-role.s { background: #F3E8FF; color: #7E22CE; }
    .du-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    .du-empty i { font-size: 30px; display: block; margin-bottom: 8px; color: #86EFAC; }
    .du-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; max-width: 90vw; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content du">
    <?= csrf_field() ?>
    <h2><i class="fas fa-user-xmark" style="color:#DC2626;"></i> Delete User</h2>
    <p class="sub">শুধু Super Admin এই পেজ দেখেন। এখান থেকে ইউজার আর তার সব ডাটা ওয়েবসাইট থেকে পুরোপুরি মুছে ফেলা যায়।</p>
    <div class="du-warn"><i class="fas fa-triangle-exclamation" style="margin-top:2px;"></i><div>এখান থেকে যা মুছবেন তা আর কখনো ফেরত আনা যাবে না — পেমেন্টের হিসাব, প্যাকেজ, রেফারেল, চ্যাট সব মুছে যাবে। আয়ের হিসাব (Subscriptions) থেকেও বাদ যাবে।</div></div>

    <div class="du-card">
        <h3><i class="fas fa-ghost" style="color:#DC2626;"></i> "Deleted user" — মুছে ফেলা ইউজারদের রয়ে যাওয়া ডাটা <span style="color:#94A3B8;font-weight:600;font-size:14px;">(<?= count($orph) ?> জন)</span>
            <?php if (count($orph) > 1): ?><button type="button" class="du-btn" id="duAll" style="margin-left:auto;"><i class="fas fa-trash"></i> সব মুছে ফেলুন (<?= count($orph) ?> জন · <?= number_format($totalRows) ?>টা রেকর্ড)</button><?php endif; ?></h3>
        <p class="hint">এই ইউজারদের অ্যাকাউন্ট আগেই মুছে ফেলা হয়েছিল, কিন্তু তাদের পেমেন্ট / রেফারেল / পোস্ট ইত্যাদি রয়ে গেছে — তাই বিভিন্ন জায়গায় "Deleted user" দেখায়। মুছে ফেললে এগুলো সব জায়গা থেকে চলে যাবে।</p>
        <?php if (!$orph): ?>
            <div class="du-empty"><i class="fas fa-circle-check"></i>কোনো "Deleted user" এর ডাটা নেই — সব পরিষ্কার।</div>
        <?php else: foreach ($orph as $id => $o): ?>
            <div class="du-row">
                <div class="du-av"><i class="fas fa-user-slash"></i></div>
                <div class="du-who">
                    <b>Deleted user <span style="color:#94A3B8;font-weight:600;">#<?= (int)$id ?></span><?= $o['hint'] !== '' ? ' <span style="color:#64748B;font-weight:600;font-size:13px;">(' . e($o['hint']) . ')</span>' : '' ?></b>
                    <div class="du-tags">
                        <?php foreach ($o['items'] as $lbl => $n): ?><span><?= e($lbl) ?>: <?= (int)$n ?></span><?php endforeach; ?>
                        <?php if ($o['paid'] > 0): ?><span class="m">কেনা প্যাকেজ: ৳<?= number_format($o['paid']) ?></span><?php endif; ?>
                    </div>
                </div>
                <button type="button" class="du-btn" data-orph="<?= (int)$id ?>"><i class="fas fa-trash"></i> পুরোপুরি মুছুন</button>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="du-card">
        <h3><i class="fas fa-magnifying-glass" style="color:#08224A;"></i> ইউজার খুঁজে পুরোপুরি মুছুন</h3>
        <p class="hint">নাম, username, ইমেইল, মোবাইল বা ID দিয়ে খুঁজুন। মুছলে অ্যাকাউন্টসহ তার সব ডাটা চলে যাবে। (অ্যাডমিন অ্যাকাউন্ট এখানে আসে না — Admins পেজ থেকে সরান।)</p>
        <form class="du-srch" method="get" action="/Admin/DeleteUsers">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="যেমন: rahim, rahim@gmail.com, 017..., 24" autocomplete="off" />
            <button type="submit" class="du-btn dark"><i class="fas fa-magnifying-glass"></i> খুঁজুন</button>
            <?php if ($q !== ''): ?><a class="du-btn ghost" href="/Admin/DeleteUsers">মুছুন</a><?php endif; ?>
        </form>
        <?php if ($q !== ''): ?>
            <div style="margin-top:14px;">
            <?php if (!$found): ?><div class="du-empty" style="padding:14px 0;">"<?= e($q) ?>" — কাউকে পাওয়া যায়নি।</div><?php endif; ?>
            <?php foreach ($found as $u): $nm = !is_blank($u->FullName) ? $u->FullName : $u->UserName; ?>
                <div class="du-row">
                    <div class="du-av u"><?= e(initials((string)$nm)) ?></div>
                    <div class="du-who">
                        <b><a href="/Profile/Show/<?= (int)$u->Id ?>" target="_blank" rel="noopener"><?= e($nm) ?></a></b><span class="du-role <?= $u->Role === 'Teacher' ? 't' : 's' ?>"><?= $u->Role === 'Teacher' ? 'Teacher' : 'Student' ?></span>
                        <small>#<?= (int)$u->Id ?> · @<?= e($u->UserName) ?> · <?= e($u->Email) ?><?= !is_blank($u->MobileNumber) ? ' · ' . e($u->MobileNumber) : '' ?> · যোগ দিয়েছেন <?= e(fmt_date($u->CreatedAt, 'd M Y')) ?></small>
                    </div>
                    <button type="button" class="du-btn" data-user="<?= (int)$u->Id ?>" data-name="<?= e($nm) ?>"><i class="fas fa-trash"></i> অ্যাকাউন্ট + সব ডাটা মুছুন</button>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="du-toast" id="duToast"></div>
<script>
(function () {
    var tok = document.querySelector('input[name="__RequestVerificationToken"]').value;
    function toast(m) { var t = document.getElementById('duToast'); t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 3200); }
    async function post(url, data) {
        var fd = new FormData(); fd.append('__RequestVerificationToken', tok);
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        try { return await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); }
        catch (e) { return { success: false, message: 'সার্ভারে সমস্যা — আবার চেষ্টা করুন।' }; }
    }
    function sure(msg) {
        if (!confirm(msg + '\n\nএটা আর ফেরত আনা যাবে না।')) return false;
        var t = prompt('নিশ্চিত করতে DELETE লিখুন:', '');
        if (t === null || t.trim().toUpperCase() !== 'DELETE') { toast('মোছা হয়নি।'); return false; }
        return true;
    }
    async function run(btn, url, data) {
        btn.disabled = true; var h = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> মুছছে...';
        var d = await post(url, data); toast(d.message || '');
        if (d.success) setTimeout(function () { location.reload(); }, 1200); else { btn.disabled = false; btn.innerHTML = h; }
    }
    document.addEventListener('click', function (e) {
        var o = e.target.closest('[data-orph]');
        if (o) { if (sure('Deleted user #' + o.getAttribute('data-orph') + ' এর সব রয়ে যাওয়া ডাটা পুরোপুরি মুছে ফেলবেন?')) run(o, '/Admin/PurgeOrphanUser', { id: o.getAttribute('data-orph') }); return; }
        var u = e.target.closest('[data-user]');
        if (u) { if (sure('"' + u.getAttribute('data-name') + '" এর অ্যাকাউন্ট আর সব ডাটা (পোস্ট, পেমেন্ট, প্যাকেজ, চ্যাট, রেফারেল...) পুরোপুরি মুছে ফেলবেন?')) run(u, '/Admin/DeleteUser', { id: u.getAttribute('data-user') }); return; }
        var all = e.target.closest('#duAll');
        if (all) { if (sure('সব "Deleted user" এর রয়ে যাওয়া ডাটা একসাথে পুরোপুরি মুছে ফেলবেন?')) run(all, '/Admin/PurgeOrphanUser', { all: 'true' }); }
    });
})();
</script>
