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
        $user = $this->db->GetUserById($userId);
        if (!$user) $this->redirectToAction('Login', 'Account');

        $this->view(null, null, [
            'User' => $user,
            'Stats' => $this->db->GetUserDashboardStats($userId),
            'RecentPosts' => $this->db->GetUserCircularPosts($userId, 10),
            'Applications' => $this->db->GetCircularApplicationsForOwner($userId),
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
                "$student আপনার অ্যাপ্লিকেশন গ্রহণ করেছেন। ড্যাশবোর্ড থেকে তার সাথে যোগাযোগ করুন।", 'success',
                '/Teacher/TeacherDashboard?section=applications', 'fa-check-circle');
        } elseif ($status === 'Rejected') {
            $this->db->CreateNotification($app->TeacherId, 'Application Not Selected',
                "$student আপনার অ্যাপ্লিকেশনটি এবার গ্রহণ করেননি।", 'warning',
                '/Teacher/TeacherDashboard?section=applications', 'fa-times-circle');
        }
        json_response(['success' => true, 'status' => $status]);
    }
}
