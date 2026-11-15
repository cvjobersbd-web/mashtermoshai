<?php
/** লগইন — লগইন আর সাইনআপ একই ডিজাইনে (views/Account/_Auth.php), সাইনআপে ক্লিক করলে অ্যানিমেশন করে বদলায় */
$ViewData['Title'] = 'Login';
$ViewData['Description'] = t('মাষ্টার মশাই অ্যাকাউন্টে লগইন করুন — টিউটর খুঁজুন, টিউশন পোস্ট করুন আর ড্যাশবোর্ড দেখুন।', 'Log in to your Mashter Moshai account — find tutors, post tuitions and see your dashboard.');
$mode = 'login';
include __DIR__ . '/_Auth.php';
