<?php
/** লগইন — লগইন আর সাইনআপ একই ডিজাইনে (views/Account/_Auth.php), সাইনআপে ক্লিক করলে অ্যানিমেশন করে বদলায় */
$ViewData['Title'] = 'Login';
$ViewData['FullTitle'] = t('লগইন — আপনার অ্যাকাউন্টে প্রবেশ করুন', 'Login — Sign In to Your Tutor or Student Account') . ' | ' . seo_brand();
// returnUrl সহ এলেও canonical ঠিক সেই ঠিকানাই (SEO টুলে Canonical mismatch না দেখায়)
$__ru = (string)input_str('returnUrl', '');
$ViewData['Canonical'] = seo_origin() . '/Account/Login' . ($__ru !== '' && is_local_url($__ru) ? '?returnUrl=' . rawurlencode($__ru) : '');
$ViewData['Description'] = t('মাষ্টার মশাই অ্যাকাউন্টে লগইন করুন — টিউটর খুঁজুন, টিউশন পোস্ট করুন আর ড্যাশবোর্ড দেখুন।', 'Log in to your Mashter Moshai account — find tutors, post tuitions and see your dashboard.');
$mode = 'login';
include __DIR__ . '/_Auth.php';
