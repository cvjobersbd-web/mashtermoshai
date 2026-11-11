<?php
/** কোর্স বানানো — ক্লাস (রেকর্ড / লাইভ), আনলকের সময়, অ্যাসাইনমেন্ট, কুইজ, প্রকাশ */
$c = $Model; $lessons = $ViewBag->Lessons ?? []; $tasks = $ViewBag->Tasks ?? []; $proc = $ViewBag->Processing ?? [];
$ViewData['Title'] = 'কোর্স — ' . $c->Title;
$ViewData['Robots'] = 'noindex, nofollow';
$byLesson = []; $general = [];
foreach ($tasks as $t) { if ($t->LessonId) $byLesson[$t->LessonId][] = $t; else $general[] = $t; }
$dtv = fn($v) => $v ? date('Y-m-d\TH:i', strtotime((string)$v)) : '';
$pub = $c->Status === 'published';
$lessonOpts = array_map(fn($l) => ['id' => $l->Id, 't' => $l->Title], $lessons);
$taskJson = function ($t) use ($dtv) {
    return ['id' => $t->Id, 'kind' => $t->Kind, 'lessonId' => $t->LessonId, 'title' => $t->Title, 'instructions' => (string)$t->Instructions, 'dueAt' => $dtv($t->DueAt),
        'maxMarks' => $t->MaxMarks, 'passPercent' => $t->PassPercent, 'questions' => $t->QuestionList];
};
?>
<?php partial('Course/_Styles'); ?>
<style>
    .bd-head { display: grid; grid-template-columns: 220px minmax(0, 1fr); gap: 18px; align-items: center; }
    .bd-head .cx-thumb { border-radius: 14px; }
    .bd-acts { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
    .bd-warn { background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; border-radius: 14px; padding: 12px 16px; font-size: 14px; line-height: 1.6; margin-bottom: 16px; }
    .bd-l { border: 1px solid #E5ECF3; border-radius: 16px; margin-bottom: 12px; background: #fff; overflow: hidden; }
    .bd-lh { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 12px 14px; }
    .bd-n { width: 40px; height: 40px; border-radius: 12px; background: #E0F2FE; color: #0369A1; display: flex; align-items: center; justify-content: center; font-weight: 800; }
    .bd-n.live { background: #FEE2E2; color: #B91C1C; }
    .bd-lt { font-weight: 800; color: #0F172A; font-size: 15.5px; }
    .bd-lm { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; font-size: 12.5px; color: #64748B; margin-top: 4px; }
    .bd-la { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
    .bd-ic { width: 34px; height: 34px; border-radius: 9px; border: none; background: #F1F5F9; color: #475569; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
    .bd-ic:hover { background: #E2E8F0; } .bd-ic.red:hover { background: #FEE2E2; color: #B91C1C; }
    .bd-tasks { border-top: 1px dashed #E5ECF3; padding: 8px 14px 12px 66px; display: flex; flex-direction: column; gap: 6px; }
    .bd-t { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #334155; flex-wrap: wrap; }
    .bd-t .nm { font-weight: 700; flex: 1; min-width: 160px; }
    .bd-add { display: flex; gap: 6px; flex-wrap: wrap; }
    .bd-add button { border: 1px dashed #CBD5E1; background: #fff; color: #475569; border-radius: 9px; padding: 5px 11px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit; }
    .bd-add button:hover { border-color: #0EA5E9; color: #0369A1; }
    .bd-kind { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    .bd-kind label { border: 2px solid #E2E8F0; border-radius: 13px; padding: 12px; cursor: pointer; display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: #64748B; }
    .bd-kind label b { display: block; color: #0F172A; font-size: 14.5px; }
    .bd-kind input { margin-top: 4px; }
    .bd-kind label:has(input:checked) { border-color: #0EA5E9; background: #F0F9FF; }
    .bd-q { border: 1px solid #E5ECF3; border-radius: 13px; padding: 12px; margin-bottom: 10px; background: #FAFCFF; }
    .bd-q-h { display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: 13px; color: #0369A1; margin-bottom: 8px; }
    .bd-o { display: grid; grid-template-columns: 26px minmax(0, 1fr) 30px; gap: 6px; align-items: center; margin-bottom: 6px; }
    .bd-o input[type=text] { border: 1.5px solid #DCE6F0; border-radius: 9px; padding: 7px 10px; font-size: 14px; font-family: inherit; width: 100%; }
    .bd-o input[type=radio] { width: 18px; height: 18px; accent-color: #16A34A; }
    #lsDlg [data-only], #tkDlg [data-only] { display: none; }
    #lsDlg[data-kind="live"] [data-only="live"], #lsDlg[data-kind="recorded"] [data-only="recorded"], #tkDlg[data-kind="quiz"] [data-only="quiz"], #tkDlg[data-kind="assignment"] [data-only="assignment"] { display: block; }
    @media (max-width: 760px) { .bd-head { grid-template-columns: minmax(0, 1fr); } .bd-lh { grid-template-columns: 34px minmax(0, 1fr); } .bd-la { grid-column: 1 / -1; justify-content: flex-start; } .bd-tasks { padding-left: 14px; } .bd-kind { grid-template-columns: 1fr; } }
</style>
<?= csrf_field() ?>
<div class="cx-page">
    <div class="cx-wrap narrow">
        <a class="cx-back" href="/Course/Manage"><i class="fas fa-arrow-left"></i> আমার কোর্স</a>
        <div class="cx-card">
            <div class="bd-head">
                <?= course_thumb_html($c) ?>
                <div>
                    <h1 class="cx-h1" style="font-size:24px;"><?= e($c->Title) ?></h1>
                    <div class="bd-lm" style="font-size:13.5px;margin-top:8px;">
                        <?= $c->AdminHidden ? '<span class="cx-pill no"><i class="fas fa-eye-slash"></i> অ্যাডমিন লুকিয়েছেন</span>' : ($pub ? '<span class="cx-pill ok">● প্রকাশিত</span>' : '<span class="cx-pill warn">Draft — এখনো কেউ দেখতে পাচ্ছে না</span>') ?>
                        <?= course_price_html($c) ?>
                        <span><i class="fas fa-user-graduate"></i> <?= (int)$ViewBag->Students ?> জন শিক্ষার্থী</span>
                    </div>
                    <div class="bd-acts">
                        <button type="button" class="cx-btn <?= $pub ? 'ghost' : 'ok' ?> sm" id="bdPub" data-on="<?= $pub ? 0 : 1 ?>"><i class="fas <?= $pub ? 'fa-eye-slash' : 'fa-rocket' ?>"></i> <?= $pub ? 'Draft এ নিন' : 'প্রকাশ করুন' ?></button><?php if (!$pub && !$c->PublishPaid && ($c->TeacherRole ?? '') !== 'Admin'): $pf = course_publish_fee($c->Kind); ?><small style="display:block;margin-top:4px;font-size:12px;font-weight:700;color:<?= $pf === null ? '#B45309' : '#475569' ?>;"><?= $pf === null ? '🔒 প্রকাশ এখনো চালু হয়নি' : ($pf === 0 ? 'প্রকাশ ফ্রি' : 'প্রকাশ ফি ৳' . $pf . ' (একবার)') ?></small><?php endif; ?>
                        <a class="cx-btn ghost sm" href="/Course/Edit/<?= (int)$c->Id ?>"><i class="fas fa-pen"></i> তথ্য / দাম</a>
                        <a class="cx-btn ghost sm" href="/Course/Students/<?= (int)$c->Id ?>"><i class="fas fa-users"></i> শিক্ষার্থী ও জমা</a>
                        <a class="cx-btn ghost sm" href="/Course/Details/<?= (int)$c->Id ?>" target="_blank" rel="noopener"><i class="fas fa-eye"></i> পেজ দেখুন</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$ViewBag->BbbReady): ?>
            <div class="bd-warn"><i class="fas fa-triangle-exclamation"></i> লাইভ / রেকর্ডিং সার্ভার এখনো চালু করা হয়নি — ক্লাস যোগ করে রাখতে পারবেন, কিন্তু রেকর্ড বা লাইভ শুরু করা যাবে না। <?= admin_is_super() ? '<a href="/Admin/LiveSettings" style="font-weight:700;">Live Class সেটিং →</a>' : 'অ্যাডমিনকে জানান।' ?></div>
        <?php endif; ?>

        <div class="cx-card">
            <h3><i class="fas fa-circle-play"></i> ক্লাসগুলো <span class="cx-pill"><?= count($lessons) ?>টা</span>
                <span style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;">
                    <button type="button" class="cx-btn pri sm" data-new-lesson="recorded"><i class="fas fa-circle-dot"></i> রেকর্ড ক্লাস</button>
                    <button type="button" class="cx-btn live sm" data-new-lesson="live"><i class="fas fa-tower-broadcast"></i> লাইভ ক্লাস</button>
                </span></h3>
            <p class="cx-sub" style="margin:-6px 0 14px;font-size:13.5px;">রেকর্ড ক্লাস: "রেকর্ড করুন" চাপলে আপনার জন্য একটা রুম খুলবে — ক্লাস নিয়ে <b>End meeting</b> চাপুন, কিছুক্ষণ পর ভিডিও নিজে থেকে এখানে চলে আসবে। চাইলে আনলকের সময় দিন — তার আগে শিক্ষার্থী দেখতে পাবে না। প্রতিটা ক্লাসের পর অ্যাসাইনমেন্ট বা কুইজ যোগ করতে পারবেন।</p>
            <?php if (!$lessons): ?><div class="cx-empty"><i class="fas fa-video"></i>এখনো কোনো ক্লাস নেই — উপরের বাটন দিয়ে প্রথম ক্লাস যোগ করুন।</div><?php endif; ?>
            <?php foreach ($lessons as $i => $l): $live = $l->Kind === 'live';
                $lj = ['id' => $l->Id, 'kind' => $l->Kind, 'title' => $l->Title, 'description' => (string)$l->Description, 'unlockAt' => $dtv($l->UnlockAt), 'startsAt' => $dtv($l->StartsAt), 'duration' => $l->DurationMin]; ?>
                <div class="bd-l" data-lesson="<?= (int)$l->Id ?>">
                    <div class="bd-lh">
                        <div class="bd-n<?= $live ? ' live' : '' ?>"><?= $i + 1 ?></div>
                        <div>
                            <div class="bd-lt"><?= e($l->Title) ?></div>
                            <div class="bd-lm">
                                <?php if ($live): ?>
                                    <span class="cx-pill live"><i class="fas fa-tower-broadcast"></i> লাইভ</span>
                                    <span><i class="far fa-calendar"></i> <?= e(fmt_date($l->StartsAt, 'd M Y, h:i A')) ?> · <?= (int)$l->DurationMin ?> মিনিট</span>
                                    <?= $l->LiveState === 'ended' ? '<span class="cx-pill">শেষ</span>' : ($l->LiveState === 'open' ? '<span class="cx-pill ok">এখন চলতে পারে</span>' : '') ?>
                                    <?= $l->RecordingUrl ? '<span class="cx-pill ok"><i class="fas fa-film"></i> রেকর্ডিং আছে</span>' : '' ?>
                                <?php else: ?>
                                    <span class="cx-pill blue"><i class="fas fa-circle-play"></i> রেকর্ড ক্লাস</span>
                                    <?php if ($l->RecordingUrl): ?><span class="cx-pill ok"><i class="fas fa-check"></i> ভিডিও রেডি<?= $l->RecordingMin ? ' · ' . (int)$l->RecordingMin . ' মিনিট' : '' ?></span>
                                    <?php elseif (isset($proc[$l->Id])): ?><span class="cx-pill warn"><i class="fas fa-spinner fa-spin"></i> ভিডিও তৈরি হচ্ছে...</span>
                                    <?php else: ?><span class="cx-pill no">এখনো রেকর্ড হয়নি</span><?php endif; ?>
                                    <?= $l->Locked ? '<span><i class="fas fa-lock"></i> খুলবে ' . e(fmt_date($l->UnlockAt, 'd M Y, h:i A')) . '</span>' : '' ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="bd-la">
                            <?php if ($live): ?>
                                <?php if ($l->LiveState !== 'ended'): ?><a class="cx-btn live sm" href="/Live/Lesson/<?= (int)$l->Id ?>" target="_blank" rel="noopener"><i class="fas fa-tower-broadcast"></i> লাইভ শুরু করুন</a><?php endif; ?>
                            <?php elseif (!$l->RecordingUrl): ?>
                                <a class="cx-btn pri sm" href="/Live/Record/<?= (int)$l->Id ?>"><i class="fas fa-circle-dot"></i> রেকর্ড করুন</a>
                            <?php else: ?>
                                <a class="cx-btn ghost sm" href="<?= e($l->RecordingUrl) ?>" target="_blank" rel="noopener"><i class="fas fa-play"></i> দেখুন</a>
                                <button type="button" class="bd-ic" title="আবার রেকর্ড" data-rerec="<?= (int)$l->Id ?>"><i class="fas fa-rotate"></i></button>
                            <?php endif; ?>
                            <button type="button" class="bd-ic" title="উপরে" data-move="<?= (int)$l->Id ?>" data-dir="-1"<?= $i === 0 ? ' disabled' : '' ?>><i class="fas fa-arrow-up"></i></button>
                            <button type="button" class="bd-ic" title="নিচে" data-move="<?= (int)$l->Id ?>" data-dir="1"<?= $i === count($lessons) - 1 ? ' disabled' : '' ?>><i class="fas fa-arrow-down"></i></button>
                            <button type="button" class="bd-ic" title="এডিট" data-edit-lesson='<?= e(json_encode($lj, JSON_UNESCAPED_UNICODE)) ?>'><i class="fas fa-pen"></i></button>
                            <button type="button" class="bd-ic red" title="মুছুন" data-del-lesson="<?= (int)$l->Id ?>"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="bd-tasks">
                        <?php foreach ($byLesson[$l->Id] ?? [] as $t): ?>
                            <div class="bd-t">
                                <span class="cx-pill <?= $t->Kind === 'quiz' ? 'purple' : 'warn' ?>"><i class="fas <?= $t->Kind === 'quiz' ? 'fa-list-check' : 'fa-paperclip' ?>"></i> <?= $t->Kind === 'quiz' ? 'কুইজ · ' . count($t->QuestionList) . 'টা প্রশ্ন' : 'অ্যাসাইনমেন্ট · ' . (int)$t->MaxMarks . ' নম্বর' ?></span>
                                <span class="nm"><?= e($t->Title) ?></span>
                                <?= $t->DueAt ? '<small style="color:#94A3B8;"><i class="far fa-clock"></i> ' . e(fmt_date($t->DueAt, 'd M, h:i A')) . '</small>' : '' ?>
                                <button type="button" class="bd-ic" data-edit-task='<?= e(json_encode($taskJson($t), JSON_UNESCAPED_UNICODE)) ?>'><i class="fas fa-pen"></i></button>
                                <button type="button" class="bd-ic red" data-del-task="<?= (int)$t->Id ?>"><i class="fas fa-trash"></i></button>
                            </div>
                        <?php endforeach; ?>
                        <div class="bd-add"><button type="button" data-new-task="assignment" data-lesson-id="<?= (int)$l->Id ?>"><i class="fas fa-plus"></i> অ্যাসাইনমেন্ট</button><button type="button" data-new-task="quiz" data-lesson-id="<?= (int)$l->Id ?>"><i class="fas fa-plus"></i> কুইজ</button></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cx-card">
            <h3><i class="fas fa-clipboard-list"></i> পুরো কোর্সের কাজ <small style="color:#94A3B8;font-weight:500;font-size:13px;">(কোনো নির্দিষ্ট ক্লাসের না — যেমন ফাইনাল প্রজেক্ট / ফাইনাল পরীক্ষা)</small></h3>
            <div class="bd-tasks" style="border:none;padding:0;">
                <?php foreach ($general as $t): ?>
                    <div class="bd-t">
                        <span class="cx-pill <?= $t->Kind === 'quiz' ? 'purple' : 'warn' ?>"><i class="fas <?= $t->Kind === 'quiz' ? 'fa-list-check' : 'fa-paperclip' ?>"></i> <?= $t->Kind === 'quiz' ? 'কুইজ · ' . count($t->QuestionList) . 'টা প্রশ্ন' : 'অ্যাসাইনমেন্ট · ' . (int)$t->MaxMarks . ' নম্বর' ?></span>
                        <span class="nm"><?= e($t->Title) ?></span>
                        <button type="button" class="bd-ic" data-edit-task='<?= e(json_encode($taskJson($t), JSON_UNESCAPED_UNICODE)) ?>'><i class="fas fa-pen"></i></button>
                        <button type="button" class="bd-ic red" data-del-task="<?= (int)$t->Id ?>"><i class="fas fa-trash"></i></button>
                    </div>
                <?php endforeach; ?>
                <div class="bd-add"><button type="button" data-new-task="assignment" data-lesson-id="0"><i class="fas fa-plus"></i> অ্যাসাইনমেন্ট</button><button type="button" data-new-task="quiz" data-lesson-id="0"><i class="fas fa-plus"></i> কুইজ</button></div>
            </div>
            <p class="cx-sub" style="font-size:13px;margin-top:12px;"><i class="fas fa-award" style="color:#F59E0B;"></i> শিক্ষার্থী সব ক্লাস দেখে, সব অ্যাসাইনমেন্ট জমা দিয়ে আর সব কুইজে পাস করলে নিজে থেকেই সার্টিফিকেট পাবে।</p>
        </div>
    </div>
</div>

<!-- ক্লাস যোগ / এডিট -->
<div class="cx-dlg" id="lsDlg" data-kind="recorded">
    <form class="cx-dlg-box" id="lsForm" autocomplete="off">
        <h3><span id="lsTitle">নতুন ক্লাস</span><button type="button" data-close><i class="fas fa-xmark"></i></button></h3>
        <input type="hidden" name="courseId" value="<?= (int)$c->Id ?>" /><input type="hidden" name="id" value="0" />
        <div class="bd-kind" id="lsKind">
            <label><input type="radio" name="kind" value="recorded" checked /><span><b><i class="fas fa-circle-play"></i> রেকর্ড ক্লাস</b>আপনি রেকর্ড করবেন, শিক্ষার্থী যখন খুশি দেখবে</span></label>
            <label><input type="radio" name="kind" value="live" /><span><b><i class="fas fa-tower-broadcast"></i> লাইভ ক্লাস</b>নির্দিষ্ট সময়ে সবাই একসাথে — রেকর্ডিং পরে থেকে যাবে</span></label>
        </div>
        <div class="cx-f" data-f="title"><label>ক্লাসের নাম *</label><input type="text" name="title" maxlength="200" placeholder="যেমন: অধ্যায় ১ — ভেক্টর" /></div>
        <div class="cx-f"><label>বিবরণ <small>(ঐচ্ছিক)</small></label><textarea name="description" rows="3" maxlength="3000" placeholder="এই ক্লাসে কী থাকছে, কোনো নোট / লিংক..."></textarea></div>
        <div class="cx-f" data-only="recorded" data-f="unlockAt"><label>কখন খুলবে <small>(ঐচ্ছিক — খালি রাখলে সাথে সাথেই)</small></label><input type="datetime-local" name="unlockAt" /></div>
        <div class="cx-grid2" data-only="live">
            <div class="cx-f" data-f="startsAt"><label>লাইভ ক্লাসের তারিখ ও সময় *</label><input type="datetime-local" name="startsAt" /></div>
            <div class="cx-f"><label>কতক্ষণ (মিনিট)</label><input type="number" name="duration" min="15" max="300" value="60" /></div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;"><button type="submit" class="cx-btn pri"><i class="fas fa-floppy-disk"></i> সেভ করুন</button><span class="cx-msg" data-msg></span></div>
        <p class="cx-sub" style="font-size:12.5px;margin-top:10px;">নতুন ক্লাস যোগ করলে ভর্তি হওয়া সব শিক্ষার্থী নোটিফিকেশন পাবে।</p>
    </form>
</div>

<!-- অ্যাসাইনমেন্ট / কুইজ -->
<div class="cx-dlg" id="tkDlg" data-kind="assignment">
    <form class="cx-dlg-box" id="tkForm" autocomplete="off">
        <h3><span id="tkTitle">নতুন অ্যাসাইনমেন্ট</span><button type="button" data-close><i class="fas fa-xmark"></i></button></h3>
        <input type="hidden" name="courseId" value="<?= (int)$c->Id ?>" /><input type="hidden" name="id" value="0" /><input type="hidden" name="kind" value="assignment" />
        <div class="cx-f"><label>কোন ক্লাসের পর</label><select name="lessonId"><option value="0">পুরো কোর্সের (নির্দিষ্ট ক্লাসের না)</option><?php foreach ($lessonOpts as $i => $o): ?><option value="<?= (int)$o['id'] ?>"><?= $i + 1 ?>. <?= e($o['t']) ?></option><?php endforeach; ?></select></div>
        <div class="cx-f" data-f="title"><label>শিরোনাম *</label><input type="text" name="title" maxlength="200" /></div>
        <div class="cx-f"><label>নির্দেশনা / কী করতে হবে <small>(ঐচ্ছিক)</small></label><textarea name="instructions" rows="3" maxlength="3000" placeholder="যেমন: সমাধানগুলো Google Drive এ PDF করে আপলোড করে লিংক দিন"></textarea></div>
        <div class="cx-grid2">
            <div class="cx-f" data-f="dueAt"><label>জমার শেষ সময় <small>(ঐচ্ছিক)</small></label><input type="datetime-local" name="dueAt" /></div>
            <div class="cx-f" data-only="assignment"><label>পূর্ণ নম্বর</label><input type="number" name="maxMarks" min="1" max="1000" value="10" /></div>
            <div class="cx-f" data-only="quiz"><label>পাস মার্ক % <small>(খালি = কোর্সের <?= (int)$c->PassPercent ?>%)</small></label><input type="number" name="passPercent" min="0" max="100" /></div>
        </div>
        <div data-only="assignment"><p class="cx-sub" style="font-size:12.5px;margin:-4px 0 12px;"><i class="fab fa-google-drive"></i> শিক্ষার্থী Google Drive / GitHub বা যেকোনো https লিংক দিয়ে জমা দেবে।</p></div>
        <div data-only="quiz">
            <div id="tkQs"></div>
            <button type="button" class="cx-btn ghost sm" id="tkAddQ" style="margin-bottom:14px;"><i class="fas fa-plus"></i> প্রশ্ন যোগ করুন</button>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;"><button type="submit" class="cx-btn pri"><i class="fas fa-floppy-disk"></i> সেভ করুন</button><span class="cx-msg" data-msg></span></div>
    </form>
</div>

<script>
(function () {
    var CID = <?= (int)$c->Id ?>;
    function reload() { location.reload(); }
    function open(d) { d.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function close(d) { d.classList.remove('open'); document.body.style.overflow = ''; }
    document.querySelectorAll('.cx-dlg').forEach(function (d) {
        d.addEventListener('click', function (e) { if (e.target === d || e.target.closest('[data-close]')) close(d); });
    });
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function showErr(form, d) {
        var m = form.querySelector('[data-msg]'); m.className = 'cx-msg ' + (d.success ? 'ok' : 'err'); m.textContent = d.message || '';
        form.querySelectorAll('.cx-f.bad').forEach(function (x) { x.classList.remove('bad'); });
        if (!d.success && d.field) { var x = form.querySelector('[data-f="' + d.field + '"]'); if (x) x.classList.add('bad'); }
    }

    // ---------- প্রকাশ
    document.getElementById('bdPub').addEventListener('click', async function () {
        this.disabled = true; var d = await cxPost('/Course/Publish', { id: CID, on: this.dataset.on });
        if (!d.success && d.needPay) { if (confirm((d.message || '') + '\n\nএখনই পেমেন্ট পেজে যাবেন?')) { location.href = d.needPay; return; } this.disabled = false; return; }
        if (!d.success && d.locked) { alert(d.message || ''); this.disabled = false; return; }
        cxToast(d.message || '');
        if (d.success) setTimeout(reload, 900); else this.disabled = false;
    });

    // ---------- ক্লাস
    var ld = document.getElementById('lsDlg'), lf = document.getElementById('lsForm');
    function setLKind(k) { ld.dataset.kind = k; lf.querySelector('input[name=kind][value="' + k + '"]').checked = true; }
    lf.querySelectorAll('input[name=kind]').forEach(function (r) { r.addEventListener('change', function () { setLKind(r.value); }); });
    function lessonDlg(x) {
        lf.reset(); lf.querySelector('[data-msg]').textContent = '';
        lf.elements.id.value = x.id || 0; setLKind(x.kind || 'recorded');
        document.getElementById('lsKind').style.display = x.id ? 'none' : '';
        document.getElementById('lsTitle').textContent = x.id ? 'ক্লাস এডিট' : 'নতুন ক্লাস';
        lf.elements.title.value = x.title || ''; lf.elements.description.value = x.description || '';
        lf.elements.unlockAt.value = x.unlockAt || ''; lf.elements.startsAt.value = x.startsAt || ''; lf.elements.duration.value = x.duration || 60;
        open(ld); setTimeout(function () { lf.elements.title.focus(); }, 50);
    }
    document.querySelectorAll('[data-new-lesson]').forEach(function (b) { b.addEventListener('click', function () { lessonDlg({ kind: b.dataset.newLesson }); }); });
    document.querySelectorAll('[data-edit-lesson]').forEach(function (b) { b.addEventListener('click', function () { lessonDlg(JSON.parse(b.dataset.editLesson)); }); });
    lf.addEventListener('submit', async function (e) {
        e.preventDefault(); var btn = lf.querySelector('button[type=submit]'); btn.disabled = true;
        var d = await cxPost('/Course/SaveLesson', new FormData(lf)); btn.disabled = false; showErr(lf, d);
        if (d.success) { cxToast(d.message || 'সেভ হয়েছে'); setTimeout(reload, 700); }
    });
    document.querySelectorAll('[data-del-lesson]').forEach(function (b) { b.addEventListener('click', async function () {
        if (!confirm('ক্লাসটা মুছে ফেলবেন? এর সাথের অ্যাসাইনমেন্ট / কুইজও মুছে যাবে।')) return;
        var d = await cxPost('/Course/DeleteLesson', { id: b.dataset.delLesson }); cxToast(d.message || ''); if (d.success) setTimeout(reload, 600);
    }); });
    document.querySelectorAll('[data-move]').forEach(function (b) { b.addEventListener('click', async function () {
        var d = await cxPost('/Course/MoveLesson', { id: b.dataset.move, dir: b.dataset.dir }); if (d.success) reload(); else cxToast(d.message || '');
    }); });
    document.querySelectorAll('[data-rerec]').forEach(function (b) { b.addEventListener('click', async function () {
        if (!confirm('আগের রেকর্ডিং বাদ দিয়ে নতুন করে রেকর্ড করবেন?')) return;
        var d = await cxPost('/Course/Rerecord', { id: b.dataset.rerec }); cxToast(d.message || ''); if (d.success) setTimeout(reload, 900);
    }); });

    // ---------- অ্যাসাইনমেন্ট / কুইজ
    var td = document.getElementById('tkDlg'), tf = document.getElementById('tkForm'), qs = document.getElementById('tkQs');
    function renumber() { qs.querySelectorAll('.bd-q').forEach(function (q, i) { q.querySelector('[data-qn]').textContent = 'প্রশ্ন ' + (i + 1); q.querySelectorAll('input[type=radio]').forEach(function (r) { r.name = 'ans' + i; }); }); }
    function optRow(q, val, checked) {
        var o = document.createElement('div'); o.className = 'bd-o';
        o.innerHTML = '<input type="radio" title="সঠিক উত্তর"' + (checked ? ' checked' : '') + ' /><input type="text" maxlength="200" placeholder="উত্তর" value="' + esc(val) + '" /><button type="button" class="bd-ic red" title="বাদ"><i class="fas fa-xmark"></i></button>';
        o.querySelector('button').addEventListener('click', function () { if (q.querySelectorAll('.bd-o').length > 2) o.remove(); });
        q.querySelector('[data-opts]').appendChild(o);
    }
    function addQ(x) {
        x = x || { q: '', o: ['', '', '', ''], a: 0 };
        var q = document.createElement('div'); q.className = 'bd-q';
        q.innerHTML = '<div class="bd-q-h"><span data-qn></span><button type="button" class="bd-ic red" title="প্রশ্ন বাদ"><i class="fas fa-trash"></i></button></div>' +
            '<div class="cx-f" style="margin-bottom:8px;"><input type="text" maxlength="500" placeholder="প্রশ্ন লিখুন" data-q value="' + esc(x.q) + '" /></div><div data-opts></div>' +
            '<button type="button" class="bd-add-o" style="border:none;background:none;color:#0369A1;font-weight:700;font-size:12.5px;cursor:pointer;padding:2px 0;"><i class="fas fa-plus"></i> উত্তর যোগ</button>' +
            '<div style="font-size:11.5px;color:#94A3B8;margin-top:4px;">সঠিক উত্তরের বাম পাশের গোল বাটনটা বাছাই করুন</div>';
        (x.o || []).forEach(function (v, i) { optRow(q, v, i === x.a); });
        q.querySelector('.bd-q-h button').addEventListener('click', function () { if (qs.children.length > 1) { q.remove(); renumber(); } });
        q.querySelector('.bd-add-o').addEventListener('click', function () { if (q.querySelectorAll('.bd-o').length < 6) optRow(q, '', false); });
        qs.appendChild(q); renumber();
    }
    document.getElementById('tkAddQ').addEventListener('click', function () { addQ(); });
    function taskDlg(x) {
        tf.reset(); tf.querySelector('[data-msg]').textContent = ''; qs.innerHTML = '';
        var k = x.kind || 'assignment'; td.dataset.kind = k; tf.elements.kind.value = k; tf.elements.id.value = x.id || 0;
        document.getElementById('tkTitle').textContent = (x.id ? 'এডিট — ' : 'নতুন ') + (k === 'quiz' ? 'কুইজ' : 'অ্যাসাইনমেন্ট');
        tf.elements.lessonId.value = String(x.lessonId || 0);
        tf.elements.title.value = x.title || ''; tf.elements.instructions.value = x.instructions || ''; tf.elements.dueAt.value = x.dueAt || '';
        tf.elements.maxMarks.value = x.maxMarks || 10; tf.elements.passPercent.value = x.passPercent == null ? '' : x.passPercent;
        if (k === 'quiz') { (x.questions && x.questions.length ? x.questions : [null]).forEach(function (q) { addQ(q); }); }
        open(td); setTimeout(function () { tf.elements.title.focus(); }, 50);
    }
    document.querySelectorAll('[data-new-task]').forEach(function (b) { b.addEventListener('click', function () { taskDlg({ kind: b.dataset.newTask, lessonId: b.dataset.lessonId }); }); });
    document.querySelectorAll('[data-edit-task]').forEach(function (b) { b.addEventListener('click', function () { taskDlg(JSON.parse(b.dataset.editTask)); }); });
    tf.addEventListener('submit', async function (e) {
        e.preventDefault();
        var fd = new FormData(tf);
        if (td.dataset.kind === 'quiz') {
            var list = [];
            qs.querySelectorAll('.bd-q').forEach(function (q) {
                var o = [], a = -1;
                q.querySelectorAll('.bd-o').forEach(function (r) { var v = r.querySelector('input[type=text]').value.trim(); if (!v) return; if (r.querySelector('input[type=radio]').checked) a = o.length; o.push(v); });
                list.push({ q: q.querySelector('[data-q]').value.trim(), o: o, a: a });
            });
            fd.set('questions', JSON.stringify(list));
        }
        var btn = tf.querySelector('button[type=submit]'); btn.disabled = true;
        var d = await cxPost('/Course/SaveTask', fd); btn.disabled = false; showErr(tf, d);
        if (d.success) { cxToast(d.message || 'সেভ হয়েছে'); setTimeout(reload, 700); }
    });
    document.querySelectorAll('[data-del-task]').forEach(function (b) { b.addEventListener('click', async function () {
        if (!confirm('মুছে ফেলবেন? শিক্ষার্থীদের জমাও মুছে যাবে।')) return;
        var d = await cxPost('/Course/DeleteTask', { id: b.dataset.delTask }); cxToast(d.message || ''); if (d.success) setTimeout(reload, 600);
    }); });
})();
</script>
