<script>
(function () {
    var els = document.querySelectorAll('[data-gm-reset]'); if (!els.length) return;
    var bn = function (s) { return String(s).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }); };
    var end = Date.now() + (+els[0].getAttribute('data-gm-reset')) * 1000;
    function tick() {
        var s = Math.max(0, Math.round((end - Date.now()) / 1000));
        var txt = bn(Math.floor(s / 3600)) + ' ঘণ্টা ' + bn(Math.floor(s % 3600 / 60)) + ' মিনিট';
        els.forEach(function (e) { e.textContent = txt; });
        if (s === 0) { setTimeout(function () { location.reload(); }, 3000); return; }
        setTimeout(tick, 30000);
    }
    tick();
})();
</script>
