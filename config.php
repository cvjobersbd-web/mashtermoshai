<?php
/**
 * =============================================================
 *  Smart Tutor Hub — কনফিগারেশন ফাইল (তোমার কম্পিউটার / XAMPP এর জন্য)
 *
 *  ⚠️ এই ফাইলটা সার্ভারে আপলোড করবে না। সার্ভারের config.php তে
 *     cPanel এর ডাটাবেস ইউজার ও পাসওয়ার্ড আলাদা দেওয়া আছে।
 *     (Git deploy করলে সার্ভারের config.php বদলায় না।)
 * =============================================================
 */
return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'mashterm_smarttutordb',   // XAMPP phpMyAdmin এ যে নামে ডাটাবেস Import করেছ
        'user'     => 'root',                    // XAMPP এ root
        'pass'     => '',                        // XAMPP এ পাসওয়ার্ড ফাঁকা
        'charset'  => 'utf8mb4',
    ],

    // বাংলাদেশ সময়
    'timezone' => 'Asia/Dhaka',

    // সমস্যা খুঁজতে true করো (লাইভ সাইটে অবশ্যই false রাখবে)
    'debug' => false,

    // সেশন কতক্ষণ থাকবে (মিনিট)
    'session_minutes' => 60,

    // EPS অনলাইন পেমেন্ট — key গুলো EPS এর Credentials ফাইলে আছে। এখানে (GitHub এ) আসল key রাখবে না:
    // লোকালে config.local.php তে, লাইভে সার্ভারের config.php তে বসাবে। enabled = false থাকলে চেকআউটে EPS দেখায় না।
    'eps' => [
        'enabled'     => false,
        'mode'        => 'sandbox',   // 'sandbox' (টেস্ট) বা 'live' (আসল টাকা)
        'merchant_id' => '',
        'store_id'    => '',
        'username'    => '',
        'password'    => '',
        'hash_key'    => '',
        'return_base' => '',          // ঐচ্ছিক, যেমন 'https://mashtermoshai.com'
        'only_users'  => [],          // ঐচ্ছিক: শুধু এই ইউজার Id গুলো EPS দেখবে (টেস্টের জন্য) — খালি = সবাই
    ],
];
