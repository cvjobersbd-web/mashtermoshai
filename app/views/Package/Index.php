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

$card = function (Obj $p, string $cta) { ?>
    <div class="pk-card<?= $p->IsFeatured ? ' featured' : '' ?>" data-pkg="<?= (int)$p->Id ?>">
        <?php if (!is_blank($p->Badge)): ?><span class="pk-badge"><?= e($p->Badge) ?></span><?php endif; ?>
        <div class="pk-name"><?= e($p->Name) ?></div>
        <?php if (trim($p->Name) !== $p->DurationLabel): ?><div class="pk-dur"><i class="far fa-clock"></i> <?= e($p->DurationLabel) ?></div><?php endif; ?>
        <div class="pk-price">
            <b><?= e(taka($p->Price)) ?></b>
            <?php if ($p->RegularPrice): ?><s><?= e(taka($p->RegularPrice)) ?></s><?php endif; ?>
        </div>
        <div class="pk-sub">
            <?php if ($p->Discount > 0): ?><span class="pk-off"><?= e(bn_num($p->Discount)) ?>% ছাড়</span><?php endif; ?>
            <?php if ($p->PerMonth): ?><span>মাসে মাত্র <?= e(taka($p->PerMonth)) ?></span><?php elseif ($p->Audience === 'Student' && $p->PostLimit === 1): ?><span>প্রতি পোস্ট</span><?php endif; ?>
        </div>
        <?php if (!is_blank($p->Description)): ?><p class="pk-desc"><?= e($p->Description) ?></p><?php endif; ?>
        <?php if ($p->FeatureList): ?>
            <ul class="pk-feat">
                <?php foreach ($p->FeatureList as $f): ?><li><i class="fas fa-check"></i><span><?= e($f) ?></span></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <button type="button" class="pk-btn" data-pk-choose="<?= e($p->Name) ?>" data-pk-price="<?= e(taka($p->Price)) ?>"><?= e($cta) ?></button>
    </div>
<?php };
?>
<style>
    .pk-wrap { max-width: 1180px; margin: 0 auto; padding: 28px 16px 56px; }
    .pk-hero { text-align: center; padding: 34px 18px 30px; border-radius: 24px; color: #fff;
        background: radial-gradient(circle at 15% 20%, rgba(2,184,245,.35), transparent 45%), radial-gradient(circle at 85% 80%, rgba(29,127,229,.35), transparent 45%), linear-gradient(135deg, #08224A 0%, #134856 100%); }
    .pk-hero small { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; padding: 5px 14px; border-radius: 20px; background: rgba(255,255,255,.12); color: #CFF2FC; }
    .pk-hero h1 { margin: 14px 0 8px; font-size: clamp(24px, 4vw, 36px); font-weight: 800; color: #fff; }
    .pk-hero p { margin: 0 auto; max-width: 640px; font-size: 15px; line-height: 1.7; color: rgba(255,255,255,.85); }

    .pk-tabs { display: flex; justify-content: center; margin: -22px auto 0; position: relative; z-index: 2; }
    .pk-tabs-in { display: inline-flex; gap: 4px; padding: 5px; background: #fff; border-radius: 40px; box-shadow: 0 10px 30px rgba(8,34,74,.14); }
    .pk-tab { border: none; background: none; padding: 11px 22px; border-radius: 30px; font-weight: 700; font-size: 14.5px; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background .2s, color .2s; }
    .pk-tab.on { background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; }

    .pk-sec { margin-top: 34px; }
    .pk-sec[hidden] { display: none; }
    .pk-sec-h { text-align: center; margin-bottom: 22px; }
    .pk-sec-h h2 { font-size: 22px; font-weight: 800; color: #08224A; margin: 0 0 6px; }
    .pk-sec-h p { margin: 0 auto; max-width: 620px; color: #64748B; font-size: 14.5px; line-height: 1.7; }

    .pk-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; align-items: stretch; }
    .pk-grid.few { grid-template-columns: repeat(auto-fit, minmax(260px, 340px)); justify-content: center; }
    .pk-card { position: relative; display: flex; flex-direction: column; background: #fff; border: 1.5px solid #DCECF5; border-radius: 20px; padding: 24px 20px 20px;
        box-shadow: 0 4px 18px rgba(8,34,74,.05); transition: transform .2s, box-shadow .2s; }
    .pk-card:hover { transform: translateY(-3px); box-shadow: 0 14px 34px rgba(8,34,74,.10); }
    .pk-card.featured { border-color: #1D7FE5; box-shadow: 0 14px 34px rgba(29,127,229,.18); }
    .pk-card.featured::before { content: ''; position: absolute; inset: 0 0 auto; height: 5px; border-radius: 20px 20px 0 0; background: linear-gradient(90deg, #1D7FE5, #02B8F5); }
    .pk-badge { position: absolute; top: 14px; right: 14px; font-size: 11.5px; font-weight: 800; padding: 4px 11px; border-radius: 20px; background: #FFF4D6; color: #9A5B00; border: 1px solid #F8D57E; }
    .pk-card.featured .pk-badge { background: #1D7FE5; color: #fff; border-color: #1D7FE5; }
    .pk-name { font-size: 19px; font-weight: 800; color: #08224A; padding-right: 86px; }
    .pk-dur { margin-top: 4px; font-size: 13px; color: #64748B; display: flex; align-items: center; gap: 6px; }
    .pk-price { margin-top: 16px; display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
    .pk-price b { font-size: 36px; font-weight: 800; color: #08224A; line-height: 1; font-family: var(--font-heading); }
    .pk-price s { font-size: 16px; color: #94A3B8; font-weight: 600; }
    .pk-sub { min-height: 24px; margin-top: 8px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; font-size: 13px; color: #475569; }
    .pk-off { font-size: 12px; font-weight: 800; padding: 3px 10px; border-radius: 20px; background: #E8F8EF; color: #157347; }
    .pk-desc { margin: 12px 0 0; font-size: 13.5px; color: #475569; line-height: 1.6; }
    .pk-feat { list-style: none; margin: 16px 0 0; padding: 16px 0 0; border-top: 1px dashed #DCECF5; display: flex; flex-direction: column; gap: 10px; flex: 1; }
    .pk-feat li { display: flex; gap: 9px; font-size: 13.5px; color: #334155; line-height: 1.5; }
    .pk-feat i { flex-shrink: 0; width: 18px; height: 18px; border-radius: 50%; background: #E3F2FF; color: #1D7FE5; font-size: 9.5px; display: inline-flex; align-items: center; justify-content: center; margin-top: 2px; }
    .pk-btn { margin-top: 20px; width: 100%; border: 1.5px solid #1D7FE5; background: #fff; color: #1D7FE5; font-weight: 700; font-size: 14.5px; padding: 11px; border-radius: 12px; cursor: pointer; transition: background .2s, color .2s; }
    .pk-btn:hover, .pk-card.featured .pk-btn { background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; border-color: transparent; }

    .pk-free { display: flex; flex-direction: column; background: linear-gradient(160deg, #F0FAFF 0%, #fff 60%); }
    .pk-free .pk-price b { color: #157347; }
    .pk-free .pk-btn { border-color: #157347; color: #157347; }
    .pk-free .pk-btn:hover { background: #157347; color: #fff; }

    .pk-note { margin: 22px auto 0; max-width: 760px; display: flex; gap: 10px; align-items: flex-start; padding: 13px 16px; border-radius: 14px; background: #FFF8E6; border: 1px solid #F8E1A5; color: #7A4B00; font-size: 13.5px; line-height: 1.6; }
    .pk-goal { margin-top: 40px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .pk-goal > div { background: #fff; border: 1px solid #DCECF5; border-radius: 18px; padding: 20px; display: flex; gap: 14px; align-items: flex-start; }
    .pk-goal i.big { flex-shrink: 0; width: 46px; height: 46px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 19px; background: #E3F2FF; color: #1D7FE5; }
    .pk-goal h3 { margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #08224A; }
    .pk-goal p { margin: 0; font-size: 13.5px; color: #475569; line-height: 1.65; }
    .pk-empty { text-align: center; padding: 40px 16px; border: 2px dashed #DCECF5; border-radius: 18px; color: #64748B; background: #fff; }

    .pk-modal { position: fixed; inset: 0; z-index: 3000; background: rgba(8,34,74,.45); display: flex; align-items: center; justify-content: center; padding: 16px; }
    .pk-modal[hidden] { display: none; }
    .pk-modal-box { background: #fff; border-radius: 20px; max-width: 400px; width: 100%; padding: 26px 22px 20px; text-align: center; box-shadow: 0 24px 60px rgba(8,34,74,.3); }
    .pk-modal-box .ic { width: 58px; height: 58px; border-radius: 50%; margin: 0 auto 12px; background: #FFF4D6; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .pk-modal-box h3 { margin: 0 0 8px; font-size: 18px; font-weight: 800; color: #08224A; }
    .pk-modal-box p { margin: 0 0 16px; font-size: 14px; color: #475569; line-height: 1.7; }
    .pk-modal-box .row-b { display: flex; gap: 10px; }
    .pk-modal-box .row-b > * { flex: 1; padding: 10px; border-radius: 11px; font-weight: 700; font-size: 14px; text-decoration: none; border: 1.5px solid #DCECF5; background: #fff; color: #475569; cursor: pointer; }
    .pk-modal-box .row-b a { background: #1D7FE5; border-color: #1D7FE5; color: #fff; }

    @media (max-width: 640px) {
        .pk-wrap { padding-top: 16px; }
        .pk-hero { border-radius: 18px; padding: 26px 16px 34px; }
        .pk-tab { padding: 10px 14px; font-size: 13.5px; }
        .pk-goal { grid-template-columns: 1fr; }
    }
</style>

<div class="pk-wrap">
    <section class="pk-hero">
        <small><i class="fas fa-tags"></i> Tutor &amp; Student Service</small>
        <h1>মাষ্টার মশাই প্যাকেজ</h1>
        <p>শিক্ষক ও শিক্ষার্থীদের জন্য আলাদা সুবিধা — শিক্ষার্থী যেন সহজে প্রয়োজনীয় শিক্ষক খুঁজে পায়, আর শিক্ষক যেন নিজের যোগ্যতা অনুযায়ী উপযুক্ত শিক্ষার্থীর কাছে পৌঁছাতে পারেন।</p>
    </section>

    <div class="pk-tabs">
        <div class="pk-tabs-in" role="tablist">
            <button type="button" class="pk-tab<?= $tab === 'teacher' ? ' on' : '' ?>" data-pk-tab="teacher" role="tab" aria-selected="<?= $tab === 'teacher' ? 'true' : 'false' ?>"><i class="fas fa-chalkboard-teacher"></i> শিক্ষকদের জন্য</button>
            <button type="button" class="pk-tab<?= $tab === 'student' ? ' on' : '' ?>" data-pk-tab="student" role="tab" aria-selected="<?= $tab === 'student' ? 'true' : 'false' ?>"><i class="fas fa-user-graduate"></i> শিক্ষার্থীদের জন্য</button>
        </div>
    </div>

    <!-- ===== শিক্ষক ===== -->
    <section class="pk-sec" id="pk-teacher"<?= $tab === 'teacher' ? '' : ' hidden' ?>>
        <div class="pk-sec-h">
            <h2>শিক্ষকদের জন্য Subscription</h2>
            <p>নির্দিষ্ট মেয়াদের Subscription নিয়ে নিজের প্রোফাইল সক্রিয় রাখুন এবং শিক্ষার্থীদের সাথে যুক্ত হোন।</p>
        </div>
        <?php if ($teacherPk): ?>
            <div class="pk-grid<?= count($teacherPk) < 3 ? ' few' : '' ?>">
                <?php foreach ($teacherPk as $p) $card($p, 'Subscribe করুন'); ?>
            </div>
        <?php else: ?>
            <div class="pk-empty"><i class="fas fa-box-open fa-2x"></i><p style="margin:10px 0 0;">এখনো কোনো প্যাকেজ যোগ করা হয়নি।</p></div>
        <?php endif; ?>
    </section>

    <!-- ===== শিক্ষার্থী ===== -->
    <section class="pk-sec" id="pk-student"<?= $tab === 'student' ? '' : ' hidden' ?>>
        <div class="pk-sec-h">
            <h2>শিক্ষার্থীদের জন্য</h2>
            <p>Account খোলা ও প্ল্যাটফর্ম ব্যবহার সম্পূর্ণ ফ্রি। শুধু “Teacher Needed / Tutor Required” ধরনের Tuition Post করতে চাইলে পোস্ট প্যাকেজ নিতে হবে।</p>
        </div>
        <div class="pk-grid<?= count($studentPk) + 1 < 3 ? ' few' : '' ?>">
            <div class="pk-card pk-free">
                <span class="pk-badge" style="background:#E8F8EF;color:#157347;border-color:#BFE8CF;">সবসময় ফ্রি</span>
                <div class="pk-name">Free Account</div>
                <div class="pk-dur"><i class="far fa-clock"></i> আজীবন</div>
                <div class="pk-price"><b>৳0</b></div>
                <div class="pk-sub"><span>কোনো খরচ নেই</span></div>
                <ul class="pk-feat">
                    <li><i class="fas fa-check"></i><span>বিনামূল্যে Account খুলুন</span></li>
                    <li><i class="fas fa-check"></i><span>টিউটর খুঁজুন, প্রোফাইল ও রিভিউ দেখুন</span></li>
                    <li><i class="fas fa-check"></i><span>টিউটর সেভ করুন ও Request পাঠান</span></li>
                    <li><i class="fas fa-check"></i><span>Notice, Helpdesk সহ সব ফিচার ব্যবহার করুন</span></li>
                </ul>
                <?php if ($isLoggedIn): ?>
                    <a class="pk-btn" href="/FindTutor" style="text-align:center;text-decoration:none;">টিউটর খুঁজুন</a>
                <?php else: ?>
                    <a class="pk-btn" href="/Account/Register" style="text-align:center;text-decoration:none;">ফ্রি Account খুলুন</a>
                <?php endif; ?>
            </div>
            <?php foreach ($studentPk as $p) $card($p, 'পোস্ট প্যাকেজ নিন'); ?>
        </div>
        <div class="pk-note"><i class="fas fa-info-circle" style="margin-top:3px;"></i>
            <span>পেমেন্টের পর আপনার প্রয়োজন অনুযায়ী Tuition Post করতে পারবেন — বিষয়, ক্লাস, কোথায় পড়াতে হবে, কতদিন/কত ঘণ্টা, পারিশ্রমিক বা বাজেট ইত্যাদি। শিক্ষকরা পোস্ট দেখে আপনার সাথে যোগাযোগ করবেন।</span></div>
    </section>

    <section class="pk-goal">
        <div><i class="fas fa-search big"></i><div><h3>শিক্ষার্থীর জন্য</h3><p>সহজে প্রয়োজনীয় শিক্ষক খুঁজে পাওয়া — বিষয়, ক্লাস, এলাকা আর বাজেট মিলিয়ে।</p></div></div>
        <div><i class="fas fa-handshake big" style="background:#E8F8EF;color:#157347;"></i><div><h3>শিক্ষকের জন্য</h3><p>নিজের যোগ্যতা অনুযায়ী উপযুক্ত শিক্ষার্থীর কাছে পৌঁছানো এবং নিয়মিত টিউশন পাওয়া।</p></div></div>
    </section>
</div>

<div class="pk-modal" id="pkModal" hidden role="dialog" aria-modal="true" aria-labelledby="pkModalT">
    <div class="pk-modal-box">
        <div class="ic"><i class="fas fa-hourglass-half"></i></div>
        <h3 id="pkModalT">অনলাইন পেমেন্ট শীঘ্রই আসছে</h3>
        <p id="pkModalP"></p>
        <div class="row-b">
            <button type="button" data-pk-close>ঠিক আছে</button>
            <a href="/Helpdesk">Helpdesk</a>
        </div>
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
    var m = document.getElementById('pkModal');
    document.addEventListener('click', function (e) {
        var c = e.target.closest('[data-pk-choose]');
        if (c) {
            document.getElementById('pkModalP').textContent = '“' + c.getAttribute('data-pk-choose') + '” (' + c.getAttribute('data-pk-price') + ') প্যাকেজটি নিতে অনলাইন পেমেন্ট খুব শীঘ্রই চালু হবে। এখনই নিতে চাইলে Helpdesk-এ আমাদের জানান।';
            m.hidden = false; return;
        }
        if (e.target === m || e.target.closest('[data-pk-close]')) m.hidden = true;
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') m.hidden = true; });
})();
</script>
<?php section_end(); ?>
