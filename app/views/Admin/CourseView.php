<?php
/** Admin → Courses → একটা কোর্সের ডিটেইলস: টিচার, দাম, ক্লাস, অ্যাসাইনমেন্ট, কুইজ, কে কে কিনেছে, ব্লক */
$c = $Model; $kl = $c->KindLabel;
$ViewData['Title'] = 'Course Details';
$lessons = $ViewBag->Lessons ?? []; $tasks = $ViewBag->Tasks ?? []; $studs = $ViewBag->Students ?? []; $pend = $ViewBag->Pending ?? [];
$asg = array_values(array_filter($tasks, fn($t) => $t->Kind !== 'quiz')); $qz = array_values(array_filter($tasks, fn($t) => $t->Kind === 'quiz'));
$lname = []; foreach ($lessons as $i => $l) $lname[$l->Id] = ($i + 1) . '. ' . $l->Title;
$recReady = 0; foreach ($lessons as $l) if ($l->Kind !== 'live' && $l->RecordingUrl) $recReady++;
$income = 0; foreach ($studs as $s) $income += (int)$s->Price;
$srcLbl = ['payment' => 'পেমেন্ট', 'free' => 'ফ্রি', 'admin' => 'অ্যাডমিন দিয়েছেন'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 90px; min-height: calc(100vh - 80px); }
    .cv a.back { color: #64748B; text-decoration: none; font-weight: 700; font-size: 13.5px; }
    .cv-head { display: grid; grid-template-columns: 220px 1fr; gap: 18px; background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin: 10px 0 18px; }
    .cv-head .cx-thumb { aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #0B3B6B, #0EA5E9); color: #fff; display: flex; align-items: center; justify-content: center; }
    .cv-head .cx-thumb img { width: 100%; height: 100%; object-fit: cover; } .cv-head .cx-thumb .ini { font-weight: 800; padding: 0 8px; text-align: center; }
    .cv-head h2 { margin: 0 0 4px; font-size: 22px; color: #08224A; }
    .cv-meta { display: flex; flex-wrap: wrap; gap: 8px 16px; color: #475569; font-size: 13.5px; margin: 8px 0 12px; }
    .cv-meta a { color: #0F172A; font-weight: 700; }
    .cv-act { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .cv-stats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .cv-st { background: #fff; border-radius: 14px; padding: 12px 14px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); }
    .cv-st small { display: block; color: #64748B; font-weight: 700; font-size: 12px; } .cv-st b { display: block; font-size: 21px; color: #0F172A; margin-top: 3px; }
    .cv-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .cv-card h3 { margin: 0 0 12px; font-size: 17px; color: #0F172A; font-weight: 800; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .cv-tw { overflow-x: auto; }
    .cv-t { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 640px; }
    .cv-t th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 8px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .cv-t td { padding: 9px 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
    .cv-t td a { color: #0F172A; font-weight: 700; text-decoration: none; } .cv-t td a:hover { text-decoration: underline; }
    .cv-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; white-space: nowrap; background: #F1F5F9; color: #475569; }
    .cv-pill.ok { background: #DCFCE7; color: #166534; } .cv-pill.w { background: #FEF3C7; color: #92400E; } .cv-pill.no { background: #FEE2E2; color: #991B1B; } .cv-pill.b { background: #E0F2FE; color: #075985; }
    .cv-btn { height: 36px; padding: 0 14px; border-radius: 9px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; text-decoration: none; }
    .cv-btn.l { background: #F1F5F9; color: #334155; } .cv-btn.r { background: #DC2626; } .cv-btn.g { background: #15803D; } .cv-btn.o { background: #FEE2E2; color: #B91C1C; }
    .cv-btn:hover { filter: brightness(1.05); }
    .cv-block { background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 14px; padding: 12px 16px; margin: 10px 0 0; color: #991B1B; font-weight: 700; font-size: 14px; }
    .cv-bar { height: 7px; background: #E2E8F0; border-radius: 99px; overflow: hidden; width: 90px; display: inline-block; vertical-align: middle; margin-right: 6px; } .cv-bar i { display: block; height: 100%; background: #16A34A; }
    .cv-u { display: flex; align-items: center; gap: 9px; } .cv-u img, .cv-u .av { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; background: #E0F2FE; color: #075985; display: flex; align-items: center; justify-content: center; font-weight: 800; flex: none; }
    .cv-empty { color: #94A3B8; text-align: center; padding: 18px 8px; }
    .cv-modal { position: fixed; inset: 0; background: rgba(15,23,42,.55); display: none; align-items: center; justify-content: center; z-index: 9998; padding: 16px; }
    .cv-modal > div { background: #fff; border-radius: 16px; padding: 20px; width: 100%; max-width: 460px; }
    .cv-modal textarea { width: 100%; min-height: 90px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 10px; font-family: inherit; font-size: 14px; box-sizing: border-box; margin: 10px 0 12px; }
    .cv-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; }
    @media (max-width: 1100px) { .cv-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 80px; } .cv-head { grid-template-columns: 1fr; } .cv-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content cv">
    <?= csrf_field() ?>
    <a class="back" href="<?= $c->Kind === 'live' ? '/Admin/LiveClasses' : '/Admin/Courses' ?>"><i class="fas fa-arrow-left"></i> সব <?= $kl ?></a>

    <div class="cv-head">
        <div><?= course_thumb_html($c) ?></div>
        <div>
            <h2><?= e($c->Title) ?></h2>
            <?php if ($c->Subtitle): ?><div style="color:#64748B;font-size:14px;"><?= e($c->Subtitle) ?></div><?php endif; ?>
            <div class="cv-meta">
                <span><?= $c->AdminBlocked ? '<span class="cv-pill no"><i class="fas fa-ban"></i> ব্লক করা</span>' : ($c->AdminHidden ? '<span class="cv-pill no">লুকানো</span>' : ($c->Status === 'published' ? '<span class="cv-pill ok">প্রকাশিত</span>' : '<span class="cv-pill w">Draft</span>')) ?></span>
                <span><i class="fas fa-chalkboard-user"></i> টিচার: <a href="/Admin/UserDetails/<?= (int)$c->TeacherId ?>"><?= e($c->TeacherName ?: $c->TeacherUser) ?></a></span>
                <span><i class="fas fa-tag"></i> দাম: <b>৳<?= number_format($c->Price) ?></b><?= $c->RegularPrice ? ' <s style="color:#94A3B8;">৳' . number_format($c->RegularPrice) . '</s>' : '' ?></span>
                <?php if ($c->Category): ?><span><i class="fas fa-folder"></i> <?= e($c->Category) ?></span><?php endif; ?>
                <span><i class="fas fa-calendar"></i> বানানো: <?= e(fmt_date($c->CreatedAt, 'd M Y')) ?></span>
            </div>
            <div class="cv-act">
                <a class="cv-btn l" href="/Course/Details/<?= (int)$c->Id ?>" target="_blank" rel="noopener"><i class="fas fa-eye"></i> কোর্স পেজ</a>
                <a class="cv-btn l" href="/Course/Build/<?= (int)$c->Id ?>"><i class="fas fa-layer-group"></i> ক্লাস ও কাজ</a>
                <a class="cv-btn l" href="/Course/Students/<?= (int)$c->Id ?>"><i class="fas fa-clipboard-check"></i> জমা দেওয়া কাজ</a>
                <?php if ($c->AdminBlocked): ?>
                    <button class="cv-btn g" type="button" id="cvUnblock"><i class="fas fa-circle-check"></i> আনব্লক করুন</button>
                <?php else: ?>
                    <button class="cv-btn r" type="button" id="cvBlock"><i class="fas fa-ban"></i> কোর্স ব্লক করুন</button>
                <?php endif; ?>
            </div>
            <?php if ($c->AdminBlocked): ?><div class="cv-block"><i class="fas fa-ban"></i> এই <?= $kl ?>টি ব্লক করা — কেউ কিনতে বা ক্লাস দেখতে পারবে না, টিচার এডিট করতে পারবে না।<?= $c->BlockReason !== '' ? '<br><span style="font-weight:600;">কারণ: ' . e($c->BlockReason) . '</span>' : '' ?></div><?php endif; ?>
        </div>
    </div>

    <div class="cv-stats">
        <div class="cv-st" style="--c:#0EA5E9;"><small>ক্লাস</small><b><?= count($lessons) ?>টা</b></div>
        <div class="cv-st" style="--c:#16A34A;"><small>ভিডিও রেডি</small><b><?= $recReady ?>টা</b></div>
        <div class="cv-st" style="--c:#F59E0B;"><small>অ্যাসাইনমেন্ট</small><b><?= count($asg) ?>টা</b></div>
        <div class="cv-st" style="--c:#7C3AED;"><small>কুইজ</small><b><?= count($qz) ?>টা</b></div>
        <div class="cv-st" style="--c:#0F172A;"><small>শিক্ষার্থী</small><b><?= count($studs) ?> জন</b></div>
        <div class="cv-st" style="--c:#DC2626;"><small>মোট বিক্রি</small><b>৳<?= number_format($income) ?></b></div>
    </div>

    <div class="cv-card">
        <h3><i class="fas fa-user-graduate" style="color:#0EA5E9;"></i> যারা কোর্সটা নিয়েছে <span class="cv-pill"><?= count($studs) ?> জন</span></h3>
        <?php if (!$studs): ?><div class="cv-empty">এখনো কেউ কোর্সটা নেয়নি।</div><?php else: ?>
        <div class="cv-tw"><table class="cv-t">
            <thead><tr><th>#</th><th>শিক্ষার্থী</th><th>মোবাইল</th><th>ইমেইল</th><th>কবে নিয়েছে</th><th>কীভাবে</th><th>দিয়েছে</th><th>অগ্রগতি</th></tr></thead>
            <tbody>
            <?php foreach ($studs as $i => $s): $pg = $s->Progress ?? []; $nm = $s->FullName ?: $s->UserName; $py = $s->Payment ?? null; ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><div class="cv-u"><?= $s->ProfileImageLink ? '<img src="' . e($s->ProfileImageLink) . '" alt="" loading="lazy" />' : '<span class="av">' . e(mb_substr((string)$nm, 0, 1)) . '</span>' ?>
                        <div><a href="/Admin/UserDetails/<?= (int)$s->UserId ?>"><?= e($nm) ?></a><br><small style="color:#94A3B8;">@<?= e($s->UserName) ?></small></div></div></td>
                    <td><?= e($s->MobileNumber ?? '') ?: '—' ?></td>
                    <td style="font-size:12.5px;"><?= e($s->Email ?? '') ?: '—' ?></td>
                    <td style="white-space:nowrap;"><?= e(fmt_date($s->CreatedAt, 'd M Y, h:i A')) ?></td>
                    <td><?= e($srcLbl[$s->Source] ?? $s->Source) ?><?php if ($py): ?><br><small style="color:#64748B;"><?= e($py->MethodName) ?><?= $py->TxnId ? ' · ' . e($py->TxnId) : '' ?></small><?php endif; ?></td>
                    <td><b>৳<?= number_format((int)$s->Price) ?></b></td>
                    <td style="white-space:nowrap;"><span class="cv-bar"><i style="width:<?= (int)($pg['percent'] ?? 0) ?>%;"></i></span><?= (int)($pg['percent'] ?? 0) ?>%
                        <br><small style="color:#94A3B8;">ক্লাস <?= (int)($pg['lessons'][0] ?? 0) ?>/<?= (int)($pg['lessons'][1] ?? 0) ?> · কাজ <?= (int)(($pg['assignments'][0] ?? 0) + ($pg['quizzes'][0] ?? 0)) ?>/<?= (int)(($pg['assignments'][1] ?? 0) + ($pg['quizzes'][1] ?? 0)) ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
        <?php if ($pend): ?>
            <div style="margin-top:14px;font-weight:800;color:#92400E;"><i class="fas fa-clock"></i> পেমেন্ট যাচাইয়ের অপেক্ষায় (<?= count($pend) ?> জন):
                <?php foreach ($pend as $p): ?><a href="/Admin/UserDetails/<?= (int)$p->UserId ?>" style="margin-left:6px;color:#0F172A;"><?= e($p->FullName ?: $p->UserName) ?></a><?php endforeach; ?>
                — <a href="<?= $c->Kind === 'live' ? '/Admin/LivePayments' : '/Admin/CoursePayments' ?>">যাচাই করুন →</a></div>
        <?php endif; ?>
    </div>

    <div class="cv-card">
        <h3><i class="fas fa-circle-play" style="color:#0EA5E9;"></i> ক্লাসগুলো <span class="cv-pill"><?= count($lessons) ?>টা</span></h3>
        <?php if (!$lessons): ?><div class="cv-empty">এখনো কোনো ক্লাস যোগ করা হয়নি।</div><?php else: ?>
        <div class="cv-tw"><table class="cv-t">
            <thead><tr><th>#</th><th>ক্লাসের নাম</th><th>ধরন</th><th>ভিডিও</th><th>কখন খুলবে / শুরু</th></tr></thead>
            <tbody>
            <?php foreach ($lessons as $i => $l): $live = $l->Kind === 'live'; ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><b><?= e($l->Title) ?></b></td>
                    <td><?= $live ? '<span class="cv-pill no">লাইভ</span>' : '<span class="cv-pill b">রেকর্ড</span>' ?></td>
                    <td><?= $l->RecordingUrl ? '<span class="cv-pill ok">রেডি' . ($l->RecordingMin ? ' · ' . (int)$l->RecordingMin . ' মিনিট' : '') . '</span>' : '<span class="cv-pill w">এখনো হয়নি</span>' ?></td>
                    <td style="white-space:nowrap;"><?= $live ? ($l->StartsAt ? e(fmt_date($l->StartsAt, 'd M Y, h:i A')) : '—') : ($l->UnlockAt ? e(fmt_date($l->UnlockAt, 'd M Y, h:i A')) : 'সাথে সাথেই') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>

    <div class="cv-card">
        <h3><i class="fas fa-clipboard-list" style="color:#F59E0B;"></i> অ্যাসাইনমেন্ট ও কুইজ <span class="cv-pill"><?= count($asg) ?>টা অ্যাসাইনমেন্ট · <?= count($qz) ?>টা কুইজ</span></h3>
        <?php if (!$tasks): ?><div class="cv-empty">এখনো কোনো অ্যাসাইনমেন্ট বা কুইজ দেওয়া হয়নি।</div><?php else: ?>
        <div class="cv-tw"><table class="cv-t">
            <thead><tr><th>#</th><th>নাম</th><th>ধরন</th><th>কোন ক্লাসের</th><th>শেষ সময়</th></tr></thead>
            <tbody>
            <?php foreach ($tasks as $i => $t): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><b><?= e($t->Title) ?></b></td>
                    <td><?= $t->Kind === 'quiz' ? '<span class="cv-pill" style="background:#EDE9FE;color:#5B21B6;">কুইজ</span>' : '<span class="cv-pill w">অ্যাসাইনমেন্ট</span>' ?></td>
                    <td><?= $t->LessonId ? e($lname[(int)$t->LessonId] ?? '—') : 'পুরো কোর্সের' ?></td>
                    <td style="white-space:nowrap;"><?= $t->DueAt ? e(fmt_date($t->DueAt, 'd M Y, h:i A')) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>

<div class="cv-modal" id="cvModal"><div>
    <h3 style="margin:0;font-size:18px;color:#991B1B;"><i class="fas fa-ban"></i> <?= $kl ?> ব্লক করবেন?</h3>
    <p style="font-size:13.5px;color:#475569;margin:8px 0 0;line-height:1.6;">ব্লক করলে: Course পেজে দেখাবে না, কেউ কিনতে পারবে না, যারা কিনেছে তারাও ক্লাস দেখতে পারবে না, টিচার এডিট করতে পারবে না। টিচার ও শিক্ষার্থীরা নোটিফিকেশন পাবে। পরে চাইলে আনব্লক করা যাবে।</p>
    <textarea id="cvReason" maxlength="300" placeholder="কারণ লিখুন (টিচার দেখতে পাবে) — ঐচ্ছিক"></textarea>
    <div style="display:flex;gap:8px;justify-content:flex-end;"><button class="cv-btn l" type="button" id="cvCancel">বাতিল</button><button class="cv-btn r" type="button" id="cvGo"><i class="fas fa-ban"></i> ব্লক করুন</button></div>
</div></div>

<script>
(function () {
    var cid = <?= (int)$c->Id ?>;
    function tok() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    function toast(m) { var t = document.querySelector('.cv-toast'); if (!t) { t = document.createElement('div'); t.className = 'cv-toast'; document.body.appendChild(t); } t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 3200); }
    async function block(on, reason) {
        var fd = new FormData(); fd.append('id', cid); fd.append('blocked', on ? 1 : 0); fd.append('reason', reason || ''); fd.append('__RequestVerificationToken', tok());
        var d; try { d = await (await fetch('/Admin/BlockCourse', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); } catch (e) { d = { success: false, message: 'সার্ভারে সমস্যা' }; }
        toast(d.message || ''); if (d.success) setTimeout(function () { location.reload(); }, 900);
    }
    var m = document.getElementById('cvModal');
    var b = document.getElementById('cvBlock'); if (b) b.addEventListener('click', function () { m.style.display = 'flex'; document.getElementById('cvReason').focus(); });
    document.getElementById('cvCancel').addEventListener('click', function () { m.style.display = 'none'; });
    document.getElementById('cvGo').addEventListener('click', function () { block(true, document.getElementById('cvReason').value); });
    var u = document.getElementById('cvUnblock'); if (u) u.addEventListener('click', function () { if (confirm('কোর্সটা আবার চালু করবেন? টিচার ও শিক্ষার্থীরা নোটিফিকেশন পাবে।')) block(false, ''); });
})();
</script>
