<?php
/**
 * রেফারেল লিংক (হাইলাইট করা) + কপি + সোশ্যাল মিডিয়ায় শেয়ার বাটন — প্রোফাইলের কার্ড আর /Referral পেজে একই
 * @var string $code  @var int $bonus  @var bool $compact
 */
$link = site_public_url() . '/?ref=' . rawurlencode($code);
$msg = "মাষ্টার মশাই — সহজে যাচাই করা টিউটর খুঁজুন বা টিউশন পান। আমার লিংক দিয়ে ফ্রি অ্যাকাউন্ট খুলুন: $link";
$enc = rawurlencode($link); $encMsg = rawurlencode($msg);
$compact = !empty($compact);
$uid = 'rf' . substr(md5($code . ($compact ? 'c' : 'f')), 0, 6);
?>
<div class="rf-share<?= $compact ? ' compact' : '' ?>" id="<?= $uid ?>" data-link="<?= e($link) ?>" data-msg="<?= e($msg) ?>">
    <div class="rf-linkbox">
        <span class="rf-tag"><i class="fas fa-link"></i> আপনার রেফারেল লিংক</span>
        <div class="rf-link"><input type="text" readonly value="<?= e($link) ?>" aria-label="Referral link" onclick="this.select()" /><button type="button" class="rf-copy" data-rf="copy"><i class="far fa-copy"></i> <span>Copy</span></button></div>
        <p class="rf-earn"><i class="fas fa-gift"></i> প্রতিজন বন্ধু এই লিংক দিয়ে সাইনআপ করলে আপনি পাবেন <b>৳<?= (int)$bonus ?></b> বোনাস!</p>
    </div>
    <div class="rf-socials" aria-label="শেয়ার করুন">
        <a class="wa" href="https://wa.me/?text=<?= $encMsg ?>" target="_blank" rel="noopener" title="WhatsApp"><i class="fab fa-whatsapp"></i><span>WhatsApp</span></a>
        <a class="fb" href="https://www.facebook.com/sharer/sharer.php?u=<?= $enc ?>" target="_blank" rel="noopener" title="Facebook"><i class="fab fa-facebook-f"></i><span>Facebook</span></a>
        <a class="ms" href="fb-messenger://share/?link=<?= $enc ?>" data-rf="messenger" title="Messenger"><i class="fab fa-facebook-messenger"></i><span>Messenger</span></a>
        <a class="ig" href="#" data-rf="instagram" title="Instagram"><i class="fab fa-instagram"></i><span>Instagram</span></a>
        <a class="tg" href="https://t.me/share/url?url=<?= $enc ?>&amp;text=<?= rawurlencode('মাষ্টার মশাই — আমার লিংক দিয়ে ফ্রি অ্যাকাউন্ট খুলুন') ?>" target="_blank" rel="noopener" title="Telegram"><i class="fab fa-telegram"></i><span>Telegram</span></a>
        <a class="x" href="https://twitter.com/intent/tweet?text=<?= $encMsg ?>" target="_blank" rel="noopener" title="X"><i class="fab fa-x-twitter"></i><span>X</span></a>
        <a class="li" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $enc ?>" target="_blank" rel="noopener" title="LinkedIn"><i class="fab fa-linkedin-in"></i><span>LinkedIn</span></a>
        <a class="more" href="#" data-rf="more" title="আরো"><i class="fas fa-share-nodes"></i><span>More</span></a>
    </div>
</div>
<?php if (empty($GLOBALS['__rf_share_once'])): $GLOBALS['__rf_share_once'] = 1; ?>
<style>
    .rf-linkbox { background: linear-gradient(135deg, #E6F9FF, #FFF7D6); border: 2px dashed #00CFFF; border-radius: 14px; padding: 14px; }
    .rf-tag { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; color: #0369A1; text-transform: uppercase; letter-spacing: .3px; }
    .rf-link { display: flex; gap: 8px; margin-top: 8px; }
    .rf-link input { flex: 1; min-width: 0; height: 42px; border: 1.5px solid #00CFFF; border-radius: 10px; padding: 0 12px; font-weight: 700; font-size: 14px; color: #0F172A; background: #fff; outline: none; }
    .rf-copy { flex-shrink: 0; height: 42px; padding: 0 14px; border: none; border-radius: 10px; background: #00CFFF; color: #fff; font-weight: 800; font-size: 13.5px; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px; }
    .rf-copy:hover { background: #00B5E0; } .rf-copy.ok { background: #16A34A; }
    .rf-earn { margin: 10px 0 0; font-size: 13.5px; color: #92400E; font-weight: 600; }
    .rf-earn b { font-size: 16px; color: #B45309; } .rf-earn i { color: #F59E0B; }
    .rf-socials { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-top: 12px; }
    .rf-socials a { display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 9px 4px; border-radius: 10px; background: #F6F9FC; text-decoration: none; color: #334155; font-size: 11.5px; font-weight: 600; transition: transform .15s, background .15s; }
    .rf-socials a:hover { transform: translateY(-2px); background: #EEF4FA; }
    .rf-socials i { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; }
    .rf-socials .wa i { background: #25D366; } .rf-socials .fb i { background: #1877F2; } .rf-socials .ms i { background: linear-gradient(135deg, #00B2FF, #A033FF); }
    .rf-socials .ig i { background: radial-gradient(circle at 30% 107%, #FDF497 0%, #FD5949 45%, #D6249F 60%, #285AEB 90%); } .rf-socials .tg i { background: #229ED9; }
    .rf-socials .x i { background: #0F172A; } .rf-socials .li i { background: #0A66C2; } .rf-socials .more i { background: #64748B; }
    .rf-share.compact .rf-link { flex-direction: column; }
    .rf-share.compact .rf-link input { font-size: 12.5px; text-overflow: ellipsis; }
    .rf-share.compact .rf-copy { justify-content: center; height: 38px; }
    .rf-share.compact .rf-socials span { display: none; }
    .rf-share.compact .rf-socials a { padding: 6px 2px; }
    .rf-toast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; z-index: 3000; opacity: 0; transition: opacity .25s; pointer-events: none; max-width: 92vw; text-align: center; }
</style>
<script>
(function () {
    var mobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    function toast(m) { var t = document.querySelector('.rf-toast'); if (!t) { t = document.createElement('div'); t.className = 'rf-toast'; document.body.appendChild(t); } t.textContent = m; t.style.opacity = 1; clearTimeout(t._h); t._h = setTimeout(function () { t.style.opacity = 0; }, 2800); }
    function copy(text) {
        if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text);
        var ta = document.createElement('textarea'); ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = 0; document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } finally { ta.remove(); } return Promise.resolve();
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-rf]'); if (!b) return;
        var box = b.closest('.rf-share'), link = box.getAttribute('data-link'), msg = box.getAttribute('data-msg'), k = b.getAttribute('data-rf');
        if (k === 'messenger' && mobile) return; // মোবাইলে Messenger অ্যাপ খোলে
        e.preventDefault();
        if (k === 'more' && navigator.share) { navigator.share({ title: 'মাষ্টার মশাই', text: msg, url: link }).catch(function () {}); return; }
        copy(k === 'copy' ? link : msg).then(function () {
            if (k === 'copy') { var s = b.querySelector('span'); b.classList.add('ok'); s.textContent = 'Copied!'; setTimeout(function () { b.classList.remove('ok'); s.textContent = 'Copy'; }, 1800); toast('লিংক কপি হয়েছে ✅'); }
            else if (k === 'instagram') { toast('লেখা আর লিংক কপি হয়েছে — Instagram এ পেস্ট করুন'); if (mobile) setTimeout(function () { location.href = 'instagram://direct-inbox'; }, 600); }
            else if (k === 'messenger') toast('লিংক কপি হয়েছে — Messenger এ পেস্ট করে পাঠান');
            else toast('লেখা আর লিংক কপি হয়েছে — যেকোনো জায়গায় পেস্ট করুন');
        });
    });
})();
</script>
<?php endif; ?>
