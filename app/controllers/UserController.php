<?php
class UserController extends Controller
{
    /** GET /User/Dashboard (Student Dashboard) */
    public function Dashboard(): void
    {
        $userId = current_user_id();
        $role = current_role();
        if ($userId === null || ($role !== 'User' && $role !== 'Admin')) {
            tempdata_set('Error', 'You must be logged in as a Student to access this page.');
            $this->redirectToAction('Login', 'Account');
        }
        // নতুন অ্যাকাউন্ট (User) এর কোনো ড্যাশবোর্ড নেই — Student Verification Approve হলে Student Dashboard
        if ($role === 'User' && role_label('User', $userId) !== 'Student') {
            $tr = account_track($userId) === 'teacher';
            tempdata_set('Info', $tr ? 'আপনার টিচার আবেদন Approve হলে Teacher Dashboard পাবেন।' : 'Student Dashboard পেতে আগে Student Verification ফর্ম পূরণ করুন — অ্যাডমিন Approve করলে ড্যাশবোর্ড খুলে যাবে।');
            redirect($tr ? '/Profile/Show?tab=profile#ab-tutor' : '/Kyc/Student');
        }
        $user = $this->db->GetUserById($userId);
        if (!$user) $this->redirectToAction('Login', 'Account');

        $this->view(null, null, [
            'User' => $user,
            'Stats' => $this->db->GetUserDashboardStats($userId),
            'RecentPosts' => $this->db->GetUserCircularPosts($userId, 10),
            'Applications' => $this->db->GetCircularApplicationsForOwner($userId),
            'MyReview' => $this->db->GetReviewByUser($userId),
        ]);
    }

    /** POST /User/UpdateCircularApplication — স্টুডেন্ট টিচারের অ্যাপ্লিকেশন Accept/Reject করে */
    public function UpdateCircularApplication_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login.']);
        $status = (string)input_str('status', '');
        if (!in_array($status, ['Accepted', 'Rejected', 'Pending'], true)) json_response(['success' => false, 'message' => 'Invalid status.']);

        $app = $this->db->UpdateCircularApplicationStatus(input_int('id', 0), $userId, $status);
        if (!$app) json_response(['success' => false, 'message' => 'অ্যাপ্লিকেশনটি পাওয়া যায়নি।']);

        $student = session_get('UserFullName') ?? session_get('UserName') ?? 'Student';
        if ($status === 'Accepted') {
            $this->db->CreateNotification($app->TeacherId, 'Application Accepted 🎉',
                "$student আপনার অ্যাপ্লিকেশন গ্রহণ করেছেন। এখন Messages থেকে সরাসরি চ্যাট করতে পারবেন।", 'success',
                '/Chat?c=' . (int)$app->Id, 'fa-comments');
        } elseif ($status === 'Rejected') {
            $this->db->CreateNotification($app->TeacherId, 'Application Not Selected',
                "$student আপনার অ্যাপ্লিকেশনটি এবার গ্রহণ করেননি।", 'warning',
                '/Teacher/TeacherDashboard?section=applications', 'fa-times-circle');
        }
        // Accepted হলে দুজনের চ্যাট খোলে; Reject / Undo করলে চ্যাট বন্ধ হয়ে যায়
        json_response(['success' => true, 'status' => $status, 'chat' => $status === 'Accepted' ? '/Chat?c=' . (int)$app->Id : null]);
    }
}
