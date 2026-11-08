<?php
/** Reference / Agency — দুই পেজের উপরের ট্যাব */
$cur = (string)($GLOBALS['__route']['action'] ?? '');
?>
<style>
    .rt-tabs { display: flex; gap: 6px; flex-wrap: wrap; background: #fff; padding: 6px; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,.05); margin-bottom: 18px; width: fit-content; max-width: 100%; }
    .rt-tabs a { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 14px; color: #334155; text-decoration: none; }
    .rt-tabs a.on { background: #08224A; color: #fff; }
    .rt-tabs a:not(.on):hover { background: #F1F5F9; }
</style>
<nav class="rt-tabs">
    <a href="/Admin/References" class="<?= $cur === 'References' ? 'on' : '' ?>"><i class="fas fa-bullhorn"></i> রেফারেন্স রিপোর্ট</a>
    <a href="/Admin/Partners" class="<?= in_array($cur, ['Partners', 'Partner'], true) ? 'on' : '' ?>"><i class="fas fa-handshake"></i> এজেন্সির লিংক ও কোড</a>
</nav>
