<?php
/** Admin → Devices: নতুন ডিভাইসে লগইনের অনুরোধ (Accept / বাতিল) আর চালু ডিভাইস লগআউট */
$ViewData['Title'] = 'Devices';
$list = is_array($Model) ? $Model : [];
$st = (string)$ViewBag->St; $q = (string)$ViewBag->Q; $on = (bool)$ViewBag->On;
$pill = ['Pending' => ['অনুরোধ অপেক্ষায়', 'warn'], 'Active' => ['চালু', 'ok'], 'Revoked' => ['লগআউট করা', 'gray'], 'Rejected' => ['বাতিল', 'no']];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .dv h2 { color: #08224A; margin: 0; font-size: 24px; }
    .dv .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 820px; }
    .dv-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .dv-rule { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .dv-rule b { display: block; font-size: 15.5px; color: #0F172A; } .dv-rule small { color: #64748B; font-size: 13px; line-height: 1.55; }
    .dv-sw { position: relative; width: 52px; height: 30px; flex-shrink: 0; }
    .dv-sw input { opacity: 0; width: 0; height: 0; position: absolute; }
    .dv-sw span { position: absolute; inset: 0; border-radius: 999px; background: #CBD5E1; cursor: pointer; transition: background .2s; }
    .dv-sw span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 24px; height: 24px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
    .dv-sw input:checked + span { background: #16A34A; } .dv-sw input:checked + span::after { transform: translateX(22px); }
    .dv-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .dv-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
    .dv-tabs a { padding: 7px 14px; border-radius: 999px; background: #F1F5F9; color: #334155; font-weight: 700; font-size: 13px; text-decoration: none; }
    .dv-tabs a.on { background: #08224A; color: #fff; }
    .dv-tabs a .n { background: #F59E0B; color: #fff; border-radius: 999px; padding: 0 7px; margin-left: 5px; font-size: 11.5px; }
    .dv-q { display: flex; gap: 6px; } .dv-q input { height: 38px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; width: 230px; }
    .dv-btn { height: 36px; padding: 0 14px; border-radius: 9px; border: none; font-weight: 700; font-size: 13px; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .dv-btn.dark { background: #08224A; color: #fff; } .dv-btn.ok { background: #16A34A; color: #fff; } .dv-btn.no { background: #FEE2E2; color: #B91C1C; } .dv-btn.out { background: #FFF7ED; color: #C2410C; border: 1px solid #FED7AA; }
    .dv-row { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.6fr) auto; gap: 14px; align-items: center; border: 1px solid #EEF2F7; border-radius: 14px; padding: 14px; margin-bottom: 10px; }
    .dv-row.pending { border-color: #FCD34D; background: #FFFDF5; }
    .dv-who a { font-weight: 800; color: #0F172A; text-decoration: none; } .dv-who small { display: block; color: #64748B; font-size: 12.5px; margin-top: 2px; }
    .dv-role { font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 999px; margin-left: 4px; } .dv-role.t { background: #E0F2FE; color: #0369A1; } .dv-role.s { background: #F3E8FF; color: #7E22CE; }
    .dv-devs { display: grid; gap: 6px; font-size: 13.5px; color: #334155; }
    .dv-devs div { display: flex; gap: 8px; align-items: flex-start; } .dv-devs i { width: 18px; margin-top: 3px; text-align: center; } .dv-devs .old i { color: #B45309; } .dv-devs .new i { color: #0369A1; }
    .dv-devs small { color: #94A3B8; }
    .dv-note { font-size: 12.5px; color: #475569; background: #F8FAFC; border-radius: 8px; padding: 5px 9px; margin-top: 4px; }
    .dv-pill { font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .dv-pill.ok { background: #DCFCE7; color: #166534; } .dv-pill.warn { background: #FEF3C7; color: #92400E; } .dv-pill.no { background: #FEE2E2; color: #991B1B; } .dv-pill.gray { background: #F1F5F9; color: #475569; }
    .dv-acts { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; align-items: center; }
    .dv-empty { text-align: center; color: #94A3B8; padding: 26px 0; }
    #dvMsg { font-size: 13.5px; font-weight: 600; margin-left: 4px; } #dvMsg.ok { color: #15803D; } #dvMsg.err { color: #B91C1C; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .dv-row { grid-template-columns: minmax(0, 1fr); } .dv-acts { justify-content: flex-start; } .dv-q input { width: 100%; } .dv-q { width: 100%; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content dv">
    <?= csrf_field() ?>
    <h2><i class="fas fa-mobile-screen" style="color:#00B5E0;"></i> Devices</h2>
    <p class="sub">শিক্ষার্থী আর টিচার একবারে শুধু একটা ডিভাইসে লগইন থাকতে পারেন। অন্য ডিভাইস থেকে লগইন করতে চাইলে এখানে অনুরোধ আসে — <b>Accept</b> করলে নতুন ডিভাইস চালু হয় আর আগের ডিভাইস নিজে থেকেই লগআউট হয়ে যায়। চাইলে যেকোনো চালু ডিভাইস <b>Logout</b> করতে পারেন।</p>

    <div class="dv-card dv-rule">
        <div><b>এক ডিভাইসে লগইনের নিয়ম</b><small>চালু থাকলে প্রথম যে ডিভাইসে লগইন করবেন সেটাই চালু থাকবে। অ্যাডমিন অ্যাকাউন্টে এই নিয়ম খাটে না।</small> <span id="dvMsg" role="status"></span></div>
        <label class="dv-sw"><input type="checkbox" id="dvOn"<?= $on ? ' checked' : '' ?> /><span></span></label>
    </div>

    <div class="dv-card">
        <div class="dv-bar">
            <div class="dv-tabs">
                <?php foreach (['Pending' => 'অনুরোধ', 'Active' => 'চালু ডিভাইস', 'all' => 'সব'] as $k => $l): ?>
                    <a href="/Admin/Devices?<?= e(http_build_query(array_filter(['st' => $k, 'q' => $q]))) ?>" class="<?= $st === $k ? 'on' : '' ?>"><?= $l ?><?= $k === 'Pending' && (int)$ViewBag->Pending ? '<span class="n">' . (int)$ViewBag->Pending . '</span>' : '' ?></a>
                <?php endforeach; ?>
            </div>
            <form class="dv-q" method="get" action="/Admin/Devices"><input type="hidden" name="st" value="<?= e($st) ?>" /><input name="q" value="<?= e($q) ?>" placeholder="নাম, username, ইমেইল বা মোবাইল" /><button class="dv-btn dark" type="submit"><i class="fas fa-magnifying-glass"></i></button></form>
        </div>
        <?php if (!$list): ?><div class="dv-empty"><?= $st === 'Pending' ? 'এখন কোনো নতুন ডিভাইসের অনুরোধ নেই।' : 'কিছু পাওয়া যায়নি।' ?></div><?php endif; ?>
        <?php foreach ($list as $d): [$pl, $pc] = $pill[$d->Status] ?? [$d->Status, 'gray']; ?>
            <div class="dv-row<?= $d->Status === 'Pending' ? ' pending' : '' ?>" id="dv-<?= $d->Id ?>">
                <div class="dv-who">
                    <a href="/Profile/Show/<?= $d->UserId ?>" target="_blank" rel="noopener"><?= e($d->Name) ?></a><span class="dv-role <?= $d->Role === 'Teacher' ? 't' : 's' ?>"><?= $d->Role === 'Teacher' ? 'টিচার' : 'শিক্ষার্থী' ?></span>
                    <small>@<?= e($d->UserName) ?><?= !is_blank($d->MobileNumber) ? ' · ' . e($d->MobileNumber) : '' ?></small>
                </div>
                <div class="dv-devs">
                    <?php if ($d->Status === 'Pending'): ?>
                        <div class="old"><i class="fas fa-laptop"></i><span>এখন চালু: <b><?= e($d->CurrentLabel ?: '—') ?></b><?= $d->CurrentSeen ? ' <small>· শেষ ব্যবহার ' . e(time_ago($d->CurrentSeen)) . '</small>' : '' ?></span></div>
                        <div class="new"><i class="fas fa-mobile-screen-button"></i><span>নতুন: <b><?= e((string)$d->Label) ?></b> <small>· অনুরোধ <?= e(time_ago($d->CreatedAt)) ?> · IP <?= e((string)$d->Ip) ?></small></span></div>
                        <?php if (!is_blank($d->Note)): ?><div class="dv-note"><i class="fas fa-quote-left"></i> <?= e($d->Note) ?></div><?php endif; ?>
                    <?php else: ?>
                        <div class="new"><i class="fas fa-<?= stripos((string)$d->Label, 'Android') !== false || stripos((string)$d->Label, 'iPhone') !== false ? 'mobile-screen-button' : 'laptop' ?>"></i><span><b><?= e((string)$d->Label) ?></b> <small>· শেষ ব্যবহার <?= e(time_ago($d->LastSeenAt ?: $d->CreatedAt)) ?> · IP <?= e((string)$d->Ip) ?></small></span></div>
                    <?php endif; ?>
                </div>
                <div class="dv-acts">
                    <span class="dv-pill <?= $pc ?>"><?= $pl ?></span>
                    <?php if ($d->Status === 'Pending'): ?>
                        <button type="button" class="dv-btn ok" data-dv="ApproveDevice" data-id="<?= $d->Id ?>"><i class="fas fa-check"></i> Accept</button>
                        <button type="button" class="dv-btn no" data-dv="RejectDevice" data-id="<?= $d->Id ?>"><i class="fas fa-xmark"></i> বাতিল</button>
                    <?php elseif ($d->Status === 'Active'): ?>
                        <button type="button" class="dv-btn out" data-dv="RevokeDevice" data-id="<?= $d->Id ?>"><i class="fas fa-right-from-bracket"></i> Logout</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
(function () {
    var tk = function () { return document.querySelector('input[name="__RequestVerificationToken"]').value; };
    async function post(url, data) {
        var fd = new FormData(); for (var k in data) fd.append(k, data[k]); fd.append('__RequestVerificationToken', tk());
        return (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
    }
    var ask = { ApproveDevice: 'Accept করবেন? নতুন ডিভাইস চালু হবে আর আগের ডিভাইস থেকে লগআউট হয়ে যাবে।', RejectDevice: 'অনুরোধটি বাতিল করবেন?', RevokeDevice: 'এই ডিভাইস থেকে লগআউট করবেন? পরের বার যে ডিভাইসে লগইন করবে সেটাই চালু হবে।' };
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-dv]'); if (!b) return;
        var act = b.getAttribute('data-dv'); if (!confirm(ask[act])) return;
        b.disabled = true;
        try { var r = await post('/Admin/' + act, { id: b.getAttribute('data-id') }); alert((r.success ? '✅ ' : '❌ ') + (r.message || '')); if (r.success) location.reload(); else b.disabled = false; }
        catch (x) { b.disabled = false; alert('নেটওয়ার্ক সমস্যা।'); }
    });
    var on = document.getElementById('dvOn'), msg = document.getElementById('dvMsg');
    on.addEventListener('change', async function () {
        try { var r = await post('/Admin/SaveDeviceLock', { on: on.checked ? 'true' : 'false' }); msg.className = r.success ? 'ok' : 'err'; msg.textContent = r.message || ''; }
        catch (x) { msg.className = 'err'; msg.textContent = 'সেভ করা যায়নি।'; }
    });
})();
</script>
