<?php
/**
 * টিউটর ফিল্টার প্যানেল — মোবাইলে নিচ থেকে ওঠে, কম্পিউটারে ডান পাশে খোলে।
 * দরকার: $f (tf_read), $counts (tf_counts), $areaCounts (tf_area_counts), $type, $total (এখনকার ফল)
 * যেকোনো বাটন [data-tf-open] চাপলে খোলে।
 */
$locs = tuition_options()['locations'] ?? [];
$subjects = tuition_options()['subjects'] ?? default_tuition_subjects();
$c = $counts;
$chk = fn(string $k, string $v) => in_array($v, $f[$k] ?? [], true) ? ' checked' : '';
// জেলা: যাদের টিউটর আছে আগে (বেশি থেকে কম), বাছাই করা গুলো সবসময় সামনে
$dists = array_keys($locs);
usort($dists, fn($a, $b) => [in_array($b, $f['dist'], true), $c['dist'][$b] ?? 0] <=> [in_array($a, $f['dist'], true), $c['dist'][$a] ?? 0]);
$subs = $subjects;
usort($subs, fn($a, $b) => [in_array($b, $f['sub'], true), $c['sub'][$b] ?? 0] <=> [in_array($a, $f['sub'], true), $c['sub'][$a] ?? 0]);
$unis = tf_unis();
$chip = function (string $name, string $val, string $label, int $n, bool $on, string $extra = '', bool $hidden = false) {
    return '<label class="tf-chip' . ($hidden ? ' tf-more' : '') . ($n === 0 && !$on ? ' zero' : '') . '"><input type="checkbox" name="' . e($name) . '[]" value="' . e($val) . '"' . ($on ? ' checked' : '') . ' />'
        . '<span>' . $label . $extra . ' <em data-c="' . e($name . '|' . $val) . '">' . bn_num($n) . '</em></span></label>';
};
$radio = function (string $name, string $val, string $label, bool $on) {
    return '<label class="tf-chip tf-r"><input type="radio" name="' . e($name) . '" value="' . e($val) . '"' . ($on ? ' checked' : '') . ' /><span>' . $label . '</span></label>';
};
?>
<style>
    .tf-ov { position: fixed; inset: 0; background: rgba(2, 6, 23, .5); z-index: 1990; opacity: 0; pointer-events: none; transition: opacity .25s; }
    .tf-ov.show { opacity: 1; pointer-events: auto; }
    .tf-panel { position: fixed; z-index: 1991; background: #fff; display: flex; flex-direction: column; box-shadow: 0 -10px 40px rgba(0,0,0,.2); transition: transform .3s cubic-bezier(.2,.8,.2,1);
        left: 0; right: 0; bottom: 0; height: 88vh; height: 88dvh; border-radius: 22px 22px 0 0; transform: translateY(105%); }
    .tf-panel.show { transform: none; }
    @media (min-width: 992px) { .tf-panel { left: auto; top: 0; width: 470px; height: 100vh; border-radius: 0; transform: translateX(105%); box-shadow: -10px 0 40px rgba(0,0,0,.18); } }
    .tf-h { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #EEF2F7; flex-shrink: 0; }
    .tf-h h3 { margin: 0; font-size: 20px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 10px; }
    .tf-h h3 i { color: #00B5E0; }
    .tf-x { width: 38px; height: 38px; border-radius: 50%; border: none; background: #F1F5F9; font-size: 18px; color: #334155; cursor: pointer; }
    .tf-grab { width: 44px; height: 5px; border-radius: 5px; background: #CBD5E1; margin: 8px auto 0; flex-shrink: 0; }
    @media (min-width: 992px) { .tf-grab { display: none; } }
    .tf-b { flex: 1; overflow-y: auto; padding: 4px 20px 20px; overscroll-behavior: contain; }
    .tf-sec { padding: 16px 0; border-bottom: 1px solid #F1F5F9; }
    .tf-sec:last-child { border-bottom: none; }
    .tf-sec h4 { margin: 0 0 10px; font-size: 15.5px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px; }
    .tf-sec h4 i { color: #00B5E0; width: 18px; text-align: center; }
    .tf-sec h4 small { font-weight: 600; color: #64748B; font-size: 12px; margin-left: auto; }
    .tf-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .tf-chip { margin: 0; cursor: pointer; }
    .tf-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .tf-chip span { display: inline-flex; align-items: center; gap: 6px; padding: 8px 13px; border-radius: 999px; background: #F1F8FC; border: 1.5px solid #DCEFF7; color: #1E293B; font-size: 13.5px; font-weight: 600; transition: .15s; line-height: 1.25; }
    .tf-chip span em { font-style: normal; font-size: 11.5px; font-weight: 700; color: #64748B; background: #fff; border-radius: 8px; padding: 0 6px; }
    .tf-chip span small { display: block; font-size: 11px; color: #64748B; font-weight: 600; }
    .tf-chip input:checked + span { background: #00B5E0; border-color: #00B5E0; color: #fff; }
    .tf-chip input:checked + span em { color: #0369A1; }
    .tf-chip input:checked + span small { color: #E0F7FF; }
    .tf-chip input:focus-visible + span { box-shadow: 0 0 0 3px rgba(0,181,224,.35); }
    .tf-chip.zero span { opacity: .45; }
    .tf-chip.tf-more { display: none; }
    .tf-sec.open .tf-chip.tf-more { display: inline-block; }
    .tf-moreb { border: none; background: none; color: #0369A1; font-weight: 700; font-size: 13px; padding: 6px 2px 0; cursor: pointer; }
    .tf-sec.open .tf-moreb .a { display: none; } .tf-moreb .b { display: none; } .tf-sec.open .tf-moreb .b { display: inline; }
    .tf-in { width: 100%; margin-top: 10px; border: 1.5px solid #DCE7F0; border-radius: 12px; padding: 10px 14px; font-size: 14px; background: #F8FBFF; }
    .tf-in:focus { outline: none; border-color: #00B5E0; background: #fff; }
    .tf-note { font-size: 12px; color: #64748B; margin-top: 8px; }
    .tf-sub { font-size: 13px; font-weight: 700; color: #475569; margin: 12px 0 8px; }
    .tf-f { display: flex; gap: 10px; padding: 12px 20px calc(12px + env(safe-area-inset-bottom)); border-top: 1px solid #EEF2F7; flex-shrink: 0; background: #fff; }
    .tf-f button { border: none; border-radius: 14px; font-weight: 800; font-size: 15px; padding: 14px 10px; cursor: pointer; }
    .tf-reset { flex: 0 0 34%; background: #F1F5F9; color: #334155; }
    .tf-go { flex: 1; background: #00B5E0; color: #fff; }
    .tf-go:disabled { background: #94A3B8; }
    body.tf-lock { overflow: hidden; }
</style>

<div class="tf-ov" id="tfOv"></div>
<aside class="tf-panel" id="tfPanel" role="dialog" aria-modal="true" aria-labelledby="tfTitle" aria-hidden="true">
    <div class="tf-grab"></div>
    <div class="tf-h"><h3 id="tfTitle"><i class="fas fa-sliders"></i> টিউটর ফিল্টার</h3><button type="button" class="tf-x" data-tf-close aria-label="বন্ধ করুন">✕</button></div>
    <form id="tfForm" class="tf-b" method="get" action="/Tutors/All">
        <?php if ($type !== 'all'): ?><input type="hidden" name="type" value="<?= e($type) ?>" /><?php endif; ?>

        <div class="tf-sec">
            <h4><i class="fas fa-bolt"></i> দ্রুত বাছাই</h4>
            <div class="tf-chips">
                <?= $radio('gender', '', 'সব টিচার', $f['gender'] === '') ?>
                <?= $radio('gender', 'male', '<i class="fas fa-person"></i> পুরুষ', $f['gender'] === 'male') ?>
                <?= $radio('gender', 'female', '<i class="fas fa-person-dress"></i> মহিলা', $f['gender'] === 'female') ?>
            </div>
            <div class="tf-chips" style="margin-top:8px;">
                <label class="tf-chip"><input type="checkbox" name="prem" value="1"<?= $f['prem'] ? ' checked' : '' ?> /><span>👑 শুধু Premium</span></label>
            </div>
        </div>

        <div class="tf-sec<?= count(array_filter($f['dist'], fn($d) => array_search($d, $dists, true) >= 12)) ? ' open' : '' ?>">
            <h4><i class="fas fa-location-dot"></i> জেলা <small>একাধিক বাছাই করা যায়</small></h4>
            <div class="tf-chips" id="tfDist">
                <?php foreach ($dists as $i => $d): echo $chip('dist', $d, e($d === 'Online' ? 'অনলাইন' : $d), (int)($c['dist'][$d] ?? 0), in_array($d, $f['dist'], true), '', $i >= 12); endforeach; ?>
            </div>
            <button type="button" class="tf-moreb" data-tf-more><span class="a">+ সব জেলা দেখুন</span><span class="b">− কম দেখান</span></button>
        </div>

        <div class="tf-sec" id="tfAreaSec">
            <h4><i class="fas fa-map-location-dot"></i> এলাকা</h4>
            <div class="tf-chips" id="tfAreas"><?php foreach ($areaCounts as $D => $list): foreach ($list as $ar => $n): echo $chip('area', $ar, e($ar), (int)$n, in_array($ar, $f['area'], true)); endforeach; endforeach; ?></div>
            <div class="tf-note" id="tfAreaHint"<?= $f['dist'] ? ' style="display:none"' : '' ?>>উপরে জেলা বাছাই করলে সেই জেলার এলাকা আসবে — বা নিচে নিজে লিখুন।</div>
            <input type="text" class="tf-in" name="aq" value="<?= e($f['aq']) ?>" maxlength="60" placeholder="এলাকার নাম লিখুন — যেমন মিরপুর, Mirpur 10, Uttara" autocomplete="off" />
        </div>

        <div class="tf-sec">
            <h4><i class="fas fa-graduation-cap"></i> ক্লাস</h4>
            <div class="tf-chips"><?php foreach (tf_class_groups() as $k => [$lb]) echo $chip('cls', $k, e($lb), (int)($c['cls'][$k] ?? 0), in_array($k, $f['cls'], true)); ?></div>
        </div>

        <div class="tf-sec<?= count(array_filter($f['sub'], fn($x) => array_search($x, $subs, true) >= 12)) ? ' open' : '' ?>">
            <h4><i class="fas fa-book"></i> বিষয় <small>যেকোনোটা পড়ালেই দেখাবে</small></h4>
            <div class="tf-chips"><?php foreach ($subs as $i => $x) { if ($x === 'All Subjects') continue; echo $chip('sub', $x, e($x), (int)($c['sub'][$x] ?? 0), in_array($x, $f['sub'], true), '', $i >= 13); } ?></div>
            <button type="button" class="tf-moreb" data-tf-more><span class="a">+ সব বিষয় দেখুন</span><span class="b">− কম দেখান</span></button>
        </div>

        <div class="tf-sec">
            <h4><i class="fas fa-school"></i> মিডিয়াম</h4>
            <div class="tf-chips"><?php foreach (TF_MEDIUMS as $k => $lb) echo $chip('med', $k, e($lb), (int)($c['med'][$k] ?? 0), in_array($k, $f['med'], true)); ?></div>
        </div>

        <div class="tf-sec">
            <h4><i class="fas fa-money-bill-wave"></i> মাসিক বেতন</h4>
            <div class="tf-chips"><?php foreach (TF_SALARY as $k => [$lb]) echo $chip('sal', $k, e($lb), (int)($c['sal'][$k] ?? 0), in_array($k, $f['sal'], true)); ?></div>
            <div class="tf-note">টিচারের "আশা করা বেতন" এই সীমার মধ্যে হলে দেখাবে।</div>
        </div>

        <div class="tf-sec">
            <h4><i class="far fa-clock"></i> পড়ানোর সময়</h4>
            <div class="tf-chips"><?php foreach (TF_SLOTS as $k => [$lb, $sub]) echo $chip('slot', $k, e($lb) . '<small>' . e($sub) . '</small>', (int)($c['slot'][$k] ?? 0), in_array($k, $f['slot'], true)); ?></div>
            <div class="tf-sub">যেসব দিনে লাগবে</div>
            <div class="tf-chips"><?php foreach (TF_DAYS as $k => $lb) echo $chip('day', $k, e($lb), (int)($c['day'][$k] ?? 0), in_array($k, $f['day'], true)); ?></div>
            <div class="tf-note">দিন বাছাই করলে — বাছাই করা সব দিনে যে টিচার ফ্রি, শুধু তারা।</div>
        </div>

        <?php $uniOrder = array_keys($unis); usort($uniOrder, fn($a, $b) => [in_array($b, $f['uni'], true), ($c['uni'][$b] ?? 0) > 0] <=> [in_array($a, $f['uni'], true), ($c['uni'][$a] ?? 0) > 0]); ?>
        <div class="tf-sec">
            <h4><i class="fas fa-building-columns"></i> বিশ্ববিদ্যালয় / প্রতিষ্ঠান</h4>
            <div class="tf-chips"><?php foreach ($uniOrder as $k) echo $chip('uni', $k, e($unis[$k][0]), (int)($c['uni'][$k] ?? 0), in_array($k, $f['uni'], true)); ?></div>
            <input type="text" class="tf-in" name="uq" value="<?= e($f['uq']) ?>" maxlength="60" placeholder="অন্য প্রতিষ্ঠানের নাম লিখুন — যেমন Dhaka College" autocomplete="off" />
        </div>

        <div class="tf-sec">
            <h4><i class="fas fa-user-tie"></i> টিচার সম্পর্কে</h4>
            <div class="tf-sub" style="margin-top:0;">অভিজ্ঞতা</div>
            <div class="tf-chips"><?= $radio('exp', '', 'যেকোনো', $f['exp'] === '') ?><?php foreach (TF_EXP as $k => $lb) echo $radio('exp', $k, e($lb), $f['exp'] === $k); ?></div>
            <div class="tf-sub">পড়ানোর ধরন</div>
            <div class="tf-chips"><?= $radio('mode', '', 'যেকোনো', $f['mode'] === '') ?><?= $radio('mode', 'home', '🏠 বাসায় গিয়ে', $f['mode'] === 'home') ?><?= $radio('mode', 'online', '💻 অনলাইনে', $f['mode'] === 'online') ?></div>
            <div class="tf-sub">টিচারের বিভাগ (HSC)</div>
            <div class="tf-chips"><?php foreach (TF_GROUPS as $k => $lb) echo $chip('grp', $k, e($lb), (int)($c['grp'][$k] ?? 0), in_array($k, $f['grp'], true)); ?></div>
            <div class="tf-sub">ধর্ম</div>
            <div class="tf-chips"><?php foreach (TF_RELIGION as $k => $lb) echo $chip('rel', $k, e($lb), (int)($c['rel'][$k] ?? 0), in_array($k, $f['rel'], true)); ?></div>
        </div>

        <div class="tf-sec">
            <h4><i class="fas fa-arrow-down-wide-short"></i> সাজানো</h4>
            <div class="tf-chips"><?php foreach (TF_SORTS as $k => $lb) echo $radio('sort', $k, e($lb), $f['sort'] === $k); ?></div>
        </div>
    </form>
    <div class="tf-f">
        <button type="button" class="tf-reset" id="tfReset"><i class="fas fa-rotate-left"></i> রিসেট</button>
        <button type="submit" form="tfForm" class="tf-go" id="tfGo"><span id="tfN"><?= bn_num($total) ?></span> জন টিচার দেখুন</button>
    </div>
</aside>

<script>
(function () {
    var ov = document.getElementById('tfOv'), pn = document.getElementById('tfPanel'), form = document.getElementById('tfForm'), go = document.getElementById('tfGo');
    var bn = function (s) { return String(s).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }); };
    var esc = function (s) { return String(s).replace(/[&<>"]/g, function (ch) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]; }); };
    function open() { ov.classList.add('show'); pn.classList.add('show'); pn.setAttribute('aria-hidden', 'false'); document.body.classList.add('tf-lock'); }
    function close() { ov.classList.remove('show'); pn.classList.remove('show'); pn.setAttribute('aria-hidden', 'true'); document.body.classList.remove('tf-lock'); }
    document.querySelectorAll('[data-tf-open]').forEach(function (b) { b.addEventListener('click', function (e) { e.preventDefault(); open(); }); });
    ov.addEventListener('click', close);
    pn.querySelectorAll('[data-tf-close]').forEach(function (b) { b.addEventListener('click', close); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && pn.classList.contains('show')) close(); });
    pn.querySelectorAll('[data-tf-more]').forEach(function (b) { b.addEventListener('click', function () { b.closest('.tf-sec').classList.toggle('open'); }); });

    function query() {
        var p = new URLSearchParams();
        new FormData(form).forEach(function (v, k) { if (String(v).trim() !== '' && !(k === 'sort' && v === 'rec')) p.append(k, v); });
        return p;
    }
    var timer = 0, seq = 0, areaSel = {};
    function refresh() {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var my = ++seq; go.disabled = true;
            fetch('/Tutors/Count?' + query().toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); }).then(function (d) {
                if (my !== seq || !d.success) return;
                document.getElementById('tfN').textContent = bn(d.n); go.disabled = false;
                // প্রতিটা অপশনের সংখ্যা
                Object.keys(d.counts).forEach(function (g) { Object.keys(d.counts[g]).forEach(function (v) {
                    var em = pn.querySelector('em[data-c="' + CSS.escape(g + '|' + v) + '"]'); if (!em) return;
                    em.textContent = bn(d.counts[g][v]); var lab = em.closest('.tf-chip'); lab.classList.toggle('zero', d.counts[g][v] === 0 && !lab.querySelector('input').checked);
                }); });
                // বাছাই করা জেলার এলাকা
                pn.querySelectorAll('#tfAreas input:checked').forEach(function (i) { areaSel[i.value] = 1; });
                pn.querySelectorAll('#tfAreas input:not(:checked)').forEach(function (i) { delete areaSel[i.value]; });
                var h = '';
                Object.keys(d.areas).forEach(function (D) { Object.keys(d.areas[D]).forEach(function (a) {
                    var n = d.areas[D][a], on = !!areaSel[a];
                    h += '<label class="tf-chip' + (n === 0 && !on ? ' zero' : '') + '"><input type="checkbox" name="area[]" value="' + esc(a) + '"' + (on ? ' checked' : '') + ' /><span>' + esc(a) + ' <em>' + bn(n) + '</em></span></label>';
                }); });
                document.getElementById('tfAreas').innerHTML = h;
                document.getElementById('tfAreaHint').style.display = Object.keys(d.areas).length ? 'none' : '';
            }).catch(function () { go.disabled = false; });
        }, 220);
    }
    form.addEventListener('change', refresh);
    form.querySelectorAll('input[type=text]').forEach(function (i) { i.addEventListener('input', refresh); });
    form.addEventListener('submit', function (e) { e.preventDefault(); var q = query().toString(); location.href = form.getAttribute('action') + (q ? '?' + q : ''); });
    document.getElementById('tfReset').addEventListener('click', function () {
        form.querySelectorAll('input[type=checkbox]').forEach(function (i) { i.checked = false; });
        form.querySelectorAll('input[type=text]').forEach(function (i) { i.value = ''; });
        form.querySelectorAll('input[type=radio]').forEach(function (i) { i.checked = i.value === '' || (i.name === 'sort' && i.value === 'rec'); });
        areaSel = {}; refresh();
    });
})();
</script>
