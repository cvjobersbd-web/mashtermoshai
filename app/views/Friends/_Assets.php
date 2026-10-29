<?php
/** ফ্রেন্ড পেজগুলোর কমন স্টাইল + বাটনের JS (Friends/Index ও Friends/Person এ ব্যবহার হয়) */
?>
<style>
    :root {
        --fr-navy: #08224A; --fr-teal: #134856; --fr-sky: #02B8F5; --fr-sky-dark: #0099D6;
        --fr-ice: #F5FBFF; --fr-line: #DCECF5; --fr-muted: #64748B; --fr-gold: #FFB300;
    }

    /* ===== Buttons ===== */
    .fr-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        height: 38px; padding: 0 14px; border-radius: 10px; border: 1px solid transparent;
        font-size: 13.5px; font-weight: 600; line-height: 1; cursor: pointer; text-decoration: none;
        transition: background .2s, color .2s, transform .15s, box-shadow .2s; white-space: nowrap;
    }
    .fr-btn:active { transform: scale(.97); }
    .fr-btn[disabled] { opacity: .6; cursor: default; }
    .fr-primary { background: var(--fr-sky); color: #fff; box-shadow: 0 4px 12px rgba(2,184,245,.25); }
    .fr-primary:hover { background: var(--fr-sky-dark); color: #fff; }
    .fr-ghost { background: #EEF5FA; color: var(--fr-navy); }
    .fr-ghost:hover { background: var(--fr-line); color: var(--fr-navy); }
    .fr-is-friend { background: #E7F8EF; color: #157347; cursor: default; }
    .fr-is-sent { background: #FFF7E0; color: #8a5a00; cursor: default; }
    .fr-icon-only span { display: none; }
    .fr-icon-only { width: 38px; padding: 0; flex: 0 0 38px !important; }
    .fr-btn .fa-spinner { animation: fa-spin 1s linear infinite; }

    /* ===== Person card ===== */
    .fr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 16px; }
    .fr-card {
        background: #fff; border: 1px solid var(--fr-line); border-radius: 18px; padding: 22px 16px 16px;
        display: flex; flex-direction: column; align-items: center; text-align: center; min-width: 0;
        box-shadow: 0 2px 12px rgba(8,34,74,.04); transition: transform .2s, box-shadow .2s, opacity .3s;
        position: relative; overflow: hidden;
    }
    .fr-card::before {
        content: ''; position: absolute; inset: 0 0 auto 0; height: 58px;
        background: linear-gradient(135deg, #E3F6FD 0%, #F5FBFF 100%);
    }
    .fr-card:hover { transform: translateY(-3px); box-shadow: 0 10px 26px rgba(8,34,74,.09); }
    .fr-card.fr-gone { opacity: 0; transform: scale(.96); }
    .fr-card-link { display: flex; flex-direction: column; align-items: center; text-decoration: none; position: relative; min-width: 0; max-width: 100%; }
    .fr-avatar {
        width: 84px; height: 84px; border-radius: 50%; position: relative; overflow: hidden; flex-shrink: 0;
        background: linear-gradient(135deg, var(--fr-navy), var(--fr-teal)); color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 26px;
        border: 4px solid #fff; box-shadow: 0 0 0 2px var(--fr-line);
    }
    .fr-avatar.is-teacher { box-shadow: 0 0 0 2px #7BD3A4; }
    .fr-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; }
    .fr-name {
        margin-top: 10px; font-size: 15.5px; font-weight: 700; color: var(--fr-navy);
        max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .fr-card-link:hover .fr-name { color: #028EA5; }
    .fr-role {
        margin-top: 6px; font-size: 10.5px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase;
        padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;
    }
    .fr-role.teacher { background: #E7F8EF; color: #157347; }
    .fr-role.student { background: #E3F2FD; color: #1565C0; }
    .fr-sub { margin-top: 8px; font-size: 12.5px; color: var(--fr-muted); max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .fr-meta { margin-top: 4px; font-size: 12px; color: #7A8CA0; min-height: 18px; }
    .fr-meta i { color: #028EA5; margin-right: 3px; }
    .fr-actions { margin-top: 14px; display: flex; gap: 8px; width: 100%; }
    .fr-actions > * { flex: 1 1 0; min-width: 0; }

    /* ===== Empty state ===== */
    .fr-empty {
        grid-column: 1 / -1; text-align: center; padding: 54px 20px; background: #fff;
        border: 2px dashed var(--fr-line); border-radius: 18px;
    }
    .fr-empty-icon {
        width: 76px; height: 76px; border-radius: 50%; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center;
        background: var(--fr-ice); color: #7FBCD6; font-size: 30px;
    }
    .fr-empty h5 { color: var(--fr-navy); font-weight: 700; margin: 0 0 6px; }
    .fr-empty p { color: #7A8CA0; font-size: 14px; margin: 0 auto; max-width: 420px; }
    .fr-empty-btn { margin-top: 18px; }

    /* ===== Toast ===== */
    .fr-toast {
        position: fixed; left: 50%; bottom: 26px; z-index: 3000; transform: translate(-50%, 20px); opacity: 0;
        background: var(--fr-navy); color: #fff; padding: 12px 20px; border-radius: 12px; font-size: 14px; font-weight: 500;
        box-shadow: 0 12px 30px rgba(8,34,74,.3); transition: opacity .25s, transform .25s; pointer-events: none;
        max-width: calc(100vw - 32px); text-align: center;
    }
    .fr-toast.show { opacity: 1; transform: translate(-50%, 0); }
    .fr-toast.err { background: #B42318; }

    @media (max-width: 576px) {
        .fr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .fr-card { padding: 16px 10px 12px; border-radius: 14px; }
        .fr-card::before { height: 46px; }
        .fr-avatar { width: 66px; height: 66px; font-size: 21px; border-width: 3px; }
        .fr-name { font-size: 14px; }
        .fr-sub { font-size: 11.5px; }
        .fr-actions { flex-direction: column; gap: 6px; }
        .fr-actions > * { flex: none; width: 100%; }
        .fr-icon-only { width: 100%; flex: none !important; }
        .fr-icon-only span { display: inline; }
        .fr-btn { height: 34px; font-size: 12.5px; padding: 0 10px; }
    }
</style>

<script>
(function () {
    if (window.__frReady) return;
    window.__frReady = true;

    // friend_actions_html() (helpers.php) এর মতো একই বাটন
    function btn(act, cls, icon, label, uid) {
        return '<button type="button" class="fr-btn ' + cls + '" data-act="' + act + '" data-uid="' + uid + '"><i class="fas ' + icon + '"></i><span>' + label + '</span></button>';
    }
    function renderActions(uid, rel) {
        switch (rel) {
            case 'friends':
                return '<span class="fr-btn fr-is-friend"><i class="fas fa-user-check"></i><span>Friends</span></span>'
                    + btn('unfriend', 'fr-ghost fr-icon-only', 'fa-user-xmark', 'Unfriend', uid);
            case 'sent':
                return '<span class="fr-btn fr-is-sent"><i class="fas fa-clock"></i><span>Requested</span></span>'
                    + btn('cancel', 'fr-ghost', 'fa-xmark', 'Cancel', uid);
            case 'received':
                return btn('accept', 'fr-primary', 'fa-check', 'Confirm', uid) + btn('decline', 'fr-ghost', 'fa-xmark', 'Delete', uid);
            case 'self':
                return '<a class="fr-btn fr-ghost" href="/Profile"><i class="fas fa-pen"></i><span>Edit Profile</span></a>';
            default:
                return btn('send', 'fr-primary', 'fa-user-plus', 'Add Friend', uid);
        }
    }
    window.frRenderActions = renderActions;

    var toastEl, toastTimer;
    function toast(msg, isErr) {
        if (!toastEl) { toastEl = document.createElement('div'); toastEl.className = 'fr-toast'; document.body.appendChild(toastEl); }
        toastEl.textContent = msg;
        toastEl.classList.toggle('err', !!isErr);
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2600);
    }
    window.frToast = toast;

    function token() {
        var t = document.querySelector('input[name="__RequestVerificationToken"]');
        return t ? t.value : '';
    }

    function applyRelation(uid, rel) {
        document.querySelectorAll('.fr-actions[data-uid="' + uid + '"]').forEach(function (box) {
            box.setAttribute('data-rel', rel);
            box.innerHTML = renderActions(uid, rel);
        });
        document.dispatchEvent(new CustomEvent('fr:relation', { detail: { uid: uid, rel: rel } }));
    }

    function applyCounts(c) {
        if (!c) return;
        document.querySelectorAll('[data-fr-count]').forEach(function (el) {
            var k = el.getAttribute('data-fr-count');
            if (c[k] === undefined) return;
            el.textContent = c[k];
            if (el.hasAttribute('data-hide-zero')) el.style.display = c[k] > 0 ? '' : 'none';
        });
        var nb = document.getElementById('frNavBadge');
        if (nb) { nb.textContent = c.requests > 9 ? '9+' : c.requests; nb.style.display = c.requests > 0 ? '' : 'none'; }
    }

    var ACTIONS = { send: 'Send', accept: 'Accept', decline: 'Decline', cancel: 'Cancel', unfriend: 'Unfriend' };

    document.addEventListener('click', async function (e) {
        var b = e.target.closest('.fr-btn[data-act]');
        if (!b || b.disabled) return;
        e.preventDefault();
        var act = b.getAttribute('data-act'), uid = b.getAttribute('data-uid');
        if (!ACTIONS[act]) return;
        if (act === 'unfriend' && !confirm('ফ্রেন্ড লিস্ট থেকে সরাতে চান?')) return;

        var box = b.closest('.fr-actions');
        var all = box ? box.querySelectorAll('.fr-btn[data-act]') : [b];
        all.forEach(function (x) { x.disabled = true; });
        var icon = b.querySelector('i'), oldIcon = icon ? icon.className : '';
        if (icon) icon.className = 'fas fa-spinner';

        var fd = new FormData();
        fd.append('userId', uid);
        fd.append('__RequestVerificationToken', token());
        try {
            var res = await fetch('/Friends/' + ACTIONS[act], { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            var data = await res.json();
            if (data.relation) applyRelation(uid, data.relation);
            else { all.forEach(function (x) { x.disabled = false; }); if (icon) icon.className = oldIcon; }
            applyCounts(data.counts);
            toast(data.message || (data.success ? 'Done' : 'Failed'), !data.success);
        } catch (err) {
            all.forEach(function (x) { x.disabled = false; });
            if (icon) icon.className = oldIcon;
            toast('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true);
        }
    });
})();
</script>
