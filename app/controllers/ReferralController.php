<?php
/**
 * রেফারেল — নিজের লিংক শেয়ার, আয় দেখা, টাকা তোলার আবেদন।
 *  GET  /Referral            আমার রেফারেল (লিংক, আয়, রেফার করা ইউজার, উইথড্র ইতিহাস)
 *  POST /Referral/Withdraw   উইথড্র আবেদন (bKash / Nagad / Rocket / Bank) — অ্যাডমিন Admin → Referrals থেকে টাকা পাঠায়
 */
class ReferralController extends Controller
{
    private function me(bool $json = false): int
    {
        $me = current_user_id();
        if ($me === null) {
            if ($json) json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
            redirect('/Account/Login?returnUrl=' . rawurlencode('/Referral'));
        }
        if (current_role() === 'Admin') { if ($json) json_response(['success' => false, 'message' => 'অ্যাডমিনের জন্য নয়।']); redirect('/Admin/Referrals'); }
        return (int)$me;
    }

    /** GET /Referral */
    public function Index(): void
    {
        $me = $this->me();
        $this->view(null, null, [
            'Code' => $this->db->GetReferralCode($me), 'Summary' => $this->db->GetReferralSummary($me), 'Settings' => $this->db->ReferralSettings(),
            'Referrals' => $this->db->GetMyReferrals($me), 'Withdrawals' => $this->db->GetMyWithdrawals($me),
        ]);
    }

    /** POST /Referral/Share (src) — নিজের লিংক / কোড কোথায় শেয়ার করল (অ্যাডমিন দেখেন) */
    public function Share_POST(): void
    {
        $me = $this->me(true);
        $src = (string)input_str('src', '');
        $this->db->LogReferralEvent($me, 'share', $src === 'code' ? 'code' : $src, (string)($_SERVER['REMOTE_ADDR'] ?? ''));
        json_response(['success' => true]);
    }

    /** POST /Referral/Withdraw (amount, method, accountName, accountNumber, bankName, branch) */
    public function Withdraw_POST(): void
    {
        $me = $this->me(true);
        $set = $this->db->ReferralSettings();
        $sum = $this->db->GetReferralSummary($me);
        $amount = input_int('amount', 0);
        $method = (string)input_str('method', '');
        $clean = fn($k, $n) => mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str($k, '')))), 0, $n);
        $name = $clean('accountName', 100); $number = preg_replace('/[^0-9A-Za-z-]/', '', $clean('accountNumber', 40));
        $bank = $clean('bankName', 100); $branch = $clean('branch', 100);

        if (!in_array($method, Db::REF_METHODS, true)) json_response(['success' => false, 'message' => 'কোথায় টাকা নেবেন সেটা বাছাই করুন।']);
        if ($amount < $set['min']) json_response(['success' => false, 'message' => "সর্বনিম্ন ৳{$set['min']} তোলা যাবে।"]);
        if ($amount > $sum['balance']) json_response(['success' => false, 'message' => "আপনার ব্যালেন্স ৳{$sum['balance']} — এর বেশি তোলা যাবে না।"]);
        if ($method === 'Bank') {
            if ($name === '' || $bank === '' || strlen($number) < 6) json_response(['success' => false, 'message' => 'ব্যাংকের নাম, অ্যাকাউন্টের নাম আর অ্যাকাউন্ট নম্বর দিন।']);
        } elseif (!preg_match('/^01[3-9]\d{8}$/', $number)) {
            json_response(['success' => false, 'message' => "সঠিক $method নম্বর দিন (যেমন 01712345678)।"]);
        }
        if ((int)$this->db->scalar("SELECT COUNT(*) FROM `ReferralWithdrawals` WHERE `UserId` = :u AND `Status` = 'Pending' AND `Method` <> 'Discount'", ['u' => $me]) >= 3) {
            json_response(['success' => false, 'message' => 'আগের আবেদনগুলো এখনো প্রক্রিয়াধীন — সেগুলো শেষ হলে আবার আবেদন করুন।']);
        }
        $this->db->CreateWithdrawal($me, $amount, $method, $name, $number, $bank, $branch);
        $who = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'User');
        foreach ($this->db->GetAdmins() as $a) {
            $this->db->CreateNotification((int)$a->Id, '💸 Withdrawal Request', "$who ৳$amount তোলার আবেদন করেছেন ($method)।", 'info', '/Admin/Referrals', 'fa-money-bill-transfer');
        }
        json_response(['success' => true, 'message' => "৳$amount তোলার আবেদন পাঠানো হয়েছে। অ্যাডমিন টাকা পাঠালে নোটিফিকেশন পাবেন।"]);
    }
}
