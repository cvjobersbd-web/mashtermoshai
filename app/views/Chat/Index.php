<?php
/**
 * চ্যাট (মেসেঞ্জারের মতো) — বাঁয়ে চ্যাটের তালিকা, ডানে কথোপকথন।
 * মেসেজগুলো JS দিয়ে textContent হিসেবে বসে, তাই কারো লেখা HTML/স্ক্রিপ্ট হিসেবে চলে না।
 */
$threads = is_array($Model) ? $Model : [];
$cur = $ViewBag->Current;
$closed = !empty($ViewBag->Closed);
$ViewData['Title'] = 'Messages';
$ViewData['FullTitle'] = ($cur ? $cur->OtherName . ' — ' : '') . 'Messages | মাষ্টার মশাই';
$ViewData['Robots'] = 'noindex, nofollow';
$role = current_role();
?>
<style>
    .ch { background: #EEF3F8; padding: 18px 0 26px; }
    .ch-wrap { max-width: 1240px; margin: 0 auto; padding: 0 16px; }
    .ch-box { display: grid; grid-template-columns: 340px minmax(0, 1fr); height: calc(100vh - 80px - 44px); min-height: 520px; background: #fff; border: 1px solid #E1E8F0; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 26px rgba(15,27,45,.06); }
    /* ===== তালিকা ===== */
    .ch-list { border-right: 1px solid #EAEFF5; display: flex; flex-direction: column; min-height: 0; }
    .ch-lh { padding: 18px 18px 12px; }
    .ch-lh h1 { margin: 0; font-size: 22px; font-weight: 800; color: #0F172A; font-family: var(--font-heading); }
    .ch-lh p { margin: 4px 0 0; font-size: 12.5px; color: #64748B; }
    .ch-threads { flex: 1; overflow-y: auto; padding: 4px 8px 10px; }
    .ch-t { display: flex; gap: 11px; align-items: center; padding: 10px; border-radius: 12px; text-decoration: none; color: inherit; position: relative; }
    .ch-t:hover { background: #F3F7FB; }
    .ch-t.on { background: #E6F9FF; }
    .ch-av { width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #08224A, #0A5A86); color: #fff; font-weight: 700; font-size: 16px; }
    .ch-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ch-av.sm { width: 40px; height: 40px; font-size: 14px; }
    .ch-tm { min-width: 0; flex: 1; }
    .ch-tn { display: flex; gap: 6px; align-items: baseline; }
    .ch-tn b { font-size: 14.5px; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ch-tn small { margin-left: auto; font-size: 11.5px; color: #94A3B8; white-space: nowrap; }
    .ch-tl { font-size: 13px; color: #64748B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    .ch-t.unread .ch-tl { color: #0F172A; font-weight: 700; }
    .ch-t .dot { width: 20px; height: 20px; border-radius: 10px; background: #00CFFF; color: #fff; font-size: 11px; font-weight: 800; display: none; align-items: center; justify-content: center; flex-shrink: 0; padding: 0 6px; }
    .ch-t.unread .dot { display: inline-flex; }
    .ch-tr { font-size: 10.5px; font-weight: 700; color: #0369A1; background: #E0F2FE; border-radius: 6px; padding: 0 6px; }
    .ch-none { padding: 30px 22px; text-align: center; color: #64748B; font-size: 14px; line-height: 1.6; }
    .ch-none i { font-size: 40px; color: #B9E9F7; display: block; margin-bottom: 12px; }
    .ch-none a { color: #0284C7; font-weight: 700; }
    /* ===== কথোপকথন ===== */
    .ch-main { display: flex; flex-direction: column; min-height: 0; min-width: 0; }
    .ch-mh { display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-bottom: 1px solid #EAEFF5; }
    .ch-back { display: none; width: 38px; height: 38px; border-radius: 50%; align-items: center; justify-content: center; color: #0F172A; text-decoration: none; flex-shrink: 0; }
    .ch-back:hover { background: #F1F5F9; }
    .ch-mh .who { min-width: 0; flex: 1; }
    .ch-mh .who a { font-weight: 800; color: #0F172A; text-decoration: none; font-size: 16px; }
    .ch-mh .who a:hover { text-decoration: underline; }
    .ch-mh .who div { font-size: 12.5px; color: #64748B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ch-mh .prof { margin-left: auto; height: 36px; padding: 0 14px; border-radius: 10px; border: 1px solid #DCE5EF; color: #0F172A; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
    .ch-mh .prof:hover { background: #F1F5F9; }
    .ch-msgs { flex: 1; overflow-y: auto; padding: 18px 18px 8px; background: #FAFCFE; display: flex; flex-direction: column; gap: 3px; }
    .ch-older { align-self: center; margin-bottom: 10px; border: 1px solid #DCE5EF; background: #fff; border-radius: 999px; padding: 6px 14px; font-size: 12.5px; font-weight: 700; color: #334155; cursor: pointer; font-family: inherit; }
    .ch-day { align-self: center; font-size: 11.5px; font-weight: 700; color: #64748B; background: #EEF2F7; border-radius: 999px; padding: 3px 12px; margin: 12px 0 8px; }
    .ch-intro { align-self: center; text-align: center; max-width: 420px; font-size: 13px; color: #64748B; background: #fff; border: 1px dashed #CFE3F0; border-radius: 14px; padding: 14px 16px; margin: 6px 0 12px; line-height: 1.6; }
    .ch-intro b { color: #0F172A; }
    .ch-m { max-width: min(72%, 560px); display: flex; flex-direction: column; }
    .ch-m .bb { padding: 9px 13px; border-radius: 18px; font-size: 14.5px; line-height: 1.45; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere; }
    .ch-m .bb a { color: inherit; text-decoration: underline; font-weight: 600; }
    .ch-m .tm { font-size: 10.5px; color: #94A3B8; margin: 2px 8px 6px; }
    .ch-m.them { align-self: flex-start; }
    .ch-m.them .bb { background: #fff; color: #0F172A; border: 1px solid #E5ECF3; border-bottom-left-radius: 6px; }
    .ch-m.me { align-self: flex-end; align-items: flex-end; }
    .ch-m.me .bb { background: #00CFFF; color: #fff; border-bottom-right-radius: 6px; }
    .ch-m.me .bb a { color: #fff; }
    .ch-m.pending .bb { opacity: .6; }
    .ch-m.failed .bb { background: #FEE2E2; color: #991B1B; }
    .ch-seen { align-self: flex-end; font-size: 11px; color: #64748B; margin: -2px 8px 4px; }
    .ch-form { display: flex; gap: 10px; align-items: flex-end; padding: 12px 14px; border-top: 1px solid #EAEFF5; background: #fff; }
    .ch-form textarea { flex: 1; resize: none; border: 1px solid #DCE5EF; border-radius: 20px; padding: 10px 16px; font-size: 14.5px; font-family: inherit; line-height: 1.4; max-height: 140px; outline: none; background: #F6F9FC; color: #0F172A; }
    .ch-form textarea:focus { border-color: #00CFFF; background: #fff; }
    .ch-form button { width: 44px; height: 44px; border-radius: 50%; border: none; background: #00CFFF; color: #fff; font-size: 17px; cursor: pointer; flex-shrink: 0; transition: background .2s; }
    .ch-form button:hover { background: #00B5E0; }
    .ch-form button:disabled { opacity: .5; cursor: default; }
    .ch-closed { margin: auto; text-align: center; max-width: 420px; padding: 30px 20px; color: #64748B; font-size: 14.5px; line-height: 1.65; }
    .ch-closed i { font-size: 44px; color: #CBD5E1; display: block; margin-bottom: 12px; }
    .ch-closed b { color: #0F172A; }
    .ch-closedbar { background: #FEF3C7; color: #92400E; font-size: 13.5px; font-weight: 600; padding: 12px 16px; text-align: center; border-top: 1px solid #FDE68A; }
    @media (max-width: 860px) {
        .ch { padding: 0; }
        .ch-wrap { padding: 0; }
        .ch-box { grid-template-columns: minmax(0, 1fr); border-radius: 0; border: none; height: calc(100vh - 80px); height: calc(100dvh - 80px); min-height: 0; }
        .ch-box.has-cur .ch-list { display: none; }
        .ch-box:not(.has-cur) .ch-main { display: none; }
        .ch-back { display: inline-flex; }
        .ch-m { max-width: 84%; }
        .ch-mh { padding: 10px 10px; gap: 8px; }
        .ch-mh .prof span { display: none; }
        .ch-mh .prof { padding: 0 11px; }
        .ch-msgs { padding: 14px 10px 6px; }
        .ch-form { padding: 10px; }
    }
</style>

<div class="ch">
    <div class="ch-wrap">
        <div class="ch-box<?= ($cur || $closed) ? ' has-cur' : '' ?>" id="chBox">
            <aside class="ch-list">
                <div class="ch-lh">
                    <h1>Messages</h1>
                    <p><?= $role === 'Teacher' ? 'স্টুডেন্ট আপনার অ্যাপ্লিকেশন Accept করলে এখানে চ্যাট খোলে।' : 'আপনি টিচারের অ্যাপ্লিকেশন Accept করলে এখানে চ্যাট খোলে।' ?></p>
                </div>
                <div class="ch-threads" id="chThreads">
                    <?php if (!$threads): ?>
                        <div class="ch-none">
                            <i class="fas fa-comments"></i>
                            এখনো কোনো চ্যাট নেই।<br>
                            <?php if ($role === 'Teacher'): ?>
                                <a href="/Tuition/Circular">Tuition Circular</a> থেকে স্টুডেন্টের পোস্টে Apply করুন — স্টুডেন্ট Accept করলে এখানে কথা বলতে পারবেন।
                            <?php else: ?>
                                <a href="/Tuition/Circular?post=1">টিউশন পোস্ট দিন</a> — টিচাররা Apply করবে। <a href="/User/Dashboard?section=applications">Applications</a> থেকে পছন্দের টিচারকে Accept করলে এখানে কথা বলতে পারবেন।
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($threads as $t): ?>
                            <a class="ch-t<?= $cur && $cur->AppId === $t->AppId ? ' on' : '' ?><?= $t->Unread ? ' unread' : '' ?>" href="/Chat?c=<?= (int)$t->AppId ?>" data-id="<?= (int)$t->AppId ?>">
                                <span class="ch-av"><?= e($t->Initials) ?><?php if ($t->OtherPhoto !== ''): ?><img src="<?= e($t->OtherPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                                <span class="ch-tm">
                                    <span class="ch-tn"><b><?= e($t->OtherName) ?></b> <span class="ch-tr"><?= e($t->OtherRole) ?></span><small><?= e(time_ago($t->LastAt)) ?></small></span>
                                    <span class="ch-tl"><?= $t->LastBody !== '' ? ($t->LastMine ? 'আপনি: ' : '') . e(str_preview($t->LastBody, 70)) : e($t->Topic !== '' ? $t->Topic : 'চ্যাট শুরু করুন') ?></span>
                                </span>
                                <span class="dot"><?= (int)$t->Unread ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <section class="ch-main">
                <?php if ($cur): ?>
                    <header class="ch-mh">
                        <a class="ch-back" href="/Chat" aria-label="সব চ্যাট"><i class="fas fa-arrow-left"></i></a>
                        <span class="ch-av sm"><?= e($cur->Initials) ?><?php if ($cur->OtherPhoto !== ''): ?><img src="<?= e($cur->OtherPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                        <div class="who">
                            <a href="/Profile/Show/<?= (int)$cur->OtherId ?>"><?= e($cur->OtherName) ?></a>
                            <div><?= e($cur->OtherRole) ?><?= $cur->Topic !== '' ? ' · ' . e($cur->Topic) : '' ?></div>
                        </div>
                        <a class="prof" href="/Profile/Show/<?= (int)$cur->OtherId ?>"><i class="fas fa-user"></i><span>প্রোফাইল</span></a>
                    </header>
                    <div class="ch-msgs" id="chMsgs" aria-live="polite">
                        <div class="ch-intro">
                            <b><?= e($cur->OtherName) ?></b> এর সাথে আপনার টিউশন ঠিক হয়েছে<?= $cur->Topic !== '' ? ' (' . e($cur->Topic) . ')' : '' ?>।<br>
                            এখানে ক্লাসের সময়, বিষয় বা Zoom/Google Meet লিংক নিয়ে কথা বলুন। কেউ খারাপ আচরণ করলে <a href="/Helpdesk">Helpdesk</a> এ জানান।
                        </div>
                    </div>
                    <div class="ch-closedbar" id="chClosedBar" hidden><i class="fas fa-lock"></i> এই চ্যাটটি বন্ধ হয়ে গেছে — অ্যাপ্লিকেশনটি আর Accepted নেই।</div>
                    <form class="ch-form" id="chForm" autocomplete="off">
                        <textarea id="chText" rows="1" maxlength="2000" placeholder="মেসেজ লিখুন..." aria-label="মেসেজ লিখুন"></textarea>
                        <button type="submit" id="chSend" aria-label="পাঠান"><i class="fas fa-paper-plane"></i></button>
                    </form>
                <?php elseif ($closed): ?>
                    <header class="ch-mh"><a class="ch-back" href="/Chat" aria-label="সব চ্যাট"><i class="fas fa-arrow-left"></i></a><div class="who"><b>Messages</b></div></header>
                    <div class="ch-closed">
                        <i class="fas fa-lock"></i>
                        <b>এই চ্যাটটি এখন বন্ধ।</b><br>
                        অ্যাপ্লিকেশনটি Reject করা হয়েছে বা আর Accepted নেই — তাই এখানে আর কথা বলা যাবে না।
                    </div>
                <?php else: ?>
                    <div class="ch-closed">
                        <i class="fas fa-comment-dots" style="color:#B9E9F7;"></i>
                        <?= $threads ? '<b>বাঁ পাশ থেকে একটা চ্যাট বেছে নিন।</b>' : '<b>এখনো কোনো চ্যাট নেই।</b><br>টিউশন ঠিক হলে (অ্যাপ্লিকেশন Accept হলে) এখানে টিচার আর স্টুডেন্ট কথা বলতে পারবেন।' ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var token = function () { var i = document.querySelector('input[name="__RequestVerificationToken"]'); return i ? i.value : ''; };
    var threadsBox = document.getElementById('chThreads');
    var CUR = <?= js($cur ? $cur->AppId : 0) ?>;

    // ===== তালিকা রিফ্রেশ (নতুন মেসেজ / নতুন চ্যাট) =====
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
    function drawThreads(list) {
        if (!list.length) return;
        threadsBox.innerHTML = list.map(function (t) {
            var cls = 'ch-t' + (t.id === CUR ? ' on' : '') + (t.unread && t.id !== CUR ? ' unread' : '');
            var av = '<span class="ch-av">' + esc(t.initials) + (t.photo ? '<img src="' + esc(t.photo) + '" alt="" onerror="this.remove()" />' : '') + '</span>';
            var last = t.last ? (t.lastMine ? 'আপনি: ' : '') + esc(t.last) : esc(t.topic || 'চ্যাট শুরু করুন');
            return '<a class="' + cls + '" href="/Chat?c=' + t.id + '" data-id="' + t.id + '">' + av + '<span class="ch-tm"><span class="ch-tn"><b>' + esc(t.name) + '</b> <span class="ch-tr">' + esc(t.role)
                + '</span><small>' + esc(t.ago) + '</small></span><span class="ch-tl">' + last + '</span></span><span class="dot">' + (t.unread || 0) + '</span></a>';
        }).join('');
    }
    async function refreshThreads() {
        if (document.hidden) return;
        try { var r = await (await fetch('/Chat/Threads', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); if (r.success) drawThreads(r.threads); } catch (e) { }
    }
    setInterval(refreshThreads, 15000);

    if (!CUR) return;

    // ===== কথোপকথন =====
    var box = document.getElementById('chMsgs'), form = document.getElementById('chForm'), ta = document.getElementById('chText'), send = document.getElementById('chSend');
    var closedBar = document.getElementById('chClosedBar');
    var lastId = 0, firstId = 0, lastDay = '', seenUpTo = <?= (int)$ViewBag->SeenUpTo ?>, seen = {}, closed = false, busy = false;
    var URL_RE = /(https?:\/\/[^\s<>"']+)/g;

    function dayLabel(d) {
        var t = new Date(), y = new Date(Date.now() - 864e5);
        var f = function (x) { return x.getFullYear() + '-' + String(x.getMonth() + 1).padStart(2, '0') + '-' + String(x.getDate()).padStart(2, '0'); };
        if (d === f(t)) return 'আজ'; if (d === f(y)) return 'গতকাল';
        var p = d.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }
    // লেখা নিরাপদে বসানো — লিংক থাকলে (যেমন Zoom/Meet) ক্লিক করা যায়
    function fill(el, text) {
        var parts = String(text).split(URL_RE);
        parts.forEach(function (p, i) {
            if (i % 2 === 1) { var a = document.createElement('a'); a.href = p; a.textContent = p; a.target = '_blank'; a.rel = 'noopener nofollow ugc'; el.appendChild(a); }
            else if (p) el.appendChild(document.createTextNode(p));
        });
    }
    function bubble(m) {
        var w = document.createElement('div'); w.className = 'ch-m ' + (m.mine ? 'me' : 'them'); w.setAttribute('data-id', m.id || '');
        var b = document.createElement('div'); b.className = 'bb'; fill(b, m.body); w.appendChild(b);
        var tm = document.createElement('div'); tm.className = 'tm'; tm.textContent = m.time || ''; w.appendChild(tm);
        return w;
    }
    function nearBottom() { return box.scrollHeight - box.scrollTop - box.clientHeight < 120; }
    function toBottom() { box.scrollTop = box.scrollHeight; }
    function append(list) {
        var stick = nearBottom();
        list.forEach(function (m) {
            if (seen[m.id]) return; seen[m.id] = 1;
            if (m.day !== lastDay) { var d = document.createElement('div'); d.className = 'ch-day'; d.textContent = dayLabel(m.day); box.appendChild(d); lastDay = m.day; }
            box.appendChild(bubble(m));
            lastId = Math.max(lastId, m.id); if (!firstId || m.id < firstId) firstId = m.id;
        });
        drawSeen();
        if (stick || list.some(function (m) { return m.mine; })) toBottom();
    }
    function drawSeen() {
        var old = box.querySelector('.ch-seen'); if (old) old.remove();
        var mine = box.querySelectorAll('.ch-m.me[data-id]'); if (!mine.length) return;
        var last = mine[mine.length - 1]; if (+last.getAttribute('data-id') > seenUpTo || box.lastElementChild !== last) return;
        var s = document.createElement('div'); s.className = 'ch-seen'; s.textContent = 'Seen'; last.after(s);
    }
    function setClosed() {
        if (closed) return; closed = true;
        closedBar.hidden = false; form.remove();
    }

    // প্রথম মেসেজগুলো
    append(<?= js($ViewBag->Messages) ?>); toBottom();
    <?php if (!empty($ViewBag->HasOlder)): ?>
    (function () {
        var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'ch-older'; btn.textContent = 'আগের মেসেজ দেখুন';
        var intro = box.querySelector('.ch-intro'); box.insertBefore(btn, intro ? intro.nextSibling : box.firstChild);
        btn.addEventListener('click', async function () {
            btn.disabled = true; btn.textContent = 'লোড হচ্ছে...';
            try {
                var r = await (await fetch('/Chat/Messages?c=' + CUR + '&before=' + firstId)).json();
                if (!r.success) { if (r.closed) setClosed(); return; }
                var h = box.scrollHeight, frag = document.createDocumentFragment(), d0 = '';
                r.messages.forEach(function (m) {
                    if (seen[m.id]) return; seen[m.id] = 1;
                    if (m.day !== d0) { var d = document.createElement('div'); d.className = 'ch-day'; d.textContent = dayLabel(m.day); frag.appendChild(d); d0 = m.day; }
                    frag.appendChild(bubble(m)); if (m.id < firstId) firstId = m.id;
                });
                // একই দিনের লেবেল দুবার না দেখাতে
                var nextDay = btn.nextElementSibling; if (nextDay && nextDay.classList.contains('ch-day') && nextDay.textContent === dayLabel(d0)) nextDay.remove();
                btn.after(frag); box.scrollTop = box.scrollHeight - h;
                if (r.hasOlder) { btn.disabled = false; btn.textContent = 'আগের মেসেজ দেখুন'; } else btn.remove();
            } catch (e) { btn.disabled = false; btn.textContent = 'আবার চেষ্টা করুন'; }
        });
    })();
    <?php endif; ?>

    // নতুন মেসেজ — কয়েক সেকেন্ড পরপর
    async function poll() {
        if (closed || busy || document.hidden) return;
        busy = true;
        try {
            var r = await (await fetch('/Chat/Messages?c=' + CUR + '&after=' + lastId + '&read=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!r.success) { if (r.closed) setClosed(); return; }
            seenUpTo = r.seenUpTo || seenUpTo; append(r.messages);
        } catch (e) { } finally { busy = false; }
    }
    setInterval(poll, 3000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { poll(); refreshThreads(); } });

    // পাঠানো
    ta.addEventListener('input', function () { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 140) + 'px'; });
    ta.addEventListener('keydown', function (e) {
        // ডেস্কটপে Enter = পাঠান, Shift+Enter = নতুন লাইন (মোবাইলে Enter নতুন লাইন)
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing && window.matchMedia('(hover: hover)').matches) { e.preventDefault(); form.requestSubmit(); }
    });
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var text = ta.value.trim(); if (!text || closed) return;
        var temp = bubble({ mine: true, body: text, time: '...' }); temp.classList.add('pending'); box.appendChild(temp); toBottom();
        ta.value = ''; ta.style.height = ''; ta.focus();
        var fd = new FormData(); fd.append('c', CUR); fd.append('body', text); fd.append('after', lastId); fd.append('__RequestVerificationToken', token());
        try {
            var r = await (await fetch('/Chat/Send', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!r.success) {
                temp.classList.remove('pending'); temp.classList.add('failed'); temp.querySelector('.tm').textContent = r.message || 'পাঠানো যায়নি';
                if (r.closed) setClosed(); else if (!ta.value) ta.value = text;
                return;
            }
            temp.remove(); append(r.messages);
        } catch (er) { temp.classList.remove('pending'); temp.classList.add('failed'); temp.querySelector('.tm').textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন'; if (!ta.value) ta.value = text; }
    });
    if (window.matchMedia('(hover: hover)').matches) ta.focus();
})();
</script>
<?php section_end(); ?>
