<?php
/** Admin → Coupons: স্পেশাল অফারের কুপন কোড — কোড দিলে নির্দিষ্ট প্যাকেজ কম দামে */
$ViewData['Title'] = 'Coupons';
$list = $Model ?: [];
$durs = [];
foreach ($ViewBag->Packages as $p) if (empty($p->IsLive) && $p->Audience !== 'Course') $durs[$p->Audience][(int)$p->DurationMonths] = $p->Name . ' — ' . taka($p->Price);
$audBn = ['Teacher' => 'টিচার', 'Student' => 'শিক্ষার্থী'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .cp h2 { color: #08224A; margin: 0; font-size: 24px; }
    .cp .sub { color: #64748B; margin: 4px 0 16px; font-size: 14px; }
    .cp-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 16px; }
    .cp-card h3 { margin: 0 0 12px; font-size: 16px; font-weight: 800; color: #08224A; }
    .cp-f { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; align-items: end; }
    .cp-f label { display: block; font-size: 12.5px; font-weight: 800; color: #475569; margin-bottom: 5px; }
    .cp-f input, .cp-f select { width: 100%; height: 42px; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 0 10px; font-family: inherit; font-size: 14px; background: #fff; }
    .cp-f .wide { grid-column: span 2; }
    .cp-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; font-weight: 800; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px; }
    .cp-btn.go { background: #7C3AED; color: #fff; } .cp-btn.l { background: #F1F5F9; color: #334155; } .cp-btn.r { background: #FEE2E2; color: #B91C1C; }
    .cp-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .cp-tbl th { text-align: left; font-size: 11.5px; color: #64748B; padding: 8px; border-bottom: 1px solid #E2E8F0; white-space: nowrap; }
    .cp-tbl td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
    .cp-code { font-family: ui-monospace, monospace; font-weight: 800; font-size: 15px; letter-spacing: 1px; background: #F5F3FF; color: #5B21B6; border-radius: 8px; padding: 4px 10px; }
    .cp-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; }
    .cp-pill.on { background: #DCFCE7; color: #166534; } .cp-pill.off { background: #F1F5F9; color: #64748B; } .cp-pill.exp { background: #FEE2E2; color: #991B1B; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .cp-f .wide { grid-column: auto; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content cp">
    <h2><i class="fas fa-ticket" style="color:#7C3AED;"></i> Coupons</h2>
    <p class="sub">স্পেশাল অফারের কুপন কোড — ইউজার পেমেন্ট পেজে কোডটা দিলে বাছাই করা প্যাকেজ অফারের দামে পাবে। প্যাকেজ পেজে "স্পেশাল অফার · কুপন কোড দিলে মাত্র ৳..." দেখায় (কোডটা দেখায় না — আপনি যাদের দেবেন তারাই পাবে)।</p>
    <?= csrf_field() ?>

    <div class="cp-card">
        <h3 id="cpFormT"><i class="fas fa-plus"></i> নতুন কুপন</h3>
        <form class="cp-f" id="cpForm" autocomplete="off">
            <input type="hidden" name="id" value="" />
            <div><label>কুপন কোড</label><input name="code" maxlength="30" placeholder="যেমন TEACHER500" style="text-transform:uppercase;" required /></div>
            <div><label>কার জন্য</label><select name="audience" id="cpAud"><option value="Teacher">টিচার</option><option value="Student">শিক্ষার্থী</option></select></div>
            <div><label>কোন প্যাকেজে</label><select name="months" id="cpMonths"></select></div>
            <div><label>অফারের দাম (৳)</label><input type="number" name="price" min="0" required placeholder="যেমন 500" /></div>
            <div><label>শেষ তারিখ <small style="font-weight:600;">(ফাঁকা = শেষ নেই)</small></label><input type="date" name="endsAt" /></div>
            <div><label>সর্বোচ্চ কত জন <small style="font-weight:600;">(0 = সীমা নেই)</small></label><input type="number" name="maxUses" min="0" value="0" /></div>
            <div class="wide"><label>নোট (শুধু আপনি দেখবেন)</label><input name="note" maxlength="200" placeholder="যেমন ফেসবুক ক্যাম্পেইন — নভেম্বর" /></div>
            <div><label style="display:flex;align-items:center;gap:8px;height:42px;margin:0;"><input type="checkbox" name="active" checked style="width:18px;height:18px;" /> চালু</label></div>
            <div style="display:flex;gap:8px;"><button type="submit" class="cp-btn go"><i class="fas fa-save"></i> Save</button><button type="button" class="cp-btn l" id="cpReset" hidden>বাতিল</button></div>
        </form>
        <div id="cpMsg" style="margin-top:10px;font-weight:700;font-size:13.5px;"></div>
    </div>

    <div class="cp-card">
        <h3><i class="fas fa-list"></i> সব কুপন (<?= count($list) ?>)</h3>
        <?php if (!$list): ?><div style="text-align:center;color:#94A3B8;padding:20px 0;">এখনো কোনো কুপন নেই।</div><?php else: ?>
        <div style="overflow-x:auto;"><table class="cp-tbl">
            <tr><th>কোড</th><th>কার জন্য</th><th>প্যাকেজ</th><th>অফারের দাম</th><th>শেষ তারিখ</th><th>ব্যবহার</th><th>অবস্থা</th><th>নোট</th><th></th></tr>
            <?php foreach ($list as $c): $exp = $c->EndsAt && $c->EndsAt < now(); ?>
                <tr>
                    <td><span class="cp-code"><?= e($c->Code) ?></span></td>
                    <td><?= e($audBn[$c->Audience] ?? $c->Audience) ?></td>
                    <td><?= (int)$c->DurationMonths ? e(bn_num((int)$c->DurationMonths)) . ' মাসের প্যাকেজ' : 'সব নরমাল প্যাকেজ' ?></td>
                    <td><b style="color:#15803D;"><?= e(taka((int)$c->Price)) ?></b></td>
                    <td><?= $c->EndsAt ? e(date('d M Y', strtotime((string)$c->EndsAt))) : '<span style="color:#94A3B8;">শেষ নেই</span>' ?></td>
                    <td><b><?= (int)$c->UsedApproved ?></b> কিনেছে<?= $c->UsedPending ? ' · ' . (int)$c->UsedPending . ' অপেক্ষায়' : '' ?><?= (int)$c->MaxUses ? ' <small style="color:#64748B;">/ সর্বোচ্চ ' . (int)$c->MaxUses . '</small>' : '' ?></td>
                    <td><?= !$c->IsActive ? '<span class="cp-pill off">বন্ধ</span>' : ($exp ? '<span class="cp-pill exp">মেয়াদ শেষ</span>' : '<span class="cp-pill on">চালু</span>') ?></td>
                    <td><small><?= e((string)$c->Note) ?></small></td>
                    <td style="white-space:nowrap;"><button type="button" class="cp-btn l" style="height:32px;font-size:12px;" data-edit="<?= e(json_encode(['id' => $c->Id, 'code' => $c->Code, 'audience' => $c->Audience, 'months' => (int)$c->DurationMonths, 'price' => (int)$c->Price, 'endsAt' => $c->EndsAt ? substr((string)$c->EndsAt, 0, 10) : '', 'maxUses' => (int)$c->MaxUses, 'active' => $c->IsActive, 'note' => (string)$c->Note], JSON_UNESCAPED_UNICODE)) ?>">Edit</button>
                        <button type="button" class="cp-btn r" style="height:32px;font-size:12px;" data-del="<?= (int)$c->Id ?>"><i class="fas fa-trash"></i></button></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var DURS = <?= js($durs) ?>, f = document.getElementById('cpForm'), aud = document.getElementById('cpAud'), mon = document.getElementById('cpMonths'), msg = document.getElementById('cpMsg');
    function tok() { return document.querySelector('input[name="__RequestVerificationToken"]').value; }
    function fillMonths(sel) {
        var d = DURS[aud.value] || {}, h = '';
        Object.keys(d).forEach(function (m) { h += '<option value="' + m + '">' + d[m] + '</option>'; });
        h += '<option value="0">সব নরমাল প্যাকেজ</option>';
        mon.innerHTML = h; if (sel !== undefined) mon.value = String(sel); else if (d[6]) mon.value = '6';
    }
    aud.addEventListener('change', function () { fillMonths(); });
    fillMonths();
    function reset() { f.reset(); f.id.value = ''; fillMonths(); document.getElementById('cpFormT').innerHTML = '<i class="fas fa-plus"></i> নতুন কুপন'; document.getElementById('cpReset').hidden = true; }
    document.getElementById('cpReset').addEventListener('click', reset);
    f.addEventListener('submit', async function (e) {
        e.preventDefault();
        var fd = new FormData(f); fd.set('active', f.active.checked ? 'true' : 'false'); fd.append('__RequestVerificationToken', tok());
        var r = await (await fetch('/Admin/SaveCoupon', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
        msg.style.color = r.success ? '#15803D' : '#DC2626'; msg.textContent = r.message || '';
        if (r.success) setTimeout(function () { location.reload(); }, 600);
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-edit]');
        if (b) {
            var c = JSON.parse(b.getAttribute('data-edit'));
            f.id.value = c.id; f.code.value = c.code; aud.value = c.audience; fillMonths(c.months); f.price.value = c.price; f.endsAt.value = c.endsAt; f.maxUses.value = c.maxUses; f.note.value = c.note; f.active.checked = !!c.active;
            document.getElementById('cpFormT').innerHTML = '<i class="fas fa-pen"></i> কুপন বদলান — ' + c.code; document.getElementById('cpReset').hidden = false;
            window.scrollTo({ top: 0, behavior: 'smooth' }); return;
        }
        var d = e.target.closest('[data-del]');
        if (d && confirm('এই কুপন মুছে ফেলবেন? (আগে যারা ব্যবহার করেছে তাদের পেমেন্ট ঠিক থাকবে)')) {
            var fd = new FormData(); fd.append('id', d.getAttribute('data-del')); fd.append('__RequestVerificationToken', tok());
            await fetch('/Admin/DeleteCoupon', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); location.reload();
        }
    });
})();
</script>
<?php section_end(); ?>
