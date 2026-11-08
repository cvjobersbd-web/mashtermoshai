<?php
/** কোর্সের শিক্ষার্থী — অগ্রগতি, অ্যাসাইনমেন্ট জমা দেখা, নম্বর / মন্তব্য দেওয়া */
$c = $Model; $students = $ViewBag->Students ?? []; $subs = $ViewBag->Subs ?? []; $tasks = $ViewBag->Tasks ?? [];
$ViewData['Title'] = 'শিক্ষার্থী — ' . $c->Title;
$ViewData['Robots'] = 'noindex, nofollow';
$asg = array_values(array_filter($subs, fn($s) => $s->TaskKind === 'assignment'));
$unreviewed = count(array_filter($asg, fn($s) => $s->Status !== 'reviewed'));
$quiz = array_values(array_filter($subs, fn($s) => $s->TaskKind === 'quiz'));
$certs = count(array_filter($students, fn($s) => !empty($s->CertCode)));
?>
<?php partial('Course/_Styles'); ?>
<style>
    .st-u { display: flex; gap: 10px; align-items: center; min-width: 180px; }
    .st-u img, .st-u .av { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background: #E0F2FE; color: #0369A1; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
    .st-u b { display: block; color: #0F172A; font-size: 14px; } .st-u small { color: #94A3B8; font-size: 12px; }
    .st-p { min-width: 150px; } .st-p small { display: block; color: #64748B; font-size: 12px; margin-top: 4px; }
    .st-s { border: 1px solid #E5ECF3; border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .st-s.new { border-left: 4px solid #F59E0B; } .st-s.done { border-left: 4px solid #16A34A; }
    .st-sh { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; align-items: flex-start; }
    .st-sh b { color: #0F172A; } .st-sh small { color: #64748B; font-size: 12.5px; display: block; margin-top: 2px; }
    .st-link { display: inline-flex; align-items: center; gap: 7px; margin-top: 10px; background: #F0F9FF; color: #0369A1; border-radius: 9px; padding: 7px 12px; font-weight: 700; font-size: 13px; text-decoration: none; word-break: break-all; }
    .st-note { margin-top: 8px; font-size: 13.5px; color: #334155; background: #F8FAFC; border-radius: 9px; padding: 8px 12px; white-space: pre-wrap; }
    .st-rv { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; align-items: center; }
    .st-rv input { height: 38px; border: 1.5px solid #DCE6F0; border-radius: 10px; padding: 0 11px; font-size: 14px; font-family: inherit; outline: none; }
    .st-rv input[name=marks] { width: 90px; } .st-rv input[name=feedback] { flex: 1 1 240px; min-width: 0; }
    .st-tabs { display: flex; gap: 6px; margin-bottom: 14px; flex-wrap: wrap; }
    .st-tabs button { border: none; padding: 8px 15px; border-radius: 999px; background: #fff; color: #334155; font-weight: 700; font-size: 13.5px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.05); font-family: inherit; }
    .st-tabs button.on { background: #08224A; color: #fff; }
    [data-pane][hidden] { display: none !important; }
</style>
<?= csrf_field() ?>
<div class="cx-page">
    <div class="cx-wrap">
        <a class="cx-back" href="/Course/Build/<?= (int)$c->Id ?>"><i class="fas fa-arrow-left"></i> <?= e($c->Title) ?></a>
        <h1 class="cx-h1" style="margin-bottom:16px;"><i class="fas fa-users" style="color:#0EA5E9;"></i> শিক্ষার্থী ও জমা</h1>
        <div class="cx-stats">
            <div class="cx-stat" style="--c:#0EA5E9;"><small>শিক্ষার্থী</small><b><?= count($students) ?> জন</b></div>
            <div class="cx-stat" style="--c:#F59E0B;"><small>দেখা বাকি (অ্যাসাইনমেন্ট)</small><b><?= $unreviewed ?>টা</b></div>
            <div class="cx-stat" style="--c:#7C3AED;"><small>কুইজ দিয়েছে</small><b><?= count($quiz) ?>টা</b></div>
            <div class="cx-stat" style="--c:#16A34A;"><small>সার্টিফিকেট পেয়েছে</small><b><?= $certs ?> জন</b></div>
        </div>
        <div class="st-tabs">
            <button type="button" class="on" data-tab="stu"><i class="fas fa-user-graduate"></i> শিক্ষার্থী</button>
            <button type="button" data-tab="asg"><i class="fas fa-paperclip"></i> অ্যাসাইনমেন্ট জমা<?= $unreviewed ? ' (' . $unreviewed . ')' : '' ?></button>
            <button type="button" data-tab="quiz"><i class="fas fa-list-check"></i> কুইজের ফল</button>
        </div>

        <div class="cx-card" data-pane="stu">
            <?php if (!$students): ?><div class="cx-empty"><i class="fas fa-user-graduate"></i>এখনো কেউ ভর্তি হয়নি।</div><?php else: ?>
            <div class="cx-tw"><table class="cx-tbl">
                <thead><tr><th>শিক্ষার্থী</th><th>ভর্তি</th><th>অগ্রগতি</th><th>ক্লাস</th><th>অ্যাসাইনমেন্ট</th><th>কুইজ</th><th>নম্বর</th><th>সার্টিফিকেট</th></tr></thead>
                <tbody>
                <?php foreach ($students as $s): $p = $s->Progress; $nm = (string)($s->FullName ?: $s->UserName); ?>
                    <tr>
                        <td><div class="st-u"><?php if (!empty($s->ProfileImageLink)): ?><img src="<?= e($s->ProfileImageLink) ?>" alt="" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'av',textContent:'<?= e(mb_substr($nm, 0, 1)) ?>'}))" /><?php else: ?><span class="av"><?= e(mb_substr($nm, 0, 1)) ?></span><?php endif; ?>
                            <div><b><?= is_admin() ? '<a href="/Admin/UserDetails/' . (int)$s->UserId . '" style="color:inherit;">' . e($nm) . '</a>' : e($nm) ?></b><small><?= e($s->MobileNumber ?: ($s->Email ?? '')) ?></small></div></div></td>
                        <td><?= e(fmt_date($s->CreatedAt, 'd M Y')) ?><br><small style="color:#94A3B8;"><?= (int)$s->Price > 0 ? '৳' . number_format((int)$s->Price) : 'ফ্রি' ?></small></td>
                        <td class="st-p"><div class="cx-bar"><div style="width:<?= (int)$p['percent'] ?>%;"></div></div><small><?= (int)$p['percent'] ?>%</small></td>
                        <td><?= $p['lessons'][0] ?>/<?= $p['lessons'][1] ?></td>
                        <td><?= $p['assignments'][0] ?>/<?= $p['assignments'][1] ?></td>
                        <td><?= $p['quizzes'][0] ?>/<?= $p['quizzes'][1] ?></td>
                        <td><?= $p['marks'][1] ? $p['marks'][0] . '/' . $p['marks'][1] : '—' ?></td>
                        <td><?= !empty($s->CertCode) ? '<a class="cx-pill ok" href="/Course/Certificate/' . e($s->CertCode) . '" target="_blank" rel="noopener"><i class="fas fa-award"></i> ' . e($s->CertCode) . '</a>' : '<span class="cx-pill">এখনো না</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>

        <div class="cx-card" data-pane="asg" hidden>
            <?php if (!$asg): ?><div class="cx-empty"><i class="fas fa-paperclip"></i>এখনো কেউ অ্যাসাইনমেন্ট জমা দেয়নি।</div><?php endif; ?>
            <?php foreach ($asg as $s): $rv = $s->Status === 'reviewed'; ?>
                <div class="st-s <?= $rv ? 'done' : 'new' ?>">
                    <div class="st-sh">
                        <div><b><?= e($s->FullName ?: $s->UserName) ?></b> — <?= e($s->TaskTitle) ?><small><i class="far fa-clock"></i> <?= e(fmt_date($s->UpdatedAt ?: $s->CreatedAt, 'd M Y, h:i A')) ?><?= (int)$s->Attempts > 1 ? ' · ' . (int)$s->Attempts . ' বার জমা' : '' ?></small></div>
                        <?= $rv ? '<span class="cx-pill ok"><i class="fas fa-check"></i> দেখা হয়েছে' . ($s->Marks !== null ? ' · ' . (int)$s->Marks . '/' . (int)$s->MaxMarks : '') . '</span>' : '<span class="cx-pill warn">নতুন</span>' ?>
                    </div>
                    <?php if (!empty($s->Link)): ?><a class="st-link" href="<?= e($s->Link) ?>" target="_blank" rel="noopener nofollow"><i class="fas <?= str_contains((string)$s->Link, 'github.com') ? 'fa-brands fa-github' : (str_contains((string)$s->Link, 'drive.google') ? 'fa-brands fa-google-drive' : 'fa-link') ?>"></i> <?= e($s->Link) ?></a><?php endif; ?>
                    <?php if (!is_blank($s->Note ?? '')): ?><div class="st-note"><?= e($s->Note) ?></div><?php endif; ?>
                    <form class="st-rv" data-review="<?= (int)$s->Id ?>">
                        <input type="number" name="marks" min="0" max="<?= (int)$s->MaxMarks ?>" placeholder="নম্বর" value="<?= $s->Marks !== null ? (int)$s->Marks : '' ?>" title="পূর্ণ নম্বর <?= (int)$s->MaxMarks ?>" />
                        <span style="color:#94A3B8;font-size:13px;">/ <?= (int)$s->MaxMarks ?></span>
                        <input type="text" name="feedback" maxlength="500" placeholder="মন্তব্য (ঐচ্ছিক)" value="<?= e($s->Feedback ?? '') ?>" />
                        <button class="cx-btn ok sm" type="submit"><i class="fas fa-check"></i> সেভ</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cx-card" data-pane="quiz" hidden>
            <?php if (!$quiz): ?><div class="cx-empty"><i class="fas fa-list-check"></i>এখনো কেউ কুইজ দেয়নি।</div><?php else: ?>
            <div class="cx-tw"><table class="cx-tbl">
                <thead><tr><th>শিক্ষার্থী</th><th>কুইজ</th><th>সর্বশেষ স্কোর</th><th>চেষ্টা</th><th>ফল</th><th>সময়</th></tr></thead>
                <tbody>
                <?php foreach ($quiz as $s): ?>
                    <tr>
                        <td><b><?= e($s->FullName ?: $s->UserName) ?></b></td>
                        <td><?= e($s->TaskTitle) ?></td>
                        <td><?= (int)$s->Score ?>/<?= (int)$s->Total ?></td>
                        <td><?= (int)$s->Attempts ?></td>
                        <td><?= (int)$s->Passed === 1 ? '<span class="cx-pill ok">পাস</span>' : '<span class="cx-pill no">এখনো পাস করেনি</span>' ?></td>
                        <td><?= e(fmt_date($s->UpdatedAt ?: $s->CreatedAt, 'd M, h:i A')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
(function () {
    document.querySelectorAll('[data-tab]').forEach(function (b) { b.addEventListener('click', function () {
        document.querySelectorAll('[data-tab]').forEach(function (x) { x.classList.toggle('on', x === b); });
        document.querySelectorAll('[data-pane]').forEach(function (p) { p.hidden = p.dataset.pane !== b.dataset.tab; });
    }); });
    if (location.hash === '#asg') document.querySelector('[data-tab="asg"]').click();
    document.querySelectorAll('[data-review]').forEach(function (f) { f.addEventListener('submit', async function (e) {
        e.preventDefault(); var b = f.querySelector('button'); b.disabled = true;
        var d = await cxPost('/Course/Review', { id: f.dataset.review, marks: f.elements.marks.value, feedback: f.elements.feedback.value });
        b.disabled = false; cxToast(d.message || '');
        if (d.success) { var c = f.closest('.st-s'); c.classList.remove('new'); c.classList.add('done'); }
    }); });
})();
</script>
