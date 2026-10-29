<?php
$ViewData['Title'] = 'Friends';

if ($Model->IsAdmin): ?>
    <div style="background:#F5FBFF; padding:60px 16px; min-height:60vh;">
        <div style="max-width:520px; margin:0 auto; background:#fff; border-radius:18px; padding:36px 26px; text-align:center; box-shadow:0 6px 24px rgba(8,34,74,.06);">
            <i class="fas fa-user-shield" style="font-size:40px; color:#028EA5;"></i>
            <h4 style="color:#08224A; font-weight:700; margin:14px 0 8px;">অ্যাডমিন অ্যাকাউন্টে ফ্রেন্ড সিস্টেম নেই</h4>
            <p style="color:#64748B; margin:0;">ফ্রেন্ড রিকোয়েস্ট শুধু স্টুডেন্ট আর টিচারদের মধ্যে চলে।</p>
        </div>
    </div>
<?php return; endif;

$tab = $Model->Tab;
$nFriends = count($Model->Friends);
$nIncoming = count($Model->Incoming);
$nSent = count($Model->Sent);
$tabs = [
    'friends'  => ['My Friends', 'fa-user-group', $nFriends, 'friends'],
    'requests' => ['Requests', 'fa-user-plus', $nIncoming, 'requests'],
    'sent'     => ['Sent', 'fa-paper-plane', $nSent, 'sent'],
    'find'     => ['Find People', 'fa-magnifying-glass', null, null],
];
?>
<?= csrf_field() ?>
<?php partial('Friends/_Assets'); ?>

<style>
    .fp-page { background: #F5FBFF; padding: 30px 16px 70px; min-height: 80vh; }
    .fp-wrap { max-width: 1150px; margin: 0 auto; }

    /* Hero */
    .fp-hero {
        position: relative; overflow: hidden; border-radius: 22px; padding: 28px 30px;
        background: linear-gradient(135deg, #08224A 0%, #134856 60%, #0F6E86 100%); color: #fff;
        display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
        box-shadow: 0 14px 36px rgba(8,34,74,.22);
    }
    .fp-hero::after {
        content: ''; position: absolute; right: -60px; top: -80px; width: 260px; height: 260px; border-radius: 50%;
        background: radial-gradient(circle, rgba(2,184,245,.35) 0%, transparent 65%); pointer-events: none;
    }
    .fp-hero h1 { font-family: var(--font-heading); font-size: 30px; font-weight: 700; margin: 0 0 6px; color: #fff; }
    .fp-hero p { margin: 0; color: #CFE6F0; font-size: 14.5px; }
    .fp-stats { display: flex; gap: 10px; position: relative; z-index: 1; }
    .fp-stat {
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.16); border-radius: 14px;
        padding: 10px 16px; min-width: 86px; text-align: center; text-decoration: none; color: #fff; transition: background .2s;
    }
    .fp-stat:hover { background: rgba(255,255,255,.18); color: #fff; }
    .fp-stat b { display: block; font-size: 22px; font-weight: 800; line-height: 1.1; }
    .fp-stat small { font-size: 11px; letter-spacing: .5px; text-transform: uppercase; color: #BFDDEA; font-weight: 700; }

    /* Tabs */
    .fp-tabs {
        display: flex; gap: 6px; background: #fff; border: 1px solid #DCECF5; border-radius: 16px; padding: 6px;
        margin: 22px 0 20px; overflow-x: auto; scrollbar-width: none; position: sticky; top: 84px; z-index: 20;
        box-shadow: 0 6px 18px rgba(8,34,74,.05);
    }
    .fp-tabs::-webkit-scrollbar { display: none; }
    .fp-tab {
        flex: 1 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 16px; border-radius: 11px; color: #475569; font-weight: 600; font-size: 14px; text-decoration: none;
        white-space: nowrap; transition: background .2s, color .2s;
    }
    .fp-tab:hover { background: #F5FBFF; color: #08224A; }
    .fp-tab.active { background: #02B8F5; color: #fff; box-shadow: 0 6px 14px rgba(2,184,245,.28); }
    .fp-count {
        min-width: 22px; height: 22px; padding: 0 7px; border-radius: 11px; font-size: 11.5px; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center; background: #EEF5FA; color: #08224A;
    }
    .fp-tab.active .fp-count { background: rgba(255,255,255,.25); color: #fff; }
    .fp-tab .fp-count.hot { background: #FFB300; color: #08224A; }

    .fp-panel { display: none; }
    .fp-panel.active { display: block; }
    .fp-panel.fp-anim { animation: fpIn .25s ease; }
    @keyframes fpIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    .fp-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .fp-panel-head h3 { font-family: var(--font-heading); font-size: 20px; font-weight: 700; color: #08224A; margin: 0; }
    .fp-panel-head p { margin: 2px 0 0; font-size: 13.5px; color: #64748B; }

    /* Search */
    .fp-search { position: relative; flex: 1 1 280px; max-width: 420px; }
    .fp-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #7A8CA0; font-size: 14px; }
    .fp-search input {
        width: 100%; height: 44px; border: 1.5px solid #DCECF5; border-radius: 12px; padding: 0 14px 0 40px;
        font-size: 14.5px; background: #fff; outline: none; color: #08224A; transition: border-color .2s, box-shadow .2s;
    }
    .fp-search input:focus { border-color: #02B8F5; box-shadow: 0 0 0 3px rgba(2,184,245,.12); }
    .fp-find-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 18px; }
    .fp-find-bar .fp-search { max-width: none; }
    .fp-chips { display: flex; gap: 6px; flex-wrap: wrap; }
    .fp-chip {
        border: 1px solid #DCECF5; background: #fff; color: #334155; height: 40px; padding: 0 16px; border-radius: 20px;
        font-size: 13.5px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .fp-chip.active { background: #08224A; border-color: #08224A; color: #fff; }
    .fp-hint { font-size: 12.5px; color: #7A8CA0; margin: -6px 0 14px; }
    .fp-loading { opacity: .45; pointer-events: none; transition: opacity .2s; }
    .fp-nomatch { display: none; }

    @media (max-width: 640px) {
        .fp-page { padding: 18px 12px 60px; }
        .fp-hero { padding: 22px 18px; border-radius: 18px; }
        .fp-hero h1 { font-size: 24px; }
        .fp-stats { width: 100%; }
        .fp-stat { flex: 1; min-width: 0; padding: 8px 6px; }
        .fp-stat b { font-size: 19px; }
        .fp-tabs { top: 72px; margin: 16px -12px 16px; border-radius: 0; border-left: 0; border-right: 0; }
        .fp-tab { padding: 9px 12px; font-size: 13px; }
        .fp-chip { height: 36px; padding: 0 12px; font-size: 12.5px; }
        .fp-search { flex-basis: 100%; max-width: none; }
    }
</style>

<div class="fp-page">
    <div class="fp-wrap">

        <!-- ===== HERO ===== -->
        <div class="fp-hero">
            <div style="position:relative; z-index:1;">
                <h1><i class="fas fa-user-group" style="color:#02B8F5; margin-right:8px;"></i>Friends</h1>
                <p>স্টুডেন্ট ও টিচারদের সাথে যুক্ত হন — ফ্রেন্ড হলে একে অপরের ফোন ও ইমেইল দেখতে পাবেন।</p>
            </div>
            <div class="fp-stats">
                <a href="?tab=friends" class="fp-stat" data-tab-link="friends"><b data-fr-count="friends"><?= $nFriends ?></b><small>Friends</small></a>
                <a href="?tab=requests" class="fp-stat" data-tab-link="requests"><b data-fr-count="requests"><?= $nIncoming ?></b><small>Requests</small></a>
                <a href="?tab=sent" class="fp-stat" data-tab-link="sent"><b data-fr-count="sent"><?= $nSent ?></b><small>Sent</small></a>
            </div>
        </div>

        <!-- ===== TABS ===== -->
        <nav class="fp-tabs" role="tablist">
            <?php foreach ($tabs as $key => [$label, $icon, $count, $countKey]): ?>
                <a href="?tab=<?= $key ?>" class="fp-tab <?= $tab === $key ? 'active' : '' ?>" data-tab-link="<?= $key ?>" role="tab">
                    <i class="fas <?= $icon ?>"></i> <?= $label ?>
                    <?php if ($countKey !== null): ?>
                        <span class="fp-count <?= $key === 'requests' ? 'hot' : '' ?>" data-fr-count="<?= $countKey ?>" <?= $key === 'requests' ? 'data-hide-zero' : '' ?>
                              style="<?= $key === 'requests' && $count === 0 ? 'display:none' : '' ?>"><?= $count ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- ===== MY FRIENDS ===== -->
        <section class="fp-panel <?= $tab === 'friends' ? 'active' : '' ?>" id="panel-friends">
            <div class="fp-panel-head">
                <div>
                    <h3>My Friends</h3>
                    <p>আপনার সব ফ্রেন্ড এক জায়গায়।</p>
                </div>
                <?php if ($nFriends > 0): ?>
                    <label class="fp-search">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="search" id="friendFilter" placeholder="ফ্রেন্ডদের মধ্যে খুঁজুন..." autocomplete="off" />
                    </label>
                <?php endif; ?>
            </div>
            <div class="fr-grid" id="friendsGrid">
                <?php if ($nFriends === 0): ?>
                    <?php partial('Friends/_Empty', ['icon' => 'fa-user-group', 'title' => 'এখনো কোনো ফ্রেন্ড নেই',
                        'text' => 'স্টুডেন্ট বা টিচার খুঁজে ফ্রেন্ড রিকোয়েস্ট পাঠান। তারা Accept করলেই এখানে দেখাবে।',
                        'btnText' => 'Find People', 'btnHref' => '?tab=find']); ?>
                <?php else: ?>
                    <?php foreach ($Model->Friends as $p) partial('Friends/_PersonCard', ['p' => $p]); ?>
                    <div class="fp-nomatch fr-empty" id="friendNoMatch" style="padding:30px 20px;">
                        <h5>মিল পাওয়া যায়নি</h5><p>অন্য নাম লিখে দেখুন।</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== REQUESTS (আমার কাছে এসেছে) ===== -->
        <section class="fp-panel <?= $tab === 'requests' ? 'active' : '' ?>" id="panel-requests">
            <div class="fp-panel-head">
                <div>
                    <h3>Friend Requests</h3>
                    <p>যারা আপনাকে ফ্রেন্ড হতে চেয়েছেন। Confirm চাপলে ফ্রেন্ড হয়ে যাবেন।</p>
                </div>
            </div>
            <div class="fr-grid">
                <?php if ($nIncoming === 0): ?>
                    <?php partial('Friends/_Empty', ['icon' => 'fa-inbox', 'title' => 'কোনো নতুন রিকোয়েস্ট নেই',
                        'text' => 'কেউ ফ্রেন্ড রিকোয়েস্ট পাঠালে এখানে দেখাবে, আর নোটিফিকেশনও পাবেন।']); ?>
                <?php else: ?>
                    <?php foreach ($Model->Incoming as $p) partial('Friends/_PersonCard', ['p' => $p]); ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== SENT ===== -->
        <section class="fp-panel <?= $tab === 'sent' ? 'active' : '' ?>" id="panel-sent">
            <div class="fp-panel-head">
                <div>
                    <h3>Sent Requests</h3>
                    <p>আপনার পাঠানো রিকোয়েস্ট — এখনো Accept হয়নি। চাইলে Cancel করতে পারেন।</p>
                </div>
            </div>
            <div class="fr-grid">
                <?php if ($nSent === 0): ?>
                    <?php partial('Friends/_Empty', ['icon' => 'fa-paper-plane', 'title' => 'কোনো পেন্ডিং রিকোয়েস্ট নেই',
                        'text' => 'Find People থেকে কাউকে Add Friend করলে এখানে দেখাবে।', 'btnText' => 'Find People', 'btnHref' => '?tab=find']); ?>
                <?php else: ?>
                    <?php foreach ($Model->Sent as $p) partial('Friends/_PersonCard', ['p' => $p]); ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== FIND PEOPLE ===== -->
        <section class="fp-panel <?= $tab === 'find' ? 'active' : '' ?>" id="panel-find">
            <div class="fp-panel-head">
                <div>
                    <h3>Find People</h3>
                    <p>নাম, username অথবা প্রতিষ্ঠানের নাম লিখে খুঁজুন।</p>
                </div>
            </div>
            <form class="fp-find-bar" id="findForm" action="/Friends" method="get" onsubmit="return false;">
                <input type="hidden" name="tab" value="find" />
                <label class="fp-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" name="q" id="findQ" value="<?= e($Model->Query) ?>" placeholder="যেমন: Karim, karim123, Dhaka College" autocomplete="off" maxlength="60" />
                </label>
                <div class="fp-chips" id="roleChips">
                    <?php foreach (['all' => ['সবাই', 'fa-users'], 'User' => ['Students', 'fa-user-graduate'], 'Teacher' => ['Teachers', 'fa-chalkboard-teacher']] as $rk => [$rl, $ri]): ?>
                        <button type="button" class="fp-chip <?= $Model->Role === $rk ? 'active' : '' ?>" data-role="<?= $rk ?>"><i class="fas <?= $ri ?>"></i> <?= $rl ?></button>
                    <?php endforeach; ?>
                </div>
            </form>
            <p class="fp-hint" id="findHint"><?= $Model->Query === '' ? 'নতুন যোগ দেওয়া মানুষ দেখানো হচ্ছে।' : '' ?></p>
            <div class="fr-grid" id="findGrid">
                <?php if (empty($Model->People)): ?>
                    <?php partial('Friends/_Empty', ['icon' => 'fa-magnifying-glass', 'title' => 'কাউকে পাওয়া যায়নি',
                        'text' => $Model->Query !== '' ? '"' . $Model->Query . '" নামে কেউ নেই। অন্য নাম বা username দিয়ে খুঁজুন।' : 'এখনো কেউ নেই।']); ?>
                <?php else: ?>
                    <?php foreach ($Model->People as $p) partial('Friends/_PersonCard', ['p' => $p]); ?>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    // ===== ট্যাব (রিলোড ছাড়া) =====
    var panels = document.querySelectorAll('.fp-panel');
    function showTab(key, push) {
        if (!document.getElementById('panel-' + key)) return;
        panels.forEach(function (p) { var on = p.id === 'panel-' + key; p.classList.toggle('active', on); p.classList.toggle('fp-anim', on); });
        document.querySelectorAll('.fp-tab').forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab-link') === key); });
        centerTab();
        if (push) {
            var u = new URL(location.href);
            u.searchParams.set('tab', key);
            if (key !== 'find') { u.searchParams.delete('q'); u.searchParams.delete('role'); }
            history.replaceState(null, '', u.pathname + u.search);
        }
        if (key === 'find') { var q = document.getElementById('findQ'); if (q && window.innerWidth > 768) q.focus({ preventScroll: true }); }
    }
    // মোবাইলে বর্তমান ট্যাবটা স্ক্রলবারের মাঝে আনা
    function centerTab() {
        var bar = document.querySelector('.fp-tabs'), t = bar && bar.querySelector('.fp-tab.active');
        if (t && bar.scrollWidth > bar.clientWidth) bar.scrollLeft = t.offsetLeft - (bar.clientWidth - t.offsetWidth) / 2;
    }
    centerTab();
    document.querySelectorAll('[data-tab-link]').forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); showTab(a.getAttribute('data-tab-link'), true); });
    });
    document.querySelectorAll('.fr-empty-btn[href="?tab=find"]').forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); showTab('find', true); window.scrollTo({ top: 0, behavior: 'smooth' }); });
    });

    // ===== ফ্রেন্ড লিস্টে লোকাল ফিল্টার =====
    var ff = document.getElementById('friendFilter');
    if (ff) ff.addEventListener('input', function () {
        var q = ff.value.trim().toLowerCase(), shown = 0;
        document.querySelectorAll('#friendsGrid .fr-card').forEach(function (c) {
            var ok = !q || (c.getAttribute('data-name') || '').indexOf(q) !== -1;
            c.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        var nm = document.getElementById('friendNoMatch');
        if (nm) nm.style.display = shown ? 'none' : 'block';
    });

    // ===== Find People — লাইভ সার্চ =====
    var qInput = document.getElementById('findQ'), grid = document.getElementById('findGrid'), hint = document.getElementById('findHint');
    var role = <?= js($Model->Role) ?>, timer = null, seq = 0;
    async function runSearch() {
        var q = qInput.value.trim(), my = ++seq;
        grid.classList.add('fp-loading');
        try {
            var res = await fetch('/Friends/Search?q=' + encodeURIComponent(q) + '&role=' + encodeURIComponent(role), { headers: { 'X-Requested-With': 'fetch' } });
            var html = await res.text();
            if (my !== seq) return;
            grid.innerHTML = html;
            hint.textContent = q === '' ? 'নতুন যোগ দেওয়া মানুষ দেখানো হচ্ছে।' : '';
            var u = new URL(location.href);
            u.searchParams.set('tab', 'find');
            if (q) u.searchParams.set('q', q); else u.searchParams.delete('q');
            if (role !== 'all') u.searchParams.set('role', role); else u.searchParams.delete('role');
            history.replaceState(null, '', u.pathname + u.search);
        } catch (e) {
            if (window.frToast) frToast('সার্চ করা যায়নি — আবার চেষ্টা করুন।', true);
        } finally {
            if (my === seq) grid.classList.remove('fp-loading');
        }
    }
    if (qInput) {
        qInput.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(runSearch, 300); });
        qInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); clearTimeout(timer); runSearch(); } });
    }
    document.querySelectorAll('#roleChips .fp-chip').forEach(function (c) {
        c.addEventListener('click', function () {
            document.querySelectorAll('#roleChips .fp-chip').forEach(function (x) { x.classList.remove('active'); });
            c.classList.add('active');
            role = c.getAttribute('data-role');
            runSearch();
        });
    });

    // ===== Requests ট্যাবে Confirm/Delete করলে কার্ড আস্তে সরে যায় =====
    document.addEventListener('fr:relation', function (e) {
        var uid = e.detail.uid, rel = e.detail.rel;
        var move = { requests: rel !== 'received', sent: rel !== 'sent', friends: rel !== 'friends' };
        Object.keys(move).forEach(function (k) {
            if (!move[k]) return;
            var card = document.querySelector('#panel-' + k + ' .fr-card[data-uid="' + uid + '"]');
            if (!card) return;
            setTimeout(function () {
                card.classList.add('fr-gone');
                setTimeout(function () {
                    var g = card.parentNode; card.remove();
                    if (g && !g.querySelector('.fr-card')) {
                        g.innerHTML = '<div class="fr-empty"><span class="fr-empty-icon"><i class="fas fa-check"></i></span><h5>সব শেষ!</h5><p>এই তালিকায় আর কেউ নেই।</p></div>';
                    }
                }, 300);
            }, 1200);
        });
    });
})();
</script>
<?php section_end(); ?>
