<?php
/**
 * প্যাকেজ / Pricing — শিক্ষকের Subscription আর শিক্ষার্থীর Tuition Post প্যাকেজ।
 * প্যাকেজগুলো অ্যাডমিন প্যানেল (Admin → Packages) থেকে সাজানো হয়। পেমেন্ট সিস্টেম পরে যুক্ত হবে।
 */
$ViewData['Title'] = 'Packages';
$teacherPk = $ViewBag->TeacherPackages ?? [];
$studentPk = $ViewBag->StudentPackages ?? [];
$tab = $ViewBag->Tab === 'student' ? 'student' : 'teacher';
$isLoggedIn = session_get('UserName') != null;
$me = $ViewBag->Me;
$benefits = package_benefits();
$myName = (string)(session_get('UserFullName') ?? '');
$pb = package_banner();
$bannerImg = $pb['pk_banner_enabled'] !== '0' ? trim((string)$pb['pk_banner']) : '';
if ($bannerImg !== '' && !preg_match('#^(https?://|/uploads/)[^\s"\'<>()]+$#i', $bannerImg)) $bannerImg = '';
$benefitGrid = function (array $list, string $title) { if (!$list) return; ?>
    <div class="pk-ben-h"><h3><?= e($title) ?></h3></div>
    <div class="pk-ben">
        <?php foreach ($list as $b): ?>
            <div class="pk-ben-i"><span class="ic"><?php if (($b['i'] ?? '') === 'fa-certificate'): ?><?= blue_tick_html('নীল টিক') ?><?php else: ?><i class="fas <?= e(in_array($b['i'] ?? '', package_benefit_icons(), true) ? $b['i'] : 'fa-check') ?>"></i><?php endif; ?></span>
                <div><b><?= e($b['t']) ?></b><?php if (($b['d'] ?? '') !== ''): ?><p><?= e($b['d']) ?></p><?php endif; ?></div></div>
        <?php endforeach; ?>
    </div>
<?php };

$card = function (Obj $p, string $cta) { ?>
    <div class="pk-card<?= $p->IsFeatured ? ' featured' : '' ?>" data-pkg="<?= (int)$p->Id ?>">
        <div class="pk-top">
            <div class="pk-name"><?= e($p->Name) ?></div>
            <?php if (!is_blank($p->Badge)): ?><span class="pk-badge"><?= e($p->Badge) ?></span><?php endif; ?>
        </div>
        <?php if (trim($p->Name) !== $p->DurationLabel): ?><div class="pk-dur"><?= e($p->DurationLabel) ?></div><?php endif; ?>
        <div class="pk-price">
            <b><?= e(taka($p->Price)) ?></b>
            <?php if ($p->RegularPrice): ?><s><?= e(taka($p->RegularPrice)) ?></s><?php endif; ?>
        </div>
        <?php if ($p->PerMonth): ?><span class="pk-pill">প্রতি মাসে মাত্র <?= e(taka($p->PerMonth)) ?></span>
        <?php elseif ($p->Audience === 'Teacher'): ?><span class="pk-pill green">মাত্র <?= e(taka($p->Price)) ?></span>
        <?php elseif ($p->PostLimit > 1 && $p->Price > 0): ?><span class="pk-pill">প্রতি পোস্ট মাত্র <?= e(taka((int)round($p->Price / $p->PostLimit))) ?></span>
        <?php else: ?><span class="pk-pill green"><?= e($p->DurationLabel) ?></span><?php endif; ?>
        <?php if ($p->FeatureList): ?>
            <ul class="pk-feat">
                <?php foreach ($p->FeatureList as $f): ?><li><i class="fas fa-check"></i><span><?= e($f) ?></span></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a class="pk-btn" href="/Payment/Checkout/<?= (int)$p->Id ?>"><?= e($cta) ?> <i class="fas fa-arrow-right"></i></a>
    </div>
<?php };
?>
<style>
    .pk-page { background: #F5F9FD; padding-bottom: 56px; }
    .pk-wrap { max-width: 1180px; margin: 0 auto; padding: 0 16px; }
    .pk-hero { position: relative; overflow: hidden; color: #fff; min-height: 300px; display: flex; align-items: center;
        background: linear-gradient(90deg, #0E4A5E 0%, #0E4A5E 34%, rgba(14,74,94,.82) 46%, rgba(14,74,94,0) 64%), var(--pk-img, none) right center / cover no-repeat, linear-gradient(120deg, #0E4A5E, #1B6A80); }
    .pk-hero.noimg { background: radial-gradient(circle at 85% 30%, rgba(2,184,245,.35), transparent 45%), linear-gradient(120deg, #0E4A5E, #1B6A80); }
    .pk-hero-in { position: relative; z-index: 1; width: 100%; max-width: 1180px; margin: 0 auto; padding: 38px 16px; }
    .pk-hero-txt { max-width: 520px; }
    .pk-hero small { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 700; padding: 6px 14px; border-radius: 20px; background: rgba(255,255,255,.16); color: #fff; }
    .pk-hero h1 { margin: 14px 0 10px; font-size: clamp(28px, 4.2vw, 44px); font-weight: 800; color: #fff; line-height: 1.2; }
    .pk-hero p { margin: 0; font-size: 16px; line-height: 1.7; color: rgba(255,255,255,.92); }

    .pk-tabs-in { display: inline-flex; gap: 6px; padding: 6px; margin-top: 22px; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.18); }
    .pk-tab { border: none; background: none; padding: 12px 22px; border-radius: 12px; font-weight: 700; font-size: 14.5px; color: #1E293B; cursor: pointer; display: inline-flex; align-items: center; gap: 9px; transition: background .2s, color .2s; }
    .pk-tab.on { background: linear-gradient(135deg, #1D7FE5, #2B8CF0); color: #fff; box-shadow: 0 6px 16px rgba(29,127,229,.35); }

    .pk-me { max-width: 760px; margin: 26px auto 0; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 12px 16px; border-radius: 14px; background: #fff; border: 1px solid #DCECF5; font-size: 14px; color: #334155; }
    .pk-me > i { color: #1D7FE5; font-size: 18px; }
    .pk-me > div { flex: 1; min-width: 200px; }
    .pk-me b { color: #08224A; }
    .pk-me-p { display: inline-flex; gap: 5px; align-items: center; margin-left: 8px; font-size: 12px; font-weight: 700; padding: 2px 9px; border-radius: 12px; background: #FEF3C7; color: #92400E; }
    .pk-me a { font-weight: 700; color: #1D7FE5; text-decoration: none; font-size: 13.5px; }
    .pk-sec { margin-top: 30px; }
    .pk-ben-h { text-align: center; margin: 38px 0 16px; }
    .pk-ben-h h3 { font-size: 20px; font-weight: 800; color: #08224A; margin: 0; }
    .pk-ben { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
    .pk-ben-i { display: flex; gap: 14px; align-items: flex-start; background: #fff; border: 1px solid #DCECF5; border-radius: 16px; padding: 16px; }
    .pk-ben-i .ic { width: 44px; height: 44px; border-radius: 13px; background: #E3F2FF; color: #1D7FE5; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .pk-ben-i .ic .blue-tick { width: 26px; height: 26px; margin: 0; }
    .pk-ben-i b { display: block; color: #08224A; font-size: 15px; font-weight: 800; }
    .pk-ben-i p { margin: 4px 0 0; color: #475569; font-size: 13.5px; line-height: 1.6; }
    .pk-tick-demo { margin: 26px auto 0; max-width: 760px; display: flex; gap: 18px; align-items: center; padding: 16px 18px; border-radius: 18px; background: linear-gradient(135deg, #EFF8FF, #fff); border: 1px solid #CFE6FB; }
    .pk-tick-card { display: flex; gap: 12px; align-items: center; background: #fff; border-radius: 14px; padding: 10px 16px 10px 10px; box-shadow: 0 6px 18px rgba(8,34,74,.08); flex-shrink: 0; }
    .pk-tick-card .av { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center; }
    .pk-tick-card b { display: flex; align-items: center; font-size: 16px; color: #08224A; }
    .pk-tick-card small { display: block; color: #64748B; font-size: 12px; }
    .pk-tick-demo p { margin: 0; font-size: 14px; color: #334155; line-height: 1.65; }
    .pk-tick-demo p b { color: #08224A; }
    @media (max-width: 640px) { .pk-tick-demo { flex-direction: column; align-items: stretch; text-align: center; } .pk-tick-card { justify-content: center; } }
    .pk-sec[hidden] { display: none; }
    .pk-sec-h { text-align: center; margin-bottom: 22px; }
    .pk-sec-h h2 { font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: #08224A; margin: 0 0 6px; }
    .pk-sec-h p { margin: 0 auto; max-width: 620px; color: #64748B; font-size: 14.5px; line-height: 1.7; }

    .pk-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; align-items: stretch; }
    .pk-grid.few { grid-template-columns: repeat(auto-fit, minmax(260px, 320px)); justify-content: center; }
    .pk-card { position: relative; display: flex; flex-direction: column; align-items: flex-start; background: #fff; border: 1.5px solid #E3ECF5; border-radius: 18px; padding: 22px 20px 20px;
        box-shadow: 0 6px 22px rgba(8,34,74,.06); transition: transform .2s, box-shadow .2s; }
    .pk-card:hover { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(8,34,74,.10); }
    .pk-card.featured { border: 2px solid #1D7FE5; box-shadow: 0 16px 38px rgba(29,127,229,.20); }
    .pk-top { width: 100%; display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; min-height: 30px; }
    .pk-name { font-size: 18px; font-weight: 800; color: #08224A; padding-top: 3px; }
    .pk-badge { font-size: 12.5px; font-weight: 800; padding: 5px 13px; border-radius: 20px; background: #FFD84D; color: #4A3200; white-space: nowrap; }
    .pk-card.featured .pk-badge { background: #1D7FE5; color: #fff; }
    .pk-dur { margin-top: 2px; font-size: 13px; color: #64748B; }
    .pk-price { margin-top: 14px; display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
    .pk-price b { font-size: 40px; font-weight: 800; color: #08224A; line-height: 1; font-family: var(--font-heading); letter-spacing: -.5px; }
    .pk-price s { font-size: 17px; color: #94A3B8; font-weight: 600; }
    .pk-pill { display: inline-flex; align-items: center; margin-top: 12px; font-size: 13px; font-weight: 700; padding: 5px 12px; border-radius: 8px; background: #EEF2F6; color: #334155; }
    .pk-pill.green { background: #DDF6E6; color: #12733B; }
    .pk-feat { list-style: none; margin: 18px 0 0; padding: 0; display: flex; flex-direction: column; gap: 11px; flex: 1; width: 100%; }
    .pk-feat li { display: flex; gap: 10px; font-size: 14px; color: #1E293B; line-height: 1.5; }
    .pk-feat i { flex-shrink: 0; width: 19px; height: 19px; border-radius: 50%; background: #22C55E; color: #fff; font-size: 10px; display: inline-flex; align-items: center; justify-content: center; margin-top: 2px; }
    .pk-btn { margin-top: 22px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; border: 1.5px solid #1D7FE5; background: #fff; color: #1D7FE5 !important; font-weight: 700; font-size: 15px;
        padding: 12px; border-radius: 12px; cursor: pointer; text-decoration: none; transition: background .2s, color .2s, box-shadow .2s; }
    .pk-btn i { font-size: 13px; }
    .pk-btn:hover, .pk-card.featured .pk-btn { background: linear-gradient(135deg, #1D7FE5, #2B8CF0); color: #fff !important; border-color: transparent; box-shadow: 0 8px 20px rgba(29,127,229,.3); }

    .pk-free { display: flex; flex-direction: column; background: linear-gradient(160deg, #F0FAFF 0%, #fff 60%); }
    .pk-free .pk-price b { color: #157347; }
    .pk-free .pk-btn { border-color: #157347; color: #157347 !important; }
    .pk-free .pk-btn:hover { background: #157347; color: #fff !important; box-shadow: none; }

    .pk-note { margin: 22px auto 0; max-width: 760px; display: flex; gap: 10px; align-items: flex-start; padding: 13px 16px; border-radius: 14px; background: #FFF8E6; border: 1px solid #F8E1A5; color: #7A4B00; font-size: 13.5px; line-height: 1.6; }
    .pk-goal { margin-top: 40px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .pk-goal > div { background: #fff; border: 1px solid #DCECF5; border-radius: 18px; padding: 20px; display: flex; gap: 14px; align-items: flex-start; }
    .pk-goal i.big { flex-shrink: 0; width: 46px; height: 46px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 19px; background: #E3F2FF; color: #1D7FE5; }
    .pk-goal h3 { margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #08224A; }
    .pk-goal p { margin: 0; font-size: 13.5px; color: #475569; line-height: 1.65; }
    .pk-empty { text-align: center; padding: 40px 16px; border: 2px dashed #DCECF5; border-radius: 18px; color: #64748B; background: #fff; }


    @media (max-width: 640px) {
        .pk-hero { min-height: 0; background: linear-gradient(180deg, rgba(14,74,94,.80) 0%, rgba(14,74,94,.94) 100%), var(--pk-img, none) right center / cover no-repeat, linear-gradient(120deg, #0E4A5E, #1B6A80); }
        .pk-hero.noimg { background: linear-gradient(120deg, #0E4A5E, #1B6A80); }
        .pk-hero-in { padding: 28px 16px 26px; }
        .pk-hero p { font-size: 14.5px; }
        .pk-tabs-in { display: flex; width: 100%; }
        .pk-tab { flex: 1; justify-content: center; padding: 11px 8px; font-size: 13.5px; }
        .pk-goal { grid-template-columns: 1fr; }
    }
</style>

<div class="pk-page">
    <section class="pk-hero<?= $bannerImg === '' ? ' noimg' : '' ?>"<?= $bannerImg !== '' ? ' style="--pk-img:url(\'' . e($bannerImg) . '\')"' : '' ?>>
        <div class="pk-hero-in">
            <div class="pk-hero-txt">
                <small><i class="fas fa-graduation-cap"></i> Tutor &amp; Student Service</small>
                <h1>মাষ্টার মশাই প্যাকেজ</h1>
                <p>শিক্ষক ও শিক্ষার্থীদের জন্য আলাদা সুবিধা — নিরাপদ ও সহজভাবে টিউশন শিখে-শিখাতে যুক্ত হোন মাষ্টার মশাইয়ের সাথে।</p>
                <div class="pk-tabs-in" role="tablist">
                    <button type="button" class="pk-tab<?= $tab === 'teacher' ? ' on' : '' ?>" data-pk-tab="teacher" role="tab" aria-selected="<?= $tab === 'teacher' ? 'true' : 'false' ?>"><i class="fas fa-graduation-cap"></i> শিক্ষকদের জন্য</button>
                    <button type="button" class="pk-tab<?= $tab === 'student' ? ' on' : '' ?>" data-pk-tab="student" role="tab" aria-selected="<?= $tab === 'student' ? 'true' : 'false' ?>"><i class="fas fa-user-group"></i> শিক্ষার্থীদের জন্য</button>
                </div>
            </div>
        </div>
    </section>

<div class="pk-wrap">
    <?php if ($me): ?>
        <div class="pk-me">
            <i class="fas fa-user-check"></i>
            <div>
                <?php if ($me['role'] === 'Teacher'): ?>
                    <b>আপনার Subscription:</b> <?= $me['sub'] ? 'চালু — ' . e(date('d M Y', strtotime($me['sub']->EndsAt))) . ' পর্যন্ত' : 'চালু নেই' ?>
                <?php else: ?>
                    <b>আপনার পোস্ট ক্রেডিট:</b> <?= $me['credits'] === -1 ? 'আনলিমিটেড' : e(bn_num($me['credits'])) . 'টি' ?>
                <?php endif; ?>
                <?php if ($me['pending'] > 0): ?><span class="pk-me-p"><i class="fas fa-hourglass-half"></i> <?= e(bn_num($me['pending'])) ?>টি পেমেন্ট যাচাই হচ্ছে</span><?php endif; ?>
            </div>
            <a href="/Payment/My">আমার পেমেন্ট <i class="fas fa-arrow-right"></i></a>
        </div>
    <?php endif; ?>

    <!-- ===== শিক্ষক ===== -->
    <section class="pk-sec" id="pk-teacher"<?= $tab === 'teacher' ? '' : ' hidden' ?>>
        <div class="pk-sec-h">
            <h2>শিক্ষকদের জন্য Subscription</h2>
            <p>আপনার প্রোফাইল সক্রিয় করুন এবং শিক্ষার্থীদের সাথে সহজেই যুক্ত হোন</p>
        </div>
        <?php if ($teacherPk): ?>
            <div class="pk-grid<?= count($teacherPk) < 3 ? ' few' : '' ?>">
                <?php foreach ($teacherPk as $p) $card($p, 'Subscribe করুন'); ?>
            </div>
        <?php else: ?>
            <div class="pk-empty"><i class="fas fa-box-open fa-2x"></i><p style="margin:10px 0 0;">এখনো কোনো প্যাকেজ যোগ করা হয়নি।</p></div>
        <?php endif; ?>
        <div class="pk-tick-demo">
            <div class="pk-tick-card">
                <span class="av"><?= e(initials($myName !== '' && ($me['role'] ?? '') === 'Teacher' ? $myName : 'Your Name')) ?></span>
                <div><b><?= e($myName !== '' && ($me['role'] ?? '') === 'Teacher' ? $myName : 'আপনার নাম') ?><?= blue_tick_html() ?></b><small>Verified Teacher · Subscription চালু</small></div>
            </div>
            <p><b>Subscription নিলেই প্রোফাইলে নীল টিক!</b> আপনার প্রোফাইল আর Find Tutor কার্ডে নামের পাশে নীল টিক দেখাবে। Subscription শেষ হলে টিক চলে যাবে, আবার নিলে ফিরে আসবে।</p>
        </div>
        <?php $benefitGrid($benefits['Teacher'], 'Subscription নিলে যা যা পাবেন'); ?>
    </section>

    <!-- ===== শিক্ষার্থী ===== -->
    <section class="pk-sec" id="pk-student"<?= $tab === 'student' ? '' : ' hidden' ?>>
        <div class="pk-sec-h">
            <h2>শিক্ষার্থীদের জন্য</h2>
            <p>Account খোলা ও প্ল্যাটফর্ম ব্যবহার সম্পূর্ণ ফ্রি। শুধু “Teacher Needed / Tutor Required” ধরনের Tuition Post করতে চাইলে পোস্ট প্যাকেজ নিতে হবে।</p>
        </div>
        <div class="pk-grid<?= count($studentPk) + 1 < 3 ? ' few' : '' ?>">
            <div class="pk-card pk-free">
                <div class="pk-top"><div class="pk-name">Free Account</div><span class="pk-badge" style="background:#DDF6E6;color:#12733B;">সবসময় ফ্রি</span></div>
                <div class="pk-price"><b>৳0</b></div>
                <span class="pk-pill green">কোনো খরচ নেই · আজীবন</span>
                <ul class="pk-feat">
                    <li><i class="fas fa-check"></i><span>বিনামূল্যে Account খুলুন</span></li>
                    <li><i class="fas fa-check"></i><span>টিউটর খুঁজুন, প্রোফাইল ও রিভিউ দেখুন</span></li>
                    <li><i class="fas fa-check"></i><span>টিউটর সেভ করুন ও Request পাঠান</span></li>
                    <li><i class="fas fa-check"></i><span>Notice, Helpdesk সহ সব ফিচার ব্যবহার করুন</span></li>
                </ul>
                <?php if ($isLoggedIn): ?>
                    <a class="pk-btn" href="/FindTutor">টিউটর খুঁজুন <i class="fas fa-arrow-right"></i></a>
                <?php else: ?>
                    <a class="pk-btn" href="/Account/Register">ফ্রি Account খুলুন <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
            <?php foreach ($studentPk as $p) $card($p, 'পোস্ট প্যাকেজ নিন'); ?>
        </div>
        <div class="pk-note"><i class="fas fa-info-circle" style="margin-top:3px;"></i>
            <span>পেমেন্টের পর আপনার প্রয়োজন অনুযায়ী Tuition Post করতে পারবেন — বিষয়, ক্লাস, কোথায় পড়াতে হবে, কতদিন/কত ঘণ্টা, পারিশ্রমিক বা বাজেট ইত্যাদি। শিক্ষকরা পোস্ট দেখে আপনার সাথে যোগাযোগ করবেন।</span></div>
        <?php $benefitGrid($benefits['Student'], 'পোস্ট প্যাকেজ নিলে যা যা পাবেন'); ?>
    </section>

    <section class="pk-goal">
        <div><i class="fas fa-search big"></i><div><h3>শিক্ষার্থীর জন্য</h3><p>সহজে প্রয়োজনীয় শিক্ষক খুঁজে পাওয়া — বিষয়, ক্লাস, এলাকা আর বাজেট মিলিয়ে।</p></div></div>
        <div><i class="fas fa-handshake big" style="background:#E8F8EF;color:#157347;"></i><div><h3>শিক্ষকের জন্য</h3><p>নিজের যোগ্যতা অনুযায়ী উপযুক্ত শিক্ষার্থীর কাছে পৌঁছানো এবং নিয়মিত টিউশন পাওয়া।</p></div></div>
    </section>
</div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    document.querySelectorAll('[data-pk-tab]').forEach(function (b) {
        b.addEventListener('click', function () {
            var k = b.getAttribute('data-pk-tab');
            document.querySelectorAll('[data-pk-tab]').forEach(function (x) { var on = x === b; x.classList.toggle('on', on); x.setAttribute('aria-selected', on ? 'true' : 'false'); });
            document.getElementById('pk-teacher').hidden = k !== 'teacher';
            document.getElementById('pk-student').hidden = k !== 'student';
            try { history.replaceState(null, '', '/Package?for=' + k); } catch (e) { }
        });
    });
})();
</script>
<?php section_end(); ?>
