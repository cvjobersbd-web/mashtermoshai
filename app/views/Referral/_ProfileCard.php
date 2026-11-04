<?php
/** প্রোফাইলের পাশে "My Referral Bonus" কার্ড — শুধু নিজের প্রোফাইলে দেখায় */
$me = (int)current_user_id();
try { $rfSet = db()->ReferralSettings(); if (!$rfSet['enabled']) return; $rfCode = db()->GetReferralCode($me); $rfSum = db()->GetReferralSummary($me); }
catch (Throwable $e) { return; }
?>
<style>
    .rfc { background: #fff; border: 1px solid #E3ECF5; border-radius: 16px; padding: 16px; margin-top: 16px; box-shadow: 0 2px 12px rgba(15,27,45,.05); }
    .rfc h3 { margin: 0 0 12px; font-size: 16px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px; }
    .rfc h3 i { color: #F59E0B; }
    .rfc-sum { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
    .rfc-sum div { background: #F6F9FC; border-radius: 12px; padding: 10px; text-align: center; }
    .rfc-sum b { display: block; font-size: 20px; color: #0F172A; }
    .rfc-sum small { font-size: 11.5px; color: #64748B; font-weight: 600; }
    .rfc-sum .earn b { color: #16A34A; }
    .rfc-acts { display: flex; gap: 8px; margin-top: 12px; }
    .rfc-acts a { flex: 1; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-weight: 800; font-size: 13px; text-decoration: none; }
    .rfc-acts .w { background: #16A34A; color: #fff; } .rfc-acts .w:hover { background: #15803D; color: #fff; }
    .rfc-acts .d { background: #F1F5F9; color: #334155; } .rfc-acts .d:hover { background: #E2E8F0; }
    .rfc-mob { display: none; margin: 0 0 16px; }
    @media (max-width: 992px) { .rfc-side { display: none; } .rfc-mob { display: block; } }
</style>
<div class="rfc <?= ($where ?? 'side') === 'mobile' ? 'rfc-mob' : 'rfc-side' ?>"<?= ($where ?? 'side') === 'side' ? ' id="myReferral"' : '' ?>>
    <h3><i class="fas fa-gift"></i> My Referral Bonus</h3>
    <div class="rfc-sum">
        <div class="earn"><b>৳<?= number_format($rfSum['earned']) ?></b><small>Total Earning</small></div>
        <div><b>৳<?= number_format($rfSum['balance']) ?></b><small>Balance</small></div>
    </div>
    <?php partial('Referral/_Share', ['code' => $rfCode, 'bonus' => $rfSet['bonus'], 'compact' => true]); ?>
    <div class="rfc-acts">
        <a class="w" href="/Referral#withdraw"><i class="fas fa-money-bill-transfer"></i> Withdraw</a>
        <a class="d" href="/Referral"><i class="fas fa-users"></i> <?= (int)$rfSum['count'] ?> জন রেফার</a>
    </div>
</div>
