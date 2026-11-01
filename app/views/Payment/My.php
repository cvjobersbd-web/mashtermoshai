<?php
/** আমার পেমেন্ট — Subscription / পোস্ট ক্রেডিটের অবস্থা আর পেমেন্টের ইতিহাস */
$ViewData['Title'] = 'My Payments';
$list = is_array($Model) ? $Model : [];
$sub = $ViewBag->TeacherSub;
$credits = (int)$ViewBag->Credits;
$rules = $ViewBag->Rules ?? ['teacher' => false, 'student' => false];
$newId = (int)$ViewBag->NewId;
$role = (string)session_get('UserRole');
$statusBn = ['Pending' => 'যাচাই হচ্ছে', 'Approved' => 'Accepted', 'Rejected' => 'Rejected'];
$fmtDate = fn($d) => $d ? date('d M Y', strtotime((string)$d)) : '';
?>
<style>
    .mp-wrap { max-width: 900px; margin: 0 auto; padding: 22px 16px 60px; }
    .mp-h { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .mp-h h1 { font-size: clamp(22px, 3vw, 28px); font-weight: 800; color: #08224A; margin: 0; }
    .mp-h a { display: inline-flex; gap: 7px; align-items: center; padding: 10px 16px; border-radius: 12px; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; font-weight: 700; font-size: 14px; text-decoration: none; }
    .mp-ok { display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; border-radius: 14px; background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; font-size: 14.5px; line-height: 1.6; margin-bottom: 16px; }
    .mp-ok i { font-size: 20px; margin-top: 2px; }
    .mp-stat { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 22px; }
    .mp-s { background: #fff; border: 1px solid #DCECF5; border-radius: 16px; padding: 16px 18px; display: flex; gap: 14px; align-items: center; }
    .mp-s .ic { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; background: #E3F2FF; color: #1D7FE5; }
    .mp-s.on .ic { background: #DCFCE7; color: #15803D; }
    .mp-s.off .ic { background: #FEF3C7; color: #B45309; }
    .mp-s small { display: block; color: #64748B; font-size: 12.5px; }
    .mp-s b { display: block; color: #08224A; font-size: 16.5px; font-weight: 800; }
    .mp-s span { font-size: 12.5px; color: #475569; }
    .mp-list { display: flex; flex-direction: column; gap: 12px; }
    .mp-item { background: #fff; border: 1px solid #DCECF5; border-left: 5px solid #F59E0B; border-radius: 16px; padding: 16px 18px; }
    .mp-item.Approved { border-left-color: #22C55E; }
    .mp-item.Rejected { border-left-color: #EF4444; }
    .mp-item.new { box-shadow: 0 0 0 3px rgba(29,127,229,.2); }
    .mp-top { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .mp-top .nm { font-size: 16px; font-weight: 800; color: #08224A; }
    .mp-top .amt { font-weight: 800; color: #1D7FE5; }
    .mp-pill { margin-left: auto; font-size: 11.5px; font-weight: 800; padding: 4px 11px; border-radius: 20px; background: #FEF3C7; color: #92400E; }
    .mp-pill.Approved { background: #DCFCE7; color: #166534; }
    .mp-pill.Rejected { background: #FEE2E2; color: #991B1B; }
    .mp-meta { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 8px; font-size: 13px; color: #64748B; }
    .mp-meta b { color: #334155; font-weight: 600; }
    .mp-meta a { color: #1D7FE5; font-weight: 600; text-decoration: none; }
    .mp-note { margin-top: 10px; padding: 9px 12px; border-radius: 10px; background: #FEF2F2; color: #991B1B; font-size: 13px; }
    .mp-note.ok { background: #F0FDF4; color: #166534; }
    .mp-empty { text-align: center; padding: 46px 16px; border: 2px dashed #DCECF5; border-radius: 18px; color: #64748B; background: #fff; }
    @media (max-width: 560px) { .mp-pill { margin-left: 0; } }
</style>

<div class="mp-wrap">
    <div class="mp-h">
        <h1><i class="fas fa-receipt" style="color:#1D7FE5;"></i> আমার পেমেন্ট</h1>
        <a href="/Package"><i class="fas fa-tags"></i> প্যাকেজ দেখুন</a>
    </div>

    <?php if ($newId): ?>
        <div class="mp-ok"><i class="fas fa-circle-check"></i><div><b>পেমেন্টের তথ্য জমা হয়েছে!</b><br>অ্যাডমিন যাচাই করে Accept করলে আপনার প্যাকেজ চালু হবে এবং নোটিফিকেশন পাবেন।</div></div>
    <?php endif; ?>

    <div class="mp-stat">
        <?php if ($role === 'Teacher'): ?>
            <div class="mp-s <?= $sub ? 'on' : 'off' ?>"><span class="ic"><i class="fas fa-crown"></i></span><div>
                <small>শিক্ষক Subscription</small>
                <?php if ($sub): ?><b>চালু আছে</b><span><?= e($fmtDate($sub->EndsAt)) ?> পর্যন্ত</span>
                <?php else: ?><b>চালু নেই</b><span><?= $rules['teacher'] ? 'প্রোফাইল সক্রিয় রাখতে Subscription নিন' : 'Subscription নিয়ে প্রোফাইল সক্রিয় রাখুন' ?></span><?php endif; ?>
            </div></div>
        <?php endif; ?>
        <?php if ($role !== 'Teacher' || $credits !== 0): ?>
            <div class="mp-s <?= $credits !== 0 ? 'on' : 'off' ?>"><span class="ic"><i class="fas fa-pen-to-square"></i></span><div>
                <small>Tuition Post ক্রেডিট</small>
                <b><?= $credits === -1 ? 'আনলিমিটেড' : e(bn_num($credits)) . 'টি পোস্ট বাকি' ?></b>
                <span><?= $credits === 0 ? 'Teacher Needed পোস্ট করতে প্যাকেজ নিন' : 'Tuition Circular-এ পোস্ট করতে পারবেন' ?></span>
            </div></div>
        <?php endif; ?>
    </div>

    <?php if (!$list): ?>
        <div class="mp-empty"><i class="fas fa-receipt fa-2x"></i><p style="margin:10px 0 0;">এখনো কোনো পেমেন্ট করেননি।</p></div>
    <?php else: ?>
        <div class="mp-list">
            <?php foreach ($list as $p): ?>
                <div class="mp-item <?= e($p->Status) ?><?= $p->Id === $newId ? ' new' : '' ?>" id="pay-<?= (int)$p->Id ?>">
                    <div class="mp-top">
                        <span class="nm"><?= e($p->PackageName) ?></span>
                        <span class="amt"><?= e(taka($p->Amount)) ?></span>
                        <span class="mp-pill <?= e($p->Status) ?>"><?= e($statusBn[$p->Status] ?? $p->Status) ?></span>
                    </div>
                    <div class="mp-meta">
                        <span><i class="fas fa-wallet"></i> <b><?= e($p->MethodName) ?></b></span>
                        <?php if ($p->TxnId): ?><span>TxnID: <b><?= e($p->TxnId) ?></b></span><?php endif; ?>
                        <span><i class="far fa-calendar"></i> <?= e($fmtDate($p->PayDate)) ?></span>
                        <a href="<?= e($p->ReceiptUrl) ?>" target="_blank" rel="noopener"><i class="fas fa-paperclip"></i> রিসিট</a>
                    </div>
                    <?php if ($p->Status === 'Rejected'): ?>
                        <div class="mp-note"><i class="fas fa-circle-xmark"></i> <?= e($p->AdminNote ?: 'পেমেন্ট যাচাই করা যায়নি।') ?> — সঠিক তথ্য দিয়ে আবার জমা দিতে পারেন।</div>
                    <?php elseif ($p->Status === 'Approved' && $p->AdminNote): ?>
                        <div class="mp-note ok"><i class="fas fa-circle-check"></i> <?= e($p->AdminNote) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
