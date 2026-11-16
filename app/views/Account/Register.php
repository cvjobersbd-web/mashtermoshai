<?php
/** সাইনআপ — লগইন আর সাইনআপ একই ডিজাইনে (views/Account/_Auth.php), লগইনে ক্লিক করলে অ্যানিমেশন করে বদলায় */
$ViewData['Title'] = 'Register';
$ViewData['FullTitle'] = t('ফ্রি অ্যাকাউন্ট খুলুন — টিউটর বা শিক্ষার্থী হিসেবে', 'Create a Free Account — Join as a Tutor or Student') . ' | ' . seo_brand();
$ViewData['Canonical'] = seo_origin() . '/Account/Register';
$ViewData['Description'] = t('মাষ্টার মশাই-এ ফ্রিতে অ্যাকাউন্ট খুলুন — শিক্ষার্থী হিসেবে টিউটর খুঁজুন, অথবা টিউটর হিসেবে টিউশন পান।', 'Create a free Mashter Moshai account — find a tutor as a student, or get tuitions as a tutor.');
$mode = 'register';
include __DIR__ . '/_Auth.php';
