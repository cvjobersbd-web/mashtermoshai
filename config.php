<?php
/**
 * =============================================================
 *  Smart Tutor Hub — কনফিগারেশন ফাইল
 *  cPanel → MySQL Databases থেকে যে ডাটাবেস/ইউজার বানাবেন,
 *  সেগুলোর তথ্য এখানে বসান।
 * =============================================================
 */
return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'smarttutordb',   // ডাটাবেসের নাম (cPanel এ: mashterm_xxxx)
        'user'     => 'root',           // ডাটাবেস ইউজার (XAMPP এ root)
        'pass'     => '',               // ডাটাবেস পাসওয়ার্ড (XAMPP এ ফাঁকা)
        'charset'  => 'utf8mb4',
    ],

    // বাংলাদেশ সময়
    'timezone' => 'Asia/Dhaka',

    // সমস্যা খুঁজতে true করুন (লাইভ সাইটে অবশ্যই false রাখবেন)
    'debug' => false,

    // সেশন কতক্ষণ থাকবে (মিনিট)
    'session_minutes' => 60,
];
