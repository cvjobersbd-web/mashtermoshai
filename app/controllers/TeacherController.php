<?php
require_once __DIR__ . '/KycController.php';
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
        if ($status === 'Expired') {
            tempdata_set('Info', 'আপনার টিচার প্যাকেজের মেয়াদ শেষ হয়েছে — প্যাকেজ রিনিউ করলেই আবার টিচার হয়ে যাবেন (আগের সব তথ্য রাখা আছে)।');
            redirect('/Package?for=teacher&apply=1');
        }
        if ($status === 'AwaitingPayment') {
            tempdata_set('Info', 'আপনার ফর্ম জমা আছে — এখন একটা টিচার প্যাকেজ কিনে পেমেন্ট করুন, তারপর আবেদন অ্যাডমিনের কাছে যাবে।');
            redirect('/Package?for=teacher&apply=1');
        }
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

    /** ডিটেইলস ফর্ম (td[...]) পরিষ্কার করে + বাধ্যতামূলক ঘরগুলো যাচাই — ভুল থাকলে বার্তার তালিকা */
    private function readDetails(array &$errors): array
    {
        $raw = $_POST['td'] ?? [];
        $d = Db::cleanTeacherDetails(is_array($raw) ? $raw : []);
        $need = ['whyHire' => 'Reasons to be Hired', 'university' => 'University / College', 'livingCity' => 'Living City', 'subjects' => 'Subjects',
            'classes' => 'Classes', 'methods' => 'Tutoring Method', 'days' => 'Availability Days', 'experienceYears' => 'Tutoring Experience',
            'expectedSalary' => 'Expected Salary', 'gender' => 'Gender'];
        foreach ($need as $k => $label) if ($d[$k] === '' || $d[$k] === []) $errors[] = "$label দিন।";
        if ($d['expectedSalary'] !== '' && !preg_match('/^\d[\d,]*$/', $d['expectedSalary'])) $errors[] = 'Expected Salary শুধু সংখ্যায় দিন (যেমন 5000)।';
        return $d;
    }

    /** POST /Teacher/RequestForm */
    public function RequestForm_POST(): void
    {
        if (session_get('UserName') === null) $this->redirectToAction('Login', 'Account');
        $userId = current_user_id() ?? 0;
        $user = $this->db->GetUserById($userId);

        $model = obj();
        foreach (self::REQUEST_FIELDS as $f) $model->$f = trim((string)input_str($f, ''));
        $model->UserId = $userId;   // নিরাপত্তার জন্য সেশন থেকে নেওয়া
        $model->FullName = (string)($user->FullName ?? $model->FullName);
        $model->Email = (string)($user->Email ?? $model->Email);
        $model->MobileNumber = (string)($user->MobileNumber ?? $model->MobileNumber);
        $model->Id = 0;
        $model->Status = 'Pending';

        $errors = [];
        $d = $this->readDetails($errors);
        // পুরোনো ঘরগুলো ডিটেইলস থেকে (অ্যাডমিনের Teacher Requests পেজ আর Find Tutor এ আগের মতোই দেখায়)
        $model->Institution = $d['university'];
        $model->SubjectExpertise = implode(', ', $d['subjects']);
        $model->Experience = $d['experienceYears'];
        $model->HourlyRate = $d['expectedSalary'];
        $model->AvailableDays = implode(', ', $d['days']);
        $model->PreferredTime = $d['timeFrom'] !== '' && $d['timeTo'] !== '' ? date('h:i A', strtotime($d['timeFrom'])) . ' - ' . date('h:i A', strtotime($d['timeTo'])) : '';
        $model->WhyTeach = $d['whyHire'];
        if ($model->Education === '') $errors[] = 'Highest Education দিন।';
        if ($model->CvLink !== '' && !preg_match('#^https?://#i', $model->CvLink)) $errors[] = 'CV Link https:// দিয়ে শুরু হতে হবে।';
        if (in_array($this->db->GetUserTeacherRequestStatus($userId), ['Pending', 'AwaitingPayment', 'Approved'], true)) $errors[] = 'আপনার আগের আবেদন এখনো চালু আছে।';

        $viewBag = ['Details' => $d];
        // Teacher Verification — মোবাইল, WhatsApp, NID এর ছবি (সামনে + পেছনে)
        $kyc = null;
        if (!$errors) {
            try {
                $oldKyc = $this->db->GetKyc($userId, 'teacher');
                $kyc = KycController::readTeacherKyc($this->db, $userId, $oldKyc && $oldKyc->Status !== 'Approved' ? $oldKyc : null, function ($m) { throw new InvalidArgumentException($m); });
                $model->MobileNumber = $kyc[0]['mobile'];
            } catch (InvalidArgumentException $e) { $errors[] = $e->getMessage(); }
        }
        if (!$errors) {
            $model->RequestDate = now();
            $err = null;
            if ($this->db->CreateTeacherRequest($model, $err)) {
                $this->db->SaveTeacherDetails($userId, $d);
                if ($kyc) {
                    try {
                        $this->db->SaveKyc($userId, 'teacher', $kyc[0], $kyc[1], $kyc[2], (int)$model->Id ?: null);
                        if (is_blank($user->MobileNumber ?? '')) $this->db->exec('UPDATE `Users` SET `MobileNumber` = :m WHERE `Id` = :u', ['m' => $kyc[0]['mobile'], 'u' => $userId]);
                    } catch (Throwable $e) { error_log('[teacher kyc] ' . $e->getMessage()); }
                }
                // টিচার প্যাকেজ থাকলে: আগে প্যাকেজ কিনে পেমেন্ট, তারপর আবেদন অ্যাডমিনের কাছে যায়
                if ($this->db->TeacherApplyNeedsPayment()) {
                    $this->db->SetTeacherRequestState((int)$model->Id, 'AwaitingPayment');
                    tempdata_set('Success', 'ফর্ম জমা হয়েছে! এখন পছন্দের টিচার প্যাকেজ বেছে পেমেন্ট করুন — তারপর আবেদন অ্যাডমিনের কাছে যাবে।');
                    redirect('/Package?for=teacher&apply=1');
                }
                tempdata_set('Success', 'Your teacher request has been submitted successfully! Please wait for admin approval.');
                $this->view(null, $model, $viewBag);
            }
            $viewBag['Error'] = is_blank($err) ? 'Failed to submit request. Please try again.' : "Failed to submit request. Database error: $err";
        } else {
            $viewBag['Error'] = 'অনুগ্রহ করে ঠিক করুন: ' . implode(' ', $errors);
        }
        $this->view(null, $model, $viewBag);
    }

    /** GET /Teacher/EditDetails — অনুমোদিত টিচার নিজের Tutor Details (ডিটেইলস পেজের তথ্য) বদলায় */
    public function EditDetails(): void
    {
        $me = current_user_id();
        if ($me === null) redirect('/Account/Login?returnUrl=' . rawurlencode('/Teacher/EditDetails'));
        if (current_role() !== 'Teacher') redirect('/Teacher/RequestForm');
        $this->view(null, null, ['Details' => $this->db->GetTeacherDetails((int)$me)]);
    }

    /** POST /Teacher/EditDetails */
    public function EditDetails_POST(): void
    {
        $me = current_user_id();
        if ($me === null || current_role() !== 'Teacher') redirect('/Account/Login');
        $errors = [];
        $d = $this->readDetails($errors);
        if ($errors) { $this->view('EditDetails', null, ['Details' => $d, 'Error' => 'অনুগ্রহ করে ঠিক করুন: ' . implode(' ', $errors)]); return; }
        $this->db->SaveTeacherDetails((int)$me, $d);
        tempdata_set('Success', 'Tutor Details সেভ হয়েছে।');
        redirect('/Tutors/Profile/' . (int)$me);
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
