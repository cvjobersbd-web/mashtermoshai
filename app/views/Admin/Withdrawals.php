<?php
/** Admin → Withdraw: রেফারেল বোনাসের টাকা তোলার আবেদন — নম্বর দেখে টাকা পাঠান, তারপর Paid (TxnID সহ) বা বাতিল (কারণ সহ) */
$ViewData['Title'] = 'Withdraw Requests';
$list = is_array($Model) ? $Model : [];
$st = $ViewBag->Stats; $tab = (string)$ViewBag->Tab; $bal = $ViewBag->Balances ?? []; $cn = $ViewBag->Counts ?? [];
$sc = ['Pending' => ['অপেক্ষমাণ', 'p'], 'Paid' => ['টাকা পাঠানো হয়েছে', 'ok'], 'Rejected' => ['বাতিল', 'no']];
$mIcon = ['bKash' => '#E2136E', 'Nagad' => '#F6921E', 'Rocket' => '#8C3494', 'Bank' => '#0F766E'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .wd h2 { color: #08224A; margin: 0; font-size: 24px; }
    .wd .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 880px; }
    .wd-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .wd-st { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); }
    .wd-st small { display: block; color: #64748B; font-weight: 700; font-size: 12px; } .wd-st b { display: block; font-size: 22px; color: #0F172A; margin-top: 3px; }
    .wd-tabs { display: flex; gap: 6px; margin-bottom: 14px; flex-wrap: wrap; }
    .wd-tabs a { padding: 8px 15px; border-radius: 999px; background: #fff; color: #334155; font-weight: 700; font-size: 13.5px; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,.04); display: inline-flex; gap: 7px; align-items: center; }
    .wd-tabs a.on { background: #08224A; color: #fff; }
    .wd-tabs a span { background: rgba(0,0,0,.08); border-radius: 999px; padding: 0 8px; font-size: 12px; }
    .wd-tabs a.on span { background: rgba(255,255,255,.2); }
    .wd-c { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 16px 18px; margin-bottom: 14px; border-left: 5px solid var(--c, #CBD5E1); }
    .wd-top { display: flex; gap: 14px; align-items: flex-start; flex-wrap: wrap; }
    .wd-who { flex: 1; min-width: 220px; }
    .wd-who b { font-size: 16px; color: #0F172A; } .wd-who b a { color: inherit; text-decoration: none; } .wd-who b a:hover { text-decoration: underline; }
    .wd-who small { display: block; color: #64748B; font-size: 12.5px; margin-top: 3px; }
    .wd-amt { text-align: right; } .wd-amt b { display: block; font-size: 26px; font-weight: 800; color: #15803D; line-height: 1.1; }
    .wd-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; margin-top: 5px; }
    .wd-pill.p { background: #FEF3C7; color: #92400E; } .wd-pill.ok { background: #DCFCE7; color: #166534; } .wd-pill.no { background: #FEE2E2; color: #991B1B; }
    .wd-acc { display: grid; grid-template-columns: repeat(4, minmax(0, auto)); justify-content: start; gap: 6px 22px; margin-top: 12px; background: #F8FAFC; border: 1px solid #EEF2F7; border-radius: 12px; padding: 12px 14px; }
    .wd-acc small { display: block; color: #64748B; font-size: 11.5px; font-weight: 700; }
    .wd-acc b { font-size: 15px; color: #0F172A; }
    .wd-acc .num { font-family: ui-monospace, monospace; font-size: 18px; letter-spacing: .6px; }
    .wd-copy { border: none; background: #E0F2FE; color: #0369A1; border-radius: 7px; height: 26px; padding: 0 9px; font-size: 12px; font-weight: 700; cursor: pointer; margin-left: 6px; vertical-align: middle; }
    .wd-bal { margin-top: 10px; font-size: 12.5px; color: #475569; display: flex; gap: 14px; flex-wrap: wrap; }
    .wd-bal b { color: #0F172A; }
    .wd-note { margin-top: 10px; font-size: 13px; background: #F1F5F9; border-radius: 10px; padding: 8px 12px; color: #334155; }
    .wd-act { margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; border-top: 1px dashed #E2E8F0; padding-top: 14px; }
    .wd-act input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; flex: 1 1 220px; min-width: 0; outline: none; }
    .wd-act input:focus { border-color: #02B8F5; }
    .wd-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
    .wd-btn.g { background: #15803D; } .wd-btn.r { background: #FEE2E2; color: #B91C1C; } .wd-btn.l { background: #F1F5F9; color: #334155; }
    .wd-form[data-f="pay"] { display: contents; } .wd-form[data-f="reject"] { width: 100%; }
    .wd-form[hidden] { display: none !important; }
    .wd-msg { font-size: 13px; font-weight: 600; } .wd-msg.err { color: #B91C1C; }
    .wd-empty { text-align: center; color: #94A3B8; padding: 40px 10px; background: #fff; border-radius: 16px; }
    .wd-empty i { font-size: 34px; display: block; margin-bottom: 8px; color: #CBD5E1; }
    .wd-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .wd-stats { grid-template-columns: minmax(0, 1fr); } .wd-acc { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px) { .wd-amt { text-align: left; width: 100%; } .wd-acc { grid-template-columns: minmax(0, 1fr); } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content wd">
    <?= csrf_field() ?>
    <h2><i class="fas fa-money-bill-transfer" style="color:#16A34A;"></i> Withdraw আবেদন</h2>
    <p class="sub">ইউজাররা রেফারেল বোনাসের টাকা তুলতে আবেদন করলে এখানে আসে। নিচের নম্বরে টাকা পাঠান, তারপর Transaction ID লিখে <b>"টাকা পাঠিয়েছি"</b> চাপুন — ইউজার নোটিফিকেশন পাবে। ভুল / সন্দেহজনক হলে কারণ লিখে বাতিল করুন — টাকা ইউজারের ব্যালেন্সে ফেরত যাবে।</p>

    <div class="wd-stats">
        <div class="wd-st" style="--c:#F59E0B;"><small>অপেক্ষমাণ আবেদন</small><b><?= (int)$st['pendingCount'] ?>টা · ৳<?= number_format($st['pendingAmount']) ?></b></div>
        <div class="wd-st" style="--c:#16A34A;"><small>মোট পাঠানো হয়েছে</small><b>৳<?= number_format($st['paid']) ?></b></div>
        <div class="wd-st" style="--c:#0EA5E9;"><small>মোট বোনাস দেওয়া হয়েছে</small><b>৳<?= number_format($st['earned']) ?></b></div>
    </div>

    <div class="wd-tabs">
        <?php foreach (['Pending' => 'অপেক্ষমাণ', 'Paid' => 'Paid', 'Rejected' => 'বাতিল', 'all' => 'সব'] as $k => $l): ?>
            <a href="/Admin/Withdrawals?w=<?= $k ?>" class="<?= $tab === $k ? 'on' : '' ?>"><?= $l ?><?php if ($k !== 'all'): ?> <span><?= (int)($cn[$k] ?? 0) ?></span><?php endif; ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$list): ?>
        <div class="wd-empty"><i class="fas fa-inbox"></i><?= $tab === 'Pending' ? 'এখন কোনো আবেদন অপেক্ষায় নেই।' : 'কোনো আবেদন নেই।' ?></div>
    <?php endif; ?>
    <?php foreach ($list as $w): $nm = !is_blank($w->FullName) ? $w->FullName : ($w->UserName ?: 'User'); [$sl, $sk] = $sc[$w->Status] ?? [$w->Status, 'p']; $b = $bal[(int)$w->UserId] ?? null; ?>
        <div class="wd-c" style="--c:<?= $w->Status === 'Pending' ? '#F59E0B' : ($w->Status === 'Paid' ? '#16A34A' : '#DC2626') ?>;" id="wd-<?= (int)$w->Id ?>">
            <div class="wd-top">
                <div class="wd-who">
                    <b><a href="/Profile/Show/<?= (int)$w->UserId ?>" target="_blank" rel="noopener"><?= e($nm) ?></a></b>
                    <small><?= e(role_label($w->Role, $w->UserId)) ?> · @<?= e((string)$w->UserName) ?><?= !is_blank($w->MobileNumber) ? ' · ' . e($w->MobileNumber) : '' ?></small>
                    <small>আবেদন: <?= e(fmt_date($w->CreatedAt, 'd M Y, h:i A')) ?><?= !is_blank($w->ProcessedAt) ? ' · প্রক্রিয়া: ' . e(fmt_date($w->ProcessedAt, 'd M Y, h:i A')) : '' ?></small>
                </div>
                <div class="wd-amt"><b>৳<?= number_format((int)$w->Amount) ?></b><span class="wd-pill <?= $sk ?>"><?= e($sl) ?></span></div>
            </div>
            <div class="wd-acc">
                <div><small>মাধ্যম</small><b style="color:<?= e($mIcon[$w->Method] ?? '#0F172A') ?>;"><?= e($w->Method) ?></b></div>
                <div><small>নম্বর / অ্যাকাউন্ট</small><b class="num"><?= e($w->AccountNumber) ?></b><button type="button" class="wd-copy" data-copy="<?= e($w->AccountNumber) ?>"><i class="far fa-copy"></i> কপি</button></div>
                <?php if (!is_blank($w->AccountName)): ?><div><small>অ্যাকাউন্টের নাম</small><b><?= e($w->AccountName) ?></b></div><?php endif; ?>
                <?php if (!is_blank($w->BankName)): ?><div><small>ব্যাংক / শাখা</small><b><?= e($w->BankName) ?><?= !is_blank($w->Branch) ? ', ' . e($w->Branch) : '' ?></b></div><?php endif; ?>
            </div>
            <?php if ($b): ?><div class="wd-bal"><span>মোট বোনাস: <b>৳<?= number_format($b['earned']) ?></b></span><span>আগে তুলেছেন: <b>৳<?= number_format($b['withdrawn']) ?></b></span><span>রেফার করেছেন: <b><?= (int)$b['count'] ?> জন</b></span></div><?php endif; ?>
            <?php if (!is_blank($w->TxnId) || !is_blank($w->AdminNote)): ?>
                <div class="wd-note"><?= !is_blank($w->TxnId) ? '<b>TxnID:</b> ' . e($w->TxnId) : '' ?><?= !is_blank($w->TxnId) && !is_blank($w->AdminNote) ? ' · ' : '' ?><?= !is_blank($w->AdminNote) ? '<b>নোট:</b> ' . e($w->AdminNote) : '' ?></div>
            <?php endif; ?>
            <?php if ($w->Status === 'Pending'): ?>
                <div class="wd-act" data-id="<?= (int)$w->Id ?>">
                    <div class="wd-form" data-f="pay">
                        <input type="text" maxlength="60" placeholder="Transaction ID (যেমন: DJU8RDNST8)" data-in="txn" />
                        <button type="button" class="wd-btn g" data-do="Paid"><i class="fas fa-check"></i> টাকা পাঠিয়েছি (Paid)</button>
                        <button type="button" class="wd-btn r" data-show="reject"><i class="fas fa-xmark"></i> বাতিল</button>
                    </div>
                    <div class="wd-form" data-f="reject" hidden>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <input type="text" maxlength="300" placeholder="বাতিলের কারণ লিখুন — ইউজার দেখতে পাবে" data-in="note" />
                            <button type="button" class="wd-btn r" data-do="Rejected"><i class="fas fa-ban"></i> বাতিল নিশ্চিত করুন</button>
                            <button type="button" class="wd-btn l" data-show="pay">ফিরে যান</button>
                        </div>
                    </div>
                    <span class="wd-msg"></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<div class="wd-toast" id="wdToast"></div>
<script>
(function () {
    var tok = document.querySelector('input[name="__RequestVerificationToken"]').value;
    function toast(m) { var t = document.getElementById('wdToast'); t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 2200); }
    document.addEventListener('click', async function (e) {
        var c = e.target.closest('[data-copy]');
        if (c) { var v = c.getAttribute('data-copy'); if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v); else { var a = document.createElement('textarea'); a.value = v; document.body.appendChild(a); a.select(); try { document.execCommand('copy'); } catch (x) { } a.remove(); } toast('নম্বর কপি হয়েছে ✓'); return; }
        var sh = e.target.closest('[data-show]');
        if (sh) { var box = sh.closest('.wd-act'); var want = sh.getAttribute('data-show'); box.querySelectorAll('.wd-form').forEach(function (f) { f.hidden = f.getAttribute('data-f') !== want; }); var inp = box.querySelector('.wd-form[data-f="' + want + '"] input'); if (inp) inp.focus(); return; }
        var b = e.target.closest('[data-do]'); if (!b) return;
        var act = b.closest('.wd-act'), msg = act.querySelector('.wd-msg'), st = b.getAttribute('data-do');
        var fd = new FormData(); fd.append('__RequestVerificationToken', tok); fd.append('id', act.getAttribute('data-id')); fd.append('status', st);
        if (st === 'Paid') fd.append('txn', act.querySelector('[data-in="txn"]').value.trim());
        else { var n = act.querySelector('[data-in="note"]').value.trim(); if (!n) { msg.className = 'wd-msg err'; msg.textContent = 'বাতিলের কারণ লিখুন।'; return; } fd.append('note', n); }
        if (st === 'Paid' && !confirm('টাকা পাঠানো হয়েছে — Paid করবেন? ইউজার নোটিফিকেশন পাবে।')) return;
        b.disabled = true; msg.className = 'wd-msg'; msg.textContent = 'সেভ হচ্ছে...';
        try {
            var d = await (await fetch('/Admin/ProcessWithdrawal', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!d.success) { msg.className = 'wd-msg err'; msg.textContent = d.message || 'হয়নি।'; b.disabled = false; return; }
            toast(d.message || 'হয়েছে'); setTimeout(function () { location.reload(); }, 700);
        } catch (x) { msg.className = 'wd-msg err'; msg.textContent = 'সার্ভারে সমস্যা — আবার চেষ্টা করুন।'; b.disabled = false; }
    });
})();
</script>
