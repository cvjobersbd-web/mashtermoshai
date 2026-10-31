<?php
class HomeController extends Controller
{
    public function Index(): void
    {
        try {
            $settings = $this->db->GetSiteSettings();
            $homeTuitions = $this->db->GetHomePageTuitionPosts(3);
        } catch (Throwable $e) {
            $settings = new SiteSettings();
            $homeTuitions = [];
        }
        $this->view(null, null, ['SiteSettings' => $settings, 'HomeTuitions' => $homeTuitions]);
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
