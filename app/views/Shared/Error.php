<?php
$is404 = (int)($Model->StatusCode ?? 0) === 404;
$ViewData['Title'] = $is404 ? 'Page Not Found' : 'Error';
// কোন ঠিকানাটা খোলার চেষ্টা হয়েছিল (শুধু path — ?token= ইত্যাদি গোপন অংশ দেখানো হয় না)
$reqPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
?>
<style>
    .err-wrap { min-height: 62vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px; background: linear-gradient(160deg, #CFF2FC 0%, #F5FBFF 60%, #fff 100%); }
    .err-card { width: 100%; max-width: 480px; background: #fff; border: 1px solid #DCECF5; border-radius: 22px; box-shadow: 0 18px 50px rgba(8,34,74,.10); padding: 34px 26px; text-align: center; }
    .err-code { font-family: var(--font-heading, inherit); font-size: 64px; font-weight: 800; line-height: 1; color: #02B8F5; margin-bottom: 6px; }
    .err-card h1 { font-size: 21px; font-weight: 800; color: #08224A; margin: 0 0 8px; }
    .err-card p { color: #64748B; font-size: 14.5px; line-height: 1.6; margin: 0 0 6px; }
    .err-path { display: inline-block; max-width: 100%; margin: 10px 0 4px; background: #F5FBFF; border: 1px dashed #B9D6E6; border-radius: 10px; padding: 7px 12px; font-family: monospace; font-size: 13px; color: #08224A; word-break: break-all; }
    .err-rid { font-size: 12.5px; color: #7A8CA0; margin-top: 8px; }
    .err-btns { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 22px; }
    .err-btn { display: inline-flex; align-items: center; gap: 8px; border-radius: 12px; padding: 11px 22px; font-weight: 700; font-size: 14.5px; text-decoration: none; }
    .err-btn.primary { background: #02B8F5; color: #fff; box-shadow: 0 10px 22px rgba(2,184,245,.28); }
    .err-btn.primary:hover { background: #0099D6; color: #fff; }
    .err-btn.ghost { border: 1.5px solid #DCECF5; color: #08224A; background: #fff; }
    .err-btn.ghost:hover { border-color: #02B8F5; }
</style>

<div class="err-wrap">
    <div class="err-card">
        <?php if ($is404): ?>
            <div class="err-code">404</div>
            <h1>পেজটি পাওয়া যায়নি</h1>
            <p>আপনি যে পেজটি খুঁজছেন সেটি নেই, সরানো হয়েছে অথবা লিংকটি ভুল।</p>
            <div class="err-path"><?= e($reqPath) ?></div>
        <?php else: ?>
            <div class="err-code" style="color:#e74c3c;"><i class="fas fa-triangle-exclamation" style="font-size:52px;"></i></div>
            <h1>কিছু একটা সমস্যা হয়েছে</h1>
            <p>An error occurred while processing your request.</p>
            <?php if (!empty($Model->Message)): ?><div class="err-path"><?= e($Model->Message) ?></div><?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($Model->RequestId)): ?><div class="err-rid">Request ID: <code><?= e($Model->RequestId) ?></code></div><?php endif; ?>
        <div class="err-btns">
            <a href="/" class="err-btn primary"><i class="fas fa-home"></i> Go to Home</a>
            <a href="javascript:history.back()" class="err-btn ghost"><i class="fas fa-arrow-left"></i> Go Back</a>
        </div>
    </div>
</div>
