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
                foreach ($tutors as $t) if ($t->TutorId === 'TCH-' . $m[1]) redirect('/Profile/Show/' . $t->UserId);
            }
            $tidError = "“$tid” আইডির কোনো টিউটর পাওয়া যায়নি।";
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
        $pages = max(1, (int)ceil($total / self::PER_PAGE));
        $page = min($pages, max(1, input_int('page', 1)));
        $this->view(null, array_slice($tutors, ($page - 1) * self::PER_PAGE, self::PER_PAGE), [
            'Type' => $type, 'Title' => self::TYPES[$type], 'Cities' => $cities, 'Page' => $page, 'Pages' => $pages, 'Total' => $total,
        ]);
    }
}
