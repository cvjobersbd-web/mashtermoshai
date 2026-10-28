<?php
$ViewData['Title'] = 'Reviews';
$reviews = is_array($Model) ? $Model : [];
$cur = (string)($ViewBag->CurrentStatus ?? 'all');
$tabs = [
    'all' => ['All', 'fa-list', (int)$ViewBag->TotalCount],
    'Pending' => ['Pending', 'fa-hourglass-half', (int)$ViewBag->ReviewPending],
    'Approved' => ['Approved', 'fa-check-circle', (int)$ViewBag->ApprovedCount],
    'Rejected' => ['Rejected', 'fa-times-circle', (int)$ViewBag->RejectedCount],
];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .stat-box { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border-left: 4px solid #02B8F5; }
    .stat-box.pending { border-left-color: #f39c12; }
    .stat-box.approved { border-left-color: #2ecc71; }
    .stat-box.rejected { border-left-color: #e74c3c; }
    .stat-box .num { font-size: 28px; font-weight: 800; color: #08224A; line-height: 1.2; }
    .stat-box .lbl { font-size: 13px; color: #64748B; margin-top: 4px; }

    .filter-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-tab { padding: 9px 20px; border-radius: 30px; font-size: 13px; font-weight: 600; text-decoration: none; border: 1.5px solid #DCECF5; color: #475569; background: #fff; display: inline-flex; align-items: center; gap: 6px; transition: all .2s; }
    .filter-tab:hover { border-color: #02B8F5; color: #08224A; }
    .filter-tab.active { background: #02B8F5; color: #fff; border-color: #02B8F5; }
    .filter-tab .cnt { background: rgba(0,0,0,0.08); border-radius: 10px; padding: 0 8px; font-size: 11px; }
    .filter-tab.active .cnt { background: rgba(255,255,255,0.25); }

    .rv-item { background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 14px; border: 1px solid #DCECF5; border-left: 5px solid #f39c12; box-shadow: 0 2px 12px rgba(8,34,74,0.04); }
    .rv-item.status-Approved { border-left-color: #2ecc71; }
    .rv-item.status-Rejected { border-left-color: #e74c3c; }
    .rv-head { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
    .rv-ava { width: 54px; height: 54px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 3px solid #CFF2FC; }
    .rv-ava img { width: 100%; height: 100%; object-fit: cover; }
    .rv-name { margin: 0; font-size: 16.5px; font-weight: 700; color: #08224A; }
    .rv-meta { font-size: 12.5px; color: #64748B; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .rv-role { font-size: 10.5px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; padding: 2px 9px; border-radius: 10px; background: #CFF2FC; color: #028EA5; }
    .rv-role.Teacher { background: #E8F8EF; color: #155724; }
    .rv-stars { color: #FFB300; font-size: 13px; letter-spacing: 1px; }
    .rv-stars .off { color: #DCECF5; }
    .rv-pill { margin-left: auto; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: .4px; }
    .rv-pill.Pending { background: #FFF7E0; color: #8a5a00; }
    .rv-pill.Approved { background: #d4edda; color: #155724; }
    .rv-pill.Rejected { background: #f8d7da; color: #721c24; }
    .rv-text { margin: 14px 0 0; padding: 14px 16px; background: #F5FBFF; border-radius: 12px; font-size: 14.5px; line-height: 1.7; color: #334155; white-space: pre-wrap; position: relative; }
    .rv-text::before { content: '\201C'; position: absolute; top: -10px; left: 10px; font-size: 44px; color: #02B8F5; font-family: var(--font-heading); line-height: 1; }
    .rv-adm-note { margin-top: 8px; font-size: 12.5px; color: #8B1E15; }
    .rv-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; padding-top: 14px; border-top: 1px solid #EEF5FA; }
    .rv-act { border: none; border-radius: 9px; padding: 8px 16px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: opacity .2s, transform .15s; }
    .rv-act:hover { opacity: .9; }
    .rv-act:active { transform: scale(.96); }
    .rv-act:disabled { opacity: .5; cursor: default; }
    .rv-act.approve { background: #2ecc71; color: #fff; }
    .rv-act.reject { background: #fff; color: #e74c3c; border: 1px solid #e74c3c; }
    .rv-act.pend { background: #fff; color: #64748B; border: 1px solid #DCECF5; }
    .rv-act.del { background: #fff; color: #e74c3c; border: 1px solid #f5c6cb; margin-left: auto; }

    .rv-empty { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; border: 2px dashed #DCECF5; color: #7A8CA0; }
    .rv-empty i { font-size: 46px; color: #B9D6E6; margin-bottom: 12px; display: block; }

    @media (max-width: 992px) {
        .admin-main-content { margin-left: 0; padding: 20px 15px; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .rv-pill { margin-left: 0; }
        .rv-act { flex: 1 1 auto; justify-content: center; }
        .rv-act.del { margin-left: 0; flex: 0 0 auto; }
    }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color:#08224A; margin:0;"><i class="fas fa-star me-2" style="color:#FFB300;"></i>Reviews</h2>
            <p style="color:#64748B; margin:4px 0 0 0; font-size:14px;">
                স্টুডেন্ট ও টিচারদের রিভিউ দেখুন — Approve করলে হোম পেজের "Stakeholders' Statements" সেকশনে দেখাবে
            </p>
        </div>
        <?php if ((int)$ViewBag->ReviewPending > 0): ?>
            <span class="badge bg-warning text-dark" style="font-size:13px; padding:8px 14px;"><?= e($ViewBag->ReviewPending) ?> Pending</span>
        <?php endif; ?>
    </div>

    <?= csrf_field() ?>

    <div class="stats-grid">
        <div class="stat-box"><div class="num"><?= e($ViewBag->TotalCount) ?></div><div class="lbl"><i class="fas fa-list"></i> Total</div></div>
        <div class="stat-box pending"><div class="num"><?= e($ViewBag->ReviewPending) ?></div><div class="lbl"><i class="fas fa-hourglass-half"></i> Pending</div></div>
        <div class="stat-box approved"><div class="num"><?= e($ViewBag->ApprovedCount) ?></div><div class="lbl"><i class="fas fa-check-circle"></i> On Home Page</div></div>
        <div class="stat-box rejected"><div class="num"><?= e($ViewBag->RejectedCount) ?></div><div class="lbl"><i class="fas fa-times-circle"></i> Rejected</div></div>
    </div>

    <div class="filter-tabs">
        <?php foreach ($tabs as $key => [$label, $icon, $n]): ?>
            <a href="/Admin/Reviews<?= $key === 'all' ? '' : '?status=' . e($key) ?>" class="filter-tab <?= $cur === $key ? 'active' : '' ?>">
                <i class="fas <?= e($icon) ?>"></i> <?= e($label) ?> <span class="cnt"><?= e($n) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="rv-empty">
            <i class="fas fa-comment-slash"></i>
            <h5 style="color:#08224A;">কোনো রিভিউ নেই</h5>
            <p style="margin:0;">স্টুডেন্ট বা টিচাররা তাদের ড্যাশবোর্ডের "My Review" থেকে রিভিউ দিলে এখানে দেখাবে।</p>
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
            <div class="rv-item status-<?= e($r->Status) ?>" id="rv-<?= e($r->Id) ?>">
                <div class="rv-head">
                    <div class="rv-ava">
                        <?php if (!is_blank($r->ProfileImageLink)): ?><img src="<?= e($r->ProfileImageLink) ?>" alt="" /><?php else: ?><?= e($r->Initials) ?><?php endif; ?>
                    </div>
                    <div style="min-width:0;">
                        <p class="rv-name"><?= e($r->FullName) ?></p>
                        <div class="rv-meta">
                            <span class="rv-role <?= e($r->Role) ?>"><?= e($r->RoleLabel) ?></span>
                            <?php if (!is_blank($r->Subtitle)): ?><span><?= e($r->Subtitle) ?></span><?php endif; ?>
                            <span class="rv-stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star <?= $i > $r->Rating ? 'off' : '' ?>"></i><?php endfor; ?></span>
                            <span><i class="far fa-clock"></i> <?= e($r->TimeAgo) ?></span>
                        </div>
                    </div>
                    <span class="rv-pill <?= e($r->Status) ?>" id="rv-pill-<?= e($r->Id) ?>"><?= e($r->Status) ?></span>
                </div>

                <div class="rv-text"><?= e($r->Content) ?></div>
                <?php if ($r->Status === 'Rejected' && !is_blank($r->AdminNote)): ?>
                    <div class="rv-adm-note"><i class="fas fa-sticky-note"></i> Note: <?= e($r->AdminNote) ?></div>
                <?php endif; ?>

                <div class="rv-actions">
                    <?php if ($r->Status !== 'Approved'): ?>
                        <button class="rv-act approve" onclick="rvSet(<?= (int)$r->Id ?>, 'Approved', this)"><i class="fas fa-check"></i> Approve</button>
                    <?php endif; ?>
                    <?php if ($r->Status !== 'Rejected'): ?>
                        <button class="rv-act reject" onclick="rvSet(<?= (int)$r->Id ?>, 'Rejected', this)"><i class="fas fa-times"></i> <?= $r->Status === 'Approved' ? 'Hide from Home' : 'Reject' ?></button>
                    <?php endif; ?>
                    <?php if ($r->Status !== 'Pending'): ?>
                        <button class="rv-act pend" onclick="rvSet(<?= (int)$r->Id ?>, 'Pending', this)"><i class="fas fa-undo"></i> Back to Pending</button>
                    <?php endif; ?>
                    <button class="rv-act del" onclick="rvDel(<?= (int)$r->Id ?>, this)" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php section_start('Scripts'); ?>
<script>
    function rvToken() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }

    function rvSet(id, status, btn) {
        var note = '';
        if (status === 'Rejected') {
            note = prompt('Reject করার কারণ লিখুন (ঐচ্ছিক — ইউজার নোটিফিকেশনে দেখবে):', '');
            if (note === null) return;
        }
        var fd = new FormData();
        fd.append('id', id); fd.append('status', status); fd.append('note', note);
        fd.append('__RequestVerificationToken', rvToken());
        btn.disabled = true;
        fetch('/Admin/UpdateReviewStatus', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) { if (d.success) location.reload(); else { alert(d.message || 'Failed'); btn.disabled = false; } })
            .catch(function (e) { alert('Error: ' + e.message); btn.disabled = false; });
    }

    function rvDel(id, btn) {
        if (!confirm('এই রিভিউটি স্থায়ীভাবে মুছে ফেলবেন?')) return;
        var fd = new FormData();
        fd.append('id', id); fd.append('__RequestVerificationToken', rvToken());
        btn.disabled = true;
        fetch('/Admin/DeleteReview', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) { var el = document.getElementById('rv-' + id); el.style.transition = 'opacity .3s'; el.style.opacity = 0; setTimeout(function () { el.remove(); }, 300); }
                else { alert(d.message || 'Failed'); btn.disabled = false; }
            })
            .catch(function (e) { alert('Error: ' + e.message); btn.disabled = false; });
    }
</script>
<?php section_end(); ?>
