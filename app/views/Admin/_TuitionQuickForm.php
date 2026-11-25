<?php
/**
 * টিউশন পোস্টের ছোট ফর্ম — শুধু শিরোনাম, বিস্তারিত লেখা আর Status।
 * দরকার: $Model, $isEdit (bool), $ViewBag
 */
$errs = $GLOBALS['__ModelErrors'] ?? [];
?>
<style>
    .admin-main-content.tq-main { margin-left: 250px; padding: 24px 20px; min-height: calc(100vh - 80px); }
    .tq-wrap { max-width: 860px; margin: 0 auto; }
    @media (max-width: 992px) { .admin-main-content.tq-main { margin-left: 0; padding: 14px; } }
    .tq-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .tq-head h2 { margin: 0; font-size: 26px; font-weight: 800; color: #08224A; display: flex; align-items: center; gap: 10px; }
    .tq-head h2 i { width: 34px; height: 34px; border-radius: 50%; background: #0F8FB5; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }
    .tq-head p { margin: 4px 0 0; color: #64748B; font-size: 14px; }
    .tq-back { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1px solid #DCE7F0; border-radius: 12px; background: #fff; color: #334155; font-weight: 700; text-decoration: none; }
    .tq-card { background: #fff; border-radius: 18px; padding: 26px; box-shadow: 0 6px 24px rgba(8, 34, 74, .06); }
    .tq-lbl { display: block; font-weight: 700; color: #08224A; font-size: 14px; margin-bottom: 7px; }
    .tq-lbl .req { color: #DC2626; }
    .tq-in { width: 100%; border: 1.5px solid #DCE7F0; border-radius: 12px; padding: 12px 14px; font-size: 15px; background: #F8FBFF; color: #0F172A; }
    .tq-in:focus { outline: none; border-color: #0EA5E9; background: #fff; box-shadow: 0 0 0 4px rgba(14, 165, 233, .12); }
    textarea.tq-in { min-height: 300px; line-height: 1.75; resize: vertical; }
    .tq-tip { font-size: 12.5px; color: #64748B; margin: 6px 0 18px; }
    .tq-err { color: #DC2626; font-size: 13px; margin-top: 5px; }
    .tq-alert { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; font-size: 14px; }
    .tq-info { background: #F0F9FF; border: 1px dashed #7DD3FC; color: #075985; border-radius: 12px; padding: 11px 14px; margin-bottom: 18px; font-size: 13.5px; line-height: 1.6; }
    .tq-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .tq-act { display: flex; gap: 12px; margin-top: 8px; padding-top: 18px; border-top: 1px solid #EEF2F7; flex-wrap: wrap; }
    .tq-cancel { padding: 12px 22px; border-radius: 12px; border: 1px solid #DCE7F0; background: #fff; color: #475569; font-weight: 700; text-decoration: none; }
    .tq-save { padding: 12px 26px; border-radius: 12px; border: none; background: #0EA5E9; color: #fff; font-weight: 800; }
    .tq-save:hover { background: #0284C7; }
    @media (max-width: 640px) { .tq-row { grid-template-columns: 1fr; } .tq-card { padding: 18px; } }
</style>

<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content tq-main">
<div class="tq-wrap">
    <div class="tq-head">
        <div>
            <h2><i class="fas <?= $isEdit ? 'fa-pen' : 'fa-plus' ?>"></i> <?= $isEdit ? 'টিউশন পোস্ট এডিট' : 'নতুন টিউশন পোস্ট' ?></h2>
            <p><?= $isEdit ? 'লেখা ঠিক করে সেভ করুন' : 'শিরোনাম আর বিস্তারিত লিখে Publish করুন — টিউশন সার্কুলারে চলে যাবে' ?></p>
        </div>
        <a href="/Admin/TuitionPosts" class="tq-back"><i class="fas fa-arrow-left"></i> Back to Posts</a>
    </div>

    <?php if ($errs): ?><div class="tq-alert"><i class="fas fa-circle-exclamation"></i> <?= e(implode(' · ', $errs)) ?></div><?php endif; ?>
    <?php if ($ViewBag->Error !== null): ?><div class="tq-alert"><i class="fas fa-circle-exclamation"></i> <?= e($ViewBag->Error) ?></div><?php endif; ?>

    <div class="tq-card">
        <?php if (!$isEdit): ?>
            <div class="tq-info"><i class="fas fa-lightbulb"></i> টিপস: <b>Tutor Requests</b> পেইজে রিকোয়েস্টের <b>Convert to Post</b> বাটনে চাপলে পোস্ট তৈরি হয়ে যায় — সেখান থেকেই সরাসরি Publish করা যায়, বা কপি করে এখানে পেস্ট করুন।</div>
        <?php endif; ?>
        <form method="post" action="<?= $isEdit ? '/Admin/EditTuitionPost' : '/Admin/AddTuitionPost' ?>" id="tqForm">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="Id" value="<?= (int)$Model->Id ?>" />
                <input type="hidden" name="TuitionCode" value="<?= e($Model->TuitionCode) ?>" />
            <?php endif; ?>

            <label class="tq-lbl" for="Title">শিরোনাম <span class="req">*</span></label>
            <input type="text" name="Title" id="Title" class="tq-in" value="<?= e($Model->Title) ?>" maxlength="300" required
                   placeholder="যেমন: টিচার লাগবে — কোতোয়ালী, চট্টগ্রাম (ক্লাস ৬-৮)" />
            <?php if (isset($errs['Title'])): ?><div class="tq-err"><?= e($errs['Title']) ?></div><?php endif; ?>
            <div class="tq-tip">ছোট করে — কোন এলাকা, কোন ক্লাস।</div>

            <label class="tq-lbl" for="SpecialNote">পোস্টের বিস্তারিত <span class="req">*</span></label>
            <textarea name="SpecialNote" id="SpecialNote" class="tq-in" maxlength="4000" required
                      placeholder="এখানে Convert করা পোস্টটা পেস্ট করুন, বা নিজে লিখুন — বিষয়, ক্লাস, এলাকা, পড়ানোর ধরন, বেতন ইত্যাদি।"><?= e($Model->SpecialNote) ?></textarea>
            <?php if (isset($errs['SpecialNote'])): ?><div class="tq-err"><?= e($errs['SpecialNote']) ?></div><?php endif; ?>
            <div class="tq-tip"><i class="fas fa-shield-halved"></i> অভিভাবকের মোবাইল নম্বর বা ইমেইল দেবেন না — টিচাররা Apply করবেন।</div>

            <div class="tq-row">
                <div>
                    <label class="tq-lbl" for="Status">Status</label>
                    <select name="Status" id="Status" class="tq-in">
                        <option value="Open" <?= ($Model->Status ?? 'Open') !== 'Closed' ? 'selected' : '' ?>>🟢 Open — সার্কুলারে দেখা যাবে</option>
                        <option value="Closed" <?= ($Model->Status ?? '') === 'Closed' ? 'selected' : '' ?>>🔴 Closed — লুকানো</option>
                    </select>
                </div>
                <?php if ($isEdit && !is_blank($Model->TuitionCode)): ?>
                    <div>
                        <span class="tq-lbl">টিউশন কোড</span>
                        <div class="tq-in" style="background:#F1F5F9;"><?= e($Model->TuitionCode) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tq-act">
                <a href="/Admin/TuitionPosts" class="tq-cancel"><i class="fas fa-times"></i> Cancel</a>
                <button type="submit" class="tq-save" id="tqSave"><i class="fas fa-paper-plane"></i> <?= $isEdit ? 'সেভ করুন' : 'Publish করুন' ?></button>
            </div>
        </form>
    </div>
</div>
</div><!-- /admin-main-content -->
<script>
    document.getElementById('tqForm').addEventListener('submit', function () {
        var b = document.getElementById('tqSave'); b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> অপেক্ষা করুন...';
    });
</script>
