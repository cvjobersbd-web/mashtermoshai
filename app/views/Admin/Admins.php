<?php
/** Admin → Admins (শুধু Super Admin): অ্যাডমিন যোগ, অনুমতি ঠিক করা, চালু/বন্ধ, পাসওয়ার্ড রিসেট, সরানো */
$ViewData['Title'] = 'Admins';
$admins = is_array($Model) ? $Model : [];
$perms = admin_permissions();
$me = (int)current_user_id();
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .am-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .am-head h2 { color: #08224A; margin: 0; font-size: 24px; }
    .am-head p { color: #64748B; margin: 4px 0 0; font-size: 14px; max-width: 720px; }
    .am-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 15px; border-radius: 10px; border: 1.5px solid #DCECF5; background: #fff; color: #08224A;
        font-weight: 700; font-size: 13.5px; text-decoration: none; cursor: pointer; white-space: nowrap; font-family: inherit; }
    .am-btn:hover { border-color: #02B8F5; color: #08224A; }
    .am-btn.pri { border: none; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; }
    .am-btn.del { color: #B91C1C; border-color: #FBD5D5; }
    .am-btn.sm { padding: 6px 11px; font-size: 12.5px; border-radius: 8px; }
    .am-btn:disabled { opacity: .45; cursor: default; }
    .am-list { display: flex; flex-direction: column; gap: 12px; }
    .am-card { background: #fff; border-radius: 16px; padding: 16px 18px; box-shadow: 0 4px 18px rgba(0,0,0,.05); border: 1.5px solid #E8EEF5; display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 14px; align-items: center; }
    .am-card.off { background: #F8FAFC; opacity: .8; }
    .am-card.super { border-color: #FCD34D; }
    .am-av { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 17px; position: relative; overflow: hidden; }
    .am-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .am-who b { font-size: 16px; color: #08224A; }
    .am-who .sub { color: #64748B; font-size: 13px; margin-top: 2px; word-break: break-all; }
    .am-tag { display: inline-flex; gap: 5px; align-items: center; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; margin-left: 6px; vertical-align: 2px; }
    .am-tag.super { background: linear-gradient(135deg, #F5A623, #FFB300); color: #3B2600; }
    .am-tag.adm { background: #E0F2FE; color: #0369A1; }
    .am-tag.off { background: #FEE2E2; color: #B91C1C; }
    .am-tag.me { background: #DCFCE7; color: #166534; }
    .am-perms { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; }
    .am-perms span { font-size: 11.5px; background: #F1F5F9; color: #334155; padding: 3px 9px; border-radius: 999px; font-weight: 600; }
    .am-acts { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
    .am-modal { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 4000; display: none; align-items: center; justify-content: center; padding: 16px; }
    .am-modal.open { display: flex; }
    .am-modal .box { background: #fff; border-radius: 18px; padding: 22px; width: 100%; max-width: 560px; max-height: 92vh; overflow: auto; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
    .am-modal h4 { margin: 0 0 14px; color: #08224A; font-size: 19px; font-weight: 800; }
    .am-fg { margin-bottom: 12px; }
    .am-fg label { display: block; font-weight: 700; color: #334155; font-size: 13px; margin-bottom: 5px; }
    .am-fg input[type=text], .am-fg input[type=email], .am-fg input[type=password] { width: 100%; height: 42px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; outline: none; font-family: inherit; }
    .am-fg input:focus { border-color: #02B8F5; }
    .am-fg input.bad { border-color: #F87171; }
    .am-fg small { color: #94A3B8; font-size: 12px; display: block; margin-top: 4px; }
    .am-type { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
    .am-type label { display: flex; gap: 10px; align-items: flex-start; border: 1.5px solid #E3ECF5; border-radius: 12px; padding: 11px 12px; cursor: pointer; margin: 0; }
    .am-type label:has(input:checked) { border-color: #02B8F5; background: #F0FAFF; }
    .am-type b { display: block; color: #08224A; font-size: 14px; } .am-type small { color: #64748B; font-size: 12px; }
    .am-type input { margin-top: 3px; }
    .am-pgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .am-pgrid label { display: flex; gap: 9px; align-items: flex-start; border: 1px solid #E3ECF5; border-radius: 10px; padding: 9px 10px; cursor: pointer; margin: 0; font-size: 13px; }
    .am-pgrid label:has(input:checked) { border-color: #22C55E; background: #F0FDF4; }
    .am-pgrid b { display: block; color: #08224A; font-size: 13px; } .am-pgrid small { color: #64748B; font-size: 11.5px; line-height: 1.35; display: block; }
    .am-pgrid input { margin-top: 3px; }
    .am-note { background: #FFF8E6; border: 1px solid #F8E1A5; color: #7A4B00; border-radius: 10px; padding: 9px 12px; font-size: 12.5px; margin: 10px 0 0; line-height: 1.6; }
    .am-msg { margin-top: 10px; font-size: 13.5px; font-weight: 600; min-height: 18px; }
    .am-msg.bad { color: #B91C1C; } .am-msg.ok { color: #15803D; }
    .am-mf { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
    .am-info { background: #F0F9FF; border: 1px solid #BAE6FD; color: #0C4A6E; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; margin-bottom: 18px; line-height: 1.7; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
    @media (max-width: 640px) { .am-card { grid-template-columns: auto minmax(0, 1fr); } .am-acts { grid-column: 1 / -1; justify-content: flex-start; } .am-type, .am-pgrid { grid-template-columns: 1fr; } }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="am-head">
        <div>
            <h2><i class="fas fa-user-shield me-2" style="color:#F5A623;"></i>Admins</h2>
            <p>কে অ্যাডমিন, কে Super Admin, আর কে কোন অংশে কাজ করতে পারবে — এখান থেকে ঠিক করুন। এই পেজ শুধু Super Admin দেখতে পারেন।</p>
        </div>
        <button type="button" class="am-btn pri" id="amAdd"><i class="fas fa-user-plus"></i> নতুন অ্যাডমিন</button>
    </div>
    <?= csrf_field() ?>
    <div class="am-info"><i class="fas fa-circle-info"></i> <b>Super Admin</b> সবকিছু করতে পারেন — অ্যাডমিন যোগ/বাদ, প্যাকেজের দাম, পেমেন্ট নম্বর, Email সেটিং আর Activity Log।
        <b>Admin</b> শুধু টিক দেওয়া অংশগুলোতে কাজ করতে পারেন। কাউকে <b>বন্ধ</b> করলে সাথে সাথে লগআউট হয়ে যায়।</div>

    <div class="am-list">
        <?php foreach ($admins as $a): ?>
            <div class="am-card<?= $a->IsSuper ? ' super' : '' ?><?= $a->IsActive ? '' : ' off' ?>" data-id="<?= (int)$a->Id ?>" data-name="<?= e($a->Name) ?>"
                 data-super="<?= $a->IsSuper ? '1' : '0' ?>" data-perms="<?= e(implode(',', $a->PermList)) ?>">
                <span class="am-av"><?= e($a->Initials) ?><?php if (!is_blank($a->ProfileImageLink)): ?><img src="<?= e($a->ProfileImageLink) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                <div class="am-who">
                    <b><?= e($a->Name) ?></b>
                    <?php if ($a->IsSuper): ?><span class="am-tag super"><i class="fas fa-crown"></i> Super Admin</span><?php else: ?><span class="am-tag adm">Admin</span><?php endif; ?>
                    <?php if (!$a->IsActive): ?><span class="am-tag off">বন্ধ</span><?php endif; ?>
                    <?php if ($a->Id === $me): ?><span class="am-tag me">আপনি</span><?php endif; ?>
                    <div class="sub"><i class="far fa-envelope"></i> <?= e($a->Email) ?> · <i class="far fa-user"></i> <?= e($a->UserName) ?> · শেষ লগইন: <?= $a->LastLoginAt ? e(time_ago($a->LastLoginAt)) : '—' ?></div>
                    <?php if (!$a->IsSuper): ?>
                        <div class="am-perms"><?php foreach ($a->PermList as $k): if (!isset($perms[$k])) continue; ?><span><i class="fas <?= $perms[$k][1] ?>"></i> <?= e($perms[$k][0]) ?></span><?php endforeach; ?>
                            <?php if (!$a->PermList): ?><span style="background:#FEE2E2;color:#B91C1C;">কোনো অনুমতি নেই</span><?php endif; ?></div>
                    <?php endif; ?>
                </div>
                <div class="am-acts">
                    <button type="button" class="am-btn sm" data-am="edit"><i class="fas fa-sliders"></i> অনুমতি</button>
                    <button type="button" class="am-btn sm" data-am="pw"><i class="fas fa-key"></i> পাসওয়ার্ড</button>
                    <?php if ($a->Id !== $me): ?>
                        <button type="button" class="am-btn sm" data-am="active" data-on="<?= $a->IsActive ? '0' : '1' ?>"><i class="fas <?= $a->IsActive ? 'fa-ban' : 'fa-circle-check' ?>"></i> <?= $a->IsActive ? 'বন্ধ করুন' : 'চালু করুন' ?></button>
                        <button type="button" class="am-btn sm del" data-am="remove" title="অ্যাডমিন থেকে সরান"><i class="fas fa-user-minus"></i></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- নতুন / এডিট -->
<div class="am-modal" id="amModal" role="dialog" aria-modal="true" aria-labelledby="amTitle">
    <form class="box" id="amForm" autocomplete="off" novalidate>
        <h4 id="amTitle">নতুন অ্যাডমিন</h4>
        <input type="hidden" name="id" value="0" />
        <div data-new>
            <div class="am-fg"><label for="amName">নাম</label><input type="text" id="amName" name="fullName" maxlength="80" placeholder="যেমন: Mohammad Bin Monjur" /></div>
            <div class="am-fg"><label for="amEmail">ইমেইল (এটা দিয়ে লগইন করবেন)</label><input type="email" id="amEmail" name="email" maxlength="120" placeholder="name@gmail.com" /></div>
            <div class="am-fg"><label for="amPw">পাসওয়ার্ড</label><input type="password" id="amPw" name="password" maxlength="100" autocomplete="new-password" />
                <small>কমপক্ষে ৮ অক্ষর — বড় হাতের, ছোট হাতের অক্ষর, সংখ্যা আর চিহ্ন (যেমন @) লাগবে।</small></div>
        </div>
        <div class="am-type">
            <label><input type="radio" name="isSuper" value="true" /><span><b><i class="fas fa-crown" style="color:#F5A623;"></i> Super Admin</b><small>সবকিছু করতে পারবেন</small></span></label>
            <label><input type="radio" name="isSuper" value="false" checked /><span><b><i class="fas fa-user-shield" style="color:#0369A1;"></i> Admin</b><small>শুধু নিচে টিক দেওয়া অংশ</small></span></label>
        </div>
        <div id="amPermBox">
            <div class="am-pgrid">
                <?php foreach ($perms as $k => [$lbl, $ic, $desc]): ?>
                    <label><input type="checkbox" name="perm" value="<?= $k ?>" /><span><b><i class="fas <?= $ic ?>"></i> <?= e($lbl) ?></b><small><?= e($desc) ?></small></span></label>
                <?php endforeach; ?>
            </div>
            <div class="am-note"><i class="fas fa-lock"></i> প্যাকেজের দাম, পেমেন্ট নম্বর (bKash/Nagad), Email সেটিং, ইউজার মুছে ফেলা আর অ্যাডমিন ম্যানেজমেন্ট — এগুলো শুধু Super Admin পারেন।</div>
        </div>
        <div class="am-msg" id="amMsg"></div>
        <div class="am-mf"><button type="button" class="am-btn" data-close>বাতিল</button><button type="submit" class="am-btn pri" id="amSave"><i class="fas fa-save"></i> সেভ</button></div>
    </form>
</div>

<!-- পাসওয়ার্ড -->
<div class="am-modal" id="pwModal" role="dialog" aria-modal="true">
    <form class="box" id="pwForm" autocomplete="off" novalidate style="max-width:420px;">
        <h4>পাসওয়ার্ড বদলান — <span id="pwWho"></span></h4>
        <div class="am-fg"><label for="pwNew">নতুন পাসওয়ার্ড</label><input type="password" id="pwNew" maxlength="100" autocomplete="new-password" />
            <small>কমপক্ষে ৮ অক্ষর — বড় হাতের, ছোট হাতের অক্ষর, সংখ্যা আর চিহ্ন।</small></div>
        <div class="am-msg" id="pwMsg"></div>
        <div class="am-mf"><button type="button" class="am-btn" data-close>বাতিল</button><button type="submit" class="am-btn pri"><i class="fas fa-key"></i> বদলান</button></div>
    </form>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    function tok() { return document.querySelector('input[name="__RequestVerificationToken"]').value; }
    async function post(url, data) {
        var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); }); fd.append('__RequestVerificationToken', tok());
        try { var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); return await r.json(); }
        catch (e) { return { success: false, message: 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।' }; }
    }
    var modal = document.getElementById('amModal'), form = document.getElementById('amForm'), msg = document.getElementById('amMsg');
    var pwModal = document.getElementById('pwModal'), pwId = 0;
    function show(m, t, ok) { m.textContent = t; m.className = 'am-msg ' + (ok ? 'ok' : 'bad'); }
    function syncType() { document.getElementById('amPermBox').hidden = form.querySelector('input[name="isSuper"]:checked').value === 'true'; }
    form.querySelectorAll('input[name="isSuper"]').forEach(function (r) { r.addEventListener('change', syncType); });
    function open(card) {
        form.reset(); msg.textContent = ''; form.querySelectorAll('.bad').forEach(function (x) { x.classList.remove('bad'); });
        var isNew = !card;
        form.id.value = isNew ? 0 : card.getAttribute('data-id');
        document.getElementById('amTitle').textContent = isNew ? 'নতুন অ্যাডমিন' : 'অনুমতি — ' + card.getAttribute('data-name');
        form.querySelector('[data-new]').hidden = !isNew;
        var sup = !isNew && card.getAttribute('data-super') === '1';
        form.querySelector('input[name="isSuper"][value="' + (sup ? 'true' : 'false') + '"]').checked = true;
        var ps = isNew ? [] : (card.getAttribute('data-perms') || '').split(',');
        form.querySelectorAll('input[name="perm"]').forEach(function (c) { c.checked = ps.indexOf(c.value) !== -1; });
        syncType(); modal.classList.add('open');
        setTimeout(function () { (isNew ? document.getElementById('amName') : form.querySelector('input[name="isSuper"]:checked')).focus(); }, 50);
    }
    document.getElementById('amAdd').addEventListener('click', function () { open(null); });
    document.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', function () { b.closest('.am-modal').classList.remove('open'); }); });
    [modal, pwModal].forEach(function (m) { m.addEventListener('click', function (e) { if (e.target === m) m.classList.remove('open'); }); });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var data = { id: form.id.value, isSuper: form.querySelector('input[name="isSuper"]:checked').value,
            perms: Array.prototype.filter.call(form.querySelectorAll('input[name="perm"]'), function (c) { return c.checked; }).map(function (c) { return c.value; }).join(',') };
        if (+form.id.value === 0) { data.fullName = form.fullName.value.trim(); data.email = form.email.value.trim(); data.password = form.password.value; }
        var b = document.getElementById('amSave'); b.disabled = true;
        var r = await post('/Admin/SaveAdmin', data);
        b.disabled = false;
        if (r.success) { show(msg, r.message, true); setTimeout(function () { location.reload(); }, 1200); }
        else { show(msg, r.message || 'সেভ হয়নি।'); if (r.field && form[r.field]) { form[r.field].classList.add('bad'); form[r.field].focus(); } }
    });

    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-am]'); if (!b) return;
        var card = b.closest('[data-id]'), id = card.getAttribute('data-id'), name = card.getAttribute('data-name'), act = b.getAttribute('data-am');
        if (act === 'edit') return open(card);
        if (act === 'pw') { pwId = id; document.getElementById('pwWho').textContent = name; document.getElementById('pwNew').value = ''; document.getElementById('pwMsg').textContent = ''; pwModal.classList.add('open'); document.getElementById('pwNew').focus(); return; }
        if (act === 'active') {
            var on = b.getAttribute('data-on') === '1';
            if (!on && !confirm('"' + name + '" কে বন্ধ করবেন? সাথে সাথে লগআউট হয়ে যাবেন আর লগইন করতে পারবেন না।')) return;
            var r = await post('/Admin/AdminSetActive', { id: id, on: on ? 'true' : 'false' }); alert((r.success ? '✅ ' : '❌ ') + r.message); if (r.success) location.reload();
        }
        if (act === 'remove') {
            if (!confirm('"' + name + '" কে অ্যাডমিন থেকে সরাবেন? অ্যাকাউন্টটা সাধারণ ইউজার হয়ে যাবে।')) return;
            var r2 = await post('/Admin/RemoveAdmin', { id: id }); alert((r2.success ? '✅ ' : '❌ ') + r2.message); if (r2.success) location.reload();
        }
    });
    document.getElementById('pwForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        var m = document.getElementById('pwMsg');
        var r = await post('/Admin/ResetAdminPassword', { id: pwId, password: document.getElementById('pwNew').value });
        show(m, r.message || '', r.success); if (r.success) setTimeout(function () { pwModal.classList.remove('open'); }, 1000);
    });
})();
</script>
<?php section_end(); ?>
