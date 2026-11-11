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
        $track = account_track((int)current_user_id());
        if ($pkg->Audience === 'Student' && $track === 'teacher') return ONE_ROLE_MSG_TEACHER;
        if ($pkg->Audience === 'Teacher' && $track === 'student') return ONE_ROLE_MSG_STUDENT;
        if ($pkg->Audience === 'Student' && $role === 'User') {
            $ks = $this->db->KycStatus((int)current_user_id(), 'student');
            // ফর্ম জমা দিলেই প্যাকেজ কেনা যায় (যাচাই চলাকালীনও) — অ্যাডমিন আগে ফর্ম Approve করে তারপর পেমেন্ট Accept করেন
            if ($ks === 'Rejected') return 'আপনার Student Verification ফর্ম গ্রহণ করা হয়নি — ফর্মটা ঠিক করে আবার জমা দিন, তারপর প্যাকেজ কিনতে পারবেন।';
            if ($ks !== 'Approved' && $ks !== 'Pending') return 'প্যাকেজ কেনার আগে Student Verification ফর্ম (নাম, বাবা-মা, ঠিকানা, ক্লাস, ID কার্ড) পূরণ করুন।';
        }
        if ($pkg->Audience === 'Student' && ($this->db->PaymentRules()['studentFree'] ?? false)) return 'এখন Tuition পোস্ট করা সম্পূর্ণ ফ্রি — কোনো প্যাকেজ বা পেমেন্ট লাগবে না। সরাসরি Tuition Circular-এ গিয়ে পোস্ট করুন।';
        if ($pkg->Audience === 'Teacher' && $role !== 'Teacher') {
            // টিচার হওয়ার ফর্ম জমা দিয়েছে (পেমেন্ট বাকি / যাচাই চলছে) — প্যাকেজ কিনতে পারবে
            $tr = $this->db->GetLatestTeacherRequest((int)current_user_id());
            if (!$tr || !in_array($tr->Status, ['AwaitingPayment', 'Pending', 'Expired'], true)) return 'টিচার প্যাকেজ কিনতে আগে “Become a Tutor” ফর্ম পূরণ করুন — তারপর প্যাকেজ বেছে পেমেন্ট করবেন।';
        }
        return null;
    }

    /** POST /Payment/CheckCoupon (code, packageId) — চেকআউটে কুপন যাচাই, ঠিক হলে নতুন দাম */
    public function CheckCoupon_POST(): void
    {
        $uid = current_user_id();
        if ($uid === null) json_response(['success' => false, 'message' => 'লগইন করুন।']);
        $pkg = $this->db->GetPackage((int)input_int('packageId', 0));
        if (!$pkg || !$pkg->IsActive) json_response(['success' => false, 'message' => 'প্যাকেজ পাওয়া যায়নি।']);
        $err = null; $c = null;
        $price = $this->db->CheckCoupon((string)input_str('code', ''), $pkg, (int)$uid, $err, $c);
        if ($price === null) json_response(['success' => false, 'message' => $err]);
        json_response(['success' => true, 'price' => $price, 'before' => (int)$pkg->Price, 'code' => strtoupper((string)$c->Code),
            'message' => '✅ কুপন কাজ করছে — দাম ৳' . (int)$pkg->Price . ' থেকে কমে ৳' . $price]);
    }

    /** POST /Payment/CheckRefCode (code) — চেকআউটে রেফারেল কোড লিখলে সাথে সাথে যাচাই */
    public function CheckRefCode_POST(): void
    {
        $uid = current_user_id();
        if ($uid === null) json_response(['success' => false, 'message' => 'লগইন করুন।']);
        $err = null;
        $o = $this->db->CheckPurchaseReferralCode((string)input_str('code', ''), (int)$uid, $err);
        json_response($o ? ['success' => true, 'name' => $o->Name, 'message' => '✅ ' . $o->Name . ' এর রেফারেল কোড'] : ['success' => false, 'message' => $err]);
    }

    /** GET /Payment/Checkout/{packageId} */
    /** কোর্স কেনা — প্যাকেজের মতো একটা অবজেক্ট (Checkout পেজ একই থাকে) */
    private function coursePkg(int $courseId): ?Obj
    {
        $c = $courseId ? $this->db->GetCourse($courseId) : null;
        if (!$c || !$c->IsLive || $c->Price <= 0) return null;
        return obj(['Id' => 0, 'CourseId' => $c->Id, 'Course' => $c, 'Name' => $c->Title, 'Price' => $c->Price, 'Audience' => 'Course', 'IsActive' => true,
            'RegularPrice' => $c->RegularPrice, 'Discount' => $c->RegularPrice ? (int)round(100 - $c->Price * 100 / $c->RegularPrice) : 0,
            'DurationLabel' => 'আজীবন অ্যাক্সেস · ' . ($c->TeacherName ?: 'মাষ্টার মশাই'), 'DurationMonths' => 0, 'PostLimit' => 0]);
    }

    private function courseBlockReason(Obj $pkg): ?string
    {
        $uid = (int)current_user_id();
        if (session_get('UserRole') === 'Admin') return 'অ্যাডমিন অ্যাকাউন্ট দিয়ে কোর্স কেনা যায় না।';
        if ((int)$pkg->Course->TeacherId === $uid) return 'এটা আপনার নিজের কোর্স।';
        if ($this->db->IsEnrolled($pkg->CourseId, $uid)) return 'আপনি এই কোর্সে আগেই ভর্তি আছেন।';
        if ((int)$this->db->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `UserId` = :u AND `CourseId` = :c AND `Status` = 'Pending'", ['u' => $uid, 'c' => $pkg->CourseId]) > 0)
            return 'এই কোর্সের একটা পেমেন্ট যাচাইয়ের অপেক্ষায় আছে — অ্যাডমিন Approve করলেই কোর্স খুলে যাবে।';
        return null;
    }

    public function Checkout(): void
    {
        $id = (int)input_int('id', 0);
        $courseId = (int)input_int('course', 0);
        if (current_user_id() === null) $this->redirectToAction('Login', 'Account', ['returnUrl' => $courseId ? '/Payment/Checkout?course=' . $courseId : '/Payment/Checkout/' . $id]);
        $uid = (int)current_user_id();
        if ($courseId) {
            $pkg = $this->coursePkg($courseId);
            if (!$pkg) redirect('/Course');
            $this->view(null, $pkg, ['Methods' => $this->db->GetPaymentMethods(true), 'BlockReason' => $this->courseBlockReason($pkg),
                'PendingCount' => $this->db->CountUserPendingPayments($uid), 'RefOptions' => $this->db->ReferenceOptions(true), 'RefSel' => $this->db->GetUserHowHeard($uid)]);
        }
        $pkg = $id ? $this->db->GetPackage($id) : null;
        if (!$pkg || !$pkg->IsActive) redirect('/Package');
        $this->view(null, $pkg, [
            'Methods' => $this->db->GetPaymentMethods(true),
            'BlockReason' => $this->packageBlockReason($pkg),
            'PendingCount' => $this->db->CountUserPendingPayments($uid),
            'RefOptions' => $this->db->ReferenceOptions(true),
            'RefSel' => $this->db->GetUserHowHeard($uid),
        ]);
    }

    /** POST /Payment/Submit — packageId, methodId, accountId, txnId, payDate, note, receipt(file) */
    public function Submit_POST(): void
    {
        $uid = current_user_id();
        if ($uid === null) $this->fail('পেমেন্ট জমা দিতে লগইন করুন।');
        $courseId = (int)input_int('courseId', 0);
        if ($courseId) {
            $pkg = $this->coursePkg($courseId);
            if (!$pkg) $this->fail('কোর্সটি পাওয়া যায়নি।');
            if ($why = $this->courseBlockReason($pkg)) $this->fail($why);
        } else {
            $pkg = $this->db->GetPackage((int)input_int('packageId', 0));
            if (!$pkg || !$pkg->IsActive) $this->fail('প্যাকেজটি পাওয়া যায়নি।');
            if ($why = $this->packageBlockReason($pkg)) $this->fail($why);
        }
        // কুপন কোড (স্পেশাল অফার) — ঠিক থাকলে প্যাকেজের দাম কুপনের দামে নামে
        $couponCode = strtoupper(preg_replace('/\s+/', '', (string)input_str('couponCode', '')));
        if ($couponCode !== '' && !$courseId) {
            $cerr = null;
            $cp = $this->db->CheckCoupon($couponCode, $pkg, (int)$uid, $cerr);
            if ($cp === null) $this->fail($cerr . ' (কুপন না থাকলে ঘরটা ফাঁকা রাখুন)', ['field' => 'couponCode']);
            $pkg->PriceBefore = (int)$pkg->Price;
            $pkg->Price = $cp;
        } else $couponCode = '';

        // রেফারেল ব্যালেন্স থেকে ছাড় (ঐচ্ছিক) — পুরো দাম ব্যালেন্সে হলে টাকা পাঠাতে / রিসিট দিতে হয় না
        $disc = max(0, (int)input_int('useBalance', 0));
        if ($disc > 0) {
            $bal = $this->db->GetReferralSummary((int)$uid)['balance'];
            if ($disc > $bal) $this->fail("আপনার রেফারেল ব্যালেন্স ৳$bal — এর বেশি ছাড় নেওয়া যাবে না।", ['field' => 'useBalance']);
            if ($disc > (int)$pkg->Price) $disc = (int)$pkg->Price;
        }
        $payable = (int)$pkg->Price - $disc;
        $full = $disc > 0 && $payable === 0;
        $method = null; $acc = null;
        if (!$full) {
            $method = $this->db->GetPaymentMethod((int)input_int('methodId', 0));
            if (!$method || !$method->IsActive) $this->fail('পেমেন্ট মেথড বাছাই করুন।');
            $acc = $this->db->GetPaymentAccount((int)input_int('accountId', 0));
            if (!$acc || $acc->MethodId !== $method->Id || !$acc->IsActive) $this->fail('কোন অ্যাকাউন্টে টাকা পাঠিয়েছেন সেটি বাছাই করুন।');
        }

        $txn = $full ? '' : strtoupper(preg_replace('/\s+/', '', (string)input_str('txnId', '')));
        if (!$full && $txn === '' && $method->TxnRequired) $this->fail('Transaction ID দিন।', ['field' => 'txnId']);
        if ($txn !== '' && !preg_match('/^[A-Z0-9\-_\/.#]{4,40}$/', $txn)) $this->fail('Transaction ID সঠিক নয় — শুধু ইংরেজি অক্ষর ও সংখ্যা (৪–৪০ অক্ষর)।', ['field' => 'txnId']);
        if ($txn !== '' && $this->db->PaymentTxnUsed($txn)) $this->fail('এই Transaction ID দিয়ে আগেই পেমেন্ট জমা দেওয়া হয়েছে।', ['field' => 'txnId']);

        $date = $full ? date('Y-m-d') : (string)input_str('payDate', '');
        $d = DateTime::createFromFormat('!Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) $this->fail('পেমেন্টের তারিখ দিন।', ['field' => 'payDate']);
        $today = new DateTime('today');
        if ($d > (clone $today)->modify('+1 day')) $this->fail('পেমেন্টের তারিখ ভবিষ্যতের হতে পারে না।', ['field' => 'payDate']);
        if ($d < (clone $today)->modify('-90 days')) $this->fail('৯০ দিনের বেশি পুরোনো পেমেন্ট জমা দেওয়া যায় না — Helpdesk-এ যোগাযোগ করুন।', ['field' => 'payDate']);

        // কোথা থেকে জেনে প্যাকেজ কিনছেন — ঐচ্ছিক, শুধু তালিকা থেকে
        $ref = null;
        $refKey = (string)input_str('reference', '');
        if ($refKey !== '') $ref = $this->db->CleanReference($refKey);

        // রেফারেল কোড (ঐচ্ছিক) — কারো কোড দিলে পেমেন্ট Accept হলে কোডের মালিক বোনাস পায় (শুধু প্রথম কেনায়)
        $refCode = strtoupper(preg_replace('/\s+/', '', (string)input_str('refCode', '')));
        if ($refCode !== '' && (int)$pkg->Price > 0) {
            $rerr = null;
            if (!$this->db->CheckPurchaseReferralCode($refCode, $uid, $rerr)) $this->fail($rerr . ' (কোড না থাকলে ঘরটা ফাঁকা রাখুন)', ['field' => 'refCode']);
        } else $refCode = '';

        if ($this->db->CountUserPendingPayments($uid) >= 5) $this->fail('আপনার ৫টি পেমেন্ট যাচাইয়ের অপেক্ষায় আছে। অ্যাডমিন যাচাই করার পর আবার চেষ্টা করুন।');

        $file = $full ? null : upload_file('receipt');
        if (!$full && !$file) $this->fail('পেমেন্টের রিসিট / স্ক্রিনশট আপলোড করুন।', ['field' => 'receipt']);
        if ($file) {
        $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === '.jpeg') $ext = '.jpg';
        if (!in_array($ext, self::RECEIPT_EXTS, true)) $this->fail('রিসিট হিসেবে শুধু ছবি (JPG, PNG, WEBP) বা PDF দেওয়া যাবে।', ['field' => 'receipt']);
        if ($file['size'] > self::MAX_RECEIPT) $this->fail('রিসিট ৫ MB এর কম হতে হবে।', ['field' => 'receipt']);
        if ($ext === '.pdf') {
            if ((string)@file_get_contents($file['tmp_name'], false, null, 0, 5) !== '%PDF-') $this->fail('PDF ফাইলটি সঠিক নয়।', ['field' => 'receipt']);
        } elseif (@getimagesize($file['tmp_name']) === false) $this->fail('ছবিটি সঠিক নয়।', ['field' => 'receipt']);
        }

        $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 500);
        // এজেন্ট নম্বরে Cash Out: কোন নম্বর থেকে পাঠিয়েছে — অ্যাডমিন রসিদের সাথে মিলিয়ে দেখেন (নোটের শুরুতে থাকে)
        if (!$full && $method->Kind === 'agent') {
            $sender = preg_replace('/\D/', '', strtr((string)input_str('senderNumber', ''), ['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9']));
            if (str_starts_with($sender, '880')) $sender = substr($sender, 2);
            if (!preg_match('/^01[3-9]\d{8}$/', $sender)) $this->fail('যে নম্বর থেকে টাকা পাঠিয়েছেন সেটি দিন (যেমন 01712345678)।', ['field' => 'sender']);
            $note = mb_substr('যে নম্বর থেকে পাঠিয়েছেন: ' . $sender . ($note !== '' ? ' · ' . $note : ''), 0, 500);
        }
        $info = $full ? 'পুরো দাম রেফারেল ব্যালেন্স থেকে' : implode(' · ', array_filter([$acc->Title, $acc->BankName, $acc->AccountNumber]));
        // অনুমান করা যায় না এমন নাম, যাতে অন্য কেউ রিসিট খুঁজে না পায়
        $url = $full ? '' : save_upload($file, 'payments', 'rcpt_' . $uid . '_' . bin2hex(random_bytes(12)), $ext);
        try {
            if ($courseId) $this->db->EnsureCourseTables();
            $id = $this->db->CreatePaymentRequest(($courseId ? ['CourseId' => $courseId] : []) + [
                'UserId' => $uid, 'PackageId' => $courseId ? null : $pkg->Id, 'PackageName' => $courseId ? mb_substr('কোর্স: ' . $pkg->Name, 0, 80) : $pkg->Name, 'Audience' => $pkg->Audience, 'Amount' => $payable,
                'DurationMonths' => $pkg->DurationMonths, 'PostLimit' => $pkg->PostLimit, 'MethodId' => $full ? null : $method->Id, 'MethodName' => $full ? 'রেফারেল ব্যালেন্স' : $method->Name,
                'AccountId' => $full ? null : $acc->Id, 'AccountInfo' => mb_substr($info, 0, 200), 'TxnId' => $txn !== '' ? $txn : null, 'PayDate' => $date,
                'ReceiptUrl' => $url, 'Note' => $note !== '' ? $note : null,
            ]);
        } catch (Throwable $e) {
            delete_upload($url); error_log('[payment submit] ' . $e->getMessage());
            $this->fail('জমা দেওয়া যায়নি। আবার চেষ্টা করুন।');
        }
        if ($ref) { try { $this->db->SetPaymentReference($id, $ref[0], $ref[1]); } catch (Throwable $e) { error_log('[payment ref] ' . $e->getMessage()); } }
        if ($couponCode !== '') { try { $this->db->exec('UPDATE `PaymentRequests` SET `CouponCode` = :c WHERE `Id` = :i', ['c' => $couponCode, 'i' => $id]); } catch (Throwable $e) { error_log('[payment coupon] ' . $e->getMessage()); } }
        if ($disc > 0) { try { $this->db->ReserveReferralDiscount((int)$uid, $id, $disc); } catch (Throwable $e) { error_log('[payment discount] ' . $e->getMessage()); } }
        if ($refCode !== '') { try { $this->db->SetPaymentRefCode($id, $refCode); } catch (Throwable $e) { error_log('[payment refcode] ' . $e->getMessage()); } }
        $who = (string)(session_get('UserFullName') ?? session_get('UserName'));
        // টিচার হওয়ার আবেদন + পেমেন্ট একসাথে অ্যাডমিনের কাছে
        if ($pkg->Audience === 'Teacher' && session_get('UserRole') !== 'Teacher') {
            $tr = $this->db->GetLatestTeacherRequest($uid);
            if ($tr && in_array($tr->Status, ['AwaitingPayment', 'Pending', 'Expired'], true)) {
                $this->db->SetTeacherRequestState((int)$tr->Id, 'Pending', $id);
                foreach ($this->db->GetAdmins() as $a) {
                    $this->db->CreateNotification((int)$a->Id, '🎓 New Teacher Application', "$who টিচার হওয়ার আবেদন আর “{$pkg->Name}” প্যাকেজের পেমেন্ট জমা দিয়েছেন — আগে Teacher Verification (প্রতিষ্ঠানের ID কার্ড) Approve, তারপর Teacher Payment Requests এ পেমেন্ট Accept করুন।", 'info', '/Admin/Kyc?k=teacher', 'fa-chalkboard-teacher');
                }
                json_response(['success' => true, 'id' => $id, 'redirect' => '/Payment/My?new=' . $id,
                    'message' => 'পেমেন্ট আর টিচার আবেদন অ্যাডমিনের কাছে পাঠানো হয়েছে! যাচাই করে Approve করলে আপনি টিচার হয়ে যাবেন।']);
            }
        }
        foreach ($this->db->GetAdmins() as $a) {
            $this->db->CreateNotification((int)$a->Id, 'New Payment', $full ? "$who “{$pkg->Name}” প্যাকেজ পুরোটা রেফারেল ব্যালেন্স (৳$disc) দিয়ে নিতে চেয়েছেন — Accept করুন।"
                : "$who “{$pkg->Name}” প্যাকেজের জন্য " . taka($payable) . ($disc ? " (রেফারেল ব্যালেন্স থেকে ৳$disc ছাড়ের পর)" : '') . " পেমেন্টের প্রমাণ জমা দিয়েছেন ({$method->Name})।", 'info', $pkg->Audience === 'Course' ? '/Admin/CoursePayments' : ($pkg->Audience === 'Teacher' ? '/Admin/TeacherPayments' : '/Admin/StudentPayments'), 'fa-money-bill-wave');
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
