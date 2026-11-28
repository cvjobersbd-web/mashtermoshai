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
        // ৳২০ এর "১টা Apply" — শিক্ষার্থী (SApply) আর টিচার (TApply)
        if ($pkg->Audience === 'SApply') {
            if ($role === 'Teacher' || $track === 'teacher') return 'এই Apply শিক্ষার্থীদের জন্য — টিচার হিসেবে Tuition Circular এর জন্য টিচারের "১টা Apply" নিন।';
            $ks = $this->db->KycStatus((int)current_user_id(), 'student');
            if ($ks === 'Rejected') return 'আপনার Student Verification ফর্ম গ্রহণ করা হয়নি — ফর্মটা ঠিক করে আবার জমা দিন, তারপর কিনতে পারবেন।';
            if ($ks !== 'Approved' && $ks !== 'Pending') return 'Apply কেনার আগে Student Verification ফর্ম (নাম, বাবা-মা, ঠিকানা, ক্লাস, ID কার্ড) পূরণ করুন।';
            if (student_has_package((int)current_user_id())) return 'আপনার শিক্ষার্থী প্যাকেজ চালু আছে — প্যাকেজ দিয়েই যত খুশি টিচারকে Apply করতে পারবেন, আলাদা কিনতে হবে না।';
            return null;
        }
        if ($pkg->Audience === 'TApply') {
            if ($role !== 'Teacher') return 'এই Apply টিচারদের জন্য — আগে “Become a Tutor” ফর্ম পূরণ করে টিচার হোন।';
            if (teacher_profile_open((int)current_user_id())) return 'আপনার টিচার প্যাকেজ চালু আছে — প্যাকেজ দিয়েই যত খুশি টিউশনে Apply করতে পারবেন, আলাদা কিনতে হবে না।';
            return null;
        }
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

    /** টিচারের নিজের কোর্স / লাইভ ক্লাস প্রকাশ ফি — প্যাকেজের মতো অবজেক্ট */
    private function publishPkg(int $courseId): ?Obj
    {
        $c = $courseId ? $this->db->GetCourse($courseId) : null;
        $me = (int)current_user_id();
        if (!$c || (int)$c->TeacherId !== $me || $c->PublishPaid || $c->AdminBlocked) return null;
        $fee = course_publish_fee($c->Kind);
        if (!$fee) return null;
        $kl = $c->Kind === 'live' ? 'লাইভ ক্লাস' : 'কোর্স';
        return obj(['Id' => 0, 'CourseId' => $c->Id, 'Course' => $c, 'Name' => $kl . ' প্রকাশ ফি — ' . $c->Title, 'Price' => $fee, 'Audience' => 'Publish', 'IsActive' => true,
            'RegularPrice' => null, 'Discount' => 0, 'DurationLabel' => 'একবারই — পেমেন্ট Accept হলে ' . $kl . ' প্রকাশ হবে', 'DurationMonths' => 0, 'PostLimit' => 0, 'IsPublishFee' => true]);
    }

    private function publishBlockReason(Obj $pkg): ?string
    {
        if ((int)$this->db->scalar("SELECT COUNT(*) FROM `PaymentRequests` WHERE `CourseId` = :c AND `Audience` = 'Publish' AND `Status` = 'Pending'", ['c' => $pkg->CourseId]) > 0)
            return 'এই প্রকাশ ফি এর পেমেন্ট যাচাইয়ের অপেক্ষায় আছে — অ্যাডমিন Accept করলেই প্রকাশ হয়ে যাবে।';
        return null;
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
        $pubId = (int)input_int('publish', 0);
        if (current_user_id() === null) $this->redirectToAction('Login', 'Account', ['returnUrl' => $courseId ? '/Payment/Checkout?course=' . $courseId : '/Payment/Checkout/' . $id]);
        $uid = (int)current_user_id();
        if ($pubId) {
            $pkg = $this->publishPkg($pubId);
            if (!$pkg) redirect('/Course/Manage');
            $this->view(null, $pkg, ['Methods' => $this->db->GetPaymentMethods(true), 'BlockReason' => $this->publishBlockReason($pkg),
                'PendingCount' => $this->db->CountUserPendingPayments($uid), 'RefOptions' => [], 'RefSel' => ['', '']]);
        }
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

    /**
     * চেকআউট ফর্ম থেকে অর্ডার তৈরি — প্যাকেজ / কোর্স / প্রকাশ ফি, কুপন, রেফারেল ব্যালেন্স ছাড়, "কোথা থেকে জেনেছেন", রেফারেল কোড।
     * ম্যানুয়াল পেমেন্ট আর EPS দুটোই এটা ব্যবহার করে, যাতে নিয়ম একই থাকে। ভুল থাকলে fail() (JSON দিয়ে থেমে যায়)।
     */
    private function buildOrder(int $uid): array
    {
        $courseId = (int)input_int('courseId', 0);
        $pubId = (int)input_int('publishId', 0);
        if ($pubId) {
            $pkg = $this->publishPkg($pubId);
            if (!$pkg) $this->fail('এই প্রকাশ ফি দেওয়ার দরকার নেই বা কোর্স পাওয়া যায়নি।');
            if ($why = $this->publishBlockReason($pkg)) $this->fail($why);
            $courseId = $pubId;
        } elseif ($courseId) {
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
            $cp = $this->db->CheckCoupon($couponCode, $pkg, $uid, $cerr);
            if ($cp === null) $this->fail($cerr . ' (কুপন না থাকলে ঘরটা ফাঁকা রাখুন)', ['field' => 'couponCode']);
            $pkg->PriceBefore = (int)$pkg->Price;
            $pkg->Price = $cp;
        } else $couponCode = '';

        // রেফারেল ব্যালেন্স থেকে ছাড় (ঐচ্ছিক) — পুরো দাম ব্যালেন্সে হলে টাকা পাঠাতে / রিসিট দিতে হয় না
        $disc = max(0, (int)input_int('useBalance', 0));
        if ($disc > 0) {
            $bal = $this->db->GetReferralSummary($uid)['balance'];
            if ($disc > $bal) $this->fail("আপনার রেফারেল ব্যালেন্স ৳$bal — এর বেশি ছাড় নেওয়া যাবে না।", ['field' => 'useBalance']);
            if ($disc > (int)$pkg->Price) $disc = (int)$pkg->Price;
        }
        $payable = (int)$pkg->Price - $disc;

        // কোথা থেকে জেনে প্যাকেজ কিনছেন — ঐচ্ছিক, শুধু তালিকা থেকে
        $ref = null;
        $refKey = (string)input_str('reference', '');
        if ($refKey !== '') $ref = $this->db->CleanReference($refKey);

        // রেফারেল কোড (ঐচ্ছিক) — কারো কোড দিলে পেমেন্ট Accept হলে কোডের মালিক বোনাস পায় (শুধু প্রথম কেনায়)
        $refCode = strtoupper(preg_replace('/\s+/', '', (string)input_str('refCode', '')));
        if (!empty($pkg->IsApply)) $refCode = '';   // ৳২০ এর Apply কেনায় রেফারেল বোনাস নেই
        if ($refCode !== '' && (int)$pkg->Price > 0) {
            $rerr = null;
            if (!$this->db->CheckPurchaseReferralCode($refCode, $uid, $rerr)) $this->fail($rerr . ' (কোড না থাকলে ঘরটা ফাঁকা রাখুন)', ['field' => 'refCode']);
        } else $refCode = '';

        if ($this->db->CountUserPendingPayments($uid) >= 5) $this->fail('আপনার ৫টি পেমেন্ট যাচাইয়ের অপেক্ষায় আছে। অ্যাডমিন যাচাই করার পর আবার চেষ্টা করুন।');

        return ['pkg' => $pkg, 'courseId' => $courseId, 'couponCode' => $couponCode, 'disc' => $disc, 'payable' => $payable,
            'full' => $disc > 0 && $payable === 0, 'ref' => $ref, 'refCode' => $refCode];
    }

    /**
     * পেমেন্ট রিকোয়েস্ট তৈরি হওয়ার পরের কাজ — কুপন / রেফারেল / ছাড় সেভ, টিচার আবেদন Pending, অ্যাডমিনকে নোটিফিকেশন।
     * @return array ['redirect' => ..., 'message' => ...]
     */
    private function afterCreate(int $uid, array $o, int $id, string $methodName, bool $viaEps = false): array
    {
        $pkg = $o['pkg']; $disc = (int)$o['disc']; $payable = (int)$o['payable']; $full = (bool)$o['full']; $courseId = (int)$o['courseId'];
        if ($o['ref']) { try { $this->db->SetPaymentReference($id, $o['ref'][0], $o['ref'][1]); } catch (Throwable $e) { error_log('[payment ref] ' . $e->getMessage()); } }
        if ($o['couponCode'] !== '') { try { $this->db->exec('UPDATE `PaymentRequests` SET `CouponCode` = :c WHERE `Id` = :i', ['c' => $o['couponCode'], 'i' => $id]); } catch (Throwable $e) { error_log('[payment coupon] ' . $e->getMessage()); } }
        if ($disc > 0) { try { $this->db->ReserveReferralDiscount($uid, $id, $disc); } catch (Throwable $e) { error_log('[payment discount] ' . $e->getMessage()); } }
        if ($o['refCode'] !== '') { try { $this->db->SetPaymentRefCode($id, $o['refCode']); } catch (Throwable $e) { error_log('[payment refcode] ' . $e->getMessage()); } }
        // EPS থেকে ফেরার সময় সেশন নাও থাকতে পারে — তাই নাম / রোল ডাটাবেস থেকে
        $u = $viaEps ? $this->db->GetUserById($uid) : null;
        $who = $viaEps ? (string)($u->FullName ?? $u->UserName ?? '') : (string)(session_get('UserFullName') ?? session_get('UserName'));
        $role = $viaEps ? (string)($u->Role ?? '') : (string)session_get('UserRole');
        $epsNote = $viaEps ? ' ✅ EPS-এ পরিশোধ — প্যাকেজ নিজে থেকেই চালু হয়ে গেছে।' : '';
        // টিচার হওয়ার আবেদন + পেমেন্ট একসাথে অ্যাডমিনের কাছে
        if ($pkg->Audience === 'Teacher' && $role !== 'Teacher') {
            $tr = $this->db->GetLatestTeacherRequest($uid);
            if ($tr && in_array($tr->Status, ['AwaitingPayment', 'Pending', 'Expired'], true)) {
                $this->db->SetTeacherRequestState((int)$tr->Id, 'Pending', $id);
                foreach ($this->db->GetAdmins() as $a) {
                    $this->db->CreateNotification((int)$a->Id, '🎓 New Teacher Application', "$who টিচার হওয়ার আবেদন আর “{$pkg->Name}” প্যাকেজের পেমেন্ট জমা দিয়েছেন — " . ($viaEps ? 'Teacher Verification (Academic ID Card) দেখে Approve করলেই টিচার হয়ে যাবেন।' : 'আগে Teacher Verification (Academic ID Card) Approve, তারপর Teacher Payment Requests এ পেমেন্ট Accept করুন।') . $epsNote, 'info', '/Admin/Kyc?k=teacher', 'fa-chalkboard-teacher');
                }
                return ['redirect' => '/Payment/My?new=' . $id,
                    'message' => 'পেমেন্ট আর টিচার আবেদন অ্যাডমিনের কাছে পাঠানো হয়েছে! যাচাই করে Approve করলে আপনি টিচার হয়ে যাবেন।'];
            }
        }
        foreach ($this->db->GetAdmins() as $a) {
            $this->db->CreateNotification((int)$a->Id, 'New Payment', $full ? "$who “{$pkg->Name}” প্যাকেজ পুরোটা রেফারেল ব্যালেন্স (৳$disc) দিয়ে নিতে চেয়েছেন — Accept করুন।"
                : ($viaEps ? "$who “{$pkg->Name}” এর জন্য " . taka($payable) . ($disc ? " (রেফারেল ব্যালেন্স থেকে ৳$disc ছাড়ের পর)" : '') . " EPS দিয়ে পরিশোধ করেছেন।" . $epsNote
                : "$who “{$pkg->Name}” প্যাকেজের জন্য " . taka($payable) . ($disc ? " (রেফারেল ব্যালেন্স থেকে ৳$disc ছাড়ের পর)" : '') . " পেমেন্টের প্রমাণ জমা দিয়েছেন ({$methodName})।"), 'info', in_array($pkg->Audience, ['Course', 'Publish'], true) ? (($this->db->CourseKindMap()[(int)$courseId] ?? 'course') === 'live' ? '/Admin/LivePayments' : '/Admin/CoursePayments') : (in_array($pkg->Audience, ['Teacher', 'TApply'], true) ? '/Admin/TeacherPayments' : '/Admin/StudentPayments'), 'fa-money-bill-wave');
        }
        return ['redirect' => '/Payment/My?new=' . $id,
            'message' => !empty($pkg->IsApply) ? 'পেমেন্টের তথ্য জমা হয়েছে! অ্যাডমিন যাচাই করে Accept করলে আপনি ১টা Apply করতে পারবেন।' : 'পেমেন্টের তথ্য জমা হয়েছে! অ্যাডমিন যাচাই করে Accept করলে আপনার প্যাকেজ চালু হবে।'];
    }

    /** POST /Payment/Submit — packageId, methodId, accountId, txnId, payDate, note, receipt(file) */
    public function Submit_POST(): void
    {
        $uid = current_user_id();
        if ($uid === null) $this->fail('পেমেন্ট জমা দিতে লগইন করুন।');
        $o = $this->buildOrder((int)$uid);
        $pkg = $o['pkg']; $courseId = $o['courseId']; $disc = $o['disc']; $payable = $o['payable']; $full = $o['full'];
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
                'UserId' => $uid, 'PackageId' => $courseId ? null : $pkg->Id, 'PackageName' => $courseId ? mb_substr((!empty($pkg->IsPublishFee) ? '' : 'কোর্স: ') . $pkg->Name, 0, 80) : $pkg->Name, 'Audience' => $pkg->Audience, 'Amount' => $payable,
                'DurationMonths' => $pkg->DurationMonths, 'PostLimit' => $pkg->PostLimit, 'MethodId' => $full ? null : $method->Id, 'MethodName' => $full ? 'রেফারেল ব্যালেন্স' : $method->Name,
                'AccountId' => $full ? null : $acc->Id, 'AccountInfo' => mb_substr($info, 0, 200), 'TxnId' => $txn !== '' ? $txn : null, 'PayDate' => $date,
                'ReceiptUrl' => $url, 'Note' => $note !== '' ? $note : null,
            ]);
        } catch (Throwable $e) {
            delete_upload($url); error_log('[payment submit] ' . $e->getMessage());
            $this->fail('জমা দেওয়া যায়নি। আবার চেষ্টা করুন।');
        }
        $r = $this->afterCreate((int)$uid, $o, $id, $full ? 'রেফারেল ব্যালেন্স' : $method->Name);
        json_response(['success' => true, 'id' => $id] + $r);
    }

    // ============================================================
    // ===== EPS অনলাইন পেমেন্ট =====
    // ============================================================
    private function epsLoad(): bool
    {
        require_once APP_ROOT . '/app/core/Eps.php';
        return Eps::enabled();
    }

    /** EPS শেষে ব্রাউজার যেখানে ফিরবে — config এ return_base থাকলে সেটা, না হলে এই সাইটের ঠিকানা */
    private function epsReturnBase(): string
    {
        $b = rtrim((string)Eps::cfg('return_base', ''), '/');
        if ($b !== '') return $b;
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
        $host = preg_replace('/[^A-Za-z0-9.:-]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'mashtermoshai.com'));
        return ($https ? 'https' : 'http') . '://' . $host;
    }

    /** POST /Payment/EpsStart — চেকআউট ফর্ম (packageId / courseId / publishId, কুপন, ছাড়, রেফারেল) → EPS পেজের লিংক */
    public function EpsStart_POST(): void
    {
        $uid = current_user_id();
        if ($uid === null) $this->fail('পেমেন্ট করতে লগইন করুন।');
        if (!$this->epsLoad() || !Eps::availableFor((int)$uid)) $this->fail('অনলাইন পেমেন্ট (EPS) এখন চালু নেই — অন্য পেমেন্ট মেথড ব্যবহার করুন।');
        $o = $this->buildOrder((int)$uid);
        if ($o['full'] || (int)$o['payable'] <= 0) $this->fail('পুরো দাম রেফারেল ব্যালেন্স থেকে — EPS লাগবে না। ব্যালেন্স দিয়ে সাধারণভাবে Submit করুন।');
        $pkg = $o['pkg'];
        $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 300);
        // পরে (EPS থেকে ফিরে) পেমেন্ট রিকোয়েস্ট বানাতে যা লাগবে — দাম এখনকার, পরে প্যাকেজের দাম বদলালেও ইউজার যা দিয়েছে সেটাই থাকে
        $order = [
            'pkg' => ['Id' => (int)$pkg->Id, 'Name' => (string)$pkg->Name, 'Audience' => (string)$pkg->Audience, 'Price' => (int)$pkg->Price,
                'DurationMonths' => (int)$pkg->DurationMonths, 'PostLimit' => (int)$pkg->PostLimit, 'IsPublishFee' => !empty($pkg->IsPublishFee), 'IsApply' => !empty($pkg->IsApply)],
            'courseId' => (int)$o['courseId'], 'couponCode' => $o['couponCode'], 'disc' => (int)$o['disc'], 'payable' => (int)$o['payable'],
            'ref' => $o['ref'], 'refCode' => $o['refCode'], 'note' => $note,
        ];
        try { $row = Eps::create((int)$uid, (int)$o['payable'], $order); }
        catch (Throwable $e) { error_log('[eps create] ' . $e->getMessage()); $this->fail('পেমেন্ট শুরু করা যায়নি। আবার চেষ্টা করুন।'); }
        $u = $this->db->GetUserById((int)$uid);
        $ret = $this->epsReturnBase() . '/Payment/EpsReturn/' . $row->Id;
        $kind = in_array($pkg->Audience, ['Course', 'Publish'], true) ? ($pkg->Audience === 'Publish' ? 'Publish fee' : 'Course') : $pkg->Audience . ' package';
        try {
            $r = Eps::initialize([
                'merchantTxnId' => $row->MerchantTxnId, 'orderId' => 'MM' . $row->Id . '-' . $row->MerchantTxnId, 'amount' => (int)$o['payable'], 'ip' => client_ip(),
                'successUrl' => $ret, 'failUrl' => $ret, 'cancelUrl' => $ret,
                'name' => (string)($u->FullName ?? ''), 'email' => (string)($u->Email ?? ''), 'phone' => preg_replace('/\D/', '', (string)($u->MobileNumber ?? '')),
                'address' => (string)($u->StreetAddress ?? ''), 'productName' => mb_substr((string)$pkg->Name, 0, 90), 'productCategory' => $kind, 'valueA' => 'MM-' . $row->Id,
            ]);
        } catch (Throwable $e) {
            error_log('[eps init] ' . $e->getMessage());
            Eps::update($row->Id, ['Status' => 'Failed', 'LastResponse' => mb_substr($e->getMessage(), 0, 2000)]);
            $this->fail('EPS এর সাথে সংযোগ করা যায়নি — একটু পরে আবার চেষ্টা করুন, অথবা অন্য পেমেন্ট মেথড ব্যবহার করুন।');
        }
        if ($r['epsTxnId'] !== '') Eps::update($row->Id, ['EpsTxnId' => mb_substr($r['epsTxnId'], 0, 60)]);
        json_response(['success' => true, 'redirect' => $r['redirect']]);
    }

    /**
     * EPS এর সাথে লেনদেন মিলিয়ে নেওয়া: Success হলে (টাকার পরিমাণ মিললে) পেমেন্ট রিকোয়েস্ট তৈরি — একবারই, দুবার ফিরলেও না।
     * অ্যাডমিন আগের মতোই Accept করেন (Teacher / Student Verification এর নিয়ম ঠিক থাকে) — শুধু রিসিট দেখার দরকার নেই।
     */
    private function epsSync(Obj $row): Obj
    {
        if (!in_array($row->Status, ['Initiated', 'Cancelled', 'Failed'], true)) return $row;
        try { $v = Eps::verify((string)$row->MerchantTxnId); }
        catch (Throwable $e) { error_log('[eps verify] ' . $e->getMessage()); return $row; }
        $raw = (string)($v['_raw'] ?? '');
        $st = strtolower(trim((string)($v['status'] ?? '')));
        if ($st !== 'success') {
            $new = str_contains($st, 'cancel') ? 'Cancelled' : ($st === '' || $st === 'pending' || $st === 'initiated' ? $row->Status : 'Failed');
            Eps::update($row->Id, ['Status' => $new, 'LastResponse' => $raw], $row->Status);
            return Eps::get($row->Id);
        }
        $paid = (float)str_replace(',', '', (string)($v['totalamount'] ?? '0'));
        $mtx = (string)($v['merchanttransactionid'] ?? '');
        if (abs($paid - $row->Amount) > 0.009 || ($mtx !== '' && $mtx !== (string)$row->MerchantTxnId)) {
            Eps::update($row->Id, ['Status' => 'Mismatch', 'LastResponse' => $raw], $row->Status);
            error_log("[eps mismatch] row {$row->Id}: paid $paid, expected {$row->Amount}");
            foreach ($this->db->GetAdmins() as $a) {
                try { $this->db->CreateNotification((int)$a->Id, '⚠️ EPS পেমেন্টে গরমিল', "EPS লেনদেন {$row->MerchantTxnId}: পরিশোধ ৳$paid, কিন্তু হওয়ার কথা ছিল ৳{$row->Amount} — নিজে দেখে নিন।", 'danger', '/Admin/Payments', 'fa-triangle-exclamation'); } catch (Throwable $e) { }
            }
            return Eps::get($row->Id);
        }
        // একসাথে দুটো রিকোয়েস্ট এলেও শুধু একটাই এগোবে
        if (!Eps::update($row->Id, ['Status' => 'Finalizing', 'LastResponse' => $raw], $row->Status)) return Eps::get($row->Id);
        $epsTxn = mb_substr((string)($v['epstransactionid'] ?? '') ?: (string)$row->MerchantTxnId, 0, 40);
        $fe = mb_substr(trim((string)($v['financialentity'] ?? '')), 0, 40);
        $d = $row->OrderData;
        $pkg = obj($d['pkg']);
        $courseId = (int)$d['courseId'];
        $o = ['pkg' => $pkg, 'courseId' => $courseId, 'couponCode' => (string)$d['couponCode'], 'disc' => (int)$d['disc'], 'payable' => (int)$d['payable'],
            'full' => false, 'ref' => $d['ref'] ?? null, 'refCode' => (string)$d['refCode']];
        $note = 'EPS-এ পরিশোধিত' . ($fe !== '' ? ' (' . $fe . ')' : '') . ' · Ref ' . $row->MerchantTxnId . ($row->Mode === 'sandbox' ? ' · SANDBOX টেস্ট' : '') . ((string)($d['note'] ?? '') !== '' ? ' · ' . $d['note'] : '');
        try {
            if ($courseId) $this->db->EnsureCourseTables();
            $id = $this->db->CreatePaymentRequest(($courseId ? ['CourseId' => $courseId] : []) + [
                'UserId' => $row->UserId, 'PackageId' => $courseId ? null : (int)$pkg->Id,
                'PackageName' => $courseId ? mb_substr((!empty($pkg->IsPublishFee) ? '' : 'কোর্স: ') . $pkg->Name, 0, 80) : mb_substr((string)$pkg->Name, 0, 80),
                'Audience' => $pkg->Audience, 'Amount' => (int)$d['payable'], 'DurationMonths' => (int)$pkg->DurationMonths, 'PostLimit' => (int)$pkg->PostLimit,
                'MethodId' => null, 'MethodName' => 'EPS', 'AccountId' => null, 'AccountInfo' => mb_substr('EPS অনলাইন পেমেন্ট' . ($fe !== '' ? ' · ' . $fe : ''), 0, 200),
                'TxnId' => $epsTxn, 'PayDate' => date('Y-m-d'), 'ReceiptUrl' => '', 'Note' => mb_substr($note, 0, 500),
            ]);
        } catch (Throwable $e) {
            error_log('[eps finalize] ' . $e->getMessage());
            Eps::update($row->Id, ['Status' => 'Initiated'], 'Finalizing');   // পরে আবার চেষ্টা হবে
            return Eps::get($row->Id);
        }
        Eps::update($row->Id, ['Status' => 'Paid', 'PaymentRequestId' => $id, 'EpsTxnId' => $epsTxn, 'FinancialEntity' => $fe !== '' ? $fe : null]);
        try { $this->afterCreate($row->UserId, $o, $id, 'EPS', true); } catch (Throwable $e) { error_log('[eps after] ' . $e->getMessage()); }
        // EPS এ সফল = সাথে সাথে চালু (অ্যাডমিনের Accept লাগে না) — ইউজার নোটিফিকেশন পায়
        payment_auto_approve($id);
        return Eps::get($row->Id);
    }

    /** GET /Payment/EpsReturn/{id} — EPS থেকে ফেরা (সফল / ব্যর্থ / বাতিল সব একই জায়গায়; আসল অবস্থা EPS থেকে যাচাই করে) */
    public function EpsReturn(): void
    {
        require_once APP_ROOT . '/app/core/Eps.php';
        $row = null;
        try { $row = Eps::get((int)input_int('id', 0)); } catch (Throwable $e) { error_log('[eps return] ' . $e->getMessage()); }
        if (!$row) redirect('/Payment/My');
        if (Eps::enabled()) $row = $this->epsSync($row);
        $uid = current_user_id();
        // সেশন শেষ হয়ে গেলে — লগইন করে "আমার পেমেন্ট" এ দেখবে (পেমেন্ট ততক্ষণে সেভ হয়ে গেছে)
        if ($uid === null) $this->redirectToAction('Login', 'Account', ['returnUrl' => '/Payment/My']);
        if ((int)$uid !== $row->UserId) redirect('/Payment/My');
        $this->view('EpsResult', $row, ['Sandbox' => $row->Mode === 'sandbox']);
    }

    /** GET /Payment/My — আমার পেমেন্ট ও Subscription */
    public function My(): void
    {
        $uid = current_user_id();
        if ($uid === null) $this->redirectToAction('Login', 'Account', ['returnUrl' => '/Payment/My']);
        // EPS-এ টাকা দিয়ে ব্রাউজার বন্ধ করে দিলেও — এখানে এলে EPS থেকে যাচাই করে পেমেন্টটা যোগ হয়ে যায়
        try { if ($this->epsLoad()) foreach (Eps::openForUser((int)$uid) as $er) if ($er) $this->epsSync($er); } catch (Throwable $e) { error_log('[eps my] ' . $e->getMessage()); }
        $this->view(null, $this->db->GetUserPayments($uid), [
            'TeacherSub' => $this->db->GetActiveTeacherSubscription($uid),
            'Credits' => $this->db->GetStudentPostCredits($uid),
            'Rules' => $this->db->PaymentRules(),
            'NewId' => (int)input_int('new', 0),
        ]);
    }
}
