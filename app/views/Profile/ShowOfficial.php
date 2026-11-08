<?php
/**
 * অ্যাডমিন অ্যাকাউন্টের অফিসিয়াল পেজ (যেমন "Mashter Moshai")।
 * এখানে যে পোস্ট দেওয়া হয় সেটা Educational Feed এও দেখায় — দুই জায়গায় একই পোস্ট।
 * @var Obj $Model  ইউজার
 */
$u = $Model;
$name = (string)$ViewBag->Name;
$isOwner = !empty($ViewBag->IsOwner);
$posts = is_array($ViewBag->Posts) ? $ViewBag->Posts : [];
$stats = is_array($ViewBag->Stats) ? $ViewBag->Stats : ['posts' => 0, 'likes' => 0];
$me = current_user_id();
$photo = (string)($u->ProfileImageLink ?? '');
$isAdmin = is_admin();
$subjects = is_array($ViewBag->Subjects) ? $ViewBag->Subjects : [];
$opts = function_exists('tuition_options') ? tuition_options() : ['subjects' => []];
$videoMb = max(1, (int)floor(((int)$ViewBag->VideoLimit) / 1048576));
$selfUrl = '/Profile/Show/' . (int)$u->Id;
$cover = $ViewBag->Extras ?? null;
$coverUrl = $cover && $cover->CoverImageUrl ? (string)$cover->CoverImageUrl : '';
$coverY = $cover ? (int)$cover->CoverPosY : 50;

$ViewData['Title'] = $name;
$ViewData['FullTitle'] = $name . ' | মাষ্টার মশাই';
$ViewData['Description'] = $name . ' — মাষ্টার মশাই এর অফিসিয়াল পেজ। নোট, ছবি, ভিডিও আর পড়াশোনার টিপস।';
if ($photo !== '') $ViewData['OgImage'] = $photo;
?>
<?php partial('Feed/_Styles'); ?>
<style>
    .ofp { background: #F0F3F8; min-height: 80vh; padding-bottom: 60px; }
    .ofp-wrap { max-width: 1080px; margin: 0 auto; padding: 0 16px; }
    .ofp-head { background: #fff; border-bottom: 1px solid #E3E9F1; }
    .ofp-cover { height: 230px; border-radius: 0 0 18px 18px; position: relative; overflow: hidden;
        background: radial-gradient(circle at 85% 20%, rgba(255,255,255,.22) 0 90px, transparent 91px), radial-gradient(circle at 12% 110%, rgba(255,255,255,.16) 0 120px, transparent 121px), linear-gradient(120deg, #08224A 0%, #0A5A86 55%, #00CFFF 100%); }
    .ofp-cover::after { content: ''; position: absolute; right: 40px; bottom: 26px; width: 150px; height: 90px; opacity: .35;
        background-image: radial-gradient(#fff 2px, transparent 2.4px); background-size: 18px 18px; }
    .ofp-cover img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; user-select: none; -webkit-user-drag: none; }
    .ofp-cover.has-img::after { display: none; }
    .ofp-cover.moving { cursor: grab; } .ofp-cover.moving:active { cursor: grabbing; }
    .ofp-cv-acts, .ofp-cv-mv { position: absolute; right: 14px; bottom: 14px; z-index: 2; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .ofp-cv-mv { left: 14px; right: 14px; justify-content: flex-end; }
    .ofp-cv-mv span { margin-right: auto; background: rgba(15,23,42,.65); color: #fff; padding: 7px 12px; border-radius: 9px; font-size: 13px; font-weight: 600; }
    .ofp-cv-btn { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 9px; border: none; background: rgba(255,255,255,.92); color: #0F172A; font-weight: 700; font-size: 13px; cursor: pointer; font-family: inherit; margin: 0; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
    .ofp-cv-btn:hover { background: #fff; } .ofp-cv-btn.pri { background: #00CFFF; color: #fff; }
    .ofp-cv-btn[hidden], .ofp-cv-mv[hidden], .ofp-cv-acts[hidden] { display: none; }
    @media (max-width: 640px) { .ofp-cv-btn span { display: none; } .ofp-cv-acts { right: 8px; bottom: auto; top: 8px; } }
    .ofp-id { display: flex; align-items: flex-end; gap: 22px; padding: 0 24px 20px; margin-top: -64px; position: relative; flex-wrap: wrap; }
    .ofp-av { width: 150px; height: 150px; border-radius: 50%; border: 5px solid #fff; background: #fff; box-shadow: 0 6px 22px rgba(8,34,74,.18); overflow: hidden; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 46px; font-weight: 800; color: #08224A; position: relative; }
    .ofp-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ofp-name { flex: 1; min-width: 220px; padding-bottom: 6px; }
    .ofp-name h1 { margin: 0; font-family: var(--font-heading); font-size: 30px; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ofp-name h1 .vf { color: #00CFFF; font-size: 22px; }
    .ofp-name .sub { margin-top: 4px; color: #64748B; font-size: 14.5px; }
    .ofp-name .sub b { color: #0F172A; }
    .ofp-actions { display: flex; gap: 10px; padding-bottom: 8px; flex-wrap: wrap; }
    .ofp-btn { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 10px; font-weight: 700; font-size: 14px; text-decoration: none; border: 1px solid #DCE5EF; background: #fff; color: #0F172A; cursor: pointer; font-family: inherit; }
    .ofp-btn:hover { background: #F1F5F9; color: #0F172A; }
    .ofp-btn.pri { background: #00CFFF; border-color: #00CFFF; color: #fff; }
    .ofp-btn.pri:hover { background: #00B5E0; color: #fff; }
    .ofp-tabs { display: flex; gap: 6px; padding: 0 20px; border-top: 1px solid #EEF2F7; }
    .ofp-tabs span { padding: 14px 10px; font-weight: 700; font-size: 14.5px; color: #0F172A; border-bottom: 3px solid #00CFFF; }
    .ofp-grid { display: grid; grid-template-columns: 330px minmax(0, 1fr); gap: 22px; margin-top: 22px; align-items: start; }
    .ofp-card { background: #fff; border: 1px solid #E3E9F1; border-radius: 16px; padding: 18px; box-shadow: 0 2px 12px rgba(15,27,45,.05); margin-bottom: 18px; }
    .ofp-card h3 { margin: 0 0 12px; font-size: 17px; font-weight: 700; color: #0F172A; }
    .ofp-card p { margin: 0 0 12px; color: #475569; font-size: 14px; line-height: 1.6; }
    .ofp-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ofp-stats div { background: #F4F9FE; border-radius: 12px; padding: 12px; text-align: center; }
    .ofp-stats b { display: block; font-size: 22px; color: #0F172A; }
    .ofp-stats span { font-size: 12.5px; color: #64748B; font-weight: 600; }
    .ofp-links a { display: flex; align-items: center; gap: 10px; padding: 9px 0; color: #334155; text-decoration: none; font-size: 14px; font-weight: 600; }
    .ofp-links a:hover { color: #00A8D6; }
    .ofp-links i { width: 18px; text-align: center; color: #00CFFF; }
    .ofp-side { position: sticky; top: 96px; }
    @media (max-width: 900px) {
        .ofp-grid { grid-template-columns: minmax(0, 1fr); }
        .ofp-side { position: static; order: 2; }
    }
    @media (max-width: 640px) {
        .ofp-wrap { padding: 0 10px; }
        .ofp-cover { height: 150px; border-radius: 0 0 14px 14px; }
        .ofp-id { margin-top: -52px; padding: 0 10px 16px; gap: 10px; flex-direction: column; align-items: flex-start; }
        .ofp-av { width: 104px; height: 104px; font-size: 34px; border-width: 4px; }
        .ofp-name { min-width: 0; padding-bottom: 0; }
        .ofp-name h1 { font-size: 23px; }
        .ofp-actions { width: 100%; } .ofp-actions .ofp-btn { flex: 1; justify-content: center; white-space: nowrap; padding: 0 12px; font-size: 13.5px; }
        .ofp-tabs { padding: 0 10px; }
    }
</style>

<div class="ofp">
    <section class="ofp-head">
        <div class="ofp-wrap">
            <div class="ofp-cover<?= $coverUrl !== '' ? ' has-img' : '' ?>" id="ofpCover">
                <img id="ofpCoverImg" src="<?= e($coverUrl) ?>" alt="" style="object-position:50% <?= $coverY ?>%;<?= $coverUrl === '' ? 'display:none;' : '' ?>" />
                <?php if ($isOwner): ?>
                    <div class="ofp-cv-acts" id="ofpCvActs">
                        <label class="ofp-cv-btn" for="ofpCoverFile"><i class="fas fa-camera"></i> <span><?= $coverUrl !== '' ? 'কভার পরিবর্তন' : 'কভার ফটো দিন' ?></span></label>
                        <input type="file" id="ofpCoverFile" accept="image/jpeg,image/png,image/webp" hidden />
                        <button type="button" class="ofp-cv-btn" id="ofpCvMove"<?= $coverUrl === '' ? ' hidden' : '' ?>><i class="fas fa-up-down"></i> সাজান</button>
                        <button type="button" class="ofp-cv-btn" id="ofpCvDel"<?= $coverUrl === '' ? ' hidden' : '' ?>><i class="fas fa-trash"></i> সরান</button>
                    </div>
                    <div class="ofp-cv-mv" id="ofpCvBar" hidden>
                        <span><i class="fas fa-hand"></i> ছবি টেনে উপর-নিচ করুন</span>
                        <button type="button" class="ofp-cv-btn" id="ofpCvCancel">বাতিল</button>
                        <button type="button" class="ofp-cv-btn pri" id="ofpCvSave"><i class="fas fa-check"></i> সেভ</button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ofp-id">
                <div class="ofp-av"><?= e(initials($name, 'M')) ?><?php if ($photo !== ''): ?><img src="<?= e($photo) ?>" alt="<?= e($name) ?>" onerror="this.remove()" /><?php endif; ?></div>
                <div class="ofp-name">
                    <h1><?= e($name) ?> <i class="fas fa-circle-check vf" title="অফিসিয়াল অ্যাকাউন্ট" aria-label="অফিসিয়াল অ্যাকাউন্ট"></i></h1>
                    <div class="sub">মাষ্টার মশাই এর অফিসিয়াল পেজ · <b><?= (int)$stats['posts'] ?></b> পোস্ট · <b><?= (int)$stats['likes'] ?></b> লাইক</div>
                </div>
                <div class="ofp-actions">
                    <?php if ($isOwner): ?>
                        <a class="ofp-btn" href="/Admin/Admins"><i class="fas fa-camera"></i> ছবি পরিবর্তন</a>
                        <a class="ofp-btn pri" href="#efc" onclick="var t=document.getElementById('efcText'); if (t) { t.focus(); } return true;"><i class="fas fa-pen"></i> নতুন পোস্ট</a>
                    <?php else: ?>
                        <a class="ofp-btn pri" href="/Feed"><i class="fas fa-graduation-cap"></i> Educational Feed</a>
                        <button type="button" class="ofp-btn" id="ofpShare"><i class="fas fa-share-nodes"></i> শেয়ার</button>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="ofp-tabs" aria-label="Page tabs"><span><i class="fas fa-newspaper"></i> Posts</span></nav>
        </div>
    </section>

    <div class="ofp-wrap">
        <div class="ofp-grid">
            <aside class="ofp-side">
                <div class="ofp-card">
                    <h3>পরিচিতি</h3>
                    <p><?= e($name) ?> — মাষ্টার মশাই এর অফিসিয়াল অ্যাকাউন্ট। এখানের সব পোস্ট Educational Feed এও প্রকাশ হয়।</p>
                    <div class="ofp-stats">
                        <div><b><?= (int)$stats['posts'] ?></b><span>পোস্ট</span></div>
                        <div><b><?= (int)$stats['likes'] ?></b><span>লাইক</span></div>
                    </div>
                </div>
                <div class="ofp-card ofp-links">
                    <h3>দরকারি লিংক</h3>
                    <a href="/Feed"><i class="fas fa-graduation-cap"></i> Educational Feed</a>
                    <a href="/FindTutor"><i class="fas fa-magnifying-glass"></i> Find Tutor</a>
                    <a href="/Tuition/Circular"><i class="fas fa-clipboard-list"></i> Tuition Circular</a>
                    <a href="/Helpdesk"><i class="fas fa-headset"></i> Helpdesk</a>
                </div>
            </aside>

            <main>
                <?php if ($isOwner): ?>
                    <?php partial('Feed/_Composer', ['me' => $me, 'isAdmin' => $isAdmin, 'myName' => $name, 'myPhoto' => $photo, 'videoMb' => $videoMb, 'subjects' => $subjects, 'opts' => $opts, 'returnUrl' => $selfUrl]); ?>
                <?php endif; ?>
                <div id="efList">
                    <?php foreach ($posts as $p) partial('Feed/_Post', ['p' => $p, 'me' => $me]); ?>
                </div>
                <div class="efp-empty" id="efEmpty"<?= $posts ? ' hidden' : '' ?>>
                    <i class="fas fa-newspaper"></i>
                    <?= $isOwner ? 'এখনো কোনো পোস্ট নেই। উপরে লিখে প্রথম পোস্টটা দিন — সেটা Educational Feed এও দেখাবে।' : 'এখনো কোনো পোস্ট নেই।' ?>
                </div>
                <?php if (!empty($ViewBag->HasMore)): ?><button type="button" class="efp-load" id="efLoad">আরো পোস্ট দেখুন</button><?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php if (!$isOwner): ?><?php partial('Feed/_Composer', ['me' => null, 'isAdmin' => false, 'myName' => '', 'myPhoto' => '', 'videoMb' => 0, 'subjects' => [], 'opts' => [], 'returnUrl' => $selfUrl, 'cssOnly' => true]); ?><?php endif; ?>
<?php partial('Feed/_Js'); ?>
<?php section_start('Scripts'); ?>
<?php if ($isOwner): ?>
<script>
// অফিসিয়াল পেজের কভার ফটো — আপলোড, টেনে সাজানো, সরানো
(function () {
    var cv = document.getElementById('ofpCover'), img = document.getElementById('ofpCoverImg'), file = document.getElementById('ofpCoverFile');
    var acts = document.getElementById('ofpCvActs'), bar = document.getElementById('ofpCvBar'), mv = document.getElementById('ofpCvMove'), del = document.getElementById('ofpCvDel');
    var posY = <?= (int)$coverY ?>, startY = 0, startPos = 0, drag = false, oldPos = posY, pending = null;
    function tok() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    function setPos(y) { posY = Math.max(0, Math.min(100, Math.round(y))); img.style.objectPosition = '50% ' + posY + '%'; }
    function mode(on) { cv.classList.toggle('moving', on); acts.hidden = on; bar.hidden = !on; }
    file.addEventListener('change', function () {
        var f = file.files[0]; if (!f) return;
        if (f.size > 8 * 1024 * 1024) { alert('কভার ছবি ৮ MB এর কম হতে হবে।'); file.value = ''; return; }
        pending = f; oldPos = posY; img.src = URL.createObjectURL(f); img.style.display = ''; cv.classList.add('has-img'); setPos(50); mode(true);
    });
    mv.addEventListener('click', function () { pending = null; oldPos = posY; mode(true); });
    document.getElementById('ofpCvCancel').addEventListener('click', function () { if (pending) location.reload(); else { setPos(oldPos); mode(false); } });
    function down(e) { if (!cv.classList.contains('moving')) return; drag = true; startY = (e.touches ? e.touches[0] : e).clientY; startPos = posY; e.preventDefault(); }
    function move(e) { if (!drag) return; var y = (e.touches ? e.touches[0] : e).clientY; setPos(startPos - (y - startY) * 100 / Math.max(80, cv.offsetHeight)); }
    cv.addEventListener('mousedown', down); cv.addEventListener('touchstart', down, { passive: false });
    window.addEventListener('mousemove', move); window.addEventListener('touchmove', move, { passive: true });
    window.addEventListener('mouseup', function () { drag = false; }); window.addEventListener('touchend', function () { drag = false; });
    document.getElementById('ofpCvSave').addEventListener('click', async function () {
        var b = this; b.disabled = true;
        var fd = new FormData(); fd.append('__RequestVerificationToken', tok()); fd.append('posY', posY); if (pending) fd.append('cover', pending);
        try {
            var d = await (await fetch('/Profile/SaveCover', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            b.disabled = false;
            if (!d.success) { alert('❌ ' + (d.message || 'সেভ হয়নি।')); return; }
            if (d.url) img.src = d.url;
            pending = null; file.value = ''; mode(false); mv.hidden = false; del.hidden = false; acts.querySelector('label span').textContent = 'কভার পরিবর্তন';
        } catch (x) { b.disabled = false; alert('❌ সার্ভারে সমস্যা — আবার চেষ্টা করুন।'); }
    });
    del.addEventListener('click', async function () {
        if (!confirm('কভার ফটো সরিয়ে ফেলবেন?')) return;
        var fd = new FormData(); fd.append('__RequestVerificationToken', tok());
        var d = await (await fetch('/Profile/RemoveCover', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
        if (d.success) { img.style.display = 'none'; img.removeAttribute('src'); cv.classList.remove('has-img'); mv.hidden = true; del.hidden = true; acts.querySelector('label span').textContent = 'কভার ফটো দিন'; }
        else alert('❌ ' + (d.message || ''));
    });
})();
</script>
<?php endif; ?>
<script>
(function () {
    var list = document.getElementById('efList'), more = document.getElementById('efLoad'), empty = document.getElementById('efEmpty');
    var AUTHOR = <?= (int)$u->Id ?>;
    if (more) more.addEventListener('click', async function () {
        var last = list.querySelector('[data-post]:last-child'); if (!last) return;
        more.disabled = true; more.textContent = 'লোড হচ্ছে...';
        try {
            var r = await fetch('/Feed/More?author=' + AUTHOR + '&before=' + last.getAttribute('data-post'));
            list.insertAdjacentHTML('beforeend', await r.text());
            if (r.headers.get('X-Has-More') !== '1') more.remove(); else { more.disabled = false; more.textContent = 'আরো পোস্ট দেখুন'; }
        } catch (e) { more.disabled = false; more.textContent = 'আবার চেষ্টা করুন'; }
    });
    document.addEventListener('ef:removed', function () { empty.hidden = !!list.querySelector('[data-post]'); });
    var sh = document.getElementById('ofpShare');
    if (sh) sh.addEventListener('click', function () {
        var url = location.origin + <?= js($selfUrl) ?>;
        if (navigator.share) { navigator.share({ title: document.title, url: url }).catch(function () {}); return; }
        (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () { EF.toast('লিংক কপি হয়েছে।'); }, function () { prompt('লিংক কপি করুন:', url); });
    });
})();
</script>
<?php if ($isOwner): ?><?php partial('Feed/_ComposerJs', ['videoLimit' => (int)$ViewBag->VideoLimit, 'isAdmin' => $isAdmin, 'tab' => 'profile']); ?><?php endif; ?>
<?php section_end(); ?>
