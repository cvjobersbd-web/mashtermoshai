<?php
/**
 * পেমেন্ট (ম্যানুয়াল): ইউজার QR স্ক্যান করে টাকা পাঠায় → Transaction ID, তারিখ আর রিসিট দিয়ে রিকোয়েস্ট জমা দেয়
 * → অ্যাডমিন যাচাই করে Accept করলে Subscription / পোস্ট ক্রেডিট চালু হয়।
 */
class PaymentController extends Controller
{
    private const RECEIPT_EXTS = ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.pdf'];
    private const MAX_RECEIPT = 5 * 1024 * 1024;

    private function fail(string $msg, array $extra = []): void { json_response(['success' => false, 'message' => $msg] + $extra); }

    /** প্যাকেজটা এই ইউজার কিনতে পারবে কিনা — না পারলে কারণ */
    private function packageBlockReason(Obj $pkg): ?string
    {
        $role = (string)session_get('UserRole');
        if ($role === 'Admin') return 'অ্যাডমিন অ্যাকাউন্ট দিয়ে প্যাকেজ কেনা যায় না।';
        if ($pkg->Audience === 'Student' && $this->db->PaymentRules()['studentFree']) return 'এখন Tuition পোস্ট করা সম্পূর্ণ ফ্রি — কোনো প্যাকেজ বা পেমেন্ট লাগবে না। সরাসরি Tuition Circular-এ গিয়ে পোস্ট করুন।';
        if ($pkg->Audience === 'Teacher' && $role !== 'Teacher') return 'এই Subscription শুধু অনুমোদিত শিক্ষকদের জন্য। আগে “Become a Tutor” থেকে টিচার হওয়ার আবেদন করুন।';
        return null;
    }

    /** GET /Payment/Checkout/{packageId} */
    public function Checkout(): void
    {
        $id = (int)input_int('id', 0);
        if (current_user_id() === null) $this->redirectToAction('Login', 'Account', ['returnUrl' => '/Payment/Checkout/' . $id]);
        $pkg = $id ? $this->db->GetPackage($id) : null;
        if (!$pkg || !$pkg->IsActive) redirect('/Package');
        $uid = (int)current_user_id();
        $this->view(null, $pkg, [
            'Methods' => $this->db->GetPaymentMethods(true),
            'BlockReason' => $this->packageBlockReason($pkg),
            'PendingCount' => $this->db->CountUserPendingPayments($uid),
        ]);
    }

    /** POST /Payment/Submit — packageId, methodId, accountId, txnId, payDate, note, receipt(file) */
    public function Submit_POST(): void
    {
        $uid = current_user_id();
        if ($uid === null) $this->fail('পেমেন্ট জমা দিতে লগইন করুন।');
        $pkg = $this->db->GetPackage((int)input_int('packageId', 0));
        if (!$pkg || !$pkg->IsActive) $this->fail('প্যাকেজটি পাওয়া যায়নি।');
        if ($why = $this->packageBlockReason($pkg)) $this->fail($why);
        $method = $this->db->GetPaymentMethod((int)input_int('methodId', 0));
        if (!$method || !$method->IsActive) $this->fail('পেমেন্ট মেথড বাছাই করুন।');
        $acc = $this->db->GetPaymentAccount((int)input_int('accountId', 0));
        if (!$acc || $acc->MethodId !== $method->Id || !$acc->IsActive) $this->fail('কোন অ্যাকাউন্টে টাকা পাঠিয়েছেন সেটি বাছাই করুন।');

        $txn = strtoupper(preg_replace('/\s+/', '', (string)input_str('txnId', '')));
        if ($txn === '' && $method->TxnRequired) $this->fail('Transaction ID দিন।', ['field' => 'txnId']);
        if ($txn !== '' && !preg_match('/^[A-Z0-9\-_\/.#]{4,40}$/', $txn)) $this->fail('Transaction ID সঠিক নয় — শুধু ইংরেজি অক্ষর ও সংখ্যা (৪–৪০ অক্ষর)।', ['field' => 'txnId']);
        if ($txn !== '' && $this->db->PaymentTxnUsed($txn)) $this->fail('এই Transaction ID দিয়ে আগেই পেমেন্ট জমা দেওয়া হয়েছে।', ['field' => 'txnId']);

        $date = (string)input_str('payDate', '');
        $d = DateTime::createFromFormat('!Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) $this->fail('পেমেন্টের তারিখ দিন।', ['field' => 'payDate']);
        $today = new DateTime('today');
        if ($d > (clone $today)->modify('+1 day')) $this->fail('পেমেন্টের তারিখ ভবিষ্যতের হতে পারে না।', ['field' => 'payDate']);
        if ($d < (clone $today)->modify('-90 days')) $this->fail('৯০ দিনের বেশি পুরোনো পেমেন্ট জমা দেওয়া যায় না — Helpdesk-এ যোগাযোগ করুন।', ['field' => 'payDate']);

        if ($this->db->CountUserPendingPayments($uid) >= 5) $this->fail('আপনার ৫টি পেমেন্ট যাচাইয়ের অপেক্ষায় আছে। অ্যাডমিন যাচাই করার পর আবার চেষ্টা করুন।');

        $file = upload_file('receipt');
        if (!$file) $this->fail('পেমেন্টের রিসিট / স্ক্রিনশট আপলোড করুন।', ['field' => 'receipt']);
        $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === '.jpeg') $ext = '.jpg';
        if (!in_array($ext, self::RECEIPT_EXTS, true)) $this->fail('রিসিট হিসেবে শুধু ছবি (JPG, PNG, WEBP) বা PDF দেওয়া যাবে।', ['field' => 'receipt']);
        if ($file['size'] > self::MAX_RECEIPT) $this->fail('রিসিট ৫ MB এর কম হতে হবে।', ['field' => 'receipt']);
        if ($ext === '.pdf') {
            if ((string)@file_get_contents($file['tmp_name'], false, null, 0, 5) !== '%PDF-') $this->fail('PDF ফাইলটি সঠিক নয়।', ['field' => 'receipt']);
        } elseif (@getimagesize($file['tmp_name']) === false) $this->fail('ছবিটি সঠিক নয়।', ['field' => 'receipt']);

        $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 500);
        $info = implode(' · ', array_filter([$acc->Title, $acc->BankName, $acc->AccountNumber]));
        // অনুমান করা যায় না এমন নাম, যাতে অন্য কেউ রিসিট খুঁজে না পায়
        $url = save_upload($file, 'payments', 'rcpt_' . $uid . '_' . bin2hex(random_bytes(12)), $ext);
        try {
            $id = $this->db->CreatePaymentRequest([
                'UserId' => $uid, 'PackageId' => $pkg->Id, 'PackageName' => $pkg->Name, 'Audience' => $pkg->Audience, 'Amount' => $pkg->Price,
                'DurationMonths' => $pkg->DurationMonths, 'PostLimit' => $pkg->PostLimit, 'MethodId' => $method->Id, 'MethodName' => $method->Name,
                'AccountId' => $acc->Id, 'AccountInfo' => mb_substr($info, 0, 200), 'TxnId' => $txn !== '' ? $txn : null, 'PayDate' => $date,
                'ReceiptUrl' => $url, 'Note' => $note !== '' ? $note : null,
            ]);
        } catch (Throwable $e) {
            delete_upload($url); error_log('[payment submit] ' . $e->getMessage());
            $this->fail('জমা দেওয়া যায়নি। আবার চেষ্টা করুন।');
        }
        $who = (string)(session_get('UserFullName') ?? session_get('UserName'));
        foreach ($this->db->GetAdmins() as $a) {
            $this->db->CreateNotification((int)$a->Id, 'New Payment', "$who “{$pkg->Name}” প্যাকেজের জন্য " . taka($pkg->Price) . " পেমেন্টের প্রমাণ জমা দিয়েছেন ({$method->Name})।", 'info', '/Admin/Payments', 'fa-money-bill-wave');
        }
        json_response(['success' => true, 'id' => $id, 'redirect' => '/Payment/My?new=' . $id,
            'message' => 'পেমেন্টের তথ্য জমা হয়েছে! অ্যাডমিন যাচাই করে Accept করলে আপনার প্যাকেজ চালু হবে।']);
    }

    /** GET /Payment/My — আমার পেমেন্ট ও Subscription */
    public function My(): void
    {
        $uid = current_user_id();
        if ($uid === null) $this->redirectToAction('Login', 'Account', ['returnUrl' => '/Payment/My']);
        $this->view(null, $this->db->GetUserPayments($uid), [
            'TeacherSub' => $this->db->GetActiveTeacherSubscription($uid),
            'Credits' => $this->db->GetStudentPostCredits($uid),
            'Rules' => $this->db->PaymentRules(),
            'NewId' => (int)input_int('new', 0),
        ]);
    }
}
