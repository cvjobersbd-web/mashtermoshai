<?php
/**
 * Admin → Referrals → বিস্তারিত: একজন ইউজারের রেফারেল লিংক, কোড, কোথায় শেয়ার করেছে,
 * কোথা থেকে কত জন এসেছে / সাইনআপ / প্যাকেজ কিনেছে, কাকে কাকে এনেছে, টাকা তোলা আর প্যাকেজে ছাড়ের ইতিহাস।
 */
$u = $Model; $V = $ViewBag;
$name = (string)($u->FullName ?: $u->UserName);
$ViewData['Title'] = 'Referral — ' . $name;
$sum = $V->Summary; $tot = $V->Totals; $src = $V->Sources;
$link = site_public_url() . '/?ref=' . rawurlencode((string)$V->Code);
$stCls = ['Pending' => 'p', 'Paid' => 'ok', 'Rejected' => 'no'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .ru h2 { color: #08224A; margin: 0; font-size: 23px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ru .back { font-size: 13.5px; font-weight: 700; color: #0369A1; text-decoration: none; display: inline-block; margin-bottom: 10px; }
    .ru-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .ru-card h3 { margin: 0 0 12px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; align-items: center; gap: 8px; }
    .ru-id { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 12px; }
    .ru-box { background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 12px; padding: 10px 12px; }
    .ru-box small { display: block; font-size: 11.5px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 4px; }
    .ru-box b { font-size: 14px; color: #0F172A; word-break: break-all; }
    .ru-box.code b { font-family: ui-monospace, monospace; font-size: 20px; letter-spacing: 2px; color: #B45309; }
    .ru-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .ru-stat { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); }
    .ru-stat small { color: #64748B; font-weight: 700; font-size: 12px; display: block; }
    .ru-stat b { display: block; font-size: 22px; color: #08224A; }
    .ru-stat span { font-size: 12px; color: #94A3B8; }
    .ru-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ru-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 8px 6px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .ru-tbl td { padding: 9px 6px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: top; }
    .ru-tbl tfoot td { font-weight: 800; color: #0F172A; border-top: 2px solid #E5ECF3; }
    .ru-tw { overflow-x: auto; }
    .ru-src { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0F172A; }
    .ru-src i { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; }
    .ru-bar { height: 8px; background: #F1F5F9; border-radius: 99px; overflow: hidden; min-width: 80px; }
    .ru-bar i { display: block; height: 100%; background: #16A34A; border-radius: 99px; }
    .ru-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; background: #F1F5F9; color: #334155; }
    .ru-pill.p { background: #FEF3C7; color: #92400E; } .ru-pill.ok { background: #DCFCE7; color: #166534; } .ru-pill.no { background: #FEE2E2; color: #991B1B; }
    .ru-empty { text-align: center; color: #94A3B8; padding: 20px 0; }
    .ru-note { font-size: 12.5px; color: #64748B; margin: 10px 0 0; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .ru-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ru-id { grid-template-columns: 1fr; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content ru">
    <a class="back" href="/Admin/Referrals"><i class="fas fa-arrow-left"></i> সব রেফারেল</a>
    <h2><i class="fas fa-user-tie" style="color:#7C3AED;"></i> <?= e($name) ?>
        <small style="font-size:13px;font-weight:600;color:#64748B;"><?= e(role_label((string)$u->Role, (int)$u->Id)) ?> · @<?= e((string)$u->UserName) ?></small>
        <a href="/Admin/UserDetails/<?= (int)$u->Id ?>" style="font-size:13px;font-weight:700;color:#0369A1;"><i class="fas fa-id-card"></i> ইউজারের তথ্য</a>
    </h2>
    <?php if (!empty($V->NoBonus)): ?><div style="margin-top:10px;background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;border-radius:12px;padding:10px 14px;font-weight:700;font-size:13.5px;"><i class="fas fa-user-slash"></i> এই ইউজার "বোনাস ছাড়া রেফারার" তালিকায় আছে — এর রেফারেলে কেউ প্যাকেজ কিনলে ৳ বোনাস যায় না, শুধু গোনা হয়। <a href="/Admin/Referrals#noBonus">বদলান</a></div><?php endif; ?>

    <div class="ru-card" style="margin-top:14px;">
        <div class="ru-id">
            <div class="ru-box"><small><i class="fas fa-link"></i> রেফারেল লিংক</small><b><?= e($link) ?></b></div>
            <div class="ru-box code"><small><i class="fas fa-ticket"></i> রেফারেল কোড</small><b><?= e((string)$V->Code) ?></b></div>
        </div>
        <p class="ru-note"><i class="fas fa-circle-info"></i> প্রতিটা জায়গার লিংকের শেষে আলাদা চিহ্ন থাকে (যেমন <code>&amp;src=wa</code> = WhatsApp), তাই কোথা থেকে কত জন এসেছে আলাদা করে গোনা যায়।<?= $V->LastShare ? ' শেষ শেয়ার: <b>' . e(fmt_date($V->LastShare, 'd M Y, h:i A')) . '</b>' : '' ?></p>
    </div>

    <div class="ru-stats">
        <div class="ru-stat"><small>শেয়ার করেছে</small><b><?= (int)$tot['share'] ?> বার</b><span>শেয়ার / কপি বাটনে চাপ</span></div>
        <div class="ru-stat"><small>লিংক খুলে এসেছে</small><b><?= (int)$tot['click'] ?> জন</b><span>লগইন ছাড়া ভিজিটর</span></div>
        <div class="ru-stat"><small>সাইনআপ করেছে</small><b><?= (int)$tot['signup'] ?> জন</b><span>বাতিল করাগুলো বাদে</span></div>
        <div class="ru-stat"><small>প্যাকেজ কিনেছে</small><b style="color:#16A34A;"><?= (int)$tot['buy'] ?> জন</b><span><?= $tot['signup'] ? round($tot['buy'] / $tot['signup'] * 100) . '% সাইনআপ কিনেছে' : '—' ?></span></div>
        <div class="ru-stat"><small>মোট আয়</small><b>৳<?= number_format($sum['earned']) ?></b><span>৳<?= (int)$V->Settings['bonus'] ?> করে</span></div>
        <div class="ru-stat"><small>ব্যালেন্স</small><b style="color:#0369A1;">৳<?= number_format($sum['balance']) ?></b><span>সর্বনিম্ন উইথড্র ৳<?= (int)$V->Settings['min'] ?></span></div>
        <div class="ru-stat"><small>তুলেছে (Withdraw)</small><b>৳<?= number_format($sum['withdrawn']) ?></b><span><?= $sum['pending'] ? 'অপেক্ষায় ৳' . number_format($sum['pending']) : 'অপেক্ষায় কিছু নেই' ?></span></div>
        <div class="ru-stat"><small>প্যাকেজে ছাড় নিয়েছে</small><b>৳<?= number_format($sum['discount']) ?></b><span><?= $sum['discountHold'] ? 'যাচাই চলছে ৳' . number_format($sum['discountHold']) : 'নিজের প্যাকেজ কেনায়' ?></span></div>
    </div>

    <div class="ru-card">
        <h3><i class="fas fa-share-nodes" style="color:#0EA5E9;"></i> কোথায় শেয়ার করেছে আর কোথা থেকে কত জন এসেছে</h3>
        <?php if (!$src): ?><div class="ru-empty">এখনো কোথাও শেয়ার করেনি, কেউ আসেনি।</div><?php else: $maxS = max(1, max(array_column($src, 'signup'))); ?>
        <div class="ru-tw"><table class="ru-tbl">
            <tr><th>জায়গা</th><th>শেয়ার (বার)</th><th>লিংকে ক্লিক</th><th>সাইনআপ</th><th>প্যাকেজ কিনেছে</th><th></th></tr>
            <?php foreach ($src as $k => $x): [$l, $ic, $c] = Db::REF_SOURCES[$k]; ?>
                <tr>
                    <td><span class="ru-src"><i class="<?= e($ic) ?>" style="background:<?= e($c) ?>;"></i> <?= e($l) ?></span></td>
                    <td><?= $k === 'link' ? '—' : (int)$x['share'] ?></td><td><?= $k === 'code' ? '—' : (int)$x['click'] ?></td>
                    <td><b><?= (int)$x['signup'] ?></b></td><td><b style="color:#16A34A;"><?= (int)$x['buy'] ?></b></td>
                    <td><div class="ru-bar"><i style="width:<?= round($x['signup'] / $maxS * 100) ?>%;"></i></div></td>
                </tr>
            <?php endforeach; ?>
            <tfoot><tr><td>মোট</td><td><?= (int)$tot['share'] ?></td><td><?= (int)$tot['click'] ?></td><td><?= (int)$tot['signup'] ?></td><td><?= (int)$tot['buy'] ?></td><td></td></tr></tfoot>
        </table></div>
        <p class="ru-note">"রেফারেল কোড" সারিতে: শেয়ার = কোড কপি করেছে, সাইনআপ = সাইনআপ বা প্যাকেজ কেনার সময় কোড দিয়ে এসেছে। "লিংক (জায়গা জানা নেই)" = আগের পুরোনো লিংক, যেখানে জায়গার চিহ্ন ছিল না।</p>
        <?php endif; ?>
    </div>

    <div class="ru-card">
        <h3><i class="fas fa-users" style="color:#7C3AED;"></i> যাদের এনেছে <span class="ru-pill"><?= count($V->People) ?> জন</span></h3>
        <?php if (!$V->People): ?><div class="ru-empty">এখনো কেউ আসেনি।</div><?php else: ?>
        <div class="ru-tw"><table class="ru-tbl">
            <tr><th>ইউজার</th><th>কোথা থেকে</th><th>কবে এসেছে</th><th>অবস্থা</th><th>কেনা</th><th>IP</th></tr>
            <?php foreach ($V->People as $p): $k = (string)($p->Src ?? ''); if ($k === '' || !isset(Db::REF_SOURCES[$k])) $k = ($p->Via ?? '') === 'code' ? 'code' : 'link'; [$l, $ic, $c] = Db::REF_SOURCES[$k]; ?>
                <tr>
                    <td><?php if ($p->FullName || $p->UserName): ?><a href="/Admin/UserDetails/<?= (int)$p->ReferredUserId ?>"><b><?= e($p->FullName ?: $p->UserName) ?></b></a> <small style="color:#64748B;">(<?= e(role_label((string)$p->Role, (int)$p->ReferredUserId)) ?>)</small><br><small style="color:#94A3B8;"><?= e((string)$p->Email) ?></small><?php else: ?><span style="color:#94A3B8;">Deleted</span><?php endif; ?></td>
                    <td><span class="ru-src" style="font-weight:600;font-size:12.5px;"><i class="<?= e($ic) ?>" style="background:<?= e($c) ?>;width:22px;height:22px;font-size:11px;"></i> <?= e($l) ?></span><?= ($p->Via ?? '') === 'code' ? '<br><small style="color:#B45309;">প্যাকেজ কেনার সময় কোড দিয়েছে</small>' : (($p->Via ?? '') === 'signupcode' ? '<br><small style="color:#B45309;">সাইনআপে কোড দিয়েছে</small>' : '') ?></td>
                    <td><?= e(fmt_date($p->CreatedAt, 'd M Y, h:i A')) ?></td>
                    <td><?= $p->Status === 'Credited' ? '<span class="ru-pill ok">প্যাকেজ কিনেছে · ৳' . (int)$p->Bonus . '</span>' : ($p->Status === 'Waiting' ? '<span class="ru-pill">এখনো কেনেনি</span>' : '<span class="ru-pill no">বাতিল</span>') ?></td>
                    <td><?= (int)$p->Orders ? (int)$p->Orders . ' বার · ৳' . number_format((int)$p->Spent) : '—' ?></td>
                    <td><small><?= e((string)$p->Ip) ?></small></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>

    <div class="ru-card">
        <h3><i class="fas fa-clock-rotate-left" style="color:#16A34A;"></i> টাকা তোলা আর প্যাকেজে ছাড়ের ইতিহাস</h3>
        <?php if (!$V->Withdrawals): ?><div class="ru-empty">এখনো কিছু নেই।</div><?php else: ?>
        <div class="ru-tw"><table class="ru-tbl">
            <tr><th>তারিখ</th><th>ধরন</th><th>পরিমাণ</th><th>অবস্থা</th><th>নোট</th></tr>
            <?php foreach ($V->Withdrawals as $w): $isD = $w->Method === 'Discount'; ?>
                <tr>
                    <td><?= e(fmt_date($w->CreatedAt, 'd M Y, h:i A')) ?></td>
                    <td><?= $isD ? '<i class="fas fa-gift" style="color:#F59E0B;"></i> প্যাকেজে ছাড় <small style="color:#94A3B8;">(' . e((string)$w->AccountNumber) . ')</small>' : '<i class="fas fa-money-bill-transfer" style="color:#16A34A;"></i> ' . e($w->Method) . ' <small style="color:#94A3B8;">' . e((string)$w->AccountNumber) . '</small>' ?></td>
                    <td><b>৳<?= number_format((int)$w->Amount) ?></b></td>
                    <td><span class="ru-pill <?= $stCls[$w->Status] ?? '' ?>"><?= $w->Status === 'Pending' ? ($isD ? 'পেমেন্ট যাচাই হচ্ছে' : 'অপেক্ষায়') : ($w->Status === 'Paid' ? ($isD ? 'ব্যবহার হয়েছে' : 'পাঠানো হয়েছে') : ($isD ? 'ফেরত' : 'বাতিল')) ?></span></td>
                    <td><small><?= e((string)($w->TxnId ? 'TxnID: ' . $w->TxnId . ' ' : '') . (string)$w->AdminNote) ?></small></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>
</div>
