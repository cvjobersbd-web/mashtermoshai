<?php
/** Admin → Referrals: বোনাসের পরিমাণ, উইথড্র আবেদন (টাকা পাঠিয়ে Paid / বাতিল), রেফারেল তালিকা */
$ViewData['Title'] = 'Referrals';
$set = $ViewBag->Settings; $st = $ViewBag->Stats; $tab = (string)$ViewBag->Tab;
$sc = ['Pending' => 'p', 'Paid' => 'ok', 'Rejected' => 'no'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .rfa h2 { color: #08224A; margin: 0; font-size: 24px; }
    .rfa .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; }
    .rfa-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .rfa-stat { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); }
    .rfa-stat small { color: #64748B; font-weight: 700; font-size: 12px; } .rfa-stat b { display: block; font-size: 22px; color: #08224A; }
    .rfa-stat.warn b { color: #B45309; }
    .rfa-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .rfa-card h3 { margin: 0 0 12px; font-size: 17px; font-weight: 800; color: #08224A; display: flex; align-items: center; gap: 8px; }
    .rfa-set { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; }
    .rfa-set label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; }
    .rfa-set input[type=number] { height: 42px; width: 160px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 15px; font-weight: 700; font-family: inherit; }
    .rfa-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 6px; }
    .rfa-btn.g { background: #16A34A; } .rfa-btn.r { background: #FEE2E2; color: #B91C1C; } .rfa-btn.l { background: #F1F5F9; color: #334155; }
    .rfa-tabs { display: flex; gap: 6px; margin-bottom: 12px; flex-wrap: wrap; }
    .rfa-tabs a { padding: 8px 14px; border-radius: 999px; background: #F1F5F9; color: #334155; font-weight: 700; font-size: 13px; text-decoration: none; }
    .rfa-tabs a.on { background: #08224A; color: #fff; }
    .rfa-w { border: 1px solid #EEF2F7; border-radius: 12px; padding: 14px; margin-bottom: 10px; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; }
    .rfa-w .who b { color: #0F172A; font-size: 15px; } .rfa-w .who small { color: #64748B; font-size: 12.5px; display: block; margin-top: 2px; }
    .rfa-w .amt { font-size: 20px; font-weight: 800; color: #16A34A; }
    .rfa-w .acc { margin-top: 8px; background: #F8FAFC; border-radius: 10px; padding: 8px 12px; font-size: 13.5px; color: #334155; }
    .rfa-w .acc b { font-family: monospace; font-size: 15px; color: #0F172A; letter-spacing: .5px; }
    .rfa-w .acts { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .rfa-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; }
    .rfa-pill.p { background: #FEF3C7; color: #92400E; } .rfa-pill.ok { background: #DCFCE7; color: #166534; } .rfa-pill.no { background: #FEE2E2; color: #991B1B; }
    .rfa-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rfa-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 8px 6px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .rfa-tbl td { padding: 9px 6px; border-bottom: 1px solid #F1F5F9; color: #334155; }
    .rfa-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    .rfa-tw { overflow-x: auto; }
    .rfg-search { display: flex; align-items: center; gap: 10px; border: 1.5px solid #DCECF5; border-radius: 12px; padding: 0 14px; margin-bottom: 12px; background: #fff; }
    .rfg-search i { color: #94A3B8; } .rfg-search input { border: none; outline: none; height: 44px; flex: 1; min-width: 0; font-size: 14.5px; font-family: inherit; }
    .rfg { border: 1px solid #E5ECF3; border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
    .rfg[open] { border-color: #C4B5FD; }
    .rfg summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 10px 14px; flex-wrap: wrap; padding: 12px 14px; background: #FAFBFF; }
    .rfg summary::-webkit-details-marker { display: none; }
    .rfg-who { font-size: 15px; font-weight: 800; color: #0F172A; } .rfg-who i { color: #7C3AED; margin-right: 4px; }
    .rfg-who a { color: inherit; text-decoration: none; } .rfg-who a:hover { text-decoration: underline; }
    .rfg-who small { font-weight: 600; color: #64748B; font-size: 12.5px; }
    .rfg-code { font-family: ui-monospace, monospace; font-weight: 800; font-size: 13px; background: #FEF3C7; color: #92400E; border-radius: 8px; padding: 3px 9px; letter-spacing: .5px; }
    .rfg-n { margin-left: auto; font-size: 13.5px; color: #334155; }
    .rfg-car { color: #94A3B8; transition: transform .2s; } .rfg[open] .rfg-car { transform: rotate(180deg); }
    .rfg .rfa-tbl { margin: 0; } .rfg .rfa-tw { padding: 4px 12px 10px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .rfa-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .rfa-w { grid-template-columns: 1fr; } .rfa-w .acts { justify-content: flex-start; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content rfa">
    <h2><i class="fas fa-gift" style="color:#F59E0B;"></i> Referrals</h2>
    <p class="sub">ইউজাররা নিজের লিংক শেয়ার করে — কেউ সেই লিংক দিয়ে সাইনআপ করে প্যাকেজ কিনলে (পেমেন্ট Approve হলে) রেফারার বোনাস পায় — একজন ইউজারের জন্য একবারই। উইথড্র আবেদন দেখতে ও টাকা পাঠাতে বাম মেনুর <a href="/Admin/Withdrawals"><b>Withdraw</b></a> পেজে যান।</p>

    <div class="rfa-stats">
        <div class="rfa-stat"><small>মোট রেফারেল · প্যাকেজ কিনেছে</small><b><?= number_format($st['referrals']) ?> · <?= number_format($st['bought'] ?? 0) ?></b></div>
        <div class="rfa-stat"><small>মোট বোনাস দেওয়া হয়েছে</small><b>৳<?= number_format($st['earned']) ?></b></div>
        <div class="rfa-stat"><small>পাঠানো হয়েছে (Paid)</small><b>৳<?= number_format($st['paid']) ?></b></div>
        <div class="rfa-stat warn"><small>অপেক্ষমাণ উইথড্র</small><b><?= (int)$st['pendingCount'] ?> টি · ৳<?= number_format($st['pendingAmount']) ?></b></div>
    </div>

    <div class="rfa-card">
        <h3><i class="fas fa-sliders" style="color:#00B5E0;"></i> বোনাস সেটিং</h3>
        <form class="rfa-set" id="rfaSet">
            <div><label for="rfaBonus">প্যাকেজ কিনলে বোনাস (৳)</label><input type="number" id="rfaBonus" name="bonus" min="0" max="10000" value="<?= (int)$set['bonus'] ?>" /></div>
            <div><label for="rfaMin">সর্বনিম্ন উইথড্র (৳)</label><input type="number" id="rfaMin" name="min" min="1" value="<?= (int)$set['min'] ?>" /></div>
            <div class="form-check form-switch" style="padding-bottom:8px;"><input class="form-check-input" type="checkbox" id="rfaOn" name="enabled" <?= $set['enabled'] ? 'checked' : '' ?> /><label class="form-check-label fw-semibold" for="rfaOn">রেফারেল চালু</label></div>
            <button type="submit" class="rfa-btn"><i class="fas fa-save"></i> Save</button>
        </form>
        <p style="font-size:12.5px; color:#64748B; margin:10px 0 0;">বোনাস বদলালে এরপর যারা প্যাকেজ কিনবে তাদের রেফারার নতুন পরিমাণ পাবে — আগে দেওয়া বোনাস যেমন আছে তেমন থাকবে।</p>
    </div>

    <?php if ((int)$st['pendingCount'] > 0): ?>
    <a class="rfa-card" href="/Admin/Withdrawals" style="display:flex;align-items:center;gap:12px;text-decoration:none;border:1.5px solid #FDE68A;background:#FFFBEB;">
        <i class="fas fa-money-bill-transfer" style="font-size:22px;color:#B45309;"></i>
        <span style="color:#92400E;font-weight:700;"><?= (int)$st['pendingCount'] ?>টা উইথড্র আবেদন অপেক্ষায় আছে (৳<?= number_format($st['pendingAmount']) ?>) — টাকা পাঠাতে <u>Withdraw পেজে যান</u></span>
    </a>
    <?php endif; ?>

    <?php
    $refs = $ViewBag->Referrals ?: [];
    $groups = [];
    foreach ($refs as $r) {
        $k = (int)$r->ReferrerId;
        $groups[$k] ??= ['name' => (string)($r->ReferrerName ?: $r->ReferrerUser ?: ('#' . $k)), 'user' => (string)$r->ReferrerUser, 'code' => (string)$r->RefCode, 'role' => (string)$r->ReferrerRole, 'list' => [], 'ok' => 0];
        $groups[$k]['list'][] = $r;
        if ($r->Status === 'Credited') $groups[$k]['ok']++;
    }
    uasort($groups, fn($a, $b) => count($b['list']) <=> count($a['list']));
    $roleBn = fn($r, $uid = 0) => role_label($r, $uid);
    $stBn = fn($r) => $r->Status === 'Credited' ? '<span class="rfa-pill ok">প্যাকেজ কিনেছে · ৳' . (int)$r->Bonus . ' বোনাস</span>' : ($r->Status === 'Waiting' ? '<span class="rfa-pill" style="background:#F1F5F9;color:#64748B;">এখনো প্যাকেজ কেনেনি</span>' : '<span class="rfa-pill no">বাতিল</span>');
    ?>
    <div class="rfa-card">
        <h3><i class="fas fa-sitemap" style="color:#7C3AED;"></i> কে কার রেফারেল কোডে এসেছে <span class="rfa-pill" style="background:#EDE9FE;color:#5B21B6;"><?= count($groups) ?> জন রেফারার · <?= count($refs) ?> জন নতুন ইউজার</span></h3>
        <?php if (!$refs): ?><div class="rfa-empty">এখনো কোনো রেফারেল নেই।</div><?php else: ?>
        <div class="rfg-search"><i class="fas fa-magnifying-glass"></i><input type="search" id="rfgQ" placeholder="নাম, ইউজারনেম, ইমেইল বা রেফারেল কোড (যেমন MMAB12CD) দিয়ে খুঁজুন" autocomplete="off" /></div>
        <div id="rfgList">
        <?php foreach ($groups as $rid => $g): ?>
            <details class="rfg" data-s="<?= e(mb_strtolower($g['name'] . ' ' . $g['user'] . ' ' . $g['code'] . ' ' . implode(' ', array_map(fn($x) => ($x->ReferredName ?? '') . ' ' . ($x->ReferredUser ?? '') . ' ' . ($x->ReferredEmail ?? ''), $g['list'])))) ?>">
                <summary>
                    <span class="rfg-who"><i class="fas fa-user-tie"></i> <a href="/Admin/UserDetails/<?= (int)$rid ?>"><?= e($g['name']) ?></a> <small><?= e($roleBn($g["role"], $rid)) ?><?= $g['user'] !== '' ? ' · @' . e($g['user']) : '' ?></small></span>
                    <?php if ($g['code'] !== ''): ?><span class="rfg-code" title="এই ব্যক্তির রেফারেল কোড"><i class="fas fa-ticket"></i> <?= e($g['code']) ?></span><?php endif; ?>
                    <span class="rfg-n"><b><?= count($g['list']) ?></b> জন এসেছে<?= $g['ok'] ? ' · <span style="color:#16A34A;">' . $g['ok'] . ' জন কিনেছে</span>' : '' ?></span>
                    <i class="fas fa-chevron-down rfg-car"></i>
                </summary>
                <div class="rfa-tw"><table class="rfa-tbl">
                    <tr><th>নতুন ইউজার</th><th>কবে এসেছে</th><th>অবস্থা</th><th>IP</th><th></th></tr>
                    <?php foreach ($g['list'] as $r): ?>
                        <tr>
                            <td><?php if ($r->ReferredName || $r->ReferredUser): ?><a href="/Profile/Show/<?= (int)$r->ReferredUserId ?>" target="_blank"><b><?= e($r->ReferredName ?: $r->ReferredUser) ?></b></a> <small style="color:#64748B;">(<?= e($roleBn((string)$r->ReferredRole, (int)$r->ReferredUserId)) ?>)</small><?php else: ?><span style="color:#94A3B8;">Deleted</span><?php endif; ?><br><small style="color:#94A3B8;"><?= e((string)$r->ReferredEmail) ?></small></td>
                            <td><?= e(fmt_date($r->CreatedAt, 'd M Y, h:i A')) ?></td>
                            <td><?= $stBn($r) ?></td>
                            <td><small><?= e((string)$r->Ip) ?></small></td>
                            <td><button type="button" class="rfa-btn <?= $r->Status !== 'Revoked' ? 'r' : 'l' ?>" style="height:32px; font-size:12px;" data-ref="<?= (int)$r->Id ?>" data-st="<?= $r->Status !== 'Revoked' ? 'Revoked' : 'Credited' ?>"><?= $r->Status !== 'Revoked' ? 'বাতিল' : 'আবার চালু' ?></button></td>
                        </tr>
                    <?php endforeach; ?>
                </table></div>
            </details>
        <?php endforeach; ?>
        </div>
        <div class="rfa-empty" id="rfgNone" hidden>কিছু পাওয়া যায়নি।</div>
        <p style="font-size:12.5px; color:#64748B; margin:10px 0 0;">কারো নামে ক্লিক করলে তার আনা সবার তালিকা খুলবে। একই IP থেকে অনেক সাইনআপ বা ভুয়া অ্যাকাউন্ট দেখলে "বাতিল" চাপুন — সেই বোনাস রেফারারের ব্যালেন্স থেকে কেটে যাবে।</p>
        <?php endif; ?>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    function tok() { return document.querySelector('input[name="__RequestVerificationToken"]').value; }
    async function post(url, data) { var fd = new FormData(); for (var k in data) fd.append(k, data[k]); fd.append('__RequestVerificationToken', tok()); return (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); }
    document.getElementById('rfaSet').addEventListener('submit', async function (e) {
        e.preventDefault(); var f = e.target;
        var r = await post('/Admin/SaveReferralSettings', { bonus: f.bonus.value, min: f.min.value, enabled: f.enabled.checked ? 'true' : 'false' });
        alert((r.success ? '✅ ' : '❌ ') + (r.message || ''));
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-wd]');
        if (b) {
            var st = b.getAttribute('data-st'), data = { id: b.getAttribute('data-wd'), status: st };
            if (st === 'Paid') { var t = prompt('টাকা পাঠিয়েছেন? Transaction ID দিন (ঐচ্ছিক):', ''); if (t === null) return; data.txn = t; }
            else { var n = prompt('বাতিলের কারণ লিখুন (ইউজার দেখতে পাবে):', ''); if (!n) return; data.note = n; }
            b.disabled = true;
            var r = await post('/Admin/ProcessWithdrawal', data);
            if (!r.success) { alert('❌ ' + (r.message || '')); b.disabled = false; return; }
            location.reload(); return;
        }
        var c = e.target.closest('[data-ref]');
        if (c) {
            if (c.getAttribute('data-st') === 'Revoked' && !confirm('এই রেফারেলের বোনাস বাতিল করবেন?')) return;
            var r2 = await post('/Admin/SetReferralStatus', { id: c.getAttribute('data-ref'), status: c.getAttribute('data-st') });
            if (!r2.success) { alert('❌ ' + (r2.message || '')); return; }
            location.reload();
        }
    });    var q = document.getElementById('rfgQ');
    if (q) q.addEventListener('input', function () {
        var v = q.value.trim().toLowerCase(), any = false;
        document.querySelectorAll('#rfgList .rfg').forEach(function (d) {
            var hit = !v || d.getAttribute('data-s').indexOf(v) !== -1; d.hidden = !hit; if (hit) any = true;
            if (v && hit) d.open = true; else if (!v) d.open = false;
        });
        document.getElementById('rfgNone').hidden = any;
    });
})();
</script>
<?php section_end(); ?>
