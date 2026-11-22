<?php
/**
 * টিউটর ফিল্টার (/Tutors/All) — শিক্ষার্থী নিজের দরকার অনুযায়ী টিচার খোঁজে।
 * তথ্য আসে টিচারের "Become a Tutor" ফর্ম (TeacherDetails) থেকে; না থাকলে পুরোনো আবেদন / প্রোফাইল থেকে।
 * সব হিসাব সার্ভারে — টিচারের প্রতিষ্ঠান / বেতনের আসল লেখা ব্রাউজারে যায় না।
 */

/** বিশ্ববিদ্যালয় / প্রতিষ্ঠান — key => [বাটনের নাম, পুরো নাম, মেলানোর নিয়ম (regex)] */
function tf_unis(): array
{
    return [
        'du' => ['DU', 'ঢাকা বিশ্ববিদ্যালয়', '/\b(university of dhaka|dhaka university|d\.?u\.?)\b|ঢাকা বিশ্ববিদ্যালয়|ঢাবি/iu'],
        'buet' => ['BUET', 'বুয়েট', '/\bbuet\b|bangladesh university of engineering|বুয়েট|বুয়েট/iu'],
        'cu' => ['CU', 'চট্টগ্রাম বিশ্ববিদ্যালয়', '/\b(university of chittagong|chittagong university|university of chattogram|chattogram university|c\.?u\.?)\b|চট্টগ্রাম বিশ্ববিদ্যালয়|চবি/iu'],
        'cuet' => ['CUET', 'চুয়েট', '/\bcuet\b|chittagong university of engineering|chattogram university of engineering|চুয়েট|চুয়েট/iu'],
        'ru' => ['RU', 'রাজশাহী বিশ্ববিদ্যালয়', '/\b(university of rajshahi|rajshahi university|r\.?u\.?)\b|রাজশাহী বিশ্ববিদ্যালয়|রাবি/iu'],
        'ruet' => ['RUET', 'রুয়েট', '/\bruet\b|rajshahi university of engineering|রুয়েট|রুয়েট/iu'],
        'ku' => ['KU', 'খুলনা বিশ্ববিদ্যালয়', '/\b(khulna university|university of khulna|k\.?u\.?)\b(?!.*engineering)|খুলনা বিশ্ববিদ্যালয়|খুবি/iu'],
        'kuet' => ['KUET', 'কুয়েট', '/\bkuet\b|khulna university of engineering|কুয়েট|কুয়েট/iu'],
        'ju' => ['JU', 'জাহাঙ্গীরনগর', '/jahangirnagar|জাহাঙ্গীরনগর|জাবি/iu'],
        'jnu' => ['JnU', 'জগন্নাথ', '/jagannath|জগন্নাথ|জবি/iu'],
        'sust' => ['SUST', 'শাহজালাল বিজ্ঞান ও প্রযুক্তি', '/\bsust\b|shahjalal university|শাহজালাল বিজ্ঞান|শাবিপ্রবি/iu'],
        'mist' => ['MIST', 'মিলিটারি ইনস্টিটিউট', '/\bmist\b|military institute of science/iu'],
        'bau' => ['BAU', 'কৃষি বিশ্ববিদ্যালয়', '/\bbau\b|bangladesh agricultural university|agricultural university|কৃষি বিশ্ববিদ্যালয়/iu'],
        'medical' => ['মেডিকেল', 'মেডিকেল / ডেন্টাল কলেজ', '/medical college|dental college|\bmbbs\b|\bbds\b|মেডিকেল|ডেন্টাল/iu'],
        'pubother' => ['অন্যান্য পাবলিক', 'অন্যান্য পাবলিক বিশ্ববিদ্যালয়', '/comilla university|cumilla university|begum rokeya|\bbrur\b|noakhali science|\bnstu\b|patuakhali|\bpstu\b|hajee mohammad danesh|\bhstu\b|mawlana bhashani|\bmbstu\b|jashore university|jessore university|\bjust\b|bangabandhu sheikh mujibur rahman science|\bbsmrstu\b|islamic university|\biut\b|islamic university of technology|bangladesh university of professionals|\bbup\b|barishal university|university of barishal|rangamati|textile university|bangladesh university (of|and|&) textiles?|\bbutex\b|sylhet agricultural|sher-?e-?bangla agricultural|chittagong veterinary|chattogram veterinary|\bcvasu\b|kabi nazrul|jatiya kabi|rabindra university|pabna university|gopalganj|army university|\bbaiust\b|\bduet\b|dhaka university of engineering/iu'],
        'private' => ['প্রাইভেট', 'প্রাইভেট বিশ্ববিদ্যালয়', '/north south|\bnsu\b|brac|east west|\bewu\b|\baiub\b|american international|independent university|\biub\b|united international|\buiu\b|daffodil|\bdiu\b|ahsanull?ah|\baust\b|east delta|southeast university|northern university|eastern university|sonargaon|southern university|\biubat\b|\bbubt\b|university of asia pacific|\buap\b|\bulab\b|green university|stamford|state university|university of scholars|manarat|presidency|premier university|international islamic university|\biiuc\b|leading university|metropolitan university|world university|city university|prime university|uttara university|bangladesh university\b|asian university|primeasia|canadian university|university of liberal arts|central women|z\.?h\.? sikder|varendra|north western|port city|britannia|bgc trust|feni university|cox.?s bazar international|dhaka international|atish dipankar|royal university|eu bangladesh|anwer khan|times university|german university|university of global village|bangamata/iu'],
        'college' => ['কলেজ / NU', 'কলেজ / জাতীয় বিশ্ববিদ্যালয়', '/college|collage|national university|\bnu\b|কলেজ|জাতীয় বিশ্ববিদ্যালয়/iu'],
        'madrasa' => ['মাদ্রাসা', 'মাদ্রাসা', '/madrasa|madrassa|madrasah|kamil|fazil|\balim\b|মাদ্রাসা|মাদরাসা/iu'],
        'poly' => ['পলিটেকনিক', 'পলিটেকনিক / ইনস্টিটিউট', '/polytechnic|technical|institute|পলিটেকনিক/iu'],
    ];
}

/** প্রতিষ্ঠানের লেখা → key (প্রথম যেটা মেলে)। "affiliated / college" থাকলে বড় বিশ্ববিদ্যালয় না, কলেজ */
function tf_bn(string $s): string
{
    // বাংলা অক্ষরের দুই রকম লেখা এক করা (য় / ড় / ঢ়)
    return str_replace(["\u{09DF}", "\u{09DC}", "\u{09DD}"], ["\u{09AF}\u{09BC}", "\u{09A1}\u{09BC}", "\u{09A2}\u{09BC}"], $s);
}

function tf_uni_key(string $raw): string
{
    $s = tf_bn(trim(preg_replace('/\s+/u', ' ', $raw)));
    if ($s === '') return '';
    if (preg_match('/technology unit|dhaka central university|university of national|seven colleges|7 colleges|সাত কলেজ/iu', $s)) return 'college';   // ঢাকার ৭ কলেজ / জাতীয় বিশ্ববিদ্যালয়
    $affil = preg_match('/affiliat|অধিভুক্ত|অধিনে|অধীনে/iu', $s);
    $all = tf_unis();
    // প্রকৌশল আগে (Rajshahi University of Engineering ≠ RU), তারপর বাকিগুলো
    $order = array_unique(array_merge(['buet', 'cuet', 'ruet', 'kuet', 'mist', 'sust', 'medical', 'pubother'], array_keys($all)));
    foreach ($order as $k) {
        $re = tf_bn($all[$k][2]);
        if ($affil && in_array($k, ['du', 'cu', 'ru', 'ku'], true)) continue;
        if (in_array($k, ['du', 'cu', 'ru', 'ku'], true) && preg_match('/college|কলেজ/iu', $s)) continue;
        if (preg_match($re, $s)) return $k;
    }
    return preg_match(tf_bn('/university|বিশ্ববিদ্যালয়/iu'), $s) ? 'private' : 'other';
}

/** ক্লাসের দল — key => [নাম, ফর্মের ক্লাসগুলো] */
function tf_class_groups(): array
{
    return [
        'early' => ['প্লে – ক্লাস ৫', ['Play / Nursery', 'KG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5']],
        'c68' => ['ক্লাস ৬ – ৮', ['Class 6', 'Class 7', 'Class 8']],
        'ssc' => ['ক্লাস ৯-১০ / SSC', ['Class 9', 'Class 10', 'SSC']],
        'hsc' => ['ক্লাস ১১-১২ / HSC', ['Class 11', 'Class 12', 'HSC']],
        'olevel' => ['O / A Level', ['O Level', 'A Level']],
        'adm' => ['ভর্তি প্রস্তুতি', ['University Admission', 'Medical Admission', 'Engineering Admission']],
        'uni' => ['অনার্স / ডিপ্লোমা', ['Diploma', 'University (Honours)', 'Masters']],
        'skill' => ['IELTS / চাকরি', ['IELTS', 'Job Preparation']],
    ];
}

const TF_SALARY = ['lt3' => ['৳৩,০০০ এর কম', 0, 2999], '3to5' => ['৳৩,০০০ – ৫,০০০', 3000, 5000], '5to8' => ['৳৫,০০০ – ৮,০০০', 5001, 8000], 'gt8' => ['৳৮,০০০ এর বেশি', 8001, PHP_INT_MAX]];
const TF_SLOTS = ['morning' => ['সকাল', '৬টা–১২টা', 360, 720], 'noon' => ['দুপুর', '১২টা–৩টা', 720, 900], 'afternoon' => ['বিকাল', '৩টা–৬টা', 900, 1080], 'evening' => ['সন্ধ্যা', '৬টা–৯টা', 1080, 1260], 'night' => ['রাত', '৯টা–১২টা', 1260, 1440]];
const TF_DAYS = ['Saturday' => 'শনি', 'Sunday' => 'রবি', 'Monday' => 'সোম', 'Tuesday' => 'মঙ্গল', 'Wednesday' => 'বুধ', 'Thursday' => 'বৃহঃ', 'Friday' => 'শুক্র'];
const TF_MEDIUMS = ['Bangla Medium' => 'বাংলা মিডিয়াম', 'English Version' => 'ইংলিশ ভার্সন', 'English Medium' => 'ইংলিশ মিডিয়াম', 'Madrasa' => 'মাদ্রাসা'];
const TF_EXP = ['new' => 'নতুন (১ বছরের কম)', 'mid' => '১ – ৩ বছর', 'pro' => '৩ বছরের বেশি'];
const TF_GROUPS = ['sci' => 'বিজ্ঞান', 'com' => 'বাণিজ্য', 'arts' => 'মানবিক'];
const TF_RELIGION = ['Islam' => 'ইসলাম', 'Hinduism' => 'হিন্দু', 'Christianity' => 'খ্রিস্টান', 'Buddhism' => 'বৌদ্ধ', 'Other' => 'অন্যান্য'];
const TF_SORTS = ['rec' => 'প্রস্তাবিত', 'new' => 'নতুন আগে', 'rating' => 'রেটিং বেশি আগে', 'fee_low' => 'বেতন কম আগে', 'fee_high' => 'বেতন বেশি আগে'];

/** "05:00 PM", "17:00", "5 pm" → মিনিট; না বুঝলে null */
function tf_minutes(string $t): ?int
{
    $t = strtolower(trim($t));
    if (!preg_match('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/', $t, $m)) return null;
    $h = (int)$m[1]; $mi = (int)($m[2] ?? 0);
    if (!empty($m[3])) { if ($m[3] === 'pm' && $h < 12) $h += 12; if ($m[3] === 'am' && $h === 12) $h = 0; }
    return $h > 24 || $mi > 59 ? null : $h * 60 + $mi;
}

function tf_norm(string $s): string
{
    return trim(preg_replace('/[\s\-_.,()\/]+/u', ' ', mb_strtolower(tf_bn($s))));
}

/** জেলার নামের বিকল্প বানান */
function tf_district_aliases(string $d): array
{
    static $groups = [
        ['chattogram', 'chittagong', 'ctg', 'চট্টগ্রাম'], ['cumilla', 'comilla', 'কুমিল্লা'], ['barishal', 'barisal', 'বরিশাল'], ['dhaka', 'ঢাকা'],
        ['jashore', 'jessore', 'যশোর'], ['bogura', 'bogra', 'বগুড়া'], ['narayanganj', 'narayangonj', 'নারায়ণগঞ্জ'], ['sylhet', 'সিলেট'], ['khulna', 'খুলনা'],
        ['rajshahi', 'রাজশাহী'], ['rangpur', 'রংপুর'], ['mymensingh', 'ময়মনসিংহ'], ['gazipur', 'গাজীপুর'], ["cox's bazar", 'coxs bazar', 'cox bazar', 'কক্সবাজার'],
        ['brahmanbaria', 'b baria', 'ব্রাহ্মণবাড়িয়া'], ['chapainawabganj', 'chapai nawabganj', 'চাঁপাইনবাবগঞ্জ'], ['jhalokathi', 'jhalakathi', 'ঝালকাঠি'],
        ['moulvibazar', 'maulvibazar', 'মৌলভীবাজার'], ['netrokona', 'netrakona', 'নেত্রকোনা'], ['kishoreganj', 'kishorganj', 'কিশোরগঞ্জ'],
    ];
    $k = mb_strtolower(trim($d));
    foreach ($groups as $g) if (in_array($k, $g, true) || in_array(str_replace("'", '', $k), $g, true)) return $g;
    return [$k];
}

/**
 * প্রতিটা টিউটরের ফিল্টারের তথ্য — [uid => facet]
 * @param Obj[] $tutors GetTutorDirectory() এর ফল (একই রিকোয়েস্টে)
 */
function tf_facets(Db $db, array $tutors): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $raw = $db->GetTutorRawMap();
    $det = [];
    try {
        $db->EnsureTeacherDetailsTable();
        foreach ($db->all('SELECT `UserId`, `Data` FROM `TeacherDetails` WHERE `Data` IS NOT NULL') as $r) {
            $d = json_decode((string)$r->Data, true);
            if (is_array($d)) $det[(int)$r->UserId] = $d;
        }
    } catch (Throwable $e) { error_log('[tf_facets] ' . $e->getMessage()); }
    $insts = [];
    try { foreach ($db->all("SELECT `Id`, `InstitutionName` FROM `Users` WHERE `Role` = 'Teacher'") as $u) $insts[(int)$u->Id] = (string)($u->InstitutionName ?? ''); } catch (Throwable $e) { }
    $classIdx = [];
    foreach (tf_class_groups() as $gk => [, $list]) foreach ($list as $c) $classIdx[tf_norm($c)] = $gk;

    $out = [];
    foreach ($tutors as $t) {
        $uid = (int)$t->UserId; $d = $det[$uid] ?? []; $rw = $raw[$uid] ?? [];
        $lst = fn($k) => array_values(array_filter(array_map('strval', (array)($d[$k] ?? [])), fn($x) => trim($x) !== ''));
        // প্রতিষ্ঠান
        $inst = trim((string)($d['university'] ?? '')) ?: trim((string)($rw['inst'] ?? '')) ?: trim((string)($insts[$uid] ?? ''));
        $instAll = trim($inst . ' ' . ($d['gradInstitute'] ?? '') . ' ' . ($d['postInstitute'] ?? ''));
        $uk = tf_uni_key($inst);
        if (in_array($uk, ['', 'other', 'college', 'private'], true) && trim((string)($d['gradInstitute'] ?? '')) !== '') { $g = tf_uni_key((string)$d['gradInstitute']); if (!in_array($g, ['', 'other'], true)) $uk = $g; }
        // ক্লাস / বিষয় / মিডিয়াম
        $classes = $lst('classes') ?: array_values(array_filter((array)$t->Classes, fn($c) => $c !== 'All Classes'));
        $cg = [];
        foreach ($classes as $c) { $k = $classIdx[tf_norm($c)] ?? null; if ($k) $cg[$k] = 1; }
        $subjects = array_map('tf_norm', $lst('subjects') ?: array_values(array_filter((array)$t->Subjects, fn($x) => $x !== 'General')));
        $mediums = $lst('mediums') ?: (array)$t->Mediums;
        // এলাকা
        $areas = tf_norm(implode(' | ', array_merge($lst('preferredAreas'), [(string)($d['livingArea'] ?? '')])));
        $city = trim((string)($d['livingCity'] ?? '')) ?: trim((string)($rw['city'] ?? $t->City));
        // বেতন
        $fee = preg_match('/(\d[\d,]*)/', (string)($d['expectedSalary'] ?? ''), $mm) ? (int)str_replace(',', '', $mm[1]) : ($rw['fee'] ?? null);
        if ($fee !== null && $fee < 100) $fee = null;
        // সময়
        $from = tf_minutes((string)($d['timeFrom'] ?? '')); $to = tf_minutes((string)($d['timeTo'] ?? ''));
        if (($from === null || $to === null) && preg_match('/^(.+?)\s*[-–]\s*(.+)$/u', (string)($rw['time'] ?? ''), $tm)) { $from = tf_minutes($tm[1]); $to = tf_minutes($tm[2]); }
        // দিন
        $days = $lst('days');
        if (!$days && ($rw['days'] ?? '') !== '') foreach (array_keys(TF_DAYS) as $dn) if (stripos((string)$rw['days'], substr($dn, 0, 3)) !== false) $days[] = $dn;
        // অভিজ্ঞতা (বছর)
        $expS = (string)($d['experienceYears'] ?? ($rw['exp'] ?? ''));
        $exp = stripos($expS, 'fresher') !== false ? 0 : (preg_match('/(\d+)/', $expS, $em) ? (int)$em[1] : (int)$t->ExperienceYears);
        // পড়ানোর ধরন
        $meth = $lst('methods');
        $online = $meth ? in_array('Online Tutoring', $meth, true) : in_array($t->TeachingMode, ['Online', 'Both'], true);
        $home = $meth ? (in_array('Home Tutoring', $meth, true) || in_array('Batch Tutoring', $meth, true)) : in_array($t->TeachingMode, ['Home', 'Both'], true);
        // বিভাগ (HSC, না থাকলে SSC)
        $grpS = mb_strtolower((string)(($d['hscGroup'] ?? '') ?: ($d['sscGroup'] ?? '')));
        $grp = str_contains($grpS, 'science') || str_contains($grpS, 'বিজ্ঞান') ? 'sci' : (str_contains($grpS, 'commerce') || str_contains($grpS, 'business') || str_contains($grpS, 'ব্যবসা') || str_contains($grpS, 'বাণিজ্য') ? 'com'
            : (str_contains($grpS, 'human') || str_contains($grpS, 'arts') || str_contains($grpS, 'মানবিক') ? 'arts' : ''));
        // ধর্ম (ফর্মে দেওয়া)
        $relS = mb_strtolower(trim((string)($d['religion'] ?? '')));
        $rel = '';
        foreach (['Islam' => ['islam', 'muslim', 'ইসলাম', 'মুসলিম'], 'Hinduism' => ['hindu', 'হিন্দু', 'সনাতন'], 'Christianity' => ['christian', 'খ্রিস্টান', 'খ্রিষ্টান'], 'Buddhism' => ['buddh', 'বৌদ্ধ']] as $rk => $als)
            foreach ($als as $al) if ($relS !== '' && str_contains($relS, $al)) { $rel = $rk; break 2; }
        if ($rel === '' && $relS !== '') $rel = 'Other';
        $out[$uid] = [
            'rel' => $rel,
            'uni' => $uk, 'inst' => tf_norm($instAll), 'cg' => array_keys($cg), 'sub' => $subjects, 'allSub' => in_array('all subjects', $subjects, true),
            'med' => $mediums, 'areas' => $areas, 'city' => tf_norm($city), 'fee' => $fee, 'from' => $from, 'to' => $to, 'days' => $days, 'exp' => $exp,
            'online' => $online, 'home' => $home, 'grp' => $grp, 'gender' => strtolower((string)$t->Gender), 'photo' => (bool)($rw['photo'] ?? false),
            'prem' => (bool)$t->IsPremium, 'rating' => (float)$t->Rating, 'reviews' => (int)$t->TotalReviews, 'joined' => (string)$t->JoinedAt,
        ];
    }
    return $cache = $out;
}

/** GET থেকে ফিল্টার পড়া (অজানা মান বাদ) */
function tf_read(): array
{
    $arr = function (string $k, ?array $allowed = null, int $max = 30) {
        $v = $_GET[$k] ?? [];
        if (!is_array($v)) $v = $v === '' ? [] : [$v];
        $v = array_values(array_unique(array_filter(array_map(fn($x) => mb_substr(trim((string)$x), 0, 60), $v), fn($x) => $x !== '')));
        if ($allowed !== null) $v = array_values(array_intersect($v, $allowed));
        return array_slice($v, 0, $max);
    };
    $locs = tuition_options()['locations'] ?? [];
    $dist = $arr('dist', array_keys($locs));
    // অন্য বানানে (Chittagong → Chattogram) বা পুরোনো ?city[]= লিংক
    $want = array_merge($arr('city'), array_diff($arr('dist'), $dist));
    foreach ($want as $c) foreach (array_keys($locs) as $L) if (strcasecmp($c, $L) === 0 || in_array(mb_strtolower($c), tf_district_aliases($L), true)) $dist[] = $L;
    $gender = strtolower((string)($_GET['gender'] ?? ''));
    $exp = (string)($_GET['exp'] ?? ''); $mode = (string)($_GET['mode'] ?? ''); $sort = (string)($_GET['sort'] ?? 'rec');
    return [
        'dist' => array_values(array_unique($dist)), 'area' => $arr('area'), 'aq' => mb_substr(trim((string)($_GET['aq'] ?? '')), 0, 60),
        'cls' => $arr('cls', array_keys(tf_class_groups())), 'sub' => $arr('sub'), 'med' => $arr('med', array_keys(TF_MEDIUMS)),
        'sal' => $arr('sal', array_keys(TF_SALARY)), 'slot' => $arr('slot', array_keys(TF_SLOTS)), 'day' => $arr('day', array_keys(TF_DAYS)),
        'uni' => $arr('uni', array_keys(tf_unis())), 'uq' => mb_substr(trim((string)($_GET['uq'] ?? '')), 0, 60),
        'gender' => in_array($gender, ['male', 'female'], true) ? $gender : '', 'exp' => isset(TF_EXP[$exp]) ? $exp : '',
        'mode' => in_array($mode, ['home', 'online'], true) ? $mode : '', 'grp' => $arr('grp', array_keys(TF_GROUPS)), 'rel' => $arr('rel', array_keys(TF_RELIGION)),
        'prem' => !empty($_GET['prem']), 'photo' => false, 'sort' => isset(TF_SORTS[$sort]) ? $sort : 'rec',   // "ছবিসহ প্রোফাইল" ফিল্টার বাদ
    ];
}

/** কয়টা ফিল্টার চালু (সাজানো বাদে) */
function tf_active_count(array $f): int
{
    $n = 0;
    foreach (['dist', 'area', 'cls', 'sub', 'med', 'sal', 'slot', 'day', 'uni', 'grp', 'rel'] as $k) $n += count($f[$k] ?? []);
    foreach (['aq', 'uq', 'gender', 'exp', 'mode'] as $k) if ($f[$k] !== '') $n++;
    return $n + ($f['prem'] ? 1 : 0) + ($f['photo'] ? 1 : 0);
}

/** একজন টিউটর ফিল্টারের সাথে মেলে কিনা */
function tf_match(array $x, array $f, ?string $skip = null): bool
{
    if ($skip !== 'dist' && $f['dist']) {
        $ok = false;
        $locs = tuition_options()['locations'] ?? [];
        foreach ($f['dist'] as $D) {
            foreach (tf_district_aliases($D) as $al) if ($al !== '' && (str_contains($x['city'], tf_norm($al)) || str_contains($x['areas'], tf_norm($al)))) { $ok = true; break 2; }
            if ($D === 'Online' && $x['online']) { $ok = true; break; }
            foreach ($locs[$D] ?? [] as $ar) { $a = tf_norm(preg_replace('/\s*\(.*\)$/u', '', $ar)); if ($a !== '' && mb_strlen($a) > 3 && str_contains($x['areas'], $a)) { $ok = true; break 2; } }
        }
        if (!$ok) return false;
    }
    if ($skip !== 'area' && ($f['area'] || $f['aq'] !== '')) {
        $needles = array_map(fn($a) => tf_norm(preg_replace('/\s*\(.*\)$/u', '', $a)), $f['area']);
        if ($f['aq'] !== '') $needles[] = tf_norm($f['aq']);
        $ok = false;
        foreach ($needles as $nd) if ($nd !== '' && str_contains($x['areas'], $nd)) { $ok = true; break; }
        if (!$ok) return false;
    }
    if ($skip !== 'cls' && $f['cls'] && !array_intersect($f['cls'], $x['cg'])) return false;
    if ($skip !== 'sub' && $f['sub'] && !$x['allSub'] && !array_intersect(array_map('tf_norm', $f['sub']), $x['sub'])) return false;
    if ($skip !== 'med' && $f['med'] && !array_intersect($f['med'], $x['med'])) return false;
    if ($skip !== 'sal' && $f['sal']) {
        if ($x['fee'] === null) return false;
        $ok = false; foreach ($f['sal'] as $k) { [, $lo, $hi] = TF_SALARY[$k]; if ($x['fee'] >= $lo && $x['fee'] <= $hi) { $ok = true; break; } }
        if (!$ok) return false;
    }
    if ($skip !== 'slot' && $f['slot']) {
        if ($x['from'] === null || $x['to'] === null) return false;
        $a = $x['from']; $b = $x['to'] <= $a ? $x['to'] + 1440 : $x['to'];
        $ok = false;
        foreach ($f['slot'] as $k) { [, , $lo, $hi] = TF_SLOTS[$k]; if ($a < $hi && $b > $lo || $a < $hi + 1440 && $b > $lo + 1440) { $ok = true; break; } }
        if (!$ok) return false;
    }
    if ($skip !== 'day' && $f['day'] && array_diff($f['day'], $x['days'])) return false;   // বাছাই করা সব দিনে ফ্রি
    if ($skip !== 'uni' && ($f['uni'] || $f['uq'] !== '')) {
        $ok = $f['uni'] && in_array($x['uni'], $f['uni'], true);
        if (!$ok && $f['uq'] !== '') { $q = tf_norm($f['uq']); $ok = $q !== '' && (str_contains($x['inst'], $q) || ($k = tf_uni_key($f['uq'])) !== 'other' && $k !== 'private' && $k === $x['uni']); }
        if (!$ok) return false;
    }
    if ($skip !== 'gender' && $f['gender'] !== '' && $x['gender'] !== $f['gender']) return false;
    if ($skip !== 'exp' && $f['exp'] !== '') {
        if ($f['exp'] === 'new' && $x['exp'] >= 1) return false;
        if ($f['exp'] === 'mid' && ($x['exp'] < 1 || $x['exp'] > 3)) return false;
        if ($f['exp'] === 'pro' && $x['exp'] <= 3) return false;
    }
    if ($skip !== 'mode' && $f['mode'] !== '' && !$x[$f['mode']]) return false;
    if ($skip !== 'grp' && $f['grp'] && !in_array($x['grp'], $f['grp'], true)) return false;
    if ($skip !== 'rel' && !empty($f['rel']) && !in_array($x['rel'], $f['rel'], true)) return false;
    if ($f['prem'] && !$x['prem']) return false;
    if ($f['photo'] && !$x['photo']) return false;
    return true;
}

/** ফিল্টার + সাজানো */
function tf_apply(array $tutors, array $facets, array $f): array
{
    $out = array_values(array_filter($tutors, fn($t) => isset($facets[(int)$t->UserId]) && tf_match($facets[(int)$t->UserId], $f)));
    $fx = fn($t) => $facets[(int)$t->UserId];
    switch ($f['sort']) {
        case 'keep': break;
        case 'new': usort($out, fn($a, $b) => strcmp($fx($b)['joined'], $fx($a)['joined'])); break;
        case 'rating': usort($out, fn($a, $b) => [$fx($b)['rating'], $fx($b)['reviews']] <=> [$fx($a)['rating'], $fx($a)['reviews']]); break;
        case 'fee_low': usort($out, fn($a, $b) => ($fx($a)['fee'] ?? PHP_INT_MAX) <=> ($fx($b)['fee'] ?? PHP_INT_MAX)); break;
        case 'fee_high': usort($out, fn($a, $b) => ($fx($b)['fee'] ?? -1) <=> ($fx($a)['fee'] ?? -1)); break;
        default:   // প্রস্তাবিত: Premium → রেটিং → তথ্য বেশি দেওয়া → নতুন
            usort($out, function ($a, $b) use ($fx) {
                $A = $fx($a); $B = $fx($b);
                $ia = count($A['cg']) > 0 && $A['fee'] !== null && $A['from'] !== null; $ib = count($B['cg']) > 0 && $B['fee'] !== null && $B['from'] !== null;
                return [$B['prem'], $B['rating'], $ib, $B['joined']] <=> [$A['prem'], $A['rating'], $ia, $A['joined']];
            });
    }
    return $out;
}

/** প্রতিটা অপশনে কতজন (অন্য বাছাই করা ফিল্টার ধরে, কিন্তু ওই ঘরের নিজের বাছাই বাদ দিয়ে) */
function tf_counts(array $facets, array $f): array
{
    $c = [];
    $groups = [
        'dist' => fn($x, $v) => tf_match($x, ['dist' => [$v]] + tf_blank(), null),
        'cls' => fn($x, $v) => in_array($v, $x['cg'], true),
        'sub' => fn($x, $v) => $x['allSub'] || in_array(tf_norm($v), $x['sub'], true),
        'med' => fn($x, $v) => in_array($v, $x['med'], true),
        'sal' => fn($x, $v) => $x['fee'] !== null && $x['fee'] >= TF_SALARY[$v][1] && $x['fee'] <= TF_SALARY[$v][2],
        'slot' => fn($x, $v) => tf_match($x, ['slot' => [$v]] + tf_blank(), null),
        'day' => fn($x, $v) => in_array($v, $x['days'], true),
        'uni' => fn($x, $v) => $x['uni'] === $v,
        'grp' => fn($x, $v) => $x['grp'] === $v,
        'rel' => fn($x, $v) => $x['rel'] === $v,
    ];
    $vals = ['dist' => array_keys(tuition_options()['locations'] ?? []), 'cls' => array_keys(tf_class_groups()), 'sub' => tuition_options()['subjects'] ?? default_tuition_subjects(),
        'med' => array_keys(TF_MEDIUMS), 'sal' => array_keys(TF_SALARY), 'slot' => array_keys(TF_SLOTS), 'day' => array_keys(TF_DAYS), 'uni' => array_keys(tf_unis()), 'grp' => array_keys(TF_GROUPS), 'rel' => array_keys(TF_RELIGION)];
    foreach ($groups as $g => $fn) {
        $base = array_filter($facets, fn($x) => tf_match($x, $f, $g));
        foreach ($vals[$g] as $v) { $n = 0; foreach ($base as $x) if ($fn($x, $v)) $n++; $c[$g][$v] = $n; }
    }
    return $c;
}

function tf_blank(): array
{
    return ['dist' => [], 'area' => [], 'aq' => '', 'cls' => [], 'sub' => [], 'med' => [], 'sal' => [], 'slot' => [], 'day' => [], 'uni' => [], 'uq' => '',
        'gender' => '', 'exp' => '', 'mode' => '', 'grp' => [], 'rel' => [], 'prem' => false, 'photo' => false, 'sort' => 'rec'];
}

/** একটা জেলার এলাকাগুলো আর প্রতিটায় কতজন (এখনকার অন্য ফিল্টার ধরে) */
function tf_area_counts(array $facets, array $f, array $districts): array
{
    $locs = tuition_options()['locations'] ?? [];
    $base = array_filter($facets, fn($x) => tf_match($x, $f, 'area'));
    $out = [];
    foreach ($districts as $D) foreach ($locs[$D] ?? [] as $ar) {
        $a = tf_norm(preg_replace('/\s*\(.*\)$/u', '', $ar)); $n = 0;
        if ($a !== '') foreach ($base as $x) if (str_contains($x['areas'], $a)) $n++;
        $out[$D][$ar] = $n;
    }
    return $out;
}
