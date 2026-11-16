<?php
/**
 * SEO — /robots.txt আর /sitemap.xml (index.php থেকে এখানে আসে)
 *  robots.txt: Google কোন পেজ দেখবে আর কোনগুলো দেখবে না (অ্যাডমিন, লগইন, পেমেন্ট, চ্যাট ...)
 *  sitemap.xml: সব পাবলিক পেজের তালিকা — হোম, টিউটরদের প্রোফাইল, ক্যাটাগরি, টিউশন, নোটিশ, কোর্স, Edu Feed পোস্ট
 */
class SeoController extends Controller
{
    /** সাইটের মূল ঠিকানা (https, www ছাড়া) — canonical আর sitemap এ একই */
    private function origin(): string { return seo_origin(); }

    public function Robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        $o = $this->origin();
        echo "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /Admin\n"
            . "Allow: /Account/Login\n"
            . "Allow: /Account/Register\n"
            . "Disallow: /Account/\n"
            . "Disallow: /Payment/\n"
            . "Disallow: /Chat\n"
            . "Disallow: /Kyc\n"
            . "Disallow: /Notification\n"
            . "Disallow: /Referral\n"
            . "Disallow: /Saved\n"
            . "Disallow: /Friends\n"
            . "Disallow: /Live/\n"
            . "Disallow: /User/Dashboard\n"
            . "Disallow: /Teacher/TeacherDashboard\n"
            . "Disallow: /Course/Learn\n"
            . "Disallow: /Course/Manage\n"
            . "Disallow: /Course/Build\n"
            . "Disallow: /Partner/\n"
            . "\n"
            . "Sitemap: {$o}/sitemap.xml\n";
        exit;
    }

    public function Sitemap(): void
    {
        $o = $this->origin();
        $urls = [];
        $add = function (string $path, ?string $mod = null, string $freq = 'weekly', string $pri = '0.6') use (&$urls, $o) {
            $urls[$path] = [$o . $path, $mod ? date('Y-m-d', strtotime($mod) ?: time()) : null, $freq, $pri];
        };
        // মূল পেজগুলো
        $add('/', null, 'daily', '1.0');
        foreach ([['/Tutors', 'daily', '0.9'], ['/Tuition/Circular', 'daily', '0.9'], ['/Categories', 'weekly', '0.8'], ['/FindTutor', 'daily', '0.8'],
                  ['/Feed', 'daily', '0.7'], ['/Course', 'weekly', '0.7'], ['/Package', 'monthly', '0.7'], ['/Notice', 'weekly', '0.5'],
                  ['/Helpdesk/Contact', 'monthly', '0.4'], ['/Account/Register', 'monthly', '0.4'], ['/Account/Login', 'yearly', '0.3'], ['/Home/Privacy', 'yearly', '0.3'], ['/Home/Terms', 'yearly', '0.3'], ] as [$p, $f, $r]) $add($p, null, $f, $r);

        $db = $this->db;
        try { $db->EnsureCategoryTables(); $db->EnsureEduFeedTables(); } catch (Throwable $e) { }
        // ক্যাটাগরি
        try { foreach ($db->all("SELECT `Slug`, `UpdatedAt`, `CreatedAt` FROM `Categories` WHERE `IsActive` = 1 AND `Slug` <> '' ORDER BY `SortOrder`, `Id`") as $c)
            $add('/Categories/Details/' . rawurlencode((string)$c->Slug), $c->UpdatedAt ?: $c->CreatedAt, 'weekly', '0.7'); } catch (Throwable $e) { error_log('[sitemap] ' . $e->getMessage()); }
        // অনুমোদিত টিউটরদের প্রোফাইল
        try { foreach ($db->GetTutorDirectory() as $t) $add('/Tutors/Profile/' . (int)$t->UserId, $t->RequestDate ?? null, 'weekly', '0.7'); } catch (Throwable $e) { error_log('[sitemap] ' . $e->getMessage()); }
        // খোলা টিউশন
        try { foreach ($db->all("SELECT `Id`, `PostedDate`, `UpdatedAt` FROM `TuitionPosts` WHERE `Status` = 'Open' ORDER BY `Id` DESC LIMIT 2000") as $p)
            $add('/Tuition/Details/' . (int)$p->Id, $p->UpdatedAt ?: $p->PostedDate, 'daily', '0.6'); } catch (Throwable $e) { error_log('[sitemap] ' . $e->getMessage()); }
        // নোটিশ
        try { foreach ($db->all("SELECT `Id`, `PublishedDate`, `UpdatedAt` FROM `Notices` WHERE `IsActive` = 1 ORDER BY `Id` DESC LIMIT 500") as $n)
            $add('/Notice/Details/' . (int)$n->Id, $n->UpdatedAt ?: $n->PublishedDate, 'monthly', '0.4'); } catch (Throwable $e) { error_log('[sitemap] ' . $e->getMessage()); }
        // প্রকাশিত কোর্স
        try { foreach ($db->GetPublicCourses() as $c) $add('/Course/Details/' . (int)$c->Id, $c->UpdatedAt ?? $c->CreatedAt ?? null, 'weekly', '0.6'); } catch (Throwable $e) { error_log('[sitemap] ' . $e->getMessage()); }
        // Edu Feed — Approve করা পোস্ট
        try { foreach ($db->all("SELECT `Id`, `ApprovedAt`, `CreatedAt` FROM `EduPosts` WHERE `Status` = 'Approved' ORDER BY `Id` DESC LIMIT 2000") as $p)
            $add('/Feed/Post/' . (int)$p->Id, $p->ApprovedAt ?: $p->CreatedAt, 'monthly', '0.5'); } catch (Throwable $e) { error_log('[sitemap] ' . $e->getMessage()); }

        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        $x = fn($s) => htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$loc, $mod, $freq, $pri]) {
            echo '  <url><loc>' . $x($loc) . '</loc>' . ($mod ? '<lastmod>' . $mod . '</lastmod>' : '') . '<changefreq>' . $freq . '</changefreq><priority>' . $pri . '</priority></url>' . "\n";
        }
        echo '</urlset>' . "\n";
        exit;
    }
}
