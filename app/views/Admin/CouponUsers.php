<?php
/** Admin → Coupons → একটা কোড দিয়ে কে কে প্যাকেজ কিনেছে */
$c = $Model; $rows = $ViewBag->Users ?? [];
$ViewData['Title'] = 'Coupon — ' . $c->Code;
$audBn = ['Teacher' => 'টিচার', 'Student' => 'শিক্ষার্থী'];
$ok = 0; $pend = 0; $rej = 0; $sum = 0;
foreach ($rows as $r) { if ($r->Status === 'Approved') { $ok++; $sum += (int)$r->Amount; } elseif ($r->Status === 'Pending') $pend++; else $rej++; }
$img = (string)($c->ImageUrl ?? '');
$stLbl = ['Approved' => ['ok', 'কিনেছে'], 'Pending' => ['w', 'যাচাইয়ের অপেক্ষায়'], 'Rejected' => ['no', 'বাতিল']];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 90px; min-height: calc(100vh - 80px); }
    .cu a.back { color: #64748B; text-decoration: none; font-weight: 700; font-size: 13.5px; }
    .cu-head { display: grid; grid-template-columns: minmax(0, 420px) 1fr; gap: 20px; background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin: 10px 0 18px; align-items: center; }
    .cu-head .im { border-radius: 12px; overflow: hidden; aspect-ratio: 1600/711; background: #EEF2F7; }
    .cu-head .im img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .cu-code { font-family: ui-monospace, monospace; font-weight: 800; font-size: 26px; letter-spacing: 1.5px; background: #F5F3FF; color: #5B21B6; border-radius: 10px; padding: 6px 14px; display: inline-block; }
    .cu-meta { color: #475569; font-size: 14px; margin: 10px 0 14px; }
    .cu-stats { display: flex; gap: 10px; flex-wrap: wrap; }
    .cu-st { background: #F8FAFC; border-radius: 12px; padding: 10px 14px; border-left: 4px solid var(--c); min-width: 120px; }
    .cu-st small { display: block; color: #64748B; font-weight: 700; font-size: 12px; } .cu-st b { font-size: 20px; color: #0F172A; }
    .cu-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; }
    .cu-card h3 { margin: 0 0 12px; font-size: 17px; color: #0F172A; font-weight: 800; }
    .cu-tw { overflow-x: auto; }
    .cu-t { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 760px; }
    .cu-t th { text-align: left; font-size: 11.5px; color: #64748B; padding: 9px 8px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .cu-t td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
    .cu-t td a { color: #0F172A; font-weight: 700; text-decoration: none; } .cu-t td a:hover { text-decoration: underline; }
    .cu-u { display: flex; align-items: center; gap: 9px; } .cu-u img, .cu-u .av { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background: #E0F2FE; color: #075985; display: flex; align-items: center; justify-content: center; font-weight: 800; flex: none; }
    .cu-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .cu-pill.ok { background: #DCFCE7; color: #166534; } .cu-pill.w { background: #FEF3C7; color: #92400E; } .cu-pill.no { background: #FEE2E2; color: #991B1B; }
    .cu-empty { color: #94A3B8; text-align: center; padding: 26px 8px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 80px; } .cu-head { grid-template-columns: 1fr; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content cu">
    <a class="back" href="/Admin/Coupons"><i class="fas fa-arrow-left"></i> সব কুপন</a>

    <div class="cu-head">
        <div class="im"><?php if ($img !== ''): ?><img src="<?= e($img) ?>" alt="<?= e($c->Code) ?>" /><?php endif; ?></div>
        <div>
            <span class="cu-code"><?= e($c->Code) ?></span>
            <div class="cu-meta"><?= e($audBn[$c->Audience] ?? $c->Audience) ?> · <?= (int)$c->DurationMonths ? e(bn_num((int)$c->DurationMonths)) . ' মাসের প্যাকেজ' : 'সব নরমাল প্যাকেজ' ?> · অফারের দাম <b style="color:#15803D;"><?= e(taka((int)$c->Price)) ?></b>
                · <?= (int)$c->IsActive ? '<span class="cu-pill ok">চালু</span>' : '<span class="cu-pill no">বন্ধ</span>' ?></div>
            <div class="cu-stats">
                <div class="cu-st" style="--c:#16A34A;"><small>কিনেছে</small><b><?= $ok ?> জন</b></div>
                <div class="cu-st" style="--c:#F59E0B;"><small>যাচাইয়ের অপেক্ষায়</small><b><?= $pend ?> জন</b></div>
                <div class="cu-st" style="--c:#0EA5E9;"><small>মোট টাকা</small><b><?= e(taka($sum)) ?></b></div>
                <?php if ($rej): ?><div class="cu-st" style="--c:#DC2626;"><small>বাতিল</small><b><?= $rej ?></b></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="cu-card">
        <h3><i class="fas fa-users" style="color:#7C3AED;"></i> এই কোড দিয়ে যারা প্যাকেজ নিয়েছে</h3>
        <?php if (!$rows): ?><div class="cu-empty">এখনো কেউ এই কোড ব্যবহার করেনি।</div><?php else: ?>
        <div class="cu-tw"><table class="cu-t">
            <thead><tr><th>#</th><th>নাম</th><th>মোবাইল</th><th>প্যাকেজ</th><th>দিয়েছে</th><th>পেমেন্ট</th><th>তারিখ</th><th>অবস্থা</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): $nm = $r->FullName ?: ($r->UserName ?: 'মুছে ফেলা ইউজার'); [$pc, $pl] = $stLbl[$r->Status] ?? ['w', $r->Status]; ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><div class="cu-u"><?= $r->ProfileImageLink ? '<img src="' . e($r->ProfileImageLink) . '" alt="" loading="lazy" />' : '<span class="av">' . e(mb_substr((string)$nm, 0, 1)) . '</span>' ?>
                        <div><?php if ($r->UserName): ?><a href="/Admin/UserDetails/<?= (int)$r->UserId ?>"><?= e($nm) ?></a><br><small style="color:#94A3B8;">@<?= e($r->UserName) ?></small><?php else: ?><?= e($nm) ?><?php endif; ?></div></div></td>
                    <td><?= e((string)($r->MobileNumber ?? '')) ?: '—' ?></td>
                    <td><?= e((string)$r->PackageName) ?></td>
                    <td><b><?= e(taka((int)$r->Amount)) ?></b></td>
                    <td><?= e((string)$r->MethodName) ?><?= $r->TxnId ? '<br><small style="color:#64748B;">' . e($r->TxnId) . '</small>' : '' ?></td>
                    <td style="white-space:nowrap;"><?= e(fmt_date($r->CreatedAt, 'd M Y, h:i A')) ?></td>
                    <td><span class="cu-pill <?= $pc ?>"><?= e($pl) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php if ($pend): ?><div style="margin-top:12px;font-size:13.5px;"><a href="/Admin/Payments" style="font-weight:800;color:#92400E;">অপেক্ষায় থাকা পেমেন্ট যাচাই করুন →</a></div><?php endif; ?>
        <?php endif; ?>
    </div>
</div>
