<?php
/** আপনি শিক্ষার্থী না টিচার — পুরোনো ইউজার প্রথম লগইনে, বা ফর্ম জমা দেওয়ার আগে রোল বদলাতে */
$ViewData['Title'] = t('আপনি কে?', 'Choose your role');
$ViewData['Robots'] = 'noindex, nofollow';
$cur = $ViewBag->Current; $track = $ViewBag->Track; $next = (string)$ViewBag->Next;
?>
<?php partial('Account/_AuthStyles'); ?>
<style>
    .cr-g { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 6px 0 16px; }
    .cr-g label { margin: 0; cursor: pointer; }
    .cr-g input { position: absolute; opacity: 0; pointer-events: none; }
    .cr-g span { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 20px 12px; border: 2px solid #DCE7F0; border-radius: 16px; background: #F8FBFF; text-align: center; transition: .15s; }
    .cr-g span i { font-size: 32px; color: #94A3B8; margin-bottom: 4px; }
    .cr-g span b { font-size: 17px; color: #0F172A; } .cr-g span small { font-size: 12.5px; color: #64748B; line-height: 1.5; }
    .cr-g input:checked + span { border-color: #1677E5; background: #EAF4FF; box-shadow: 0 0 0 4px rgba(22,119,229,.12); } .cr-g input:checked + span i { color: #1677E5; }
    .cr-note { font-size: 13px; color: #475569; background: #F1F5F9; border-radius: 12px; padding: 10px 12px; line-height: 1.6; margin: 0 0 14px; }
    @media (max-width: 420px) { .cr-g { gap: 10px; } .cr-g span { padding: 16px 8px; } }
</style>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <div class="auth-ico"><i class="fas fa-user-check"></i></div>
            <h4><?= t('আপনি কে — শিক্ষার্থী না টিচার?', 'Are you a student or a teacher?') ?></h4>
            <p><?= t('একটা বাছাই করুন — তারপর সেই অনুযায়ী Verification ফর্ম পূরণ করবেন। অ্যাডমিন Approve করলে আপনার ড্যাশবোর্ড খুলে যাবে।', 'Pick one, then fill in the matching verification form. Your dashboard opens once the admin approves it.') ?></p>
        </div>
        <div class="auth-body">
            <?php if ($TempData['Error'] !== null): ?><div class="auth-alert err"><i class="fas fa-circle-exclamation"></i><div><?= e($TempData['Error']) ?></div></div><?php endif; ?>
            <?php if ($track !== null): ?>
                <div class="auth-alert ok"><i class="fas fa-circle-info"></i><div>
                    <?= $track === 'teacher' ? 'আপনি টিচার হওয়ার ফর্ম জমা দিয়েছেন — এখন আর নিজে রোল বদলানো যাবে না। বদলাতে চাইলে Helpdesk-এ অ্যাডমিনের সাথে যোগাযোগ করুন।' : 'আপনি Student Verification ফর্ম জমা দিয়েছেন — এখন আর নিজে রোল বদলানো যাবে না। বদলাতে চাইলে Helpdesk-এ অ্যাডমিনের সাথে যোগাযোগ করুন।' ?></div></div>
                <a class="auth-btn" href="<?= $track === 'teacher' ? '/Teacher/RequestForm' : '/Kyc/Student' ?>" style="text-decoration:none;margin-top:12px;"><i class="fas fa-arrow-right"></i> <?= $track === 'teacher' ? 'আমার টিচার ফর্ম' : 'আমার Student Verification' ?></a>
            <?php else: ?>
                <form method="post" action="/Account/ChooseRole">
                    <?= csrf_field() ?>
                    <?php if ($next !== ''): ?><input type="hidden" name="next" value="<?= e($next) ?>" /><?php endif; ?>
                    <div class="cr-g">
                        <label><input type="radio" name="role" value="student"<?= $cur === 'student' ? ' checked' : '' ?> required /><span><i class="fas fa-user-graduate"></i><b><?= t('শিক্ষার্থী', 'Student') ?></b><small><?= t('টিচার খুঁজব, টিউশন পোস্ট করব', 'Find tutors and post tuitions') ?></small></span></label>
                        <label><input type="radio" name="role" value="teacher"<?= $cur === 'teacher' ? ' checked' : '' ?> /><span><i class="fas fa-chalkboard-teacher"></i><b><?= t('টিচার', 'Teacher') ?></b><small><?= t('পড়াতে চাই, টিউশন পেতে চাই', 'Teach and get tuitions') ?></small></span></label>
                    </div>
                    <p class="cr-note"><i class="fas fa-circle-info" style="color:#1677E5;"></i> <?= t('Verification ফর্ম জমা দেওয়ার আগ পর্যন্ত এখান থেকে নিজে বদলাতে পারবেন। একটা অ্যাকাউন্ট হয় শিক্ষার্থী, নয় টিচার — দুটো একসাথে না।', 'You can change this yourself until you submit the verification form. One account is either a student or a teacher, not both.') ?></p>
                    <button type="submit" class="auth-btn"><i class="fas fa-arrow-right"></i> <?= $cur ? t('সেভ করে এগিয়ে যান', 'Save and continue') : t('এগিয়ে যান', 'Continue') ?></button>
                </form>
            <?php endif; ?>
            <div class="auth-foot"><a href="/"><i class="fas fa-house"></i> <?= t('পরে করব — হোমে যান', 'Later — go home') ?></a></div>
        </div>
    </div>
</div>
