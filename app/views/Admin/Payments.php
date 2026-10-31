<?php
/** Admin → Payments: ইউজারদের জমা দেওয়া পেমেন্টের প্রমাণ দেখে Accept / Reject */
$ViewData['Title'] = 'Payments';
$list = is_array($Model) ? $Model : [];
$st = $ViewBag->Status ?? 'Pending';
$stats = $ViewBag->Stats ?? ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Total' => 0, 'Revenue' => 0];
$tabs = ['Pending' => ['যাচাই বাকি', 'fa-hourglass-half'], 'Approved' => ['Accepted', 'fa-circle-check'], 'Rejected' => ['Rejected', 'fa-circle-xmark'], 'all' => ['সব', 'fa-list']];
$fmt = fn($d) => $d ? date('d M Y, h:i A', strtotime((string)$d)) : '';
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .ap-head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; align-items: flex-start; margin-bottom: 18px; }
    .ap-head h2 { color: #08224A; margin: 0; font-size: 24px; }
    .ap-head p { color: #64748B; margin: 4px 0 0; font-size: 14px; }
    .ap-head a { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: 10px; border: 1.5px solid #DCECF5; background: #fff; color: #08224A; font-weight: 600; font-size: 13.5px; text-decoration: none; }
    .ap-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
    .ap-stat { background: #fff; border-radius: 14px; padding: 16px 18px; box-shadow: 0 4px 16px rgba(0,0,0,.06); border-left: 4px solid #F59E0B; }
    .ap-stat.g { border-left-color: #22C55E; } .ap-stat.r { border-left-color: #EF4444; } .ap-stat.b { border-left-color: #1D7FE5; }
    .ap-stat .n { font-size: 26px; font-weight: 800; color: #08224A; line-height: 1.2; }
    .ap-stat .l { font-size: 13px; color: #64748B; }
    .ap-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
    .ap-tab { padding: 9px 18px; border-radius: 30px; font-size: 13px; font-weight: 600; text-decoration: none; border: 1.5px solid #DCECF5; color: #475569; background: #fff; display: inline-flex; gap: 7px; align-items: center; }
    .ap-tab.on { background: #1D7FE5; border-color: #1D7FE5; color: #fff; }
    .ap-tab .c { background: rgba(0,0,0,.08); border-radius: 10px; padding: 0 8px; font-size: 11px; }
    .ap-tab.on .c { background: rgba(255,255,255,.25); }

    .ap-item { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 18px; background: #fff; border: 1px solid #DCECF5; border-left: 5px solid #F59E0B; border-radius: 16px; padding: 16px; margin-bottom: 14px; box-shadow: 0 2px 12px rgba(8,34,74,.04); }
    .ap-item.Approved { border-left-color: #22C55E; } .ap-item.Rejected { border-left-color: #EF4444; }
    .ap-rc { display: block; width: 150px; height: 190px; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; background: #F8FAFC; cursor: zoom-in; position: relative; }
    .ap-rc img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
    .ap-rc .pdf { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #DC2626; font-size: 13px; font-weight: 700; text-decoration: none; }
    .ap-rc .pdf i { font-size: 38px; }
    .ap-rc .zoom { position: absolute; right: 8px; bottom: 8px; background: rgba(8,34,74,.75); color: #fff; border-radius: 8px; padding: 4px 8px; font-size: 11px; }
    .ap-user { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ap-ava { width: 42px; height: 42px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ap-ava img { width: 100%; height: 100%; object-fit: cover; }
    .ap-user .nm { font-weight: 800; color: #08224A; font-size: 15.5px; text-decoration: none; }
    .ap-user small { display: block; color: #64748B; font-size: 12px; }
    .ap-role { font-size: 10.5px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; padding: 2px 9px; border-radius: 10px; background: #CFF2FC; color: #028EA5; }
    .ap-role.Teacher { background: #E8F8EF; color: #155724; }
    .ap-pill { margin-left: auto; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 20px; background: #FEF3C7; color: #92400E; text-transform: uppercase; }
    .ap-pill.Approved { background: #DCFCE7; color: #166534; } .ap-pill.Rejected { background: #FEE2E2; color: #991B1B; }
    .ap-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 10px 16px; margin-top: 14px; }
    .ap-grid div small { display: block; font-size: 11.5px; color: #94A3B8; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
    .ap-grid div b { font-size: 14px; color: #0F172A; word-break: break-all; }
    .ap-grid .amt b { font-size: 18px; color: #1D7FE5; }
    .ap-grid .txn b { font-family: ui-monospace, Menlo, Consolas, monospace; background: #F1F5F9; border-radius: 6px; padding: 1px 6px; }
    .ap-note { margin-top: 12px; padding: 10px 12px; border-radius: 10px; background: #F8FAFC; font-size: 13.5px; color: #334155; white-space: pre-wrap; }
    .ap-note.adm { background: #FEF2F2; color: #991B1B; }
    .ap-note.adm.ok { background: #F0FDF4; color: #166534; }
    .ap-acts { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; padding-top: 14px; border-top: 1px solid #EEF5FA; }
    .ap-btn { border: none; border-radius: 10px; padding: 9px 18px; font-size: 13.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; }
    .ap-btn:disabled { opacity: .55; cursor: default; }
    .ap-btn.ok { background: #22C55E; color: #fff; }
    .ap-btn.no { background: #fff; color: #DC2626; border: 1.5px solid #FCA5A5; }
    .ap-btn.del { background: #fff; color: #64748B; border: 1.5px solid #E2E8F0; margin-left: auto; }
    .ap-empty { text-align: center; padding: 56px 20px; background: #fff; border-radius: 16px; border: 2px dashed #DCECF5; color: #7A8CA0; }
    .ap-empty i { font-size: 44px; color: #B9D6E6; display: block; margin-bottom: 10px; }
    .ap-lb { position: fixed; inset: 0; z-index: 3000; background: rgba(2,6,23,.85); display: flex; align-items: center; justify-content: center; padding: 18px; cursor: zoom-out; }
    .ap-lb[hidden] { display: none; }
    .ap-lb img { max-width: 100%; max-height: 92vh; border-radius: 12px; background: #fff; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 20px 15px; } .ap-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) {
        .ap-item { grid-template-columns: 1fr; }
        .ap-rc { width: 100%; height: 220px; }
        .ap-pill { margin-left: 0; }
        .ap-btn { flex: 1; justify-content: center; }
        .ap-btn.del { margin-left: 0; }
    }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="ap-head">
        <div>
            <h2><i class="fas fa-money-bill-wave me-2" style="color:#22C55E;"></i>Payments</h2>
            <p>ইউজারদের জমা দেওয়া পেমেন্টের প্রমাণ দেখুন — টাকা এসেছে নিশ্চিত হলে Accept করুন, তাহলেই তার প্যাকেজ চালু হবে।</p>
        </div>
        <a href="/Admin/PaymentMethods"><i class="fas fa-wallet"></i> Payment Methods &amp; QR</a>
    </div>
    <?= csrf_field() ?>

    <div class="ap-stats">
        <div class="ap-stat"><div class="n"><?= e($stats['Pending']) ?></div><div class="l"><i class="fas fa-hourglass-half"></i> যাচাই বাকি</div></div>
        <div class="ap-stat g"><div class="n"><?= e($stats['Approved']) ?></div><div class="l"><i class="fas fa-circle-check"></i> Accepted</div></div>
        <div class="ap-stat r"><div class="n"><?= e($stats['Rejected']) ?></div><div class="l"><i class="fas fa-circle-xmark"></i> Rejected</div></div>
        <div class="ap-stat b"><div class="n"><?= e(taka($stats['Revenue'])) ?></div><div class="l"><i class="fas fa-sack-dollar"></i> মোট আয় (Accepted)</div></div>
    </div>

    <div class="ap-tabs">
        <?php foreach ($tabs as $k => [$lbl, $ic]): ?>
            <a class="ap-tab<?= $st === $k ? ' on' : '' ?>" href="/Admin/Payments?status=<?= e($k) ?>"><i class="fas <?= $ic ?>"></i> <?= e($lbl) ?> <span class="c"><?= e($k === 'all' ? $stats['Total'] : $stats[$k]) ?></span></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$list): ?>
        <div class="ap-empty"><i class="fas fa-inbox"></i><h5 style="color:#08224A;">কোনো পেমেন্ট নেই</h5><p style="margin:0;">ইউজার প্যাকেজ নিয়ে পেমেন্টের প্রমাণ জমা দিলে এখানে দেখাবে।</p></div>
    <?php endif; ?>

    <?php foreach ($list as $p): ?>
        <div class="ap-item <?= e($p->Status) ?>" id="ap-<?= (int)$p->Id ?>">
            <?php if ($p->IsPdf): ?>
                <a class="ap-rc" href="<?= e($p->ReceiptUrl) ?>" target="_blank" rel="noopener"><span class="pdf"><i class="fas fa-file-pdf"></i> PDF রিসিট খুলুন</span></a>
            <?php else: ?>
                <a class="ap-rc" href="<?= e($p->ReceiptUrl) ?>" data-lb><img src="<?= e($p->ReceiptUrl) ?>" alt="Receipt" loading="lazy" /><span class="zoom"><i class="fas fa-expand"></i> বড় করুন</span></a>
            <?php endif; ?>
            <div style="min-width:0;">
                <div class="ap-user">
                    <span class="ap-ava"><?php if (!is_blank($p->ProfileImageLink)): ?><img src="<?= e($p->ProfileImageLink) ?>" alt="" /><?php else: ?><?= e($p->Initials) ?><?php endif; ?></span>
                    <div>
                        <a class="nm" href="/Profile/Show/<?= (int)$p->UserId ?>" target="_blank"><?= e($p->FullName ?: 'Deleted user') ?></a>
                        <small><?= e(trim(($p->MobileNumber ?? '') . ' · ' . ($p->Email ?? ''), ' ·')) ?></small>
                    </div>
                    <?php if ($p->Role): ?><span class="ap-role <?= e($p->Role) ?>"><?= e($p->Role === 'User' ? 'Student' : $p->Role) ?></span><?php endif; ?>
                    <span class="ap-pill <?= e($p->Status) ?>"><?= e($p->Status === 'Approved' ? 'Accepted' : $p->Status) ?></span>
                </div>
                <div class="ap-grid">
                    <div class="amt"><small>Amount</small><b><?= e(taka($p->Amount)) ?></b></div>
                    <div><small>Package</small><b><?= e($p->PackageName) ?></b> <span style="font-size:12px;color:#64748B;">(<?= $p->Audience === 'Teacher' ? 'শিক্ষক' : 'শিক্ষার্থী' ?>)</span></div>
                    <div><small>Method</small><b><?= e($p->MethodName) ?></b></div>
                    <div class="txn"><small>Transaction ID</small><b><?= e($p->TxnId ?: '—') ?></b></div>
                    <div><small>Payment date</small><b><?= e(date('d M Y', strtotime($p->PayDate))) ?></b></div>
                    <div><small>জমা দিয়েছেন</small><b><?= e($fmt($p->CreatedAt)) ?></b></div>
                    <?php if ($p->AccountInfo): ?><div style="grid-column:1/-1;"><small>যে অ্যাকাউন্টে পাঠিয়েছেন</small><b><?= e($p->AccountInfo) ?></b></div><?php endif; ?>
                </div>
                <?php if ($p->Note): ?><div class="ap-note"><i class="fas fa-comment"></i> <?= e($p->Note) ?></div><?php endif; ?>
                <?php if ($p->AdminNote): ?><div class="ap-note adm<?= $p->Status === 'Approved' ? ' ok' : '' ?>"><i class="fas fa-user-shield"></i> <?= e($p->AdminNote) ?></div><?php endif; ?>
                <div class="ap-acts">
                    <?php if ($p->Status === 'Pending'): ?>
                        <button type="button" class="ap-btn ok" data-ap="approve" data-id="<?= (int)$p->Id ?>"><i class="fas fa-check"></i> Accept</button>
                        <button type="button" class="ap-btn no" data-ap="reject" data-id="<?= (int)$p->Id ?>"><i class="fas fa-times"></i> Reject</button>
                    <?php else: ?>
                        <span style="font-size:12.5px;color:#64748B;"><i class="far fa-clock"></i> যাচাই: <?= e($fmt($p->ReviewedAt)) ?></span>
                        <?php if ($p->Status === 'Rejected'): ?><button type="button" class="ap-btn del" data-ap="delete" data-id="<?= (int)$p->Id ?>"><i class="fas fa-trash"></i> Delete</button><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="ap-lb" id="apLb" hidden><img alt="Receipt" /></div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    function token() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    async function post(url, data) {
        var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); }); fd.append('__RequestVerificationToken', token());
        var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        return r.json();
    }
    var lb = document.getElementById('apLb');
    document.addEventListener('click', async function (e) {
        var z = e.target.closest('[data-lb]');
        if (z) { e.preventDefault(); lb.querySelector('img').src = z.getAttribute('href'); lb.hidden = false; return; }
        if (e.target.closest('#apLb')) { lb.hidden = true; return; }
        var b = e.target.closest('[data-ap]'); if (!b) return;
        var act = b.getAttribute('data-ap'), id = b.getAttribute('data-id'), data = { id: id }, url;
        if (act === 'approve') {
            if (!confirm('টাকা পেয়েছেন নিশ্চিত? Accept করলে ইউজারের প্যাকেজ এখনই চালু হবে।')) return;
            url = '/Admin/ApprovePayment';
        } else if (act === 'reject') {
            var note = prompt('Reject করার কারণ লিখুন (ইউজার দেখতে পাবে):', 'Transaction ID / টাকার পরিমাণ মেলেনি');
            if (note === null) return;
            if (!note.trim()) { alert('কারণ লিখুন।'); return; }
            data.note = note.trim(); url = '/Admin/RejectPayment';
        } else {
            if (!confirm('এই Rejected পেমেন্ট আর রিসিট মুছে ফেলবেন?')) return;
            url = '/Admin/DeletePayment';
        }
        b.disabled = true;
        try {
            var d = await post(url, data);
            if (!d.success) { alert(d.message || 'Failed'); b.disabled = false; return; }
            location.reload();
        } catch (x) { alert('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'); b.disabled = false; }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') lb.hidden = true; });
})();
</script>
<?php section_end(); ?>
