<?php
/** কোর্স সম্পূর্ণের সার্টিফিকেট — যে কেউ লিংক দিয়ে যাচাই করতে পারে; প্রিন্ট / PDF হিসেবে সেভ */
$x = $Model;
$name = (string)($x->FullName ?: $x->UserName);
$teacher = (string)($x->TeacherName ?: 'মাষ্টার মশাই');
$ViewData['Title'] = 'Certificate — ' . $name;
$ViewData['FullTitle'] = 'সার্টিফিকেট: ' . $name . ' — ' . $x->CourseTitle . ' | মাষ্টার মশাই';
$ViewData['Description'] = $name . ' "' . $x->CourseTitle . '" কোর্স সফলভাবে সম্পন্ন করেছেন — মাষ্টার মশাই।';
$verify = site_public_url() . '/Course/Certificate/' . $x->CertCode;
$logo = site_settings()->NavbarLogoUrl ?? '';
?>
<style>
    .ce-page { background: #EEF3F9; padding: 28px 12px 60px; min-height: 80vh; }
    .ce-acts { max-width: 1100px; margin: 0 auto 16px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    .ce-acts button, .ce-acts a { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 11px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; text-decoration: none; font-family: inherit; }
    .ce-acts .p { background: #08224A; color: #fff; } .ce-acts .g { background: #fff; color: #334155; border: 1px solid #DCE6F0; }
    .ce-wrap { max-width: 1100px; margin: 0 auto; overflow-x: auto; }
    .ce { width: 1056px; height: 746px; margin: 0 auto; background: #fff; position: relative; box-shadow: 0 20px 50px rgba(15,52,103,.15); font-family: 'Georgia', 'Times New Roman', serif; color: #0B1F44; }
    .ce-b1 { position: absolute; inset: 18px; border: 3px solid #B45309; }
    .ce-b2 { position: absolute; inset: 28px; border: 1px solid #D6A75A; }
    .ce-corner { position: absolute; width: 120px; height: 120px; background: linear-gradient(135deg, #08224A 50%, transparent 50%); }
    .ce-corner.tl { top: 0; left: 0; } .ce-corner.br { bottom: 0; right: 0; transform: rotate(180deg); }
    .ce-in { position: absolute; inset: 60px 80px; display: flex; flex-direction: column; align-items: center; text-align: center; }
    .ce-logo { height: 70px; margin-bottom: 6px; }
    .ce-org { font-family: var(--font-heading, sans-serif); font-weight: 800; letter-spacing: 2px; color: #0369A1; font-size: 16px; }
    .ce-t { font-size: 50px; letter-spacing: 6px; margin: 12px 0 0; color: #B45309; font-weight: 700; text-transform: uppercase; }
    .ce-t2 { font-size: 18px; letter-spacing: 8px; color: #475569; text-transform: uppercase; margin-top: 2px; }
    .ce-pre { margin-top: 30px; font-size: 17px; color: #475569; font-style: italic; }
    .ce-name { font-size: 46px; margin: 10px 0 0; font-weight: 700; border-bottom: 2px solid #D6A75A; padding: 0 30px 6px; min-width: 60%; }
    .ce-txt { margin-top: 18px; font-size: 17px; color: #334155; line-height: 1.6; max-width: 760px; }
    .ce-txt b { color: #0B1F44; font-size: 22px; display: block; margin-top: 4px; }
    .ce-foot { margin-top: auto; width: 100%; display: flex; justify-content: space-between; align-items: flex-end; }
    .ce-sig { text-align: center; min-width: 220px; }
    .ce-sig .ln { border-top: 1.5px solid #0B1F44; padding-top: 6px; font-size: 15px; font-weight: 700; } .ce-sig small { display: block; color: #64748B; font-size: 12.5px; font-family: sans-serif; }
    .ce-seal { width: 110px; height: 110px; border-radius: 50%; background: radial-gradient(circle, #F59E0B 0 55%, #B45309 56% 62%, #F59E0B 63%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 40px; box-shadow: 0 6px 14px rgba(180,83,9,.35); }
    .ce-code { position: absolute; bottom: 40px; left: 0; right: 0; text-align: center; font-size: 12px; color: #64748B; font-family: sans-serif; }
    @media print {
        @page { size: A4 landscape; margin: 0; }
        body * { visibility: hidden; } .ce, .ce * { visibility: visible; }
        .ce { position: fixed; left: 0; top: 0; box-shadow: none; width: 297mm; height: 210mm; }
    }
</style>
<div class="ce-page">
    <div class="ce-acts">
        <button type="button" class="p" onclick="window.print()"><i class="fas fa-print"></i> প্রিন্ট / PDF সেভ</button>
        <button type="button" class="g" id="ceCopy"><i class="fas fa-link"></i> যাচাইয়ের লিংক কপি</button>
        <?php if (current_user_id() === (int)$x->UserId): ?><a class="g" href="/Course/Learn/<?= (int)$x->CourseId ?>"><i class="fas fa-arrow-left"></i> কোর্সে ফিরে যান</a><?php endif; ?>
    </div>
    <div class="ce-wrap">
        <div class="ce">
            <div class="ce-corner tl"></div><div class="ce-corner br"></div>
            <div class="ce-b1"></div><div class="ce-b2"></div>
            <div class="ce-in">
                <?php if (!is_blank($logo)): ?><img class="ce-logo" src="<?= e($logo) ?>" alt="" onerror="this.remove()" /><?php endif; ?>
                <div class="ce-org">MASHTERMOSHAI · মাষ্টার মশাই</div>
                <div class="ce-t">Certificate</div>
                <div class="ce-t2">of Completion</div>
                <div class="ce-pre">This is proudly presented to</div>
                <div class="ce-name"><?= e($name) ?></div>
                <div class="ce-txt">for successfully completing all classes, assignments and quizzes of the course
                    <b><?= e($x->CourseTitle) ?></b></div>
                <div class="ce-foot">
                    <div class="ce-sig"><div class="ln"><?= e($teacher) ?></div><small>Course Instructor</small></div>
                    <div class="ce-seal"><i class="fas fa-award"></i></div>
                    <div class="ce-sig"><div class="ln"><?= e(fmt_date($x->CertIssuedAt, 'd F Y')) ?></div><small>Date of Completion</small></div>
                </div>
            </div>
            <div class="ce-code">Certificate ID: <b><?= e($x->CertCode) ?></b> · Verify: <?= e($verify) ?></div>
        </div>
    </div>
</div>
<script>
document.getElementById('ceCopy').addEventListener('click', function () {
    var v = <?= js($verify) ?>;
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v).then(function () { alert('কপি হয়েছে'); }); else prompt('লিংক কপি করুন:', v);
});
</script>
