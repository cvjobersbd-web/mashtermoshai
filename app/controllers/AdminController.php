<?php
class AdminController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

    /** পেজ অ্যাকশনের জন্য: Admin না হলে Login এ পাঠায় */
    private function requireAdminPage(): void
    {
        if (!is_admin()) $this->redirectToAction('Login', 'Account');
    }

    /** JSON অ্যাকশনের জন্য */
    private function requireAdminJson(): void
    {
        if (!is_admin()) json_response(['success' => false, 'message' => 'Unauthorized']);
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
        $id = input_int('id', 0) ?? 0;
        if (!$this->db->LiftSuspension($id, current_user_id())) json_response(['success' => false, 'message' => 'এই ইউজার এখন Suspend অবস্থায় নেই।']);
        $this->db->CreateNotification($id, 'Suspension Lifted', 'আপনার অ্যাকাউন্টের Suspend তুলে নেওয়া হয়েছে। আবার স্বাগতম!', 'success', null, 'fa-user-check');
        json_response(['success' => true, 'message' => 'Suspend তুলে নেওয়া হয়েছে।']);
    }

    public function DeleteUser_POST(): void
    {
        $this->requireAdminJson();
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
        $id = input_int('id', 0);
        $pw = (string)input_str('newPassword', '');
        if (trim($pw) === '' || strlen($pw) < 6) json_response(['success' => false, 'message' => 'Password must be at least 6 characters']);
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
        $requests = $this->db->GetAllTeacherRequests();
        $this->view(null, $requests, [
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
        $this->requireAdminJson();
        $id = input_int('id', 0);
        $requestId = input_int('requestId', 0);
        if ($id > 0 && current_user_id() === $id) json_response(['success' => false, 'message' => 'Cannot delete your own account']);
        $userDeleted = $requestDeleted = false;
        try { if ($id > 0) $userDeleted = $this->db->DeleteUser($id); } catch (Throwable $e) {}
        try { $requestDeleted = $this->db->DeleteTeacherRequestById($requestId); } catch (Throwable $e) {}
        if ($userDeleted || $requestDeleted) json_response(['success' => true, 'message' => 'Teacher deleted successfully']);
        json_response(['success' => false, 'message' => 'Failed to delete: no matching user or request was found']);
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
        if (!in_array($status, ['Pending', 'Contacted', 'Completed', 'Cancelled'], true)) json_response(['success' => false, 'message' => 'Invalid status value.']);
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
        $this->view(null, $this->db->GetSiteSettings());
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
        if (input_bool('reset')) {
            $this->db->SaveSiteContent(profile_promo_defaults());
            json_response(['success' => true, 'message' => 'আগের (ডিফল্ট) ছবিতে ফেরত নেওয়া হয়েছে।'] + profile_promo_defaults());
        }
        $img = trim((string)input_str('pp_image', ''));
        $link = trim((string)input_str('pp_link', ''));
        // শুধু http(s) লিংক বা এই সাইটের /uploads/ ফাইল
        if ($img === '' || !(preg_match('#^https?://[^\s"\'<>]+$#i', $img) || preg_match('#^/uploads/[A-Za-z0-9/_.-]+$#', $img))) {
            json_response(['success' => false, 'message' => 'ছবির লিংক ঠিক নেই — https:// দিয়ে শুরু হওয়া লিংক দিন অথবা ছবি আপলোড করুন।']);
        }
        if (mb_strlen($img) > 500) json_response(['success' => false, 'message' => 'লিংক খুব বড়।']);
        if ($link !== '' && !(preg_match('#^https?://[^\s"\'<>]+$#i', $link) || is_local_url($link))) {
            json_response(['success' => false, 'message' => 'ক্লিক লিংক ঠিক নেই — /FindTutor এর মতো সাইটের পাথ বা https:// লিংক দিন।']);
        }
        $vals = ['pp_image' => $img, 'pp_link' => mb_substr($link, 0, 300), 'pp_enabled' => input_bool('pp_enabled') ? '1' : '0'];
        $err = null;
        if (!$this->db->SaveSiteContent($vals, $err)) json_response(['success' => false, 'message' => 'সেভ করা যায়নি: ' . $err]);
        json_response(['success' => true, 'message' => 'প্রোফাইল প্রোমো ছবি সেভ হয়েছে।'] + $vals);
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
