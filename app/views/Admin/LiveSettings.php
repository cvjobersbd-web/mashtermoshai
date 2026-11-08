<?php
/** Admin → Live Class: BigBlueButton সার্ভার (live.mashtermoshai.com) এর ঠিকানা আর secret — শুধু Super Admin */
$ViewData['Title'] = 'Live Class Server';
$has = (bool)$ViewBag->HasSecret;
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 100px; min-height: calc(100vh - 80px); }
    .lv { max-width: 860px; }
    .lv h2 { color: #0F172A; margin: 0; font-size: 26px; font-weight: 800; }
    .lv .sub { color: #64748B; margin: 6px 0 18px; font-size: 14.5px; line-height: 1.6; }
    .lv-st { display: flex; align-items: center; gap: 14px; border-radius: 16px; padding: 16px 20px; margin-bottom: 18px; border: 1px solid #A7F3D0; background: #ECFDF5; }
    .lv-st.off { border-color: #FDE68A; background: #FFFBEB; }
    .lv-dot { width: 14px; height: 14px; border-radius: 50%; background: #10B981; box-shadow: 0 0 0 5px rgba(16,185,129,.18); flex-shrink: 0; }
    .lv-st.off .lv-dot { background: #F59E0B; box-shadow: 0 0 0 5px rgba(245,158,11,.2); }
    .lv-st b { display: block; font-size: 16.5px; color: #0F172A; } .lv-st small { color: #475569; font-size: 13.5px; }
    .lv-card { background: #fff; border: 1px solid #E8EEF4; border-radius: 18px; padding: 22px 24px; margin-bottom: 18px; box-shadow: 0 4px 16px rgba(15,23,42,.04); }
    .lv-card h3 { margin: 0 0 16px; font-size: 17px; font-weight: 800; color: #0F172A; }
    .lv-f { margin-bottom: 16px; }
    .lv-f label { display: block; font-size: 13.5px; font-weight: 700; color: #334155; margin-bottom: 7px; }
    .lv-f input { width: 100%; height: 46px; border: 1.5px solid #DCE5EE; border-radius: 12px; padding: 0 14px; font-size: 15px; font-family: ui-monospace, monospace; color: #0F172A; }
    .lv-f input:focus { outline: none; border-color: #0EA5E9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
    .lv-f .h { font-size: 12.5px; color: #64748B; margin-top: 6px; line-height: 1.55; } .lv-f .saved { color: #047857; font-weight: 700; }
    .lv-a { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .lv-btn { height: 44px; padding: 0 18px; border-radius: 11px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
    .lv-btn.l { background: #F1F5F9; color: #334155; } .lv-btn.r { background: #FEE2E2; color: #B91C1C; }
    .lv-msg { font-size: 14px; font-weight: 600; } .lv-msg.ok { color: #047857; } .lv-msg.err { color: #B91C1C; }
    .lv ol { margin: 0; padding-left: 20px; color: #334155; line-height: 1.8; font-size: 14.5px; }
    .lv code { background: #0F172A; color: #A5F3FC; padding: 2px 8px; border-radius: 6px; font-size: 13px; }
    .lv-warn { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; line-height: 1.6; margin-top: 14px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 90px; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content lv">
    <?= csrf_field() ?>
    <h2><i class="fas fa-tower-broadcast" style="color:#EF4444;"></i> Live Class সার্ভার</h2>
    <p class="sub">কোর্সের লাইভ ক্লাস, রেকর্ড ক্লাস আর প্রিমিয়াম টিচারদের অনলাইন ক্লাস — সব চলে আপনার নিজের BigBlueButton সার্ভারে। এখানে সার্ভারের ঠিকানা আর secret দিলেই চালু হয়ে যাবে।</p>

    <div class="lv-st<?= $has ? '' : ' off' ?>"><span class="lv-dot"></span><div><b><?= $has ? 'সার্ভার যুক্ত আছে' : 'এখনো চালু হয়নি' ?></b><small><?= $has ? 'Secret সেভ করা আছে (' . e($ViewBag->Hint) . ') — নিচে "সংযোগ পরীক্ষা" চাপলে বোঝা যাবে সব ঠিক আছে কিনা।' : 'Secret দেওয়া হয়নি — লাইভ / রেকর্ড ক্লাস শুরু করা যাবে না।' ?></small></div></div>

    <form class="lv-card" id="lvForm" autocomplete="off">
        <h3>সার্ভার সেটিং</h3>
        <div class="lv-f"><label for="lvUrl">সার্ভারের ঠিকানা (API URL)</label><input type="text" id="lvUrl" name="url" value="<?= e($ViewBag->Url) ?>" spellcheck="false" />
            <div class="h">সাধারণত <b>https://live.mashtermoshai.com/bigbluebutton/</b></div></div>
        <div class="lv-f"><label for="lvSec">Shared Secret</label><input type="password" id="lvSec" name="secret" value="" placeholder="<?= $has ? 'নতুন secret দিতে চাইলে লিখুন — খালি রাখলে আগেরটাই থাকবে' : 'সার্ভার থেকে secret কপি করে এখানে দিন' ?>" spellcheck="false" autocomplete="new-password" />
            <div class="h"><?= $has ? '<span class="saved"><i class="fas fa-lock"></i> সেভ করা আছে: ' . e($ViewBag->Hint) . '</span> · ' : '' ?>Secret কখনো কাউকে দেবেন না, GitHub / কোডে রাখবেন না — শুধু এখানে দিন।</div></div>
        <div class="lv-a">
            <button type="submit" class="lv-btn"><i class="fas fa-floppy-disk"></i> সেভ করুন</button>
            <button type="button" class="lv-btn l" id="lvTest"><i class="fas fa-plug-circle-check"></i> সংযোগ পরীক্ষা</button>
            <?php if ($has): ?><button type="button" class="lv-btn r" id="lvClear"><i class="fas fa-trash"></i> Secret মুছুন</button><?php endif; ?>
            <span class="lv-msg" id="lvMsg"></span>
        </div>
    </form>

    <div class="lv-card">
        <h3>Secret কোথায় পাবেন</h3>
        <ol>
            <li>VPS এ SSH দিয়ে ঢুকুন।</li>
            <li>এই কমান্ড চালান: <code>sudo bbb-conf --secret</code></li>
            <li>যে <b>URL</b> আর <b>Secret</b> দেখাবে, সেগুলো উপরের ঘরে বসিয়ে <b>সেভ করুন</b> → <b>সংযোগ পরীক্ষা</b> চাপুন।</li>
        </ol>
        <div class="lv-warn"><i class="fas fa-shield-halved"></i> secret টা পাবলিক GitHub রিপোতে, config.php তে বা কোনো চ্যাটে দেবেন না — কেউ পেলে আপনার সার্ভারে নিজের মতো মিটিং খুলতে পারবে। ফাঁস হয়েছে মনে হলে সার্ভারে <code>sudo bbb-conf --setsecret নতুন_secret</code> চালিয়ে বদলে এখানে নতুনটা দিন।</div>
    </div>
</div>
<script>
(function () {
    var f = document.getElementById('lvForm'), m = document.getElementById('lvMsg');
    async function post(url, extra) {
        var fd = new FormData(f); Object.keys(extra || {}).forEach(function (k) { fd.set(k, extra[k]); });
        fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        m.className = 'lv-msg'; m.textContent = 'অপেক্ষা করুন...';
        var d; try { d = await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); } catch (e) { d = { success: false, message: 'সার্ভারে সমস্যা' }; }
        m.className = 'lv-msg ' + (d.success ? 'ok' : 'err'); m.textContent = d.message || ''; return d;
    }
    f.addEventListener('submit', async function (e) { e.preventDefault(); var d = await post('/Admin/SaveLiveSettings'); if (d.success && f.elements.secret.value) setTimeout(function () { location.reload(); }, 1200); });
    document.getElementById('lvTest').addEventListener('click', function () { post('/Admin/TestLiveServer'); });
    var c = document.getElementById('lvClear');
    if (c) c.addEventListener('click', async function () { if (!confirm('Secret মুছে ফেললে লাইভ / রেকর্ড ক্লাস বন্ধ হয়ে যাবে। নিশ্চিত?')) return; var d = await post('/Admin/SaveLiveSettings', { clear: '1', secret: '' }); if (d.success) setTimeout(function () { location.reload(); }, 900); });
})();
</script>
