<?php
/** Admin → Refund Requests: Help Desk → Contact থেকে আসা রিফান্ড আবেদন (Transaction ID সহ) */
$ViewData['Title'] = 'Refund Requests';
$list = $Model ?? []; $S = $ViewBag->S; $reasons = Db::REFUND_REASONS;
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .rr h2 { color: #08224A; margin: 0; font-size: 24px; font-weight: 800; }
    .rr .sub { color: #64748B; margin: 4px 0 16px; font-size: 14px; max-width: 900px; }
    .rr-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
    .rr-tabs a { padding: 7px 14px; border-radius: 999px; background: #fff; border: 1px solid #E2E8F0; color: #334155; font-weight: 700; font-size: 13px; text-decoration: none; }
    .rr-tabs a.on { background: #08224A; color: #fff; border-color: #08224A; }
    .rr-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 16px 18px; margin-bottom: 12px; display: grid; grid-template-columns: 1fr auto; gap: 10px 18px; }
    .rr-card h4 { margin: 0; font-size: 15.5px; font-weight: 800; color: #0F172A; } .rr-card h4 small { color: #64748B; font-weight: 600; font-size: 12.5px; }
    .rr-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; font-size: 13px; color: #334155; margin-top: 6px; } .rr-meta b { color: #0F172A; }
    .rr-txn { font-family: monospace; background: #F1F5F9; border-radius: 6px; padding: 1px 7px; font-weight: 700; }
    .rr-why { margin-top: 8px; font-size: 13.5px; } .rr-why span { display: inline-block; background: #FEF3C7; color: #92400E; border-radius: 999px; padding: 2px 10px; font-weight: 700; font-size: 12px; }
    .rr-det { margin-top: 6px; color: #475569; font-size: 13.5px; white-space: pre-line; }
    .rr-act { display: flex; flex-direction: column; gap: 6px; min-width: 220px; }
    .rr-act textarea { border: 1.5px solid #DCE7F0; border-radius: 8px; padding: 7px 9px; font-size: 13px; font-family: inherit; resize: vertical; }
    .rr-act div { display: flex; gap: 6px; }
    .rr-act button { flex: 1; height: 34px; border: none; border-radius: 8px; color: #fff; font-weight: 700; cursor: pointer; font-size: 13px; }
    .rr-ok { background: #16A34A; } .rr-no { background: #DC2626; }
    .rr-st { display: inline-block; font-size: 12px; font-weight: 800; border-radius: 999px; padding: 3px 10px; }
    .rr-st.Pending { background: #FEF3C7; color: #92400E; } .rr-st.Approved { background: #DCFCE7; color: #166534; } .rr-st.Rejected { background: #FEE2E2; color: #991B1B; }
    .rr-empty { text-align: center; color: #94A3B8; padding: 40px 0; background: #fff; border-radius: 14px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .rr-card { grid-template-columns: 1fr; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content rr">
    <?= csrf_field() ?>
    <h2><i class="fas fa-rotate-left" style="color:#0EA5E9;"></i> Refund Requests</h2>
    <p class="sub">ইউজাররা Help Desk → Contact এর রিফান্ড ফর্ম থেকে আবেদন করে (<a href="/Home/Refund" target="_blank">রিফান্ড নীতি</a>)। Approve / Reject করলে ইউজার নোটিফিকেশন পায়। <b>টাকা ফেরত নিজে থেকে যায় না</b> — Approve করার পর যে নম্বর / মাধ্যম থেকে পেমেন্ট এসেছিল সেখানে হাতে পাঠাতে হবে।</p>
    <nav class="rr-tabs">
        <?php foreach (['Pending' => 'অপেক্ষমাণ' . ($ViewBag->PendingCount ? ' (' . (int)$ViewBag->PendingCount . ')' : ''), 'Approved' => 'অনুমোদিত', 'Rejected' => 'বাতিল', 'all' => 'সব'] as $k => $l): ?><a href="/Admin/Refunds?s=<?= $k ?>" class="<?= $S === $k ? 'on' : '' ?>"><?= e($l) ?></a><?php endforeach; ?>
    </nav>
    <?php if (!$list): ?><div class="rr-empty">কোনো আবেদন নেই।</div><?php endif; ?>
    <?php foreach ($list as $r): ?>
        <div class="rr-card" data-id="<?= (int)$r->Id ?>">
            <div>
                <h4><?= e((string)($r->FullName ?: $r->UserName ?: 'Deleted user')) ?> <small>@<?= e((string)$r->UserName) ?> · <?= $r->Role === 'Teacher' ? 'Teacher' : 'Student' ?> · <?= e(date('d M Y, h:i A', strtotime((string)$r->CreatedAt))) ?></small> <span class="rr-st <?= e($r->Status) ?>"><?= e($r->Status) ?></span></h4>
                <div class="rr-meta">
                    <span>প্যাকেজ: <b><?= e((string)($r->PackageName ?: '—')) ?></b></span>
                    <?php if ($r->Amount): ?><span>টাকা: <b>৳<?= number_format((int)$r->Amount) ?></b></span><?php endif; ?>
                    <span>Transaction ID: <span class="rr-txn"><?= e((string)$r->TxnId) ?></span></span>
                    <?php if (!empty($r->MethodName)): ?><span>মাধ্যম: <b><?= e((string)$r->MethodName) ?></b></span><?php endif; ?>
                    <?php if (!empty($r->PaidAt)): ?><span>পেমেন্ট: <?= e(date('d M Y', strtotime((string)$r->PaidAt))) ?> (<?= e((string)$r->PayStatus) ?>)</span><?php endif; ?>
                    <?php if (!empty($r->MobileNumber)): ?><span>মোবাইল: <b><?= e((string)$r->MobileNumber) ?></b></span><?php endif; ?>
                </div>
                <div class="rr-why"><span><?= e($reasons[$r->Reason][0] ?? $r->Reason) ?></span></div>
                <?php if (!is_blank($r->Details)): ?><div class="rr-det"><?= e((string)$r->Details) ?></div><?php endif; ?>
                <?php if (!is_blank($r->AdminNote)): ?><div class="rr-det"><b>অ্যাডমিনের নোট:</b> <?= e((string)$r->AdminNote) ?></div><?php endif; ?>
            </div>
            <?php if ($r->Status === 'Pending'): ?>
            <div class="rr-act">
                <textarea rows="2" maxlength="500" placeholder="নোট (Reject করলে কারণ লিখতেই হবে)"></textarea>
                <div><button type="button" class="rr-ok" data-st="Approved"><i class="fas fa-check"></i> Approve</button><button type="button" class="rr-no" data-st="Rejected"><i class="fas fa-xmark"></i> Reject</button></div>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<script>
document.addEventListener('click', function (e) {
    var b = e.target.closest('.rr-act button'); if (!b) return;
    var card = b.closest('.rr-card'), fd = new FormData();
    if (b.dataset.st === 'Approved' && !confirm('Approve করবেন? এরপর টাকা হাতে ফেরত পাঠাতে হবে।')) return;
    fd.append('id', card.dataset.id); fd.append('status', b.dataset.st); fd.append('note', card.querySelector('textarea').value);
    fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
    b.disabled = true;
    fetch('/Admin/DecideRefund', { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
        if (d.success) location.reload(); else { alert(d.message || 'হয়নি।'); b.disabled = false; }
    }).catch(function () { alert('হয়নি — আবার চেষ্টা করুন।'); b.disabled = false; });
});
</script>
