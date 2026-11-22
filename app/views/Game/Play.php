<?php
$game = $ViewBag->Game; $level = $ViewBag->Level; $m = $ViewBag->Meta;
$lvBn = ['easy' => 'সহজ', 'medium' => 'মাঝারি', 'hard' => 'কঠিন'];
$ViewData['Title'] = $m['name'] . ' — ' . $lvBn[$level];
$ViewData['FullTitle'] = $m['name'] . ' (' . $lvBn[$level] . ') — ' . t('ব্রেইন গেম', 'Brain Game') . ' | ' . seo_brand();
$ViewData['Description'] = $m['name'] . ' — ' . $m['tag'] . '। ' . t('মাষ্টার মশাই এ খেলুন, সময় আর পয়েন্ট দেখুন।', 'Play on Mashter Moshai.');
$loggedIn = $ViewBag->Me !== null;
partial('Game/_Styles');
?>
<style>
    .gp { max-width: 560px; margin: 0 auto; padding: 18px 14px 60px; }
    .gp-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .gp-back { color: #334155; text-decoration: none; font-weight: 700; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 10px; background: #fff; border: 1px solid #E2E8F0; }
    .gp-title { font-size: 22px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px; margin: 0; }
    .gp-lv { display: flex; gap: 6px; margin: 14px 0 12px; }
    .gp-lv a { flex: 1; text-align: center; padding: 8px 6px; border-radius: 10px; font-weight: 800; font-size: 14px; text-decoration: none; color: #475569; background: #fff; border: 1.5px solid #E2E8F0; }
    .gp-lv a.on { color: #fff; border-color: var(--gc); background: var(--gc); }
    .gp-bar { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
    .gp-timer { font-size: 22px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px; font-variant-numeric: tabular-nums; }
    .gp-timer i { color: var(--gc); font-size: 18px; }
    .gp-btns { display: flex; gap: 6px; }
    .gp-btn { border: 1.5px solid #E2E8F0; background: #fff; color: #334155; font-weight: 700; font-size: 13.5px; padding: 8px 12px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
    .gp-btn:hover { border-color: var(--gc); color: var(--gc); }
    .gp-btn:disabled { opacity: .45; cursor: default; }
    .gp-btn.gp-hint { border-color: #FCD34D; background: #FFFBEB; color: #92400E; }
    .gp-btn.gp-hint b { background: #F59E0B; color: #fff; border-radius: 9px; font-size: 11px; padding: 0 6px; margin-left: 2px; }
    .gp-btn.gp-hint b:empty { display: none; }
    @media (max-width: 420px) { .gp-btn { padding: 8px 9px; font-size: 12.5px; } .gp-timer { font-size: 19px; } }
    .gp-board-wrap { position: relative; }
    .gp-board { position: relative; width: 100%; aspect-ratio: 1; border-radius: 16px; overflow: hidden; touch-action: none; user-select: none; -webkit-user-select: none; background: #1E293B; box-shadow: 0 10px 30px rgba(15, 23, 42, .18); }
    .gp-loading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #CBD5E1; font-weight: 700; gap: 10px; }
    .gp-msg { min-height: 24px; text-align: center; font-size: 14px; font-weight: 700; color: #B45309; margin: 10px 0 0; transition: .2s; }
    .gp-msg.ok { color: #15803D; }
    .gp-prog { display: flex; align-items: center; gap: 10px; margin-top: 12px; font-size: 13px; font-weight: 700; color: #475569; }
    .gp-pbar { flex: 1; height: 8px; border-radius: 8px; background: #E2E8F0; overflow: hidden; }
    .gp-pbar i { display: block; height: 100%; width: 0; border-radius: 8px; background: var(--gc); transition: width .2s; }
    @keyframes gpEmpty { 0%, 100% { background: transparent; } 50% { background: rgba(250, 204, 21, .55); } }
    .gp-empty { animation: gpEmpty .7s ease-in-out 3; }
    .gp-shake { animation: gpShake .4s; }
    @keyframes gpShake { 20%, 60% { transform: translateX(-7px); } 40%, 80% { transform: translateX(7px); } }
    .gp-rules { margin-top: 16px; background: #fff; border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; }
    .gp-rules summary { cursor: pointer; padding: 13px 16px; font-weight: 800; color: #0F172A; list-style: none; display: flex; justify-content: space-between; align-items: center; }
    .gp-rules summary::-webkit-details-marker { display: none; }
    .gp-rules ol { margin: 0; padding: 0 18px 14px 36px; color: #475569; font-size: 14px; line-height: 1.75; }
    .gp-note { font-size: 12.5px; color: #64748B; text-align: center; margin-top: 12px; }
    .gp-note a { color: #0369A1; font-weight: 700; }

    /* ---------- Zip ---------- */
    .zg { position: absolute; inset: 0; display: grid; }
    .zc { border-right: 1px solid rgba(148, 163, 184, .18); border-bottom: 1px solid rgba(148, 163, 184, .18); position: relative; }
    .zsvg { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }
    .zn { position: absolute; left: 50%; top: 50%; width: 62%; height: 62%; transform: translate(-50%, -50%); border-radius: 50%; background: #F1F5F9; color: #0F172A; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: clamp(14px, 4.4vw, 22px); z-index: 2; box-shadow: 0 2px 6px rgba(0,0,0,.25); pointer-events: none; }
    .zn.on { background: #fff; outline: 3px solid rgba(255,255,255,.35); }

    /* ---------- Sudoku ---------- */
    .gp-board.sd { background: #fff; border: 3px solid #334155; }
    .sg { position: absolute; inset: 0; display: grid; grid-template-columns: repeat(6, 1fr); grid-template-rows: repeat(6, 1fr); }
    .sc { border-right: 1px solid #CBD5E1; border-bottom: 1px solid #CBD5E1; display: flex; align-items: center; justify-content: center; font-size: clamp(20px, 6.5vw, 32px); font-weight: 700; color: #1D4ED8; cursor: pointer; }
    .sc.br { border-right: 3px solid #334155; } .sc.bb { border-bottom: 3px solid #334155; }
    .sc.given { color: #0F172A; background: #F1F5F9; font-weight: 800; cursor: default; }
    .sc.hl { background: #EFF6FF; } .sc.given.hl { background: #E2E8F0; }
    .sc.same { background: #DBEAFE; }
    .sc.sel { background: #BFDBFE !important; box-shadow: inset 0 0 0 3px #2563EB; }
    .sc.bad { color: #DC2626 !important; background: #FEE2E2 !important; }
    .sk { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 14px; }
    .sk button { border: none; border-radius: 12px; background: #E2E8F0; color: #0F172A; font-size: 24px; font-weight: 800; padding: 12px 0; cursor: pointer; }
    .sk button:hover { background: #CBD5E1; }
    .sk button.fn { font-size: 13px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; }
    .sk button.fn i { font-size: 18px; }

    /* ---------- Patches ---------- */
    .pg { position: absolute; inset: 0; display: grid; }
    .pc { border-right: 1px dashed rgba(148, 163, 184, .35); border-bottom: 1px dashed rgba(148, 163, 184, .35); position: relative; }
    .pr { position: absolute; border-radius: 10px; border: 3px solid; z-index: 1; pointer-events: none; transition: opacity .15s; }
    .pr.bad { border-style: dashed; }
    .pr.prev { border-style: dashed; opacity: .9; z-index: 3; }
    .pr .sz { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-size: 12px; font-weight: 800; color: #fff; background: rgba(0,0,0,.35); padding: 1px 7px; border-radius: 8px; white-space: nowrap; }
    .pk { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 70%; height: 70%; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: clamp(15px, 5vw, 26px); z-index: 2; pointer-events: none; box-shadow: 0 2px 6px rgba(0,0,0,.3); }
    .pk svg { width: 55%; height: 55%; }
    .pk-bg { position: absolute; inset: 10%; opacity: .32; display: flex; } .pk-bg svg { width: 100%; height: 100%; }
    .pk-n { position: relative; text-shadow: 0 1px 2px rgba(0,0,0,.3); }
    .pk-legend { display: flex; flex-wrap: wrap; gap: 8px 16px; justify-content: center; font-size: 12.5px; color: #475569; margin-top: 10px; }
    .pk-legend span { display: inline-flex; align-items: center; gap: 5px; }
    .pk-legend svg { width: 16px; height: 16px; }

    /* ---------- ফলাফল ---------- */
    .gp-res { position: fixed; inset: 0; z-index: 2000; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(2, 6, 23, .6); backdrop-filter: blur(3px); }
    .gp-res.show { display: flex; }
    .gp-rc { position: relative; width: 100%; max-width: 420px; max-height: calc(100vh - 32px); overflow: auto; border-radius: 24px; background: #fff; text-align: center; box-shadow: 0 30px 80px rgba(0,0,0,.35); animation: gpPop .35s cubic-bezier(.3,1.5,.5,1); }
    @keyframes gpPop { from { transform: scale(.85); opacity: 0; } }
    .gp-rh { padding: 22px 18px 18px; color: #fff; background: linear-gradient(135deg, var(--gc), #0B3A6E); }
    .gp-rh .em { font-size: 40px; line-height: 1; }
    .gp-rh h3 { margin: 6px 0 2px; font-size: 22px; font-weight: 800; color: #fff; }
    .gp-rh small { color: rgba(255,255,255,.85); font-weight: 600; }
    .gp-rt { font-size: 52px; font-weight: 800; line-height: 1.1; margin-top: 8px; font-variant-numeric: tabular-nums; }
    .gp-rp { display: inline-block; margin-top: 8px; background: #FACC15; color: #422006; font-weight: 800; padding: 5px 14px; border-radius: 20px; font-size: 15px; }
    .gp-rb { padding: 16px 18px 18px; }
    .gp-rg { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .gp-rg div { background: #F8FAFC; border-radius: 12px; padding: 10px 8px; }
    .gp-rg b { display: block; font-size: 19px; font-weight: 800; color: #0F172A; }
    .gp-rg small { color: #64748B; font-size: 12px; }
    .gp-rnote { margin: 10px 0 0; font-size: 13.5px; color: #475569; line-height: 1.55; background: #FFF7ED; border-radius: 12px; padding: 10px 12px; }
    .gp-ra { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 14px; }
    .gp-ra a, .gp-ra button { border: none; border-radius: 12px; padding: 12px 8px; font-weight: 800; font-size: 14px; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 7px; }
    .gp-ra .pri { grid-column: 1 / -1; background: var(--gc); color: #fff; font-size: 16px; }
    .gp-ra .sec { background: #F1F5F9; color: #0F172A; }
    .gp-ra .lg { grid-column: 1 / -1; background: #FACC15; color: #422006; }
    .gp-x { position: absolute; right: 12px; top: 10px; background: rgba(255,255,255,.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; font-size: 16px; cursor: pointer; z-index: 2; }
    #gpConfetti { position: fixed; inset: 0; pointer-events: none; z-index: 2001; }
</style>

<div class="gp" style="--gc:<?= e($m['color']) ?>;">
    <?= csrf_field() ?>
    <div class="gp-top">
        <a href="/Game" class="gp-back"><i class="fas fa-arrow-left"></i> <?= t('সব গেম', 'All games') ?></a>
        <h1 class="gp-title"><i class="fas <?= e($m['icon']) ?>" style="color:<?= e($m['color']) ?>;"></i> <?= e($m['name']) ?></h1>
        <a href="/Game/Leaderboard" class="gp-back" title="Leaderboard"><i class="fas fa-trophy" style="color:#F59E0B;"></i></a>
    </div>
    <div class="gp-lv">
        <?php foreach ($lvBn as $k => $lb): ?><a href="/Game/Play/<?= $game ?>?level=<?= $k ?>" class="<?= $k === $level ? 'on' : '' ?>"><?= $lb ?></a><?php endforeach; ?>
    </div>
    <div class="gp-bar">
        <div class="gp-timer"><i class="far fa-clock"></i> <span id="gpTime">0:00</span></div>
        <div class="gp-btns">
            <button type="button" class="gp-btn gp-hint" id="gpHint"><i class="far fa-lightbulb"></i> <?= t('হিন্ট', 'Hint') ?><b id="gpHintN"></b></button>
            <button type="button" class="gp-btn" id="gpUndo"><i class="fas fa-rotate-left"></i> <?= t('আগের ধাপ', 'Undo') ?></button>
            <button type="button" class="gp-btn" id="gpReset"><i class="fas fa-eraser"></i> <?= t('মুছুন', 'Clear') ?></button>
        </div>
    </div>
    <div class="gp-board-wrap">
        <div class="gp-board<?= $game === 'sudoku' ? ' sd' : '' ?>" id="gpBoard"><div class="gp-loading"><i class="fas fa-spinner fa-spin"></i> <?= t('পাজল তৈরি হচ্ছে...', 'Preparing puzzle...') ?></div></div>
    </div>
    <?php if ($game === 'sudoku'): ?>
        <div class="sk" id="sdKeys">
            <?php foreach ([1, 2, 3] as $d): ?><button type="button" data-d="<?= $d ?>"><?= $d ?></button><?php endforeach; ?>
            <button type="button" class="fn" data-d="0"><i class="fas fa-xmark"></i><?= t('মুছুন', 'Erase') ?></button>
            <?php foreach ([4, 5, 6] as $d): ?><button type="button" data-d="<?= $d ?>"><?= $d ?></button><?php endforeach; ?>
            <button type="button" class="fn" data-d="u"><i class="fas fa-rotate-left"></i><?= t('আগের', 'Undo') ?></button>
        </div>
    <?php elseif ($game === 'patches'): ?>
        <div class="pk-legend" id="pkLegend"></div>
    <?php endif; ?>
    <div class="gp-prog" id="gpProg"><div class="gp-pbar"><i id="gpPbar"></i></div><span id="gpPtxt"></span></div>
    <div class="gp-msg" id="gpMsg"></div>

    <details class="gp-rules" id="gpRules">
        <summary><span><i class="fas fa-circle-question" style="color:<?= e($m['color']) ?>;"></i> <?= t('কীভাবে খেলবেন', 'How to play') ?></span><i class="fas fa-chevron-down"></i></summary>
        <ol><?php foreach ($m['rules'] as $rl): ?><li><?= e($rl) ?></li><?php endforeach; ?></ol>
    </details>
    <p class="gp-note">
        <?php if ($loggedIn): ?><?= t('দ্রুত শেষ করলে বেশি পয়েন্ট — কঠিন লেভেলে আরো বেশি!', 'Finish fast for more points — harder levels give more!') ?> · <a href="/Game/Leaderboard"><?= t('লিডারবোর্ড', 'Leaderboard') ?></a>
        <?php endif; ?>
    </p>
</div>

<div class="gp-res" id="gpRes" style="--gc:<?= e($m['color']) ?>;" role="dialog" aria-modal="true" aria-labelledby="gpResT">
    <div class="gp-rc">
        <button type="button" class="gp-x" id="gpResX" aria-label="Close">✕</button>
        <div class="gp-rh">
            <div class="em">🎉</div>
            <h3 id="gpResT"><?= t('দারুণ! সমাধান হয়েছে', 'Solved!') ?></h3>
            <small><?= e($m['name']) ?> · <?= $lvBn[$level] ?></small>
            <div class="gp-rt" id="gpResTime">0:00</div>
            <div id="gpResPts"></div>
        </div>
        <div class="gp-rb">
            <div class="gp-rg" id="gpResGrid"></div>
            <div id="gpResNote"></div>
            <div class="gp-ra">
                <button type="button" class="pri" id="gpAgain"><i class="fas fa-shuffle"></i> <?= t('আবার খেলুন — নতুন পাজল', 'Play again — new puzzle') ?></button>
                <a class="sec" href="/Game/Leaderboard"><i class="fas fa-trophy" style="color:#F59E0B;"></i> <?= t('লিডারবোর্ড', 'Leaderboard') ?></a>
                <button type="button" class="sec" id="gpShare"><i class="fas fa-share-nodes"></i> <?= t('শেয়ার', 'Share') ?></button>
                <?php if (!$loggedIn): ?><a class="lg" href="/Account/Login?returnUrl=<?= rawurlencode('/Game/Play/' . $game . '?level=' . $level) ?>"><i class="fas fa-right-to-bracket"></i> <?= t('লগইন করে পয়েন্ট জিতুন', 'Log in to earn points') ?></a><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<canvas id="gpConfetti"></canvas>

<?php section_start('Scripts'); ?>
<script>
(function () {
    'use strict';
    var GAME = <?= js($game) ?>, LEVEL = <?= js($level) ?>, LOGGED = <?= $loggedIn ? 'true' : 'false' ?>, GNAME = <?= js($m['name']) ?>, LVBN = <?= js($lvBn[$level]) ?>;
    var SHARE_URL = <?= js(site_public_url() . '/Game/Play/' . $game . '?level=' . $level) ?>;
    var board = document.getElementById('gpBoard'), msgEl = document.getElementById('gpMsg');
    var bn = function (s) { return String(s).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }); };
    var fmt = function (s) { s = Math.max(0, s | 0); return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); };
    var token = function () { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; };
    var round = null, t0 = 0, timerId = null, done = false, busy = false, api = null;

    function prog(done2, total, label) {
        document.getElementById('gpPbar').style.width = Math.round(100 * done2 / total) + '%';
        document.getElementById('gpPtxt').textContent = bn(done2) + ' / ' + bn(total) + ' ' + label;
    }
    function flash(els) { els.forEach(function (e) { e.classList.remove('gp-empty'); void e.offsetWidth; e.classList.add('gp-empty'); }); }
    function msg(t, ok) { msgEl.textContent = t || ''; msgEl.className = 'gp-msg' + (ok ? ' ok' : ''); }
    function shake() { board.classList.remove('gp-shake'); void board.offsetWidth; board.classList.add('gp-shake'); }
    function tick() { if (!done) document.getElementById('gpTime').textContent = fmt((Date.now() - t0) / 1000); }

    // প্রথমবার নিয়ম খোলা থাকবে
    try { if (!localStorage.getItem('gm_rules_' + GAME)) { document.getElementById('gpRules').open = true; localStorage.setItem('gm_rules_' + GAME, '1'); } } catch (e) { }

    function post(url, data) {
        var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', token());
        return fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); });
    }

    function load() {
        done = false; busy = false; msg('');
        board.innerHTML = '<div class="gp-loading"><i class="fas fa-spinner fa-spin"></i> পাজল তৈরি হচ্ছে...</div>';
        post('/Game/New', { game: GAME, level: LEVEL }).then(function (d) {
            if (!d.success) { if (d.login) { location.href = '/Account/Login?returnUrl=' + encodeURIComponent(location.pathname + location.search); return; } board.innerHTML = '<div class="gp-loading">' + (d.message || 'পাজল পাওয়া যায়নি') + '</div>'; return; }
            round = d; board.innerHTML = ''; hintN = 0; document.getElementById('gpHintN').textContent = '';
            api = ({ zip: Zip, sudoku: Sudoku, patches: Patches })[GAME](d.puzzle);
            t0 = Date.now(); clearInterval(timerId); timerId = setInterval(tick, 250); tick();
        }).catch(function () { board.innerHTML = '<div class="gp-loading">ইন্টারনেট সমস্যা — পেজটা আবার লোড করুন।</div>'; });
    }

    function submit(answer) {
        if (done || busy) return;
        busy = true;
        post('/Game/Submit', { id: round.id, token: round.token, answer: JSON.stringify(answer) }).then(function (d) {
            busy = false;
            if (!d.success) { if (d.login) { alert(d.message); location.href = '/Account/Login?returnUrl=' + encodeURIComponent(location.pathname + location.search); return; } msg(d.message || 'এখনো মেলেনি'); shake(); return; }
            done = true; clearInterval(timerId);
            document.getElementById('gpTime').textContent = fmt(d.seconds);
            showResult(d);
        }).catch(function () { busy = false; msg('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন'); });
    }

    var hintN = 0;
    document.getElementById('gpHint').onclick = function () {
        if (!api || done || busy || !round) return;
        var warned = false; try { warned = localStorage.getItem('gm_hint_ok') === '1'; } catch (e) { }
        if (!warned) { if (!confirm('হিন্ট নিলে এই খেলার পয়েন্ট প্রতিবার ২০% করে কমবে। হিন্ট নেবেন?')) return; try { localStorage.setItem('gm_hint_ok', '1'); } catch (e) { } }
        var btn = this; btn.disabled = true;
        post('/Game/Hint', { id: round.id, token: round.token, state: JSON.stringify(api.state()) }).then(function (d) {
            btn.disabled = false;
            if (!d.success) { msg(d.message || 'হিন্ট পাওয়া যায়নি'); return; }
            hintN = d.hints; document.getElementById('gpHintN').textContent = bn(hintN);
            msg('💡 ' + api.hint(d.hint), true);
        }).catch(function () { btn.disabled = false; msg('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন'); });
    };
    document.getElementById('gpUndo').onclick = function () { if (api && !done) api.undo(); };
    document.getElementById('gpReset').onclick = function () { if (api && !done) api.reset(); };

    // ============================================================ ZIP
    function Zip(p) {
        var n = p.n, L = n * n, nums = {}, maxN = 0, path = [], drag = false;
        Object.keys(p.nums).forEach(function (k) { nums[+k] = p.nums[k]; maxN = Math.max(maxN, p.nums[k]); });
        var g = document.createElement('div'); g.className = 'zg'; g.style.gridTemplateColumns = 'repeat(' + n + ',1fr)'; g.style.gridTemplateRows = 'repeat(' + n + ',1fr)';
        var cells = [];
        for (var i = 0; i < L; i++) {
            var c = document.createElement('div'); c.className = 'zc';
            if (i % n === n - 1) c.style.borderRight = 'none'; if (i >= L - n) c.style.borderBottom = 'none';
            if (nums[i]) { var b = document.createElement('div'); b.className = 'zn'; b.textContent = nums[i]; c.appendChild(b); }
            g.appendChild(c); cells.push(c);
        }
        var NS = 'http://www.w3.org/2000/svg', svg = document.createElementNS(NS, 'svg'); svg.setAttribute('class', 'zsvg'); svg.setAttribute('viewBox', '0 0 ' + n + ' ' + n);
        svg.innerHTML = '<defs><linearGradient id="zgr" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FB923C"/><stop offset=".55" stop-color="#F97316"/><stop offset="1" stop-color="#EF4444"/></linearGradient></defs>';
        var pl = document.createElementNS(NS, 'polyline'); pl.setAttribute('fill', 'none'); pl.setAttribute('stroke', 'url(#zgr)'); pl.setAttribute('stroke-width', '0.46'); pl.setAttribute('stroke-linecap', 'round'); pl.setAttribute('stroke-linejoin', 'round');
        var dot = document.createElementNS(NS, 'circle'); dot.setAttribute('r', '0.23'); dot.setAttribute('fill', '#F97316');
        svg.appendChild(pl); svg.appendChild(dot); board.appendChild(svg); board.appendChild(g);
        var xy = function (i) { return [(i % n) + .5, Math.floor(i / n) + .5]; };
        var adj = function (a, b) { return Math.abs(Math.floor(a / n) - Math.floor(b / n)) + Math.abs(a % n - b % n) === 1; };
        var nextNum = function () { var k = 1; path.forEach(function (c) { if (nums[c]) k = nums[c] + 1; }); return k; };
        function draw() {
            pl.setAttribute('points', path.map(function (c) { return xy(c).join(','); }).join(' '));
            if (path.length) { var q = xy(path[path.length - 1]); dot.setAttribute('cx', q[0]); dot.setAttribute('cy', q[1]); dot.style.display = path.length === 1 ? '' : 'none'; } else dot.style.display = 'none';
            var on = {}; path.forEach(function (c) { on[c] = 1; });
            cells.forEach(function (c, i) { var b = c.querySelector('.zn'); if (b) b.classList.toggle('on', !!on[i]); });
            prog(path.length, L, 'ঘর ভরা হয়েছে');
        }
        function tryAdd(c, quiet) {
            if (path.indexOf(c) >= 0) return false;
            var last = path[path.length - 1];
            if (!adj(last, c)) return false;
            if (nums[c] && nums[c] !== nextNum()) { if (!quiet) msg('আগে ' + bn(nextNum()) + ' নম্বরে যান'); return false; }
            path.push(c); return true;
        }
        // আঙুল / মাউসের জায়গা → ঘর। ঘরের একদম কিনারায় থাকলে (কোনাকুনি ভুল এড়াতে) -১
        function cellAt(x, y, strict) {
            var r = board.getBoundingClientRect(), cw = r.width / n, fx = (x - r.left) / cw, fy = (y - r.top) / cw;
            if (fx < 0 || fy < 0 || fx >= n || fy >= n) return -1;
            var cx = Math.floor(fx), cy = Math.floor(fy);
            if (strict) { var dx = fx - cx - .5, dy = fy - cy - .5; if (Math.abs(dx) > .38 || Math.abs(dy) > .38) return -1; }
            return cy * n + cx;
        }
        // last থেকে c পর্যন্ত ছোট পথ (ফাঁকা ঘর দিয়ে) — দ্রুত বা কোনাকুনি টানলে মাঝের ঘরগুলো নিজে থেকে ভরে
        function bridge(c, hx, hy) {
            var last = path[path.length - 1], used = {}; path.forEach(function (x) { used[x] = 1; });
            var prevMap = {}, q = [last], seen = {}; seen[last] = 1;
            while (q.length) {
                var x = q.shift(); if (x === c) break;
                var xr = Math.floor(x / n), xc = x % n, nb = [];
                if (xr > 0) nb.push(x - n); if (xr < n - 1) nb.push(x + n); if (xc > 0) nb.push(x - 1); if (xc < n - 1) nb.push(x + 1);
                if (hx !== undefined) nb.sort(function (p1, p2) { var d1 = Math.pow(p1 % n + .5 - hx, 2) + Math.pow(Math.floor(p1 / n) + .5 - hy, 2), d2 = Math.pow(p2 % n + .5 - hx, 2) + Math.pow(Math.floor(p2 / n) + .5 - hy, 2); return d1 - d2; });
                nb.forEach(function (v) { if (seen[v] || used[v]) return; if (nums[v] && v !== c) return; if (Math.abs(Math.floor(v / n) - Math.floor(c / n)) + Math.abs(v % n - c % n) > 6) return; seen[v] = 1; prevMap[v] = x; q.push(v); });
            }
            if (!seen[c]) return false;
            var seq = [], y = c; while (y !== last) { seq.unshift(y); y = prevMap[y]; }
            if (seq.length > 6) return false;
            for (var k = 0; k < seq.length; k++) if (!tryAdd(seq[k], k < seq.length - 1)) break;
            return true;
        }
        function moveTo(c, hx, hy) {
            if (c < 0 || !path.length) return false;
            var last = path[path.length - 1]; if (c === last) return false;
            var idx = path.indexOf(c);
            if (idx >= 0) { path = path.slice(0, idx + 1); return true; }   // পেছনে টানলে মুছে যায়
            if (adj(last, c)) { tryAdd(c); return true; }
            return bridge(c, hx, hy);
        }
        function check() {
            if (path.length === L) { if (nums[path[L - 1]] === maxN) submit(path.slice()); else { msg('সব ঘর ভরেছে — কিন্তু শেষ সংখ্যায় (' + bn(maxN) + ') গিয়ে শেষ হতে হবে'); shake(); } return; }
            if (path.length && nums[path[path.length - 1]] === maxN) {
                // শেষ সংখ্যায় পৌঁছেছে কিন্তু ঘর ফাঁকা — কোন ঘরগুলো বাকি দেখিয়ে দেওয়া
                var on = {}; path.forEach(function (c) { on[c] = 1; });
                var empty = cells.filter(function (c, i) { return !on[i]; });
                msg('প্রায় হয়ে গেছে! কিন্তু ' + bn(empty.length) + 'টা ঘর এখনো ফাঁকা — সব ঘর পার হয়ে তারপর ' + bn(maxN) + '-এ শেষ করুন (হলুদ ঘরগুলো দেখুন)');
                flash(empty); shake();
            }
        }
        var lastX = 0, lastY = 0, raf = 0;
        function follow(x, y) {
            // আগের জায়গা থেকে এখন পর্যন্ত রেখা ধরে ছোট ছোট ধাপে — কোনো ঘর বাদ পড়বে না
            var r = board.getBoundingClientRect(), cw = r.width / n, step = cw / 6, dx = x - lastX, dy = y - lastY, d = Math.sqrt(dx * dx + dy * dy), k = Math.max(1, Math.ceil(d / step)), ch = false, px = lastX, py = lastY;
            for (var t = 1; t <= k; t++) {
                var sx = lastX + dx * t / k, sy = lastY + dy * t / k, c = cellAt(sx, sy, true);
                if (c >= 0 && moveTo(c, ((px + sx) / 2 - r.left) / cw, ((py + sy) / 2 - r.top) / cw)) ch = true;
                if (c >= 0) { px = sx; py = sy; }
            }
            lastX = x; lastY = y;
            if (ch && !raf) raf = requestAnimationFrame(function () { raf = 0; draw(); });
        }
        board.onpointerdown = function (e) {
            if (done) return;
            var c = cellAt(e.clientX, e.clientY, false); if (c < 0) return;
            e.preventDefault();
            try { board.setPointerCapture(e.pointerId); } catch (er) { }
            lastX = e.clientX; lastY = e.clientY; drag = true;
            if (!path.length) { if (nums[c] === 1) { path = [c]; draw(); msg(''); } else { msg('১ থেকে শুরু করুন'); drag = false; } return; }
            var idx = path.indexOf(c);
            if (idx >= 0) { path = path.slice(0, idx + 1); draw(); msg(''); return; }
            if (moveTo(c)) { draw(); msg(''); } else drag = !!path.length;
        };
        board.onpointermove = function (e) {
            if (!drag || done) return;
            var evs = e.getCoalescedEvents ? e.getCoalescedEvents() : [e]; if (!evs.length) evs = [e];
            evs.forEach(function (ev) { follow(ev.clientX, ev.clientY); });
        };
        board.onpointerup = board.onpointercancel = function () { if (!drag) return; drag = false; draw(); check(); };
        draw();
        return {
            undo: function () { path.pop(); draw(); msg(''); }, reset: function () { path = []; draw(); msg(''); },
            state: function () { return path.slice(); },
            hint: function (h) { path = h.path.map(Number); draw(); var last = path[path.length - 1]; cells[last].animate([{ background: 'rgba(250,204,21,.65)' }, { background: 'transparent' }], { duration: 1200 }); check(); return h.fixed ? 'ভুল মোড়টা ঠিক করে পরের ঘরগুলো দেখিয়ে দেওয়া হলো' : 'পরের ঘরগুলো দেখিয়ে দেওয়া হলো'; }
        };
    }

    // ============================================================ SUDOKU
    function Sudoku(p) {
        var given = p.grid.map(Number), val = given.slice(), sel = -1, hist = [];
        var g = document.createElement('div'); g.className = 'sg'; var cells = [];
        for (var i = 0; i < 36; i++) {
            var c = document.createElement('div'), r = Math.floor(i / 6), k = i % 6;
            c.className = 'sc' + (given[i] ? ' given' : '') + (k === 2 ? ' br' : '') + ((r === 1 || r === 3) ? ' bb' : '');
            if (k === 5) c.style.borderRight = 'none'; if (r === 5) c.style.borderBottom = 'none';
            c.dataset.i = i; g.appendChild(c); cells.push(c);
        }
        board.appendChild(g);
        var box = function (i) { return Math.floor(Math.floor(i / 6) / 2) * 2 + Math.floor((i % 6) / 3); };
        var peers = function (a, b) { return a !== b && (Math.floor(a / 6) === Math.floor(b / 6) || a % 6 === b % 6 || box(a) === box(b)); };
        function draw() {
            var bad = {};
            for (var a = 0; a < 36; a++) if (val[a]) for (var b = a + 1; b < 36; b++) if (val[b] === val[a] && peers(a, b)) { if (!given[a]) bad[a] = 1; if (!given[b]) bad[b] = 1; if (given[a] && given[b]) { } }
            cells.forEach(function (c, i) {
                c.textContent = val[i] || '';
                c.classList.toggle('sel', i === sel);
                c.classList.toggle('hl', sel >= 0 && peers(i, sel));
                c.classList.toggle('same', sel >= 0 && val[sel] > 0 && val[i] === val[sel] && i !== sel);
                c.classList.toggle('bad', !!bad[i]);
            });
            prog(val.filter(function (v) { return v > 0; }).length, 36, 'ঘর ভরা হয়েছে');
            return Object.keys(bad).length === 0;
        }
        function put(d) {
            if (done || sel < 0 || given[sel]) return;
            if (val[sel] === d) return;
            hist.push([sel, val[sel]]); val[sel] = d;
            var ok = draw();
            if (val.every(function (v) { return v > 0; })) { if (ok) submit(val.slice()); else { msg('কিছু সংখ্যা একই সারি / কলাম / বক্সে দুবার আছে — লাল ঘরগুলো দেখুন'); shake(); } }
            else msg('');
        }
        g.addEventListener('click', function (e) { var c = e.target.closest('.sc'); if (!c || done) return; sel = +c.dataset.i; draw(); });
        document.getElementById('sdKeys').onclick = function (e) {
            var b = e.target.closest('button'); if (!b) return;
            if (b.dataset.d === 'u') return undo();
            if (sel < 0) { msg('আগে একটা খালি ঘরে চাপ দিন'); return; }
            put(+b.dataset.d);
        };
        function undo() { var h = hist.pop(); if (!h) return; val[h[0]] = h[1]; sel = h[0]; draw(); msg(''); }
        document.onkeydown = function (e) {
            if (done || e.target.closest('input,textarea')) return;
            if (/^[1-6]$/.test(e.key)) { put(+e.key); e.preventDefault(); }
            else if (e.key === 'Backspace' || e.key === 'Delete' || e.key === '0') { put(0); e.preventDefault(); }
            else if (sel >= 0 && /^Arrow/.test(e.key)) {
                var r = Math.floor(sel / 6), k = sel % 6;
                if (e.key === 'ArrowUp') r = (r + 5) % 6; if (e.key === 'ArrowDown') r = (r + 1) % 6; if (e.key === 'ArrowLeft') k = (k + 5) % 6; if (e.key === 'ArrowRight') k = (k + 1) % 6;
                sel = r * 6 + k; draw(); e.preventDefault();
            }
        };
        sel = given.indexOf(0); draw();
        return {
            undo: undo, reset: function () { hist = []; val = given.slice(); draw(); msg(''); },
            state: function () { return val.slice(); },
            hint: function (h) {
                sel = h.cell; hist.push([h.cell, val[h.cell]]); val[h.cell] = h.value; var ok = draw();
                cells[h.cell].animate([{ background: '#FDE68A' }, { background: '#BFDBFE' }], { duration: 1400 });
                if (val.every(function (v) { return v > 0; }) && ok) submit(val.slice());
                return h.fixed ? 'এই ঘরের সংখ্যাটা ভুল ছিল — ঠিক করে দেওয়া হলো' : 'এই ঘরে ' + bn(h.value) + ' বসবে';
            }
        };
    }

    // ============================================================ PATCHES
    function Patches(p) {
        var n = p.n, L = n * n, clues = p.clues, rects = [], hist = [], a = -1, prev = null;
        var COLORS = ['#0EA5E9', '#F97316', '#8B5CF6', '#10B981', '#EF4444', '#EAB308', '#EC4899', '#14B8A6', '#6366F1', '#84CC16', '#F43F5E', '#06B6D4', '#A855F7', '#22C55E', '#FB923C', '#3B82F6', '#D946EF', '#65A30D'];
        var clueAt = {}; clues.forEach(function (cl, i) { cl.col = COLORS[i % COLORS.length]; clueAt[cl.c] = i; });
        var ICON = {
            sq: '<svg viewBox="0 0 20 20"><rect x="3" y="3" width="14" height="14" rx="2" fill="currentColor"/></svg>',
            tall: '<svg viewBox="0 0 20 20"><rect x="6" y="2" width="8" height="16" rx="2" fill="currentColor"/></svg>',
            wide: '<svg viewBox="0 0 20 20"><rect x="2" y="6" width="16" height="8" rx="2" fill="currentColor"/></svg>',
            any: '<svg viewBox="0 0 20 20"><rect x="3" y="3" width="14" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2.4" stroke-dasharray="3 2.4"/></svg>'
        };
        var LBL = { sq: 'বর্গ', tall: 'লম্বা আয়ত', wide: 'চওড়া আয়ত', any: 'যেকোনো আকৃতি' };
        document.getElementById('pkLegend').innerHTML = ['sq', 'tall', 'wide', 'any'].map(function (k) { return '<span style="color:#334155">' + ICON[k] + LBL[k] + '</span>'; }).join('') + '<span>🔢 সংখ্যা = কয়টা ঘর</span>';
        var g = document.createElement('div'); g.className = 'pg'; g.style.gridTemplateColumns = 'repeat(' + n + ',1fr)'; g.style.gridTemplateRows = 'repeat(' + n + ',1fr)';
        for (var i = 0; i < L; i++) {
            var c = document.createElement('div'); c.className = 'pc';
            if (i % n === n - 1) c.style.borderRight = 'none'; if (i >= L - n) c.style.borderBottom = 'none';
            if (clueAt[i] !== undefined) {
                var cl = clues[clueAt[i]], k = document.createElement('div'); k.className = 'pk'; k.style.background = cl.col; k.title = LBL[cl.t] + (cl.s ? ' · ' + cl.s + ' ঘর' : '');
                k.innerHTML = cl.s ? '<span class="pk-bg">' + ICON[cl.t] + '</span><span class="pk-n">' + cl.s + '</span>' : ICON[cl.t];
                c.appendChild(k);
            }
            g.appendChild(c);
        }
        var layer = document.createElement('div'); layer.style.cssText = 'position:absolute;inset:0;pointer-events:none;';
        board.appendChild(g); board.appendChild(layer);
        var rc = function (i) { return [Math.floor(i / n), i % n]; };
        function box(x, y) { var A = rc(x), B = rc(y); var r = Math.min(A[0], B[0]), c = Math.min(A[1], B[1]); return { r: r, c: c, h: Math.abs(A[0] - B[0]) + 1, w: Math.abs(A[1] - B[1]) + 1 }; }
        function inside(R) { var out = []; for (var i = R.r; i < R.r + R.h; i++) for (var j = R.c; j < R.c + R.w; j++) if (clueAt[i * n + j] !== undefined) out.push(clueAt[i * n + j]); return out; }
        function fits(cl, R) { if (cl.t === 'sq' && R.h !== R.w) return false; if (cl.t === 'tall' && R.h <= R.w) return false; if (cl.t === 'wide' && R.w <= R.h) return false; if (cl.s && cl.s !== R.h * R.w) return false; return true; }
        function overlap(A, B) { return A.r < B.r + B.h && B.r < A.r + A.h && A.c < B.c + B.w && B.c < A.c + A.w; }
        function el(R, cls, col) {
            var d = document.createElement('div'); d.className = 'pr ' + cls; var pad = 3;
            d.style.left = 'calc(' + (R.c * 100 / n) + '% + ' + pad + 'px)'; d.style.top = 'calc(' + (R.r * 100 / n) + '% + ' + pad + 'px)';
            d.style.width = 'calc(' + (R.w * 100 / n) + '% - ' + 2 * pad + 'px)'; d.style.height = 'calc(' + (R.h * 100 / n) + '% - ' + 2 * pad + 'px)';
            d.style.borderColor = col; d.style.background = col + '55';
            d.innerHTML = '<span class="sz">' + R.h + '×' + R.w + (R.h * R.w > 1 ? ' = ' + R.h * R.w : '') + '</span>';
            return d;
        }
        function draw() {
            var cov = 0; rects.forEach(function (R) { cov += R.h * R.w; }); prog(cov, L, 'ঘর ঢাকা হয়েছে');
            layer.innerHTML = '';
            rects.forEach(function (R) { var cl = clues[R.k]; layer.appendChild(el(R, fits(cl, R) ? '' : 'bad', fits(cl, R) ? cl.col : '#EF4444')); });
            if (prev) { var ins = inside(prev); layer.appendChild(el(prev, 'prev', ins.length === 1 ? clues[ins[0]].col : '#94A3B8')); }
        }
        function cellAt(e) {
            var r = board.getBoundingClientRect(), x = Math.min(Math.max(e.clientX - r.left, 0), r.width - 1), y = Math.min(Math.max(e.clientY - r.top, 0), r.height - 1);
            return Math.floor(y / (r.height / n)) * n + Math.floor(x / (r.width / n));
        }
        function place(R) {
            var ins = inside(R);
            if (ins.length !== 1) { msg(ins.length ? 'একটা আয়তে একটাই চিহ্ন থাকতে পারে' : 'আয়তের ভেতরে একটা রঙিন চিহ্ন থাকতে হবে'); shake(); return; }
            hist.push(rects.slice());
            rects = rects.filter(function (X) { return !overlap(X, R) && X.k !== ins[0]; });
            R.k = ins[0]; rects.push(R);
            var cl = clues[R.k];
            msg(fits(cl, R) ? '' : (cl.s && cl.s !== R.h * R.w ? 'এই চিহ্নের আয়ত ' + bn(cl.s) + ' ঘরের হতে হবে' : 'আকৃতি মেলেনি — চিহ্নটা ' + LBL[cl.t] + ' চায়'));
            draw(); check();
        }
        function check() {
            var cov = 0; rects.forEach(function (R) { cov += R.h * R.w; });
            var bad = rects.filter(function (R) { return !fits(clues[R.k], R); }).length;
            if (cov === L && rects.length === clues.length && !bad) { submit(rects.map(function (R) { return [R.r, R.c, R.h, R.w]; })); return; }
            if (rects.length === clues.length) {
                if (bad) { msg(bn(bad) + 'টা আয়ত চিহ্নের সাথে মেলেনি (লাল দাগ) — ঠিক করুন'); shake(); return; }
                var empty = []; for (var i = 0; i < L; i++) { var r = Math.floor(i / n), c = i % n; if (!rects.some(function (R) { return r >= R.r && r < R.r + R.h && c >= R.c && c < R.c + R.w; })) empty.push(g.children[i]); }
                msg('সব চিহ্নে আয়ত হয়েছে, কিন্তু ' + bn(empty.length) + 'টা ঘর ফাঁকা — পুরো গ্রিড ঢাকতে হবে (হলুদ ঘরগুলো দেখুন)'); flash(empty); shake();
            }
        }
        board.onpointerdown = function (e) { if (done) return; e.preventDefault(); a = cellAt(e); prev = box(a, a); try { board.setPointerCapture(e.pointerId); } catch (er) { } draw(); };
        var praf = 0;
        board.onpointermove = function (e) { if (a < 0 || done) return; var nb = box(a, cellAt(e)); if (prev && nb.r === prev.r && nb.c === prev.c && nb.h === prev.h && nb.w === prev.w) return; prev = nb; if (!praf) praf = requestAnimationFrame(function () { praf = 0; draw(); }); };
        board.onpointerup = function (e) {
            if (a < 0 || done) return;
            var b = cellAt(e), R = box(a, b); a = -1; prev = null;
            if (R.h * R.w === 1) {   // এক ঘরে চাপ: সেখানে আয়ত থাকলে মুছে ফেলা
                var hit = rects.filter(function (X) { return overlap(X, R); });
                if (hit.length) { hist.push(rects.slice()); rects = rects.filter(function (X) { return hit.indexOf(X) < 0; }); draw(); msg(''); return; }
                if (inside(R).length !== 1) { draw(); return; }
            }
            place(R);
        };
        board.onpointercancel = function () { a = -1; prev = null; draw(); };
        draw();
        return {
            undo: function () { var h = hist.pop(); if (h) { rects = h; draw(); msg(''); } }, reset: function () { if (rects.length) hist.push(rects.slice()); rects = []; draw(); msg(''); },
            state: function () { return rects.map(function (R) { return [R.r, R.c, R.h, R.w]; }); },
            hint: function (h) {
                hist.push(rects.slice());
                if (h.remove) { rects = rects.filter(function (R) { return !(R.r === h.remove[0] && R.c === h.remove[1] && R.h === h.remove[2] && R.w === h.remove[3]); }); draw(); return 'এই জায়গার আয়তটা ভুল ছিল — সরিয়ে দেওয়া হলো'; }
                var R = { r: h.add[0], c: h.add[1], h: h.add[2], w: h.add[3] }, ins = inside(R);
                rects = rects.filter(function (X) { return !overlap(X, R) && X.k !== ins[0]; }); R.k = ins[0]; rects.push(R); draw();
                var el2 = layer.lastChild; if (el2 && el2.animate) el2.animate([{ transform: 'scale(1.06)', boxShadow: '0 0 0 6px rgba(250,204,21,.7)' }, { transform: 'scale(1)', boxShadow: '0 0 0 0 rgba(250,204,21,0)' }], { duration: 1200 });
                check(); return 'একটা আয়ত বসিয়ে দেওয়া হলো';
            }
        };
    }

    // ============================================================ ফলাফল
    var lastRes = null;
    function showResult(d) {
        lastRes = d;
        document.getElementById('gpResTime').textContent = fmt(d.seconds);
        var pts = document.getElementById('gpResPts'), grid = document.getElementById('gpResGrid'), note = document.getElementById('gpResNote');
        var cell = function (b, s) { return '<div><b>' + b + '</b><small>' + s + '</small></div>'; };
        pts.innerHTML = (d.counted ? '<span class="gp-rp">+' + bn(d.points) + ' পয়েন্ট</span>' : '')
            + '<div style="margin-top:8px;font-size:13px;font-weight:700;color:rgba(255,255,255,.92);">' + (d.hints ? '💡 ' + bn(d.hints) + 'টা হিন্ট নিয়েছেন (পয়েন্ট −' + bn(Math.min(90, d.hints * 20)) + '%)' : '💪 হিন্ট ছাড়াই!') + '</div>';
        var g = '', nt = '';
        if (d.loggedIn) {
            g += cell(bn(d.todayPts || 0), 'আজকের মোট পয়েন্ট');
            g += cell(d.rank ? '#' + bn(d.rank) + '<small style="font-size:12px;color:#64748B;"> / ' + bn(d.players) + '</small>' : '—', 'আজকের র‍্যাঙ্ক (সবাই)');
            if (d.groupRank) g += cell('#' + bn(d.groupRank), (d.group === 'teacher' ? 'টিচারদের' : 'শিক্ষার্থীদের') + ' মধ্যে');
            g += cell('🔥 ' + bn(d.streak || 0), 'দিন টানা খেলা');
            if (d.brain) g += cell('🧠 ' + (d.brain.pct !== null ? bn(d.brain.pct) + '%' : '—'), d.brain.pct !== null ? 'ব্রেইন স্কোর' : 'ব্রেইন স্কোর — আর ' + bn(d.brain.need) + 'টা খেলা');
            g += cell('🏅 ' + fmt(d.best), d.newBest ? 'নতুন সেরা সময়! 🎊' : 'আপনার সেরা সময়');
            if (d.isAdmin) nt = 'অ্যাডমিনের খেলা লিডারবোর্ডে গণ্য হয় না।';
            else if (!d.counted) nt = 'খুব কম সময়ে শেষ হয়েছে — এই খেলাটা লিডারবোর্ডে গণ্য হয়নি।';
        } else {
            nt = 'লগইন করা থাকলে এই খেলায় <b>+' + bn(d.points) + ' পয়েন্ট</b> পেতেন আর লিডারবোর্ডে নাম উঠত!';
        }
        grid.innerHTML = g; grid.style.display = g ? '' : 'none';
        note.innerHTML = nt ? '<p class="gp-rnote">' + nt + '</p>' : '';
        document.getElementById('gpRes').classList.add('show');
        confetti();
    }
    document.getElementById('gpAgain').onclick = function () { document.getElementById('gpRes').classList.remove('show'); load(); };
    document.getElementById('gpResX').onclick = function () { document.getElementById('gpRes').classList.remove('show'); };
    document.getElementById('gpShare').onclick = function () {
        if (!lastRes) return;
        var txt = 'আমি মাষ্টার মশাই-এ ' + GNAME + ' (' + LVBN + ') ' + fmt(lastRes.seconds) + ' সময়ে সমাধান করেছি! 🧠 তুমি পারবে?';
        if (navigator.share) { navigator.share({ title: GNAME, text: txt, url: SHARE_URL }).catch(function () { }); return; }
        var all = txt + ' ' + SHARE_URL, btn = this, ok = function () { btn.innerHTML = '<i class="fas fa-check"></i> কপি হয়েছে'; };
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(all).then(ok); else { var t = document.createElement('textarea'); t.value = all; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); ok(); } catch (e) { } t.remove(); }
    };

    function confetti() {
        var cv = document.getElementById('gpConfetti'), ctx = cv.getContext('2d'); cv.width = innerWidth; cv.height = innerHeight;
        var cols = ['#F97316', '#FACC15', '#22C55E', '#3B82F6', '#EC4899', '#8B5CF6'], ps = [];
        for (var i = 0; i < 140; i++) ps.push({ x: innerWidth / 2 + (Math.random() - .5) * 200, y: innerHeight * .35, vx: (Math.random() - .5) * 14, vy: -Math.random() * 14 - 4, s: 5 + Math.random() * 6, c: cols[i % cols.length], r: Math.random() * 6, vr: (Math.random() - .5) * .4 });
        var f = 0;
        (function step() {
            ctx.clearRect(0, 0, cv.width, cv.height);
            ps.forEach(function (p) { p.vy += .35; p.vx *= .99; p.x += p.vx; p.y += p.vy; p.r += p.vr; ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.r); ctx.fillStyle = p.c; ctx.fillRect(-p.s / 2, -p.s / 3, p.s, p.s * .66); ctx.restore(); });
            if (++f < 150) requestAnimationFrame(step); else ctx.clearRect(0, 0, cv.width, cv.height);
        })();
    }

    load();
})();
</script>
<?php section_end(); ?>
