<?php
/**
 * অ্যাডমিন তালিকায় খোঁজার বার — লিখতে লিখতে তালিকা ছোট হয় (নাম, ইউজারনেম, ইমেইল ...)
 * @var string $items  যেগুলো খুঁজবে তাদের CSS selector (প্রতিটায় data-s থাকলে সেটায়, না থাকলে লেখায় খোঁজে)
 * @var string $ph     placeholder   @var string $sid  আলাদা id (এক পেজে একাধিক হলে)
 */
$sid = $sid ?? 'als' . substr(md5((string)$items), 0, 6);
?>
<div class="als" style="display:flex;align-items:center;gap:10px;border:1.5px solid #CFE3EA;border-radius:12px;padding:0 14px;background:#fff;margin:<?= e($margin ?? '0 0 14px') ?>;">
    <i class="fas fa-magnifying-glass" style="color:#94A3B8;"></i>
    <input type="search" id="<?= e($sid) ?>" placeholder="<?= e($ph ?? 'নাম, ইউজারনেম বা ইমেইল দিয়ে খুঁজুন') ?>" autocomplete="off"
           style="border:none;outline:none;height:44px;flex:1;min-width:0;font-size:14.5px;font-family:inherit;background:transparent;" />
    <small id="<?= e($sid) ?>N" style="color:#64748B;font-weight:700;white-space:nowrap;"></small>
</div>
<script>
(function () {
    var q = document.getElementById(<?= js($sid) ?>), n = document.getElementById(<?= js($sid . 'N') ?>), sel = <?= js((string)$items) ?>;
    if (!q) return;
    function run() {
        var ws = q.value.trim().toLowerCase().replace(/^@/, '').split(/\s+/).filter(Boolean), c = 0, all = document.querySelectorAll(sel);
        all.forEach(function (el) {
            var s = (el.getAttribute('data-s') || el.textContent).toLowerCase();
            var ok = ws.every(function (w) { return s.indexOf(w) !== -1; });
            el.style.display = ok ? '' : 'none'; if (ok) c++;
        });
        n.textContent = ws.length ? (c ? c + ' টি' : 'নেই') : '';
        n.style.color = ws.length && !c ? '#DC2626' : '#64748B';
    }
    q.addEventListener('input', run);
})();
</script>
