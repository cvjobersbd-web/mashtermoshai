<?php
/** মাষ্টার মশাই — শিক্ষক Subscription আর শিক্ষার্থীর Tuition Post প্যাকেজ (পেমেন্ট পরে যুক্ত হবে) */
class PackageController extends Controller
{
    /** GET /Package?for=teacher|student */
    public function Index(): void
    {
        $for = strtolower((string)input_str('for', ''));
        if (!in_array($for, ['teacher', 'student'], true)) {
            $role = (string)session_get('UserRole');
            $for = $role === 'User' ? 'student' : 'teacher';
        }
        $this->view(null, null, [
            'TeacherPackages' => $this->db->GetPackages('Teacher', true),
            'StudentPackages' => $this->db->GetPackages('Student', true),
            'Tab' => $for,
        ]);
    }
}
