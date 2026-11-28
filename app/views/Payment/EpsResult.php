<?php
/** EPS থেকে ফেরার পর — পেমেন্ট সফল / বাতিল / ব্যর্থ (অবস্থা সার্ভার থেকে EPS এ যাচাই করা) */
$row = $Model;
$ViewData['Title'] = 'Payment';
$o = $row->OrderData;
$name = (string)($o['pkg']['Name'] ?? '');
$st = (string)$row->Status;
$back = !empty($o['courseId']) ? (($o['pkg']['Audience'] ?? '') === 'Publish' ? '/Payment/Checkout?publish=' . (int)$o['courseId'] : '/Payment/Checkout?course=' . (int)$o['courseId']) : '/Payment/Checkout/' . (int)($o['pkg']['Id'] ?? 0);
$v = [
    'Paid' => ['ok', 'fa-circle-check', 'পেমেন্ট সফল হয়েছে!', 'আপনার টাকা EPS-এ পরিশোধ হয়ে গেছে আর প্যাকেজ চালু হয়ে গেছে। নতুন করে রিসিট দিতে হবে না।'],
    'Finalizing' => ['ok', 'fa-circle-check', 'পেমেন্ট সফল হয়েছে!', 'পেমেন্টটা সেভ হচ্ছে — কিছুক্ষণ পর “আমার পেমেন্ট” পেজে দেখতে পাবেন।'],
    'Cancelled' => ['warn', 'fa-circle-xmark', 'পেমেন্ট বাতিল করা হয়েছে', 'কোনো টাকা কাটা হয়নি। চাইলে আবার চেষ্টা করুন।'],
    'Failed' => ['bad', 'fa-triangle-exclamation', 'পেমেন্ট সম্পন্ন হয়নি', 'কোনো টাকা কাটা হয়ে থাকলে চিন্তা করবেন না — EPS থেকে যাচাই করে আমরা দেখে নেব। আবার চেষ্টা করতে পারেন, অথবা Helpdesk-এ জানান।'],
    'Mismatch' => ['bad', 'fa-triangle-exclamation', 'পেমেন্ট যাচাই করা হচ্ছে', 'পরিশোধ করা টাকার পরিমাণ মেলেনি — অ্যাডমিনকে জানানো হয়েছে, তাঁরা দেখে আপনার সাথে যোগাযোগ করবেন।'],
    'Initiated' => ['warn', 'fa-hourglass-half', 'পেমেন্ট এখনো নিশ্চিত হয়নি', 'EPS থেকে এখনো সফল হওয়ার খবর আসেনি। টাকা কেটে থাকলে কিছুক্ষণ পর “আমার পেমেন্ট” পেজ খুললে নিজে থেকেই যোগ হয়ে যাবে।'],
][$st] ?? ['warn', 'fa-hourglass-half', 'পেমেন্ট যাচাই হচ্ছে', ''];
?>
<style>
    .er-wrap { max-width: 620px; margin: 0 auto; padding: 40px 16px 70px; }
    .er-card { background: #fff; border: 1px solid #DCECF5; border-radius: 20px; padding: 30px 26px; text-align: center; box-shadow: 0 6px 24px rgba(8,34,74,.06); }
    .er-ic { width: 76px; height: 76px; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 36px; }
    .er-card.ok .er-ic { background: #DCFCE7; color: #15803D; }
    .er-card.warn .er-ic { background: #FEF3C7; color: #B45309; }
    .er-card.bad .er-ic { background: #FEE2E2; color: #B91C1C; }
    .er-card h1 { font-size: clamp(21px, 3vw, 26px); font-weight: 800; color: #08224A; margin: 0 0 8px; }
    .er-card p { color: #475569; font-size: 14.5px; line-height: 1.7; margin: 0 auto; max-width: 460px; }
    .er-rows { margin: 20px 0 0; border: 1px solid #E2E8F0; border-radius: 14px; text-align: left; overflow: hidden; }
    .er-rows div { display: flex; justify-content: space-between; gap: 12px; padding: 10px 14px; font-size: 13.5px; border-top: 1px solid #EEF2F7; }
    .er-rows div:first-child { border-top: none; }
    .er-rows span { color: #64748B; }
    .er-rows b { color: #08224A; word-break: break-all; text-align: right; }
    .er-acts { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 22px; }
    .er-acts a { display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; border-radius: 12px; font-weight: 700; font-size: 14px; text-decoration: none; }
    .er-acts .pri { background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; }
    .er-acts .sec { background: #EFF6FF; color: #1D7FE5; }
    .er-sb { display: inline-block; margin-bottom: 14px; padding: 4px 12px; border-radius: 20px; background: #FEF3C7; color: #92400E; font-size: 12px; font-weight: 800; letter-spacing: .3px; }
</style>
<div class="er-wrap">
    <div class="er-card <?= e($v[0]) ?>">
        <?php if (!empty($ViewBag->Sandbox)): ?><div class="er-sb"><i class="fas fa-flask"></i> SANDBOX — টেস্ট পেমেন্ট, আসল টাকা নয়</div><?php endif; ?>
        <div class="er-ic"><i class="fas <?= e($v[1]) ?>"></i></div>
        <h1><?= e($v[2]) ?></h1>
        <p><?= e($v[3]) ?></p>
        <div class="er-rows">
            <div><span>প্যাকেজ / কোর্স</span><b><?= e($name) ?></b></div>
            <div><span>টাকার পরিমাণ</span><b><?= e(taka($row->Amount)) ?></b></div>
            <?php if (!is_blank($row->FinancialEntity ?? null)): ?><div><span>পেমেন্ট মাধ্যম</span><b><?= e($row->FinancialEntity) ?></b></div><?php endif; ?>
            <div><span>রেফারেন্স</span><b><?= e($row->MerchantTxnId) ?></b></div>
        </div>
        <div class="er-acts">
            <?php if (in_array($st, ['Paid', 'Finalizing', 'Mismatch'], true)): ?>
                <a class="pri" href="/Payment/My<?= $row->PaymentRequestId ? '?new=' . (int)$row->PaymentRequestId : '' ?>"><i class="fas fa-receipt"></i> আমার পেমেন্ট</a>
            <?php else: ?>
                <a class="pri" href="<?= e($back) ?>"><i class="fas fa-rotate-right"></i> আবার চেষ্টা করুন</a>
                <a class="sec" href="/Payment/My"><i class="fas fa-receipt"></i> আমার পেমেন্ট</a>
            <?php endif; ?>
        </div>
    </div>
</div>
