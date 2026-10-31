<?php
/**
 * পেমেন্ট পেজ: ১) মেথড বাছাই (Bank Transfer / bKash / Nagad / Rocket / Other)
 *              ২) QR স্ক্যান করে / অ্যাকাউন্টে টাকা পাঠানো
 *              ৩) Payment details — Transaction ID, তারিখ, রিসিট, নোট → জমা
 */
$pkg = $Model;
$ViewData['Title'] = 'Payment';
$methods = $ViewBag->Methods ?? [];
$block = $ViewBag->BlockReason;
$pending = (int)($ViewBag->PendingCount ?? 0);
$today = date('Y-m-d');
$jsMethods = array_map(function ($m) {
    return ['id' => $m->Id, 'name' => $m->Name, 'kind' => $m->Kind, 'txnRequired' => $m->TxnRequired, 'instructions' => (string)$m->Instructions,
        'accounts' => array_map(fn($a) => ['id' => $a->Id, 'title' => $a->Title, 'accountName' => (string)$a->AccountName, 'accountNumber' => (string)$a->AccountNumber,
            'bankName' => (string)$a->BankName, 'branch' => (string)$a->Branch, 'routing' => (string)$a->RoutingNumber, 'accountType' => (string)$a->AccountType,
            'qr' => (string)$a->QrImageUrl], $m->Accounts)];
}, $methods);
?>
<?= payment_logo_css() ?>
<style>
    .co-wrap { max-width: 1100px; margin: 0 auto; padding: 22px 16px 60px; }
    .co-back { display: inline-flex; align-items: center; gap: 7px; font-size: 13.5px; font-weight: 600; color: #475569; text-decoration: none; margin-bottom: 14px; }
    .co-back:hover { color: #1D7FE5; }
    .co-h1 { font-size: clamp(22px, 3vw, 28px); font-weight: 800; color: #08224A; margin: 0 0 4px; }
    .co-lead { color: #64748B; font-size: 14.5px; margin: 0 0 20px; }
    .co-grid { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 20px; align-items: start; }
    .co-card { background: #fff; border: 1px solid #DCECF5; border-radius: 18px; padding: 20px; box-shadow: 0 4px 18px rgba(8,34,74,.05); }
    .co-card + .co-card { margin-top: 16px; }
    .co-step { display: flex; align-items: center; gap: 10px; margin: 0 0 14px; font-size: 16.5px; font-weight: 800; color: #08224A; }
    .co-step b { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; font-size: 13.5px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

    .co-methods { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
    .co-m { position: relative; border: 2px solid #E2E8F0; background: #fff; border-radius: 14px; padding: 14px 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; min-height: 70px; transition: border-color .2s, box-shadow .2s, transform .15s; }
    .co-m:hover { border-color: #9EC2FA; }
    .co-m.on { border-color: #1D7FE5; box-shadow: 0 0 0 4px rgba(29,127,229,.12); }
    .co-m.on::after { content: '\f00c'; font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free'; font-weight: 900; position: absolute; top: -8px; right: -8px; width: 22px; height: 22px; border-radius: 50%; background: #1D7FE5; color: #fff; font-size: 11px; display: flex; align-items: center; justify-content: center; }

    .co-pay { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 20px; align-items: start; }
    .co-qr { text-align: center; }
    .co-qr-img { display: block; width: 100%; aspect-ratio: 1; border-radius: 16px; border: 1px solid #DCECF5; background: #fff; padding: 8px; cursor: zoom-in; overflow: hidden; }
    .co-qr-img img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .co-qr-acts { display: flex; gap: 8px; justify-content: center; margin-top: 10px; }
    .co-qr-acts a, .co-qr-acts button { font-size: 12.5px; font-weight: 700; color: #1D7FE5; background: #EFF6FF; border: none; border-radius: 9px; padding: 7px 12px; text-decoration: none; cursor: pointer; display: inline-flex; gap: 6px; align-items: center; }
    .co-noqr { padding: 30px 12px; border: 2px dashed #DCECF5; border-radius: 16px; color: #94A3B8; font-size: 13px; }
    .co-accsel { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
    .co-accsel button { border: 1.5px solid #DCECF5; background: #fff; border-radius: 30px; padding: 7px 14px; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; }
    .co-accsel button.on { border-color: #1D7FE5; background: #EFF6FF; color: #1D7FE5; }
    .co-amount { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 16px; border-radius: 14px; background: linear-gradient(135deg, #08224A, #134856); color: #fff; margin-bottom: 12px; }
    .co-amount small { display: block; font-size: 12px; color: #CFF2FC; }
    .co-amount b { font-size: 26px; font-weight: 800; font-family: var(--font-heading); }
    .co-rows { border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; }
    .co-row { display: flex; align-items: center; gap: 10px; padding: 10px 14px; font-size: 13.5px; border-top: 1px solid #EEF2F7; }
    .co-row:first-child { border-top: none; }
    .co-row span { color: #64748B; min-width: 110px; }
    .co-row b { color: #08224A; font-weight: 700; word-break: break-all; flex: 1; }
    .co-copy { border: none; background: #F1F5F9; color: #475569; border-radius: 8px; padding: 5px 10px; font-size: 12px; font-weight: 700; cursor: pointer; flex-shrink: 0; }
    .co-copy.done { background: #DCFCE7; color: #15803D; }
    .co-ins { margin-top: 12px; padding: 12px 14px; border-radius: 12px; background: #F0F9FF; border: 1px solid #CFE8FA; color: #0C4A6E; font-size: 13.5px; line-height: 1.65; display: flex; gap: 9px; }
    .co-ins i { margin-top: 3px; color: #0284C7; }

    .co-f { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .co-f .full { grid-column: 1 / -1; }
    .co-f label { display: block; font-size: 13.5px; font-weight: 700; color: #1E293B; margin-bottom: 6px; }
    .co-f label .req { color: #DC2626; }
    .co-f input[type=text], .co-f input[type=date], .co-f textarea { width: 100%; border: 1.5px solid #DCECF5; border-radius: 12px; padding: 11px 14px; font-size: 14.5px; outline: none; background: #F8FBFE; font-family: inherit; }
    .co-f input:focus, .co-f textarea:focus { border-color: #1D7FE5; background: #fff; box-shadow: 0 0 0 3px rgba(29,127,229,.10); }
    .co-f .bad input, .co-f .bad .co-drop { border-color: #DC2626 !important; }
    .co-f textarea { min-height: 80px; resize: vertical; }
    .co-hint { margin-top: 6px; font-size: 12.5px; color: #64748B; line-height: 1.55; }
    .co-hint.warn { color: #9A5B00; background: #FFF8E6; border-radius: 9px; padding: 7px 10px; }
    .co-f .co-drop { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; min-height: 130px; border: 2px dashed #B9D6E6; border-radius: 14px; background: #F8FBFE; cursor: pointer; text-align: center; padding: 14px; transition: border-color .2s, background .2s; }
    .co-drop:hover, .co-drop.drag { border-color: #1D7FE5; background: #EFF6FF; }
    .co-drop i.big { font-size: 28px; color: #1D7FE5; }
    .co-drop b { font-size: 14px; color: #08224A; }
    .co-drop small { color: #64748B; font-size: 12.5px; }
    .co-drop input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .co-prev { display: flex; align-items: center; gap: 12px; padding: 10px; border: 1.5px solid #DCECF5; border-radius: 14px; background: #fff; }
    .co-prev[hidden], .co-f .co-drop[hidden], .co-accsel[hidden], .co-ins[hidden], .co-hint[hidden] { display: none; }
    .co-prev img { width: 70px; height: 70px; object-fit: cover; border-radius: 10px; border: 1px solid #E2E8F0; }
    .co-prev .pdf { width: 70px; height: 70px; border-radius: 10px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .co-prev div { flex: 1; min-width: 0; font-size: 13px; color: #334155; word-break: break-all; }
    .co-prev button { border: none; background: #FEF2F2; color: #DC2626; border-radius: 9px; padding: 7px 11px; cursor: pointer; font-weight: 700; font-size: 12.5px; }
    .co-err { display: none; margin-top: 14px; padding: 10px 14px; border-radius: 12px; background: #FEF2F2; color: #B91C1C; font-size: 13.5px; font-weight: 600; }
    .co-err.show { display: block; }
    .co-submit { margin-top: 18px; width: 100%; border: none; border-radius: 14px; padding: 14px; font-size: 16px; font-weight: 800; color: #fff; background: linear-gradient(135deg, #1D7FE5, #02B8F5); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 9px; box-shadow: 0 10px 24px rgba(29,127,229,.25); }
    .co-submit:disabled { opacity: .65; cursor: default; }

    .co-sum { position: sticky; top: 96px; }
    .co-sum h3 { font-size: 15px; font-weight: 800; color: #08224A; margin: 0 0 12px; }
    .co-pkg { padding: 14px; border-radius: 14px; background: #F5F9FF; border: 1px solid #DCE9FB; }
    .co-pkg .aud { font-size: 11.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; color: #1D7FE5; }
    .co-pkg .nm { font-size: 19px; font-weight: 800; color: #08224A; margin-top: 2px; }
    .co-pkg .du { font-size: 13px; color: #64748B; margin-top: 2px; }
    .co-line { display: flex; justify-content: space-between; font-size: 14px; color: #475569; padding: 10px 0; border-bottom: 1px dashed #E2E8F0; }
    .co-line.tot { border: none; font-size: 16px; color: #08224A; font-weight: 800; }
    .co-line s { color: #94A3B8; }
    .co-how { list-style: none; padding: 0; margin: 14px 0 0; display: flex; flex-direction: column; gap: 10px; }
    .co-how li { display: flex; gap: 10px; font-size: 13px; color: #475569; line-height: 1.5; }
    .co-how li i { color: #1D7FE5; width: 16px; margin-top: 3px; }
    .co-alert { padding: 14px 16px; border-radius: 14px; font-size: 14px; line-height: 1.6; display: flex; gap: 10px; margin-bottom: 16px; }
    .co-alert.warn { background: #FFF8E6; border: 1px solid #F8E1A5; color: #7A4B00; }
    .co-alert.info { background: #EFF6FF; border: 1px solid #CFE0FB; color: #1E3A8A; }
    .co-alert a { font-weight: 700; }

    .co-lb { position: fixed; inset: 0; z-index: 3000; background: rgba(2,6,23,.82); display: flex; align-items: center; justify-content: center; padding: 18px; cursor: zoom-out; }
    .co-lb[hidden] { display: none; }
    .co-lb img { max-width: min(520px, 100%); max-height: 90vh; border-radius: 16px; background: #fff; padding: 10px; }

    @media (max-width: 900px) {
        .co-grid { grid-template-columns: 1fr; }
        .co-sum { position: static; order: -1; }
    }
    @media (max-width: 640px) {
        .co-pay { grid-template-columns: 1fr; }
        .co-qr { max-width: 260px; margin: 0 auto; width: 100%; }
        .co-f { grid-template-columns: 1fr; }
        .co-row span { min-width: 90px; }
        .co-methods { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="co-wrap">
    <a class="co-back" href="/Package?for=<?= $pkg->Audience === 'Student' ? 'student' : 'teacher' ?>"><i class="fas fa-arrow-left"></i> সব প্যাকেজ</a>
    <h1 class="co-h1">পেমেন্ট করুন</h1>
    <p class="co-lead">QR কোড স্ক্যান করে টাকা পাঠান, তারপর পেমেন্টের প্রমাণ জমা দিন — অ্যাডমিন যাচাই করে Accept করলেই প্যাকেজ চালু হবে।</p>

    <?php if ($block): ?>
        <div class="co-alert warn"><i class="fas fa-exclamation-triangle" style="margin-top:3px;"></i><span><?= e($block) ?>
            <?php if ($pkg->Audience === 'Teacher'): ?> <a href="/Profile/Show?tab=profile#ab-tutor">Become a Tutor</a><?php endif; ?></span></div>
    <?php endif; ?>
    <?php if ($pending > 0): ?>
        <div class="co-alert info"><i class="fas fa-hourglass-half" style="margin-top:3px;"></i><span>আপনার <?= e(bn_num($pending)) ?>টি পেমেন্ট যাচাইয়ের অপেক্ষায় আছে। <a href="/Payment/My">আমার পেমেন্ট দেখুন</a></span></div>
    <?php endif; ?>

    <div class="co-grid">
        <div>
            <?php if (!$methods): ?>
                <div class="co-card" style="text-align:center;color:#64748B;"><i class="fas fa-wallet fa-2x"></i><p style="margin:10px 0 0;">এখনো কোনো পেমেন্ট মেথড যোগ করা হয়নি। Helpdesk-এ যোগাযোগ করুন।</p></div>
            <?php else: ?>
            <form id="coForm" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="packageId" value="<?= (int)$pkg->Id ?>" />
                <input type="hidden" name="methodId" id="coMethodId" />
                <input type="hidden" name="accountId" id="coAccountId" />

                <div class="co-card">
                    <h2 class="co-step"><b>1</b> পেমেন্ট মেথড বাছাই করুন</h2>
                    <div class="co-methods" role="radiogroup" aria-label="Payment method">
                        <?php foreach ($methods as $i => $m): ?>
                            <button type="button" class="co-m" role="radio" aria-checked="false" data-method="<?= (int)$m->Id ?>" title="<?= e($m->Name) ?>"><?= payment_logo_html($m, 36) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="co-card" id="coPayCard" hidden>
                    <h2 class="co-step"><b>2</b> <span id="coPayTitle">QR স্ক্যান করে টাকা পাঠান</span></h2>
                    <div class="co-accsel" id="coAccSel" hidden></div>
                    <div class="co-pay">
                        <div class="co-qr" id="coQr"></div>
                        <div>
                            <div class="co-amount"><div><small>এই পরিমাণ টাকা পাঠান</small><b><?= e(taka($pkg->Price)) ?></b></div>
                                <button type="button" class="co-copy" data-copy="<?= (int)$pkg->Price ?>"><i class="far fa-copy"></i> Copy</button></div>
                            <div class="co-rows" id="coRows"></div>
                            <div class="co-ins" id="coIns" hidden><i class="fas fa-info-circle"></i><span></span></div>
                        </div>
                    </div>
                </div>

                <div class="co-card" id="coDetails" hidden>
                    <h2 class="co-step"><b>3</b> Payment details</h2>
                    <div class="co-f">
                        <div class="full" data-f="txnId">
                            <label for="coTxn">Transaction ID<span class="req" id="coTxnReq">*</span></label>
                            <input type="text" id="coTxn" name="txnId" maxlength="40" autocomplete="off" placeholder="যেমন: DJU8RDNST8" />
                            <div class="co-hint warn" id="coTxnBank" hidden><i class="fas fa-info-circle"></i> Bank Transfer-এ Transaction ID না থাকলে খালি রাখতে পারেন — deposit slip / receipt অবশ্যই upload করুন।</div>
                        </div>
                        <div data-f="payDate">
                            <label for="coDate">Payment date<span class="req">*</span></label>
                            <input type="date" id="coDate" name="payDate" value="<?= e($today) ?>" max="<?= e($today) ?>" />
                        </div>
                        <div class="full" data-f="receipt">
                            <label>Payment receipt<span class="req">*</span></label>
                            <label class="co-drop" id="coDrop" for="coFile">
                                <i class="fas fa-cloud-arrow-up big"></i>
                                <b>Tap to add a screenshot or PDF</b>
                                <small>JPG, PNG, WEBP বা PDF · সর্বোচ্চ ৫ MB</small>
                                <input type="file" id="coFile" name="receipt" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf" />
                            </label>
                            <div class="co-prev" id="coPrev" hidden></div>
                            <div class="co-hint"><i class="fas fa-eye"></i> Transaction amount ও ID যেন পরিষ্কার দেখা যায়।</div>
                        </div>
                        <div class="full">
                            <label for="coNote">Note</label>
                            <textarea id="coNote" name="note" maxlength="500" placeholder="যে নম্বর/অ্যাকাউন্ট থেকে টাকা পাঠিয়েছেন বা অন্য কিছু জানাতে চাইলে লিখুন (ঐচ্ছিক)"></textarea>
                        </div>
                    </div>
                    <div class="co-err" id="coErr" role="alert"></div>
                    <button type="submit" class="co-submit" id="coSubmit"<?= $block ? ' disabled' : '' ?>><i class="fas fa-paper-plane"></i> Submit payment request</button>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <aside class="co-card co-sum">
            <h3>অর্ডার সারাংশ</h3>
            <div class="co-pkg">
                <div class="aud"><?= $pkg->Audience === 'Teacher' ? 'শিক্ষক Subscription' : 'শিক্ষার্থী · Tuition Post' ?></div>
                <div class="nm"><?= e($pkg->Name) ?></div>
                <div class="du"><i class="far fa-clock"></i> <?= e($pkg->DurationLabel) ?></div>
            </div>
            <?php if ($pkg->RegularPrice): ?>
                <div class="co-line"><span>নিয়মিত মূল্য</span><s><?= e(taka($pkg->RegularPrice)) ?></s></div>
                <div class="co-line"><span>ছাড় (<?= e(bn_num($pkg->Discount)) ?>%)</span><span style="color:#157347;">−<?= e(taka($pkg->RegularPrice - $pkg->Price)) ?></span></div>
            <?php endif; ?>
            <div class="co-line tot"><span>মোট</span><span><?= e(taka($pkg->Price)) ?></span></div>
            <ul class="co-how">
                <li><i class="fas fa-qrcode"></i><span>অ্যাপ দিয়ে QR স্ক্যান করে ঠিক <?= e(taka($pkg->Price)) ?> পাঠান</span></li>
                <li><i class="fas fa-camera"></i><span>পেমেন্ট সফল হওয়ার স্ক্রিনশট নিন</span></li>
                <li><i class="fas fa-file-arrow-up"></i><span>Transaction ID, তারিখ ও স্ক্রিনশট জমা দিন</span></li>
                <li><i class="fas fa-circle-check"></i><span>অ্যাডমিন যাচাই করে Accept করলে নোটিফিকেশন পাবেন</span></li>
            </ul>
        </aside>
    </div>
</div>

<div class="co-lb" id="coLb" hidden><img alt="QR code" /></div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var form = document.getElementById('coForm'); if (!form) return;
    var METHODS = <?= js($jsMethods) ?>;
    var cur = null, curAcc = null, file = null;
    function esc(t) { return String(t == null ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

    function pickMethod(id) {
        cur = METHODS.find(function (m) { return m.id === id; }); if (!cur) return;
        document.querySelectorAll('.co-m').forEach(function (b) { var on = +b.getAttribute('data-method') === id; b.classList.toggle('on', on); b.setAttribute('aria-checked', on ? 'true' : 'false'); });
        document.getElementById('coMethodId').value = id;
        var sel = document.getElementById('coAccSel');
        sel.hidden = cur.accounts.length < 2;
        sel.innerHTML = cur.accounts.map(function (a, i) { return '<button type="button" data-acc="' + a.id + '">' + esc(a.title) + '</button>'; }).join('');
        pickAccount(cur.accounts[0].id);
        var ins = document.getElementById('coIns');
        ins.hidden = !cur.instructions; ins.querySelector('span').textContent = cur.instructions;
        document.getElementById('coPayTitle').textContent = cur.name + ' দিয়ে টাকা পাঠান';
        var bank = cur.kind === 'bank' || !cur.txnRequired;
        document.getElementById('coTxnReq').hidden = !cur.txnRequired;
        document.getElementById('coTxnBank').hidden = !bank;
        document.getElementById('coPayCard').hidden = false; document.getElementById('coDetails').hidden = false;
    }
    function row(label, val) {
        if (!val) return '';
        return '<div class="co-row"><span>' + label + '</span><b>' + esc(val) + '</b><button type="button" class="co-copy" data-copy="' + esc(val) + '"><i class="far fa-copy"></i> Copy</button></div>';
    }
    function pickAccount(id) {
        curAcc = cur.accounts.find(function (a) { return a.id === id; });
        document.getElementById('coAccountId').value = id;
        document.querySelectorAll('#coAccSel [data-acc]').forEach(function (b) { b.classList.toggle('on', +b.getAttribute('data-acc') === id); });
        var q = document.getElementById('coQr');
        q.innerHTML = curAcc.qr
            ? '<a class="co-qr-img" href="' + esc(curAcc.qr) + '" data-zoom><img src="' + esc(curAcc.qr) + '" alt="Scan to pay" /></a>'
              + '<div class="co-qr-acts"><button type="button" data-zoom-btn><i class="fas fa-expand"></i> বড় করুন</button><a href="' + esc(curAcc.qr) + '" download target="_blank" rel="noopener"><i class="fas fa-download"></i> Download</a></div>'
            : '<div class="co-noqr"><i class="fas fa-qrcode fa-2x"></i><br>এই অ্যাকাউন্টের QR নেই — নিচের তথ্যে টাকা পাঠান</div>';
        var bankish = cur.kind === 'bank';
        document.getElementById('coRows').innerHTML = [
            '<div class="co-row"><span>অ্যাকাউন্ট</span><b>' + esc(curAcc.title) + '</b></div>',
            row(bankish ? 'ব্যাংক' : 'প্রতিষ্ঠান', curAcc.bankName), row(bankish ? 'Account name' : 'নাম', curAcc.accountName),
            row(bankish ? 'Account no.' : 'নম্বর', curAcc.accountNumber), row('ধরন', curAcc.accountType), row('Branch', curAcc.branch), row('Routing no.', curAcc.routing)
        ].join('');
    }
    document.querySelectorAll('.co-m').forEach(function (b) { b.addEventListener('click', function () { pickMethod(+b.getAttribute('data-method')); }); });
    document.getElementById('coAccSel').addEventListener('click', function (e) { var b = e.target.closest('[data-acc]'); if (b) pickAccount(+b.getAttribute('data-acc')); });

    // QR বড় করে দেখা + কপি
    var lb = document.getElementById('coLb');
    document.addEventListener('click', function (e) {
        var z = e.target.closest('[data-zoom], [data-zoom-btn]');
        if (z && curAcc && curAcc.qr) { e.preventDefault(); lb.querySelector('img').src = curAcc.qr; lb.hidden = false; return; }
        if (e.target.closest('#coLb')) { lb.hidden = true; return; }
        var c = e.target.closest('[data-copy]');
        if (c) {
            var v = c.getAttribute('data-copy'), done = function () { var h = c.innerHTML; c.classList.add('done'); c.innerHTML = '<i class="fas fa-check"></i> Copied'; setTimeout(function () { c.classList.remove('done'); c.innerHTML = h; }, 1400); };
            if (navigator.clipboard) navigator.clipboard.writeText(v).then(done, done); else done();
        }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') lb.hidden = true; });

    // রিসিট
    var inp = document.getElementById('coFile'), prev = document.getElementById('coPrev'), drop = document.getElementById('coDrop');
    function setFile(f) {
        file = f || null; markBad(null);
        if (!file) { prev.hidden = true; prev.innerHTML = ''; drop.hidden = false; inp.value = ''; return; }
        var isPdf = /pdf$/i.test(file.type) || /\.pdf$/i.test(file.name);
        var size = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        prev.innerHTML = (isPdf ? '<span class="pdf"><i class="fas fa-file-pdf"></i></span>' : '<img alt="" />')
            + '<div><b>' + esc(file.name) + '</b><br><small>' + size + '</small></div><button type="button" data-rm><i class="fas fa-times"></i> Remove</button>';
        if (!isPdf) { var r = new FileReader(); r.onload = function () { prev.querySelector('img').src = r.result; }; r.readAsDataURL(file); }
        prev.hidden = false; drop.hidden = true;
    }
    inp.addEventListener('change', function () { setFile(inp.files[0]); });
    prev.addEventListener('click', function (e) { if (e.target.closest('[data-rm]')) setFile(null); });
    ['dragenter', 'dragover'].forEach(function (t) { drop.addEventListener(t, function (e) { e.preventDefault(); drop.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (t) { drop.addEventListener(t, function (e) { e.preventDefault(); drop.classList.remove('drag'); }); });
    drop.addEventListener('drop', function (e) { if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]); });

    var err = document.getElementById('coErr');
    function markBad(f) { document.querySelectorAll('.co-f [data-f]').forEach(function (x) { x.classList.toggle('bad', x.getAttribute('data-f') === f); }); }
    function showErr(msg, f) { err.textContent = msg; err.classList.add('show'); markBad(f || null); var el = f && document.querySelector('[data-f="' + f + '"]'); (el || err).scrollIntoView({ behavior: 'smooth', block: 'center' }); }

    form.addEventListener('submit', async function (e) {
        e.preventDefault(); err.classList.remove('show');
        if (!cur) return showErr('পেমেন্ট মেথড বাছাই করুন।');
        var txn = form.txnId.value.trim();
        if (cur.txnRequired && !txn) return showErr('Transaction ID দিন।', 'txnId');
        if (!form.payDate.value) return showErr('পেমেন্টের তারিখ দিন।', 'payDate');
        if (!file) return showErr('পেমেন্টের রিসিট / স্ক্রিনশট আপলোড করুন।', 'receipt');
        if (file.size > 5 * 1024 * 1024) return showErr('রিসিট ৫ MB এর কম হতে হবে।', 'receipt');
        var fd = new FormData(form); fd.set('receipt', file);
        var btn = document.getElementById('coSubmit'); btn.disabled = true; var h = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> জমা হচ্ছে...';
        try {
            var r = await fetch('/Payment/Submit', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            var d = await r.json();
            if (!d.success) { showErr(d.message || 'জমা দেওয়া যায়নি।', d.field); btn.disabled = false; btn.innerHTML = h; return; }
            location.href = d.redirect;
        } catch (x) { showErr('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'); btn.disabled = false; btn.innerHTML = h; }
    });
    if (METHODS.length) pickMethod(METHODS[0].id);
})();
</script>
<?php section_end(); ?>
