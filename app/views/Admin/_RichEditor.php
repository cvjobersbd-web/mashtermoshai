<?php
/**
 * ছোট লেখা-এডিটর (অ্যাডমিন) — বোল্ড, ইটালিক, আন্ডারলাইন, লেখার সাইজ, রঙ, হাইলাইট, নতুন লাইন।
 * সার্ভারে সেভের সময় sanitize_rich_html() দিয়ে পরিষ্কার হয়।
 * @var string $id      contenteditable এর id (এটার innerHTML পাঠাতে হবে)
 * @var string $html    এখনকার (পরিষ্কার) HTML
 * @var string $box     এডিটরের লেখার স্টাইল (যেখানে দেখাবে তার মতো)
 * @var array  $colors  রঙের সোয়াচ
 * @var bool   $sizes   সাইজ বদলানো যাবে কিনা
 */
$colors = $colors ?? ['#1F2937', '#00CFFF', '#1677E5', '#08224A', '#16A34A', '#DC2626', '#F59E0B', '#7C3AED', '#FFFFFF'];
$sizes = $sizes ?? true;
?>
<?php if (empty($GLOBALS['__rteCss'])): $GLOBALS['__rteCss'] = true; ?>
<style>
    .rte { border: 1.5px solid #DCECF5; border-radius: 12px; background: #fff; overflow: hidden; }
    .rte:focus-within { border-color: #02B8F5; box-shadow: 0 0 0 3px rgba(2,184,245,.12); }
    .rte.bad { border-color: #DC2626; }
    .rte-bar { display: flex; flex-wrap: wrap; gap: 4px; align-items: center; padding: 6px 8px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; }
    .rte-bar button, .rte-bar select { height: 32px; min-width: 32px; border: 1px solid transparent; border-radius: 7px; background: transparent; color: #334155; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 0 8px; font-family: inherit; }
    .rte-bar button:hover, .rte-bar select:hover { background: #E2E8F0; }
    .rte-bar button.on { background: #DBEAFE; color: #1D4ED8; }
    .rte-bar select { border-color: #E2E8F0; background: #fff; font-size: 13px; padding: 0 6px; }
    .rte-sep { width: 1px; height: 22px; background: #E2E8F0; margin: 0 4px; }
    .rte-sw { width: 22px !important; min-width: 22px !important; height: 22px !important; padding: 0 !important; border-radius: 50% !important; border: 2px solid #fff !important; box-shadow: 0 0 0 1px #CBD5E1; }
    .rte-pick { position: relative; overflow: hidden; }
    .rte-pick input[type=color] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; border: 0; padding: 0; }
    .rte-lbl { font-size: 11.5px; font-weight: 700; color: #94A3B8; padding: 0 2px 0 4px; }
    .rte-box { min-height: 52px; padding: 12px 14px; outline: none; overflow-wrap: anywhere; }
    .rte-box:empty::before { content: attr(data-ph); color: #94A3B8; font-weight: 400; font-size: 15px; }
    .rte-hint { font-size: 12px; color: #94A3B8; margin-top: 5px; }
</style>
<?php endif; ?>
<div class="rte" data-rte>
    <div class="rte-bar">
        <button type="button" data-cmd="bold" title="বোল্ড (মোটা)"><b>B</b></button>
        <button type="button" data-cmd="italic" title="ইটালিক (বাঁকা)"><i>I</i></button>
        <button type="button" data-cmd="underline" title="আন্ডারলাইন"><u>U</u></button>
        <?php if ($sizes): ?>
            <span class="rte-sep"></span>
            <select data-size title="লেখার সাইজ">
                <option value="">সাইজ</option>
                <option value="0.7em">খুব ছোট</option>
                <option value="0.85em">ছোট</option>
                <option value="1em">স্বাভাবিক</option>
                <option value="1.2em">বড়</option>
                <option value="1.45em">আরও বড়</option>
                <option value="1.8em">অনেক বড়</option>
            </select>
        <?php endif; ?>
        <span class="rte-sep"></span>
        <span class="rte-lbl">রঙ</span>
        <?php foreach ($colors as $c): ?><button type="button" class="rte-sw" data-color="<?= e($c) ?>" style="background:<?= e($c) ?>;" title="<?= e($c) ?>"></button><?php endforeach; ?>
        <button type="button" class="rte-pick" title="অন্য রঙ বাছুন"><i class="fas fa-palette" style="color:#7C3AED;"></i><input type="color" data-color-pick value="#00CFFF" /></button>
        <span class="rte-sep"></span>
        <button type="button" class="rte-pick" title="হাইলাইট (পেছনের রঙ)"><i class="fas fa-highlighter" style="color:#CA8A04;"></i><input type="color" data-bg-pick value="#FEF08A" /></button>
        <button type="button" data-cmd="removeFormat" title="বাছাই করা লেখার সব ফরম্যাট মুছুন"><i class="fas fa-eraser"></i></button>
    </div>
    <div class="rte-box" contenteditable="true" id="<?= e($id) ?>" style="<?= e($box ?? '') ?>" data-ph="<?= e($ph ?? 'এখানে লিখুন...') ?>"><?= $html /* sanitize_rich_html করা */ ?></div>
</div>
<script>
(function () {
    if (window.__rteInit) return; window.__rteInit = true;
    var saved = null, cur = null;
    function boxOf(el) { var r = el.closest('[data-rte]'); return r ? r.querySelector('.rte-box') : null; }
    function save() { var s = window.getSelection(); if (s.rangeCount && cur && cur.contains(s.anchorNode)) saved = s.getRangeAt(0).cloneRange(); }
    function restore(box) { box.focus(); if (saved && box.contains(saved.startContainer)) { var s = window.getSelection(); s.removeAllRanges(); s.addRange(saved); } }
    function exec(box, cmd, val, css) {
        restore(box);
        try { document.execCommand('styleWithCSS', false, !!css); } catch (e) { }
        document.execCommand(cmd, false, val === undefined ? null : val);
        save(); box.dispatchEvent(new Event('input', { bubbles: true }));
    }
    /** বাছাই করা লেখায় একটা CSS প্রপার্টি বসায় (ভেতরের আগের একই প্রপার্টি সরিয়ে) */
    function wrapStyle(box, prop, value) {
        restore(box);
        var s = window.getSelection(); if (!s.rangeCount || s.isCollapsed) { alert('আগে লেখাটা মাউস দিয়ে বাছাই (select) করুন।'); return; }
        try { document.execCommand('styleWithCSS', false, false); } catch (e) { }
        document.execCommand('fontSize', false, '7');
        box.querySelectorAll('font[size="7"]').forEach(function (f) {
            var sp = document.createElement('span');
            if (value) sp.style.setProperty(prop, value);
            while (f.firstChild) sp.appendChild(f.firstChild);
            sp.querySelectorAll('[style]').forEach(function (x) { x.style.removeProperty(prop); if (!x.getAttribute('style')) x.removeAttribute('style'); });
            sp.querySelectorAll('font[size]').forEach(function (x) { x.removeAttribute('size'); });
            if (prop === 'font-weight') sp.querySelectorAll('b, strong').forEach(function (x) { x.replaceWith.apply(x, Array.prototype.slice.call(x.childNodes)); });
            if (!sp.getAttribute('style')) { var fr = document.createDocumentFragment(); while (sp.firstChild) fr.appendChild(sp.firstChild); f.replaceWith(fr); } else f.replaceWith(sp);
        });
        save(); box.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function setSize(box, size) { wrapStyle(box, 'font-size', size === '1em' ? '' : size); }
    /** বোল্ড: শিরোনাম আগে থেকেই একটু মোটা — তাই বাছাই করা অংশ আরও মোটা (800) বা আবার স্বাভাবিক */
    function toggleBold(box) {
        restore(box);
        var s = window.getSelection(); if (!s.rangeCount) return;
        var n = s.anchorNode; if (n && n.nodeType === 3) n = n.parentNode;
        var w = parseInt(getComputedStyle(n).fontWeight, 10) || 400, base = parseInt(getComputedStyle(box).fontWeight, 10) || 400;
        wrapStyle(box, 'font-weight', w >= Math.max(700, base + 100) ? (base === 400 ? '' : String(base)) : (base >= 600 ? '800' : '700'));
    }
    document.addEventListener('selectionchange', function () { var a = document.activeElement; if (a && a.classList && a.classList.contains('rte-box')) { cur = a; save(); } });
    document.addEventListener('mousedown', function (e) { if (e.target.closest('.rte-bar button')) e.preventDefault(); });
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.rte-bar [data-cmd], .rte-bar [data-color]'); if (!b) return;
        var box = boxOf(b); if (!box) return;
        if (b.hasAttribute('data-color')) exec(box, 'foreColor', b.getAttribute('data-color'), true);
        else if (b.getAttribute('data-cmd') === 'bold') toggleBold(box);
        else exec(box, b.getAttribute('data-cmd'));
    });
    document.addEventListener('change', function (e) {
        var t = e.target, box = t.closest && t.closest('[data-rte]') ? boxOf(t) : null; if (!box) return;
        if (t.hasAttribute('data-size')) { if (t.value) setSize(box, t.value); t.value = ''; }
        else if (t.hasAttribute('data-color-pick')) exec(box, 'foreColor', t.value, true);
        else if (t.hasAttribute('data-bg-pick')) exec(box, 'hiliteColor', t.value, true);
    });
    document.addEventListener('keydown', function (e) {
        var box = e.target.closest && e.target.closest('.rte-box'); if (!box || e.key !== 'Enter') return;
        e.preventDefault();
        if (!document.execCommand('insertLineBreak')) document.execCommand('insertHTML', false, '<br><br>');
    });
    document.addEventListener('paste', function (e) {
        var box = e.target.closest && e.target.closest('.rte-box'); if (!box) return;
        e.preventDefault(); var t = (e.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, t);
    });
})();
</script>
