<div class="ef-lb" id="efLb" role="dialog" aria-modal="true" aria-label="ছবি">
    <img alt="" /><button type="button" class="x" aria-label="বন্ধ"><i class="fas fa-xmark"></i></button>
    <button type="button" class="pv" aria-label="আগের ছবি"><i class="fas fa-chevron-left"></i></button><button type="button" class="nx" aria-label="পরের ছবি"><i class="fas fa-chevron-right"></i></button>
    <span class="ct"></span>
</div>
<div class="ef-toast" id="efToast" role="status"></div>
<?php if (!isset($GLOBALS['__efCsrf'])) { $GLOBALS['__efCsrf'] = 1; echo csrf_field(); } ?>
<script>
window.EF = (function () {
    var LOGIN = '/Account/Login?returnUrl=' + encodeURIComponent(location.pathname + location.search);
    var LOGGED = <?= js(current_user_id() !== null) ?>;
    function token() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    async function post(url, data) {
        var fd = data instanceof FormData ? data : new FormData();
        if (!(data instanceof FormData)) Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', token());
        try { var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); return await r.json(); }
        catch (e) { return { success: false, message: 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।' }; }
    }
    var toastEl = document.getElementById('efToast'), tt;
    function toast(m, bad) { toastEl.textContent = m; toastEl.className = 'ef-toast show' + (bad ? ' err' : ''); clearTimeout(tt); tt = setTimeout(function () { toastEl.className = 'ef-toast'; }, 3000); }
    function absUrl(id) { return location.origin + '/Feed/Post/' + id; }

    // ===== লাইটবক্স =====
    var lb = document.getElementById('efLb'), lbImg = lb.querySelector('img'), lbList = [], lbI = 0;
    function lbShow() { lbImg.src = lbList[lbI]; lb.querySelector('.ct').textContent = lbList.length > 1 ? (lbI + 1) + ' / ' + lbList.length : ''; lb.querySelector('.pv').hidden = lb.querySelector('.nx').hidden = lbList.length < 2; }
    function lbOpen(list, i) { lbList = list; lbI = i; lbShow(); lb.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function lbClose() { lb.classList.remove('open'); document.body.style.overflow = ''; lbImg.removeAttribute('src'); }
    lb.addEventListener('click', function (e) {
        if (e.target.closest('.x') || e.target === lb) lbClose();
        else if (e.target.closest('.pv')) { lbI = (lbI - 1 + lbList.length) % lbList.length; lbShow(); }
        else if (e.target.closest('.nx')) { lbI = (lbI + 1) % lbList.length; lbShow(); }
    });
    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape') lbClose(); else if (e.key === 'ArrowLeft') lb.querySelector('.pv').click(); else if (e.key === 'ArrowRight') lb.querySelector('.nx').click();
    });

    async function loadComments(card) {
        var box = card.querySelector('.ef-cbox'); if (!box) return;
        box.hidden = false;
        if (box.getAttribute('data-loaded') !== '1') {
            box.setAttribute('data-loaded', '1');
            var list = box.querySelector('.ef-clist');
            list.innerHTML = '<div class="ef-cempty"><i class="fas fa-spinner fa-spin"></i></div>';
            try { var r = await fetch('/Feed/Comments/' + card.getAttribute('data-post')); list.innerHTML = r.ok ? await r.text() : ''; } catch (e) { list.innerHTML = ''; }
            if (!list.children.length) list.innerHTML = '<div class="ef-cempty">এখনো কোনো কমেন্ট নেই — প্রথম কমেন্টটা আপনিই করুন।</div>';
        }
        var ta = box.querySelector('textarea'); if (ta) ta.focus();
    }
    function setCount(card, n) {
        var b = card.querySelector('[data-cc]'); if (b) b.textContent = n ? n + ' comment' + (n > 1 ? 's' : '') : '';
    }

    document.addEventListener('click', async function (e) {
        // মেনু বাইরে ক্লিক করলে বন্ধ
        document.querySelectorAll('.ef-menu.open').forEach(function (m) { if (!m.contains(e.target)) m.classList.remove('open'); });
        var b = e.target.closest('[data-ef]'); if (!b) return;
        var card = b.closest('[data-post]'); if (!card) return;
        var id = card.getAttribute('data-post'), act = b.getAttribute('data-ef');
        if (act === 'menu') { b.parentNode.classList.toggle('open'); return; }
        if (act === 'more') { card.querySelector('.ef-text').classList.remove('clamp'); b.remove(); return; }
        if (act === 'img') { lbOpen(JSON.parse(b.closest('[data-imgs]').getAttribute('data-imgs')), +b.getAttribute('data-i')); return; }
        if (act === 'yt') {
            var ifr = document.createElement('iframe');
            ifr.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(b.getAttribute('data-yt')) + '?autoplay=1&rel=0';
            ifr.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'; ifr.allowFullscreen = true; ifr.title = 'YouTube video';
            b.replaceWith(ifr); return;
        }
        if (act === 'copy') {
            b.closest('.ef-menu').classList.remove('open');
            try { await navigator.clipboard.writeText(absUrl(id)); toast('লিংক কপি হয়েছে'); } catch (er) { toast(absUrl(id)); }
            return;
        }
        if (act === 'share') {
            var data = { title: 'Educational Feed — মাষ্টার মশাই', url: absUrl(id) };
            try { if (navigator.share) { await navigator.share(data); return; } await navigator.clipboard.writeText(data.url); toast('লিংক কপি হয়েছে — যেকোনো জায়গায় শেয়ার করুন'); }
            catch (er) { if (er && er.name !== 'AbortError') toast(data.url); }
            return;
        }
        if (act === 'delete') {
            b.closest('.ef-menu').classList.remove('open');
            if (!confirm('পোস্টটি মুছে ফেলবেন? এটা আর ফেরত আনা যাবে না।')) return;
            var r = await post('/Feed/Delete', { postId: id });
            if (r.success) { card.style.transition = 'opacity .25s'; card.style.opacity = '0'; setTimeout(function () { card.remove(); document.dispatchEvent(new CustomEvent('ef:removed')); }, 260); toast(r.message); }
            else toast(r.message || 'মোছা যায়নি।', true);
            return;
        }
        if (act === 'like') {
            if (!LOGGED) { location.href = LOGIN; return; }
            b.disabled = true;
            var r2 = await post('/Feed/Like', { postId: id });
            b.disabled = false;
            if (!r2.success) { toast(r2.message, true); return; }
            b.classList.toggle('liked', r2.liked); b.setAttribute('aria-pressed', r2.liked ? 'true' : 'false');
            b.querySelector('i').className = (r2.liked ? 'fas' : 'far') + ' fa-thumbs-up';
            var lk = card.querySelector('[data-likes]');
            lk.innerHTML = r2.count ? <?= js(like_badge_html()) ?> + ' <span data-lc>' + r2.count + '</span>' : '';
            return;
        }
        if (act === 'comments') { loadComments(card); return; }
        if (act === 'cdel') {
            if (!confirm('কমেন্টটি মুছে ফেলবেন?')) return;
            var cm = b.closest('[data-comment]');
            var r3 = await post('/Feed/DeleteComment', { commentId: cm.getAttribute('data-comment') });
            if (r3.success) { cm.remove(); setCount(card, r3.count); } else toast(r3.message, true);
        }
    });

    // কমেন্ট পাঠানো (Enter = পাঠাও, Shift+Enter = নতুন লাইন)
    document.addEventListener('submit', async function (e) {
        var f = e.target.closest('[data-ef-cform]'); if (!f) return;
        e.preventDefault();
        var ta = f.querySelector('textarea'), btn = f.querySelector('button'), v = ta.value.trim();
        if (!v) { ta.focus(); return; }
        var card = f.closest('[data-post]');
        btn.disabled = true;
        var r = await post('/Feed/Comment', { postId: card.getAttribute('data-post'), content: v });
        btn.disabled = false;
        if (!r.success) { toast(r.message, true); return; }
        var list = card.querySelector('.ef-clist'), em = list.querySelector('.ef-cempty'); if (em) em.remove();
        list.insertAdjacentHTML('beforeend', r.html);
        ta.value = ''; ta.style.height = '';
        setCount(card, r.count);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && e.target.matches('[data-ef-cform] textarea')) { e.preventDefault(); e.target.form.requestSubmit(); }
    });
    document.addEventListener('input', function (e) {
        if (e.target.matches('[data-ef-cform] textarea')) { e.target.style.height = 'auto'; e.target.style.height = Math.min(e.target.scrollHeight, 140) + 'px'; }
    });
    return { post: post, toast: toast, token: token };
})();
</script>
