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
];
