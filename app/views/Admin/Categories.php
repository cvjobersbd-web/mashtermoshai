<?php
/**
 * Admin → Categories: গ্রুপ (English Medium, Bangla Medium ...) আর তার ভেতরের ক্যাটাগরি যোগ/এডিট/চালু-বন্ধ/সাজানো/মুছে ফেলা।
 * ক্যাটাগরির ছবি, ব্যানার, About This Category, Why Choose ইত্যাদি লেখা "Edit" পেজ থেকে বদলানো যায়।
 */
$ViewData['Title'] = 'Categories';
$groups = is_array($Model) ? $Model : [];
$total = array_sum(array_map(fn($g) => count($g->Categories), $groups));
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .ca-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .ca-head h2 { color: #08224A; margin: 0; font-size: 24px; }
    .ca-head p { color: #64748B; margin: 4px 0 0; font-size: 14px; max-width: 760px; }
    .ca-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 15px; border-radius: 10px; border: 1.5px solid #DCECF5; background: #fff; color: #08224A;
        font-weight: 600; font-size: 13.5px; text-decoration: none; cursor: pointer; white-space: nowrap; font-family: inherit; }
    .ca-btn:hover { border-color: #02B8F5; color: #08224A; }
    .ca-btn.pri { border: none; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; }
    .ca-btn.pri:hover { color: #fff; filter: brightness(1.05); }
    .ca-btn.del { color: #B91C1C; border-color: #FBD5D5; }
    .ca-btn.del:hover { border-color: #F87171; color: #B91C1C; }
    .ca-btn:disabled { opacity: .4; cursor: default; }
    .ca-btn.sm { padding: 6px 10px; font-size: 12.5px; border-radius: 8px; }

    .ca-new { display: flex; gap: 10px; flex-wrap: wrap; background: #fff; border-radius: 16px; padding: 16px 18px; box-shadow: 0 4px 20px rgba(0,0,0,.06); margin-bottom: 22px; align-items: center; }
    .ca-new b { color: #08224A; font-size: 14.5px; }
    .ca-in { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; outline: none; font-family: inherit; color: #08224A; min-width: 0; }
    .ca-in:focus { border-color: #02B8F5; }

    .ca-grp { background: #fff; border-radius: 16px; padding: 18px 18px 20px; box-shadow: 0 4px 20px rgba(0,0,0,.06); margin-bottom: 22px; }
    .ca-grp.off { background: #F8FAFC; }
    .ca-grp-h { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
    .ca-grp-h h5 { margin: 0; color: #08224A; font-weight: 800; font-size: 18px; display: flex; align-items: center; gap: 8px; }
    .ca-grp-h .cnt { font-size: 12px; font-weight: 700; background: #E8F4FD; color: #1D63E0; padding: 2px 9px; border-radius: 10px; }
    .ca-grp-h .offb { font-size: 12px; font-weight: 700; background: #FEE2E2; color: #B91C1C; padding: 2px 9px; border-radius: 10px; }
    .ca-grp-h .sp { flex: 1; }
    .ca-grp-h .mv { display: inline-flex; gap: 4px; }

    .ca-sw { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; color: #475569; cursor: pointer; margin: 0; white-space: nowrap; }
    .ca-sw input { appearance: none; -webkit-appearance: none; width: 34px; height: 19px; border-radius: 20px; background: #CBD5E1; position: relative; cursor: pointer; transition: background .2s; margin: 0; flex-shrink: 0; }
    .ca-sw input::after { content: ''; position: absolute; top: 2px; left: 2px; width: 15px; height: 15px; border-radius: 50%; background: #fff; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
    .ca-sw input:checked { background: #22C55E; }
    .ca-sw input:checked::after { transform: translateX(15px); }

    .ca-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 14px; }
    .ca-cat { border: 1.5px solid #DCECF5; border-radius: 14px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
    .ca-cat.off { opacity: .6; background: #F8FAFC; }
    .ca-thumb { height: 110px; display: flex; align-items: center; justify-content: center; background: #F7FAFD; border-bottom: 1px solid #EEF3F8; position: relative; }
    .ca-thumb img { max-width: 90%; max-height: 96px; object-fit: contain; }
    .ca-thumb .ic { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; }
    .ca-thumb .tag { position: absolute; top: 7px; left: 7px; font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 8px; background: #fff; color: #475569; border: 1px solid #E2E8F0; }
    .ca-body { padding: 10px 11px 11px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .ca-name { font-weight: 700; color: #08224A; font-size: 14.5px; line-height: 1.3; }
    .ca-name small { display: block; font-weight: 500; color: #94A3B8; font-size: 11.5px; margin-top: 2px; word-break: break-all; }
    .ca-row { display: flex; align-items: center; gap: 6px; justify-content: space-between; margin-top: auto; }
    .ca-ic-btns { display: inline-flex; gap: 4px; }
    .ca-ic-btns a, .ca-ic-btns button { width: 30px; height: 30px; border-radius: 8px; border: 1px solid #DCECF5; background: #fff; color: #475569; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; font-size: 12px; text-decoration: none; padding: 0; }
    .ca-ic-btns a:hover, .ca-ic-btns button:hover:not(:disabled) { border-color: #02B8F5; color: #08224A; }
    .ca-ic-btns button.del { color: #DC2626; }
    .ca-ic-btns button:disabled { opacity: .35; cursor: default; }
    .ca-empty { border: 1.5px dashed #CBD5E1; border-radius: 14px; padding: 22px; text-align: center; color: #64748B; font-size: 14px; grid-column: 1 / -1; }
    .ca-help { background: #F0F9FF; border: 1px solid #BAE6FD; color: #0C4A6E; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; margin-bottom: 18px; line-height: 1.6; }
    .ca-toast { position: fixed; right: 20px; bottom: 20px; z-index: 3000; background: #08224A; color: #fff; padding: 11px 16px; border-radius: 10px; font-size: 14px;
        opacity: 0; transform: translateY(15px); transition: .25s; pointer-events: none; }
    .ca-toast.show { opacity: 1; transform: none; }
    .ca-toast.err { background: #B91C1C; }
    @media (max-width: 991px) { .admin-main-content { margin-left: 0; padding: 14px; } }
    @media (max-width: 560px) {
        .ca-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .ca-new .ca-in { flex: 1 1 100%; }
        .ca-grp { padding: 14px 12px 16px; }
    }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="ca-head">
        <div>
            <h2><i class="fas fa-layer-group me-2" style="color:#1D7FE5;"></i>Categories</h2>
            <p>ওয়েবসাইটের <b>Categories</b> পেজের সব ক্যাটাগরি। প্রত্যেক ক্যাটাগরির ছবি, ব্যানার, “What you will learn”, “About This Category” আর “Why Choose” লেখা <b>Edit</b> চেপে বদলানো যায়। মোট <?= (int)$total ?>টি ক্যাটাগরি।</p>
        </div>
        <a class="ca-btn" href="/Categories" target="_blank"><i class="fas fa-external-link-alt"></i> Categories পেজ দেখুন</a>
    </div>
    <?= csrf_field() ?>

    <div class="ca-help"><i class="fas fa-circle-info"></i> ছবি না দিলে কার্ডে আইকন দেখায়। নিজের বানানো বা ফ্রি-লাইসেন্সের ইলাস্ট্রেশন (সাদা/স্বচ্ছ ব্যাকগ্রাউন্ড, চারকোনা, যেমন 600×600) আপলোড করলে সবচেয়ে সুন্দর দেখাবে।
        <b>Suggested Tutors</b> মেলানো হয় ক্যাটাগরির “Keywords” দিয়ে — টিউটরের প্রোফাইলের Subject বা Class/Level এর সাথে হুবহু মিললে দেখায়।</div>

    <form class="ca-new" id="caNewGroup" autocomplete="off">
        <b><i class="fas fa-folder-plus" style="color:#1D7FE5;"></i> নতুন গ্রুপ</b>
        <input class="ca-in" name="name" maxlength="60" placeholder="যেমন: Skill Development" style="flex:1 1 240px;" />
        <button class="ca-btn pri" type="submit"><i class="fas fa-plus"></i> গ্রুপ যোগ করুন</button>
    </form>

    <?php if (!$groups): ?>
        <div class="ca-grp"><div class="ca-empty">কোনো গ্রুপ নেই — উপরে থেকে একটা গ্রুপ যোগ করুন, তারপর তার ভেতরে ক্যাটাগরি যোগ করুন।</div></div>
    <?php endif; ?>

    <?php foreach ($groups as $gi => $g): ?>
        <section class="ca-grp<?= $g->IsActive ? '' : ' off' ?>" data-group="<?= (int)$g->Id ?>" data-name="<?= e($g->Name) ?>">
            <div class="ca-grp-h">
                <h5><i class="fas fa-folder-open" style="color:#F5A623;"></i><span data-gname><?= e($g->Name) ?></span></h5>
                <span class="cnt"><?= count($g->Categories) ?>টি</span>
                <?php if (!$g->IsActive): ?><span class="offb">বন্ধ — সাইটে দেখায় না</span><?php endif; ?>
                <span class="sp"></span>
                <label class="ca-sw"><input type="checkbox" data-gactive<?= $g->IsActive ? ' checked' : '' ?> /> চালু</label>
                <span class="mv">
                    <button class="ca-btn sm" type="button" data-gmv="-1" title="উপরে"<?= $gi === 0 ? ' disabled' : '' ?>><i class="fas fa-arrow-up"></i></button>
                    <button class="ca-btn sm" type="button" data-gmv="1" title="নিচে"<?= $gi === count($groups) - 1 ? ' disabled' : '' ?>><i class="fas fa-arrow-down"></i></button>
                </span>
                <button class="ca-btn sm" type="button" data-grename><i class="fas fa-pen"></i> নাম বদলান</button>
                <button class="ca-btn sm del" type="button" data-gdel><i class="fas fa-trash"></i></button>
                <a class="ca-btn sm pri" href="/Admin/EditCategory?group=<?= (int)$g->Id ?>"><i class="fas fa-plus"></i> ক্যাটাগরি যোগ করুন</a>
            </div>
            <div class="ca-grid">
                <?php if (!$g->Categories): ?><div class="ca-empty">এই গ্রুপে কোনো ক্যাটাগরি নেই।</div><?php endif; ?>
                <?php foreach ($g->Categories as $ci => $c): [$bg, $fg] = category_tint($c->Name); ?>
                    <div class="ca-cat<?= $c->IsActive ? '' : ' off' ?>" data-id="<?= (int)$c->Id ?>">
                        <div class="ca-thumb">
                            <span class="tag"><?= $c->MatchType === 'class' ? 'Class' : 'Subject' ?></span>
                            <?php if ($c->ImageUrl !== ''): ?><img src="<?= e($c->ImageUrl) ?>" alt="" loading="lazy" />
                            <?php else: ?><span class="ic" style="background:<?= $bg ?>;color:<?= $fg ?>;"><i class="fas <?= e($c->Icon) ?>"></i></span><?php endif; ?>
                        </div>
                        <div class="ca-body">
                            <div class="ca-name"><?= e($c->Name) ?><small>/<?= e($c->Slug) ?></small></div>
                            <div class="ca-row">
                                <label class="ca-sw"><input type="checkbox" data-cactive<?= $c->IsActive ? ' checked' : '' ?> /> চালু</label>
                                <span class="ca-ic-btns">
                                    <button type="button" data-cmv="-1" title="আগে"<?= $ci === 0 ? ' disabled' : '' ?>><i class="fas fa-arrow-left"></i></button>
                                    <button type="button" data-cmv="1" title="পরে"<?= $ci === count($g->Categories) - 1 ? ' disabled' : '' ?>><i class="fas fa-arrow-right"></i></button>
                                </span>
                            </div>
                            <div class="ca-row">
                                <a class="ca-btn sm" href="/Admin/EditCategory/<?= (int)$c->Id ?>" style="flex:1;justify-content:center;"><i class="fas fa-pen"></i> Edit</a>
                                <span class="ca-ic-btns">
                                    <a href="<?= e($c->Url) ?>" target="_blank" title="সাইটে দেখুন"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="del" data-cdel title="মুছে ফেলুন"><i class="fas fa-trash"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<div class="ca-toast" id="caToast"></div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    function token() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    async function post(url, data) {
        var fd = new FormData();
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', token());
        try { var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); return await r.json(); }
        catch (e) { return { success: false, message: 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।' }; }
    }
    var toast = document.getElementById('caToast'), tt;
    function say(m, bad) { toast.textContent = m; toast.className = 'ca-toast show' + (bad ? ' err' : ''); clearTimeout(tt); tt = setTimeout(function () { toast.className = 'ca-toast'; }, 2600); }
    function done(r, reload) { if (!r || !r.success) { say((r && r.message) || 'কিছু একটা ভুল হয়েছে।', true); return false; } if (r.message) say(r.message); if (reload) setTimeout(function () { location.reload(); }, 450); return true; }

    // নতুন গ্রুপ
    document.getElementById('caNewGroup').addEventListener('submit', async function (e) {
        e.preventDefault();
        var n = this.name.value.trim(); if (!n) { this.name.focus(); return; }
        done(await post('/Admin/SaveCategoryGroup', { id: 0, name: n, isActive: 'true' }), true);
    });

    function groupIds() { return Array.prototype.map.call(document.querySelectorAll('.ca-grp[data-group]'), function (g) { return g.getAttribute('data-group'); }); }

    document.addEventListener('click', async function (e) {
        var b = e.target.closest('button'); if (!b) return;
        var grp = b.closest('.ca-grp[data-group]'); if (!grp) return;
        var gid = grp.getAttribute('data-group');
        if (b.hasAttribute('data-grename')) {
            var n = prompt('গ্রুপের নতুন নাম:', grp.getAttribute('data-name')); if (n === null || !n.trim()) return;
            done(await post('/Admin/SaveCategoryGroup', { id: gid, name: n.trim(), isActive: grp.querySelector('[data-gactive]').checked ? 'true' : 'false' }), true);
        } else if (b.hasAttribute('data-gdel')) {
            var cnt = grp.querySelectorAll('.ca-cat').length;
            if (!confirm('"' + grp.getAttribute('data-name') + '" গ্রুপ মুছে ফেলবেন?' + (cnt ? '\nভেতরের ' + cnt + 'টি ক্যাটাগরিও মুছে যাবে!' : ''))) return;
            done(await post('/Admin/DeleteCategoryGroup', { id: gid }), true);
        } else if (b.hasAttribute('data-gmv')) {
            var ids = groupIds(), i = ids.indexOf(gid), j = i + +b.getAttribute('data-gmv');
            if (j < 0 || j >= ids.length) return;
            ids.splice(j, 0, ids.splice(i, 1)[0]);
            done(await post('/Admin/ReorderCategoryGroups', { ids: ids.join(',') }), true);
        } else if (b.hasAttribute('data-cmv')) {
            var card = b.closest('.ca-cat'), cards = Array.prototype.slice.call(grp.querySelectorAll('.ca-cat'));
            var ci = cards.indexOf(card), cj = ci + +b.getAttribute('data-cmv');
            if (cj < 0 || cj >= cards.length) return;
            if (cj > ci) cards[cj].after(card); else cards[cj].before(card);
            cards = Array.prototype.slice.call(grp.querySelectorAll('.ca-cat'));
            cards.forEach(function (c, k) { var m = c.querySelectorAll('[data-cmv]'); m[0].disabled = k === 0; m[1].disabled = k === cards.length - 1; });
            var r = await post('/Admin/ReorderCategories', { groupId: gid, ids: cards.map(function (c) { return c.getAttribute('data-id'); }).join(',') });
            if (done(r)) say('ক্রম সেভ হয়েছে');
        } else if (b.hasAttribute('data-cdel')) {
            var cd = b.closest('.ca-cat');
            if (!confirm('"' + cd.querySelector('.ca-name').firstChild.textContent.trim() + '" ক্যাটাগরি মুছে ফেলবেন?')) return;
            if (done(await post('/Admin/DeleteCategory', { id: cd.getAttribute('data-id') }))) cd.remove();
        }
    });

    document.addEventListener('change', async function (e) {
        var t = e.target, grp = t.closest('.ca-grp[data-group]'); if (!grp) return;
        if (t.hasAttribute('data-gactive')) {
            done(await post('/Admin/SaveCategoryGroup', { id: grp.getAttribute('data-group'), name: grp.getAttribute('data-name'), isActive: t.checked ? 'true' : 'false' }), true);
        } else if (t.hasAttribute('data-cactive')) {
            var cd = t.closest('.ca-cat');
            var r = await post('/Admin/ToggleCategory', { id: cd.getAttribute('data-id'), on: t.checked ? 'true' : 'false' });
            if (done(r)) { cd.classList.toggle('off', !t.checked); say(t.checked ? 'ক্যাটাগরি চালু হয়েছে' : 'ক্যাটাগরি বন্ধ করা হয়েছে'); } else t.checked = !t.checked;
        }
    });
})();
</script>
<?php section_end(); ?>
