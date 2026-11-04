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
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .rfa-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .rfa-w { grid-template-columns: 1fr; } .rfa-w .acts { justify-content: flex-start; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content rfa">
    <h2><i class="fas fa-gift" style="color:#F59E0B;"></i> Referrals</h2>
    <p class="sub">ইউজাররা নিজের লিংক শেয়ার করে — কেউ সেই লিংক দিয়ে সাইনআপ করলে রেফারার বোনাস পায়। উইথড্র আবেদন দেখে টাকা পাঠান, তারপর <b>Paid</b> চাপুন।</p>

    <div class="rfa-stats">
        <div class="rfa-stat"><small>মোট রেফারেল</small><b><?= number_format($st['referrals']) ?></b></div>
        <div class="rfa-stat"><small>মোট বোনাস দেওয়া হয়েছে</small><b>৳<?= number_format($st['earned']) ?></b></div>
        <div class="rfa-stat"><small>পাঠানো হয়েছে (Paid)</small><b>৳<?= number_format($st['paid']) ?></b></div>
        <div class="rfa-stat warn"><small>অপেক্ষমাণ উইথড্র</small><b><?= (int)$st['pendingCount'] ?> টি · ৳<?= number_format($st['pendingAmount']) ?></b></div>
    </div>

    <div class="rfa-card">
        <h3><i class="fas fa-sliders" style="color:#00B5E0;"></i> বোনাস সেটিং</h3>
        <form class="rfa-set" id="rfaSet">
            <div><label for="rfaBonus">প্রতি রেফারে বোনাস (৳)</label><input type="number" id="rfaBonus" name="bonus" min="0" max="10000" value="<?= (int)$set['bonus'] ?>" /></div>
            <div><label for="rfaMin">সর্বনিম্ন উইথড্র (৳)</label><input type="number" id="rfaMin" name="min" min="1" value="<?= (int)$set['min'] ?>" /></div>
            <div class="form-check form-switch" style="padding-bottom:8px;"><input class="form-check-input" type="checkbox" id="rfaOn" name="enabled" <?= $set['enabled'] ? 'checked' : '' ?> /><label class="form-check-label fw-semibold" for="rfaOn">রেফারেল চালু</label></div>
            <button type="submit" class="rfa-btn"><i class="fas fa-save"></i> Save</button>
        </form>
        <p style="font-size:12.5px; color:#64748B; margin:10px 0 0;">বোনাস বদলালে শুধু এরপরের নতুন সাইনআপে নতুন পরিমাণ দেবে — আগের বোনাস যেমন আছে তেমন থাকবে।</p>
    </div>

    <div class="rfa-card">
        <h3><i class="fas fa-money-bill-transfer" style="color:#16A34A;"></i> উইথড্র আবেদন</h3>
        <div class="rfa-tabs">
            <?php foreach (['Pending' => 'অপেক্ষমাণ', 'Paid' => 'Paid', 'Rejected' => 'বাতিল', 'all' => 'সব'] as $k => $l): ?><a href="/Admin/Referrals?w=<?= $k ?>" class="<?= $tab === $k ? 'on' : '' ?>"><?= $l ?></a><?php endforeach; ?>
        </div>
        <?php if (!$ViewBag->Withdrawals): ?><div class="rfa-empty">কোনো আবেদন নেই।</div><?php endif; ?>
        <?php foreach ($ViewBag->Withdrawals as $w): $nm = !is_blank($w->FullName) ? $w->FullName : ($w->UserName ?: 'User'); ?>
            <div class="rfa-w" id="rfw-<?= (int)$w->Id ?>">
                <div>
                    <div class="who"><b><?= e($nm) ?></b> <span class="rfa-pill <?= $sc[$w->Status] ?? 'p' ?>"><?= e($w->Status) ?></span>
                        <small><?= $w->Role === 'Teacher' ? 'Teacher' : 'Student' ?> · আবেদন: <?= e(fmt_date($w->CreatedAt, 'd M Y, h:i A')) ?><?= !is_blank($w->ProcessedAt) ? ' · প্রক্রিয়া: ' . e(fmt_date($w->ProcessedAt, 'd M Y')) : '' ?></small></div>
                    <div class="acc"><?= e($w->Method) ?>: <b><?= e($w->AccountNumber) ?></b>
                        <?php if (!is_blank($w->AccountName)): ?> · <?= e($w->AccountName) ?><?php endif; ?>
                        <?php if (!is_blank($w->BankName)): ?><br><?= e($w->BankName) ?><?= !is_blank($w->Branch) ? ', ' . e($w->Branch) : '' ?><?php endif; ?>
                        <?php if (!is_blank($w->TxnId)): ?><br>TxnID: <?= e($w->TxnId) ?><?php endif; ?>
                        <?php if (!is_blank($w->AdminNote)): ?><br><small style="color:#64748B;">নোট: <?= e($w->AdminNote) ?></small><?php endif; ?>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="amt">৳<?= number_format((int)$w->Amount) ?></div>
                    <?php if ($w->Status === 'Pending'): ?>
                        <div class="acts" style="margin-top:8px;">
                            <button type="button" class="rfa-btn g" data-wd="<?= (int)$w->Id ?>" data-st="Paid"><i class="fas fa-check"></i> Paid</button>
                            <button type="button" class="rfa-btn r" data-wd="<?= (int)$w->Id ?>" data-st="Rejected"><i class="fas fa-xmark"></i> বাতিল</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="rfa-card">
        <h3><i class="fas fa-users" style="color:#00B5E0;"></i> সাম্প্রতিক রেফারেল</h3>
        <?php if (!$ViewBag->Referrals): ?><div class="rfa-empty">এখনো কোনো রেফারেল নেই।</div><?php else: ?>
            <div class="rfa-tw"><table class="rfa-tbl">
                <tr><th>রেফার করেছেন</th><th>নতুন ইউজার</th><th>তারিখ</th><th>IP</th><th>বোনাস</th><th></th></tr>
                <?php foreach ($ViewBag->Referrals as $r): ?>
                    <tr>
                        <td><a href="/Admin/UserDetails/<?= (int)$r->ReferrerId ?>"><?= e($r->ReferrerName ?: $r->ReferrerUser ?: '#' . $r->ReferrerId) ?></a></td>
                        <td><?= e($r->ReferredName ?: $r->ReferredUser ?: 'Deleted') ?><br><small style="color:#94A3B8;"><?= e((string)$r->ReferredEmail) ?></small></td>
                        <td><?= e(fmt_date($r->CreatedAt, 'd M Y, h:i A')) ?></td>
                        <td><small><?= e((string)$r->Ip) ?></small></td>
                        <td><?= $r->Status === 'Credited' ? '<b style="color:#16A34A;">৳' . (int)$r->Bonus . '</b>' : '<span class="rfa-pill no">বাতিল</span>' ?></td>
                        <td><button type="button" class="rfa-btn <?= $r->Status === 'Credited' ? 'r' : 'l' ?>" style="height:32px; font-size:12px;" data-ref="<?= (int)$r->Id ?>" data-st="<?= $r->Status === 'Credited' ? 'Revoked' : 'Credited' ?>"><?= $r->Status === 'Credited' ? 'বাতিল' : 'আবার চালু' ?></button></td>
                    </tr>
                <?php endforeach; ?>
            </table></div>
            <p style="font-size:12.5px; color:#64748B; margin:10px 0 0;">একই IP থেকে অনেক সাইনআপ বা ভুয়া অ্যাকাউন্ট দেখলে "বাতিল" চাপুন — সেই বোনাস রেফারারের ব্যালেন্স থেকে কেটে যাবে।</p>
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
    });
})();
</script>
<?php section_end(); ?>
