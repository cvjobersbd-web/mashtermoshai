<?php
/** টিচার নিজের Tutor Details বদলায় — এগুলো /Tutors/Profile/{id} পেজে দেখায় */
$ViewData['Title'] = 'Edit Tutor Details';
$me = (int)current_user_id();
?>
<style>
    .ted { max-width: 980px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
    .ted h2 { color: #08224A; font-weight: 800; margin: 0 0 6px; font-size: 24px; }
    .ted .sub { color: #64748B; margin-bottom: 22px; font-size: 14px; }
    .ted .required-star { color: #e74c3c; }
    .ted .acts { display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px; flex-wrap: wrap; }
    .ted .acts a, .ted .acts button { height: 46px; padding: 0 24px; border-radius: 10px; font-weight: 700; font-size: 15px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; border: none; cursor: pointer; font-family: inherit; }
    .ted .acts a { background: #F1F5F9; color: #334155; }
    .ted .acts button { background: #00CFFF; color: #fff; }
    .ted .acts button:hover { background: #00B5E0; }
    @media (max-width: 640px) { .ted { padding: 18px 14px; border-radius: 0; } }
</style>
<div class="container py-4 px-0 px-sm-3">
    <div class="ted">
        <h2><i class="fas fa-user-pen" style="color:#00B5E0;"></i> Tutor Details</h2>
        <p class="sub">এই তথ্যগুলো আপনার <a href="/Tutors/Profile/<?= $me ?>">ডিটেইলস পেজে</a> দেখায় — স্টুডেন্ট আর অভিভাবকরা দেখে আপনাকে রিকোয়েস্ট পাঠায়।</p>
        <?php if (!empty($ViewBag->Error)): ?><div class="alert alert-danger"><?= e($ViewBag->Error) ?></div><?php endif; ?>
        <form method="post" action="/Teacher/EditDetails">
            <?= csrf_field() ?>
            <?php partial('Teacher/_DetailsForm', ['d' => $ViewBag->Details]); ?>
            <div class="acts">
                <a href="/Tutors/Profile/<?= $me ?>">বাতিল</a>
                <button type="submit"><i class="fas fa-save"></i> Save Details</button>
            </div>
        </form>
    </div>
</div>
