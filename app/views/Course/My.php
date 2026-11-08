<?php
/** আমার কোর্স — কেনা কোর্সগুলো, অগ্রগতি, সার্টিফিকেট */
$list = is_array($Model) ? $Model : [];
$ViewData['Title'] = 'আমার কোর্স';
$ViewData['Robots'] = 'noindex, nofollow';
?>
<?php partial('Course/_Styles'); ?>
<style>
    .cm-g { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .cm-c { background: #fff; border: 1px solid #E3EAF3; border-radius: 18px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 6px 22px rgba(15,52,103,.05); }
    .cm-b { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 9px; flex: 1; }
    .cm-b h3 { margin: 0; font-size: 16.5px; font-weight: 800; color: #0F172A; line-height: 1.35; }
    .cm-b small { color: #64748B; font-size: 13px; }
    .cm-r { display: flex; justify-content: space-between; font-size: 12.5px; color: #64748B; }
    .cm-a { display: flex; gap: 8px; margin-top: auto; padding-top: 4px; flex-wrap: wrap; }
    @media (max-width: 980px) { .cm-g { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 620px) { .cm-g { grid-template-columns: minmax(0, 1fr); } }
</style>
<div class="cx-page">
    <div class="cx-wrap">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:18px;">
            <div><h1 class="cx-h1"><i class="fas fa-bookmark" style="color:#0EA5E9;"></i> আমার কোর্স</h1><p class="cx-sub">যে কোর্সগুলো কিনেছেন — অগ্রগতি দেখুন, ক্লাস চালিয়ে যান।</p></div>
            <a class="cx-btn pri" href="/Course"><i class="fas fa-magnifying-glass"></i> আরও কোর্স দেখুন</a>
        </div>
        <?php if (!$list): ?>
            <div class="cx-card cx-empty"><i class="fas fa-book-open"></i>এখনো কোনো কোর্স কেনেননি।<br><a class="cx-btn pri" href="/Course" style="margin-top:14px;">কোর্স দেখুন</a></div>
        <?php else: ?>
        <div class="cm-g">
            <?php foreach ($list as $c): $p = $c->Progress; $cert = (string)($c->Enrollment->CertCode ?? ''); ?>
                <div class="cm-c">
                    <a href="/Course/Learn/<?= (int)$c->Id ?>" style="display:block;"><?= course_thumb_html($c) ?></a>
                    <div class="cm-b">
                        <h3><?= e($c->Title) ?></h3>
                        <small><i class="fas fa-chalkboard-user"></i> <?= e($c->TeacherName ?: $c->TeacherUser) ?></small>
                        <div class="cx-bar"><div style="width:<?= (int)$p['percent'] ?>%;"></div></div>
                        <div class="cm-r"><span><?= (int)$p['percent'] ?>% সম্পন্ন</span><span>ক্লাস <?= $p['lessons'][0] ?>/<?= $p['lessons'][1] ?> · কাজ <?= $p['assignments'][0] + $p['quizzes'][0] ?>/<?= $p['assignments'][1] + $p['quizzes'][1] ?></span></div>
                        <div class="cm-a">
                            <a class="cx-btn pri sm" href="/Course/Learn/<?= (int)$c->Id ?>"><i class="fas fa-circle-play"></i> <?= $p['percent'] > 0 ? 'চালিয়ে যান' : 'শুরু করুন' ?></a>
                            <?php if ($cert !== ''): ?><a class="cx-btn warn sm" href="/Course/Certificate/<?= e($cert) ?>" target="_blank" rel="noopener"><i class="fas fa-award"></i> সার্টিফিকেট</a><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
