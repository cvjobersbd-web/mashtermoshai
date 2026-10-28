<?php
/**
 * ✅ Student / Teacher ড্যাশবোর্ডের "My Review" ফর্ম
 * ব্যবহার: partial('Shared/_ReviewForm', ['rvReview' => $reviewOrNull, 'rvSubtitleHint' => 'যেমন: Dhaka College']);
 */
$rv = $rvReview ?? null;
$rvHint = (string)($rvSubtitleHint ?? 'যেমন: Dhaka College / অভিভাবক');
$rvRating = $rv ? (int)$rv->Rating : 5;
$statusInfo = [
    'Pending'  => ['fa-check-circle', 'হোম পেজে দেখানো হচ্ছে', 'আপনার রিভিউ এখন সবাই দেখতে পাচ্ছে। ধন্যবাদ!'],
    'Approved' => ['fa-check-circle', 'হোম পেজে দেখানো হচ্ছে', 'আপনার রিভিউ এখন সবাই দেখতে পাচ্ছে। ধন্যবাদ!'],
    'Rejected' => ['fa-eye-slash', 'রিভিউটি হোম পেজে দেখানো হচ্ছে না', 'চাইলে নিচে এডিট করে আবার পাঠাতে পারেন।'],
];
?>
<style>
    .rv-card { background: #fff; border: 1px solid #DCECF5; border-radius: 18px; padding: 24px; box-shadow: 0 4px 18px rgba(8,34,74,0.04); }
    .rv-status { display: flex; gap: 12px; align-items: flex-start; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; border: 1px solid; }
    .rv-status i { font-size: 20px; margin-top: 2px; }
    .rv-status strong { display: block; font-size: 14.5px; }
    .rv-status span { font-size: 13px; opacity: .9; }
    .rv-status.Pending { background: #FFF7E0; border-color: #FFD36B; color: #8a5a00; }
    .rv-status.Approved { background: #E8F8EF; border-color: #A8E2C0; color: #155724; }
    .rv-status.Rejected { background: #FDECEA; border-color: #F5B7B1; color: #8B1E15; }
    .rv-status .rv-note { display: block; margin-top: 4px; font-style: italic; }

    .rv-label { display: block; font-weight: 700; font-size: 13.5px; color: #08224A; margin-bottom: 6px; }
    .rv-label small { font-weight: 400; color: #7A8CA0; }
    .rv-stars { display: inline-flex; flex-direction: row-reverse; gap: 4px; margin-bottom: 18px; }
    .rv-stars input { position: absolute; opacity: 0; pointer-events: none; }
    .rv-stars label { font-size: 30px; color: #DCECF5; cursor: pointer; transition: color .15s, transform .15s; line-height: 1; }
    .rv-stars label:hover, .rv-stars label:hover ~ label, .rv-stars input:checked ~ label { color: #FFB300; }
    .rv-stars label:active { transform: scale(.9); }
    .rv-input { width: 100%; border: 1.5px solid #DCECF5; border-radius: 12px; padding: 11px 14px; font-size: 14.5px; background: #F5FBFF; color: #08224A; outline: none; transition: border-color .2s, background .2s; }
    .rv-input:focus { border-color: #02B8F5; background: #fff; }
    textarea.rv-input { min-height: 130px; resize: vertical; line-height: 1.6; }
    .rv-count { text-align: right; font-size: 12px; color: #7A8CA0; margin-top: 4px; }
    .rv-btn { margin-top: 16px; display: inline-flex; align-items: center; gap: 8px; border: none; border-radius: 12px; padding: 12px 26px; font-weight: 700; font-size: 15px; color: #fff; background: #02B8F5; box-shadow: 0 8px 20px rgba(2,184,245,.3); cursor: pointer; transition: background .2s, transform .15s; }
    .rv-btn:hover { background: #0099D6; }
    .rv-btn:active { transform: scale(.97); }
    .rv-btn:disabled { opacity: .6; cursor: default; }
    .rv-tip { font-size: 12.5px; color: #7A8CA0; margin-top: 10px; }
    @media (max-width: 576px) { .rv-card { padding: 18px 14px; } .rv-btn { width: 100%; justify-content: center; } .rv-stars label { font-size: 34px; } }
</style>

<div class="rv-card">
    <?php if ($rv && isset($statusInfo[$rv->Status])): [$ic, $tt, $ds] = $statusInfo[$rv->Status]; ?>
        <div class="rv-status <?= e($rv->Status === 'Pending' ? 'Approved' : $rv->Status) ?>">
            <i class="fas <?= e($ic) ?>"></i>
            <div>
                <strong><?= e($tt) ?></strong>
                <span><?= e($ds) ?></span>
                <?php if ($rv->Status === 'Rejected' && !is_blank($rv->AdminNote)): ?>
                    <span class="rv-note">অ্যাডমিনের নোট: <?= e($rv->AdminNote) ?></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <form id="rvForm" onsubmit="return rvSubmit(event)">
        <?= csrf_field() ?>
        <span class="rv-label">আপনার রেটিং</span>
        <div class="rv-stars" role="radiogroup" aria-label="Rating">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" name="rating" id="rv-star-<?= $i ?>" value="<?= $i ?>" <?= $rvRating === $i ? 'checked' : '' ?> />
                <label for="rv-star-<?= $i ?>" title="<?= $i ?> star"><i class="fas fa-star"></i></label>
            <?php endfor; ?>
        </div>

        <div class="mb-3">
            <label class="rv-label" for="rvSubtitle">আপনার পরিচয় <small>(ঐচ্ছিক — নামের নিচে দেখাবে)</small></label>
            <input type="text" class="rv-input" id="rvSubtitle" maxlength="80" value="<?= e($rv->Subtitle ?? '') ?>" placeholder="<?= e($rvHint) ?>" />
        </div>

        <label class="rv-label" for="rvContent">আপনার অভিজ্ঞতা লিখুন</label>
        <textarea class="rv-input" id="rvContent" maxlength="500" placeholder="মাষ্টারমশাই ব্যবহার করে আপনার অভিজ্ঞতা কেমন? (২০–৫০০ অক্ষর)"><?= e($rv->Content ?? '') ?></textarea>
        <div class="rv-count"><span id="rvCount">0</span>/500</div>

        <button type="submit" class="rv-btn" id="rvBtn">
            <i class="fas fa-paper-plane"></i> <?= $rv ? 'Update Review' : 'Submit Review' ?>
        </button>
        <p class="rv-tip"><i class="fas fa-info-circle"></i> Submit করলেই রিভিউ সাথে সাথে হোম পেজে দেখাবে। পরে চাইলে এখান থেকে এডিট করতে পারবেন।</p>
    </form>
</div>

<script>
    (function () {
        var ta = document.getElementById('rvContent'), cnt = document.getElementById('rvCount');
        function upd() { cnt.textContent = ta.value.length; }
        ta.addEventListener('input', upd); upd();
    })();

    function rvSubmit(e) {
        e.preventDefault();
        var content = document.getElementById('rvContent').value.trim();
        if (content.length < 20) { alert('রিভিউ অন্তত ২০ অক্ষরের হতে হবে।'); return false; }
        var star = document.querySelector('#rvForm input[name="rating"]:checked');
        var fd = new FormData();
        fd.append('rating', star ? star.value : 5);
        fd.append('subtitle', document.getElementById('rvSubtitle').value.trim());
        fd.append('content', content);
        var tok = document.querySelector('input[name="__RequestVerificationToken"]');
        if (tok) fd.append('__RequestVerificationToken', tok.value);

        var btn = document.getElementById('rvBtn'), html = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        fetch('/Review/Submit', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                alert(d.message || (d.success ? 'Done' : 'Failed'));
                if (d.success) location.reload(); else { btn.disabled = false; btn.innerHTML = html; }
            })
            .catch(function (err) { alert('Error: ' + err.message); btn.disabled = false; btn.innerHTML = html; });
        return false;
    }
</script>
