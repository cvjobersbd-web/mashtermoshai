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
        // হোম পেজের নতুন সেকশন — প্রতিটা আলাদা try, যাতে একটায় সমস্যা হলে পুরো পেজ না ভাঙে
        $homeCats = [];
        try {
            // প্রত্যেক গ্রুপ থেকে পালা করে নিয়ে ৮টা ক্যাটাগরি (যাতে বিভিন্ন ধরনের দেখায়)
            $lists = array_values(array_filter(array_map(fn($g) => $g->Categories, $this->db->GetCategoryGroups(true))));
            $seenNames = [];
            for ($i = 0; count($homeCats) < 8 && $lists; $i++) {
                $any = false;
                foreach ($lists as $l) {
                    if (!isset($l[$i])) continue;
                    $any = true;
                    $key = mb_strtolower($l[$i]->Name);   // একই নাম দুই গ্রুপে থাকলে একবারই
                    if (isset($seenNames[$key])) continue;
                    $seenNames[$key] = true; $homeCats[] = $l[$i];
                    if (count($homeCats) >= 8) break;
                }
                if (!$any) break;
            }
        } catch (Throwable $e) { error_log('[Home categories] ' . $e->getMessage()); }
        $homeFeed = [];
        try { $homeFeed = $this->db->GetEduFeed(null, null, 3); } catch (Throwable $e) { error_log('[Home feed] ' . $e->getMessage()); }
        $homePkgs = ['Teacher' => [], 'Student' => []];
        $studentFree = false;
        try {
            $homePkgs = ['Teacher' => $this->db->GetPackages('Teacher', true), 'Student' => $this->db->GetPackages('Student', true)];
            $studentFree = (bool)($this->db->PaymentRules()['studentFree'] ?? false);
        } catch (Throwable $e) { error_log('[Home packages] ' . $e->getMessage()); }

        $this->view(null, null, ['SiteSettings' => $settings, 'HomeTuitions' => $homeTuitions,
            'HomeCategories' => $homeCats, 'HomeFeed' => $homeFeed, 'HomePackages' => $homePkgs, 'StudentFree' => $studentFree]);
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
