<?php
/** কোর্স করা — ক্লাস দেখা (রেকর্ডিং / লাইভ), অ্যাসাইনমেন্ট জমা, কুইজ, অগ্রগতি, সার্টিফিকেট */
$c = $Model; $lessons = $ViewBag->Lessons ?? []; $cur = $ViewBag->Cur; $tasks = $ViewBag->Tasks ?? []; $subs = $ViewBag->Subs ?? [];
$done = array_flip($ViewBag->Done ?? []); $pg = $ViewBag->Progress; $enr = $ViewBag->Enrollment; $manager = !empty($ViewBag->Manager); $proc = $ViewBag->Processing ?? [];
$ViewData['Title'] = $c->Title;
$ViewData['Robots'] = 'noindex, nofollow';
$byLesson = []; $general = [];
foreach ($tasks as $t) { if ($t->LessonId) $byLesson[$t->LessonId][] = $t; else $general[] = $t; }
$curTasks = $cur && !$cur->Locked ? ($byLesson[$cur->Id] ?? []) : [];
$cert = $enr && !empty($enr->CertCode) ? (string)$enr->CertCode : '';
$learner = (bool)$enr;
$idx = 0; foreach ($lessons as $i => $l) if ($cur && $l->Id === $cur->Id) $idx = $i;
$prev = $lessons[$idx - 1] ?? null; $next = $lessons[$idx + 1] ?? null;
?>
<?php partial('Course/_Styles'); ?>
<style>
    .cl-g { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 22px; align-items: start; }
    .cl-top { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .cl-player { background: #0B1220; border-radius: 18px; overflow: hidden; aspect-ratio: 16 / 9; position: relative; color: #fff; display: flex; align-items: center; justify-content: center; text-align: center; }
    .cl-player iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: #000; }
    .cl-ph { padding: 24px; max-width: 520px; }
    .cl-ph i.big { font-size: 46px; margin-bottom: 14px; display: block; opacity: .9; }
    .cl-ph h3 { margin: 0 0 8px; font-size: 20px; font-weight: 800; } .cl-ph p { margin: 0 0 16px; opacity: .85; font-size: 15px; line-height: 1.6; }
    .cl-cd { font-size: 26px; font-weight: 800; letter-spacing: .5px; margin: 6px 0 14px; font-variant-numeric: tabular-nums; color: #7DD3FC; }
    .cl-head { margin: 16px 0 6px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; align-items: flex-start; }
    .cl-head h2 { margin: 0; font-size: 21px; font-weight: 800; color: #0B1F44; }
    .cl-desc { color: #475569; font-size: 15px; line-height: 1.7; white-space: pre-line; margin: 8px 0 0; }
    .cl-nav { display: flex; justify-content: space-between; gap: 10px; margin: 16px 0 18px; flex-wrap: wrap; }
    .cl-side { position: sticky; top: 92px; }
    .cl-ls { display: flex; flex-direction: column; gap: 4px; max-height: 60vh; overflow-y: auto; margin: 0 -6px; padding: 0 6px; }
    .cl-li { display: flex; gap: 10px; align-items: center; padding: 9px 10px; border-radius: 11px; text-decoration: none; color: #334155; font-size: 14px; border: 1px solid transparent; }
    .cl-li:hover { background: #F1F5F9; color: #0F172A; }
    .cl-li.on { background: #E0F2FE; border-color: #BAE6FD; color: #0B1F44; font-weight: 700; }
    .cl-li .ic { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #F1F5F9; color: #64748B; font-size: 12px; }
    .cl-li .ic.done { background: #DCFCE7; color: #16A34A; } .cl-li .ic.live { background: #FEE2E2; color: #DC2626; } .cl-li .ic.lock { color: #94A3B8; }
    .cl-li .tx { flex: 1; min-width: 0; } .cl-li .tx small { display: block; color: #94A3B8; font-size: 11.5px; font-weight: 500; }
    .cl-pg .r { display: flex; justify-content: space-between; font-size: 13.5px; color: #475569; margin-top: 8px; } .cl-pg .r b { color: #0F172A; }
    .cl-cert { background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 1px solid #FCD34D; border-radius: 16px; padding: 16px; text-align: center; margin-bottom: 18px; }
    .cl-cert i { font-size: 34px; color: #B45309; } .cl-cert b { display: block; font-size: 16px; color: #78350F; margin: 6px 0 10px; }
    .tk { border: 1px solid #E3EAF3; border-radius: 16px; padding: 16px 18px; margin-bottom: 14px; background: #fff; }
    .tk-h { display: flex; gap: 10px; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; }
    .tk-h b { font-size: 16px; color: #0F172A; display: flex; gap: 8px; align-items: center; }
    .tk-in { color: #475569; font-size: 14.5px; line-height: 1.6; white-space: pre-line; margin: 8px 0 10px; }
    .tk-sub { background: #F8FAFC; border: 1px solid #EEF2F7; border-radius: 12px; padding: 10px 12px; font-size: 13.5px; color: #334155; margin-bottom: 10px; overflow-wrap: anywhere; }
    .tk-sub a { color: #0369A1; font-weight: 700; }
    .tk-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; }
    .tk-form input, .tk-form textarea { border: 1.5px solid #DCE6F0; border-radius: 10px; padding: 9px 12px; font-size: 14px; font-family: inherit; outline: none; width: 100%; }
    .tk-form textarea { grid-column: 1 / -1; min-height: 60px; resize: vertical; }
    .qz-q { border: 1px solid #EEF2F7; border-radius: 12px; padding: 12px 14px; margin-bottom: 10px; }
    .qz-q p { margin: 0 0 8px; font-weight: 700; color: #0F172A; font-size: 14.5px; }
    .qz-o { display: flex; align-items: center; gap: 9px; padding: 7px 10px; border-radius: 9px; cursor: pointer; font-size: 14px; color: #334155; border: 1px solid transparent; }
    .qz-o:hover { background: #F8FAFC; }
    .qz-o input { accent-color: #0EA5E9; width: 17px; height: 17px; }
    .qz-o.right { background: #DCFCE7; border-color: #86EFAC; color: #14532D; } .qz-o.wrong { background: #FEE2E2; border-color: #FCA5A5; color: #7F1D1D; }
    .qz-res { font-weight: 800; padding: 10px 12px; border-radius: 11px; margin-top: 8px; display: none; }
    .qz-res.ok { display: block; background: #DCFCE7; color: #14532D; } .qz-res.no { display: block; background: #FEF3C7; color: #78350F; }
    .cl-prev { background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; border-radius: 12px; padding: 10px 14px; font-size: 14px; font-weight: 600; margin-bottom: 14px; }
    @media (max-width: 980px) { .cl-g { grid-template-columns: minmax(0, 1fr); } .cl-side { position: static; } .tk-form { grid-template-columns: minmax(0, 1fr); } }
</style>
<?= csrf_field() ?>
<div class="cx-page">
    <div class="cx-wrap">
        <div class="cl-top">
            <div>
                <a class="cx-back" href="<?= $learner ? '/Course/My' : '/Course/Build/' . (int)$c->Id ?>"><i class="fas fa-arrow-left"></i> <?= $learner ? 'আমার কোর্স' : 'কোর্স সাজানো' ?></a>
                <h1 class="cx-h1"><?= e($c->Title) ?></h1>
            </div>
            <a class="cx-btn ghost sm" href="/Course/Details/<?= (int)$c->Id ?>"><i class="fas fa-circle-info"></i> কোর্সের বিবরণ</a>
        </div>
        <?php if ($manager && !$learner): ?><div class="cl-prev"><i class="fas fa-eye"></i> আপনি শিক্ষক / অ্যাডমিন হিসেবে প্রিভিউ দেখছেন — জমা / কুইজ এখানে দেওয়া যাবে না।</div><?php endif; ?>
        <div class="cl-g">
            <div>
                <?php if (!$cur): ?>
                    <div class="cx-card cx-empty"><i class="fas fa-hourglass-half"></i>শিক্ষক এখনো কোনো ক্লাস যোগ করেননি — ক্লাস যোগ হলে নোটিফিকেশন পাবেন।</div>
                <?php else: ?>
                    <div class="cl-player" id="clPlayer">
                        <?php if ($cur->Kind === 'recorded' && $cur->Locked): ?>
                            <div class="cl-ph"><i class="fas fa-lock big"></i><h3>এই ক্লাস এখনো খোলেনি</h3><p>খুলবে <?= e(fmt_date($cur->UnlockAt, 'd M Y, h:i A')) ?> এ</p><div class="cl-cd" data-cd="<?= e(date('c', $cur->UnlockTs)) ?>"></div></div>
                        <?php elseif ($cur->RecordingUrl && ($cur->Kind === 'recorded' || $cur->LiveState === 'ended')): ?>
                            <iframe src="<?= e($cur->RecordingUrl) ?>" allow="autoplay; fullscreen" allowfullscreen title="<?= e($cur->Title) ?>"></iframe>
                        <?php elseif ($cur->Kind === 'recorded'): ?>
                            <div class="cl-ph"><i class="fas <?= isset($proc[$cur->Id]) ? 'fa-gears' : 'fa-video-slash' ?> big"></i>
                                <h3><?= isset($proc[$cur->Id]) ? 'রেকর্ডিং তৈরি হচ্ছে...' : 'ভিডিও এখনো আসেনি' ?></h3>
                                <p><?= isset($proc[$cur->Id]) ? 'ক্লাসের রেকর্ডিং প্রসেস হচ্ছে — কয়েক মিনিট পর পেজটা রিফ্রেশ করুন।' : 'শিক্ষক ক্লাসটা রেকর্ড করলে এখানে দেখতে পাবেন।' ?></p>
                                <?php if ($manager): ?><a class="cx-btn live" href="/Live/Record/<?= (int)$cur->Id ?>"><i class="fas fa-circle-dot"></i> এখনই রেকর্ড করুন</a><?php endif; ?></div>
                        <?php elseif ($cur->LiveState === 'upcoming' || $cur->LiveState === 'none'): ?>
                            <div class="cl-ph"><i class="fas fa-tower-broadcast big" style="color:#FCA5A5;"></i><h3>লাইভ ক্লাস</h3><p><?= e(fmt_date($cur->StartsAt, 'd M Y, h:i A')) ?> এ শুরু হবে · <?= (int)$cur->DurationMin ?> মিনিট</p>
                                <div class="cl-cd" data-cd="<?= e(date('c', $cur->StartTs)) ?>"></div>
                                <?php if ($manager): ?><a class="cx-btn live" href="/Live/Lesson/<?= (int)$cur->Id ?>"><i class="fas fa-tower-broadcast"></i> এখনই লাইভ শুরু করুন</a><?php else: ?><span class="cx-pill" style="background:rgba(255,255,255,.12);color:#fff;">শুরুর ১৫ মিনিট আগে "যোগ দিন" বাটন আসবে</span><?php endif; ?></div>
                        <?php elseif ($cur->LiveState === 'open'): ?>
                            <div class="cl-ph"><i class="fas fa-tower-broadcast big" style="color:#F87171;"></i><h3>লাইভ ক্লাস <?= $cur->StartTs <= time() ? 'চলছে' : 'কিছুক্ষণের মধ্যে' ?></h3><p><?= e(fmt_date($cur->StartsAt, 'h:i A')) ?> থেকে · <?= (int)$cur->DurationMin ?> মিনিট</p>
                                <a class="cx-btn live lg" href="/Live/Lesson/<?= (int)$cur->Id ?>" target="_blank" rel="noopener"><i class="fas fa-<?= $manager ? 'tower-broadcast' : 'right-to-bracket' ?>"></i> <?= $manager ? 'লাইভ শুরু করুন' : 'লাইভ ক্লাসে যোগ দিন' ?></a></div>
                        <?php else: ?>
                            <div class="cl-ph"><i class="fas fa-gears big"></i><h3>লাইভ ক্লাস শেষ হয়েছে</h3><p><?= $cur->LiveStartedAt ? 'রেকর্ডিং তৈরি হলে এখানে দেখতে পাবেন — কিছুক্ষণ পর পেজটা রিফ্রেশ করুন।' : 'এই ক্লাসটা হয়নি বা রেকর্ডিং নেই।' ?></p></div>
                        <?php endif; ?>
                    </div>
                    <div class="cl-head">
                        <div><span class="cx-pill <?= $cur->Kind === 'live' ? 'live' : 'blue' ?>"><?= $cur->Kind === 'live' ? '● লাইভ ক্লাস' : 'রেকর্ড ক্লাস' ?></span> <span class="cx-pill">ক্লাস <?= $idx + 1 ?>/<?= count($lessons) ?></span>
                            <h2 style="margin-top:8px;"><?= e($cur->Title) ?></h2></div>
                        <?php if ($learner): ?>
                            <?php if (isset($done[$cur->Id])): ?><span class="cx-pill ok" style="font-size:13px;padding:7px 12px;"><i class="fas fa-circle-check"></i> শেষ করেছেন</span>
                            <?php elseif (($cur->Kind === 'recorded' && !$cur->Locked && $cur->RecordingUrl) || ($cur->Kind === 'live' && in_array($cur->LiveState, ['open', 'ended'], true))): ?>
                                <button type="button" class="cx-btn ok" id="clDone" data-id="<?= (int)$cur->Id ?>"><i class="fas fa-check"></i> ক্লাস শেষ করেছি</button><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!is_blank($cur->Description)): ?><p class="cl-desc"><?= e($cur->Description) ?></p><?php endif; ?>
                    <?php if ($cur->RecordingUrl): ?><p style="margin:8px 0 0;font-size:13px;"><a href="<?= e($cur->RecordingUrl) ?>" target="_blank" rel="noopener" style="color:#0369A1;font-weight:700;"><i class="fas fa-up-right-from-square"></i> বড় করে / নতুন ট্যাবে দেখুন</a></p><?php endif; ?>
                    <div class="cl-nav">
                        <?php if ($prev): ?><a class="cx-btn ghost sm" href="?lesson=<?= (int)$prev->Id ?>"><i class="fas fa-arrow-left"></i> আগের ক্লাস</a><?php else: ?><span></span><?php endif; ?>
                        <?php if ($next): ?><a class="cx-btn ghost sm" href="?lesson=<?= (int)$next->Id ?>">পরের ক্লাস <i class="fas fa-arrow-right"></i></a><?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php foreach ([['এই ক্লাসের কাজ', $curTasks], ['কোর্সের কাজ', $general]] as [$hdr, $list]): if (!$list) continue; ?>
                    <h3 style="font-size:17px;font-weight:800;color:#0B1F44;margin:8px 0 12px;"><i class="fas fa-clipboard-check" style="color:#0EA5E9;"></i> <?= $hdr ?></h3>
                    <?php foreach ($list as $t): $s = $subs[$t->Id] ?? null; $overdue = $t->DueAt && strtotime((string)$t->DueAt) < time(); ?>
                        <div class="tk" id="tk-<?= (int)$t->Id ?>">
                            <div class="tk-h">
                                <b><i class="fas <?= $t->Kind === 'quiz' ? 'fa-list-check' : 'fa-paperclip' ?>" style="color:<?= $t->Kind === 'quiz' ? '#7C3AED' : '#0EA5E9' ?>;"></i> <?= e($t->Title) ?></b>
                                <span>
                                    <?php if ($t->Kind === 'quiz'): ?><?= $s ? ((int)$s->Passed ? '<span class="cx-pill ok">পাস · ' . (int)$s->Score . '/' . (int)$s->Total . '</span>' : '<span class="cx-pill warn">' . (int)$s->Score . '/' . (int)$s->Total . ' — আবার দিন</span>') : '<span class="cx-pill purple">' . count($t->QuestionList) . 'টা প্রশ্ন</span>' ?>
                                    <?php else: ?><?= $s ? ($s->Status === 'reviewed' ? '<span class="cx-pill ok">দেখা হয়েছে' . ($s->Marks !== null ? ' · ' . (int)$s->Marks . '/' . (int)$t->MaxMarks : '') . '</span>' : '<span class="cx-pill blue">জমা দিয়েছেন</span>') : '<span class="cx-pill ' . ($overdue ? 'no' : 'warn') . '">জমা বাকি</span>' ?><?php endif; ?>
                                    <?php if ($t->DueAt): ?> <span class="cx-pill <?= $overdue ? 'no' : '' ?>"><i class="far fa-clock"></i> <?= e(fmt_date($t->DueAt, 'd M, h:i A')) ?></span><?php endif; ?>
                                </span>
                            </div>
                            <?php if (!is_blank($t->Instructions)): ?><div class="tk-in"><?= e($t->Instructions) ?></div><?php endif; ?>
                            <?php if ($t->Kind === 'assignment'): ?>
                                <?php if ($s): ?><div class="tk-sub"><i class="fas fa-link"></i> জমা: <a href="<?= e($s->Link) ?>" target="_blank" rel="noopener nofollow"><?= e(mb_strimwidth((string)$s->Link, 0, 70, '…')) ?></a>
                                    <?php if (!is_blank($s->Note)): ?><br><small style="color:#64748B;"><?= e($s->Note) ?></small><?php endif; ?>
                                    <?php if (!is_blank($s->Feedback)): ?><br><b style="color:#15803D;"><i class="fas fa-comment-dots"></i> শিক্ষকের মন্তব্য:</b> <?= e($s->Feedback) ?><?php endif; ?></div><?php endif; ?>
                                <?php if ($learner): ?>
                                    <form class="tk-form" data-asg="<?= (int)$t->Id ?>">
                                        <input type="url" name="link" maxlength="500" placeholder="Google Drive / GitHub / যেকোনো লিংক (https://...)" value="<?= e($s->Link ?? '') ?>" required />
                                        <button type="submit" class="cx-btn pri"><i class="fas fa-paper-plane"></i> <?= $s ? 'আবার জমা দিন' : 'জমা দিন' ?></button>
                                        <textarea name="note" maxlength="1000" placeholder="শিক্ষকের জন্য কিছু লিখতে চাইলে (ঐচ্ছিক)"><?= e($s->Note ?? '') ?></textarea>
                                    </form>
                                <?php endif; ?>
                            <?php else: $given = $s ? (json_decode((string)$s->Answers, true) ?: []) : []; ?>
                                <form data-quiz="<?= (int)$t->Id ?>">
                                    <?php foreach ($t->QuestionList as $qi => $q): ?>
                                        <div class="qz-q"><p><?= $qi + 1 ?>. <?= e($q['q']) ?></p>
                                            <?php foreach ($q['o'] as $oi => $o): ?><label class="qz-o"><input type="radio" name="q<?= $qi ?>" value="<?= $oi ?>"<?= isset($given[$qi]) && (int)$given[$qi] === $oi ? ' checked' : '' ?><?= $learner ? '' : ' disabled' ?> /> <?= e($o) ?><?= !$learner && $manager && (int)$q['a'] === $oi ? ' <span class="cx-pill ok">সঠিক</span>' : '' ?></label><?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($learner): ?><button type="submit" class="cx-btn pri"><i class="fas fa-check-double"></i> <?= $s ? 'আবার দিন' : 'উত্তর জমা দিন' ?></button><?php endif; ?>
                                    <div class="qz-res"></div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <aside class="cl-side">
                <?php if ($cert !== ''): ?>
                    <div class="cl-cert"><i class="fas fa-award"></i><b>অভিনন্দন! কোর্স সম্পূর্ণ করেছেন</b><a class="cx-btn warn" href="/Course/Certificate/<?= e($cert) ?>" target="_blank" rel="noopener"><i class="fas fa-download"></i> সার্টিফিকেট</a></div>
                <?php endif; ?>
                <?php if ($learner): ?>
                <div class="cx-card cl-pg" id="clPg">
                    <h3 style="margin-bottom:10px;"><i class="fas fa-chart-line"></i> আপনার অগ্রগতি <span class="cx-pill ok" style="margin-left:auto;" data-pg="percent"><?= (int)$pg['percent'] ?>%</span></h3>
                    <div class="cx-bar"><div data-pg="bar" style="width:<?= (int)$pg['percent'] ?>%;"></div></div>
                    <div class="r"><span><i class="fas fa-circle-play"></i> ক্লাস</span><b data-pg="lessons"><?= $pg['lessons'][0] ?>/<?= $pg['lessons'][1] ?></b></div>
                    <div class="r"><span><i class="fas fa-paperclip"></i> অ্যাসাইনমেন্ট</span><b data-pg="assignments"><?= $pg['assignments'][0] ?>/<?= $pg['assignments'][1] ?></b></div>
                    <div class="r"><span><i class="fas fa-list-check"></i> কুইজ (পাস)</span><b data-pg="quizzes"><?= $pg['quizzes'][0] ?>/<?= $pg['quizzes'][1] ?></b></div>
                    <?php if ($pg['marks'][1] > 0): ?><div class="r"><span><i class="fas fa-star"></i> অ্যাসাইনমেন্টের নম্বর</span><b><?= $pg['marks'][0] ?>/<?= $pg['marks'][1] ?></b></div><?php endif; ?>
                    <?php if ($cert === ''): ?><p style="font-size:12.5px;color:#94A3B8;margin:10px 0 0;">সব ক্লাস শেষ, সব অ্যাসাইনমেন্ট জমা আর সব কুইজে পাস করলে নিজে থেকেই সার্টিফিকেট পাবেন।</p><?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="cx-card">
                    <h3 style="margin-bottom:10px;"><i class="fas fa-layer-group"></i> ক্লাসগুলো</h3>
                    <div class="cl-ls">
                        <?php foreach ($lessons as $i => $l): $isDone = isset($done[$l->Id]); ?>
                            <a class="cl-li<?= $cur && $l->Id === $cur->Id ? ' on' : '' ?>" href="?lesson=<?= (int)$l->Id ?>">
                                <span class="ic <?= $isDone ? 'done' : ($l->Kind === 'live' ? 'live' : ($l->Locked ? 'lock' : '')) ?>"><i class="fas <?= $isDone ? 'fa-check' : ($l->Kind === 'live' ? 'fa-tower-broadcast' : ($l->Locked ? 'fa-lock' : 'fa-play')) ?>"></i></span>
                                <span class="tx"><?= $i + 1 ?>. <?= e($l->Title) ?><small><?= $l->Kind === 'live' ? 'লাইভ · ' . e(fmt_date($l->StartsAt, 'd M, h:i A')) : ($l->Locked ? 'খুলবে ' . e(fmt_date($l->UnlockAt, 'd M, h:i A')) : ($l->RecordingMin ? (int)$l->RecordingMin . ' মিনিট' : 'রেকর্ড ক্লাস')) ?><?= isset($byLesson[$l->Id]) ? ' · ' . count($byLesson[$l->Id]) . 'টা কাজ' : '' ?></small></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
<script>
(function () {
    // কাউন্টডাউন
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    document.querySelectorAll('[data-cd]').forEach(function (el) {
        var t = Date.parse(el.getAttribute('data-cd'));
        (function tick() {
            var s = Math.floor((t - Date.now()) / 1000);
            if (s <= 0) { el.textContent = 'এখনই — পেজ রিফ্রেশ হচ্ছে...'; setTimeout(function () { location.reload(); }, 2500); return; }
            var d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60);
            el.textContent = (d ? d + ' দিন ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s % 60); setTimeout(tick, 1000);
        })();
    });
    function setPg(p, cert) {
        if (!p) return;
        var box = document.getElementById('clPg'); if (!box) return;
        box.querySelector('[data-pg="percent"]').textContent = p.percent + '%';
        box.querySelector('[data-pg="bar"]').style.width = p.percent + '%';
        ['lessons', 'assignments', 'quizzes'].forEach(function (k) { box.querySelector('[data-pg="' + k + '"]').textContent = p[k][0] + '/' + p[k][1]; });
        if (cert) { cxToast('🎉 অভিনন্দন! কোর্স সম্পূর্ণ — সার্টিফিকেট পেয়েছেন'); setTimeout(function () { location.reload(); }, 1600); }
    }
    var dn = document.getElementById('clDone');
    if (dn) dn.addEventListener('click', async function () {
        dn.disabled = true;
        var d = await cxPost('/Course/Done', { lessonId: dn.getAttribute('data-id') });
        if (!d.success) { dn.disabled = false; cxToast(d.message || 'হয়নি।'); return; }
        dn.outerHTML = '<span class="cx-pill ok" style="font-size:13px;padding:7px 12px;"><i class="fas fa-circle-check"></i> শেষ করেছেন</span>';
        setPg(d.progress, d.cert);
    });
    document.querySelectorAll('form[data-asg]').forEach(function (f) {
        f.addEventListener('submit', async function (e) {
            e.preventDefault(); var b = f.querySelector('button'); b.disabled = true;
            var d = await cxPost('/Course/Submit', { taskId: f.getAttribute('data-asg'), link: f.elements.link.value.trim(), note: f.elements.note.value });
            b.disabled = false; cxToast(d.message || '');
            if (d.success) { setPg(d.progress, d.cert); if (!d.cert) setTimeout(function () { location.reload(); }, 900); }
        });
    });
    document.querySelectorAll('form[data-quiz]').forEach(function (f) {
        f.addEventListener('submit', async function (e) {
            e.preventDefault();
            var qs = f.querySelectorAll('.qz-q'), ans = [], miss = false;
            qs.forEach(function (q, i) { var c = q.querySelector('input:checked'); if (!c) miss = true; ans.push(c ? parseInt(c.value, 10) : -1); });
            if (miss) { cxToast('সব প্রশ্নের উত্তর দিন।'); return; }
            var b = f.querySelector('button'); b.disabled = true;
            var d = await cxPost('/Course/Quiz', { taskId: f.getAttribute('data-quiz'), answers: JSON.stringify(ans) });
            b.disabled = false;
            var r = f.querySelector('.qz-res');
            if (!d.success) { r.className = 'qz-res no'; r.textContent = d.message || 'হয়নি।'; return; }
            f.querySelectorAll('.qz-o').forEach(function (o) { o.classList.remove('right', 'wrong'); });
            (d.result || []).forEach(function (res, i) {
                var opts = qs[i].querySelectorAll('.qz-o');
                if (opts[res.correct]) opts[res.correct].classList.add('right');
                if (!res.ok && opts[res.given]) opts[res.given].classList.add('wrong');
            });
            r.className = 'qz-res ' + (d.passed ? 'ok' : 'no'); r.textContent = (d.passed ? '🎉 ' : '') + d.message;
            b.innerHTML = '<i class="fas fa-rotate"></i> আবার দিন';
            setPg(d.progress, d.cert);
        });
    });
})();
</script>
