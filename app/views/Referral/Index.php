<?php
/** আমার রেফারেল — লিংক, আয়, রেফার করা ইউজার, টাকা তোলা */
$sum = $ViewBag->Summary; $set = $ViewBag->Settings;
$ViewData['Title'] = 'My Referral Bonus';
$ViewData['Robots'] = 'noindex, nofollow';
$stCls = ['Pending' => 'pend', 'Paid' => 'paid', 'Rejected' => 'rej'];
?>
<style>
    .rfp { background: #F3F6FA; padding: 28px 0 60px; min-height: 70vh; }
    .rfp-wrap { max-width: 1080px; margin: 0 auto; padding: 0 16px; }
    .rfp-hero { background: linear-gradient(120deg, #08224A, #0A5A86 60%, #00CFFF); color: #fff; border-radius: 18px; padding: 26px 28px; margin-bottom: 18px; }
    .rfp-hero h1 { margin: 0; font-size: 26px; font-weight: 800; font-family: var(--font-heading); }
    .rfp-hero p { margin: 6px 0 0; opacity: .92; font-size: 14.5px; }
    .rfp-hero p b { color: #FDE68A; font-size: 17px; }
    .rfp-grid { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 18px; align-items: start; }
    .rfp-card { background: #fff; border: 1px solid #E3ECF5; border-radius: 16px; padding: 20px; box-shadow: 0 2px 12px rgba(15,27,45,.05); margin-bottom: 18px; }
    .rfp-card h2 { margin: 0 0 14px; font-size: 17px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px; }
    .rfp-card h2 i { color: #00B5E0; }
    .rfp-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .rfp-stat { background: #fff; border: 1px solid #E3ECF5; border-radius: 14px; padding: 14px; }
    .rfp-stat small { display: block; font-size: 12px; color: #64748B; font-weight: 700; }
    .rfp-stat b { display: block; font-size: 22px; color: #0F172A; margin-top: 2px; }
    .rfp-stat.g b { color: #16A34A; } .rfp-stat.c b { color: #0284C7; }
    .rfp-how { counter-reset: s; list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
    .rfp-how li { display: flex; gap: 10px; font-size: 13.5px; color: #334155; align-items: flex-start; }
    .rfp-how li::before { counter-increment: s; content: counter(s); width: 24px; height: 24px; border-radius: 50%; background: #E6F9FF; color: #0369A1; font-weight: 800; font-size: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rfp-f label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin: 12px 0 6px; }
    .rfp-f input, .rfp-f select { width: 100%; height: 42px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; outline: none; background: #fff; }
    .rfp-f input:focus, .rfp-f select:focus { border-color: #00CFFF; box-shadow: 0 0 0 3px rgba(0,207,255,.15); }
    .rfp-methods { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .rfp-methods label { margin: 0; border: 1.5px solid #DCE5EF; border-radius: 10px; padding: 9px 4px; text-align: center; cursor: pointer; font-weight: 700; font-size: 13px; color: #334155; }
    .rfp-methods input { display: none; }
    .rfp-methods label.on { border-color: #00CFFF; background: #E6F9FF; color: #0369A1; }
    .rfp-f .bank[hidden] { display: none; }
    .rfp-f .note { font-size: 12.5px; color: #64748B; margin-top: 8px; }
    .rfp-f button { width: 100%; height: 46px; margin-top: 16px; border: none; border-radius: 10px; background: #16A34A; color: #fff; font-weight: 800; font-size: 15px; cursor: pointer; font-family: inherit; }
    .rfp-f button:hover { background: #15803D; } .rfp-f button:disabled { opacity: .55; cursor: default; }
    .rfp-msg { margin-top: 10px; font-size: 13.5px; font-weight: 600; min-height: 1px; }
    .rfp-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rfp-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 8px 6px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .rfp-tbl td { padding: 10px 6px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: top; }
    .rfp-st { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; }
    .rfp-st.pend { background: #FEF3C7; color: #92400E; } .rfp-st.paid { background: #DCFCE7; color: #166534; } .rfp-st.rej { background: #FEE2E2; color: #991B1B; }
    .rfp-empty { text-align: center; color: #94A3B8; font-size: 13.5px; padding: 18px 0; }
    .rfp-tw { overflow-x: auto; }
    @media (max-width: 900px) { .rfp-grid { grid-template-columns: minmax(0, 1fr); } .rfp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .rfp-wrap { padding: 0 10px; } .rfp-hero { padding: 20px; } .rfp-hero h1 { font-size: 21px; } .rfp-card { padding: 16px; } }
</style>

<div class="rfp">
    <div class="rfp-wrap">
        <div class="rfp-hero">
            <h1><i class="fas fa-gift"></i> My Referral Bonus</h1>
            <p>বন্ধুদের সাথে লিংক শেয়ার করুন — আপনার লিংক দিয়ে সাইনআপ করে কেউ (স্টুডেন্ট বা টিচার) প্যাকেজ কিনলে আপনি পাবেন <b>৳<?= (int)$set['bonus'] ?></b>। টাকা যখন খুশি bKash, Nagad, Rocket বা ব্যাংকে তুলতে পারবেন।</p>
        </div>

        <div class="rfp-stats">
            <div class="rfp-stat g"><small>Total Earning</small><b>৳<?= number_format($sum['earned']) ?></b></div>
            <div class="rfp-stat c"><small>Available Balance</small><b>৳<?= number_format($sum['balance']) ?></b></div>
            <div class="rfp-stat"><small>Withdrawn</small><b>৳<?= number_format($sum['withdrawn']) ?></b></div>
            <div class="rfp-stat"><small>Referred Users</small><b><?= (int)$sum['count'] ?> জন</b></div>
        </div>

        <div class="rfp-grid">
            <div>
                <div class="rfp-card">
                    <h2><i class="fas fa-share-nodes"></i> লিংক শেয়ার করুন</h2>
                    <?php partial('Referral/_Share', ['code' => $ViewBag->Code, 'bonus' => $set['bonus'], 'compact' => false]); ?>
                </div>
                <div class="rfp-card">
                    <h2><i class="fas fa-circle-question"></i> কীভাবে কাজ করে</h2>
                    <ol class="rfp-how">
                        <li>উপরের লিংকটা WhatsApp, Facebook, Messenger, Instagram বা যেকোনো জায়গায় বন্ধুদের পাঠান।</li>
                        <li>কেউ আপনার লিংকে ক্লিক করে নতুন অ্যাকাউন্ট খুললে নিচের তালিকায় তার নাম আসবে ("প্যাকেজ কেনেনি")।</li>
                        <li>সেই ইউজার (স্টুডেন্ট বা টিচার) কোনো প্যাকেজ কিনলে, পেমেন্ট Approve হওয়ার সাথে সাথে আপনার অ্যাকাউন্টে ৳<?= (int)$set['bonus'] ?> যোগ হবে।</li>
                        <li>ব্যালেন্স ৳<?= (int)$set['min'] ?> বা বেশি হলে উইথড্র আবেদন দিন — অ্যাডমিন যাচাই করে টাকা পাঠাবেন।</li>
                    </ol>
                    <p style="font-size:12.5px; color:#94A3B8; margin:12px 0 0;">ভুয়া বা নিজের একাধিক অ্যাকাউন্ট দিয়ে রেফার করলে বোনাস বাতিল করা হবে।</p>
                </div>
                <div class="rfp-card">
                    <h2><i class="fas fa-users"></i> আমার রেফার করা ইউজার</h2>
                    <?php if (!$ViewBag->Referrals): ?>
                        <div class="rfp-empty">এখনো কেউ আপনার লিংক দিয়ে সাইনআপ করেনি — লিংকটা শেয়ার করুন!</div>
                    <?php else: ?>
                        <div class="rfp-tw"><table class="rfp-tbl">
                            <tr><th>নাম</th><th>ধরন</th><th>তারিখ</th><th>বোনাস</th></tr>
                            <?php foreach ($ViewBag->Referrals as $r): $nm = !is_blank($r->FullName) ? $r->FullName : ($r->UserName ?: 'User'); ?>
                                <tr>
                                    <td><?= e($nm) ?></td>
                                    <td><?= $r->Role === 'Teacher' ? 'Teacher' : 'Student' ?></td>
                                    <td><?= e(fmt_date($r->CreatedAt, 'd M Y')) ?></td>
                                    <td><?= $r->Status === 'Credited' ? '<b style="color:#16A34A;">+৳' . (int)$r->Bonus . '</b>' : ($r->Status === 'Waiting' ? '<span style="color:#94A3B8; font-size:12.5px;">প্যাকেজ কেনেনি</span>' : '<span class="rfp-st rej">বাতিল</span>') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table></div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="rfp-card" id="withdraw">
                    <h2><i class="fas fa-money-bill-transfer"></i> টাকা তুলুন (Withdraw)</h2>
                    <form class="rfp-f" id="rfpForm" autocomplete="off" novalidate>
                        <label>কোথায় নেবেন</label>
                        <div class="rfp-methods">
                            <?php foreach (Db::REF_METHODS as $i => $m): ?><label class="<?= $i === 0 ? 'on' : '' ?>"><input type="radio" name="method" value="<?= e($m) ?>"<?= $i === 0 ? ' checked' : '' ?> /><?= e($m) ?></label><?php endforeach; ?>
                        </div>
                        <label for="rfAmt">পরিমাণ (৳)</label>
                        <input id="rfAmt" name="amount" type="number" inputmode="numeric" min="<?= (int)$set['min'] ?>" max="<?= (int)$sum['balance'] ?>" value="<?= $sum['balance'] >= $set['min'] ? (int)$sum['balance'] : '' ?>" placeholder="সর্বনিম্ন ৳<?= (int)$set['min'] ?>" />
                        <div class="bank" hidden>
                            <label for="rfBank">ব্যাংকের নাম</label><input id="rfBank" name="bankName" maxlength="100" placeholder="যেমন: Dutch-Bangla Bank" />
                            <label for="rfBranch">শাখা</label><input id="rfBranch" name="branch" maxlength="100" placeholder="যেমন: Mirpur" />
                        </div>
                        <label for="rfName">অ্যাকাউন্টের নাম <span class="opt" style="font-weight:400; color:#94A3B8;">(ঐচ্ছিক)</span></label><input id="rfName" name="accountName" maxlength="100" />
                        <label for="rfNum" id="rfNumL">bKash নম্বর</label><input id="rfNum" name="accountNumber" maxlength="40" inputmode="numeric" placeholder="01XXXXXXXXX" />
                        <p class="note"><i class="fas fa-circle-info"></i> ব্যালেন্স: <b>৳<?= number_format($sum['balance']) ?></b> · সর্বনিম্ন ৳<?= (int)$set['min'] ?><?= $sum['pending'] ? ' · প্রক্রিয়াধীন ৳' . number_format($sum['pending']) : '' ?></p>
                        <button type="submit" id="rfBtn"<?= $sum['balance'] < $set['min'] ? ' disabled' : '' ?>><i class="fas fa-paper-plane"></i> Withdraw Request</button>
                        <div class="rfp-msg" id="rfMsg"><?= $sum['balance'] < $set['min'] ? '<span style="color:#64748B;">আরো রেফার করুন — ব্যালেন্স ৳' . (int)$set['min'] . ' হলে টাকা তুলতে পারবেন।</span>' : '' ?></div>
                    </form>
                </div>
                <div class="rfp-card">
                    <h2><i class="fas fa-clock-rotate-left"></i> উইথড্র ইতিহাস</h2>
                    <?php if (!$ViewBag->Withdrawals): ?>
                        <div class="rfp-empty">এখনো কোনো আবেদন নেই।</div>
                    <?php else: ?>
                        <div class="rfp-tw"><table class="rfp-tbl">
                            <tr><th>তারিখ</th><th>পরিমাণ</th><th>মাধ্যম</th><th>অবস্থা</th></tr>
                            <?php foreach ($ViewBag->Withdrawals as $w): ?>
                                <tr>
                                    <td><?= e(fmt_date($w->CreatedAt, 'd M Y')) ?></td>
                                    <td><b>৳<?= number_format((int)$w->Amount) ?></b></td>
                                    <td><?= e($w->Method) ?><br><small style="color:#94A3B8;"><?= e(substr((string)$w->AccountNumber, 0, 3) . '•••' . substr((string)$w->AccountNumber, -3)) ?></small></td>
                                    <td><span class="rfp-st <?= $stCls[$w->Status] ?? 'pend' ?>"><?= $w->Status === 'Pending' ? 'প্রক্রিয়াধীন' : ($w->Status === 'Paid' ? 'পাঠানো হয়েছে' : 'বাতিল') ?></span>
                                        <?php if (!is_blank($w->TxnId)): ?><br><small style="color:#64748B;">TxnID: <?= e($w->TxnId) ?></small><?php endif; ?>
                                        <?php if (!is_blank($w->AdminNote)): ?><br><small style="color:#64748B;"><?= e($w->AdminNote) ?></small><?php endif; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var f = document.getElementById('rfpForm'), lab = document.getElementById('rfNumL'), num = document.getElementById('rfNum'), bank = f.querySelector('.bank');
    function paint() {
        var m = f.method.value;
        f.querySelectorAll('.rfp-methods label').forEach(function (l) { l.classList.toggle('on', l.querySelector('input').checked); });
        bank.hidden = m !== 'Bank';
        lab.textContent = m === 'Bank' ? 'অ্যাকাউন্ট নম্বর' : m + ' নম্বর';
        num.placeholder = m === 'Bank' ? 'Account number' : '01XXXXXXXXX';
        f.querySelector('.opt').hidden = m === 'Bank';
    }
    f.querySelectorAll('input[name=method]').forEach(function (i) { i.addEventListener('change', paint); });
    paint();
    f.addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn = document.getElementById('rfBtn'), msg = document.getElementById('rfMsg');
        btn.disabled = true; msg.textContent = '';
        var fd = new FormData(f); fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        try {
            var r = await (await fetch('/Referral/Withdraw', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            msg.style.color = r.success ? '#16A34A' : '#DC2626'; msg.textContent = r.message || '';
            if (r.success) setTimeout(function () { location.reload(); }, 1400); else btn.disabled = false;
        } catch (er) { msg.style.color = '#DC2626'; msg.textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।'; btn.disabled = false; }
    });
})();
</script>
<?php section_end(); ?>
