<?php
/**
 * টিচার প্রোফাইলের বাম পাশের ডিফল্ট প্রোমো কার্ড ("আরও বেশি শিক্ষার্থীর কাছে পৌঁছান")।
 * অ্যাডমিন সেটিংসে ছবি না দিলে এটা দেখায়। দরকার: $href (বাটনের লিংক), $preview (bool, অ্যাডমিন প্রিভিউ)
 */
$href = $href ?? '/FindTutor';
$preview = $preview ?? false;
$ext = preg_match('#^https?://#i', $href);
?>
<style>
    .tpc { position: relative; overflow: hidden; border-radius: 16px; border: 1px solid #E3ECFB; padding: 20px 18px 18px; min-height: 250px;
        background: radial-gradient(120% 90% at 100% 0%, #E6F0FF 0%, rgba(230,240,255,0) 60%), linear-gradient(160deg, #F7FAFF 0%, #EEF4FF 100%);
        display: flex; flex-direction: column; box-shadow: 0 1px 2px rgba(15,27,45,.04), 0 4px 16px rgba(15,27,45,.04); }
    .tpc h5 { position: relative; z-index: 1; margin: 0; font-size: 21px; line-height: 1.4; font-weight: 800; color: #0B3B8C; max-width: 72%; }
    .tpc-art { position: absolute; right: 6px; bottom: 66px; width: 112px; height: 112px; z-index: 0; }
    .tpc-art .board { position: absolute; right: 6px; top: 0; width: 82px; height: 58px; border-radius: 8px; background: #1F3B63; border: 4px solid #C58B4C;
        box-shadow: 0 6px 14px rgba(15,27,45,.18); }
    .tpc-art .board::before { content: 'a²+b²'; position: absolute; left: 8px; top: 9px; font: 700 12px/1 Georgia, serif; color: #E7F2FF; letter-spacing: .5px; }
    .tpc-art .board::after { content: ''; position: absolute; left: 8px; right: 10px; bottom: 12px; height: 2px; background: rgba(231,242,255,.55); box-shadow: 0 -8px 0 rgba(231,242,255,.35); }
    .tpc-art .who { position: absolute; left: 4px; bottom: 0; font-size: 52px; line-height: 1; filter: drop-shadow(0 6px 10px rgba(29,99,224,.2)); }
    .tpc-art .bk { position: absolute; right: 0; bottom: 4px; font-size: 26px; }
    .tpc p { position: relative; z-index: 1; margin: 10px 0 0; font-size: 13px; color: #4B5E7A; max-width: 54%; line-height: 1.5; }
    .tpc-btn { position: relative; z-index: 1; margin-top: auto; align-self: stretch; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        height: 42px; padding: 0 16px; border-radius: 11px; background: #1D63E0; color: #fff !important; font-size: 14px; font-weight: 700; text-decoration: none;
        box-shadow: 0 6px 16px rgba(29,99,224,.28); transition: background .15s, transform .15s; }
    .tpc-btn:hover { background: #1853BF; transform: translateY(-1px); }
</style>
<div class="tpc">
    <h5>আরও বেশি শিক্ষার্থীর কাছে পৌঁছান</h5>
    <p>টিউশন পোস্ট দিন — স্টুডেন্টরা আপনাকে খুঁজে পাবে।</p>
    <div class="tpc-art" aria-hidden="true"><span class="board"></span><span class="who">👩‍🏫</span><span class="bk">📚</span></div>
    <?php if ($preview): ?>
        <span class="tpc-btn">Create Tuition Post <i class="fas fa-arrow-right"></i></span>
    <?php else: ?>
        <a class="tpc-btn" href="<?= e($href) ?>"<?= $ext ? ' target="_blank" rel="noopener"' : '' ?>>Create Tuition Post <i class="fas fa-arrow-right"></i></a>
    <?php endif; ?>
</div>
