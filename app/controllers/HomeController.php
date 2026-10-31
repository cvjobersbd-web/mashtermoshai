<?php
class HomeController extends Controller
{
    public function Index(): void
    {
        try {
            $settings = $this->db->GetSiteSettings();
        } catch (Throwable $e) {
            $settings = new SiteSettings();
        }
        // সাবজেক্ট / ক্লাস / লোকেশন অনুযায়ী কতজন টিউটর আছে (হোম পেজের কার্ডে দেখায়)
        $counts = ['subject' => [], 'class' => [], 'location' => []];
        try {
            $tutors = $this->db->GetTutorDirectory();
            $groups = ['subject' => tutor_subject_groups(), 'class' => tutor_class_groups(), 'location' => tutor_location_groups()];
            foreach ($groups as $field => $list) {
                foreach ($list as $slug => $g) {
                    $vals = $field === 'location' ? $g[1] : $g[2];
                    $counts[$field][$slug] = count(array_filter($tutors, fn($t) => tutor_matches_group($t, $field, $vals)));
                }
            }
            $counts['total'] = count($tutors);
        } catch (Throwable $e) { error_log('[Home counts] ' . $e->getMessage()); }
        $this->view(null, null, ['SiteSettings' => $settings, 'TutorCounts' => $counts]);
    }

    public function Privacy(): void { $this->view(); }

    public function About(): void { $this->view('Privacy'); }

    public function Contact(): void { $this->view('Privacy'); }

    public function Error(): void
    {
        header('Cache-Control: no-store, no-cache');
        $this->view('Shared/Error', obj(['RequestId' => bin2hex(random_bytes(4))]));
    }
}
