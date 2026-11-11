<?php
/** কোর্সের তথ্য — নাম, বিবরণ (নিজের মতো সাজানো), কী কী থাকছে, দাম, থাম্বনেইল */
$c = $Model; $isNew = !$c;
$kind = $c ? (string)$c->Kind : (input_str('kind', '') === 'live' ? 'live' : 'course'); $kl = $kind === 'live' ? 'লাইভ ক্লাস' : 'কোর্স';
$ViewData['Title'] = $isNew ? 'নতুন কোর্স' : 'কোর্স এডিট — ' . $c->Title;
$ViewData['Robots'] = 'noindex, nofollow';
$levels = $ViewBag->Levels ?? []; $cats = $ViewBag->Cats ?? [];
$pct = course_admin_pct();
?>
<?php partial('Course/_Styles'); ?>
<style>
    .ce-thumb { display: grid; grid-template-columns: 220px minmax(0, 1fr); gap: 14px; align-items: center; }
    .ce-thumb .cx-thumb { border-radius: 14px; }
    .ce-share { background: #F0FDF4; border: 1px solid #BBF7D0; color: #14532D; border-radius: 12px; padding: 10px 14px; font-size: 14px; margin-top: -4px; margin-bottom: 14px; }
    @media (max-width: 640px) { .ce-thumb { grid-template-columns: minmax(0, 1fr); } }
</style>
<?= csrf_field() ?>
<div class="cx-page">
    <div class="cx-wrap narrow">
        <a class="cx-back" href="<?= $isNew ? '/Course/Manage' : '/Course/Build/' . (int)$c->Id ?>"><i class="fas fa-arrow-left"></i> <?= $isNew ? 'আমার কোর্স' : 'কোর্সে ফিরে যান' ?></a>
        <h1 class="cx-h1" style="margin-bottom:16px;"><?= $isNew ? '<i class="fas ' . ($kind === 'live' ? 'fa-tower-broadcast' : 'fa-plus-circle') . '" style="color:' . ($kind === 'live' ? '#DC2626' : '#16A34A') . ';"></i> নতুন ' . $kl . ' বানান' : '<i class="fas fa-pen" style="color:#F59E0B;"></i> ' . $kl . 'ের তথ্য' ?></h1>
        <form id="ceForm" class="cx-card" autocomplete="off">
            <input type="hidden" name="id" value="<?= (int)($c->Id ?? 0) ?>" />
            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:800;color:#334155;font-size:13.5px;margin-bottom:6px;">ধরন</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <label style="flex:1;min-width:220px;border:1.5px solid #DCE7F0;border-radius:12px;padding:10px 12px;cursor:pointer;display:flex;gap:10px;align-items:flex-start;"><input type="radio" name="kind" value="course"<?= $kind !== 'live' ? ' checked' : '' ?> style="margin-top:4px;" /><span><b><i class="fas fa-book-open-reader" style="color:#0EA5E9;"></i> কোর্স</b><br><small style="color:#64748B;">রেকর্ড করা ভিডিও ক্লাস, অ্যাসাইনমেন্ট, কুইজ — যেকোনো সময় দেখা যায়</small></span></label>
                    <label style="flex:1;min-width:220px;border:1.5px solid #DCE7F0;border-radius:12px;padding:10px 12px;cursor:pointer;display:flex;gap:10px;align-items:flex-start;"><input type="radio" name="kind" value="live"<?= $kind === 'live' ? ' checked' : '' ?> style="margin-top:4px;" /><span><b><i class="fas fa-tower-broadcast" style="color:#DC2626;"></i> লাইভ ক্লাস / ব্যাচ</b><br><small style="color:#64748B;">নির্দিষ্ট তারিখ ও সময়ে সবাই একসাথে অনলাইনে — শিক্ষার্থীরা পেমেন্ট করে যোগ দেবে</small></span></label>
                </div>
            </div>
            <div class="cx-f" data-f="title"><label for="cTitle">কোর্সের নাম *</label><input type="text" id="cTitle" name="title" maxlength="150" value="<?= e($c->Title ?? '') ?>" placeholder="যেমন: HSC Physics — সম্পূর্ণ প্রস্তুতি" /></div>
            <div class="cx-f"><label for="cSub">ছোট বিবরণ <small>(এক লাইনে)</small></label><input type="text" id="cSub" name="subtitle" maxlength="250" value="<?= e($c->Subtitle ?? '') ?>" placeholder="যেমন: ৩ মাসে পুরো সিলেবাস — ক্লাস, নোট, পরীক্ষা" /></div>
            <div class="cx-grid3">
                <div class="cx-f" data-f="price"><label for="cPrice">দাম (৳) *</label><input type="number" id="cPrice" name="price" min="0" max="999999" value="<?= (int)($c->Price ?? 0) ?>" /><div class="hint">ফ্রি কোর্স হলে 0</div></div>
                <div class="cx-f" data-f="regularPrice"><label for="cReg">আগের দাম <small>(ঐচ্ছিক — কাটা দেখাবে)</small></label><input type="number" id="cReg" name="regularPrice" min="0" max="999999" value="<?= e($c->RegularPrice ?? '') ?>" /></div>
                <div class="cx-f"><label for="cPass">কুইজে পাস মার্ক (%)</label><input type="number" id="cPass" name="passPercent" min="0" max="100" value="<?= (int)($c->PassPercent ?? 60) ?>" /></div>
            </div>
            <div class="ce-share" id="ceShare"></div>
            <div class="cx-grid2">
                <div class="cx-f"><label for="cCat">বিভাগ</label><input type="text" id="cCat" name="category" maxlength="60" list="cCats" value="<?= e($c->Category ?? '') ?>" placeholder="যেমন: HSC, SSC, Programming, Admission" /><datalist id="cCats"><?php foreach ($cats as $k): ?><option value="<?= e($k) ?>"></option><?php endforeach; ?></datalist></div>
                <div class="cx-f"><label for="cLevel">লেভেল</label><select id="cLevel" name="level"><?php foreach ($levels as $lv): ?><option<?= ($c->Level ?? '') === $lv ? ' selected' : '' ?>><?= e($lv) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="cx-f"><label for="cInc">এই কোর্সে কী কী থাকছে <small>(প্রতি লাইনে একটা)</small></label>
                <textarea id="cInc" name="includes" rows="5" placeholder="৪০টা রেকর্ড ক্লাস&#10;প্রতি সপ্তাহে ১টা লাইভ ক্লাস&#10;প্রতিটা অধ্যায়ের পর কুইজ&#10;PDF নোট&#10;কোর্স শেষে সার্টিফিকেট"><?= e($c->Includes ?? '') ?></textarea></div>
            <div class="cx-f" data-f="description"><label>কোর্সের বিস্তারিত বিবরণ <small>(নিজের মতো সাজান)</small></label>
                <?php partial('Admin/_RichEditor', ['id' => 'cDesc', 'html' => sanitize_rich_html($c->Description ?? ''), 'ph' => 'কোর্সে কী শেখানো হবে, কারা করবে, কীভাবে ক্লাস হবে...', 'box' => 'min-height:160px;font-size:15px;line-height:1.7;color:#334155;']); ?></div>
            <div class="cx-f" data-f="thumbnail"><label>থাম্বনেইল ছবি <small>(16:9 — যেমন 1280×720, ৩ MB এর মধ্যে)</small></label>
                <div class="ce-thumb"><div id="ceThumbPrev"><?= course_thumb_html($c ?: obj(['Title' => 'কোর্সের নাম'])) ?></div>
                    <div><input type="file" id="cThumb" name="thumbnail" accept="image/jpeg,image/png,image/webp" /><div class="hint">না দিলে কোর্সের নাম দিয়ে রঙিন কার্ড দেখাবে।</div></div></div></div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <button type="submit" class="cx-btn pri lg" id="ceSave"><i class="fas fa-floppy-disk"></i> <?= $isNew ? 'কোর্স তৈরি করুন' : 'সেভ করুন' ?></button>
                <span class="cx-msg" id="ceMsg"></span>
                <?php if (!$isNew): ?><button type="button" class="cx-btn red sm" id="ceDel" style="margin-left:auto;"><i class="fas fa-trash"></i> কোর্স মুছুন</button><?php endif; ?>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    var f = document.getElementById('ceForm'), pct = <?= (int)$pct ?>;
    function share() {
        var p = parseInt(f.elements.price.value || '0', 10) || 0, s = document.getElementById('ceShare');
        s.innerHTML = p > 0 ? '<i class="fas fa-sack-dollar"></i> প্রতি বিক্রিতে আপনি পাবেন <b>৳' + Math.round(p * (100 - pct) / 100).toLocaleString('en') + '</b> (' + (100 - pct) + '%) · মাষ্টার মশাই ৳' + Math.round(p * pct / 100).toLocaleString('en') + ' (' + pct + '%)' : '<i class="fas fa-gift"></i> ফ্রি কোর্স — যে কেউ সরাসরি ভর্তি হতে পারবে।';
    }
    f.elements.price.addEventListener('input', share); share();
    document.getElementById('cThumb').addEventListener('change', function () {
        var fl = this.files[0]; if (!fl) return;
        document.getElementById('ceThumbPrev').innerHTML = '<div class="cx-thumb"><img src="' + URL.createObjectURL(fl) + '" alt="" /></div>';
    });
    f.addEventListener('submit', async function (e) {
        e.preventDefault();
        var fd = new FormData(f); fd.set('description', document.getElementById('cDesc').innerHTML);
        f.querySelectorAll('.cx-f.bad').forEach(function (x) { x.classList.remove('bad'); });
        var b = document.getElementById('ceSave'), m = document.getElementById('ceMsg'); b.disabled = true; m.className = 'cx-msg'; m.textContent = 'সেভ হচ্ছে...';
        var d = await cxPost('/Course/Save', fd);
        b.disabled = false; m.className = 'cx-msg ' + (d.success ? 'ok' : 'err'); m.textContent = d.message || '';
        if (!d.success && d.field) { var x = f.querySelector('[data-f="' + d.field + '"]'); if (x) { x.classList.add('bad'); x.scrollIntoView({ behavior: 'smooth', block: 'center' }); } }
        if (d.success && d.redirect) location.href = d.redirect;
    });
    var del = document.getElementById('ceDel');
    if (del) del.addEventListener('click', async function () {
        if (!confirm('কোর্সটা পুরোপুরি মুছে ফেলবেন? (কেউ কিনে থাকলে মোছা যাবে না)')) return;
        var d = await cxPost('/Course/Delete', { id: f.elements.id.value }); cxToast(d.message || '');
        if (d.success && d.redirect) setTimeout(function () { location.href = d.redirect; }, 700);
    });
})();
</script>
