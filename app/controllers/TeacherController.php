<?php
class TeacherController extends Controller
{
    private const REQUEST_FIELDS = ['FullName', 'Email', 'MobileNumber', 'Education', 'Institution', 'SubjectExpertise',
        'Experience', 'TeachingStyle', 'AvailableDays', 'PreferredTime', 'HourlyRate', 'CvLink', 'WhyTeach'];

    /** GET /Teacher/RequestForm */
    public function RequestForm(): void
    {
        if (session_get('UserName') === null || (current_user_id() ?? 0) === 0) {
            redirect('/Account/Login?returnUrl=' . rawurlencode('/Teacher/RequestForm'));
        }
        $userId = current_user_id() ?? 0;

        $status = $this->db->GetUserTeacherRequestStatus($userId);
        if ($status === 'Pending') {
            tempdata_set('Info', 'You have already submitted a teacher request. Please wait for admin approval.');
            $this->redirectToAction('Index', 'Profile');
        } elseif ($status === 'Approved') {
            tempdata_set('Info', 'You are already a teacher!');
            $this->redirectToAction('TeacherDashboard');
        }

        $user = $this->db->GetUserById($userId);
        $model = obj(array_fill_keys(self::REQUEST_FIELDS, ''));
        $model->Id = 0;
        $model->UserId = $userId;
        $model->FullName = $user->FullName ?? '';
        $model->Email = $user->Email ?? '';
        $model->MobileNumber = $user->MobileNumber ?? '';
        $model->Status = 'Pending';
        $this->view(null, $model);
    }

    /** POST /Teacher/RequestForm */
    public function RequestForm_POST(): void
    {
        if (session_get('UserName') === null) $this->redirectToAction('Login', 'Account');

        $model = obj();
        foreach (self::REQUEST_FIELDS as $f) $model->$f = trim((string)input_str($f, ''));
        $model->UserId = current_user_id() ?? 0;   // নিরাপত্তার জন্য সেশন থেকে নেওয়া
        $model->Id = 0;
        $model->Status = 'Pending';

        $errors = [];
        foreach (self::REQUEST_FIELDS as $f) {
            if (in_array($f, ['TeachingStyle', 'PreferredTime', 'CvLink'], true)) continue; // ফর্মে ঐচ্ছিক
            if ($model->$f === '') $errors[] = "The $f field is required.";
        }

        $viewBag = [];
        if (!$errors) {
            $model->RequestDate = now();
            $err = null;
            if ($this->db->CreateTeacherRequest($model, $err)) {
                tempdata_set('Success', 'Your teacher request has been submitted successfully! Please wait for admin approval.');
                $this->view(null, $model);
            }
            $viewBag['Error'] = is_blank($err) ? 'Failed to submit request. Please try again.' : "Failed to submit request. Database error: $err";
        } else {
            $viewBag['Error'] = 'Please fix the following: ' . implode(' ', $errors);
        }
        $this->view(null, $model, $viewBag);
    }

    /** GET /Teacher/TeacherDashboard */
    public function TeacherDashboard(): void
    {
        if (session_get('UserName') === null) $this->redirectToAction('Login', 'Account');
        $userId = current_user_id() ?? 0;
        if ($userId === 0) $this->redirectToAction('Login', 'Account');

        $user = $this->db->GetUserById($userId);
        if (!$user || $user->Role !== 'Teacher') {
            tempdata_set('Error', 'You are not authorized to view this page.');
            $this->redirectToAction('Index', 'Profile');
        }

        $apps = $this->db->GetUserTuitionApplications($userId);
        $count = fn($s) => count(array_filter($apps, fn($a) => $a->Status === $s));

        $model = obj([
            'Id' => $user->Id,
            'FullName' => $user->FullName,
            'Email' => $user->Email,
            'MobileNumber' => $user->MobileNumber ?? '',
            'SubjectExpertise' => 'Not specified',
            'Experience' => 'Not specified',
            'ProfileImageLink' => $user->ProfileImageLink ?? '',
            'Rating' => 0,
            'TuitionApplications' => $apps,
            'TotalApplications' => count($apps),
            'PendingApplications' => $count('Pending'),
            'ShortlistedApplications' => $count('Shortlisted'),
            'SelectedApplications' => $count('Selected'),
            'RejectedApplications' => $count('Rejected'),
            'Stats' => $this->db->GetTeacherDashboardStats($userId),
            'PostStats' => $this->db->GetTeacherPostStats($userId),
            'TutorRequests' => $this->db->GetTeacherRequestsFor($userId, (string)$user->FullName),
            'CircularApplications' => $this->db->GetCircularApplicationsByTeacher($userId),
            'MyReview' => $this->db->GetReviewByUser($userId),
        ]);
        $this->view(null, $model);
    }

    /** POST /Teacher/UpdateDirectRequest — টিচার নিজের কাছে আসা রিকোয়েস্ট Accept / Reject করে (Accept = স্টুডেন্টের সাথে চ্যাট খোলে) */
    public function UpdateDirectRequest_POST(): void
    {
        $me = current_user_id();
        if ($me === null || current_role() !== 'Teacher') json_response(['success' => false, 'message' => 'শুধু টিচার এটা করতে পারবেন।']);
        $status = (string)input_str('status', '');
        if (!in_array($status, ['Accepted', 'Rejected', 'Pending'], true)) json_response(['success' => false, 'message' => 'Invalid status.']);
        $user = $this->db->GetUserById((int)$me);
        $r = $this->db->TeacherDecideRequest(input_int('id', 0), (int)$me, (string)($user->FullName ?? ''), $status);
        if (!$r) json_response(['success' => false, 'message' => 'রিকোয়েস্টটি পাওয়া যায়নি।']);
        $sid = (int)($r->StudentUserId ?? 0);
        $tname = (string)($user->FullName ?? 'Teacher');
        if ($sid > 0 && $status === 'Accepted') {
            $this->db->CreateNotification($sid, 'Request Accepted 🎉', "$tname আপনার রিকোয়েস্ট গ্রহণ করেছেন। এখন Messages থেকে সরাসরি চ্যাট করতে পারবেন।", 'success', '/Chat?c=r' . (int)$r->Id, 'fa-comments');
        } elseif ($sid > 0 && $status === 'Rejected') {
            $this->db->CreateNotification($sid, 'Request Not Accepted', "$tname এবার আপনার রিকোয়েস্টটি গ্রহণ করতে পারেননি। Find Tutor থেকে অন্য টিচার খুঁজে দেখুন।", 'warning', '/FindTutor', 'fa-times-circle');
        }
        json_response(['success' => true, 'status' => $status, 'chat' => $status === 'Accepted' && $sid > 0 ? '/Chat?c=r' . (int)$r->Id : null]);
    }
}
