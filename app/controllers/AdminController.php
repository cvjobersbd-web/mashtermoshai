<?php
class AdminController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

    /** পেজ অ্যাকশনের জন্য: Admin না হলে Login এ পাঠায়; অনুমতি না থাকলে "অনুমতি নেই" পেজ */
    private function requireAdminPage(): void
    {
        if (!is_admin()) $this->redirectToAction('Login', 'Account');
        $rule = admin_action_rule($this->actionName());
        if (!admin_can($rule)) {
            http_response_code(403);
            $this->view('Admin/NoAccess', null, ['Rule' => $rule]);
        }
    }

    /** JSON অ্যাকশনের জন্য — অনুমতি যাচাই + POST হলে Activity Log এ লেখা */
    private function requireAdminJson(): void
    {
        if (!is_admin()) json_response(['success' => false, 'message' => 'Unauthorized']);
        $action = $this->actionName();
        $rule = admin_action_rule($action);
        if (!admin_can($rule)) {
            json_response(['success' => false, 'message' => $rule === 'super' ? 'এই কাজটা শুধু Super Admin করতে পারেন।' : 'এই কাজের অনুমতি আপনার নেই — Super Admin এর সাথে যোগাযোগ করুন।']);
        }
        if (is_post() && !isset($this->logged)) { $this->logged = true; $this->logActivity($action); }
    }

    private bool $logged;

    private function actionName(): string { return (string)($GLOBALS['__route']['action'] ?? ''); }

    /** Activity Log: কাজের নাম (বাংলায়) + কোন id / কী */
    private function logActivity(string $action): void
    {
        $labels = [
            'ApprovePayment' => 'পেমেন্ট Approve', 'RejectPayment' => 'পেমেন্ট Reject', 'DeletePayment' => 'পেমেন্ট মুছেছেন',
            'ProcessTeacherRequest' => 'টিচার আবেদন প্রসেস', 'DeleteTeacherAccount' => 'টিচার অ্যাকাউন্ট মুছেছেন',
            'SuspendUser' => 'ইউজার Suspend', 'UnsuspendUser' => 'Suspend তুলেছেন', 'ToggleUserStatus' => 'ইউজার চালু/বন্ধ', 'DeleteUser' => 'ইউজার মুছেছেন',
            'UpdatePassword' => 'ইউজারের পাসওয়ার্ড বদল', 'EduFeedStatus' => 'Educational Feed পোস্টের স্ট্যাটাস', 'UpdateReviewStatus' => 'রিভিউ স্ট্যাটাস', 'DeleteReview' => 'রিভিউ মুছেছেন',
            'SavePackage' => 'প্যাকেজ সেভ', 'DeletePackage' => 'প্যাকেজ মুছেছেন', 'TogglePackage' => 'প্যাকেজ চালু/বন্ধ', 'SetStudentPostFree' => 'শিক্ষার্থীর পোস্ট ফ্রি সুইচ',
            'SavePaymentMethod' => 'পেমেন্ট মেথড সেভ', 'SaveAgentNumbers' => 'এজেন্ট নম্বর বদল', 'SaveSubAlertDays' => 'মেয়াদ শেষের সতর্কতার দিন বদল', 'ApproveDevice' => 'নতুন ডিভাইস Accept', 'RejectDevice' => 'নতুন ডিভাইস বাতিল', 'RevokeDevice' => 'ডিভাইস লগআউট', 'SaveDeviceLock' => 'এক ডিভাইসের নিয়ম চালু/বন্ধ', 'SavePartner' => 'পার্টনার (এজেন্সি) সেভ', 'TogglePartner' => 'পার্টনার চালু/বন্ধ', 'DeletePartner' => 'পার্টনার মুছেছেন', 'ResetPartnerToken' => 'পার্টনারের রিপোর্ট লিংক বদল', 'DeletePaymentMethod' => 'পেমেন্ট মেথড মুছেছেন', 'SavePaymentAccount' => 'পেমেন্ট অ্যাকাউন্ট নম্বর সেভ',
            'DeletePaymentAccount' => 'পেমেন্ট অ্যাকাউন্ট মুছেছেন', 'SavePaymentRules' => 'পেমেন্টের নিয়ম সেভ', 'SaveMailSettings' => 'Email (SMTP) সেটিং সেভ', 'SaveMetaPixel' => 'Meta Pixel সেটিং সেভ',
            'SaveSettings' => 'সাইট সেটিং (লোগো/রঙ/হিরো) সেভ', 'SaveHomeSections' => 'হোম সেকশন চালু/বন্ধ', 'SaveHomeCategories' => 'হোমের ক্যাটাগরি সেভ',
            'SaveCategory' => 'ক্যাটাগরি সেভ', 'DeleteCategory' => 'ক্যাটাগরি মুছেছেন', 'DeleteCategoryGroup' => 'ক্যাটাগরি গ্রুপ মুছেছেন',
            'AddNotice' => 'নোটিশ যোগ', 'EditNotice' => 'নোটিশ এডিট', 'DeleteNotice' => 'নোটিশ মুছেছেন',
            'DeleteTuitionPost' => 'টিউশন পোস্ট মুছেছেন', 'UpdateApplicationStatus' => 'টিউশন অ্যাপ্লিকেশন স্ট্যাটাস',
            'SaveAdmin' => 'অ্যাডমিন যোগ/এডিট', 'AdminSetActive' => 'অ্যাডমিন চালু/বন্ধ', 'RemoveAdmin' => 'অ্যাডমিন সরিয়েছেন', 'ResetAdminPassword' => 'অ্যাডমিনের পাসওয়ার্ড রিসেট', 'UploadAdminPhoto' => 'অ্যাডমিনের প্রোফাইল ছবি', 'RemoveAdminPhoto' => 'অ্যাডমিনের প্রোফাইল ছবি সরানো',
        ];
        if ((str_starts_with($action, 'Upload') && $action !== 'UploadAdminPhoto') || in_array($action, ['CategoryDefaultText', 'SendTestEmail', 'TestMetaPixel'], true)) return; // শুধু ছবি আপলোড/প্রিভিউ — লগ নয়
        $bits = [];
        foreach (['id', 'userId', 'postId', 'paymentId', 'status', 'which', 'name', 'email'] as $k) {
            $v = $_POST[$k] ?? null;
            if (is_string($v) && $v !== '' && mb_strlen($v) <= 80) $bits[] = $k . ': ' . $v;
        }
        $label = $labels[$action] ?? $action; $detail = implode(' · ', $bits); $db = $this->db; $uid = (int)current_user_id();
        // শুধু সফল হলে লেখা হয় (ব্যর্থ চেষ্টা লগে আসে না)
        $GLOBALS['__on_json_success'] = function () use ($db, $uid, $label, $detail) { $db->LogAdminActivity($uid, $label, $detail); };
    }

    /** সাধারণ অ্যাডমিন অন্য অ্যাডমিনের অ্যাকাউন্টে হাত দিতে পারবে না (শুধু Super Admin) */
    private function guardAdminTarget(int $userId): void
    {
        $u = $userId > 0 ? $this->db->GetUserById($userId) : null;
        if ($u && $u->Role === 'Admin' && !admin_is_super()) json_response(['success' => false, 'message' => 'অন্য অ্যাডমিনের অ্যাকাউন্ট শুধু Super Admin বদলাতে পারেন।']);
        if ($u && $u->Role === 'Admin' && (int)$u->Id === (int)current_user_id() && in_array($this->actionName(), ['ToggleUserStatus', 'SuspendUser', 'DeleteUser'], true)) {
            json_response(['success' => false, 'message' => 'নিজের অ্যাকাউন্টে এটা করা যাবে না।']);
        }
    }

    // ============================================================
    // ===== DASHBOARD =====
    // ============================================================
    public function Dashboard(): void
    {
        $this->requireAdminPage();
        $users = $this->db->GetAllUsers();
        $this->view(null, $users, [
            'Suspensions' => $this->db->GetActiveSuspensionsMap(),
            'RefMap' => $this->db->GetReferralSourceMap(),
            'PartnerMap' => $this->db->GetPartnerSignupMap(),
            'UserCount' => count($users),
            'PendingCount' => $this->db->GetPendingTeacherRequestCount(),
        ]);
    }

    /** GET /Admin/TeacherOverview/{id?} */
    public function TeacherOverview(): void
    {
        $this->requireAdminPage();
        $all = $this->db->GetAllTeachers();
        $bag = ['AllTeachers' => $all];
        $id = input_int('id', null);
        if ($id !== null) {
            foreach ($all as $t) {
                if ($t->Id === $id) {
                    $bag['SelectedTeacher'] = $t;
                    $bag['RefSource'] = $this->db->GetReferralSource((int)$t->Id);
                    $bag['TeacherStats'] = $this->db->GetTeacherDashboardStats($t->Id);
                    $bag['PostStats'] = $this->db->GetTeacherPostStats($t->Id);
                    $bag['DirectRequests'] = $this->db->GetTeacherDirectRequests($t->FullName);
                    $bag['TuitionApps'] = $this->db->GetUserTuitionApplications($t->Id);
                    break;
                }
            }
        }
        $this->view(null, null, $bag);
    }

    /** GET /Admin/UserOverview/{id?} */
    public function UserOverview(): void
    {
        $this->requireAdminPage();
        $all = $this->db->GetAllNonAdminUsers();
        $bag = ['AllUsers' => $all];
        $id = input_int('id', null);
        if ($id !== null) {
            foreach ($all as $u) {
                if ($u->Id === $id) {
                    $bag['SelectedUser'] = $u;
                    $bag['RefSource'] = $this->db->GetReferralSource((int)$u->Id);
                    $bag['UserStats'] = $this->db->GetUserOverviewStats($u->Id);
                    $bag['UserPostStats'] = $this->db->GetUserPostStats($u->Id);
                    $bag['TeachersEngaged'] = $this->db->GetTeachersEngagedWithUser($u->Id);
                    $bag['UserTuitionApps'] = $this->db->GetUserTuitionApplications($u->Id);
                    break;
                }
            }
        }
        $this->view(null, null, $bag);
    }

    // ============================================================
    // ===== USER MANAGEMENT =====
    // ============================================================
    /** GET /Admin/UserDetails?id= */
    public function UserDetails(): void
    {
        $this->requireAdminJson();
        $u = $this->db->GetUserById(input_int('id', 0));
        if (!$u) json_response(['success' => false, 'message' => 'User not found']);
        $keys = ['Id', 'UserName', 'FullName', 'Email', 'Role', 'IsActive', 'MobileNumber', 'Gender', 'AgeRange', 'PrimaryDeviceType',
            'YearsOfExperience', 'AreaType', 'Country', 'StreetAddress', 'PermanentAddress', 'EducationLevel', 'CurrentStudyStatus',
            'ExamDegreeTitle', 'InstitutionName', 'PassingYear', 'IsCSEStudent', 'CvLink', 'GithubProfile', 'PortfolioLink',
            'LinkedInProfile', 'ProfileImageLink'];
        $out = [];
        foreach ($keys as $k) $out[$k] = $u->$k;
        $out['CreatedAt'] = fmt_date($u->CreatedAt, 'Y-m-d H:i');
        $out['LastLoginAt'] = $u->LastLoginAt ? fmt_date($u->LastLoginAt, 'Y-m-d H:i') : 'Never';
        // পেজের JS এগুলো সরাসরি innerHTML এ বসায় — তাই HTML-escape (XSS প্রতিরোধ)
        $out = array_map(fn($v) => is_string($v) ? e($v) : $v, $out);
        $out['JoinedVia'] = joined_via_html($this->db->GetReferralSource((int)$u->Id), true);   // নিজেই escape করা HTML
        json_camel(['success' => true, 'user' => $out]);
    }

    /** POST /Admin/DeleteUser */
    /**
     * POST /Admin/SuspendUser
     * duration: 1h | 1d | 2d | 3d | 7d | 30d | custom | permanent
     */
    public function SuspendUser_POST(): void
    {
        $this->requireAdminJson();
        $this->guardAdminTarget((int)input_int('id', 0));
        $id = input_int('id', 0) ?? 0;
        $user = $this->db->GetUserById($id);
        if (!$user) json_response(['success' => false, 'message' => 'User not found.']);
        if ($user->Role === 'Admin') json_response(['success' => false, 'message' => 'অ্যাডমিনকে Suspend করা যাবে না।']);

        $duration = (string)input_str('duration', '');
        $reason = trim((string)input_str('reason', ''));
        if (mb_strlen($reason) > 500) json_response(['success' => false, 'message' => 'কারণ সর্বোচ্চ ৫০০ অক্ষর।']);

        $presets = ['1h' => '+1 hour', '1d' => '+1 day', '2d' => '+2 days', '3d' => '+3 days', '7d' => '+7 days', '30d' => '+30 days'];
        $permanent = false; $until = null;
        if ($duration === 'permanent') {
            $permanent = true;
        } elseif (isset($presets[$duration])) {
            $until = date('Y-m-d H:i:s', strtotime($presets[$duration]));
        } elseif ($duration === 'custom') {
            $raw = trim((string)input_str('until', ''));
            $ts = $raw !== '' ? strtotime($raw) : false;
            if (!$ts) json_response(['success' => false, 'message' => 'সঠিক তারিখ ও সময় দিন।']);
            if ($ts <= time() + 60) json_response(['success' => false, 'message' => 'Suspend শেষের সময় এখনকার পরে হতে হবে।']);
            $until = date('Y-m-d H:i:s', $ts);
        } else {
            json_response(['success' => false, 'message' => 'সময় বেছে নিন।']);
        }

        if (!$this->db->SuspendUser($id, $until, $permanent, $reason === '' ? null : $reason, current_user_id())) {
            json_response(['success' => false, 'message' => 'Suspend করা যায়নি।']);
        }
        $when = $permanent ? 'স্থায়ীভাবে' : date('d M Y, h:i A', strtotime($until)) . ' পর্যন্ত';
        $this->db->CreateNotification($id, 'Account Suspended', "আপনার অ্যাকাউন্ট $when Suspend করা হয়েছে।" . ($reason !== '' ? " কারণ: $reason" : ''), 'danger', null, 'fa-user-slash');
        json_response(['success' => true, 'message' => ($user->FullName ?: $user->UserName) . " — $when Suspend করা হয়েছে।"]);
    }

    /** POST /Admin/UnsuspendUser */
    public function UnsuspendUser_POST(): void
    {
        $this->requireAdminJson();
        $this->guardAdminTarget((int)input_int('id', 0));
        $id = input_int('id', 0) ?? 0;
        if (!$this->db->LiftSuspension($id, current_user_id())) json_response(['success' => false, 'message' => 'এই ইউজার এখন Suspend অবস্থায় নেই।']);
        $this->db->CreateNotification($id, 'Suspension Lifted', 'আপনার অ্যাকাউন্টের Suspend তুলে নেওয়া হয়েছে। আবার স্বাগতম!', 'success', null, 'fa-user-check');
        json_response(['success' => true, 'message' => 'Suspend তুলে নেওয়া হয়েছে।']);
    }

    public function DeleteUser_POST(): void
    {
        $this->requireAdminJson();
        $this->guardAdminTarget((int)input_int('id', 0));
        $id = input_int('id', 0);
        if (current_user_id() === $id) json_response(['success' => false, 'message' => 'Cannot delete your own account']);
        try {
            if ($this->db->DeleteUser($id)) json_response(['success' => true, 'message' => 'User deleted successfully']);
        } catch (Throwable $e) {}
        json_response(['success' => false, 'message' => 'Failed to delete user']);
    }

    /** POST /Admin/ToggleUserStatus */
    public function ToggleUserStatus_POST(): void
    {
        $this->requireAdminJson();
        $this->guardAdminTarget((int)input_int('id', 0));
        $id = input_int('id', 0);
        $isActive = input_bool('isActive');
        if (current_user_id() === $id) json_response(['success' => false, 'message' => 'Cannot disable your own account']);
        if ($this->db->ToggleUserStatus($id, $isActive)) {
            if ($isActive) {
                $this->db->CreateNotification($id, '✅ Account Activated', 'Your account has been activated. You can now log in.', 'success', null, 'fa-check-circle');
            } else {
                $this->db->CreateNotification($id, '⚠️ Account Deactivated', 'Your account has been deactivated by an administrator.', 'warning', null, 'fa-exclamation-triangle');
            }
            json_response(['success' => true, 'message' => 'User status updated successfully']);
        }
        json_response(['success' => false, 'message' => 'Failed to update user status']);
    }

    /** POST /Admin/UpdatePassword */
    public function UpdatePassword_POST(): void
    {
        $this->requireAdminJson();
        $this->guardAdminTarget((int)input_int('id', 0));
        $id = input_int('id', 0);
        $pw = (string)input_str('newPassword', '');
        if (trim($pw) === '' || strlen($pw) < 6) json_response(['success' => false, 'message' => 'Password must be at least 6 characters']);
        $tu = $this->db->GetUserById((int)$id);
        if ($tu && $tu->Role === 'Admin' && ($pp = admin_password_problem($pw)) !== '') json_response(['success' => false, 'message' => $pp]);
        if ($this->db->UpdatePassword($id, $pw)) {
            $this->db->CreateNotification($id, '🔑 Password Changed', 'Your account password was changed by an administrator.', 'warning', null, 'fa-key');
            json_response(['success' => true, 'message' => 'Password updated successfully']);
        }
        json_response(['success' => false, 'message' => 'Failed to update password']);
    }

    // ============================================================
    // ===== TEACHER REQUESTS =====
    // ============================================================
    public function TeacherRequests(): void
    {
        $this->requireAdminPage();
        $this->db->EnsureTeacherRequestPaymentCol();
        $requests = $this->db->GetAllTeacherRequests();
        // প্রতিটা আবেদনের পেমেন্টের তথ্য (প্যাকেজ, টাকা, মেথড, TxnID, রিসিট)
        $payIds = []; $pays = [];
        foreach ($this->db->all('SELECT `Id`, `PaymentId` FROM `TeacherRequests` WHERE `PaymentId` IS NOT NULL') as $r) $payIds[(int)$r->Id] = (int)$r->PaymentId;
        foreach ($requests as $r) if (isset($payIds[(int)$r->Id])) { $p = $this->db->GetPaymentRequest($payIds[(int)$r->Id]); if ($p) $pays[(int)$r->Id] = $p; }
        $this->view(null, $requests, [
            'Payments' => $pays,
            'UserCount' => count($this->db->GetAllUsers()),
            'PendingCount' => count(array_filter($requests, fn($r) => $r->Status === 'Pending')),
        ]);
    }

    /** POST /Admin/ProcessTeacherRequest (id, action=Approve|Reject, adminNote) */
    public function ProcessTeacherRequest_POST(): void
    {
        $this->requireAdminJson();
        $id = input_int('id', 0);
        $action = input_str('action');
        $note = input_str('adminNote');
        if ($action !== 'Approve' && $action !== 'Reject') json_response(['success' => false, 'message' => 'Invalid action']);

        $status = $action === 'Approve' ? 'Approved' : 'Rejected';
        $req = $this->db->GetTeacherRequestById($id);
        // আবেদনের সাথে টিচার প্যাকেজের পেমেন্ট — Approve করলে পেমেন্টও Accept (Subscription চালু), Reject করলে পেমেন্টও বাতিল
        $this->db->EnsureTeacherRequestPaymentCol();
        $raw = $this->db->one('SELECT * FROM `TeacherRequests` WHERE `Id` = :i', ['i' => $id]);
        $pay = $raw && !empty($raw->PaymentId) ? $this->db->GetPaymentRequest((int)$raw->PaymentId) : null;
        if ($action === 'Approve' && $raw && $raw->Status === 'AwaitingPayment') json_response(['success' => false, 'message' => 'এই আবেদনকারী এখনো প্যাকেজের পেমেন্ট জমা দেননি — পেমেন্টের পর Approve করুন।']);
        if ($action === 'Approve' && $pay && $pay->Status === 'Rejected') json_response(['success' => false, 'message' => 'এই আবেদনের পেমেন্ট বাতিল করা হয়েছে — নতুন পেমেন্ট ছাড়া Approve করা যাবে না।']);
        if ($pay && $pay->Status === 'Pending') {
            if ($action === 'Approve') $this->db->ApprovePayment((int)$pay->Id, (int)current_user_id(), 'টিচার আবেদনের সাথে Approve');
            else $this->db->RejectPayment((int)$pay->Id, (int)current_user_id(), $note !== null && $note !== '' ? (string)$note : 'টিচার আবেদন গ্রহণ হয়নি');
        }
        if ($this->db->UpdateTeacherRequest($id, $status, $note)) {
            if ($action === 'Approve' && $req) {
                $this->db->MakeUserTeacher($req->UserId);
                $this->db->CreateNotification($req->UserId, "🎓 You're now a Teacher!",
                    'Congratulations! Your teacher request has been approved. Access your Teacher Dashboard now.',
                    'success', '/Teacher/TeacherDashboard', 'fa-chalkboard-teacher');
            } elseif ($action === 'Reject' && $req) {
                $msg = is_blank($note) ? 'Your teacher request was not approved. You can try again later.'
                    : "Your teacher request was not approved. Reason: $note";
                $this->db->CreateNotification($req->UserId, '❌ Teacher Request Rejected', $msg, 'danger', '/Teacher/RequestForm', 'fa-times-circle');
            }
            json_response(['success' => true, 'message' => 'Teacher request ' . strtolower($action) . 'd successfully']);
        }
        json_response(['success' => false, 'message' => 'Failed to process request']);
    }

    /** POST /Admin/DeleteTeacherAccount (id = userId, requestId) */
    public function DeleteTeacherAccount_POST(): void
    {
        // আর পুরোপুরি ডিলিট করা হয় না — টিচার থেকে সাধারণ ইউজার / স্টুডেন্ট অ্যাকাউন্টে নামিয়ে দেওয়া হয় (অ্যাকাউন্ট, পোস্ট, মেসেজ সব থাকে)
        $this->requireAdminJson();
        $id = input_int('id', 0);
        $requestId = input_int('requestId', 0);
        if ($id > 0 && current_user_id() === $id) json_response(['success' => false, 'message' => 'নিজের অ্যাকাউন্ট বদলানো যাবে না।']);
        $u = $id > 0 ? $this->db->GetUserById($id) : null;
        if ($u && $u->Role === 'Admin') json_response(['success' => false, 'message' => 'অ্যাডমিন অ্যাকাউন্ট এখান থেকে বদলানো যায় না।']);
        if ($u && $u->Role === 'Teacher') $this->db->exec("UPDATE `Users` SET `Role` = 'User' WHERE `Id` = :i AND `Role` = 'Teacher'", ['i' => $id]);
        if ($requestId > 0) $this->db->exec("UPDATE `TeacherRequests` SET `Status` = 'Removed', `AdminNote` = :n WHERE `Id` = :i", ['n' => 'অ্যাডমিন টিচার থেকে সরিয়েছেন (' . date('d M Y') . ')', 'i' => $requestId]);
        if ($u) {
            $this->db->exec("UPDATE `TeacherRequests` SET `Status` = 'Removed' WHERE `UserId` = :u AND `Status` IN ('Approved', 'Pending', 'AwaitingPayment')", ['u' => $id]);
            $this->db->CreateNotification($id, 'টিচার অ্যাকাউন্ট বন্ধ', 'অ্যাডমিন আপনার টিচার অ্যাকাউন্ট বন্ধ করেছেন — আপনার অ্যাকাউন্ট এখন সাধারণ ইউজার / স্টুডেন্ট অ্যাকাউন্ট। প্রশ্ন থাকলে Helpdesk এ যোগাযোগ করুন।', 'warning', '/Helpdesk', 'fa-user-graduate');
            json_response(['success' => true, 'message' => "{$u->FullName} এখন সাধারণ ইউজার / স্টুডেন্ট অ্যাকাউন্ট — অ্যাকাউন্ট মুছে ফেলা হয়নি।"]);
        }
        json_response(['success' => false, 'message' => 'ইউজার পাওয়া যায়নি।']);
    }

    /** GET /Admin/GetTeacherRequestDetails?id= */
    public function GetTeacherRequestDetails(): void
    {
        $this->requireAdminJson();
        $r = $this->db->GetTeacherRequestById(input_int('id', 0));
        if (!$r) json_response(['success' => false, 'message' => 'Request not found']);
        $keys = ['Id', 'UserId', 'FullName', 'Email', 'MobileNumber', 'Education', 'Institution', 'SubjectExpertise', 'Experience',
            'TeachingStyle', 'AvailableDays', 'PreferredTime', 'HourlyRate', 'CvLink', 'WhyTeach', 'Status'];
        $out = [];
        foreach ($keys as $k) $out[$k] = $r->$k;
        $out['RequestDate'] = fmt_date($r->RequestDate, 'd M Y, H:i');
        $out['AdminNote'] = $r->AdminNote;
        // পেজের JS এগুলো সরাসরি innerHTML এ বসায় — তাই HTML-escape (XSS প্রতিরোধ)
        $out = array_map(fn($v) => is_string($v) ? e($v) : $v, $out);
        json_camel(['success' => true, 'request' => $out]);
    }

    // ============================================================
    // ===== TUTOR REQUESTS (Student → Teacher) =====
    // ============================================================
    /**
     * GET /Admin/TutorRequests?status=
     * (মূল প্রজেক্টে এই রুটটি কমেন্ট করা ছিল, কিন্তু অ্যাডমিন নোটিফিকেশন এই লিংকে যায় —
     *  তাই View টি সহ পেজটি চালু রাখা হয়েছে)
     */
    public function TutorRequests(): void
    {
        $this->requireAdminPage();
        $status = input_str('status', 'all') ?: 'all';
        $stats = $this->db->GetTutorRequestStats();
        $this->view(null, $this->db->GetAllTutorRequests($status), [
            'TotalCount' => $stats->Total,
            'PendingCount' => $stats->Pending,
            'ContactedCount' => $stats->Contacted,
            'CompletedCount' => $stats->Completed,
            'CurrentStatus' => $status,
        ]);
    }

    /** POST /Admin/UpdateTutorRequestStatus */
    public function UpdateTutorRequestStatus_POST(): void
    {
        $this->requireAdminJson();
        $status = input_str('status');
        if (is_blank($status)) json_response(['success' => false, 'message' => 'Status is required.']);
        if (!in_array($status, ['Pending', 'Contacted', 'Completed', 'Cancelled', 'Accepted', 'Rejected'], true)) json_response(['success' => false, 'message' => 'Invalid status value.']);
        if ($this->db->UpdateTutorRequestStatus(input_int('id', 0), $status, input_str('adminNote'))) {
            json_response(['success' => true, 'message' => 'Status updated successfully.']);
        }
        json_response(['success' => false, 'message' => 'Failed to update status.']);
    }

    /** POST /Admin/DeleteTutorRequest */
    public function DeleteTutorRequest_POST(): void
    {
        $this->requireAdminJson();
        if ($this->db->DeleteTutorRequest(input_int('id', 0))) json_response(['success' => true, 'message' => 'Request deleted successfully.']);
        json_response(['success' => false, 'message' => 'Failed to delete request.']);
    }

    // ============================================================
    // ===== NOTICE MANAGEMENT =====
    // ============================================================
    public function ManageNotices(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->db->GetAllNotices());
    }

    public function AddNotice(): void
    {
        $this->requireAdminPage();
        $this->view(null, obj([
            'Id' => 0, 'Title' => '', 'Content' => '', 'Category' => 'News', 'ImageUrl' => '',
            'PublishedDate' => now(), 'IsAnnouncement' => false, 'IsActive' => true,
        ]));
    }

    private function noticeFromForm(): Obj
    {
        return obj([
            'Id' => input_int('Id', 0),
            'Title' => input_str('Title', ''),
            'Content' => input_str('Content', ''),
            'Category' => input_str('Category', 'News') ?: 'News',
            'ImageUrl' => input_str('ImageUrl', '') ?? '',
            'PublishedDate' => input_str('PublishedDate'),
            'IsAnnouncement' => input_bool('IsAnnouncement'),
            'IsActive' => input_bool('IsActive'),
        ]);
    }

    /** "PublishedDate" ফর্ম থেকে এলে তারিখ ঠিক করা */
    private function normalizeNoticeModel(Obj $m): void
    {
        if (strcasecmp((string)$m->Category, 'News') === 0) $m->IsAnnouncement = false;
        $d = to_dt($m->PublishedDate);
        if (!$d || (int)$d->format('Y') < 2000 || (int)$d->format('Y') > 2100) $m->PublishedDate = now();
        if ($m->IsAnnouncement) { $m->ImageUrl = ''; $m->IsActive = true; }
    }

    public function AddNotice_POST(): void
    {
        $this->requireAdminPage();
        $m = $this->noticeFromForm();
        if (is_blank($m->Title)) $this->view('AddNotice', $m, ['Error' => 'Title is required.']);
        if (is_blank($m->Content)) $this->view('AddNotice', $m, ['Error' => 'Content is required.']);
        $this->normalizeNoticeModel($m);
        $err = null;
        if ($this->db->CreateNotice($m, $err)) {
            tempdata_set('Success', $m->IsAnnouncement ? 'Announcement added successfully!' : 'News added successfully!');
            $this->redirectToAction('ManageNotices');
        }
        $this->view('AddNotice', $m, ['Error' => "Failed to add notice: $err"]);
    }

    public function EditNotice(): void
    {
        $this->requireAdminPage();
        $n = $this->db->GetNoticeById(input_int('id', 0));
        if (!$n) {
            tempdata_set('Error', 'Notice not found.');
            $this->redirectToAction('ManageNotices');
        }
        $this->view(null, $n);
    }

    public function EditNotice_POST(): void
    {
        $this->requireAdminPage();
        $m = $this->noticeFromForm();
        if (is_blank($m->Title)) $this->view('EditNotice', $m, ['Error' => 'Title is required.']);
        if (is_blank($m->Content)) $this->view('EditNotice', $m, ['Error' => 'Content is required.']);
        $this->normalizeNoticeModel($m);
        $err = null;
        if ($this->db->UpdateNotice($m, $err)) {
            tempdata_set('Success', $m->IsAnnouncement ? 'Announcement updated successfully!' : 'News updated successfully!');
            $this->redirectToAction('ManageNotices');
        }
        $this->view('EditNotice', $m, ['Error' => "Failed to update notice: $err"]);
    }

    public function DeleteNotice_POST(): void
    {
        $this->requireAdminJson();
        if ($this->db->DeleteNotice(input_int('id', 0))) json_response(['success' => true, 'message' => 'Notice deleted successfully.']);
        json_response(['success' => false, 'message' => 'Failed to delete notice.']);
    }

    /** ছবি আপলোডের কমন লজিক */
    private function uploadImage(string $field, string $dir, string $prefix, array $exts, int $maxBytes, string $sizeMsg, string $typeMsg, string $okMsg): void
    {
        $this->requireAdminJson();
        $file = upload_file($field);
        if (!$file) json_response(['success' => false, 'message' => 'Please select an image.']);
        $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $exts, true)) json_response(['success' => false, 'message' => $typeMsg]);
        if ($file['size'] > $maxBytes) json_response(['success' => false, 'message' => $sizeMsg]);
        try {
            $url = save_upload($file, $dir, $prefix . ticks(), $ext);
            json_response(['success' => true, 'message' => $okMsg, 'imageUrl' => $url]);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function UploadNoticeImage_POST(): void
    {
        $this->uploadImage('noticeImage', 'notices', 'notice_', self::IMAGE_EXTS, 5 * 1024 * 1024,
            'Image size must be less than 5MB.', 'Only JPG, PNG, GIF, or WEBP images are allowed.', 'Image uploaded successfully!');
    }

    // ============================================================
    // ===== SITE SETTINGS =====
    // ============================================================
    public function Settings(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->db->GetSiteSettings(), ['Section' => (string)input_str('s', '')]);
    }

    public function SaveSettings_POST(): void
    {
        $this->requireAdminJson();
        $s = $this->db->GetSiteSettings();
        $str = fn($k, $def) => has_input($k) ? (string)input_str($k, '') : $def;
        $s->HeroImageUrls = $str('HeroImageUrls', $s->HeroImageUrls);
        $s->TutorSectionImageUrl = $str('TutorSectionImageUrl', $s->TutorSectionImageUrl);
        $s->SiteName = $str('SiteName', $s->SiteName);
        $s->NavbarLogoUrl = $str('NavbarLogoUrl', $s->NavbarLogoUrl);
        $s->FooterLogoUrl = $str('FooterLogoUrl', $s->FooterLogoUrl);
        $s->SliderEnabled = has_input('SliderEnabled') ? input_bool('SliderEnabled') : false;
        $s->SliderIntervalSeconds = input_int('SliderIntervalSeconds', 0) ?? 0;
        $s->LogoSizePx = input_int('LogoSizePx', 0) ?? 0;
        $s->LogoShape = $str('LogoShape', 'circle');
        foreach (['PrimaryColor', 'SecondaryColor', 'AccentColor', 'TextColor', 'NavbarBgColor', 'FooterBgColor'] as $c) {
            $s->$c = $str($c, $s->$c);
        }
        if ($s->SliderIntervalSeconds < 1) $s->SliderIntervalSeconds = 5;
        if ($s->SliderIntervalSeconds > 60) $s->SliderIntervalSeconds = 60;
        $s->LogoSizePx = max(24, min(100, $s->LogoSizePx));
        $shape = strtolower(trim($s->LogoShape ?: 'circle'));
        $s->LogoShape = in_array($shape, ['circle', 'square', 'rounded'], true) ? $shape : 'circle';

        $err = null;
        if ($this->db->SaveSiteSettings($s, $err)) json_response(['success' => true, 'message' => 'Settings saved successfully!']);
        json_response(['success' => false, 'message' => "Failed: $err"]);
    }

    // ============================================================
    // ===== REVIEWS =====
    // ============================================================
    /** GET /Admin/Reviews?status= */
    public function Reviews(): void
    {
        $this->requireAdminPage();
        $all = $this->db->GetReviews('all');
        $avg = $all ? round(array_sum(array_map(fn($r) => (int)$r->Rating, $all)) / count($all), 1) : 0;
        $this->view(null, $all, [
            'TotalCount' => count($all),
            'LiveCount' => count(array_filter($all, fn($r) => $r->Status === 'Approved')),
            'AvgRating' => $avg,
        ]);
    }

    /** POST /Admin/UpdateReviewStatus */
    public function UpdateReviewStatus_POST(): void
    {
        $this->requireAdminJson();
        $status = (string)input_str('status', '');
        if (!in_array($status, ['Approved', 'Rejected', 'Pending'], true)) json_response(['success' => false, 'message' => 'Invalid status.']);
        $note = trim((string)input_str('note', ''));
        $r = $this->db->UpdateReviewStatus(input_int('id', 0) ?? 0, $status, $note === '' ? null : mb_substr($note, 0, 300));
        if (!$r) json_response(['success' => false, 'message' => 'Review not found.']);

        $link = $r->Role === 'Teacher' ? '/Teacher/TeacherDashboard?section=review' : '/User/Dashboard?section=review';
        if ($status === 'Approved') {
            $this->db->CreateNotification($r->UserId, 'Review Approved 🎉', 'আপনার রিভিউ এখন হোম পেজে দেখানো হচ্ছে। ধন্যবাদ!', 'success', $link, 'fa-star');
        } elseif ($status === 'Rejected') {
            $this->db->CreateNotification($r->UserId, 'Review Not Approved', $note !== '' ? "অ্যাডমিন: $note" : 'আপনার রিভিউটি এবার প্রকাশ করা হয়নি। চাইলে এডিট করে আবার পাঠাতে পারেন।', 'warning', $link, 'fa-star-half-alt');
        }
        json_response(['success' => true, 'status' => $status]);
    }

    /** POST /Admin/DeleteReview */
    public function DeleteReview_POST(): void
    {
        $this->requireAdminJson();
        if ($this->db->DeleteReview(input_int('id', 0) ?? 0)) json_response(['success' => true]);
        json_response(['success' => false, 'message' => 'Review not found.']);
    }

    /** POST /Admin/SaveBecomeTutorSection — হোম পেজের Become a Tutor সেকশনের লেখা */
    public function SaveBecomeTutorSection_POST(): void
    {
        $this->requireAdminJson();
        $def = become_tutor_defaults();
        $limits = ['bt_badge' => 40, 'bt_kicker' => 60, 'bt_title' => 30, 'bt_line1' => 70, 'bt_line2' => 70];
        $vals = ['bt_enabled' => input_bool('bt_enabled') ? '1' : '0'];
        foreach ($limits as $k => $max) {
            $v = trim((string)input_str($k, ''));
            if ($v === '') $v = $def[$k];
            if (mb_strlen($v) > $max) json_response(['success' => false, 'message' => "লেখাটা খুব বড় (সর্বোচ্চ $max অক্ষর)।"]);
            $vals[$k] = $v;
        }
        $perks = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)input_str('bt_perks', ''))), fn($x) => $x !== ''));
        if (count($perks) > 6) json_response(['success' => false, 'message' => 'সুবিধা সর্বোচ্চ ৬টা লাইন দেওয়া যাবে।']);
        foreach ($perks as $pk) if (mb_strlen($pk) > 60) json_response(['success' => false, 'message' => 'প্রতিটি সুবিধা সর্বোচ্চ ৬০ অক্ষর।']);
        $vals['bt_perks'] = implode("\n", $perks);

        $err = null;
        if ($this->db->SaveSiteContent($vals, $err)) json_response(['success' => true, 'message' => 'Become a Tutor সেকশন সেভ হয়েছে!']);
        json_response(['success' => false, 'message' => "Failed: $err"]);
    }

    /** POST /Admin/SaveHomeFaqs — হোম পেজের FAQ: enabled, items=[{q,a}] JSON, reset=true হলে ডিফল্ট */
    public function SaveHomeFaqs_POST(): void
    {
        $this->requireAdminJson();
        if (input_bool('reset')) {
            $this->db->SaveSiteContent(['home_faqs' => '', 'home_faq_enabled' => '1']);
            $items = home_faq_defaults();
            json_response(['success' => true, 'message' => 'Default প্রশ্ন-উত্তরে ফেরানো হয়েছে।', 'enabled' => true,
                'items' => array_map(fn($f) => ['q' => $f[0], 'a' => $f[1]], $items)]);
        }
        $raw = json_decode((string)input_str('items', '[]'), true);
        if (!is_array($raw)) json_response(['success' => false, 'message' => 'ভুল ডাটা।']);
        $clean = fn($v) => trim(preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", strip_tags((string)$v))));
        $list = [];
        foreach ($raw as $i => $f) {
            if (!is_array($f)) continue;
            $q = $clean($f['q'] ?? ''); $a = $clean($f['a'] ?? '');
            if ($q === '' && $a === '') continue;
            $n = count($list) + 1;
            if ($q === '') json_response(['success' => false, 'message' => "প্রশ্ন $n — প্রশ্ন লিখুন।", 'index' => $i]);
            if ($a === '') json_response(['success' => false, 'message' => "প্রশ্ন $n — উত্তর লিখুন।", 'index' => $i]);
            if (mb_strlen($q) > 200) json_response(['success' => false, 'message' => "প্রশ্ন $n — প্রশ্ন সর্বোচ্চ ২০০ অক্ষর।", 'index' => $i]);
            if (mb_strlen($a) > 1500) json_response(['success' => false, 'message' => "প্রশ্ন $n — উত্তর সর্বোচ্চ ১৫০০ অক্ষর।", 'index' => $i]);
            $list[] = ['q' => $q, 'a' => $a];
        }
        if (count($list) > 20) json_response(['success' => false, 'message' => 'সর্বোচ্চ ২০টা প্রশ্ন দেওয়া যাবে।']);
        $enabled = input_bool('enabled');
        $err = null;
        if ($this->db->SaveSiteContent(['home_faqs' => json_encode($list, JSON_UNESCAPED_UNICODE), 'home_faq_enabled' => $enabled ? '1' : '0'], $err)) {
            json_response(['success' => true, 'message' => 'FAQ সেভ হয়েছে — হোম পেজে বদলে গেছে!', 'enabled' => $enabled, 'items' => $list]);
        }
        json_response(['success' => false, 'message' => "Failed: $err"]);
    }

    /** POST /Admin/SaveHomeSections — sections = JSON {key: true|false} (হোম পেজের কোন সেকশন দেখাবে) */
    public function SaveHomeSections_POST(): void
    {
        $this->requireAdminJson();
        $in = json_decode((string)input_str('sections', '{}'), true);
        if (!is_array($in)) json_response(['success' => false, 'message' => 'ভুল ডাটা।']);
        $off = [];
        $vals = [];
        foreach (array_keys(home_section_list()) as $k) {
            $on = !array_key_exists($k, $in) || (bool)$in[$k];
            if ($k === 'become') $vals['bt_enabled'] = $on ? '1' : '0';
            elseif ($k === 'faq') $vals['home_faq_enabled'] = $on ? '1' : '0';
            elseif (!$on) $off[] = $k;
        }
        $vals['home_sections_off'] = json_encode($off);
        $err = null;
        if ($this->db->SaveSiteContent($vals, $err)) {
            $n = count(home_section_list()) - count($off) - ($vals['bt_enabled'] === '0' ? 1 : 0) - ($vals['home_faq_enabled'] === '0' ? 1 : 0);
            json_response(['success' => true, 'message' => "সেভ হয়েছে — হোম পেজে এখন {$n}টা সেকশন চালু।"]);
        }
        json_response(['success' => false, 'message' => "Failed: $err"]);
    }

    /** POST /Admin/SaveHomeCategories — ids = কমা দিয়ে (খালি = অটোমেটিক) */
    public function SaveHomeCategories_POST(): void
    {
        $this->requireAdminJson();
        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)input_str('ids', ''))), fn($x) => $x > 0)));
        if (count($ids) > 12) json_response(['success' => false, 'message' => 'সর্বোচ্চ ১২টা ক্যাটাগরি বাছাই করা যাবে।']);
        $valid = [];
        foreach ($this->db->GetCategoryGroups(false) as $g) foreach ($g->Categories as $c) $valid[$c->Id] = true;
        $ids = array_values(array_filter($ids, fn($i) => isset($valid[$i])));
        $err = null;
        if ($this->db->SaveSiteContent(['home_cats' => $ids ? json_encode($ids) : ''], $err)) {
            json_response(['success' => true, 'message' => $ids ? count($ids) . 'টা ক্যাটাগরি হোম পেজে দেখাবে।' : 'অটোমেটিক — প্রত্যেক গ্রুপ থেকে মিলিয়ে ৮টা দেখাবে।', 'ids' => $ids]);
        }
        json_response(['success' => false, 'message' => "Failed: $err"]);
    }

    /** POST /Admin/SaveSocialLinks — ফুটারের সোশ্যাল মিডিয়া আইকন + লিংক */
    public function SaveSocialLinks_POST(): void
    {
        $this->requireAdminJson();
        $list = json_decode((string)input_str('links', '[]'), true);
        if (!is_array($list)) json_response(['success' => false, 'message' => 'ভুল ডাটা।']);
        if (count($list) > 15) json_response(['success' => false, 'message' => 'সর্বোচ্চ ১৫টা সোশ্যাল মিডিয়া দেওয়া যাবে।']);
        $pl = social_platforms();
        $out = [];
        foreach ($list as $i => $it) {
            $p = is_array($it) ? (string)($it['p'] ?? '') : '';
            if (!isset($pl[$p])) json_response(['success' => false, 'message' => 'অজানা সোশ্যাল মিডিয়া।']);
            $raw = trim((string)($it['url'] ?? ''));
            if ($raw === '') json_response(['success' => false, 'message' => $pl[$p][0] . ' — লিংক দিন, অথবা এটা মুছে দিন।', 'index' => $i]);
            $url = social_normalize_url($p, $raw);
            if ($url === null) json_response(['success' => false, 'message' => $pl[$p][0] . ' — লিংকটা সঠিক নয়। উদাহরণ: ' . $pl[$p][2], 'index' => $i]);
            $out[] = ['p' => $p, 'url' => $url];
        }
        $err = null;
        if ($this->db->SaveSiteContent(['social_links' => json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)], $err)) {
            json_response(['success' => true, 'message' => 'সোশ্যাল মিডিয়া লিংক সেভ হয়েছে!', 'links' => $out]);
        }
        json_response(['success' => false, 'message' => "Failed: $err"]);
    }

    /** POST /Admin/SaveMailSettings — Forgot Password ইত্যাদির ইমেইল পাঠানোর SMTP সেটিং */
    public function SaveMailSettings_POST(): void
    {
        $this->requireAdminJson();
        $cur = mail_settings();
        $v = [
            'mail_site_url'   => rtrim(trim((string)input_str('mail_site_url', '')), '/'),
            'mail_host'       => trim((string)input_str('mail_host', '')),
            'mail_port'       => trim((string)input_str('mail_port', '465')),
            'mail_secure'     => strtolower(trim((string)input_str('mail_secure', 'ssl'))),
            'mail_user'       => trim((string)input_str('mail_user', '')),
            'mail_from_email' => trim((string)input_str('mail_from_email', '')),
            'mail_from_name'  => trim((string)input_str('mail_from_name', '')),
        ];
        if (!preg_match('#^https?://[A-Za-z0-9.\-]+(:\d+)?$#', $v['mail_site_url'])) json_response(['success' => false, 'message' => 'Site URL ঠিক নেই — যেমন: https://mashtermoshai.com']);
        if ($v['mail_host'] !== '' && !preg_match('/^[A-Za-z0-9.\-]{3,120}$/', $v['mail_host'])) json_response(['success' => false, 'message' => 'SMTP Host ঠিক নেই — যেমন: mail.mashtermoshai.com']);
        if (!ctype_digit($v['mail_port']) || (int)$v['mail_port'] < 1 || (int)$v['mail_port'] > 65535) json_response(['success' => false, 'message' => 'Port ঠিক নেই (465 অথবা 587)।']);
        if (!in_array($v['mail_secure'], ['ssl', 'tls', 'none'], true)) $v['mail_secure'] = 'ssl';
        if ($v['mail_user'] !== '' && !filter_var($v['mail_user'], FILTER_VALIDATE_EMAIL)) json_response(['success' => false, 'message' => 'Username হিসেবে পুরো ইমেইল ঠিকানা দিন — যেমন: noreply@mashtermoshai.com']);
        if ($v['mail_from_email'] !== '' && !filter_var($v['mail_from_email'], FILTER_VALIDATE_EMAIL)) json_response(['success' => false, 'message' => 'From Email ঠিক নেই।']);
        if (mb_strlen($v['mail_from_name']) > 80) json_response(['success' => false, 'message' => 'From Name সর্বোচ্চ ৮০ অক্ষর।']);
        $pass = (string)input_str('mail_pass', '');
        $v['mail_pass'] = $pass !== '' ? $pass : (string)$cur['mail_pass'];   // খালি রাখলে আগের পাসওয়ার্ডই থাকবে
        if (input_bool('clear_pass')) $v['mail_pass'] = '';
        $err = null;
        if ($this->db->SaveSiteContent($v, $err)) json_response(['success' => true, 'message' => 'ইমেইল সেটিং সেভ হয়েছে! এবার "Send Test Email" দিয়ে পরীক্ষা করুন।', 'hasPass' => $v['mail_pass'] !== '']);
        json_response(['success' => false, 'message' => "Failed: $err"]);
    }

    /** POST /Admin/SendTestEmail */
    public function SendTestEmail_POST(): void
    {
        $this->requireAdminJson();
        $to = trim((string)input_str('to', ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) json_response(['success' => false, 'message' => 'সঠিক ইমেইল ঠিকানা দিন।']);
        $m = Mailer::fromSettings();
        $site = site_settings()->GetSafeSiteName();
        $html = Mailer::template('টেস্ট ইমেইল ✅', '<p>অভিনন্দন! আপনার সাইটের ইমেইল সেটিং ঠিকমতো কাজ করছে। এখন থেকে Forgot Password এর ইমেইল ইউজারদের কাছে পৌঁছাবে।</p>');
        $err = null;
        if ($m->send($to, '', "$site — Test Email", $html, "আপনার সাইটের ইমেইল সেটিং ঠিকমতো কাজ করছে।", $err)) {
            json_response(['success' => true, 'message' => "টেস্ট ইমেইল পাঠানো হয়েছে: $to — ইনবক্স চেক করুন।"]);
        }
        json_response(['success' => false, 'message' => 'ইমেইল পাঠানো যায়নি: ' . $err]);
    }

    public function UploadHeroImage_POST(): void
    {
        $this->uploadImage('heroImage', 'hero', 'hero_', self::IMAGE_EXTS, 5 * 1024 * 1024,
            'Image size must be less than 5MB.', 'Only JPG, PNG, GIF, or WEBP images are allowed.', 'Image uploaded!');
    }

    public function UploadTutorSectionImage_POST(): void
    {
        $this->uploadImage('tutorSectionImage', 'tutor-section', 'tutor_', self::IMAGE_EXTS, 5 * 1024 * 1024,
            'Image size must be less than 5MB.', 'Only JPG, PNG, GIF, or WEBP images are allowed.', 'Image uploaded!');
    }

    /** POST /Admin/UploadProfilePromo — প্রোফাইল পেজের প্রোমো ছবি আপলোড */
    public function UploadProfilePromo_POST(): void
    {
        $this->uploadImage('promoImage', 'promo', 'promo_', self::IMAGE_EXTS, 5 * 1024 * 1024,
            'Image size must be less than 5MB.', 'Only JPG, PNG, GIF, or WEBP images are allowed.', 'Image uploaded!');
    }

    /** POST /Admin/SaveProfilePromo (pp_image, pp_link, pp_enabled, reset) */
    public function SaveProfilePromo_POST(): void
    {
        $this->requireAdminJson();
        // which = student (pp_*) | teacher (tp_*)
        $isT = input_str('which', 'student') === 'teacher';
        $k = $isT ? 'tp_' : 'pp_';
        $defaults = $isT ? teacher_promo_defaults() : profile_promo_defaults();
        if (input_bool('reset')) {
            $this->db->SaveSiteContent($defaults);
            json_response(['success' => true, 'message' => $isT ? 'ডিফল্ট কার্ডে ফেরত নেওয়া হয়েছে।' : 'আগের (ডিফল্ট) ছবিতে ফেরত নেওয়া হয়েছে।'] + $defaults);
        }
        $img = trim((string)input_str($k . 'image', ''));
        $link = trim((string)input_str($k . 'link', ''));
        // শুধু http(s) লিংক বা এই সাইটের /uploads/ ফাইল (টিচারের বেলায় খালি = ডিজাইন করা কার্ড)
        if (($img === '' && !$isT) || ($img !== '' && !(preg_match('#^https?://[^\s"\'<>]+$#i', $img) || preg_match('#^/uploads/[A-Za-z0-9/_.-]+$#', $img)))) {
            json_response(['success' => false, 'message' => 'ছবির লিংক ঠিক নেই — https:// দিয়ে শুরু হওয়া লিংক দিন অথবা ছবি আপলোড করুন।']);
        }
        if (mb_strlen($img) > 500) json_response(['success' => false, 'message' => 'লিংক খুব বড়।']);
        if ($link !== '' && !(preg_match('#^https?://[^\s"\'<>]+$#i', $link) || is_local_url($link))) {
            json_response(['success' => false, 'message' => 'ক্লিক লিংক ঠিক নেই — /FindTutor এর মতো সাইটের পাথ বা https:// লিংক দিন।']);
        }
        $vals = [$k . 'image' => $img, $k . 'link' => mb_substr($link, 0, 300), $k . 'enabled' => input_bool($k . 'enabled') ? '1' : '0'];
        $err = null;
        if (!$this->db->SaveSiteContent($vals, $err)) json_response(['success' => false, 'message' => 'সেভ করা যায়নি: ' . $err]);
        json_response(['success' => true, 'message' => $isT ? 'টিচার প্রোফাইলের প্রোমো সেভ হয়েছে।' : 'প্রোফাইল প্রোমো ছবি সেভ হয়েছে।'] + $vals);
    }

    /** POST /Admin/SaveCircularBanner (which = circular|findtutor, banner, enabled, reset) — পেজের উপরের ব্যানার ছবি */
    public function SaveCircularBanner_POST(): void
    {
        $this->requireAdminJson();
        $which = input_str('which', 'circular');
        [$k, $defaults, $page] = match ($which) {
            'findtutor' => ['ft_', findtutor_banner_defaults(), 'Find Tutor'],
            'package' => ['pk_', package_banner_defaults(), 'Package'],
            'helpdesk' => ['hd_', helpdesk_banner_defaults(), 'Help Desk'],
            default => ['tc_', circular_banner_defaults(), 'Tuition Circular'],
        };
        if (input_bool('reset')) {
            $this->db->SaveSiteContent($defaults);
            json_response(['success' => true, 'message' => 'আগের (ডিফল্ট) ব্যানারে ফেরত নেওয়া হয়েছে।'] + $defaults);
        }
        $img = trim((string)input_str($k . 'banner', ''));
        if ($k === 'pk_' && $img === '' && !input_bool('pk_banner_enabled')) {
            $vals = ['pk_banner' => '', 'pk_banner_enabled' => '0'];
            if (input($k . 'banner_text', null) !== null) $vals['pk_banner_text'] = input_bool('pk_banner_text') ? '1' : '0';
            $this->db->SaveSiteContent($vals);
            json_response(['success' => true, 'message' => 'Package ব্যানার সেভ হয়েছে (ছবি ছাড়া)।'] + $vals);
        }
        if ($img === '' || !(preg_match('#^https?://[^\s"\'<>]+$#i', $img) || preg_match('#^/uploads/[A-Za-z0-9/_.-]+$#', $img))) {
            json_response(['success' => false, 'message' => 'ছবির লিংক ঠিক নেই — https:// দিয়ে শুরু হওয়া লিংক দিন অথবা ছবি আপলোড করুন।']);
        }
        if (mb_strlen($img) > 500) json_response(['success' => false, 'message' => 'লিংক খুব বড়।']);
        $vals = [$k . 'banner' => $img, $k . 'banner_enabled' => input_bool($k . 'banner_enabled') ? '1' : '0'];
        if ($k === 'pk_' && input($k . 'banner_text', null) !== null) $vals['pk_banner_text'] = input_bool('pk_banner_text') ? '1' : '0';
        $err = null;
        if (!$this->db->SaveSiteContent($vals, $err)) json_response(['success' => false, 'message' => 'সেভ করা যায়নি: ' . $err]);
        json_response(['success' => true, 'message' => "$page ব্যানার সেভ হয়েছে।"] + $vals);
    }

    // ============================================================
    // ===== REFERRAL — বোনাস সেটিং, উইথড্র আবেদন (টাকা পাঠানো), রেফারেল তালিকা =====
    // ============================================================
    /** GET /Admin/Referrals?w=Pending|Paid|Rejected|all */
    public function Referrals(): void
    {
        $this->requireAdminPage();
        $w = (string)input_str('w', 'Pending');
        if (!in_array($w, ['Pending', 'Paid', 'Rejected', 'all'], true)) $w = 'Pending';
        $this->view(null, null, ['Settings' => $this->db->ReferralSettings(), 'Stats' => $this->db->ReferralAdminStats(), 'Tab' => $w,
            'Withdrawals' => $this->db->GetWithdrawalsAdmin($w), 'Referrals' => $this->db->GetReferralsAdmin(100)]);
    }

    /** POST /Admin/SaveReferralSettings (bonus, min, enabled) */
    public function SaveReferralSettings_POST(): void
    {
        $this->requireAdminJson();
        $bonus = input_int('bonus', -1); $min = input_int('min', 0);
        if ($bonus < 0 || $bonus > 10000) json_response(['success' => false, 'message' => 'বোনাস ০ থেকে ১০০০০ টাকার মধ্যে দিন।']);
        if ($min < 1 || $min > 100000) json_response(['success' => false, 'message' => 'সর্বনিম্ন উইথড্র ঠিক নেই।']);
        $this->db->SaveSiteContent(['ref_bonus' => (string)$bonus, 'ref_min_withdraw' => (string)$min, 'ref_enabled' => input_bool('enabled') ? '1' : '0']);
        json_response(['success' => true, 'message' => "সেভ হয়েছে — এখন থেকে রেফার করা কেউ প্যাকেজ কিনলে ৳{$bonus}।"]);
    }

    // ============================================================
    // ===== পার্টনার / এজেন্সি রেফারেল (লিংক + কোড) =====
    // ============================================================
    /** GET /Admin/Partners — সব পার্টনার, নতুন পার্টনার তৈরি */
    public function Partners(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->db->GetPartnersWithTotals(), ['Suggest' => $this->db->GeneratePartnerCode('AGENCY')]);
    }

    private function partnerDates(): array
    {
        $d = function (string $k): ?string { $v = trim((string)input_str($k, '')); $x = DateTime::createFromFormat('!Y-m-d', $v); return $x && $x->format('Y-m-d') === $v ? $v : null; };
        $from = $d('from'); $to = $d('to');
        if ($from && $to && $from > $to) [$from, $to] = [$to, $from];
        return [$from, $to];
    }

    /** GET /Admin/Partner/{id}?from=&to= — একটা পার্টনারের লিংক, কোড, শেয়ার, প্ল্যাটফর্ম অনুযায়ী ক্লিক / সাইনআপ / কেনা */
    public function Partner(): void
    {
        $this->requireAdminPage();
        $p = $this->db->GetPartner((int)input_int('id', 0));
        if (!$p) { tempdata_set('Error', 'পার্টনার পাওয়া যায়নি।'); $this->redirectToAction('Partners'); }
        [$from, $to] = $this->partnerDates();
        $this->view(null, $p, ['Report' => $this->db->GetPartnerReport((int)$p->Id, $from, $to), 'From' => $from, 'To' => $to]);
    }

    /** POST /Admin/SavePartner (id = 0 হলে নতুন) */
    public function SavePartner_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        $name = trim((string)input_str('name', ''));
        $code = strtoupper(trim((string)input_str('code', '')));
        $contact = mb_substr(trim((string)input_str('contact', '')), 0, 150);
        $note = mb_substr(trim((string)input_str('note', '')), 0, 300);
        $landing = input_str('landing', 'register') === 'home' ? 'home' : 'register';
        if ($name === '' || mb_strlen($name) > 100) json_response(['success' => false, 'message' => 'এজেন্সি / পার্টনারের নাম দিন (১০০ অক্ষরের মধ্যে)।']);
        if ($id > 0 && !$this->db->GetPartner($id)) json_response(['success' => false, 'message' => 'পার্টনার পাওয়া যায়নি।']);
        if ($code === '') $code = $this->db->GeneratePartnerCode($name);
        if (($err = $this->db->ValidPartnerCode($code)) !== null) json_response(['success' => false, 'message' => $err]);
        if ($this->db->PartnerCodeTaken($code, $id)) json_response(['success' => false, 'message' => "{$code} কোডটি আগেই ব্যবহার হয়েছে — অন্য কোড দিন।"]);
        if ($id > 0) {
            $this->db->UpdatePartner($id, $name, $code, $contact, $note, $landing);
            json_response(['success' => true, 'message' => 'সেভ হয়েছে।', 'id' => $id]);
        }
        $id = $this->db->CreatePartner($name, $code, $contact, $note, $landing, (int)current_user_id());
        json_response(['success' => true, 'message' => "পার্টনার তৈরি হয়েছে — কোড: {$code}", 'id' => $id]);
    }

    /** POST /Admin/TogglePartner (id, on) — বন্ধ থাকলে লিংক / কোড কাজ করে না, পুরোনো হিসাব থাকে */
    public function TogglePartner_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        if (!$this->db->GetPartner($id)) json_response(['success' => false, 'message' => 'পার্টনার পাওয়া যায়নি।']);
        $on = input_bool('on');
        $this->db->SetPartnerActive($id, $on);
        json_response(['success' => true, 'message' => $on ? 'চালু হয়েছে — লিংক আর কোড এখন কাজ করবে।' : 'বন্ধ হয়েছে — লিংক আর কোড আর কাজ করবে না (পুরোনো হিসাব থাকবে)।']);
    }

    /** POST /Admin/DeletePartner (id) — শুধু Super Admin */
    public function DeletePartner_POST(): void
    {
        $this->requireAdminJson();
        if (!admin_is_super()) json_response(['success' => false, 'message' => 'পার্টনার মুছতে পারেন শুধু Super Admin — চাইলে "বন্ধ" করে রাখুন।']);
        $id = (int)input_int('id', 0);
        if (!$this->db->GetPartner($id)) json_response(['success' => false, 'message' => 'পার্টনার পাওয়া যায়নি।']);
        $this->db->DeletePartner($id);
        json_response(['success' => true, 'message' => 'পার্টনার মুছে ফেলা হয়েছে।']);
    }

    /** POST /Admin/ResetPartnerToken (id) — এজেন্সির রিপোর্ট লিংক বদলায় (পুরোনোটা আর খুলবে না) */
    public function ResetPartnerToken_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        if (!$this->db->GetPartner($id)) json_response(['success' => false, 'message' => 'পার্টনার পাওয়া যায়নি।']);
        $t = $this->db->ResetPartnerToken($id);
        json_response(['success' => true, 'message' => 'নতুন রিপোর্ট লিংক তৈরি হয়েছে — পুরোনো লিংক আর খুলবে না।', 'url' => site_public_url() . '/Partner/Report?t=' . $t]);
    }

    /** GET /Admin/PartnerCsv/{id}?from=&to= — এই পার্টনার থেকে আসা ইউজারদের তালিকা (CSV) */
    public function PartnerCsv(): void
    {
        $this->requireAdminPage();
        $p = $this->db->GetPartner((int)input_int('id', 0));
        if (!$p) $this->redirectToAction('Partners');
        [$from, $to] = $this->partnerDates();
        $r = $this->db->GetPartnerReport((int)$p->Id, $from, $to);
        $safe = fn($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
        $name = 'partner_' . preg_replace('/[^A-Za-z0-9_-]/', '', (string)$p->Code) . '_' . ($from ?: 'start') . '_to_' . ($to ?: date('Y-m-d')) . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Cache-Control: no-store');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Partner', $p->Name, 'Code', $p->Code, 'From', $from ?: 'Start', 'To', $to ?: date('Y-m-d')]);
        fputcsv($out, []);
        fputcsv($out, ['Platform', 'Clicks', 'Unique Visitors', 'Signups', 'Email Verified', 'Buyers', 'Orders', 'Revenue (BDT)', 'Pending Payments']);
        foreach ($r['sources'] as $s => $x) fputcsv($out, [Db::partnerSource($s)[0], $x['clicks'], $x['visitors'], $x['signups'], $x['verified'], $x['buyers'], $x['orders'], $x['revenue'], $x['pending']]);
        $t = $r['totals'];
        fputcsv($out, ['TOTAL', $t['clicks'], $t['visitors'], $t['signups'], $t['verified'], $t['buyers'], $t['orders'], $t['revenue'], $t['pending']]);
        fputcsv($out, []);
        fputcsv($out, ['#', 'Signup Date', 'Name', 'Username', 'Email', 'Phone', 'Role', 'Joined Via', 'Platform', 'Email Verified', 'Packages Bought', 'Total Paid (BDT)', 'Last Package', 'Pending Payments']);
        foreach ($r['users'] as $i => $u) {
            fputcsv($out, array_map($safe, [$i + 1, fmt_date($u->SignedUp, 'Y-m-d H:i'), (string)$u->FullName, (string)$u->UserName, (string)$u->Email, (string)($u->MobileNumber ?? ''), $u->Role === 'Teacher' ? 'Teacher' : ($u->Role === 'Admin' ? 'Admin' : 'Student'),
                $u->Via === 'code' ? 'Code' : 'Link', Db::partnerSource((string)$u->Source)[0], $u->Verified ? 'Yes' : 'No', $u->Orders, $u->Paid, $u->LastPackage, $u->Pending]));
        }
        fclose($out);
        exit;
    }

    // ============================================================
    // ===== META PIXEL & CONVERSIONS API (শুধু Super Admin) =====
    // ============================================================
    /** GET /Admin/MetaPixel */
    public function MetaPixel(): void
    {
        $this->requireAdminPage();
        $c = px_settings(true);
        $tok = trim((string)$c['px_capi_token']);
        $this->view(null, null, [
            'S' => $c, 'HasToken' => $tok !== '', 'TokenHint' => $tok !== '' ? '••••' . substr($tok, -4) : '',
            'Events' => PIXEL_EVENTS, 'CurlOk' => function_exists('curl_init') || ini_get('allow_url_fopen'),
        ]);
    }

    /** POST /Admin/SaveMetaPixel */
    public function SaveMetaPixel_POST(): void
    {
        $this->requireAdminJson();
        $id = preg_replace('/\s+/', '', (string)input_str('pixelId', ''));
        $on = input_bool('enabled');
        if ($id !== '' && !preg_match('/^\d{6,20}$/', $id)) json_response(['success' => false, 'message' => 'Pixel ID শুধু সংখ্যা হবে (সাধারণত ১৫–১৬ ডিজিট)।']);
        if ($on && $id === '') json_response(['success' => false, 'message' => 'Pixel চালু করতে Pixel ID দিন।']);
        $test = trim((string)input_str('testCode', ''));
        if ($test !== '' && !preg_match('/^[A-Za-z0-9_\-]{3,40}$/', $test)) json_response(['success' => false, 'message' => 'Test event code ঠিক নেই (যেমন TEST12345)।']);
        $save = ['px_enabled' => $on ? '1' : '0', 'meta_pixel_id' => $id, 'px_test_code' => $test, 'px_adv_match' => input_bool('advMatch') ? '1' : '0'];
        $tok = trim((string)input_str('capiToken', ''));
        if (input_bool('clearToken')) $save['px_capi_token'] = '';
        elseif ($tok !== '') {
            if (!preg_match('/^[A-Za-z0-9_\-|]{40,600}$/', $tok)) json_response(['success' => false, 'message' => 'Access token ঠিক মনে হচ্ছে না — Events Manager থেকে পুরোটা কপি করে দিন।']);
            $save['px_capi_token'] = $tok;
        }
        $evs = (array)($_POST['events'] ?? []);
        foreach (PIXEL_EVENTS as $ev => $_) $save['px_ev_' . $ev] = in_array($ev, $evs, true) ? '1' : '0';
        $this->db->SaveSiteContent($save);
        $c = px_settings(true);
        $n = count(array_filter(array_keys(PIXEL_EVENTS), fn($e) => ($c['px_ev_' . $e] ?? '1') !== '0'));
        json_response(['success' => true, 'message' => 'সেভ হয়েছে।', 'status' => [
            'active' => $c['px_enabled'] !== '0' && $c['meta_pixel_id'] !== '', 'pixel' => $c['meta_pixel_id'],
            'mode' => trim((string)$c['px_capi_token']) !== '' ? 'Browser + Server (CAPI)' : 'Browser only', 'events' => $n, 'total' => count(PIXEL_EVENTS),
            'tokenHint' => trim((string)$c['px_capi_token']) !== '' ? '••••' . substr(trim((string)$c['px_capi_token']), -4) : '',
        ]]);
    }

    /** POST /Admin/TestMetaPixel — সার্ভার থেকে একটা test PageView পাঠিয়ে Meta এর উত্তর দেখায় */
    public function TestMetaPixel_POST(): void
    {
        $this->requireAdminJson();
        $c = px_settings(true);
        if ($c['px_enabled'] === '0' || $c['meta_pixel_id'] === '') json_response(['success' => false, 'message' => 'আগে Pixel চালু করে Pixel ID সেভ করুন।']);
        if (trim((string)$c['px_capi_token']) === '') json_response(['success' => false, 'message' => 'সার্ভার টেস্টের জন্য Conversions API access token লাগবে। Token ছাড়া ব্রাউজার Pixel ঠিকই কাজ করে — Events Manager → Test Events এ সাইট খুলে দেখুন।']);
        $code = trim((string)$c['px_test_code']);
        $ev = ['event_name' => 'PageView', 'event_time' => time(), 'event_id' => 'test-' . bin2hex(random_bytes(6)), 'action_source' => 'website',
            'event_source_url' => site_public_url() . '/', 'user_data' => ['client_ip_address' => (string)($_SERVER['REMOTE_ADDR'] ?? ''), 'client_user_agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? 'MashterMoshai-Admin-Test')]];
        [$ok, $http, $res] = capi_send([$ev], $code);
        $j = json_decode($res, true);
        if ($ok) {
            $n = (int)($j['events_received'] ?? 0);
            json_response(['success' => true, 'message' => "Meta ইভেন্ট গ্রহণ করেছে (events_received: {$n})।" . ($code !== '' ? " Events Manager → Test Events এ \"{$code}\" দিয়ে দেখুন।" : ' Test event code দিলে Test Events ট্যাবে সাথে সাথে দেখা যায়।'), 'raw' => $j ?? $res]);
        }
        $msg = (string)($j['error']['error_user_msg'] ?? $j['error']['message'] ?? $res);
        json_response(['success' => false, 'message' => 'Meta ইভেন্ট নেয়নি' . ($http ? " (HTTP {$http})" : '') . ': ' . mb_substr($msg, 0, 300), 'raw' => $j ?? $res]);
    }

    /** POST /Admin/ProcessWithdrawal (id, status = Paid|Rejected, txn, note) */
    public function ProcessWithdrawal_POST(): void
    {
        $this->requireAdminJson();
        $w = $this->db->GetWithdrawal(input_int('id', 0));
        if (!$w) json_response(['success' => false, 'message' => 'আবেদনটি পাওয়া যায়নি।']);
        if ($w->Status !== 'Pending') json_response(['success' => false, 'message' => 'এই আবেদন আগেই প্রক্রিয়া করা হয়েছে।']);
        $status = (string)input_str('status', '');
        if (!in_array($status, ['Paid', 'Rejected'], true)) json_response(['success' => false, 'message' => 'Invalid status.']);
        $txn = mb_substr(trim((string)input_str('txn', '')), 0, 60); $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 300);
        if ($status === 'Rejected' && $note === '') json_response(['success' => false, 'message' => 'বাতিলের কারণ লিখুন — ইউজার দেখতে পাবে।']);
        $this->db->SetWithdrawalStatus((int)$w->Id, $status, $txn, $note, (int)current_user_id());
        $amt = (int)$w->Amount;
        if ($status === 'Paid') {
            $this->db->CreateNotification((int)$w->UserId, 'টাকা পাঠানো হয়েছে 💸', "আপনার ৳$amt {$w->Method} এ পাঠানো হয়েছে" . ($txn !== '' ? " (TxnID: $txn)" : '') . '।', 'success', '/Referral', 'fa-money-bill-transfer');
        } else {
            $this->db->CreateNotification((int)$w->UserId, 'উইথড্র আবেদন বাতিল', "৳$amt এর আবেদন বাতিল হয়েছে: $note — টাকা আবার ব্যালেন্সে ফেরত এসেছে।", 'warning', '/Referral', 'fa-circle-xmark');
        }
        json_response(['success' => true, 'message' => $status === 'Paid' ? 'Paid হিসেবে চিহ্নিত হয়েছে।' : 'আবেদন বাতিল হয়েছে — টাকা ইউজারের ব্যালেন্সে ফেরত গেছে।']);
    }

    /** POST /Admin/SetReferralStatus (id, status = Credited|Revoked) — ভুয়া রেফারেল বাতিল */
    public function SetReferralStatus_POST(): void
    {
        $this->requireAdminJson();
        $status = (string)input_str('status', '');
        if (!in_array($status, ['Credited', 'Revoked'], true)) json_response(['success' => false, 'message' => 'Invalid status.']);
        $this->db->SetReferralStatus(input_int('id', 0), $status);
        json_response(['success' => true, 'message' => $status === 'Revoked' ? 'রেফারেল বোনাস বাতিল হয়েছে।' : 'বোনাস আবার চালু হয়েছে।']);
    }

    /** POST /Admin/SaveKeyFeatures (kf_img_1..6, reset) — হোম পেজের Key Features কার্ডের ছবি (খালি = আইকন প্যানেল) */
    public function SaveKeyFeatures_POST(): void
    {
        $this->requireAdminJson();
        $vals = [];
        foreach (key_features_defaults() as $i => $d) {
            $img = input_bool('reset') ? $d[4] : trim((string)input_str('kf_img_' . $i, ''));
            if ($img !== '' && (mb_strlen($img) > 500 || !(preg_match('#^https?://[^\s"\'<>]+$#i', $img) || preg_match('#^/uploads/[A-Za-z0-9/_.-]+$#', $img)))) {
                json_response(['success' => false, 'message' => "{$d[0]} — ছবির লিংক ঠিক নেই। https:// দিয়ে শুরু হওয়া লিংক দিন অথবা ছবি আপলোড করুন।"]);
            }
            $vals['kf_img_' . $i] = $img;
        }
        $err = null;
        if (!$this->db->SaveSiteContent($vals, $err)) json_response(['success' => false, 'message' => 'সেভ করা যায়নি: ' . $err]);
        json_response(['success' => true, 'message' => input_bool('reset') ? 'Key Features এর ছবি ডিফল্টে ফেরত নেওয়া হয়েছে।' : 'Key Features এর ছবি সেভ হয়েছে।', 'images' => $vals]);
    }

    /** POST /Admin/SaveTuitionOptions (which = locations|classes|subjects, data = JSON, reset) — টিউশনের লোকেশন / ক্লাস / বিষয়ের তালিকা */
    public function SaveTuitionOptions_POST(): void
    {
        $this->requireAdminJson();
        $which = (string)input_str('which', '');
        if (!in_array($which, ['locations', 'classes', 'subjects'], true)) json_response(['success' => false, 'message' => 'ভুল অনুরোধ।']);
        $key = 'opt_' . $which;
        if (input_bool('reset')) {
            $this->db->SaveSiteContent([$key => '']);
            $def = ['locations' => default_bd_locations(), 'classes' => default_tuition_classes(), 'subjects' => default_tuition_subjects()][$which];
            json_response(['success' => true, 'message' => 'ডিফল্ট তালিকায় ফেরত নেওয়া হয়েছে।', 'data' => $def]);
        }
        $data = json_decode((string)input_str('data', ''), true);
        if (!is_array($data)) json_response(['success' => false, 'message' => 'তালিকা ঠিক নেই।']);
        $clean = function ($v) { return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)$v))), 0, 60); };
        $uniq = function (array $list) use ($clean) {
            $out = []; $seen = [];
            foreach ($list as $v) { $v = $clean($v); $k = mb_strtolower($v); if ($v !== '' && !isset($seen[$k])) { $seen[$k] = 1; $out[] = $v; } }
            return $out;
        };
        if ($which === 'locations') {
            $out = [];
            foreach ($data as $district => $areas) {
                $d = $clean($district);
                if ($d === '' || isset($out[$d])) continue;
                $out[$d] = $uniq(is_array($areas) ? $areas : []);
                if (count($out[$d]) > 200) $out[$d] = array_slice($out[$d], 0, 200);
            }
            if (!$out) json_response(['success' => false, 'message' => 'অন্তত একটা জেলা রাখুন।']);
            if (count($out) > 200) json_response(['success' => false, 'message' => 'অনেক বেশি জেলা।']);
        } else {
            $out = $uniq($data);
            if (!$out) json_response(['success' => false, 'message' => 'অন্তত একটা রাখুন।']);
            if (count($out) > 300) json_response(['success' => false, 'message' => 'অনেক বেশি আইটেম।']);
        }
        $err = null;
        if (!$this->db->SaveSiteContent([$key => json_encode($out, JSON_UNESCAPED_UNICODE)], $err)) json_response(['success' => false, 'message' => 'সেভ করা যায়নি: ' . $err]);
        json_response(['success' => true, 'message' => 'সেভ হয়েছে।', 'data' => $out]);
    }

    // ============================================================
    // ===== PACKAGES (Teacher Subscription / Student Tuition Post) =====
    // ============================================================
    /** GET /Admin/Packages */
    public function Packages(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->db->GetPackages(), ['Benefits' => package_benefits(), 'StudentFree' => $this->db->PaymentRules()['studentFree'] ?? false]);
    }

    /** POST /Admin/SavePackage — নতুন বা এডিট */
    public function SavePackage_POST(): void
    {
        $this->requireAdminJson();
        $clean = fn($v, $max) => mb_substr(trim(preg_replace('/[ \t]+/u', ' ', strip_tags((string)$v))), 0, $max);
        $aud = input_str('audience', '');
        if (!in_array($aud, Db::PACKAGE_AUDIENCES, true)) json_response(['success' => false, 'message' => 'ভুল প্যাকেজের ধরন।']);
        $name = $clean(input_str('name', ''), 80);
        if ($name === '') json_response(['success' => false, 'message' => 'প্যাকেজের নাম দিন।']);
        $price = input_str('price', '');
        if (!preg_match('/^\d{1,7}$/', trim((string)$price))) json_response(['success' => false, 'message' => 'সঠিক দাম দিন (শুধু সংখ্যা)।']);
        $price = (int)$price;
        $reg = trim((string)input_str('regularPrice', ''));
        if ($reg !== '' && !preg_match('/^\d{1,7}$/', $reg)) json_response(['success' => false, 'message' => 'নিয়মিত মূল্য শুধু সংখ্যা হবে।']);
        $reg = $reg === '' ? null : (int)$reg;
        if ($reg !== null && $reg <= $price) json_response(['success' => false, 'message' => 'নিয়মিত মূল্য অবশ্যই বর্তমান দামের চেয়ে বেশি হতে হবে (না হলে খালি রাখুন)।']);
        $months = max(0, min(120, (int)input_int('durationMonths', 0)));
        $limit = max(0, min(1000, (int)input_int('postLimit', 0)));
        if ($aud === 'Teacher' && $months < 1) json_response(['success' => false, 'message' => 'টিচার প্যাকেজের মেয়াদ (মাস) দিন।']);
        $features = implode("\n", array_slice(array_values(array_filter(array_map(fn($l) => $clean($l, 120), preg_split('/\r?\n/', (string)input_str('features', ''))))), 0, 15));
        $data = [
            'Audience' => $aud, 'Name' => $name, 'DurationMonths' => $months, 'PostLimit' => $aud === 'Student' ? $limit : 0,
            'Price' => $price, 'RegularPrice' => $reg, 'Badge' => $clean(input_str('badge', ''), 40),
            'Description' => $clean(input_str('description', ''), 300), 'Features' => $features,
            'IsActive' => input_bool('isActive', true) ? 1 : 0, 'IsFeatured' => input_bool('isFeatured') ? 1 : 0,
        ];
        $id = (int)input_int('id', 0);
        if ($id > 0 && !$this->db->GetPackage($id)) json_response(['success' => false, 'message' => 'প্যাকেজ পাওয়া যায়নি।']);
        $id = $this->db->SavePackage($id, $data);
        json_response(['success' => true, 'message' => 'প্যাকেজ সেভ হয়েছে।', 'package' => $this->db->GetPackage($id)]);
    }

    /** POST /Admin/SavePackageBenefits — audience=Teacher|Student, data=[{i,t,d}] JSON, reset=true হলে ডিফল্ট */
    public function SavePackageBenefits_POST(): void
    {
        $this->requireAdminJson();
        $aud = input_str('audience', '');
        if (!in_array($aud, Db::PACKAGE_AUDIENCES, true)) json_response(['success' => false, 'message' => 'Invalid']);
        $key = $aud === 'Teacher' ? 'pkg_benefits_teacher' : 'pkg_benefits_student';
        if (input_bool('reset')) {
            $this->db->SaveSiteContent([$key => '']);
            json_response(['success' => true, 'list' => package_benefits()[$aud]]);
        }
        $raw = json_decode((string)input_str('data', '[]'), true);
        if (!is_array($raw)) json_response(['success' => false, 'message' => 'Invalid data']);
        $icons = package_benefit_icons();
        $clean = fn($v, $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)$v))), 0, $max);
        $list = [];
        foreach (array_slice($raw, 0, 20) as $b) {
            if (!is_array($b)) continue;
            $t = $clean($b['t'] ?? '', 60);
            if ($t === '') continue;
            $list[] = ['i' => in_array($b['i'] ?? '', $icons, true) ? $b['i'] : 'fa-check', 't' => $t, 'd' => $clean($b['d'] ?? '', 250)];
        }
        $this->db->SaveSiteContent([$key => json_encode($list, JSON_UNESCAPED_UNICODE)]);
        json_response(['success' => true, 'list' => $list]);
    }

    /** POST /Admin/SetStudentPostFree (on) — শিক্ষার্থীদের Tuition পোস্ট ফ্রি / পেইড */
    public function SetStudentPostFree_POST(): void
    {
        $this->requireAdminJson();
        $this->db->SaveSiteContent(['pkg_student_free' => input_bool('on') ? '1' : '']);
        json_response(['success' => true, 'free' => $this->db->PaymentRules()['studentFree'] ?? false]);
    }

    public function DeletePackage_POST(): void
    {
        $this->requireAdminJson();
        $this->db->DeletePackage((int)input_int('id', 0));
        json_response(['success' => true]);
    }

    /** POST /Admin/TogglePackage — flag = active|featured */
    public function TogglePackage_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        if (!$this->db->GetPackage($id)) json_response(['success' => false, 'message' => 'প্যাকেজ পাওয়া যায়নি।']);
        $this->db->SetPackageFlag($id, input_str('flag') === 'featured' ? 'IsFeatured' : 'IsActive', input_bool('on'));
        json_response(['success' => true, 'package' => $this->db->GetPackage($id)]);
    }

    /** POST /Admin/ReorderPackages — audience + ids (কমা দিয়ে) */
    public function ReorderPackages_POST(): void
    {
        $this->requireAdminJson();
        $aud = input_str('audience', '');
        if (!in_array($aud, Db::PACKAGE_AUDIENCES, true)) json_response(['success' => false, 'message' => 'Invalid']);
        $ids = array_filter(array_map('intval', explode(',', (string)input_str('ids', ''))));
        $this->db->ReorderPackages($aud, $ids);
        json_response(['success' => true]);
    }

    // ============================================================
    // ===== ADMINS (শুধু Super Admin) — অ্যাডমিন যোগ, অনুমতি, চালু/বন্ধ, Activity Log =====
    // ============================================================
    /** GET /Admin/Admins */
    public function Admins(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->db->GetAdminList());
    }

    /** POST /Admin/SaveAdmin — id (0 = নতুন), fullName, email, password, isSuper, perms (কমা দিয়ে) */
    public function SaveAdmin_POST(): void
    {
        $this->requireAdminJson();
        $me = (int)current_user_id();
        $id = (int)input_int('id', 0);
        $super = input_bool('isSuper');
        $perms = array_values(array_intersect(array_filter(explode(',', (string)input_str('perms', ''))), array_keys(admin_permissions())));
        if (!$super && !$perms) json_response(['success' => false, 'message' => 'সাধারণ অ্যাডমিনের জন্য অন্তত একটা অংশে টিক দিন, অথবা Super Admin বানান।']);
        if ($id > 0) {
            $a = $this->db->GetAdminAccess($id);
            if ($a === null) json_response(['success' => false, 'message' => 'অ্যাডমিন পাওয়া যায়নি।']);
            if ($a['super'] && !$super && $this->db->CountActiveSuperAdmins($id) === 0) json_response(['success' => false, 'message' => 'অন্তত একজন Super Admin থাকতেই হবে।']);
            if ($id === $me && !$super) json_response(['success' => false, 'message' => 'নিজেকে Super Admin থেকে নামানো যাবে না — অন্য Super Admin কে দিয়ে করান।']);
            $this->db->SetAdminAccess($id, $super, $perms, $me);
            json_response(['success' => true, 'message' => 'অনুমতি সেভ হয়েছে।']);
        }
        $name = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str('fullName', '')))), 0, 80);
        $email = strtolower(trim((string)input_str('email', '')));
        $pw = (string)input_str('password', '');
        if ($name === '') json_response(['success' => false, 'message' => 'নাম লিখুন।', 'field' => 'fullName']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) json_response(['success' => false, 'message' => 'সঠিক ইমেইল দিন।', 'field' => 'email']);
        if (($pp = admin_password_problem($pw)) !== '') json_response(['success' => false, 'message' => $pp, 'field' => 'password']);
        $ex = $this->db->one("SELECT `Id`, `Role` FROM `Users` WHERE LOWER(`Email`) = LOWER(:e) ORDER BY `Id` LIMIT 1", ['e' => $email]);
        if ($ex && $ex->Role === 'Admin') json_response(['success' => false, 'message' => 'এই ইমেইলে আগে থেকেই একজন অ্যাডমিন আছেন।', 'field' => 'email']);
        try {
            [$newId, $was] = $this->db->CreateOrPromoteAdmin($name, $email, $pw);
            $this->db->SetAdminAccess($newId, $super, $perms, $me);
        } catch (Throwable $e) {
            error_log('[SaveAdmin] ' . $e->getMessage());
            json_response(['success' => false, 'message' => 'অ্যাডমিন বানানো যায়নি। আবার চেষ্টা করুন।']);
        }
        $u = $this->db->GetUserById($newId);
        json_response(['success' => true, 'message' => ($was ? 'আগের অ্যাকাউন্টটাকে ' : 'নতুন ') . ($super ? 'Super Admin' : 'Admin') . ' বানানো হয়েছে। লগইন: ' . $email . ' (অথবা username: ' . ($u->UserName ?? '') . ')']);
    }

    /** POST /Admin/AdminSetActive — id, on */
    public function AdminSetActive_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0); $on = input_bool('on');
        $a = $this->db->GetAdminAccess($id);
        if ($a === null) json_response(['success' => false, 'message' => 'অ্যাডমিন পাওয়া যায়নি।']);
        if ($id === (int)current_user_id()) json_response(['success' => false, 'message' => 'নিজেকে বন্ধ করা যাবে না।']);
        if (!$on && $a['super'] && $this->db->CountActiveSuperAdmins($id) === 0) json_response(['success' => false, 'message' => 'শেষ Super Admin কে বন্ধ করা যাবে না।']);
        $this->db->SetUserActive($id, $on);
        json_response(['success' => true, 'message' => $on ? 'অ্যাডমিন আবার চালু হয়েছে।' : 'অ্যাডমিন বন্ধ — সাথে সাথে লগআউট হয়ে গেছে, আর লগইন করতে পারবে না।']);
    }

    /** POST /Admin/RemoveAdmin — id (অ্যাকাউন্ট থাকে, শুধু অ্যাডমিন ক্ষমতা যায়) */
    public function RemoveAdmin_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        $a = $this->db->GetAdminAccess($id);
        if ($a === null) json_response(['success' => false, 'message' => 'অ্যাডমিন পাওয়া যায়নি।']);
        if ($id === (int)current_user_id()) json_response(['success' => false, 'message' => 'নিজেকে সরানো যাবে না — অন্য Super Admin কে দিয়ে করান।']);
        if ($a['super'] && $this->db->CountActiveSuperAdmins($id) === 0) json_response(['success' => false, 'message' => 'শেষ Super Admin কে সরানো যাবে না।']);
        $this->db->RemoveAdmin($id);
        json_response(['success' => true, 'message' => 'অ্যাডমিন থেকে সরানো হয়েছে (অ্যাকাউন্টটা এখন সাধারণ ইউজার)।']);
    }

    /** POST /Admin/ResetAdminPassword — id, password */
    public function ResetAdminPassword_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0); $pw = (string)input_str('password', '');
        if ($this->db->GetAdminAccess($id) === null) json_response(['success' => false, 'message' => 'অ্যাডমিন পাওয়া যায়নি।']);
        if (($pp = admin_password_problem($pw)) !== '') json_response(['success' => false, 'message' => $pp]);
        $this->db->UpdatePassword($id, $pw);
        json_response(['success' => true, 'message' => 'পাসওয়ার্ড বদলানো হয়েছে।']);
    }

    /** POST /Admin/UploadAdminPhoto — id, photo (ফাইল) — অ্যাডমিনের প্রোফাইল ছবি (Super Admin) */
    public function UploadAdminPhoto_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        $u = $this->db->GetUserById($id);
        if (!$u || $u->Role !== 'Admin') json_response(['success' => false, 'message' => 'অ্যাডমিন পাওয়া যায়নি।']);
        $file = upload_file('photo');
        if (!$file) json_response(['success' => false, 'message' => 'একটা ছবি বেছে নিন।']);
        $ext = strtolower('.' . pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::IMAGE_EXTS, true)) json_response(['success' => false, 'message' => 'শুধু JPG, PNG, GIF বা WEBP ছবি দেওয়া যাবে।']);
        if ($file['size'] > 5 * 1024 * 1024) json_response(['success' => false, 'message' => 'ছবি ৫MB এর কম হতে হবে।']);
        if (@getimagesize($file['tmp_name']) === false) json_response(['success' => false, 'message' => 'ফাইলটি সঠিক ছবি নয়।']);
        try { $url = save_upload($file, 'profiles', 'admin_' . $id . '_' . ticks(), $ext); }
        catch (Throwable $e) { json_response(['success' => false, 'message' => 'ছবি সেভ করা যায়নি।']); }
        $old = (string)($u->ProfileImageLink ?? '');
        $this->db->exec('UPDATE `Users` SET `ProfileImageLink` = :p WHERE `Id` = :i', ['p' => $url, 'i' => $id]);
        if ($old !== '' && str_starts_with($old, '/uploads/profiles/admin_')) delete_upload($old);
        if ($id === (int)current_user_id()) session_set('ProfileImageLink', $url);
        json_response(['success' => true, 'message' => 'প্রোফাইল ছবি আপডেট হয়েছে।', 'url' => $url]);
    }

    /** POST /Admin/RemoveAdminPhoto — id */
    public function RemoveAdminPhoto_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        $u = $this->db->GetUserById($id);
        if (!$u || $u->Role !== 'Admin') json_response(['success' => false, 'message' => 'অ্যাডমিন পাওয়া যায়নি।']);
        $old = (string)($u->ProfileImageLink ?? '');
        $this->db->exec('UPDATE `Users` SET `ProfileImageLink` = NULL WHERE `Id` = :i', ['i' => $id]);
        if ($old !== '' && str_starts_with($old, '/uploads/profiles/admin_')) delete_upload($old);
        if ($id === (int)current_user_id()) session_set('ProfileImageLink', '');
        json_response(['success' => true, 'message' => 'প্রোফাইল ছবি সরানো হয়েছে।']);
    }

    /** GET /Admin/ActivityLog?admin=&date=&page= */
    public function ActivityLog(): void
    {
        $this->requireAdminPage();
        $admin = (int)input_int('admin', 0); $date = (string)input_str('date', ''); $page = max(1, (int)input_int('page', 1)); $per = 50;
        $rows = $this->db->GetAdminActivity($admin, $per + 1, ($page - 1) * $per, $date);
        $this->view(null, array_slice($rows, 0, $per), ['Admins' => $this->db->GetAdminList(), 'Admin' => $admin, 'Date' => $date, 'Page' => $page, 'HasMore' => count($rows) > $per]);
    }

    // ============================================================
    // ===== EDUCATIONAL FEED (পোস্ট রিভিউ — Approve / Reject) =====
    // ============================================================
    /** GET /Admin/EduFeed?status=Pending|Approved|Rejected|all&page= */
    public function EduFeed(): void
    {
        $this->requireAdminPage();
        $status = (string)input_str('status', 'Pending');
        if (!in_array($status, ['Pending', 'Approved', 'Rejected', 'all'], true)) $status = 'Pending';
        $page = max(1, (int)input_int('page', 1));
        $per = 20;
        $counts = $this->db->CountEduPostsByStatus();
        $this->view(null, $this->db->GetEduPostsAdmin($status, $per, ($page - 1) * $per), [
            'Status' => $status, 'Counts' => $counts, 'Page' => $page, 'Pages' => max(1, (int)ceil(($counts[$status] ?? 0) / $per)),
        ]);
    }

    /** POST /Admin/EduFeedStatus — id, status (Approved|Rejected|Pending), reason */
    public function EduFeedStatus_POST(): void
    {
        $this->requireAdminJson();
        $p = $this->db->GetEduPost((int)input_int('id', 0));
        if (!$p) json_response(['success' => false, 'message' => 'পোস্টটি পাওয়া যায়নি (মুছে ফেলা হয়ে থাকতে পারে)।']);
        $status = (string)input_str('status', '');
        if (!in_array($status, ['Approved', 'Rejected', 'Pending'], true)) json_response(['success' => false, 'message' => 'ভুল স্ট্যাটাস।']);
        $reason = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str('reason', '')))), 0, 300);
        $this->db->SetEduPostStatus($p->Id, $status, $reason, (int)current_user_id());
        if ($status !== $p->Status && $p->UserId !== (int)current_user_id()) {
            if ($status === 'Approved') {
                $this->db->CreateNotification($p->UserId, 'পোস্ট Approve হয়েছে 🎉', 'আপনার Educational Feed পোস্টটি Approve হয়েছে — এখন সবাই দেখতে পাচ্ছে।', 'success', $p->Url, 'fa-circle-check');
            } elseif ($status === 'Rejected') {
                $this->db->CreateNotification($p->UserId, 'পোস্ট Approve হয়নি', 'আপনার Educational Feed পোস্টটি Approve করা হয়নি।' . ($reason !== '' ? ' কারণ: ' . $reason : ''), 'warning', '/Feed?tab=mine', 'fa-circle-xmark');
            }
        }
        $msg = ['Approved' => 'পোস্ট Approve হয়েছে — ফিডে দেখাচ্ছে।', 'Rejected' => 'পোস্ট Reject করা হয়েছে।', 'Pending' => 'পোস্ট আবার Pending-এ রাখা হয়েছে।'][$status];
        json_response(['success' => true, 'message' => $msg, 'counts' => $this->db->CountEduPostsByStatus()]);
    }

    // ============================================================
    // ===== CATEGORIES (Categories পেজের ক্যাটাগরি + গ্রুপ) =====
    // ============================================================
    /** GET /Admin/Categories */
    public function Categories(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->db->GetCategoryGroups(false));
    }

    /** GET /Admin/EditCategory/{id} — id না দিলে নতুন (?group=গ্রুপের id) */
    public function EditCategory(): void
    {
        $this->requireAdminPage();
        $groups = $this->db->GetCategoryGroups(false);
        if (!$groups) redirect('/Admin/Categories');
        $id = (int)input_str('id', '0');
        if ($id > 0) {
            $c = $this->db->GetCategory($id);
            if (!$c) not_found();
        } else {
            $gid = (int)input_int('group', 0);
            $g = null;
            foreach ($groups as $x) if ($x->Id === $gid) $g = $x;
            $g = $g ?? $groups[0];
            $c = obj(['Id' => 0, 'GroupId' => $g->Id, 'GroupName' => $g->Name, 'Name' => '', 'Slug' => '', 'Icon' => 'fa-book-open', 'ImageUrl' => '',
                'BannerUrl' => '', 'Highlights' => '', 'About' => '', 'WhyChoose' => '', 'MatchType' => 'subject', 'Keywords' => '', 'IsActive' => true, 'Url' => '']);
        }
        $this->view(null, $c, ['Groups' => $groups]);
    }

    /** POST /Admin/SaveCategory — নতুন বা এডিট */
    public function SaveCategory_POST(): void
    {
        $this->requireAdminJson();
        $line = fn($v, $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)$v))), 0, $max);
        $text = fn($v, $max) => mb_substr(trim(preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", strip_tags((string)$v)))), 0, $max);
        $id = (int)input_int('id', 0);
        if ($id > 0 && !$this->db->GetCategory($id)) json_response(['success' => false, 'message' => 'ক্যাটাগরি পাওয়া যায়নি।']);
        $gid = (int)input_int('groupId', 0);
        $group = null;
        foreach ($this->db->GetCategoryGroups(false) as $g) if ($g->Id === $gid) $group = $g;
        if (!$group) json_response(['success' => false, 'message' => 'গ্রুপ বেছে নিন।', 'field' => 'groupId']);
        $name = $line(input_str('name', ''), 60);
        if ($name === '') json_response(['success' => false, 'message' => 'ক্যাটাগরির নাম লিখুন।', 'field' => 'name']);
        $icon = strtolower(trim((string)input_str('icon', '')));
        if ($icon !== '' && !preg_match('/^fa-[a-z0-9-]{1,40}$/', $icon)) json_response(['success' => false, 'message' => 'আইকন "fa-" দিয়ে শুরু হতে হবে (যেমন fa-book-open)।', 'field' => 'icon']);
        $img = self::safeImageUrl((string)input_str('imageUrl', '')) ?? '';
        $banner = self::safeImageUrl((string)input_str('bannerUrl', '')) ?? '';
        $hl = array_slice(array_values(array_filter(array_map(fn($x) => $line($x, 120), explode("\n", str_replace("\r", '', (string)input_str('highlights', '')))), fn($x) => $x !== '')), 0, 8);
        $kw = array_slice(array_values(array_unique(array_filter(array_map(fn($x) => $line($x, 40), explode(',', (string)input_str('keywords', ''))), fn($x) => $x !== ''))), 0, 20);
        $about = $text(input_str('about', ''), 6000);
        $why = $text(input_str('whyChoose', ''), 4000);
        $match = input_str('matchType', '') === 'class' ? 'class' : 'subject';
        // ডিফল্ট লেখার সাথে হুবহু মিললে খালি রাখি — তাহলে নাম বদলালে ডিফল্ট লেখাও নিজে থেকে বদলাবে
        $def = category_default_texts($name, $group->Name);
        if ($about === trim($def['About'])) $about = '';
        if ($why === trim($def['WhyChoose'])) $why = '';
        if (implode("\n", $hl) === trim($def['Highlights'])) $hl = [];
        try {
            $newId = $this->db->SaveCategory($id, [
                'GroupId' => $gid, 'Name' => $name, 'Slug' => $line(input_str('slug', ''), 120), 'Icon' => $icon, 'ImageUrl' => $img, 'BannerUrl' => $banner,
                'Highlights' => implode("\n", $hl), 'About' => $about, 'WhyChoose' => $why, 'MatchType' => $match, 'Keywords' => implode(', ', $kw),
                'IsActive' => input_bool('isActive'),
            ]);
        } catch (Throwable $e) {
            error_log('[SaveCategory] ' . $e->getMessage());
            json_response(['success' => false, 'message' => 'সেভ করা যায়নি। আবার চেষ্টা করুন।']);
        }
        $c = $this->db->GetCategory($newId);
        json_response(['success' => true, 'message' => $id > 0 ? 'ক্যাটাগরি আপডেট হয়েছে!' : 'নতুন ক্যাটাগরি যোগ হয়েছে!', 'id' => $newId, 'slug' => $c->Slug, 'url' => $c->Url]);
    }

    /** POST /Admin/DeleteCategory — id */
    public function DeleteCategory_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        if (!$this->db->GetCategory($id)) json_response(['success' => false, 'message' => 'ক্যাটাগরি পাওয়া যায়নি।']);
        $this->db->DeleteCategory($id);
        json_response(['success' => true, 'message' => 'ক্যাটাগরি মুছে ফেলা হয়েছে।']);
    }

    /** POST /Admin/ToggleCategory — id, on */
    public function ToggleCategory_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        if (!$this->db->GetCategory($id)) json_response(['success' => false, 'message' => 'ক্যাটাগরি পাওয়া যায়নি।']);
        $this->db->SetCategoryActive($id, input_bool('on'));
        json_response(['success' => true]);
    }

    /** POST /Admin/ReorderCategories — groupId + ids (কমা দিয়ে) */
    public function ReorderCategories_POST(): void
    {
        $this->requireAdminJson();
        $ids = array_filter(array_map('intval', explode(',', (string)input_str('ids', ''))));
        $this->db->ReorderCategories((int)input_int('groupId', 0), $ids);
        json_response(['success' => true]);
    }

    /** POST /Admin/SaveCategoryGroup — id (0 = নতুন), name, isActive */
    public function SaveCategoryGroup_POST(): void
    {
        $this->requireAdminJson();
        $name = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str('name', '')))), 0, 60);
        if ($name === '') json_response(['success' => false, 'message' => 'গ্রুপের নাম লিখুন।']);
        $id = (int)input_int('id', 0);
        $exists = false;
        foreach ($this->db->GetCategoryGroups(false) as $g) {
            if ($g->Id === $id) $exists = true;
            if ($g->Id !== $id && mb_strtolower($g->Name) === mb_strtolower($name)) json_response(['success' => false, 'message' => 'এই নামে একটা গ্রুপ আগেই আছে।']);
        }
        if ($id > 0 && !$exists) json_response(['success' => false, 'message' => 'গ্রুপ পাওয়া যায়নি।']);
        $newId = $this->db->SaveCategoryGroup($id, $name, input_bool('isActive'));
        json_response(['success' => true, 'message' => $id > 0 ? 'গ্রুপ আপডেট হয়েছে।' : 'নতুন গ্রুপ যোগ হয়েছে।', 'id' => $newId]);
    }

    /** POST /Admin/DeleteCategoryGroup — id (ভেতরের সব ক্যাটাগরিও মুছে যায়) */
    public function DeleteCategoryGroup_POST(): void
    {
        $this->requireAdminJson();
        $this->db->DeleteCategoryGroup((int)input_int('id', 0));
        json_response(['success' => true, 'message' => 'গ্রুপ মুছে ফেলা হয়েছে।']);
    }

    /** POST /Admin/ReorderCategoryGroups — ids (কমা দিয়ে) */
    public function ReorderCategoryGroups_POST(): void
    {
        $this->requireAdminJson();
        $this->db->ReorderCategoryGroups(array_filter(array_map('intval', explode(',', (string)input_str('ids', '')))));
        json_response(['success' => true]);
    }

    /** POST /Admin/CategoryDefaultText — name, groupId → ডিফল্ট লেখা (Edit পেজের "ডিফল্ট লেখা বসান") */
    public function CategoryDefaultText_POST(): void
    {
        $this->requireAdminJson();
        $gname = '';
        foreach ($this->db->GetCategoryGroups(false) as $g) if ($g->Id === (int)input_int('groupId', 0)) $gname = $g->Name;
        $name = mb_substr(trim(strip_tags((string)input_str('name', ''))), 0, 60) ?: 'This Category';
        $d = category_default_texts($name, $gname);
        json_response(['success' => true, 'highlights' => $d['Highlights'], 'about' => $d['About'], 'whyChoose' => $d['WhyChoose']]);
    }

    /** POST /Admin/UploadCategoryImage — categoryImage (ফাইল) */
    public function UploadCategoryImage_POST(): void
    {
        $this->uploadImage('categoryImage', 'categories', 'cat_', self::IMAGE_EXTS, 5 * 1024 * 1024,
            'ছবি ৫MB এর কম হতে হবে।', 'শুধু JPG, PNG, GIF বা WEBP ছবি দেওয়া যাবে।', 'ছবি আপলোড হয়েছে!');
    }

    // ============================================================
    // ===== PAYMENTS (ম্যানুয়াল পেমেন্ট যাচাই) =====
    // ============================================================
    /** GET /Admin/Payments?status=Pending|Approved|Rejected|all */
    public function Payments(): void
    {
        $this->requireAdminPage();
        $st = input_str('status', 'Pending');
        if (!in_array($st, Db::PAYMENT_STATUSES, true)) $st = 'all';
        $this->view(null, $this->db->GetPaymentRequests($st === 'all' ? null : $st), ['Status' => $st, 'Stats' => $this->db->PaymentStats()]);
    }

    /** POST /Admin/ApprovePayment (id, note?) */
    public function ApprovePayment_POST(): void
    {
        $this->requireAdminJson();
        $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 300);
        try { $p = $this->db->ApprovePayment((int)input_int('id', 0), (int)current_user_id(), $note); }
        catch (Throwable $e) { error_log('[approve payment] ' . $e->getMessage()); $p = null; }
        if (!$p) json_response(['success' => false, 'message' => 'পেমেন্টটি আগেই যাচাই করা হয়েছে বা পাওয়া যায়নি।']);
        if ($p->Audience === 'Teacher') {
            $sub = $this->db->GetActiveTeacherSubscription($p->UserId);
            $msg = "আপনার “{$p->PackageName}” Subscription (" . taka($p->Amount) . ") চালু হয়েছে" . ($sub ? ' — ' . date('d M Y', strtotime($sub->EndsAt)) . ' পর্যন্ত।' : '।');
        } else {
            $msg = "আপনার “{$p->PackageName}” প্যাকেজ (" . taka($p->Amount) . ") চালু হয়েছে — এখন Tuition Circular-এ পোস্ট করতে পারবেন।";
        }
        $extra = '';
        // মেয়াদ শেষ হয়ে ইউজার হয়ে যাওয়া টিচার রিনিউ করলে — পেমেন্ট Accept করলেই আবার টিচার
        if ($p->Audience === 'Teacher') {
            $u = $this->db->GetUserById((int)$p->UserId);
            $tr = $u && $u->Role === 'User' ? $this->db->GetLatestTeacherRequest((int)$p->UserId) : null;
            if ($tr && $tr->Status === 'Pending' && (int)($tr->PaymentId ?? 0) === (int)$p->Id) {
                $hadBefore = (int)$this->db->scalar("SELECT COUNT(*) FROM `UserSubscriptions` WHERE `UserId` = :u AND `Audience` = 'Teacher' AND (`PaymentId` IS NULL OR `PaymentId` <> :p)", ['u' => (int)$p->UserId, 'p' => (int)$p->Id]) > 0;
                if ($hadBefore) {
                    $this->db->UpdateTeacherRequest((int)$tr->Id, 'Approved', 'রিনিউ — পেমেন্ট Accept');
                    $this->db->MakeUserTeacher((int)$p->UserId);
                    $msg .= ' আপনার টিচার অ্যাকাউন্ট আবার চালু হয়েছে।';
                    $extra = ' — টিচার অ্যাকাউন্টও আবার চালু হয়েছে (রিনিউ)।';
                } else {
                    $extra = ' — এটা নতুন টিচার আবেদন, Teacher Requests থেকে ফর্ম দেখে Approve করুন।';
                }
            }
        }
        $this->db->CreateNotification($p->UserId, 'Payment Accepted', $msg, 'success', '/Payment/My', 'fa-circle-check');
        json_response(['success' => true, 'payment' => $p, 'message' => 'Payment accepted' . $extra]);
    }

    /** POST /Admin/RejectPayment (id, note) */
    public function RejectPayment_POST(): void
    {
        $this->requireAdminJson();
        $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 300);
        if ($note === '') json_response(['success' => false, 'message' => 'Reject করার কারণ লিখুন — ইউজার এটা দেখতে পাবে।']);
        $p = $this->db->RejectPayment((int)input_int('id', 0), (int)current_user_id(), $note);
        if (!$p) json_response(['success' => false, 'message' => 'পেমেন্টটি আগেই যাচাই করা হয়েছে বা পাওয়া যায়নি।']);
        $this->db->CreateNotification($p->UserId, 'Payment Rejected', "“{$p->PackageName}” প্যাকেজের পেমেন্ট গ্রহণ করা হয়নি: $note", 'warning', '/Payment/My', 'fa-circle-xmark');
        json_response(['success' => true, 'payment' => $p]);
    }

    /** POST /Admin/DeletePayment (id) — শুধু Rejected পেমেন্ট মুছে ফেলা যায় */
    public function DeletePayment_POST(): void
    {
        $this->requireAdminJson();
        $p = $this->db->GetPaymentRequest((int)input_int('id', 0));
        if (!$p || $p->Status !== 'Rejected') json_response(['success' => false, 'message' => 'শুধু Rejected পেমেন্ট মুছে ফেলা যায়।']);
        delete_upload($this->db->DeletePaymentRequest($p->Id));
        json_response(['success' => true]);
    }

    /** GET /Admin/PaymentMethods */
    public function PaymentMethods(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->db->GetPaymentMethods(), ['Rules' => $this->db->PaymentRules()]);
    }

    private function paymentImage(string $field, string $prefix): ?string
    {
        $f = upload_file($field);
        if (!$f) return null;
        $ext = strtolower('.' . pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['.jpg', '.jpeg', '.png', '.webp', '.gif'], true)) json_response(['success' => false, 'message' => 'শুধু JPG, PNG, WEBP বা GIF ছবি দেওয়া যাবে।']);
        if ($f['size'] > 3 * 1024 * 1024) json_response(['success' => false, 'message' => 'ছবি ৩ MB এর কম হতে হবে।']);
        if (@getimagesize($f['tmp_name']) === false) json_response(['success' => false, 'message' => 'ছবিটি সঠিক নয়।']);
        return save_upload($f, 'payments/assets', $prefix . '_' . ticks(), $ext === '.jpeg' ? '.jpg' : $ext);
    }

    private static function safeImageUrl(string $u): ?string
    {
        $u = trim($u);
        if ($u === '') return null;
        if (str_starts_with($u, '/uploads/') || preg_match('~^https://[^\s"<>]+$~i', $u)) return mb_substr($u, 0, 500);
        json_response(['success' => false, 'message' => 'ছবির লিংক https:// দিয়ে শুরু হতে হবে।']);
        return null;
    }

    /** POST /Admin/SavePaymentMethod — id, name, kind, color, txnRequired, instructions, isActive, logo(file) / logoUrl / removeLogo */
    public function SavePaymentMethod_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        $old = $id ? $this->db->GetPaymentMethod($id) : null;
        if ($id && !$old) json_response(['success' => false, 'message' => 'মেথড পাওয়া যায়নি।']);
        $name = mb_substr(trim(strip_tags((string)input_str('name', ''))), 0, 40);
        if ($name === '') json_response(['success' => false, 'message' => 'মেথডের নাম দিন (যেমন: bKash)।']);
        $kind = input_str('kind', 'other');
        $color = (string)input_str('color', '#475569');
        $d = [
            'Name' => $name, 'Kind' => in_array($kind, Db::PAYMENT_KINDS, true) ? $kind : 'other',
            'Color' => preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : '#475569',
            'TxnRequired' => input_bool('txnRequired') ? 1 : 0, 'IsActive' => input_bool('isActive', true) ? 1 : 0,
            'Instructions' => mb_substr(trim(strip_tags((string)input_str('instructions', ''))), 0, 1000),
        ];
        $up = $this->paymentImage('logo', 'logo');
        if ($up !== null) $d['LogoUrl'] = $up;
        elseif (input_bool('removeLogo')) $d['LogoUrl'] = null;
        elseif (($lu = (string)input_str('logoUrl', '')) !== '' && $lu !== ($old->LogoUrl ?? '')) $d['LogoUrl'] = self::safeImageUrl($lu);
        elseif (!$id) $d['LogoUrl'] = null;
        if ($old && array_key_exists('LogoUrl', $d) && $old->LogoUrl !== $d['LogoUrl']) delete_upload($old->LogoUrl);
        $id = $this->db->SavePaymentMethod($id, $d);
        json_response(['success' => true, 'message' => 'সেভ হয়েছে।']);
    }

    public function DeletePaymentMethod_POST(): void
    {
        $this->requireAdminJson();
        foreach ($this->db->DeletePaymentMethod((int)input_int('id', 0)) as $f) delete_upload($f);
        json_response(['success' => true]);
    }

    public function MovePaymentMethod_POST(): void
    {
        $this->requireAdminJson();
        $this->db->MovePaymentMethod((int)input_int('id', 0), (int)input_int('dir', 1));
        json_response(['success' => true]);
    }

    /** POST /Admin/SavePaymentAccount — id, methodId, title, accountName, accountNumber, bankName, branch, routingNumber, accountType, isActive, qr(file) / qrUrl / removeQr */
    // ============================================================
    // ===== এক ডিভাইসে লগইন — নতুন ডিভাইসের অনুরোধ, চালু ডিভাইস লগআউট =====
    // ============================================================
    /** GET /Admin/Devices?st=Pending|Active|all&q= */
    public function Devices(): void
    {
        $this->requireAdminPage();
        $st = (string)input_str('st', 'Pending'); if (!in_array($st, ['Pending', 'Active', 'all'], true)) $st = 'Pending';
        $q = mb_substr(trim((string)input_str('q', '')), 0, 60);
        $this->view(null, $this->db->GetDevicesAdmin($st, $q), ['St' => $st, 'Q' => $q, 'On' => $this->db->DeviceLockOn(), 'Pending' => $this->db->CountPendingDevices()]);
    }

    /** POST /Admin/ApproveDevice (id) — নতুন ডিভাইস চালু, আগের ডিভাইস লগআউট */
    public function ApproveDevice_POST(): void
    {
        $this->requireAdminJson();
        $d = $this->db->ApproveDevice(input_int('id', 0), (int)current_user_id());
        if (!$d) json_response(['success' => false, 'message' => 'অনুরোধটি পাওয়া যায়নি বা আগেই সিদ্ধান্ত হয়েছে।']);
        $this->db->CreateNotification((int)$d->UserId, 'নতুন ডিভাইস চালু হয়েছে', "আপনার অনুরোধ Accept হয়েছে — এখন {$d->Label} থেকে লগইন করতে পারবেন। আগের ডিভাইস থেকে লগআউট করা হয়েছে।", 'success', '/Account/Login', 'fa-mobile-screen');
        json_response(['success' => true, 'message' => 'Accept হয়েছে — নতুন ডিভাইস চালু, আগের ডিভাইস লগআউট হয়ে গেছে।']);
    }

    /** POST /Admin/RejectDevice (id) */
    public function RejectDevice_POST(): void
    {
        $this->requireAdminJson();
        $d = $this->db->RejectDevice(input_int('id', 0), (int)current_user_id());
        if (!$d) json_response(['success' => false, 'message' => 'অনুরোধটি পাওয়া যায়নি বা আগেই সিদ্ধান্ত হয়েছে।']);
        json_response(['success' => true, 'message' => 'অনুরোধ বাতিল করা হয়েছে — আগের ডিভাইস চালু থাকবে।']);
    }

    /** POST /Admin/RevokeDevice (id) — চালু ডিভাইস থেকে লগআউট */
    public function RevokeDevice_POST(): void
    {
        $this->requireAdminJson();
        $d = $this->db->RevokeDevice(input_int('id', 0), (int)current_user_id());
        if (!$d) json_response(['success' => false, 'message' => 'ডিভাইসটি পাওয়া যায়নি বা আগেই লগআউট করা হয়েছে।']);
        json_response(['success' => true, 'message' => 'লগআউট করা হয়েছে — এরপর যে ডিভাইসে লগইন করবেন সেটাই চালু হবে।']);
    }

    /** POST /Admin/SaveDeviceLock (on) — এক ডিভাইসের নিয়ম চালু / বন্ধ */
    public function SaveDeviceLock_POST(): void
    {
        $this->requireAdminJson();
        $on = input_bool('on');
        $this->db->SaveSiteContent(['dev_lock' => $on ? '1' : '0']);
        json_response(['success' => true, 'message' => $on ? 'চালু — শিক্ষার্থী আর টিচার একবারে একটা ডিভাইসেই লগইন থাকতে পারবেন।' : 'বন্ধ — যেকোনো ডিভাইসে লগইন করা যাবে।']);
    }

    /** Subscriptions রিপোর্টের ফিল্টার (aud, st, from, to) পড়া */
    private function subFilters(): array
    {
        $aud = (string)input_str('aud', 'all'); if (!in_array($aud, ['all', 'Teacher', 'Student'], true)) $aud = 'all';
        $st = (string)input_str('st', 'all'); if (!in_array($st, ['all', 'Approved', 'Pending', 'Rejected', 'never'], true)) $st = 'all';
        $d = function (string $k): ?string { $v = trim((string)input_str($k, '')); $x = DateTime::createFromFormat('!Y-m-d', $v); return $x && $x->format('Y-m-d') === $v ? $v : null; };
        $from = $d('from'); $to = $d('to');
        if ($from && $to && $from > $to) [$from, $to] = [$to, $from];
        return [$aud, $st, $from, $to];
    }

    /**
     * GET /Admin/Subscriptions?aud=all|Teacher|Student&st=all|Approved|Pending|Rejected|never&from=Y-m-d&to=Y-m-d
     * কে কোন প্যাকেজ কিনেছে / অপেক্ষমাণ / বাতিল / এখনো কেনেনি — তারিখ অনুযায়ী, মেয়াদ সহ
     */
    public function Subscriptions(): void
    {
        $this->requireAdminPage();
        [$aud, $st, $from, $to] = $this->subFilters();
        $all = $this->db->GetSubscriptionOverview();
        $stats = [];
        foreach (['Teacher', 'Student'] as $a) {
            $l = array_filter($all, fn($o) => $o->Audience === $a);
            $stats[$a] = ['users' => count($l), 'active' => count(array_filter($l, fn($o) => in_array($o->State, ['active', 'expiring', 'credit'], true))),
                'expiring' => count(array_filter($l, fn($o) => $o->State === 'expiring')), 'revenue' => array_sum(array_map(fn($o) => $o->Total, $l))];
        }
        // এই তারিখের মধ্যে সব পেমেন্ট — সারাংশ আর দিনভিত্তিক হিসাব (স্ট্যাটাস ফিল্টার ছাড়া)
        $range = $this->db->GetPurchaseReport($aud, 'all', $from, $to);
        $sum = ['Approved' => [0, 0], 'Pending' => [0, 0], 'Rejected' => [0, 0]];
        $daily = [];
        foreach ($range as $r) {
            if (!isset($sum[$r->Status])) continue;
            $sum[$r->Status][0]++; $sum[$r->Status][1] += $r->Amount;
            $day = substr($r->Date, 0, 10);
            $daily[$day] ??= ['Approved' => [0, 0], 'Pending' => [0, 0], 'Rejected' => [0, 0]];
            $daily[$day][$r->Status][0]++; $daily[$day][$r->Status][1] += $r->Amount;
        }
        krsort($daily);
        $list = $st === 'never' ? $this->db->GetNeverBoughtUsers($aud, $from, $to) : ($st === 'all' ? $range : array_values(array_filter($range, fn($r) => $r->Status === $st)));
        $this->view(null, $list, ['Stats' => $stats, 'Aud' => $aud, 'St' => $st, 'From' => $from, 'To' => $to, 'Sum' => $sum, 'Daily' => $daily,
            'NeverCount' => count($this->db->GetNeverBoughtUsers($aud, $from, $to)), 'AlertDays' => $this->db->SubAlertDays()]);
    }

    /** GET /Admin/SubscriptionsCsv?(একই ফিল্টার) — Excel এ খোলা যায় এমন CSV (UTF-8, বাংলা ঠিক থাকে) */
    public function SubscriptionsCsv(): void
    {
        $this->requireAdminPage();
        [$aud, $st, $from, $to] = $this->subFilters();
        $audBn = ['Teacher' => 'Teacher', 'Student' => 'Student'];
        $stBn = ['Approved' => 'Approved (কিনেছে)', 'Pending' => 'Pending (অপেক্ষমাণ)', 'Rejected' => 'Rejected (বাতিল)'];
        $name = 'subscriptions_' . ($st === 'never' ? 'not-bought' : strtolower($st)) . '_' . ($aud === 'all' ? 'all' : strtolower($aud)) . '_' . ($from ?: 'start') . '_to_' . ($to ?: date('Y-m-d')) . '.csv';
        $safe = fn($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;   // Excel formula injection থেকে রক্ষা
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Cache-Control: no-store');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");   // BOM — Excel এ বাংলা ঠিকমতো দেখায়
        if ($st === 'never') {
            fputcsv($out, ['#', 'Name', 'Username', 'Type', 'Phone', 'Email', 'Account Created', 'Last Login', 'Pending Payments', 'Rejected Payments']);
            foreach ($this->db->GetNeverBoughtUsers($aud, $from, $to) as $i => $u) {
                fputcsv($out, array_map($safe, [$i + 1, $u->Name, $u->UserName, $audBn[$u->Audience], $u->Phone, $u->Email, fmt_date($u->Joined, 'Y-m-d H:i'), $u->LastLogin ? fmt_date($u->LastLogin, 'Y-m-d H:i') : 'Never', $u->Pending, $u->Rejected]));
            }
        } else {
            fputcsv($out, ['#', 'Payment Date', 'Name', 'Username', 'Type', 'Phone', 'Package', 'Amount (BDT)', 'Duration (Months)', 'Duration (Days)', 'Status', 'Method', 'Transaction ID', 'Starts', 'Ends', 'Days Left', 'User Total Bought (BDT)']);
            $rows = $this->db->GetPurchaseReport($aud, $st === 'all' ? 'all' : $st, $from, $to);
            $tot = 0;
            foreach ($rows as $i => $r) {
                if ($r->Status === 'Approved') $tot += $r->Amount;
                fputcsv($out, array_map($safe, [$i + 1, fmt_date($r->Date, 'Y-m-d H:i'), $r->Name, $r->UserName, $audBn[$r->Audience] ?? $r->Audience, $r->Phone, $r->Package, $r->Amount, $r->Months, $r->Days,
                    $stBn[$r->Status] ?? $r->Status, $r->Method, $r->Txn, $r->StartsAt ? fmt_date($r->StartsAt, 'Y-m-d') : '', $r->EndsAt ? fmt_date($r->EndsAt, 'Y-m-d') : '',
                    $r->DaysLeft === null ? '' : max(0, $r->DaysLeft), $r->UserTotal]));
            }
            fputcsv($out, []);
            fputcsv($out, ['', 'Total rows', count($rows), '', '', '', 'Approved total (BDT)', $tot]);
        }
        fclose($out);
        exit;
    }

    /** POST /Admin/SaveSubAlertDays (days) — মেয়াদ শেষের কত দিন আগে থেকে সতর্ক করা হবে */
    public function SaveSubAlertDays_POST(): void
    {
        $this->requireAdminJson();
        $d = input_int('days', 0);
        if ($d < 1 || $d > 30) json_response(['success' => false, 'message' => '১ থেকে ৩০ দিনের মধ্যে দিন।']);
        $this->db->SaveSiteContent(['sub_alert_days' => (string)$d]);
        json_response(['success' => true, 'message' => "সেভ হয়েছে — এখন থেকে মেয়াদ শেষের {$d} দিন আগে থেকে টিচার আর শিক্ষার্থীকে সতর্ক করা হবে।"]);
    }

    /** POST /Admin/SaveAgentNumbers (num[{methodId}], on[{methodId}]) — bKash / Nagad / Rocket / Upay এর এজেন্ট নম্বর */
    public function SaveAgentNumbers_POST(): void
    {
        $this->requireAdminJson();
        $nums = (array)($_POST['num'] ?? []); $ons = (array)($_POST['on'] ?? []);
        $bn = ['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9'];
        $save = [];
        foreach ($this->db->GetPaymentMethods() as $m) {
            if ($m->Kind !== 'agent' || !array_key_exists((string)$m->Id, $nums)) continue;
            $n = preg_replace('/\D/', '', strtr((string)$nums[(string)$m->Id], $bn));
            if (str_starts_with($n, '880')) $n = substr($n, 2);
            if (!preg_match('/^01[3-9]\d{8}$/', $n)) json_response(['success' => false, 'message' => "{$m->Name} এর এজেন্ট নম্বর সঠিক নয় — ১১ ডিজিটের নম্বর দিন (যেমন 01791619101)।", 'methodId' => $m->Id]);
            $save[$m->Id] = [$n, !empty($ons[(string)$m->Id])];
        }
        if (!$save) json_response(['success' => false, 'message' => 'কোনো এজেন্ট মেথড পাওয়া যায়নি।']);
        foreach ($save as $mid => [$n, $on]) $this->db->SetAgentNumber((int)$mid, $n, $on);
        json_response(['success' => true, 'message' => 'এজেন্ট নম্বর সেভ হয়েছে — পেমেন্ট পেজে এখনই নতুন নম্বর দেখাবে।']);
    }

    public function SavePaymentAccount_POST(): void
    {
        $this->requireAdminJson();
        $id = (int)input_int('id', 0);
        $old = $id ? $this->db->GetPaymentAccount($id) : null;
        if ($id && !$old) json_response(['success' => false, 'message' => 'অ্যাকাউন্ট পাওয়া যায়নি।']);
        $mid = $old ? $old->MethodId : (int)input_int('methodId', 0);
        if (!$this->db->GetPaymentMethod($mid)) json_response(['success' => false, 'message' => 'মেথড পাওয়া যায়নি।']);
        $c = fn($k, $max) => mb_substr(trim(strip_tags((string)input_str($k, ''))), 0, $max);
        $d = [
            'MethodId' => $mid, 'Title' => $c('title', 80), 'AccountName' => $c('accountName', 100), 'AccountNumber' => $c('accountNumber', 40),
            'BankName' => $c('bankName', 100), 'Branch' => $c('branch', 100), 'RoutingNumber' => $c('routingNumber', 20), 'AccountType' => $c('accountType', 40),
            'IsActive' => input_bool('isActive', true) ? 1 : 0,
        ];
        if ($d['Title'] === '') json_response(['success' => false, 'message' => 'অ্যাকাউন্টের নাম/শিরোনাম দিন (যেমন: Pubali Bank — Mirpur Branch)।']);
        $up = $this->paymentImage('qr', 'qr');
        if ($up !== null) $d['QrImageUrl'] = $up;
        elseif (input_bool('removeQr')) $d['QrImageUrl'] = null;
        elseif (($qu = (string)input_str('qrUrl', '')) !== '' && $qu !== ($old->QrImageUrl ?? '')) $d['QrImageUrl'] = self::safeImageUrl($qu);
        elseif (!$id) $d['QrImageUrl'] = null;
        if ($d['AccountNumber'] === '' && empty($d['QrImageUrl']) && !($old && !array_key_exists('QrImageUrl', $d) && $old->QrImageUrl))
            json_response(['success' => false, 'message' => 'অ্যাকাউন্ট নম্বর অথবা QR ছবি — অন্তত একটা দিন।']);
        if ($old && array_key_exists('QrImageUrl', $d) && $old->QrImageUrl !== $d['QrImageUrl']) delete_upload($old->QrImageUrl);
        $this->db->SavePaymentAccount($id, $d);
        json_response(['success' => true, 'message' => 'সেভ হয়েছে।']);
    }

    public function DeletePaymentAccount_POST(): void
    {
        $this->requireAdminJson();
        delete_upload($this->db->DeletePaymentAccount((int)input_int('id', 0)));
        json_response(['success' => true]);
    }

    /** POST /Admin/SavePaymentRules — teacher, student (পেমেন্ট বাধ্যতামূলক কিনা) */
    public function SavePaymentRules_POST(): void
    {
        $this->requireAdminJson();
        $this->db->SaveSiteContent(['pay_enforce_teacher' => input_bool('teacher') ? '1' : '', 'pay_enforce_student' => input_bool('student') ? '1' : '']);
        json_response(['success' => true, 'rules' => $this->db->PaymentRules()]);
    }

    public function UploadLogo_POST(): void
    {
        $prefix = input_str('target', 'navbar') === 'footer' ? 'footer' : 'navbar';
        $this->uploadImage('logoImage', 'logos', $prefix . '_logo_', array_merge(self::IMAGE_EXTS, ['.svg']), 3 * 1024 * 1024,
            'Logo size must be less than 3MB.', 'Only JPG, PNG, GIF, WEBP, or SVG are allowed.', 'Logo uploaded!');
    }

    // ============================================================
    // ===== TUITION CIRCULAR MANAGEMENT =====
    // ============================================================
    public function TuitionPosts(): void
    {
        $this->requireAdminPage();
        try {
            $posts = $this->db->GetAllTuitionPostsWithApplications();
            $this->view(null, $posts, [
                'TotalPosts' => count($posts),
                'OpenPosts' => count(array_filter($posts, fn($p) => $p->Status === 'Open')),
                'TotalApplications' => $this->db->CountTuitionApplications(),
                'PendingApplications' => $this->db->CountTuitionApplications('Pending'),
            ]);
        } catch (Throwable $e) {
            tempdata_set('Error', 'Unable to load tuition posts. Details: ' . $e->getMessage());
            $this->view(null, [], ['TotalPosts' => 0, 'OpenPosts' => 0, 'TotalApplications' => 0, 'PendingApplications' => 0]);
        }
    }

    private function emptyTuitionPost(): Obj
    {
        return obj(['Id' => 0, 'Title' => '', 'StudentGender' => null, 'StudentClass' => null, 'Subjects' => null,
            'DaysPerWeek' => null, 'PreferredTime' => null, 'Salary' => null, 'Area' => null, 'MediaFee' => null,
            'TuitionCode' => null, 'SpecialNote' => null, 'TutorGenderPreference' => null, 'Status' => 'Open',
            'ShowOnHome' => false, 'PostedDate' => now(), 'UpdatedAt' => null, 'Applications' => []]);
    }

    private function tuitionPostFromForm(): Obj
    {
        $m = $this->emptyTuitionPost();
        $m->Id = input_int('Id', 0);
        foreach (['Title', 'StudentGender', 'StudentClass', 'Subjects', 'DaysPerWeek', 'PreferredTime', 'Salary', 'Area',
                     'MediaFee', 'TuitionCode', 'SpecialNote', 'TutorGenderPreference', 'Status'] as $f) {
            $v = input_str($f);
            $m->$f = ($v === null || trim($v) === '') ? null : trim($v);
        }
        $m->Title = $m->Title ?? '';
        if (has_input('ShowOnHome')) $m->ShowOnHome = input_bool('ShowOnHome');
        return $m;
    }

    /** ফর্ম ভ্যালিডেশন (TuitionPost এর DataAnnotations) */
    private function validateTuitionPost(Obj $m): array
    {
        $errors = [];
        if (trim($m->Title) === '') $errors['Title'] = 'Title is required';
        $limits = ['Title' => 300, 'StudentGender' => 100, 'StudentClass' => 200, 'Subjects' => 300, 'DaysPerWeek' => 50,
            'PreferredTime' => 200, 'Salary' => 100, 'Area' => 300, 'MediaFee' => 200, 'TuitionCode' => 50,
            'TutorGenderPreference' => 50, 'Status' => 20];
        foreach ($limits as $f => $max) {
            if ($m->$f !== null && mb_strlen($m->$f) > $max) $errors[$f] = "The field $f must be a string with a maximum length of $max.";
        }
        return $errors;
    }

    public function AddTuitionPost(): void
    {
        $this->requireAdminPage();
        $this->view(null, $this->emptyTuitionPost());
    }

    public function AddTuitionPost_POST(): void
    {
        $this->requireAdminPage();
        $m = $this->tuitionPostFromForm();
        if ($errors = $this->validateTuitionPost($m)) {
            $GLOBALS['__ModelErrors'] = $errors;
            $this->view('AddTuitionPost', $m);
        }
        try {
            $m->PostedDate = now();
            $m->Status = is_blank($m->Status) ? 'Open' : $m->Status;
            if (is_blank($m->TuitionCode)) {
                $areaPart = is_blank($m->Area) ? 'TUITION' : strtoupper(substr(preg_replace('/[^\p{L}]/u', '', $m->Area), 0, 8));
                if (is_blank($areaPart)) $areaPart = 'TUITION';
                $m->TuitionCode = $areaPart . random_int(1000, 9998);
            }
            $this->db->CreateTuitionPost($m);
            tempdata_set('Success', 'Tuition post added successfully!');
            $this->redirectToAction('TuitionPosts');
        } catch (Throwable $e) {
            $this->view('AddTuitionPost', $m, ['Error' => 'Failed to add tuition post: ' . $e->getMessage()]);
        }
    }

    public function EditTuitionPost(): void
    {
        $this->requireAdminPage();
        $p = $this->db->GetTuitionPostById(input_int('id', 0), true);
        if (!$p) {
            tempdata_set('Error', 'Tuition post not found.');
            $this->redirectToAction('TuitionPosts');
        }
        $this->view(null, $p);
    }

    public function EditTuitionPost_POST(): void
    {
        $this->requireAdminPage();
        $m = $this->tuitionPostFromForm();
        if ($errors = $this->validateTuitionPost($m)) {
            $GLOBALS['__ModelErrors'] = $errors;
            $this->view('EditTuitionPost', $m);
        }
        try {
            $existing = $this->db->GetTuitionPostById($m->Id);
            if (!$existing) {
                tempdata_set('Error', 'Tuition post not found.');
                $this->redirectToAction('TuitionPosts');
            }
            if (is_blank($m->Status)) $m->Status = $existing->Status;
            $this->db->UpdateTuitionPost($m);
            tempdata_set('Success', 'Tuition post updated successfully!');
            $this->redirectToAction('TuitionPosts');
        } catch (Throwable $e) {
            $this->view('EditTuitionPost', $m, ['Error' => 'Failed to update tuition post: ' . $e->getMessage()]);
        }
    }

    public function ToggleTuitionStatus_POST(): void
    {
        $this->requireAdminJson();
        try {
            $p = $this->db->GetTuitionPostById(input_int('id', 0));
            if (!$p) json_response(['success' => false, 'message' => 'Post not found']);
            $new = $p->Status === 'Open' ? 'Closed' : 'Open';
            $this->db->SetTuitionPostStatus($p->Id, $new);
            json_response(['success' => true, 'message' => "Tuition marked as $new", 'newStatus' => $new]);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function ToggleShowOnHome_POST(): void
    {
        $this->requireAdminJson();
        try {
            $p = $this->db->GetTuitionPostById(input_int('id', 0));
            if (!$p) json_response(['success' => false, 'message' => 'Post not found']);
            $show = !$p->ShowOnHome;
            $this->db->ToggleShowOnHome($p->Id, $show);
            json_response(['success' => true, 'message' => $show ? 'Added to Home Page' : 'Removed from Home Page', 'isShowing' => $show]);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function DeleteTuitionPost_POST(): void
    {
        $this->requireAdminJson();
        try {
            if (!$this->db->GetTuitionPostById(input_int('id', 0))) json_response(['success' => false, 'message' => 'Post not found']);
            $this->db->DeleteTuitionPost(input_int('id', 0));
            json_response(['success' => true, 'message' => 'Tuition post deleted successfully.']);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // ============================================================
    // ===== TUITION APPLICATIONS MANAGEMENT =====
    // ============================================================
    public function TuitionApplications(): void
    {
        $this->requireAdminPage();
        $status = input_str('status', 'all') ?: 'all';
        try {
            $apps = $this->db->GetTuitionApplications($status);
            $postId = input_int('postId', null);
            if ($postId !== null) $apps = array_values(array_filter($apps, fn($a) => $a->TuitionPostId === $postId));
            $this->view(null, $apps, [
                'CurrentStatus' => $status,
                'TotalCount' => $this->db->CountTuitionApplications(),
                'PendingCount' => $this->db->CountTuitionApplications('Pending'),
                'ShortlistedCount' => $this->db->CountTuitionApplications('Shortlisted'),
                'SelectedCount' => $this->db->CountTuitionApplications('Selected'),
            ]);
        } catch (Throwable $e) {
            tempdata_set('Error', 'Unable to load applications. Details: ' . $e->getMessage());
            $this->view(null, [], ['CurrentStatus' => $status, 'TotalCount' => 0, 'PendingCount' => 0, 'ShortlistedCount' => 0, 'SelectedCount' => 0]);
        }
    }

    public function UpdateApplicationStatus_POST(): void
    {
        $this->requireAdminJson();
        try {
            $status = input_str('status');
            if (!in_array($status, ['Pending', 'Shortlisted', 'Selected', 'Rejected'], true)) json_response(['success' => false, 'message' => 'Invalid status.']);
            $id = input_int('id', 0);
            if (!$this->db->GetTuitionApplicationById($id)) json_response(['success' => false, 'message' => 'Application not found.']);
            $this->db->UpdateApplicationStatus($id, $status, input_str('adminNote'));
            json_response(['success' => true, 'message' => 'Application status updated successfully.']);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function DeleteTuitionApplication_POST(): void
    {
        $this->requireAdminJson();
        try {
            if (!$this->db->DeleteTuitionApplication(input_int('id', 0))) json_response(['success' => false, 'message' => 'Application not found.']);
            json_response(['success' => true, 'message' => 'Application deleted.']);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
