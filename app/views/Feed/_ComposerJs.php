<?php
/** কম্পোজারের JS — পোস্ট হলে #efList এ যোগ হয়। @var int $videoLimit @var bool $isAdmin @var string $tab ('all'|'mine'|'profile') */
?>
<script>
(function () {
    var list = document.getElementById('efList'), empty = document.getElementById('efEmpty');
    var form = document.getElementById('efc'); if (!form) return;
    var ta = document.getElementById('efcText'), moreBox = document.getElementById('efcMore'), prev = document.getElementById('efcPrev');
    var imgIn = document.getElementById('efcImg'), vidIn = document.getElementById('efcVid'), ytBtn = document.getElementById('efcYtBtn');
    // ভিডিও/YouTube শুধু অ্যাডমিনের জন্য — সাধারণ ইউজারের পেজে এগুলো থাকে না
    var yt = document.getElementById('efcYt') || { value: '', hidden: true, focus: function () {} };
    var send = document.getElementById('efcSend'), bar = document.getElementById('efcBar');
    var VIDEO_MAX = <?= (int)$videoLimit ?>, IMG_MAX = 5 * 1024 * 1024, MAX_IMGS = 6, TAB = <?= js($tab) ?>;
    var images = [], video = null, urls = [];
    function expand() { moreBox.hidden = false; }
    ta.addEventListener('focus', expand);
    ta.addEventListener('input', function () { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 320) + 'px'; });
    function clearUrls() { urls.forEach(URL.revokeObjectURL); urls = []; }
    function draw() {
        clearUrls(); prev.innerHTML = '';
        images.forEach(function (f, i) {
            var u = URL.createObjectURL(f); urls.push(u);
            prev.insertAdjacentHTML('beforeend', '<div class="it"><img src="' + u + '" alt="" /><button type="button" class="rm" data-rm="img" data-i="' + i + '" aria-label="সরান"><i class="fas fa-xmark"></i></button></div>');
        });
        if (video) {
            var vu = URL.createObjectURL(video); urls.push(vu);
            prev.insertAdjacentHTML('beforeend', '<div class="it vid"><video src="' + vu + '" controls muted playsinline></video><button type="button" class="rm" data-rm="vid" aria-label="সরান"><i class="fas fa-xmark"></i></button></div>');
        }
    }
    function only(kind) {
        // ছবি / ভিডিও / YouTube — যেকোনো একটা
        if (kind !== 'img' && images.length) { images = []; }
        if (kind !== 'vid') video = null;
        if (kind !== 'yt') { yt.value = ''; yt.hidden = true; }
    }
    prev.addEventListener('click', function (e) {
        var b = e.target.closest('[data-rm]'); if (!b) return;
        if (b.getAttribute('data-rm') === 'vid') video = null; else images.splice(+b.getAttribute('data-i'), 1);
        draw();
    });
    imgIn.addEventListener('change', function () {
        var files = Array.prototype.slice.call(imgIn.files); imgIn.value = ''; if (!files.length) return;
        expand(); only('img');
        for (var i = 0; i < files.length; i++) {
            var f = files[i];
            if (!/^image\/(jpeg|png|gif|webp)$/.test(f.type)) { EF.toast('"' + f.name + '" — শুধু JPG, PNG, GIF বা WEBP ছবি।', true); continue; }
            if (f.size > IMG_MAX) { EF.toast('"' + f.name + '" ৫MB এর বেশি বড়।', true); continue; }
            if (images.length >= MAX_IMGS) { EF.toast('সর্বোচ্চ ' + MAX_IMGS + 'টা ছবি দেওয়া যাবে।', true); break; }
            images.push(f);
        }
        draw();
    });
    if (vidIn) vidIn.addEventListener('change', function () {
        var f = vidIn.files[0]; vidIn.value = ''; if (!f) return;
        if (f.size > VIDEO_MAX) { EF.toast('ভিডিও ' + Math.floor(VIDEO_MAX / 1048576) + 'MB এর বেশি বড়। বড় ভিডিও YouTube-এ দিয়ে লিংক শেয়ার করুন।', true); return; }
        expand(); only('vid'); video = f; draw();
    });
    if (ytBtn) ytBtn.addEventListener('click', function () { expand(); only('yt'); yt.hidden = false; draw(); yt.focus(); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = ta.value.trim();
        if (!text && !images.length && !video && !yt.value.trim()) { ta.focus(); EF.toast('কিছু লিখুন অথবা ছবি/ভিডিও দিন।', true); return; }
        var fd = new FormData();
        fd.append('content', text); fd.append('subject', form.subject.value.trim()); fd.append('youtube', yt.hidden ? '' : yt.value.trim());
        images.forEach(function (f) { fd.append('images[]', f, f.name); });
        if (video) fd.append('video', video, video.name);
        fd.append('__RequestVerificationToken', EF.token());
        send.disabled = true; send.innerHTML = '<i class="fas fa-spinner fa-spin"></i> পাঠানো হচ্ছে';
        var hasFiles = images.length || video; bar.hidden = !hasFiles; bar.firstElementChild.style.width = '0';
        // XHR — যাতে আপলোডের অগ্রগতি দেখানো যায়
        var x = new XMLHttpRequest();
        x.open('POST', '/Feed/Create'); x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        x.upload.onprogress = function (ev) { if (ev.lengthComputable) bar.firstElementChild.style.width = Math.round(ev.loaded / ev.total * 100) + '%'; };
        x.onload = function () {
            var r; try { r = JSON.parse(x.responseText); } catch (er) { r = { success: false, message: x.status === 413 ? 'ফাইল খুব বড় — ছোট ফাইল দিন।' : 'সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।' }; }
            if (!r.success && x.status === 400 && hasFiles) r.message = 'ফাইলগুলো সার্ভারের সীমার চেয়ে বড় হতে পারে — ছোট ফাইল দিয়ে আবার চেষ্টা করুন।';
            done(r);
        };
        x.onerror = function () { done({ success: false, message: 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।' }); };
        x.send(fd);
    });
    function done(r) {
        send.disabled = false; send.innerHTML = '<i class="fas fa-paper-plane"></i> ' + <?= js($isAdmin ? 'Post' : 'Submit') ?>; bar.hidden = true;
        if (!r.success) { EF.toast(r.message || 'পোস্ট করা যায়নি।', true); return; }
        EF.toast(r.message);
        ta.value = ''; ta.style.height = ''; form.subject.value = ''; images = []; video = null; only('none'); draw(); moreBox.hidden = true;
        if (r.status === 'Approved' || TAB === 'mine') { list.insertAdjacentHTML('afterbegin', r.html); empty.hidden = true; }
        else {
            var n = document.createElement('div'); n.className = 'ef-status Pending'; n.style.margin = '0 0 16px';
            n.innerHTML = '<i class="fas fa-hourglass-half"></i><span>আপনার পোস্ট রিভিউতে আছে। <a href="/Feed?tab=mine" style="color:inherit;font-weight:800;">My Posts</a>-এ দেখতে পাবেন।</span>';
            form.after(n);
        }
    }
})();
</script>
