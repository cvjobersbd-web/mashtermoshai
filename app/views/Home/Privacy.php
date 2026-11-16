<?php
/** গোপনীয়তা নীতি (Privacy Policy) — সাইট আসলে যে তথ্য নেয় আর যেভাবে ব্যবহার করে তার বিবরণ */
$ViewData['Title'] = t('গোপনীয়তা নীতি', 'Privacy Policy');
$ViewData['FullTitle'] = t('গোপনীয়তা নীতি — আপনার তথ্য কীভাবে সুরক্ষিত থাকে', 'Privacy Policy — How We Protect Your Data') . ' | ' . seo_brand();
$ViewData['Description'] = t('মাষ্টার মশাই কোন তথ্য নেয়, কেন নেয়, কীভাবে সুরক্ষিত রাখে আর কার সাথে শেয়ার হয় — গোপনীয়তা নীতি।', 'What information Mashter Moshai collects, why, how it is protected and who it is shared with — our Privacy Policy.');
$ct = contact_info(); $en = site_lang() === 'en';
partial('Home/_PolicyStyles');
?>
<div class="pol">
    <h1><?= t('গোপনীয়তা নীতি', 'Privacy Policy') ?></h1>
    <p class="pol-up"><?= t('সর্বশেষ আপডেট: ১৫ নভেম্বর ২০২৬', 'Last updated: 15 November 2026') ?></p>

<?php if (!$en): ?>
    <p>মাষ্টার মশাই (mashtermoshai.com) শিক্ষার্থী, অভিভাবক আর টিউটরদের যুক্ত করার একটা অনলাইন প্ল্যাটফর্ম। আপনার তথ্যের নিরাপত্তা আমাদের কাছে গুরুত্বপূর্ণ। এই নীতিতে বলা আছে আমরা কোন তথ্য নিই, কেন নিই আর কীভাবে রাখি।</p>

    <h2>১. আমরা যে তথ্য নিই</h2>
    <ul>
        <li><b>অ্যাকাউন্টের তথ্য:</b> নাম, ইউজারনেম, ইমেইল আর পাসওয়ার্ড (পাসওয়ার্ড এনক্রিপ্ট করে রাখা হয়, কেউ দেখতে পারে না)।</li>
        <li><b>যোগাযোগের তথ্য:</b> মোবাইল ও WhatsApp নম্বর।</li>
        <li><b>প্রোফাইলের তথ্য:</b> ছবি, শিক্ষাগত যোগ্যতা, প্রতিষ্ঠান, এলাকা, পড়ানোর বিষয়, অভিজ্ঞতা ইত্যাদি — যা আপনি নিজে দেন।</li>
        <li><b>যাচাইয়ের (Verification) তথ্য:</b> টিচারদের Academic ID কার্ড ও NID কার্ডের ছবি; শিক্ষার্থীদের ক্লাস, প্রতিষ্ঠান, বাবা-মায়ের নাম ও নম্বর, ঠিকানা আর ID কার্ডের ছবি।</li>
        <li><b>পেমেন্টের তথ্য:</b> যে নম্বর থেকে টাকা পাঠিয়েছেন, Transaction ID আর রসিদের ছবি। আমরা আপনার bKash / Nagad / ব্যাংকের পিন বা পাসওয়ার্ড কখনো নিই না।</li>
        <li><b>সাইট ব্যবহারের তথ্য:</b> পোস্ট, কমেন্ট, মেসেজ, রিভিউ, কোর্সের অগ্রগতি, কোন ডিভাইস থেকে লগইন করেছেন।</li>
    </ul>

    <h2>২. কেন তথ্য নিই</h2>
    <ul>
        <li>অ্যাকাউন্ট চালানো, লগইন আর পাসওয়ার্ড রিসেটের জন্য।</li>
        <li>টিচার ও শিক্ষার্থী যে আসল মানুষ, সেটা যাচাই করে সবার নিরাপত্তা নিশ্চিত করতে।</li>
        <li>টিউটর আর শিক্ষার্থীর মধ্যে মিল খুঁজে দিতে, রিকোয়েস্ট আর মেসেজ পৌঁছে দিতে।</li>
        <li>পেমেন্ট যাচাই করে প্যাকেজ চালু করতে।</li>
        <li>নোটিফিকেশন আর দরকারি ইমেইল পাঠাতে।</li>
        <li>প্রতারণা, ভুয়া অ্যাকাউন্ট আর নিয়ম ভাঙা ঠেকাতে।</li>
    </ul>

    <h2>৩. কারা আপনার তথ্য দেখতে পায়</h2>
    <ul>
        <li><b>সবাই দেখতে পায়:</b> টিচারের নাম, প্রতিষ্ঠান, বিষয়, এলাকা আর প্রোফাইলের সাধারণ তথ্য (Tutor List এ)।</li>
        <li><b>প্যাকেজ থাকলে দেখা যায়:</b> সাইটের নিয়ম অনুযায়ী কিছু তথ্য শুধু প্যাকেজ থাকা ইউজাররা দেখতে পান।</li>
        <li><b>শুধু অ্যাডমিন দেখেন:</b> ID কার্ড / NID এর ছবি, পেমেন্টের রসিদ, শিক্ষার্থীর বাবা-মায়ের তথ্য। ID কার্ডের ছবি আলাদা সুরক্ষিত জায়গায় রাখা হয়, সরাসরি লিংক দিয়ে খোলা যায় না।</li>
        <li>আইনের প্রয়োজনে সরকারি কর্তৃপক্ষ চাইলে তথ্য দেওয়া হতে পারে। এছাড়া আমরা কারো কাছে আপনার তথ্য বিক্রি করি না।</li>
    </ul>

    <h2>৪. কুকি আর বিশ্লেষণ</h2>
    <p>লগইন ধরে রাখা, ভাষা (বাংলা / English) মনে রাখা আর রেফারেল হিসাবের জন্য সাইট কুকি ব্যবহার করে। সাইট কেমন চলছে বুঝতে আর বিজ্ঞাপনের ফল মাপতে Meta (Facebook) Pixel ও Google Analytics ব্যবহার হতে পারে — এগুলো কে কোন পেজ দেখেছে এমন সাধারণ তথ্য নেয়।</p>

    <h2>৫. তথ্যের নিরাপত্তা</h2>
    <p>পুরো সাইট HTTPS (SSL) দিয়ে চলে, পাসওয়ার্ড এনক্রিপ্ট করা থাকে, আর অচেনা ডিভাইস থেকে লগইন আটকানোর ব্যবস্থা আছে। তবু ইন্টারনেটে কোনো ব্যবস্থাই ১০০% নিরাপদ নয় — তাই নিজের পাসওয়ার্ড কাউকে দেবেন না।</p>

    <h2>৬. আপনার অধিকার</h2>
    <ul>
        <li>প্রোফাইল থেকে নিজের তথ্য দেখতে ও বদলাতে পারবেন।</li>
        <li>অ্যাকাউন্ট মুছে ফেলতে চাইলে Helpdesk এ জানান — যাচাই করে মুছে দেওয়া হবে (পেমেন্টের হিসাবের মতো যে তথ্য আইনগতভাবে রাখতে হয়, তা ছাড়া)।</li>
    </ul>

    <h2>৭. শিশুদের তথ্য</h2>
    <p>স্কুলের শিক্ষার্থীরা অভিভাবকের অনুমতি নিয়ে অ্যাকাউন্ট খুলবেন। এজন্যই Student Verification এ অভিভাবকের তথ্য নেওয়া হয়।</p>

    <h2>৮. নীতির পরিবর্তন</h2>
    <p>প্রয়োজনে এই নীতি বদলানো হতে পারে। বড় কোনো পরিবর্তন হলে সাইটে নোটিশ দেওয়া হবে।</p>

    <h2>৯. যোগাযোগ</h2>
    <p>গোপনীয়তা নিয়ে কোনো প্রশ্ন থাকলে <a href="/Helpdesk/Contact">যোগাযোগ পেজ</a> বা Helpdesk ব্যবহার করুন<?= $ct['ct_phone'] !== '' ? ', অথবা কল করুন ' . e($ct['ct_phone']) : '' ?><?= $ct['ct_email'] !== '' ? ', ইমেইল: ' . e($ct['ct_email']) : '' ?>।</p>
<?php else: ?>
    <p>Mashter Moshai (mashtermoshai.com) is an online platform that connects students, parents and tutors. Your privacy matters to us. This policy explains what information we collect, why, and how we keep it.</p>

    <h2>1. Information we collect</h2>
    <ul>
        <li><b>Account information:</b> name, username, email and password (stored encrypted — nobody can read it).</li>
        <li><b>Contact information:</b> mobile and WhatsApp number.</li>
        <li><b>Profile information:</b> photo, education, institution, area, subjects, experience and other details you choose to add.</li>
        <li><b>Verification information:</b> teachers' Academic ID card and NID images; students' class, institution, parents' names and numbers, address and ID card images.</li>
        <li><b>Payment information:</b> the number you paid from, Transaction ID and receipt image. We never ask for your bKash / Nagad / bank PIN or password.</li>
        <li><b>Usage information:</b> posts, comments, messages, reviews, course progress and the devices you log in from.</li>
    </ul>

    <h2>2. Why we collect it</h2>
    <ul>
        <li>To run your account, log in and reset passwords.</li>
        <li>To verify that teachers and students are real people and keep everyone safe.</li>
        <li>To match tutors and students and deliver requests and messages.</li>
        <li>To verify payments and activate packages.</li>
        <li>To send notifications and important emails.</li>
        <li>To prevent fraud, fake accounts and rule-breaking.</li>
    </ul>

    <h2>3. Who can see your information</h2>
    <ul>
        <li><b>Everyone:</b> a teacher's name, institution, subjects, area and general profile details (in the Tutor List).</li>
        <li><b>Users with a package:</b> some information is shown only to users with a package, according to the site's rules.</li>
        <li><b>Admins only:</b> ID card / NID images, payment receipts and students' parent details. ID card images are stored in a separate protected location and cannot be opened by direct link.</li>
        <li>We may share information with government authorities when required by law. Other than that, we never sell your information to anyone.</li>
    </ul>

    <h2>4. Cookies and analytics</h2>
    <p>The site uses cookies to keep you logged in, remember your language (Bangla / English) and track referrals. To understand how the site is used and measure advertising, we may use Meta (Facebook) Pixel and Google Analytics — these collect general information such as which pages were visited.</p>

    <h2>5. Security</h2>
    <p>The whole site runs on HTTPS (SSL), passwords are encrypted, and there are safeguards against logins from unknown devices. Still, no system on the internet is 100% secure — never share your password with anyone.</p>

    <h2>6. Your rights</h2>
    <ul>
        <li>You can view and edit your information from your profile.</li>
        <li>To delete your account, contact the Helpdesk — we will verify and delete it (except information we must keep by law, such as payment records).</li>
    </ul>

    <h2>7. Children's information</h2>
    <p>School students should create an account with their parent's or guardian's permission. That is why Student Verification asks for guardian details.</p>

    <h2>8. Changes to this policy</h2>
    <p>We may update this policy when needed. Major changes will be announced on the site.</p>

    <h2>9. Contact</h2>
    <p>For any privacy question, use the <a href="/Helpdesk/Contact">Contact page</a> or the Helpdesk<?= $ct['ct_phone'] !== '' ? ', or call ' . e($ct['ct_phone']) : '' ?><?= $ct['ct_email'] !== '' ? ', email: ' . e($ct['ct_email']) : '' ?>.</p>
<?php endif; ?>
    <p class="pol-links"><a href="/Home/Terms"><?= t('শর্তাবলি দেখুন', 'Read the Terms of Service') ?> →</a></p>
</div>
