<?php
/** Admin → Telegram: অ্যাডমিনের মোবাইলে নোটিফিকেশন (বটের token, কোন চ্যাটে যাবে, কোন ঘটনাগুলো) — শুধু Super Admin */
$ViewData['Title'] = 'Telegram নোটিফিকেশন';
$S = $ViewBag->S; $bot = $ViewBag->Bot; $has = $S['token'] !== '';
$on = $has && $S['chats'] && empty($bot['error']);
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 100px; min-height: calc(100vh - 80px); }
    .tg { max-width: 880px; }
    .tg h2 { color: #0F172A; margin: 0; font-size: 26px; font-weight: 800; }
    .tg .sub { color: #64748B; margin: 6px 0 18px; font-size: 14.5px; line-height: 1.6; }
    .tg-st { display: flex; align-items: center; gap: 14px; border-radius: 16px; padding: 16px 20px; margin-bottom: 18px; border: 1px solid #A7F3D0; background: #ECFDF5; }
    .tg-st.off { border-color: #FDE68A; background: #FFFBEB; } .tg-st.bad { border-color: #FECACA; background: #FEF2F2; }
    .tg-st > i { font-size: 30px; color: #229ED9; }
    .tg-st b { display: block; font-size: 16.5px; color: #0F172A; } .tg-st small { color: #475569; font-size: 13.5px; }
    .tg-card { background: #fff; border: 1px solid #E8EEF4; border-radius: 18px; padding: 22px 24px; margin-bottom: 18px; box-shadow: 0 4px 16px rgba(15,23,42,.04); }
    .tg-card h3 { margin: 0 0 14px; font-size: 17px; font-weight: 800; color: #0F172A; display: flex; gap: 10px; align-items: center; }
    .tg-card h3 .n { width: 28px; height: 28px; border-radius: 50%; background: #E0F2FE; color: #0369A1; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
    .tg-f input[type=password], .tg-f input[type=text] { width: 100%; height: 46px; border: 1.5px solid #DCE5EE; border-radius: 12px; padding: 0 14px; font-size: 15px; font-family: ui-monospace, monospace; }
    .tg-f .h { font-size: 12.5px; color: #64748B; margin-top: 6px; line-height: 1.55; }
    .tg-a { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 12px; }
    .tg-btn { height: 44px; padding: 0 18px; border-radius: 11px; border: none; background: #229ED9; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
    .tg-btn.l { background: #F1F5F9; color: #334155; } .tg-btn.r { background: #FEE2E2; color: #B91C1C; } .tg-btn.d { background: #08224A; }
    .tg-msg { font-size: 14px; font-weight: 600; } .tg-msg.ok { color: #047857; } .tg-msg.err { color: #B91C1C; }
    .tg-chat { display: flex; align-items: center; gap: 12px; border: 1px solid #E5ECF3; border-radius: 12px; padding: 10px 14px; margin-bottom: 8px; }
    .tg-chat i.ic { width: 36px; height: 36px; border-radius: 50%; background: #E0F2FE; color: #229ED9; display: flex; align-items: center; justify-content: center; }
    .tg-chat b { flex: 1; color: #0F172A; } .tg-chat small { color: #94A3B8; font-weight: 500; display: block; font-size: 12px; }
    .tg-ev { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-top: 1px solid #F1F5F9; cursor: pointer; }
    .tg-ev:first-of-type { border-top: none; }
    .tg-ev input { width: 20px; height: 20px; margin-top: 2px; accent-color: #229ED9; }
    .tg-ev b { display: block; color: #0F172A; } .tg-ev small { color: #64748B; }
    .tg ol { margin: 0; padding-left: 20px; color: #334155; line-height: 1.85; font-size: 14.5px; }
    .tg code { background: #0F172A; color: #A5F3FC; padding: 2px 8px; border-radius: 6px; font-size: 13px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 90px; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content tg">
    <?= csrf_field() ?>
    <h2><i class="fab fa-telegram" style="color:#229ED9;"></i> Telegram নোটিফিকেশন</h2>
    <p class="sub">কেউ পেমেন্ট করলে, Verification ফর্ম জমা দিলে বা Withdraw আবেদন করলে — সাথে সাথে আপনার মোবাইলের Telegram এ মেসেজ আসবে, লিংকে চাপলেই অ্যাডমিন প্যানেলের সেই পেজ খুলবে।</p>

    <?php if ($has && !empty($bot['error'])): ?>
        <div class="tg-st bad"><i class="fas fa-triangle-exclamation" style="color:#DC2626;"></i><div><b>বট কাজ করছে না</b><small><?= e($bot['error']) ?></small></div></div>
    <?php elseif ($on): ?>
        <div class="tg-st"><i class="fab fa-telegram"></i><div><b>চালু আছে — @<?= e($bot['user'] ?? '') ?></b><small><?= count($S['chats']) ?>টা চ্যাটে নোটিফিকেশন যাচ্ছে · token: <?= e($ViewBag->Hint) ?></small></div></div>
    <?php else: ?>
        <div class="tg-st off"><i class="fab fa-telegram"></i><div><b>এখনো চালু হয়নি</b><small><?= $has ? 'বট যুক্ত আছে, কিন্তু কোনো চ্যাট নেই — মোবাইলে বটে START চেপে নিচে "চ্যাট খুঁজুন" চাপুন।' : 'নিচে বটের token দিন।' ?></small></div></div>
    <?php endif; ?>

    <form class="tg-card" id="tgTok" autocomplete="off">
        <h3><span class="n">১</span> বটের token</h3>
        <div class="tg-f"><input type="password" name="token" placeholder="<?= $has ? 'নতুন token দিতে চাইলে লিখুন — সেভ করা আছে: ' . e($ViewBag->Hint) : 'BotFather এর দেওয়া token (যেমন 123456789:AAH...)' ?>" autocomplete="new-password" spellcheck="false" />
            <div class="h"><i class="fas fa-lock"></i> token শুধু এখানে দিন — কোডে, GitHub এ বা চ্যাটে না।</div></div>
        <div class="tg-a"><button type="submit" class="tg-btn"><i class="fas fa-plug"></i> সংযোগ করুন</button><span class="tg-msg" data-m></span></div>
    </form>

    <?php if ($has): ?>
    <div class="tg-card">
        <h3><span class="n">২</span> কোথায় যাবে</h3>
        <?php if (!$S['chats']): ?><p style="color:#64748B;margin:0 0 6px;">এখনো কোনো চ্যাট নেই। মোবাইলে <?= !empty($bot['user']) ? '<a href="https://t.me/' . e($bot['user']) . '" target="_blank" rel="noopener"><b>t.me/' . e($bot['user']) . '</b></a>' : 'বটে' ?> ঢুকে <b>START</b> চাপুন (বা কিছু লিখে পাঠান), তারপর নিচের বাটন চাপুন।</p><?php endif; ?>
        <?php foreach ($S['chats'] as $c): ?>
            <div class="tg-chat"><i class="fas <?= in_array($c['type'] ?? '', ['group', 'supergroup'], true) ? 'fa-users' : 'fa-user' ?> ic"></i><b><?= e($c['name']) ?><small><?= in_array($c['type'] ?? '', ['group', 'supergroup'], true) ? 'Telegram গ্রুপ' : 'ব্যক্তিগত চ্যাট' ?> · <?= e((string)$c['id']) ?></small></b>
                <button type="button" class="tg-btn r" style="height:34px;padding:0 12px;font-size:12.5px;" data-rm="<?= e((string)$c['id']) ?>"><i class="fas fa-xmark"></i> সরান</button></div>
        <?php endforeach; ?>
        <div class="tg-a">
            <button type="button" class="tg-btn l" id="tgFind"><i class="fas fa-magnifying-glass"></i> চ্যাট খুঁজুন</button>
            <?php if ($S['chats']): ?><button type="button" class="tg-btn d" id="tgTest"><i class="fas fa-paper-plane"></i> পরীক্ষামূলক মেসেজ পাঠান</button><?php endif; ?>
            <span class="tg-msg" id="tgMsg2"></span>
        </div>
        <p style="font-size:12.5px;color:#64748B;margin:12px 0 0;"><i class="fas fa-circle-info"></i> কয়েকজন অ্যাডমিন একসাথে পেতে চাইলে: প্রত্যেকে বটে START চাপুন, অথবা একটা Telegram গ্রুপ বানিয়ে বটকে গ্রুপে যোগ করে গ্রুপে কিছু লিখুন — তারপর "চ্যাট খুঁজুন"।</p>
    </div>

    <form class="tg-card" id="tgEv">
        <h3><span class="n">৩</span> কোন কোন খবর আসবে</h3>
        <?php foreach (TG_EVENTS as $k => [$lbl, $hint]): ?>
            <label class="tg-ev"><input type="checkbox" name="on[]" value="<?= $k ?>"<?= in_array($k, $S['off'], true) ? '' : ' checked' ?> /><span><b><?= e($lbl) ?></b><small><?= e($hint) ?></small></span></label>
        <?php endforeach; ?>
        <div class="tg-a"><button type="submit" class="tg-btn d"><i class="fas fa-floppy-disk"></i> সেভ করুন</button><button type="button" class="tg-btn r" id="tgOff"><i class="fas fa-power-off"></i> পুরোপুরি বন্ধ করুন</button><span class="tg-msg" data-m></span></div>
    </form>
    <?php endif; ?>

    <div class="tg-card">
        <h3><i class="fas fa-circle-question" style="color:#229ED9;"></i> বট কীভাবে বানাবেন</h3>
        <ol>
            <li>Telegram এ <b>@BotFather</b> (নীল টিক) খুঁজে <b>START</b> চাপুন।</li>
            <li><code>/newbot</code> লিখে পাঠান → নাম দিন → username দিন (শেষে <b>bot</b>)।</li>
            <li>BotFather যে <b>token</b> দেবে সেটা উপরে বসিয়ে "সংযোগ করুন" চাপুন।</li>
            <li>নিজের বটে ঢুকে <b>START</b> চাপুন → এখানে "চ্যাট খুঁজুন"।</li>
            <li>token কোথাও ফাঁস হয়ে গেলে BotFather এ <code>/revoke</code> দিয়ে নতুন token নিয়ে এখানে বসান।</li>
        </ol>
    </div>
</div>
<script>
(function () {
    function tok() { return document.querySelector('input[name="__RequestVerificationToken"]').value; }
    async function post(url, fd, m) {
        fd = fd || new FormData(); fd.append('__RequestVerificationToken', tok());
        if (m) { m.className = 'tg-msg'; m.textContent = 'অপেক্ষা করুন...'; }
        var d; try { d = await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); } catch (e) { d = { success: false, message: 'সার্ভারে সমস্যা' }; }
        if (m) { m.className = 'tg-msg ' + (d.success ? 'ok' : 'err'); m.textContent = d.message || ''; }
        return d;
    }
    var f = document.getElementById('tgTok');
    f.addEventListener('submit', async function (e) { e.preventDefault(); var d = await post('/Admin/SaveTelegram', new FormData(f), f.querySelector('[data-m]')); if (d.success) setTimeout(function () { location.reload(); }, 1600); });
    var m2 = document.getElementById('tgMsg2');
    var fd = document.getElementById('tgFind'); if (fd) fd.addEventListener('click', async function () { var d = await post('/Admin/TelegramFindChats', null, m2); if (d.success && /যোগ হয়েছে/.test(d.message)) setTimeout(function () { location.reload(); }, 1400); });
    var ts = document.getElementById('tgTest'); if (ts) ts.addEventListener('click', function () { post('/Admin/TelegramTest', null, m2); });
    document.querySelectorAll('[data-rm]').forEach(function (b) { b.addEventListener('click', async function () { if (!confirm('এই চ্যাটে আর নোটিফিকেশন যাবে না। সরাবেন?')) return; var x = new FormData(); x.append('id', b.dataset.rm); var d = await post('/Admin/TelegramRemoveChat', x, m2); if (d.success) location.reload(); }); });
    var ev = document.getElementById('tgEv'); if (ev) ev.addEventListener('submit', function (e) { e.preventDefault(); post('/Admin/SaveTelegramEvents', new FormData(ev), ev.querySelector('[data-m]')); });
    var off = document.getElementById('tgOff'); if (off) off.addEventListener('click', async function () { if (!confirm('Telegram নোটিফিকেশন পুরোপুরি বন্ধ করবেন? (token মুছে যাবে)')) return; var x = new FormData(); x.append('clear', '1'); var d = await post('/Admin/SaveTelegramEvents', x, ev.querySelector('[data-m]')); if (d.success) setTimeout(function () { location.reload(); }, 900); });
})();
</script>
