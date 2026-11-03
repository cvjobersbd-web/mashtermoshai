<?php
/**
 * Admin → Categories → Edit / নতুন ক্যাটাগরি
 * নাম, গ্রুপ, কার্ডের ছবি, ব্যানার, What you will learn, About This Category, Why Choose, টিউটর মেলানোর Keywords
 */
$c = $Model;
$groups = is_array($ViewBag->Groups) ? $ViewBag->Groups : [];
$isNew = (int)$c->Id === 0;
$ViewData['Title'] = $isNew ? 'New Category' : 'Edit Category';
$def = category_default_texts($c->Name !== '' ? $c->Name : 'This Category', (string)$c->GroupName);
$hl = $c->Highlights !== '' ? $c->Highlights : ($isNew ? '' : $def['Highlights']);
$about = $c->About !== '' ? $c->About : ($isNew ? '' : $def['About']);
$why = $c->WhyChoose !== '' ? $c->WhyChoose : ($isNew ? '' : $def['WhyChoose']);
$opts = function_exists('tuition_options') ? tuition_options() : ['subjects' => [], 'classes' => []];
[$tbg, $tfg] = category_tint($c->Name !== '' ? $c->Name : 'x');
?>
<?php partial('Categories/_CardStyles'); ?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .ec-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .ec-head h2 { color: #08224A; margin: 0; font-size: 23px; }
    .ec-head a.back { color: #64748B; font-size: 13.5px; text-decoration: none; display: inline-flex; gap: 6px; align-items: center; margin-bottom: 4px; }
    .ec-btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 16px; border-radius: 10px; border: 1.5px solid #DCECF5; background: #fff; color: #08224A;
        font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer; white-space: nowrap; font-family: inherit; }
    .ec-btn:hover { border-color: #02B8F5; color: #08224A; }
    .ec-btn.pri { border: none; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; }
    .ec-btn.pri:hover { color: #fff; filter: brightness(1.05); }
    .ec-btn.sm { padding: 6px 11px; font-size: 12.5px; border-radius: 8px; }
    .ec-btn:disabled { opacity: .55; cursor: default; }

    .ec-layout { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 20px; align-items: start; }
    .ec-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.06); margin-bottom: 20px; }
    .ec-card h5 { margin: 0 0 4px; color: #08224A; font-weight: 800; font-size: 16.5px; display: flex; gap: 8px; align-items: center; }
    .ec-card > p.sub { margin: 0 0 14px; color: #64748B; font-size: 13px; }
    .ec-f { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ec-f .full { grid-column: 1 / -1; }
    .ec-fg label { display: block; font-weight: 700; color: #334155; font-size: 13px; margin-bottom: 5px; }
    .ec-fg small { display: block; color: #94A3B8; font-size: 12px; margin-top: 4px; line-height: 1.45; }
    .ec-in { width: 100%; height: 42px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; outline: none; font-family: inherit; color: #08224A; background: #fff; }
    textarea.ec-in { height: auto; padding: 10px 12px; line-height: 1.6; resize: vertical; }
    .ec-in:focus { border-color: #02B8F5; box-shadow: 0 0 0 3px rgba(2,184,245,.1); }
    .ec-in.bad { border-color: #F87171; }
    .ec-pre { display: flex; align-items: stretch; }
    .ec-pre span { display: inline-flex; align-items: center; padding: 0 10px; background: #F1F5F9; border: 1.5px solid #DCECF5; border-right: none; border-radius: 10px 0 0 10px; color: #64748B; font-size: 12.5px; white-space: nowrap; }
    .ec-pre .ec-in { border-radius: 0 10px 10px 0; }
    .ec-radio { display: flex; gap: 10px; flex-wrap: wrap; }
    .ec-radio label { display: inline-flex; gap: 8px; align-items: center; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 9px 13px; cursor: pointer; font-weight: 600; font-size: 13.5px; color: #334155; margin: 0; }
    .ec-radio label:has(input:checked) { border-color: #02B8F5; background: #F0FAFF; color: #08224A; }

    .ec-img { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
    .ec-img .box { width: 120px; height: 120px; border-radius: 14px; border: 1.5px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; color: #94A3B8; font-size: 12px; text-align: center; }
    .ec-img .box.wide { width: 220px; height: 90px; }
    .ec-img .box img { width: 100%; height: 100%; object-fit: contain; }
    .ec-img .box.wide img { object-fit: cover; }
    .ec-img .acts { display: flex; flex-direction: column; gap: 7px; flex: 1; min-width: 200px; }
    .ec-img .acts > div { display: flex; gap: 7px; flex-wrap: wrap; }

    .ec-tip { background: #F0F9FF; border: 1px solid #BAE6FD; color: #0C4A6E; border-radius: 10px; padding: 9px 12px; font-size: 12.5px; line-height: 1.6; margin-bottom: 12px; }
    .ec-tip code { background: #fff; padding: 1px 6px; border-radius: 5px; color: #0369A1; font-size: 12px; }
    .ec-def { float: right; font-size: 12px; color: #1D63E0; background: none; border: none; cursor: pointer; padding: 0; font-weight: 600; }

    .ec-side { position: sticky; top: 96px; }
    .ec-side .cc-card { pointer-events: none; }
    .ec-side .cc-img { height: 170px; }
    .ec-side .cc-ic { width: 120px; height: 120px; }
    .ec-sw { display: flex; align-items: center; gap: 10px; font-weight: 700; color: #334155; font-size: 14px; cursor: pointer; margin: 0; }
    .ec-sw input { appearance: none; -webkit-appearance: none; width: 42px; height: 23px; border-radius: 20px; background: #CBD5E1; position: relative; cursor: pointer; transition: background .2s; margin: 0; flex-shrink: 0; }
    .ec-sw input::after { content: ''; position: absolute; top: 2px; left: 2px; width: 19px; height: 19px; border-radius: 50%; background: #fff; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
    .ec-sw input:checked { background: #22C55E; }
    .ec-sw input:checked::after { transform: translateX(19px); }
    .ec-msg { margin-top: 10px; font-size: 13.5px; font-weight: 600; min-height: 20px; }
    .ec-msg.ok { color: #15803D; } .ec-msg.bad { color: #B91C1C; }
    .ec-save { width: 100%; justify-content: center; height: 46px; font-size: 15px; }
    @media (max-width: 1200px) { .ec-layout { grid-template-columns: minmax(0, 1fr); } .ec-side { position: static; } .ec-side .cc-card { max-width: 260px; } }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
    @media (max-width: 640px) { .ec-f { grid-template-columns: 1fr; } .ec-card { padding: 16px 14px; } }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="ec-head">
        <div>
            <a class="back" href="/Admin/Categories"><i class="fas fa-arrow-left"></i> সব ক্যাটাগরি</a>
            <h2><i class="fas fa-layer-group me-2" style="color:#1D7FE5;"></i><?= $isNew ? 'নতুন ক্যাটাগরি' : 'Edit: ' . e($c->Name) ?></h2>
        </div>
        <?php if (!$isNew): ?><a class="ec-btn" href="<?= e($c->Url) ?>" target="_blank" id="ecView"><i class="fas fa-eye"></i> সাইটে দেখুন</a><?php endif; ?>
    </div>

    <form id="ecForm" autocomplete="off" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$c->Id ?>" />
        <div class="ec-layout">
            <div>
                <div class="ec-card">
                    <h5><i class="fas fa-id-card" style="color:#1D7FE5;"></i> মূল তথ্য</h5>
                    <p class="sub">কার্ডে আর পেজের শিরোনামে এই নাম দেখাবে।</p>
                    <div class="ec-f">
                        <div class="ec-fg"><label for="ecName">ক্যাটাগরির নাম *</label><input class="ec-in" id="ecName" name="name" maxlength="60" value="<?= e($c->Name) ?>" placeholder="যেমন: Play, Class 5, IELTS, Guitar" required /></div>
                        <div class="ec-fg"><label for="ecGroup">গ্রুপ *</label>
                            <select class="ec-in" id="ecGroup" name="groupId">
                                <?php foreach ($groups as $g): ?><option value="<?= (int)$g->Id ?>"<?= $g->Id === (int)$c->GroupId ? ' selected' : '' ?>><?= e($g->Name) ?><?= $g->IsActive ? '' : ' (বন্ধ)' ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="ec-fg"><label for="ecSlug">লিংক (slug)</label>
                            <div class="ec-pre"><span>/Categories/Details/</span><input class="ec-in" id="ecSlug" name="slug" maxlength="120" value="<?= e($c->Slug) ?>" placeholder="খালি রাখলে নাম থেকে বানাবে" /></div>
                            <small>শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা আর “-”। একবার শেয়ার হয়ে গেলে না বদলানোই ভালো।</small></div>
                        <div class="ec-fg"><label for="ecIcon">আইকন (ছবি না থাকলে দেখায়)</label>
                            <div class="ec-pre"><span id="ecIconPrev" style="font-size:16px;color:#1D63E0;"><i class="fas <?= e($c->Icon) ?>"></i></span><input class="ec-in" id="ecIcon" name="icon" maxlength="44" value="<?= e($c->Icon) ?>" placeholder="fa-book-open" /></div>
                            <small>Font Awesome আইকনের নাম, যেমন <code>fa-guitar</code>, <code>fa-calculator</code>, <code>fa-language</code></small></div>
                    </div>
                </div>

                <div class="ec-card">
                    <h5><i class="fas fa-image" style="color:#0EA5E9;"></i> ছবি</h5>
                    <p class="sub">JPG/PNG/WEBP, সর্বোচ্চ 5MB। নিজের বা ফ্রি-লাইসেন্সের ছবি ব্যবহার করুন।</p>
                    <div class="ec-fg" style="margin-bottom:16px;"><label>কার্ডের ছবি (চারকোনা, সাদা/স্বচ্ছ ব্যাকগ্রাউন্ড — যেমন 600×600)</label>
                        <div class="ec-img" data-img="imageUrl">
                            <div class="box"><?php if ($c->ImageUrl !== ''): ?><img src="<?= e($c->ImageUrl) ?>" alt="" /><?php else: ?>ছবি নেই<?php endif; ?></div>
                            <div class="acts">
                                <div><label class="ec-btn sm" style="margin:0;"><i class="fas fa-upload"></i> আপলোড<input type="file" accept="image/*" hidden data-up /></label>
                                    <button type="button" class="ec-btn sm" data-rm><i class="fas fa-xmark"></i> সরান</button></div>
                                <input class="ec-in" name="imageUrl" value="<?= e($c->ImageUrl) ?>" placeholder="অথবা ছবির লিংক (https://...)" />
                            </div>
                        </div></div>
                    <div class="ec-fg"><label>ব্যানার ছবি (ঐচ্ছিক — চওড়া, যেমন 1600×450; না দিলে রঙিন ব্যাকগ্রাউন্ড + কার্ডের ছবি)</label>
                        <div class="ec-img" data-img="bannerUrl">
                            <div class="box wide"><?php if ($c->BannerUrl !== ''): ?><img src="<?= e($c->BannerUrl) ?>" alt="" /><?php else: ?>ব্যানার নেই<?php endif; ?></div>
                            <div class="acts">
                                <div><label class="ec-btn sm" style="margin:0;"><i class="fas fa-upload"></i> আপলোড<input type="file" accept="image/*" hidden data-up /></label>
                                    <button type="button" class="ec-btn sm" data-rm><i class="fas fa-xmark"></i> সরান</button></div>
                                <input class="ec-in" name="bannerUrl" value="<?= e($c->BannerUrl) ?>" placeholder="অথবা ছবির লিংক (https://...)" />
                            </div>
                        </div></div>
                </div>

                <div class="ec-card">
                    <h5><i class="fas fa-pen-nib" style="color:#8B5CF6;"></i> পেজের লেখা</h5>
                    <p class="sub">খালি রাখলে অটোমেটিক ডিফল্ট লেখা দেখাবে (নামের সাথে মিলিয়ে)।</p>
                    <div class="ec-tip"><b>লেখার নিয়ম:</b> <code>## শিরোনাম</code> → বোল্ড শিরোনাম &nbsp;·&nbsp; <code>- লাইন</code> → ✓ টিক চিহ্নের তালিকা &nbsp;·&nbsp; <code>**লেখা**</code> → বোল্ড &nbsp;·&nbsp; ফাঁকা লাইন → নতুন অনুচ্ছেদ</div>
                    <div class="ec-fg" style="margin-bottom:16px;"><label for="ecHl">What you will learn in this category <button type="button" class="ec-def" data-def="highlights">ডিফল্ট লেখা বসান</button></label>
                        <textarea class="ec-in" id="ecHl" name="highlights" rows="4" placeholder="প্রতি লাইনে একটা পয়েন্ট (সর্বোচ্চ ৮টা)"><?= e($hl) ?></textarea>
                        <small>ব্যানারে ✓ চিহ্ন দিয়ে দেখায় — প্রতি লাইনে একটা।</small></div>
                    <div class="ec-fg" style="margin-bottom:16px;"><label for="ecAbout">About This Category <button type="button" class="ec-def" data-def="about">ডিফল্ট লেখা বসান</button></label>
                        <textarea class="ec-in" id="ecAbout" name="about" rows="12" maxlength="6000"><?= e($about) ?></textarea></div>
                    <div class="ec-fg"><label for="ecWhy">Why Choose (ডান পাশের কার্ড — নিচে “Hire A Tutor” বাটন থাকে) <button type="button" class="ec-def" data-def="whyChoose">ডিফল্ট লেখা বসান</button></label>
                        <textarea class="ec-in" id="ecWhy" name="whyChoose" rows="10" maxlength="4000"><?= e($why) ?></textarea></div>
                </div>

                <div class="ec-card">
                    <h5><i class="fas fa-chalkboard-user" style="color:#22C55E;"></i> Suggested Tutors মেলানো</h5>
                    <p class="sub">টিউটরের প্রোফাইলের Subject বা Class/Level এর সাথে নিচের Keywords হুবহু মিললে সেই টিউটর এই ক্যাটাগরিতে দেখাবে। “See All” চাপলে Find Tutor পেজ এই ফিল্টারে খুলবে।</p>
                    <div class="ec-f">
                        <div class="ec-fg full"><label>কী দিয়ে মেলাবে?</label>
                            <div class="ec-radio">
                                <label><input type="radio" name="matchType" value="class"<?= $c->MatchType === 'class' ? ' checked' : '' ?> /> Class / Level / Medium</label>
                                <label><input type="radio" name="matchType" value="subject"<?= $c->MatchType !== 'class' ? ' checked' : '' ?> /> Subject</label>
                            </div></div>
                        <div class="ec-fg full"><label for="ecKw">Keywords (কমা দিয়ে আলাদা)</label>
                            <input class="ec-in" id="ecKw" name="keywords" list="ecKwList" maxlength="800" value="<?= e($c->Keywords) ?>" placeholder="যেমন: Class 5, Class Five" />
                            <datalist id="ecKwList"><?php foreach (array_unique(array_merge($opts['classes'] ?? [], $opts['subjects'] ?? [], ['Bangla Medium', 'English Medium'])) as $o): ?><option value="<?= e($o) ?>"></option><?php endforeach; ?></datalist>
                            <small>খালি রাখলে কোনো টিউটর মিলবে না। টিউটরদের প্রোফাইলে যে বানানে লেখা থাকে সেভাবেই লিখুন।</small></div>
                    </div>
                </div>
            </div>

            <aside class="ec-side">
                <div class="ec-card">
                    <h5 style="margin-bottom:12px;"><i class="fas fa-eye" style="color:#1D7FE5;"></i> কার্ড প্রিভিউ</h5>
                    <div class="cc-card" id="ecPrev">
                        <div class="cc-img"><?php if ($c->ImageUrl !== ''): ?><img src="<?= e($c->ImageUrl) ?>" alt="" /><?php else: ?><span class="cc-ic" style="background:<?= $tbg ?>;color:<?= $tfg ?>;"><i class="fas <?= e($c->Icon) ?>"></i></span><?php endif; ?></div>
                        <div class="cc-name"><?= e($c->Name !== '' ? $c->Name : 'Category') ?></div>
                    </div>
                    <div style="margin-top:16px;"><label class="ec-sw"><input type="checkbox" name="isActive" value="true"<?= $c->IsActive ? ' checked' : '' ?> /> সাইটে চালু</label></div>
                    <div style="margin-top:16px;"><button class="ec-btn pri ec-save" type="submit" id="ecSave"><i class="fas fa-floppy-disk"></i> <?= $isNew ? 'ক্যাটাগরি যোগ করুন' : 'সেভ করুন' ?></button></div>
                    <div class="ec-msg" id="ecMsg"></div>
                </div>
            </aside>
        </div>
    </form>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var form = document.getElementById('ecForm'), msg = document.getElementById('ecMsg'), btn = document.getElementById('ecSave');
    function token() { return form.querySelector('input[name="__RequestVerificationToken"]').value; }
    function show(t, ok) { msg.textContent = t; msg.className = 'ec-msg ' + (ok ? 'ok' : 'bad'); }
    async function post(url, fd) {
        fd.append('__RequestVerificationToken', token());
        try { var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); return await r.json(); }
        catch (e) { return { success: false, message: 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।' }; }
    }

    // ===== প্রিভিউ =====
    var prev = document.getElementById('ecPrev');
    function esc(t) { return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
    function icon() { var v = form.icon.value.trim().toLowerCase(); return /^fa-[a-z0-9-]{1,40}$/.test(v) ? v : 'fa-book-open'; }
    function updPrev() {
        prev.querySelector('.cc-name').textContent = form.name.value.trim() || 'Category';
        var img = form.imageUrl.value.trim(), box = prev.querySelector('.cc-img');
        var ic = box.querySelector('.cc-ic');
        if (img) box.innerHTML = '<img src="' + esc(img) + '" alt="" />';
        else { var st = ic ? ic.getAttribute('style') : 'background:#E0F2FE;color:#0369A1;'; box.innerHTML = '<span class="cc-ic" style="' + st + '"><i class="fas ' + icon() + '"></i></span>'; }
        document.getElementById('ecIconPrev').innerHTML = '<i class="fas ' + icon() + '"></i>';
    }
    ['name', 'icon', 'imageUrl'].forEach(function (n) { form[n].addEventListener('input', updPrev); });

    // ===== ছবি আপলোড / সরানো / লিংক =====
    document.querySelectorAll('[data-img]').forEach(function (w) {
        var input = w.querySelector('input.ec-in'), box = w.querySelector('.box'), empty = box.classList.contains('wide') ? 'ব্যানার নেই' : 'ছবি নেই';
        function setBox(u) { box.innerHTML = u ? '<img src="' + esc(u) + '" alt="" />' : empty; }
        input.addEventListener('change', function () { setBox(input.value.trim()); });
        w.querySelector('[data-rm]').addEventListener('click', function () { input.value = ''; setBox(''); updPrev(); });
        w.querySelector('[data-up]').addEventListener('change', async function () {
            var f = this.files[0]; this.value = ''; if (!f) return;
            if (f.size > 5 * 1024 * 1024) { show('ছবি ৫MB এর কম হতে হবে।'); return; }
            var fd = new FormData(); fd.append('categoryImage', f);
            box.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            var r = await post('/Admin/UploadCategoryImage', fd);
            if (r && r.success) { input.value = r.imageUrl; setBox(r.imageUrl); updPrev(); show('ছবি আপলোড হয়েছে — সেভ করতে ভুলবেন না।', true); }
            else { setBox(input.value.trim()); show((r && r.message) || 'আপলোড হয়নি।'); }
        });
    });

    // ===== ডিফল্ট লেখা =====
    document.querySelectorAll('[data-def]').forEach(function (b) {
        b.addEventListener('click', async function (e) {
            e.preventDefault();
            var key = b.getAttribute('data-def'), ta = form[key];
            if (ta.value.trim() && !confirm('এখনকার লেখা মুছে ডিফল্ট লেখা বসাবেন?')) return;
            var fd = new FormData(); fd.append('name', form.name.value.trim() || 'This Category'); fd.append('groupId', form.groupId.value);
            var r = await post('/Admin/CategoryDefaultText', fd);
            if (r && r.success) ta.value = r[key]; else show((r && r.message) || 'পাওয়া যায়নি।');
        });
    });

    // ===== সেভ =====
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        form.querySelectorAll('.bad').forEach(function (x) { x.classList.remove('bad'); });
        if (!form.name.value.trim()) { form.name.classList.add('bad'); form.name.focus(); show('ক্যাটাগরির নাম লিখুন।'); return; }
        btn.disabled = true; show('সেভ হচ্ছে...', true);
        var fd = new FormData(form); fd.delete('__RequestVerificationToken');
        if (!form.isActive.checked) fd.set('isActive', 'false');
        var r = await post('/Admin/SaveCategory', fd);
        btn.disabled = false;
        if (r && r.success) {
            show(r.message, true);
            form.slug.value = r.slug;
            if (+form.id.value === 0) setTimeout(function () { location.href = '/Admin/EditCategory/' + r.id + '?saved=1'; }, 500);
            else { var v = document.getElementById('ecView'); if (v) v.href = r.url; }
        } else {
            show((r && r.message) || 'সেভ করা যায়নি।');
            if (r && r.field && form[r.field]) { form[r.field].classList.add('bad'); form[r.field].focus(); }
        }
    });
    if (/[?&]saved=1/.test(location.search)) { show('নতুন ক্যাটাগরি যোগ হয়েছে!', true); history.replaceState(null, '', location.pathname); }
})();
</script>
<?php section_end(); ?>
