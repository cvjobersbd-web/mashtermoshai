<?php
/** Admin → Subscriptions: কে কোন প্যাকেজ নিয়েছে, কত টাকা, মোট কত কিনেছে, মেয়াদ কবে শেষ + সতর্ক করার দিন */
$ViewData['Title'] = 'Subscriptions';
$list = is_array($Model) ? $Model : [];
$st = $ViewBag->Stats; $aud = (string)$ViewBag->Aud; $fs = (string)$ViewBag->St; $days = (int)$ViewBag->AlertDays;
$q = fn(array $p) => '/Admin/Subscriptions?' . http_build_query(array_merge(['aud' => $aud, 'st' => $fs], $p));
$stLbl = ['active' => ['চালু', 'ok'], 'expiring' => ['শেষ হচ্ছে', 'warn'], 'expired' => ['মেয়াদ শেষ', 'no'], 'credit' => ['পোস্ট ক্রেডিট', 'ok']];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .sb h2 { color: #08224A; margin: 0; font-size: 24px; }
    .sb .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; }
    .sb-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .sb-st { background: #fff; border-radius: 16px; padding: 16px 18px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); }
    .sb-st h4 { margin: 0 0 10px; font-size: 15px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .sb-st h4 i { color: var(--c); }
    .sb-st .g { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .sb-st small { display: block; color: #64748B; font-size: 12px; font-weight: 700; }
    .sb-st b { font-size: 20px; color: #0F172A; }
    .sb-st b.rev { color: #15803D; }
    .sb-st b.warn { color: #B45309; }
    .sb-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .sb-card h3 { margin: 0 0 6px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .sb-alert { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-top: 10px; }
    .sb-alert label { font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px; }
    .sb-alert .in { display: flex; align-items: center; border: 1.5px solid #DCECF5; border-radius: 10px; overflow: hidden; }
    .sb-alert input { width: 90px; height: 42px; border: none; padding: 0 12px; font-size: 17px; font-weight: 800; font-family: inherit; outline: none; }
    .sb-alert .in span { padding: 0 12px; color: #64748B; font-size: 13.5px; font-weight: 700; background: #F8FAFC; height: 42px; display: flex; align-items: center; border-left: 1px solid #E2E8F0; }
    .sb-btn { height: 42px; padding: 0 18px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; display: inline-flex; align-items: center; gap: 7px; }
    #sbMsg { font-size: 13.5px; font-weight: 600; } #sbMsg.ok { color: #15803D; } #sbMsg.err { color: #B91C1C; }
    .sb-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
    .sb-tabs a { padding: 7px 14px; border-radius: 999px; background: #F1F5F9; color: #334155; font-weight: 700; font-size: 13px; text-decoration: none; }
    .sb-tabs a.on { background: #08224A; color: #fff; }
    .sb-tw { overflow-x: auto; }
    .sb-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 860px; }
    .sb-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 8px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .sb-tbl td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
    .sb-who { display: flex; align-items: center; gap: 10px; }
    .sb-av { width: 36px; height: 36px; border-radius: 50%; background: #E0F2FE; color: #0369A1; font-weight: 800; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; font-size: 13px; }
    .sb-av img { width: 100%; height: 100%; object-fit: cover; }
    .sb-who a { color: #0F172A; font-weight: 700; text-decoration: none; } .sb-who small { display: block; color: #94A3B8; font-size: 12px; }
    .sb-aud { font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; }
    .sb-aud.t { background: #E0F2FE; color: #0369A1; } .sb-aud.s { background: #F3E8FF; color: #7E22CE; }
    .sb-pill { font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .sb-pill.ok { background: #DCFCE7; color: #166534; } .sb-pill.warn { background: #FEF3C7; color: #92400E; } .sb-pill.no { background: #FEE2E2; color: #991B1B; }
    .sb-money { font-weight: 800; color: #0F172A; white-space: nowrap; }
    .sb-empty { text-align: center; color: #94A3B8; padding: 26px 0; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .sb-stats { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 520px) { .sb-st .g { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content sb">
    <?= csrf_field() ?>
    <h2><i class="fas fa-id-card-clip" style="color:#00B5E0;"></i> Subscriptions</h2>
    <p class="sub">কোন টিচার / শিক্ষার্থী কোন প্যাকেজ নিয়েছেন, কত টাকার, মোট কত টাকার প্যাকেজ কিনেছেন আর মেয়াদ কবে শেষ — সব এক জায়গায়। টাকার হিসাব শুধু Approve করা পেমেন্ট থেকে।</p>

    <div class="sb-stats">
        <?php foreach (['Teacher' => ['শিক্ষক', 'fa-chalkboard-user', '#0EA5E9'], 'Student' => ['শিক্ষার্থী', 'fa-user-graduate', '#A855F7']] as $a => [$lbl, $ic, $c]): $x = $st[$a]; ?>
            <div class="sb-st" style="--c:<?= $c ?>;">
                <h4><i class="fas <?= $ic ?>"></i> <?= $lbl ?></h4>
                <div class="g">
                    <div><small>প্যাকেজ নিয়েছেন</small><b><?= number_format($x['users']) ?> জন</b></div>
                    <div><small>এখন চালু</small><b><?= number_format($x['active']) ?></b></div>
                    <div><small>শিগগির শেষ হবে</small><b class="warn"><?= number_format($x['expiring']) ?></b></div>
                    <div><small>মোট আয়</small><b class="rev">৳<?= number_format($x['revenue']) ?></b></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="sb-card">
        <h3><i class="fas fa-bell" style="color:#F59E0B;"></i> মেয়াদ শেষের সতর্কতা</h3>
        <p style="font-size:13.5px;color:#64748B;margin:0;">প্যাকেজের মেয়াদ শেষ হওয়ার এত দিন আগে থেকে টিচার আর শিক্ষার্থী নোটিফিকেশন পাবেন আর সাইটের নিচে রিনিউ করার বার দেখবেন। শেষ দিনের আগেও আরেকবার মনে করিয়ে দেওয়া হয়।</p>
        <form class="sb-alert" id="sbForm" novalidate>
            <div><label for="sbDays">কত দিন আগে থেকে সতর্ক করবে</label>
                <div class="in"><input type="number" id="sbDays" name="days" min="1" max="30" value="<?= $days ?>" /><span>দিন আগে</span></div></div>
            <button type="submit" class="sb-btn"><i class="fas fa-save"></i> Save</button>
            <span id="sbMsg" role="status"></span>
        </form>
    </div>

    <div class="sb-card">
        <div class="sb-tabs">
            <?php foreach (['all' => 'সবাই', 'Teacher' => 'শিক্ষক', 'Student' => 'শিক্ষার্থী'] as $k => $l): ?><a href="<?= e($q(['aud' => $k])) ?>" class="<?= $aud === $k ? 'on' : '' ?>"><?= $l ?></a><?php endforeach; ?>
        </div>
        <div class="sb-tabs">
            <?php foreach (['all' => 'সব অবস্থা', 'active' => 'চালু', 'expiring' => 'শিগগির শেষ হবে', 'expired' => 'মেয়াদ শেষ'] as $k => $l): ?><a href="<?= e($q(['st' => $k])) ?>" class="<?= $fs === $k ? 'on' : '' ?>" style="font-size:12.5px;"><?= $l ?></a><?php endforeach; ?>
        </div>
        <?php if (!$list): ?><div class="sb-empty">এই তালিকায় কেউ নেই।</div><?php else: ?>
        <div class="sb-tw"><table class="sb-tbl">
            <tr><th>ইউজার</th><th>ধরন</th><th>বর্তমান / শেষ প্যাকেজ</th><th>প্যাকেজের দাম</th><th>কেনার তারিখ</th><th>মেয়াদ শেষ</th><th>অবস্থা</th><th>মোট কেনা</th></tr>
            <?php foreach ($list as $o): [$sl, $sc] = $stLbl[$o->State]; ?>
                <tr>
                    <td><div class="sb-who"><span class="sb-av"><?php if ($o->Photo !== ''): ?><img src="<?= e($o->Photo) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?><?= e(initials($o->Name, 'U')) ?></span>
                        <div><a href="/Profile/Show/<?= (int)$o->UserId ?>" target="_blank" rel="noopener"><?= e($o->Name) ?></a><small>@<?= e($o->UserName) ?><?= $o->Role === 'User' && $o->Audience === 'Teacher' ? ' · এখন সাধারণ ইউজার' : '' ?></small></div></div></td>
                    <td><span class="sb-aud <?= $o->Audience === 'Teacher' ? 't' : 's' ?>"><?= $o->Audience === 'Teacher' ? 'শিক্ষক' : 'শিক্ষার্থী' ?></span></td>
                    <td><b><?= e($o->Package) ?></b></td>
                    <td class="sb-money">৳<?= number_format((int)$o->Price) ?></td>
                    <td><?= e(fmt_date($o->Bought, 'd M Y')) ?></td>
                    <td><?= $o->EndsAt ? e(fmt_date($o->EndsAt, 'd M Y')) . ($o->DaysLeft > 0 ? '<br><small style="color:#64748B;">আর ' . (int)$o->DaysLeft . ' দিন</small>' : '') : '—' ?></td>
                    <td><span class="sb-pill <?= $sc ?>"><?= $sl ?></span></td>
                    <td class="sb-money">৳<?= number_format((int)$o->Total) ?><br><small style="color:#64748B;font-weight:600;"><?= (int)$o->Count ?> বার</small></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    var f = document.getElementById('sbForm'), m = document.getElementById('sbMsg');
    f.addEventListener('submit', async function (e) {
        e.preventDefault(); m.className = ''; m.textContent = 'সেভ হচ্ছে...';
        var fd = new FormData(); fd.append('days', f.days.value);
        fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        try {
            var d = await (await fetch('/Admin/SaveSubAlertDays', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            m.className = d.success ? 'ok' : 'err'; m.textContent = (d.success ? '✓ ' : '') + (d.message || '');
        } catch (x) { m.className = 'err'; m.textContent = 'সেভ করা যায়নি।'; }
    });
})();
</script>
