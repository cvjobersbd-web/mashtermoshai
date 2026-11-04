<?php
/**
 * Tutor List (Tutor Hub) — অনুমোদিত সব টিচারের তালিকা।
 *  GET /Tutors                        সেকশন আকারে (All / Premium / Top Rated / New) + পরিসংখ্যান + Tutor ID দিয়ে খোঁজা
 *  GET /Tutors/All?type=&city[]=&page= সব টিউটর — জেলা ফিল্টার আর পেজ নম্বর সহ
 * "See" চাপলে টিচারের প্রোফাইল (/Profile/Show/{id}) খোলে।
 * ডাটা: Db::GetTutorDirectory() (Find Tutor পেজের মতো একই নিয়ম — সাসপেন্ড / বন্ধ অ্যাকাউন্ট দেখায় না)
 */
class TutorsController extends Controller
{
    private const PER_PAGE = 16;
    public const CITIES = ['Dhaka', 'Chittagong', 'Khulna', 'Gazipur', 'Narayanganj', 'Sylhet', 'Cumilla', 'Barishal', 'Rajshahi', 'Rangpur', 'Mymensingh'];
    public const TYPES = [
        'all' => 'All Tutors', 'premium' => 'Premium Tutors', 'top' => 'Top Rated Tutors', 'new' => 'New Tutors',
    ];

    /** ধরন অনুযায়ী টিউটর বাছাই */
    private function ofType(array $tutors, string $type): array
    {
        switch ($type) {
            case 'premium': return array_values(array_filter($tutors, fn($t) => $t->IsPremium));
            case 'top':
                $r = array_values(array_filter($tutors, fn($t) => $t->TotalReviews > 0));
                usort($r, fn($a, $b) => [$b->Rating, $b->TotalReviews] <=> [$a->Rating, $a->TotalReviews]);
                return $r;
            case 'new':
                $r = $tutors;
                usort($r, fn($a, $b) => strcmp((string)$b->JoinedAt, (string)$a->JoinedAt));
                return $r;
        }
        return $tutors;
    }

    /** Tutor এর শহর এই জেলাগুলোর কোনোটার সাথে মিলে কিনা (Chattogram = Chittagong, Comilla = Cumilla ইত্যাদি) */
    public static function cityMatches(object $t, array $cities): bool
    {
        if (!$cities) return true;
        $hay = mb_strtolower($t->City . ' ' . $t->Location);
        $alias = ['chittagong' => ['chittagong', 'chattogram'], 'cumilla' => ['cumilla', 'comilla'], 'barishal' => ['barishal', 'barisal'], 'narayanganj' => ['narayanganj', 'narayangonj']];
        foreach ($cities as $c) {
            foreach ($alias[mb_strtolower($c)] ?? [mb_strtolower($c)] as $a) if ($a !== '' && str_contains($hay, $a)) return true;
        }
        return false;
    }

    /** GET /Tutors */
    public function Index(): void
    {
        $tutors = $this->db->GetTutorDirectory();
        // Tutor ID দিয়ে খোঁজা (যেমন TCH-1024 বা 1024)
        $tid = strtoupper(trim((string)input_str('tid', '')));
        $tidError = '';
        if ($tid !== '') {
            if (preg_match('/^(?:TCH-?)?(\d{1,7})$/', $tid, $m)) {
                foreach ($tutors as $t) if ($t->TutorId === 'TCH-' . $m[1]) { pixel_track('Search', ['search_string' => $tid, 'content_category' => 'Tutor ID']); redirect('/Profile/Show/' . $t->UserId); }
            }
            $tidError = "“{$tid}” আইডির কোনো টিউটর পাওয়া যায়নি।";
            pixel_track('Search', ['search_string' => $tid, 'content_category' => 'Tutor ID']);
        }
        $male = count(array_filter($tutors, fn($t) => strcasecmp($t->Gender, 'Male') === 0));
        $female = count(array_filter($tutors, fn($t) => strcasecmp($t->Gender, 'Female') === 0));
        $cityCounts = [];
        foreach (self::CITIES as $c) {
            $n = count(array_filter($tutors, fn($t) => self::cityMatches($t, [$c])));
            if ($n > 0) $cityCounts[$c] = $n;
        }
        arsort($cityCounts);
        $sections = [];
        foreach (self::TYPES as $k => $label) {
            $list = array_slice($this->ofType($tutors, $k), 0, 12);
            if ($list) $sections[$k] = ['title' => $label, 'tutors' => $list];
        }
        $this->view(null, $sections, [
            'Total' => count($tutors), 'Male' => $male, 'Female' => $female, 'Cities' => $cityCounts,
            'Tid' => $tid, 'TidError' => $tidError,
        ]);
    }

    /** GET /Tutors/All?type=all&city[]=Dhaka&page=2 */
    public function All(): void
    {
        $type = (string)input_str('type', 'all');
        if (!isset(self::TYPES[$type])) $type = 'all';
        $raw = $_GET['city'] ?? [];
        if (!is_array($raw)) $raw = [$raw];
        $cities = array_values(array_intersect(self::CITIES, array_map(fn($c) => trim((string)$c), $raw)));
        $tutors = array_values(array_filter($this->ofType($this->db->GetTutorDirectory(), $type), fn($t) => self::cityMatches($t, $cities)));
        $total = count($tutors);
        if ($cities && input_int('page', 1) <= 1) pixel_track('Search', ['search_string' => implode(', ', $cities), 'content_category' => self::TYPES[$type]]);
        $pages = max(1, (int)ceil($total / self::PER_PAGE));
        $page = min($pages, max(1, input_int('page', 1)));
        $this->view(null, array_slice($tutors, ($page - 1) * self::PER_PAGE, self::PER_PAGE), [
            'Type' => $type, 'Title' => self::TYPES[$type], 'Cities' => $cities, 'Page' => $page, 'Pages' => $pages, 'Total' => $total,
        ]);
    }

    /** GET /Tutors/Profile/{userId} — টিচারের পূর্ণ ডিটেইলস (লগইন ছাড়াও দেখা যায়; ফোন / ইমেইল / ঠিকানা দেখায় না) */
    public function Profile(): void
    {
        $uid = input_int('id', 0);
        $me = current_user_id();
        $isOwner = $me !== null && (int)$me === $uid;
        $isAdmin = is_admin();
        $all = $this->db->GetTutorDirectory();
        $t = null;
        foreach ($all as $x) if ($x->UserId === $uid) { $t = $x; break; }
        $user = $this->db->GetUserById($uid);
        // অ্যাডমিন টিচার আবেদন যাচাইয়ের সময় (এখনো টিচার না) পুরো ফর্ম দেখতে পারেন
        if (!$user || ($user->Role !== 'Teacher' && !($isAdmin && $this->db->HasTeacherDetails($uid)))) not_found('এই টিউটরকে পাওয়া যায়নি।');
        if (!$t && !$isOwner && !$isAdmin) not_found('এই টিউটরের প্রোফাইল এখন দেখা যাচ্ছে না।');
        if (!$t) { // মালিক / অ্যাডমিন — তালিকায় না থাকলেও (যেমন সাবস্ক্রিপশন নেই) দেখতে পারবে
            $name = !is_blank($user->FullName) ? (string)$user->FullName : (string)$user->UserName;
            $t = obj(['UserId' => $uid, 'TutorId' => '', 'FullName' => $name, 'Initials' => initials($name, 'T'), 'PhotoUrl' => (string)($user->ProfileImageLink ?? ''),
                'Institution' => 'Not specified', 'Subjects' => [], 'Classes' => [], 'Mediums' => [], 'City' => '', 'Location' => '', 'ExperienceYears' => 0,
                'MonthlyFee' => 0, 'AvailableTime' => '', 'About' => '', 'IsPremium' => false, 'IsTopRated' => false, 'Gender' => (string)($user->Gender ?? ''), 'TeachingMode' => '']);
        }
        if (!$isOwner && $me !== null && current_role() !== 'Admin') $this->db->RecordProfileView($uid, (int)$me);
        if (!$isOwner) pixel_track('ViewContent', ['content_type' => 'tutor', 'content_ids' => [(string)$uid], 'content_name' => (string)$t->FullName]);
        // নিজে বা অ্যাডমিন দেখলে গোনা হয় না
        if ($isOwner || $isAdmin) { $this->db->EnsureTeacherDetailsTable(); $views = (int)$this->db->scalar('SELECT COALESCE(MAX(`PageViews`), 0) FROM `TeacherDetails` WHERE `UserId` = :u', ['u' => $uid]); }
        else $views = $this->db->BumpTeacherPageViews($uid);

        // একই শহর বা একই বিষয়ের অন্য টিউটর
        $related = [];
        foreach ($all as $x) {
            if ($x->UserId === $uid) continue;
            $score = ($t->City !== '' && strcasecmp($x->City, $t->City) === 0 ? 2 : 0) + count(array_intersect(array_map('mb_strtolower', $x->Subjects), array_map('mb_strtolower', $t->Subjects)));
            if ($score > 0) $related[] = [$score, $x];
        }
        usort($related, fn($a, $b) => $b[0] <=> $a[0]);
        $related = array_map(fn($r) => $r[1], array_slice($related, 0, 8));
        if (count($related) < 4) foreach ($all as $x) { if (count($related) >= 8) break; if ($x->UserId !== $uid && !in_array($x, $related, true)) $related[] = $x; }

        $cv = '';
        try { $cv = (string)$this->db->scalar("SELECT `CvLink` FROM `TeacherRequests` WHERE `UserId` = :u AND `Status` = 'Approved' ORDER BY `Id` DESC LIMIT 1", ['u' => $uid]); } catch (Throwable $e) { }
        $lastSeen = null;
        try { $lastSeen = $this->db->GetTeacherIntro($uid)->LastSeenAt; } catch (Throwable $e) { }

        $this->view(null, $t, [
            'User' => $user, 'D' => $this->db->GetTeacherDetails($uid), 'IsOwner' => $isOwner, 'IsAdmin' => $isAdmin, 'Views' => $views,
            'Related' => $related, 'Stats' => $this->db->TeacherPlatformStats($uid), 'Cv' => preg_match('#^https?://#i', $cv) ? $cv : '',
            'Reviews' => $this->db->GetTeacherReviews($uid, 30), 'RStats' => $this->db->TeacherReviewStats($uid),
            'CanReview' => $me !== null && !$isOwner && current_role() !== 'Admin' && $this->db->CanReviewTeacher((int)$me, $uid),
            'MyReview' => $me !== null ? $this->db->GetMyTeacherReview($uid, (int)$me) : null,
            'Saved' => $me !== null && !$isOwner ? $this->db->IsSaved((int)$me, 'tutor', $uid) : false,
            'LastSeen' => $lastSeen,
        ]);
    }
}
