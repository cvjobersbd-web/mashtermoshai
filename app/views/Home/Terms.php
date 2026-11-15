<?php
/** শর্তাবলি (Terms of Service) — সাইটের আসল নিয়মগুলো এক জায়গায় */
$ViewData['Title'] = t('শর্তাবলি', 'Terms of Service');
$ViewData['Description'] = t('মাষ্টার মশাই ব্যবহারের শর্তাবলি — অ্যাকাউন্ট, যাচাই, প্যাকেজ ও পেমেন্ট, পোস্ট আর কোর্সের নিয়ম।', 'Terms of Service for using Mashter Moshai — accounts, verification, packages and payments, posts and courses.');
$en = site_lang() === 'en';
partial('Home/_PolicyStyles');
?>
<div class="pol">
    <h1><?= t('শর্তাবলি', 'Terms of Service') ?></h1>
    <p class="pol-up"><?= t('সর্বশেষ আপডেট: ১৫ নভেম্বর ২০২৬', 'Last updated: 15 November 2026') ?></p>

<?php if (!$en): ?>
    <p>মাষ্টার মশাই (mashtermoshai.com) ব্যবহার করলে আপনি নিচের শর্তগুলো মেনে নিচ্ছেন। দয়া করে মনোযোগ দিয়ে পড়ুন।</p>

    <h2>১. অ্যাকাউন্ট</h2>
    <ul>
        <li>সঠিক নাম, ইমেইল আর মোবাইল নম্বর দিয়ে অ্যাকাউন্ট খুলতে হবে। ভুয়া তথ্য দিলে অ্যাকাউন্ট বন্ধ করা হতে পারে।</li>
        <li>একটা অ্যাকাউন্ট হয় শিক্ষার্থীর, নয় টিচারের — দুটো একসাথে না।</li>
        <li>নিজের পাসওয়ার্ড গোপন রাখার দায়িত্ব আপনার। আপনার অ্যাকাউন্ট থেকে যা হবে, তার দায় আপনার।</li>
    </ul>

    <h2>২. যাচাই (Verification)</h2>
    <ul>
        <li>টিচার হতে Become a Tutor ফর্মে সঠিক তথ্য, Academic ID কার্ড আর NID দিতে হবে। অ্যাডমিন যাচাই করে Approve করলে তবেই টিচার হবেন।</li>
        <li>শিক্ষার্থীদের Student Verification ফর্মে সঠিক তথ্য আর ID কার্ড দিতে হবে।</li>
        <li>ভুল বা অন্যের কাগজ দিলে আবেদন বাতিল হবে, প্রয়োজনে অ্যাকাউন্ট বন্ধ হবে।</li>
    </ul>

    <h2>৩. প্যাকেজ ও পেমেন্ট</h2>
    <ul>
        <li>পেমেন্ট পেজে দেখানো নম্বরে টাকা পাঠিয়ে সঠিক Transaction ID আর রসিদের ছবি জমা দিতে হবে।</li>
        <li>অ্যাডমিন পেমেন্ট যাচাই করে Accept করলে প্যাকেজ চালু হয়; মেয়াদ শেষ হলে সুবিধাগুলো বন্ধ হয়ে যায়।</li>
        <li>ভুল বা ভুয়া পেমেন্টের তথ্য দিলে অনুরোধ বাতিল হবে।</li>
        <li>রিফান্ড নিয়ে জানতে Helpdesk এ যোগাযোগ করুন।</li>
    </ul>

    <h2>৪. যোগাযোগ ও আচরণ</h2>
    <ul>
        <li>টিচার আর শিক্ষার্থীর যোগাযোগ সাইটের রিকোয়েস্ট আর মেসেজের মাধ্যমে হবে।</li>
        <li>কাউকে হয়রানি, অপমান, হুমকি বা প্রতারণা করা যাবে না।</li>
        <li>রিভিউ সৎ হতে হবে — ভুয়া রিভিউ মুছে ফেলা হবে।</li>
    </ul>

    <h2>৫. পোস্ট আর কন্টেন্ট</h2>
    <ul>
        <li>Edu Feed আর টিউশন পোস্টে শুধু শিক্ষামূলক ও সঠিক তথ্য দেবেন। অ্যাডমিন রিভিউ করে প্রকাশ করেন, নিয়ম না মানলে মুছে ফেলতে পারেন।</li>
        <li>বিজ্ঞাপন, আপত্তিকর বা অন্যের কপিরাইট করা কন্টেন্ট দেওয়া যাবে না।</li>
    </ul>

    <h2>৬. অনলাইন কোর্স</h2>
    <ul>
        <li>কেনা কোর্স শুধু নিজে দেখবেন। কোর্সের ভিডিও ডাউনলোড, রেকর্ড বা অন্য কোথাও শেয়ার করা যাবে না।</li>
        <li>টিচার নিজের কোর্সের লেখা ও ভিডিওর দায়িত্ব নেবেন। নিয়ম না মানলে অ্যাডমিন কোর্স লুকাতে বা ব্লক করতে পারেন।</li>
    </ul>

    <h2>৭. অ্যাকাউন্ট বন্ধ</h2>
    <p>এই শর্ত না মানলে অ্যাডমিন সাময়িক বা স্থায়ীভাবে অ্যাকাউন্ট Suspend করতে পারেন।</p>

    <h2>৮. দায়সীমা</h2>
    <p>মাষ্টার মশাই টিউটর আর শিক্ষার্থীকে যুক্ত হতে সাহায্য করে। পড়ানোর মান, সময় আর পারিশ্রমিক নিয়ে চূড়ান্ত সিদ্ধান্ত টিউটর ও অভিভাবকের নিজেদের।</p>

    <h2>৯. পরিবর্তন</h2>
    <p>প্রয়োজনে এই শর্ত বদলানো হতে পারে। বদলের পরে সাইট ব্যবহার করলে নতুন শর্ত মেনে নেওয়া হয়েছে বলে ধরা হবে।</p>
<?php else: ?>
    <p>By using Mashter Moshai (mashtermoshai.com) you agree to the terms below. Please read them carefully.</p>

    <h2>1. Accounts</h2>
    <ul>
        <li>Create your account with your real name, email and mobile number. Accounts with fake information may be closed.</li>
        <li>One account is either a student account or a teacher account — not both.</li>
        <li>You are responsible for keeping your password secret and for everything done from your account.</li>
    </ul>

    <h2>2. Verification</h2>
    <ul>
        <li>To become a teacher, submit correct information, your Academic ID card and NID in the Become a Tutor form. You become a teacher only after the admin verifies and approves it.</li>
        <li>Students must submit correct information and an ID card in the Student Verification form.</li>
        <li>Wrong or someone else's documents will lead to rejection and, if needed, account closure.</li>
    </ul>

    <h2>3. Packages and payments</h2>
    <ul>
        <li>Send money to the number shown on the payment page and submit the correct Transaction ID and receipt image.</li>
        <li>A package starts when the admin verifies and accepts the payment; its benefits stop when it expires.</li>
        <li>Requests with wrong or fake payment details will be rejected.</li>
        <li>For refunds, please contact the Helpdesk.</li>
    </ul>

    <h2>4. Communication and conduct</h2>
    <ul>
        <li>Teachers and students communicate through the site's requests and messages.</li>
        <li>No harassment, insults, threats or fraud.</li>
        <li>Reviews must be honest — fake reviews will be removed.</li>
    </ul>

    <h2>5. Posts and content</h2>
    <ul>
        <li>Post only educational and accurate content on the Edu Feed and tuition posts. Admins review posts before publishing and may remove anything that breaks the rules.</li>
        <li>No advertisements, offensive content or content copyrighted by others.</li>
    </ul>

    <h2>6. Online courses</h2>
    <ul>
        <li>A purchased course is for your own use only. Course videos must not be downloaded, recorded or shared anywhere else.</li>
        <li>Teachers are responsible for their own course text and videos. Admins may hide or block a course that breaks the rules.</li>
    </ul>

    <h2>7. Account suspension</h2>
    <p>If these terms are not followed, the admin may suspend an account temporarily or permanently.</p>

    <h2>8. Limitation of responsibility</h2>
    <p>Mashter Moshai helps tutors and students connect. The final decisions about teaching quality, schedule and fees are between the tutor and the parent/student.</p>

    <h2>9. Changes</h2>
    <p>These terms may be updated when needed. Using the site after a change means you accept the new terms.</p>
<?php endif; ?>
    <p class="pol-links"><a href="/Home/Privacy"><?= t('গোপনীয়তা নীতি দেখুন', 'Read the Privacy Policy') ?> →</a></p>
</div>
