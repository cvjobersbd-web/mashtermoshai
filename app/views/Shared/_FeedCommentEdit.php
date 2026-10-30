<?php
/**
 * Tuition Circular / Find Tutor পোস্টের কমেন্ট এডিট আর ডিলিট (JS)।
 * দরকার: $prefix (কমেন্টের id prefix: cfbc- / fbc-), $editUrl, $delUrl, $countPrefix, $countSuffix, $postPrefix
 */
?>
<style>
    .fbc-meta .fbc-del:hover { color: #DC2626 !important; }
    .fbc-edit { display: flex; flex-direction: column; gap: 6px; margin: 2px 0 4px; }
    .fbc-edit textarea { width: 100%; min-width: 220px; min-height: 38px; max-height: 160px; border: 1.5px solid #9EC2FA; border-radius: 14px; padding: 8px 12px;
        font: inherit; font-size: 14.5px; line-height: 1.4; resize: none; outline: none; background: #fff; }
    .fbc-edit .row-b { display: flex; align-items: center; gap: 10px; font-size: 12px; color: #65676B; flex-wrap: wrap; }
    .fbc-edit .row-b button { border: none; background: none; padding: 0; font: inherit; font-weight: 700; cursor: pointer; color: #1D7FE5; }
    .fbc-edit .row-b button.cx { color: #65676B; }
    .fbc-edit .fbc-mention-tag { align-self: flex-start; }
    .fbc.removing { opacity: 0; transform: scale(.98); transition: opacity .25s, transform .25s; }
</style>
<script>
(function () {
    var CFG = <?= js(['prefix' => $prefix, 'edit' => $editUrl, 'del' => $delUrl, 'countPrefix' => $countPrefix, 'countSuffix' => $countSuffix, 'postPrefix' => $postPrefix]) ?>;
    function esc(t) { return String(t == null ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
    function render(content) {
        var s = esc(content), m = s.match(/^@\[([^\]]+)\]\s?/);
        return m ? '<span class="fbc-mention">' + m[1] + '</span> ' + s.slice(m[0].length) : s;
    }
    function token() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    async function post(url, data) {
        var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); }); fd.append('__RequestVerificationToken', token());
        var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        return r.json();
    }
    function own(el) { return el.closest('.fbc'); }
    // এই কমেন্টের নিজের অংশ (রিপ্লাইয়ের অংশ নয়)
    function textEl(c) { return c.querySelector(':scope > .fbc-main > .fbc-bubble .fbc-text'); }
    function bubble(c) { return c.querySelector(':scope > .fbc-main > .fbc-bubble'); }
    function meta(c) { return c.querySelector(':scope > .fbc-main > .fbc-meta'); }

    function closeEdit(c, restore) {
        var box = c.querySelector(':scope > .fbc-main > .fbc-edit');
        if (box) box.remove();
        bubble(c).hidden = false; meta(c).hidden = false;
    }
    function openEdit(c) {
        document.querySelectorAll('.fbc .fbc-edit').forEach(function (b) { closeEdit(b.closest('.fbc')); });
        var raw = c.getAttribute('data-raw') || textEl(c).textContent;
        var m = raw.match(/^@\[([^\]]+)\]\s?/), mention = m ? m[0] : '', body = m ? raw.slice(m[0].length) : raw;
        var box = document.createElement('div'); box.className = 'fbc-edit';
        box.innerHTML = (mention ? '<span class="fbc-mention-tag">' + esc(m[1]) + '</span>' : '')
            + '<textarea maxlength="2000" aria-label="Edit comment"></textarea>'
            + '<div class="row-b"><span>Enter চাপলে সেভ · Esc চাপলে বাতিল</span><button type="button" class="sv">Save</button><button type="button" class="cx">Cancel</button></div>';
        var ta = box.querySelector('textarea'); ta.value = body;
        bubble(c).hidden = true; meta(c).hidden = true;
        c.querySelector(':scope > .fbc-main').insertBefore(box, meta(c).nextSibling);
        function grow() { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 160) + 'px'; }
        grow(); ta.addEventListener('input', grow);
        ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length);
        async function save() {
            var v = ta.value.trim();
            if (!v) { alert('কমেন্ট খালি রাখা যাবে না।'); return; }
            if (v === body.trim()) { closeEdit(c); return; }
            ta.disabled = true;
            try {
                var d = await post(CFG.edit, { commentId: c.getAttribute('data-id'), content: mention + v });
                if (!d.success) { alert(d.message || 'Failed'); ta.disabled = false; ta.focus(); return; }
                c.setAttribute('data-raw', d.content);
                textEl(c).innerHTML = render(d.content);
                closeEdit(c);
            } catch (err) { alert('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'); ta.disabled = false; }
        }
        box.querySelector('.sv').addEventListener('click', save);
        box.querySelector('.cx').addEventListener('click', function () { closeEdit(c); });
        ta.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); save(); }
            else if (e.key === 'Escape') { e.preventDefault(); closeEdit(c); }
        });
    }
    async function del(c) {
        var hasReplies = !c.classList.contains('is-reply') && c.querySelector('.fbc-replies .fbc');
        if (!confirm(hasReplies ? 'কমেন্ট আর এর সব রিপ্লাই মুছে ফেলবেন?' : 'কমেন্টটি মুছে ফেলবেন?')) return;
        try {
            var d = await post(CFG.del, { commentId: c.getAttribute('data-id') });
            if (!d.success) { alert(d.message || 'Failed'); return; }
            var postEl = c.closest('[id^="' + CFG.postPrefix + '"]');
            (d.ids || []).forEach(function (id) {
                var el = document.getElementById(CFG.prefix + id);
                if (el) { el.classList.add('removing'); setTimeout(function () { el.remove(); }, 250); }
            });
            if (postEl) {
                var pid = postEl.id.slice(CFG.postPrefix.length), cnt = document.getElementById(CFG.countPrefix + pid);
                if (cnt) { var n = Math.max(0, (parseInt(cnt.textContent, 10) || 0) - (d.ids || []).length); cnt.textContent = n + CFG.countSuffix; }
            }
        } catch (err) { alert('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'); }
    }
    document.addEventListener('click', function (e) {
        var ed = e.target.closest('[data-cm-edit]');
        if (ed && own(ed) && own(ed).id.indexOf(CFG.prefix) === 0) { e.preventDefault(); openEdit(own(ed)); return; }
        var dl = e.target.closest('[data-cm-del]');
        if (dl && own(dl) && own(dl).id.indexOf(CFG.prefix) === 0) { e.preventDefault(); del(own(dl)); }
    });
})();
</script>
