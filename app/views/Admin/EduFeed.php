<?php
/**
 * Admin → Educational Feed: ইউজার/টিচারের পোস্ট রিভিউ করে Approve / Reject / Delete।
 * Approve করলেই পোস্টটি /Feed পেজে দেখায় আর লেখক নোটিফিকেশন পান।
 */
$ViewData['Title'] = 'Educational Feed';
$posts = is_array($Model) ? $Model : [];
$status = (string)$ViewBag->Status;
$counts = (array)$ViewBag->Counts;
$page = (int)$ViewBag->Page; $pages = (int)$ViewBag->Pages;
$tabs = ['Pending' => ['fa-hourglass-half', 'Pending'], 'Approved' => ['fa-circle-check', 'Approved'], 'Rejected' => ['fa-circle-xmark', 'Rejected'], 'all' => ['fa-list', 'All']];
$me = current_user_id();
?>
<?php partial('Feed/_Styles'); ?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .af-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .af-head h2 { color: #08224A; margin: 0; font-size: 24px; }
    .af-head p { color: #64748B; margin: 4px 0 0; font-size: 14px; max-width: 720px; }
    .af-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 15px; border-radius: 10px; border: 1.5px solid #DCECF5; background: #fff; color: #08224A;
        font-weight: 600; font-size: 13.5px; text-decoration: none; cursor: pointer; white-space: nowrap; font-family: inherit; }
    .af-btn:hover { border-color: #02B8F5; color: #08224A; }
    .af-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
    .af-tabs a { display: inline-flex; gap: 8px; align-items: center; padding: 9px 15px; border-radius: 12px; background: #fff; border: 1.5px solid #DCECF5; color: #334155; font-weight: 700; font-size: 14px; text-decoration: none; }
    .af-tabs a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .af-tabs a .c { font-size: 11.5px; padding: 1px 8px; border-radius: 9px; background: #F1F5F9; color: #475569; }
    .af-tabs a.on .c { background: rgba(255,255,255,.2); color: #fff; }
    .af-tabs a.pend .c { background: #F5A623; color: #fff; }
    .af-list { max-width: 720px; }
    .af-foot { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; padding: 12px 16px; border-top: 1px solid #EEF2F7; background: #FAFCFE; }
    .af-foot .st { font-size: 12.5px; font-weight: 700; padding: 3px 10px; border-radius: 9px; }
    .af-foot .st.Pending { background: #FEF3C7; color: #92400E; } .af-foot .st.Approved { background: #DCFCE7; color: #166534; } .af-foot .st.Rejected { background: #FEE2E2; color: #991B1B; }
    .af-foot .sp { flex: 1; }
    .af-foot button, .af-foot a { display: inline-flex; gap: 7px; align-items: center; padding: 8px 14px; border-radius: 9px; border: none; cursor: pointer; font-weight: 700; font-size: 13.5px; font-family: inherit; text-decoration: none; }
    .af-ok { background: #16A34A; color: #fff; } .af-ok:hover { background: #15803D; }
    .af-no { background: #fff; color: #B91C1C; border: 1.5px solid #FBD5D5 !important; } .af-no:hover { border-color: #F87171 !important; }
    .af-pend { background: #fff; color: #475569; border: 1.5px solid #DCECF5 !important; }
    .af-del { background: #fff; color: #DC2626; border: 1.5px solid #FBD5D5 !important; width: 38px; justify-content: center; padding: 8px 0 !important; }
    .af-view { background: #fff; color: #0369A1; border: 1.5px solid #DCECF5 !important; }
    .af-reason { flex-basis: 100%; font-size: 12.5px; color: #991B1B; }
    .af-empty { background: #fff; border: 1.5px dashed #CBD5E1; border-radius: 16px; padding: 44px 20px; text-align: center; color: #64748B; max-width: 720px; }
    .af-empty i { font-size: 36px; color: #B9D6E6; display: block; margin-bottom: 10px; }
    .af-pg { display: flex; gap: 6px; margin-top: 10px; max-width: 720px; justify-content: center; flex-wrap: wrap; }
    .af-pg a, .af-pg span { min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; border: 1.5px solid #DCECF5; color: #334155; text-decoration: none; font-weight: 700; padding: 0 10px; }
    .af-pg span { background: #08224A; color: #fff; border-color: #08224A; }
    .af-modal { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 4000; display: none; align-items: center; justify-content: center; padding: 16px; }
    .af-modal.open { display: flex; }
    .af-modal .box { background: #fff; border-radius: 16px; padding: 20px; width: 100%; max-width: 460px; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
    .af-modal h4 { margin: 0 0 6px; color: #08224A; font-size: 18px; }
    .af-modal p { margin: 0 0 12px; color: #64748B; font-size: 13.5px; }
    .af-modal textarea { width: 100%; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 10px 12px; font-size: 14px; font-family: inherit; outline: none; resize: vertical; }
    .af-modal textarea:focus { border-color: #02B8F5; }
    .af-modal .qr { display: flex; gap: 6px; flex-wrap: wrap; margin: 10px 0 0; }
    .af-modal .qr button { border: 1px solid #E2E8F0; background: #F8FAFC; border-radius: 999px; padding: 5px 11px; font-size: 12.5px; cursor: pointer; color: #334155; font-family: inherit; }
    .af-modal .acts { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
    @media (max-width: 560px) { .af-foot .sp { display: none; } .af-foot { padding: 10px 12px; } .af-tabs a { padding: 8px 11px; font-size: 13px; } }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="af-head">
        <div>
            <h2><i class="fas fa-newspaper me-2" style="color:#1D7FE5;"></i>Educational Feed</h2>
            <p>ইউজার আর টিচারদের পোস্ট এখানে আসে। দেখে <b>Approve</b> করলে পোস্টটি <b>Educational Feed</b> পেজে সবাই দেখতে পাবে; <b>Reject</b> করলে শুধু লেখক কারণসহ দেখতে পাবেন। দুই ক্ষেত্রেই লেখক নোটিফিকেশন পান।</p>
        </div>
        <a class="af-btn" href="/Feed" target="_blank"><i class="fas fa-external-link-alt"></i> Feed পেজ দেখুন</a>
    </div>
    <?= csrf_field() ?>

    <nav class="af-tabs" aria-label="Status">
        <?php foreach ($tabs as $k => [$ic, $lbl]): ?>
            <a href="/Admin/EduFeed?status=<?= $k ?>" class="<?= $k === $status ? 'on' : '' ?><?= $k === 'Pending' && ($counts['Pending'] ?? 0) ? ' pend' : '' ?>"><i class="fas <?= $ic ?>"></i><?= $lbl ?> <span class="c" data-count="<?= $k ?>"><?= (int)($counts[$k] ?? 0) ?></span></a>
        <?php endforeach; ?>
    </nav>

    <?php if (!$posts): ?>
        <div class="af-empty"><i class="fas <?= $status === 'Pending' ? 'fa-mug-hot' : 'fa-inbox' ?>"></i><?= $status === 'Pending' ? 'রিভিউ করার মতো কোনো পোস্ট নেই — সব শেষ! 🎉' : 'এখানে কোনো পোস্ট নেই।' ?></div>
    <?php else: ?>
        <div class="af-list" id="afList">
            <?php foreach ($posts as $p):
                $foot = '<div class="af-foot"><span class="st ' . e($p->Status) . '">' . e($p->Status) . '</span>'
                    . ($p->Status === 'Approved' ? '<span style="font-size:12.5px;color:#64748B;"><i class="fas fa-thumbs-up"></i> ' . (int)$p->LikeCount . ' · <i class="fas fa-comment"></i> ' . (int)$p->CommentCount . '</span>' : '')
                    . '<span class="sp"></span>'
                    . ($p->Status !== 'Approved' ? '<button type="button" class="af-ok" data-af="Approved"><i class="fas fa-check"></i> Approve</button>' : '<a class="af-view" href="' . e($p->Url) . '" target="_blank"><i class="fas fa-eye"></i> দেখুন</a>')
                    . ($p->Status !== 'Rejected' ? '<button type="button" class="af-no" data-af="Rejected"><i class="fas fa-xmark"></i> Reject</button>' : '<button type="button" class="af-pend" data-af="Pending"><i class="fas fa-rotate-left"></i> Pending</button>')
                    . '<button type="button" class="af-del" data-ef="delete" title="মুছে ফেলুন"><i class="fas fa-trash"></i></button>'
                    . ($p->Status === 'Rejected' && $p->RejectReason !== '' ? '<div class="af-reason"><b>কারণ:</b> ' . e($p->RejectReason) . '</div>' : '')
                    . '</div>';
                partial('Feed/_Post', ['p' => $p, 'me' => $me, 'admin' => true, 'adminFooter' => $foot]);
            endforeach; ?>
        </div>
        <?php if ($pages > 1): ?>
            <nav class="af-pg"><?php for ($i = 1; $i <= $pages; $i++): ?><?php if ($i === $page): ?><span><?= $i ?></span><?php else: ?><a href="/Admin/EduFeed?status=<?= e($status) ?>&page=<?= $i ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?></nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="af-modal" id="afModal" role="dialog" aria-modal="true" aria-labelledby="afMt">
    <div class="box">
        <h4 id="afMt">পোস্ট Reject করবেন?</h4>
        <p>কারণ লিখে দিলে লেখক জানতে পারবেন কী ঠিক করতে হবে (ঐচ্ছিক)।</p>
        <textarea id="afReason" rows="3" maxlength="300" placeholder="যেমন: শিক্ষামূলক নয় / ছবি স্পষ্ট নয় / ফোন নম্বর দেওয়া যাবে না"></textarea>
        <div class="qr">
            <button type="button">শিক্ষামূলক পোস্ট নয়</button><button type="button">ছবি/ভিডিও স্পষ্ট নয়</button><button type="button">বিজ্ঞাপন বা ফোন নম্বর দেওয়া যাবে না</button><button type="button">কপিরাইট সমস্যা</button>
        </div>
        <div class="acts"><button type="button" class="af-btn" id="afCancel">বাতিল</button><button type="button" class="af-btn" id="afReject" style="background:#B91C1C;color:#fff;border-color:#B91C1C;"><i class="fas fa-xmark"></i> Reject</button></div>
    </div>
</div>

<?php partial('Feed/_Js'); ?>
<?php section_start('Scripts'); ?>
<script>
(function () {
    var STATUS = <?= js($status) ?>, modal = document.getElementById('afModal'), reason = document.getElementById('afReason'), pending = null;
    function setCounts(c) { Object.keys(c || {}).forEach(function (k) { var el = document.querySelector('[data-count="' + k + '"]'); if (el) el.textContent = c[k]; }); }
    async function apply(card, st, why) {
        var r = await EF.post('/Admin/EduFeedStatus', { id: card.getAttribute('data-post'), status: st, reason: why || '' });
        if (!r.success) { EF.toast(r.message, true); return; }
        EF.toast(r.message); setCounts(r.counts);
        if (STATUS !== 'all') { card.style.transition = 'opacity .25s, transform .25s'; card.style.opacity = '0'; card.style.transform = 'translateX(30px)'; setTimeout(function () { card.remove(); checkEmpty(); }, 260); }
        else setTimeout(function () { location.reload(); }, 500);
    }
    function checkEmpty() { var l = document.getElementById('afList'); if (l && !l.querySelector('[data-post]')) location.reload(); }
    document.addEventListener('ef:removed', checkEmpty);
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-af]'); if (!b) return;
        var card = b.closest('[data-post]'), st = b.getAttribute('data-af');
        if (st === 'Rejected') { pending = card; reason.value = ''; modal.classList.add('open'); reason.focus(); return; }
        apply(card, st);
    });
    modal.querySelector('.qr').addEventListener('click', function (e) { var b = e.target.closest('button'); if (b) { reason.value = b.textContent; reason.focus(); } });
    document.getElementById('afCancel').addEventListener('click', function () { modal.classList.remove('open'); });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('open'); });
    document.getElementById('afReject').addEventListener('click', function () { modal.classList.remove('open'); if (pending) apply(pending, 'Rejected', reason.value.trim()); });
})();
</script>
<?php section_end(); ?>
