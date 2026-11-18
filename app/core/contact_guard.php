<?php
/**
 * "পড়াতে চাই" আর Tuition Circular পোস্টে যোগাযোগের তথ্য ঠেকানো।
 * মোবাইল নম্বর, ইমেইল, লিংক, ফেসবুক / হোয়াটসঅ্যাপ ইত্যাদি, আর বাসা / রোড / ফ্ল্যাট নম্বর থাকলে পোস্ট হবে না —
 * যোগাযোগ হবে শুধু সাইটের Apply / রিকোয়েস্টের মাধ্যমে (প্যাকেজ বা ৳২০ এর Apply দিয়ে)।
 * এলাকার নাম (যেমন "মিরপুর ১০", "উত্তরা সেক্টর ৭") দেওয়া যায়।
 */

/** নিয়মগুলো: [ধরন, regex] — বাংলা / ইংরেজি দুই অংকই ধরে, অংকের মাঝে o/O লিখলেও */
function contact_rules(): array
{
    $d = '[0-9০-৯]';                   // ইংরেজি বা বাংলা অংক
    $nb = '(?<![\p{L}\p{M}\p{N}])';   // শব্দের শুরু (বাংলা কার-চিহ্নসহ)
    $ne = '(?![\p{L}\p{M}\p{N}])';    // শব্দের শেষ
    return [
        // ১০টা বা বেশি অংক একসাথে (মাঝে ফাঁকা, ড্যাশ, ডট, ব্র্যাকেট থাকলেও) — মোবাইল / ফোন নম্বর
        ['phone', '/\+?' . $d . '(?:[\s\-\.\(\)\/_]*(?:' . $d . '|[oO])){9,}/u'],
        ['email', '/[A-Za-z0-9._%+\-]+\s*@\s*[A-Za-z0-9\-]+(?:\s*\.\s*[A-Za-z]{2,})+/u'],
        ['email', '/' . $nb . '(gmail|g\s*mail|yahoo|hotmail|outlook|proton\s*mail|icloud|জিমেইল|ইয়াহু)/iu'],
        ['link', '/(https?:\/\/|www\s*\.)\S+/iu'],
        ['link', '/' . $nb . '[A-Za-z0-9\-]+\s*(?:\.|\(dot\)|\[dot\]|\sdot\s|ডট)\s*(?:com|net|org|info|xyz|io|me|bd|link|ly|site|online|page|app|co)' . $ne . '/iu'],
        ['social', '/' . $nb . '(facebook|face\s*book|fb|f\.b|messenger|whats\s*app|wa\.me|imo|telegram|viber|instagram|insta|linkedin|twitter|tiktok|skype|wechat|signal|youtube|you\s*tube)' . $ne . '/iu'],
        ['social', '/(ফেসবুক|ফেইসবুক|ফেবু|মেসেঞ্জার|ম্যাসেঞ্জার|মেসেন্জার|হোয়াটস|হোয়াটস|হুয়াটস|ওয়াটসঅ্যাপ|ওয়াটসঅ্যাপ|টেলিগ্রাম|ভাইবার|ইনস্টাগ্রাম|ইন্সটাগ্রাম|লিংকডইন|লিংকড ইন|টুইটার|টিকটক|স্কাইপ|ইউটিউব)[\p{L}\p{M}]*/u'],
        ['social', '/' . $nb . 'ইমো(?:তে|য়|য়|র|ে)?' . $ne . '/u'],
        // বাসা / রোড / ফ্ল্যাট / হোল্ডিং নম্বর (এলাকার নাম চলবে)
        ['address', '/' . $nb . '(বাসা|বাড়ি|বাড়ি|বাড়ী|বাড়ী|হাউস|হাউজ|house|holding|হোল্ডিং|road|রোড|লেন|lane|flat|ফ্ল্যাট|ফ্লাট|apartment|apt|অ্যাপার্টমেন্ট|এপার্টমেন্ট)\s*(?:নং|নম্বর|নাম্বার|no\.?|number|#)?\s*[:#\-–]?\s*' . $d . '/iu'],
        ['address', '/' . $d . '+\s*(?:নং|no\.?|#)\s*(বাসা|বাড়ি|বাড়ি|রোড|road|house|লেন|lane|ফ্ল্যাট|flat)/iu'],
        ['address', '/' . $d . '+\s*(?:য়|য়|ম|র্থ|তম|th|st|nd|rd)?\s*(তলা|তলায়|তলায়|floor)/iu'],
    ];
}

const CONTACT_LABELS = [
    'phone' => 'মোবাইল / ফোন নম্বর', 'email' => 'ইমেইল', 'link' => 'লিংক / ওয়েবসাইট',
    'social' => 'ফেসবুক / হোয়াটসঅ্যাপ / ইমো / টেলিগ্রাম ইত্যাদি', 'address' => 'বাসা / রোড / ফ্ল্যাট নম্বর',
];

/**
 * লেখাগুলোর কোথাও যোগাযোগের তথ্য থাকলে ইউজারকে দেখানোর মেসেজ, না থাকলে null।
 * $who = 'teacher' | 'student' (মেসেজের শেষ লাইন বদলায়)
 */
function contact_info_error(array $texts, string $who = 'teacher'): ?string
{
    $found = []; $sample = '';
    foreach ($texts as $t) {
        $t = (string)$t;
        if (trim($t) === '') continue;
        foreach (contact_rules() as [$kind, $re]) {
            if (isset($found[$kind])) continue;
            if (preg_match($re, $t, $m)) { $found[$kind] = true; if ($sample === '') $sample = trim($m[0]); }
        }
    }
    if (!$found) return null;
    $labels = array_map(fn($k) => CONTACT_LABELS[$k], array_keys($found));
    return 'পোস্টে যোগাযোগের তথ্য দেওয়া যাবে না — ' . implode(', ', $labels) . ' সরিয়ে আবার চেষ্টা করুন'
        . ($sample !== '' ? ' (পাওয়া গেছে: “' . mb_strimwidth($sample, 0, 30, '…') . '”)' : '') . '। '
        . ($who === 'student' ? 'টিচাররা Apply করলে সাইটের মাধ্যমেই যোগাযোগ হবে।' : 'শিক্ষার্থীরা Apply / রিকোয়েস্ট করলে সাইটের মাধ্যমেই যোগাযোগ হবে।')
        . ' এলাকার নাম (যেমন "মিরপুর ১০") দেওয়া যাবে।';
}

/** আগের পোস্টে থাকা যোগাযোগের তথ্য দেখানোর সময় ঢেকে দেয় (•••) — অ্যাডমিন আর পোস্টদাতা নিজে আসলটা দেখেন */
function contact_mask(?string $text): string
{
    $text = (string)$text;
    if ($text === '') return $text;
    foreach (contact_rules() as [$kind, $re]) {
        $text = preg_replace($re, '•••', $text) ?? $text;
    }
    return $text;
}

/** লেখায় ঢাকার মতো কিছু আছে কিনা (দ্রুত চেক) */
function contact_has(?string $text): bool
{
    return contact_info_error([(string)$text]) !== null;
}
