<?php
/**
 * Educational Feed এর পোস্ট লেখার বক্স — Feed পেজ আর অফিসিয়াল প্রোফাইল পেজ দুই জায়গায় ব্যবহার হয়।
 * @var ?int $me @var bool $isAdmin @var string $myName @var string $myPhoto @var int $videoMb @var array $subjects @var array $opts @var string $returnUrl
 */
?>
<style>
    /* কম্পোজার + খালি অবস্থা + আরো পোস্ট বাটন (Feed আর অফিসিয়াল প্রোফাইল পেজে একই) */
    .efc { background: #fff; border: 1px solid #E3E9F1; border-radius: 16px; padding: 14px 16px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(15,27,45,.05); }
    .efc-top { display: flex; gap: 11px; align-items: flex-start; }
    .efc textarea { flex: 1; border: none; resize: none; outline: none; font-size: 16px; font-family: inherit; color: #0F172A; min-height: 46px; max-height: 320px; padding: 11px 14px; background: #F1F5F9; border-radius: 14px; line-height: 1.5; }
    .efc textarea:focus { background: #EEF6FC; }
    .efc-more[hidden] { display: none; }
    .efc-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 12px; }
    .efc-subj { flex: 1 1 200px; height: 40px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 12px; font-size: 14px; outline: none; font-family: inherit; color: #0F172A; }
    .efc-subj:focus, .efc-yt:focus { border-color: #02B8F5; }
    .efc-yt { width: 100%; height: 40px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 12px; font-size: 14px; outline: none; font-family: inherit; margin-top: 10px; }
    .efc-tools { display: flex; gap: 6px; flex-wrap: wrap; border-top: 1px solid #EEF2F7; margin-top: 12px; padding-top: 10px; align-items: center; }
    .efc-tools label, .efc-tools button.t { display: inline-flex; align-items: center; gap: 7px; padding: 8px 12px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 13.5px; color: #475569; margin: 0; border: none; background: none; font-family: inherit; }
    .efc-tools label:hover, .efc-tools button.t:hover { background: #F1F5F9; }
    .efc-tools .sp { flex: 1; }
    .efc-send { height: 40px; padding: 0 22px; border-radius: 10px; border: none; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; font-weight: 700; font-size: 14.5px; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 8px; }
    .efc-send:disabled { opacity: .6; cursor: default; }
    .efc-prev { display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: 8px; margin-top: 12px; }
    .efc-prev:empty { display: none; }
    .efc-prev .it { position: relative; aspect-ratio: 1; border-radius: 10px; overflow: hidden; background: #E2E8F0; }
    .efc-prev .it.vid { grid-column: 1 / -1; aspect-ratio: auto; background: #000; }
    .efc-prev img, .efc-prev video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .efc-prev video { max-height: 280px; object-fit: contain; }
    .efc-prev .rm { position: absolute; top: 5px; right: 5px; width: 26px; height: 26px; border-radius: 50%; border: none; background: rgba(15,23,42,.75); color: #fff; cursor: pointer; font-size: 12px; z-index: 2; }
    .efc-note { font-size: 12.5px; color: #64748B; margin-top: 10px; display: flex; gap: 7px; align-items: flex-start; }
    .efc-note i { color: #F5A623; margin-top: 3px; }
    .efc-bar { height: 6px; border-radius: 4px; background: #E2E8F0; overflow: hidden; margin-top: 10px; }
    .efc-bar[hidden] { display: none; }
    .efc-bar span { display: block; height: 100%; width: 0; background: linear-gradient(90deg, #1D7FE5, #02B8F5); transition: width .2s; }
    .efc-login { display: flex; gap: 14px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .efc-login p { margin: 0; color: #334155; font-size: 15px; }
    .efc-login a { padding: 10px 18px; border-radius: 10px; background: #08224A; color: #fff; text-decoration: none; font-weight: 700; font-size: 14px; white-space: nowrap; }
    .efp-empty { background: #fff; border: 1px dashed #CBD5E1; border-radius: 16px; padding: 44px 20px; text-align: center; color: #64748B; }
    .efp-empty i { font-size: 38px; color: #B9D6E6; display: block; margin-bottom: 12px; }
    .efp-load { display: block; margin: 6px auto 0; padding: 11px 26px; border-radius: 999px; border: 1px solid #DCE5EF; background: #fff; color: #08224A; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; }
    .efp-load:disabled { opacity: .6; }
    @media (max-width: 640px) {
        .efc { padding: 12px; border-radius: 12px; }
        .efc textarea { font-size: 15px; }
        .efc-tools label, .efc-tools button.t { padding: 8px; font-size: 13px; }
        .efc-tools .lbl { display: none; }
        .efc-tools .lbl { display: none; }
    }
</style>
<?php if (!empty($cssOnly)) return; /* শুধু স্টাইল (যেমন প্রোফাইল পেজে, যেখানে লেখার বক্স নেই) */ ?>
<?php if ($me !== null): ?>
    <form class="efc" id="efc" autocomplete="off" novalidate>
        <div class="efc-top">
            <span class="ef-av"><?= e(initials($myName, 'U')) ?><?php if ($myPhoto !== ''): ?><img src="<?= e($myPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
            <textarea name="content" id="efcText" rows="1" maxlength="5000" placeholder="কী শেখাতে বা শেয়ার করতে চান, <?= e(explode(' ', trim($myName))[0] ?: 'বন্ধু') ?>?" aria-label="পোস্ট লিখুন"></textarea>
        </div>
        <div class="efc-more" id="efcMore" hidden>
            <div class="efc-row">
                <input class="efc-subj" name="subject" list="efcSubjList" maxlength="60" placeholder="বিষয় (ঐচ্ছিক) — যেমন: Mathematics, Physics" aria-label="বিষয়" />
                <datalist id="efcSubjList"><?php foreach (array_unique(array_merge($subjects, $opts['subjects'] ?? [])) as $s): ?><option value="<?= e($s) ?>"></option><?php endforeach; ?></datalist>
            </div>
            <?php if ($isAdmin): /* ভিডিও / YouTube শুধু অ্যাডমিন অ্যাকাউন্ট থেকে */ ?>
            <input class="efc-yt" name="youtube" id="efcYt" maxlength="200" placeholder="YouTube ভিডিওর লিংক — https://youtu.be/..." hidden aria-label="YouTube লিংক" />
            <input class="efc-yt" name="facebook" id="efcFb" maxlength="300" placeholder="Facebook ভিডিওর লিংক — https://www.facebook.com/.../videos/... বা https://fb.watch/..." hidden aria-label="Facebook লিংক" />
            <?php endif; ?>
            <div class="efc-prev" id="efcPrev"></div>
            <div class="efc-bar" id="efcBar" hidden><span></span></div>
            <?php if (!$isAdmin): ?><div class="efc-note"><i class="fas fa-shield-halved"></i><span>ছবি আর লেখা পোস্ট করা যায়। পোস্টটি আগে অ্যাডমিনের কাছে রিভিউর জন্য যাবে — Approve হলে সবাই দেখতে পাবে। শুধু শিক্ষামূলক পোস্ট দিন।</span></div><?php endif; ?>
        </div>
        <div class="efc-tools">
            <label title="ছবি (সর্বোচ্চ ৬টা, প্রতিটা ৫MB)"><i class="fas fa-images" style="color:#22C55E;"></i><span class="lbl">Photo</span><input type="file" id="efcImg" accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden /></label>
            <?php if ($isAdmin): ?>
            <label title="ভিডিও (MP4/WEBM, সর্বোচ্চ <?= $videoMb ?>MB)"><i class="fas fa-video" style="color:#EF4444;"></i><span class="lbl">Video</span><input type="file" id="efcVid" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v" hidden /></label>
            <button type="button" class="t" id="efcYtBtn" title="YouTube লিংক"><i class="fab fa-youtube" style="color:#FF0000;"></i><span class="lbl">YouTube</span></button>
            <button type="button" class="t" id="efcFbBtn" title="Facebook ভিডিওর লিংক"><i class="fab fa-facebook" style="color:#1877F2;"></i><span class="lbl">Facebook</span></button>
            <?php endif; ?>
            <span class="sp"></span>
            <button type="submit" class="efc-send" id="efcSend"><i class="fas fa-paper-plane"></i> <?= $isAdmin ? 'Post' : 'Submit' ?></button>
        </div>
    </form>
<?php else: ?>
    <div class="efc efc-login">
        <p><i class="fas fa-pen-to-square" style="color:#02B8F5;"></i> নিজের নোট বা ছবি শেয়ার করতে লগইন করুন।</p>
        <a href="/Account/Login?returnUrl=<?= rawurlencode($returnUrl ?? '/Feed') ?>">Login</a>
    </div>
<?php endif; ?>

