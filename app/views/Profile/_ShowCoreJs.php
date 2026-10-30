<?php
/**
 * প্রোফাইল পেজের কমন JS (স্টুডেন্ট + টিচার): সেকশন ট্যাব, পোস্ট ফিড, ফ্রেন্ড ফিল্টার, Saved, প্রোফাইল ছবি, কভার, মডাল, পাসওয়ার্ড।
 * দরকার: $u, $isOwner, $tab, $rel, $coverY
 * পেজের নিজস্ব JS window.PF থেকে হেল্পারগুলো পায়।
 */
?>
<script>
(function () {
    var OWNER = <?= (int)$u->Id ?>;
    var IS_OWNER = <?= js($isOwner) ?>;
    window.PC_TIMELINE_OWNER = OWNER;
    function $(s, r) { return (r || document).querySelector(s); }
    function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
    function tokenVal() { var t = $('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    // সার্ভার থেকে নতুন করে পেজ আনা (ক্যাশ থেকে পুরোনো কপি না দেখায়)
    function hardReload() { var u = new URL(location.href); u.searchParams.set('_r', Date.now().toString(36)); location.replace(u.pathname + u.search + location.hash); }
    function say(m, err) { if (window.pcToast) pcToast(m, err); else alert(m); }
    async function postForm(url, data) {
        var fd = new FormData();
        Object.keys(data).forEach(function (k) { if (data[k] !== undefined && data[k] !== null) fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', tokenVal());
        return (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
    }

    // ===== সেকশন বদলানো =====
    var navBar = $('.pf-nav');
    function showTab(key, scroll) {
        if (!document.getElementById('sec-' + key)) return;
        $$('.pf-sec').forEach(function (s) { s.classList.toggle('active', s.id === 'sec-' + key); });
        $$('.pf-nav a').forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-tab') === key); });
        var u = new URL(location.href); u.searchParams.set('tab', key); u.searchParams.delete('_r'); u.hash = '';
        history.replaceState(null, '', u.pathname + u.search);
        var act = $('.pf-nav a.active');
        if (act && navBar.scrollWidth > navBar.clientWidth) navBar.scrollLeft = act.offsetLeft - (navBar.clientWidth - act.offsetWidth) / 2;
        if (scroll) {
            var top = $('.pf-body').getBoundingClientRect().top + window.scrollY - 90;
            if (window.scrollY > top) window.scrollTo({ top: top, behavior: 'smooth' });
        }
    }
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[data-tab]');
        if (!a) return;
        e.preventDefault(); showTab(a.getAttribute('data-tab'), true);
    });
    var initHash = location.hash, initEdit = new URLSearchParams(location.search).get('edit');
    showTab(<?= js($tab) ?>, false);
    if (initHash === '#ab-password') showTab('settings');

    // ===== নতুন পোস্ট =====
    var feed = $('#psFeed'), empty = $('#psEmpty'), countEl = $('#psPostCount');
    function bumpCount(d) {
        var n = Math.max(0, (parseInt(countEl.textContent, 10) || 0) + d);
        countEl.textContent = n + ' post' + (n === 1 ? '' : 's');
        empty.hidden = !!feed.querySelector('.pc');
    }
    document.addEventListener('pc:created', function (e) {
        showTab('overview');
        feed.prepend(e.detail.el); bumpCount(1);
        e.detail.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    document.addEventListener('pc:deleted', function () { bumpCount(-1); });
    document.addEventListener('click', function (e) {
        var c = e.target.closest('[data-compose]'); if (!c) return;
        var t = c.getAttribute('data-compose');
        if (t === 'photo') { window.pcOpenComposer(null, false, 'post'); setTimeout(function () { $('#pmFile').click(); }, 60); }
        else window.pcOpenComposer(null, false, t);
    });

    var more = $('#psMore');
    if (more) more.addEventListener('click', async function () {
        var last = feed.querySelector('.pc:last-child');
        more.disabled = true; more.innerHTML = '<i class="fas fa-spinner fa-spin"></i> লোড হচ্ছে...';
        try {
            var res = await fetch('/Post/Feed?userId=' + OWNER + '&tab=' + encodeURIComponent(feed.getAttribute('data-tab') || '') + '&before=' + (last ? last.getAttribute('data-id') : 0));
            var t = document.createElement('template'); t.innerHTML = await res.text();
            window.pcInitCards(t.content); feed.appendChild(t.content);
            if (res.headers.get('X-Has-More') !== '1') more.hidden = true;
            else { more.disabled = false; more.innerHTML = '<i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন'; }
        } catch (e) { more.disabled = false; more.innerHTML = 'আবার চেষ্টা করুন'; }
    });

    var ff = $('#psFriendFilter');
    if (ff) ff.addEventListener('input', function () {
        var q = ff.value.trim().toLowerCase();
        $$('#psFriendsGrid .fr-card').forEach(function (c) { c.style.display = !q || (c.getAttribute('data-name') || '').indexOf(q) !== -1 ? '' : 'none'; });
    });
    document.addEventListener('fr:relation', function (e) {
        if (String(e.detail.uid) !== String(OWNER)) return;
        var was = <?= js($rel === 'friends') ?>;
        if ((e.detail.rel === 'friends') !== was) setTimeout(function () { hardReload(); }, 900);
    });

    // ===== Saved সেকশন: ফিল্টার + আনসেভ করলে সরে যায় =====
    var svF = $('#svFilters');
    if (svF) svF.addEventListener('click', function (e) {
        var b = e.target.closest('.sv-filter'); if (!b) return;
        $$('.sv-filter', svF).forEach(function (x) { x.classList.toggle('active', x === b); });
        var f = b.getAttribute('data-f');
        $$('#svList .sv-item').forEach(function (it) { it.style.display = f === 'all' || it.getAttribute('data-type') === f ? '' : 'none'; });
    });
    document.addEventListener('saved:changed', function (e) {
        if (e.detail.saved) return;
        var it = $('#svList .sv-item[data-key="' + e.detail.type + ':' + e.detail.id + '"]');
        if (it) { it.classList.add('gone'); setTimeout(function () { it.remove(); }, 300); }
    });

    // ===== মেনু / লাইটবক্স =====
    function lightbox(src) { var lb = $('#pcLightbox'); if (!lb || !src) return; $('img', lb).src = src; lb.classList.add('open'); }
    function closeMenus(except) { $$('.av-menu.open, .cv-menu.open, .gl-menu.open, .tp-menu.open').forEach(function (m) { if (m !== except) m.classList.remove('open'); }); }
    document.addEventListener('click', function (e) { if (!e.target.closest('.av-menu, .cv-menu, .gl-menu, .tp-menu, #psAvatar, #avEditBtn, [data-cv="menu"], [data-gl="menu"], [data-tp="menu"]')) closeMenus(); });

    // ===== প্রোফাইল ছবি =====
    var av = $('#psAvatar'), avMenu = $('#avMenu'), avFile = $('#avFile');
    function avImg() { return $('img', av); }
    av.addEventListener('click', function () {
        if (IS_OWNER) { closeMenus(avMenu); avMenu.classList.toggle('open'); }
        else if (avImg()) lightbox(avImg().src);
    });
    av.addEventListener('keydown', function (e) { if (e.key === 'Enter') av.click(); });
    if (IS_OWNER) {
        $('#avEditBtn').addEventListener('click', function () { closeMenus(); avFile.click(); });
        avMenu.addEventListener('click', function (e) {
            var b = e.target.closest('[data-av]'); if (!b) return;
            avMenu.classList.remove('open');
            if (b.getAttribute('data-av') === 'view') lightbox(avImg() && avImg().src); else avFile.click();
        });
        avFile.addEventListener('change', async function () {
            var f = avFile.files[0]; avFile.value = '';
            if (!f) return;
            if (!/^image\/(jpeg|png|gif|webp)$/.test(f.type)) { say('শুধু JPG, PNG, GIF বা WEBP ছবি দেওয়া যাবে।', true); return; }
            if (f.size > 5 * 1024 * 1024) { say('ছবি ৫ MB এর কম হতে হবে।', true); return; }
            var fd = new FormData(); fd.append('profileImage', f); fd.append('__RequestVerificationToken', tokenVal());
            av.classList.add('uploading');
            try {
                var d = await (await fetch('/Profile/UploadProfileImage', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                if (!d.success) { say(d.message || 'Upload failed', true); return; }
                var url = d.imageUrl + '?t=' + Date.now();
                [av].concat($$('.me-avatar'), $$('.pc[data-owner="1"] .pc-head .pc-avatar')).forEach(function (m) {
                    var i = $('img', m); if (!i) { i = document.createElement('img'); i.alt = ''; m.appendChild(i); } i.src = url;
                });
                $('[data-av="view"]', avMenu).hidden = false;
                say('প্রোফাইল ছবি আপডেট হয়েছে।');
            } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
            finally { av.classList.remove('uploading'); }
        });
    }

    // ===== কভার ফটো =====
    var cover = $('#psCover'), cImg = $('#psCoverImg');
    cImg.addEventListener('click', function () { if (!cover.classList.contains('repo') && !cImg.hidden) lightbox(cImg.src); });
    if (IS_OWNER) {
        var cvMenu = $('#cvMenu'), cvFile = $('#cvFile');
        var saved = { src: cImg.hidden ? '' : cImg.getAttribute('src'), y: <?= (int)$coverY ?> };
        var pending = null, posY = saved.y, drag = null;
        function setPos(y) { posY = Math.max(0, Math.min(100, y)); cImg.style.objectPosition = '50% ' + posY + '%'; }
        function enterRepo() { cover.classList.add('repo'); closeMenus(); }
        function exitRepo() { cover.classList.remove('repo', 'dragging'); pending = null; }
        function paintMenu() {
            var has = !!saved.src;
            cover.classList.toggle('has-img', has || !cImg.hidden);
            $('[data-cv="reposition"]', cvMenu).hidden = !has;
            $('[data-cv="remove"]', cvMenu).hidden = !has;
            $('[data-cv="menu"] span', cover).textContent = has ? 'Edit Cover' : 'Add Cover';
        }
        cover.addEventListener('click', async function (e) {
            var b = e.target.closest('[data-cv]'); if (!b) return;
            var act = b.getAttribute('data-cv');
            if (act === 'menu') { closeMenus(cvMenu); cvMenu.classList.toggle('open'); return; }
            cvMenu.classList.remove('open');
            if (act === 'upload') cvFile.click();
            else if (act === 'reposition') { setPos(saved.y); enterRepo(); }
            else if (act === 'cancel') {
                exitRepo();
                if (saved.src) { cImg.src = saved.src; cImg.hidden = false; } else { cImg.hidden = true; cImg.removeAttribute('src'); }
                setPos(saved.y); paintMenu();
            } else if (act === 'save') {
                b.disabled = true; var old = b.innerHTML; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                var fd = new FormData(); fd.append('posY', Math.round(posY)); fd.append('__RequestVerificationToken', tokenVal());
                if (pending) fd.append('cover', pending);
                try {
                    var d = await (await fetch('/Profile/SaveCover', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                    if (!d.success) { say(d.message || 'Failed', true); return; }
                    saved = { src: d.url, y: d.posY }; cImg.src = d.url; exitRepo(); paintMenu(); say(d.message);
                } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
                finally { b.disabled = false; b.innerHTML = old; }
            } else if (act === 'remove') {
                if (!confirm('কভার ফটো সরিয়ে ফেলবেন?')) return;
                var d2 = await postForm('/Profile/RemoveCover', {});
                if (!d2.success) { say(d2.message, true); return; }
                saved = { src: '', y: 50 }; cImg.hidden = true; cImg.removeAttribute('src'); setPos(50); paintMenu(); say(d2.message);
            }
        });
        cvFile.addEventListener('change', function () {
            var f = cvFile.files[0]; cvFile.value = '';
            if (!f) return;
            if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { say('শুধু JPG, PNG বা WEBP ছবি দেওয়া যাবে।', true); return; }
            if (f.size > 8 * 1024 * 1024) { say('কভার ছবি ৮ MB এর কম হতে হবে।', true); return; }
            pending = f; cImg.src = URL.createObjectURL(f); cImg.hidden = false; cover.classList.add('has-img');
            setPos(50); enterRepo();
        });
        cImg.addEventListener('pointerdown', function (e) {
            if (!cover.classList.contains('repo')) return;
            e.preventDefault();
            var rect = cover.getBoundingClientRect();
            var shown = rect.width * (cImg.naturalHeight / Math.max(1, cImg.naturalWidth));
            drag = { y: e.clientY, start: posY, overflow: Math.max(1, shown - rect.height) };
            cover.classList.add('dragging'); cImg.setPointerCapture(e.pointerId);
        });
        cImg.addEventListener('pointermove', function (e) { if (drag) setPos(drag.start - (e.clientY - drag.y) / drag.overflow * 100); });
        function endDrag() { drag = null; cover.classList.remove('dragging'); }
        cImg.addEventListener('pointerup', endDrag); cImg.addEventListener('pointercancel', endDrag);
    }


    // ===== মডাল (Intro / Goal) =====
    function openOv(ov) { ov.classList.add('open'); document.documentElement.style.overflow = 'hidden'; }
    function closeOv(ov) { ov.classList.remove('open'); document.documentElement.style.overflow = ''; }
    $$('.pf-ov').forEach(function (ov) {
        ov.addEventListener('click', function (e) { if (e.target === ov || e.target.closest('[data-close]')) closeOv(ov); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') $$('.pf-ov.open').forEach(closeOv); });

    // ===== পাসওয়ার্ড =====
    var pw = $('#pwForm');
    if (pw) {
        $$('.pw-eye', pw).forEach(function (b) {
            b.addEventListener('click', function () {
                var i = b.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password';
                b.innerHTML = i.type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
            });
        });
        pw.addEventListener('submit', async function (e) {
            e.preventDefault();
            var msg = $('#pwMsg'), btn = $('#pwBtn');
            var cur = $('#pwCur').value, nw = $('#pwNew').value, cf = $('#pwConf').value;
            msg.className = 'pw-msg';
            if (nw.length < 6) { msg.className = 'pw-msg err'; msg.textContent = 'নতুন পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।'; return; }
            if (nw !== cf) { msg.className = 'pw-msg err'; msg.textContent = 'নতুন পাসওয়ার্ড দুইবার একই হয়নি।'; return; }
            btn.disabled = true;
            try {
                var d = await postForm('/Profile/ChangePassword', { currentPassword: cur, newPassword: nw, confirmPassword: cf });
                msg.className = 'pw-msg ' + (d.success ? 'ok' : 'err'); msg.textContent = d.message;
                if (d.success) pw.reset();
            } catch (err) { msg.className = 'pw-msg err'; msg.textContent = 'ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'; }
            btn.disabled = false;
        });
    }

    // ===== পোস্ট ফিডের ট্যাব (Recent / Materials / Achievements ...) =====
    var feedTabs = $('#feedTabs');
    if (feedTabs) feedTabs.addEventListener('click', async function (e) {
        var b = e.target.closest('button[data-feed]'); if (!b || b.classList.contains('active')) return;
        $$('button', feedTabs).forEach(function (x) { x.classList.toggle('active', x === b); });
        var key = b.getAttribute('data-feed');
        var other = document.querySelectorAll('[data-feed-pane]');
        Array.prototype.forEach.call(other, function (p) { p.hidden = p.getAttribute('data-feed-pane') !== key; });
        var isPosts = !b.hasAttribute('data-pane');
        feed.hidden = !isPosts; if (!isPosts) { empty.hidden = true; if (more) more.hidden = true; return; }
        feed.setAttribute('data-tab', key);
        feed.style.opacity = '.5';
        try {
            var res = await fetch('/Post/Feed?userId=' + OWNER + '&tab=' + encodeURIComponent(key));
            var t = document.createElement('template'); t.innerHTML = await res.text();
            window.pcInitCards(t.content); feed.innerHTML = ''; feed.appendChild(t.content);
            empty.hidden = !!feed.querySelector('.pc');
            if (more) { more.hidden = res.headers.get('X-Has-More') !== '1'; more.disabled = false; more.innerHTML = '<i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন'; }
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { feed.style.opacity = ''; }
    });

    // ===== My Profile: বাম মেনু থেকে সেকশনে যাওয়া + সেখানেই এডিট =====
    function markSub(k) { $$('.pf-sub a').forEach(function (a) { a.classList.toggle('on', a.getAttribute('data-ab-go') === k); }); }
    function closeAb(c) {
        c.classList.remove('editing');
        var f = $('.ab-form', c); if (f) { f.reset(); var m = $('.ab-msg', f); if (m) m.remove(); }
    }
    function openAb(c) {
        $$('.ab-card.editing').forEach(function (x) { if (x !== c) closeAb(x); });
        c.classList.add('editing');
        var first = $('.ab-form input, .ab-form select', c);
        if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 80);
    }
    function goAb(k, open) {
        if (k === 'password') {
            showTab('settings'); markSub(k);
            var pwc = $('#ab-password');
            if (pwc) setTimeout(function () { pwc.scrollIntoView({ behavior: 'smooth', block: 'start' }); var i = $('#pwCur'); if (i) i.focus({ preventScroll: true }); }, 30);
            return;
        }
        showTab('profile'); markSub(k);
        var c = document.getElementById('ab-' + k); if (!c) return;
        if (open && $('.ab-form', c)) openAb(c);
        setTimeout(function () {
            c.scrollIntoView({ behavior: 'smooth', block: 'start' });
            c.classList.remove('flash'); void c.offsetWidth; c.classList.add('flash');
        }, 30);
    }
    document.addEventListener('click', function (e) {
        var g = e.target.closest('[data-ab-go]');
        if (g) { e.preventDefault(); goAb(g.getAttribute('data-ab-go'), g.hasAttribute('data-ab-open')); return; }
        var ed = e.target.closest('[data-ab-edit]');
        if (ed) { openAb(ed.closest('.ab-card')); markSub(ed.getAttribute('data-ab-edit')); return; }
        var cn = e.target.closest('[data-ab-cancel]');
        if (cn) closeAb(cn.closest('.ab-card'));
    });
    document.addEventListener('submit', async function (e) {
        var f = e.target.closest('form[data-ab-form]'); if (!f) return;
        e.preventDefault();
        var btn = $('button[type=submit]', f), old = btn.innerHTML, m = $('.ab-msg', f);
        if (m) m.remove();
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        var fd = new FormData(f); fd.append('__RequestVerificationToken', tokenVal());
        try {
            var d = await (await fetch(f.getAttribute('data-ab-form'), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!d.success) {
                m = document.createElement('div'); m.className = 'ab-msg'; m.innerHTML = '<i class="fas fa-circle-exclamation"></i> ';
                m.appendChild(document.createTextNode(d.message || 'Failed')); $('.ab-factions', f).before(m);
                return;
            }
            say(d.message);
            var u = new URL(location.href); u.searchParams.set('tab', 'profile'); u.searchParams.delete('edit'); u.searchParams.set('_r', Date.now().toString(36));
            setTimeout(function () { location.replace(u.pathname + u.search + '#ab-' + f.getAttribute('data-sec')); }, 350);
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; btn.innerHTML = old; }
    });
    if (initEdit) goAb(initEdit, true);
    else if (/^#ab-[a-z]+$/.test(initHash) && initHash !== '#ab-password') goAb(initHash.slice(4), false);

    window.PF = { $: $, $$: $$, postForm: postForm, say: say, hardReload: hardReload, openOv: openOv, closeOv: closeOv,
        closeMenus: closeMenus, showTab: showTab, lightbox: lightbox, OWNER: OWNER, IS_OWNER: IS_OWNER };
})();
</script>
