<?php
/** রিফান্ড নীতি (Refund Policy) — Privacy / Terms এর মতো একই ধরন; বাংলা প্রধান, English বাছলে ইংরেজি (একই নম্বর ক্রম) */
$ViewData['Title'] = t('রিফান্ড নীতি', 'Refund Policy');
$ViewData['FullTitle'] = t('রিফান্ড নীতি — কখন টাকা ফেরত পাবেন', 'Refund Policy — When You Get a Refund') . ' | ' . seo_brand();
$ViewData['Description'] = t('মাষ্টার মশাই এর রিফান্ড নীতি — কোন ক্ষেত্রে টাকা ফেরত দেওয়া হয়, কোন ক্ষেত্রে হয় না, আর কিভাবে রিফান্ডের আবেদন করবেন।', 'Mashter Moshai refund policy — when payments are refunded, when they are not, and how to request a refund.');
$en = site_lang() === 'en';
partial('Home/_PolicyStyles');
?>
<div class="pol" id="refund">
    <h1><?= t('রিফান্ড নীতি', 'Refund Policy') ?></h1>
    <p class="pol-up"><?= t('সর্বশেষ আপডেট: ২৫ নভেম্বর ২০২৬', 'Last updated: 25 November 2026') ?></p>

<?php if (!$en): ?>
    <p>মাষ্টার মশাই (mashtermoshai.com) এর রিফান্ড নীতি।</p>

    <h2>১. আমরা কী বিক্রি করি</h2>
    <p>মাষ্টার মশাই শুধু ডিজিটাল সেবা বিক্রি করে: টিচার ও শিক্ষার্থীর সাবস্ক্রিপশন প্যাকেজ, একবার Apply করার ফি, আর অনলাইন কোর্স। পেমেন্ট নিশ্চিত হওয়ার সাথে সাথেই সেবা চালু হয়ে যায়।</p>

    <h2>২. সাধারণ নিয়ম</h2>
    <p>যেহেতু সেবা সাথে সাথেই চালু হয়ে যায়, তাই একবার সেবা ব্যবহার করা হলে পেমেন্ট আর ফেরতযোগ্য নয়। "ব্যবহার করা" মানে চালু হওয়ার পর নিচের যেকোনো একটা হয়েছে:</p>
    <ul>
        <li>কোনো টিচারের যোগাযোগের তথ্য দেখা হয়েছে,</li>
        <li>কোনো Direct Request বা আবেদন (Apply) পাঠানো হয়েছে,</li>
        <li>"টিচার লাগবে" বা টিউশন পোস্ট প্রকাশ করা হয়েছে,</li>
        <li>লাইভ ক্লাস শুরু করা হয়েছে বা তাতে যোগ দেওয়া হয়েছে,</li>
        <li>কোর্সের কোনো লেসন খোলা হয়েছে।</li>
    </ul>

    <h2>৩. যখন আমরা রিফান্ড দিই (পুরো টাকা)</h2>
    <ul>
        <li><b>ক. একই পেমেন্ট দুইবার:</b> একই প্যাকেজের জন্য আপনার কাছ থেকে একবারের বেশি টাকা নেওয়া হয়েছে।</li>
        <li><b>খ. টাকা নেওয়া হয়েছে কিন্তু কিছু চালু হয়নি:</b> আপনার পেমেন্ট সফল হয়েছে, কিন্তু ২৪ ঘণ্টার মধ্যে প্যাকেজ বা কোর্স চালু হয়নি আর আমরা সমস্যাটা ঠিক করতে পারিনি।</li>
        <li><b>গ. টিচার যাচাই বাতিল:</b> আপনি টিচার প্যাকেজের টাকা দিয়েছেন আর মাষ্টার মশাই আপনার Become a Tutor আবেদন বাতিল করেছে।</li>
        <li><b>ঘ. ভুল করে ভুল প্যাকেজ কেনা:</b> পেমেন্টের ২৪ ঘণ্টার মধ্যে জানিয়েছেন আর প্যাকেজটা ২ নম্বর অংশ অনুযায়ী ব্যবহার করা হয়নি। চাইলে আমরা সঠিক প্যাকেজের সাথে বদলেও দিতে পারি।</li>
        <li><b>ঙ. সেবা বন্ধ ছিল:</b> আপনার প্যাকেজের মেয়াদের মধ্যে প্ল্যাটফর্ম টানা ৩ দিনের বেশি বন্ধ ছিল। যতদিন বন্ধ ছিল, ততদিন আমরা প্যাকেজের মেয়াদ বাড়িয়ে দিই; আপনি চাইলে বাকি অব্যবহৃত সময়ের টাকা হিসাব করে (আনুপাতিক হারে) ফেরত দিই।</li>
    </ul>

    <h2>৪. যখন আমরা রিফান্ড দিই না</h2>
    <ul>
        <li><b>ক.</b> মত বদলে গেলে, অথবা পছন্দমতো টিচার বা শিক্ষার্থী না পেলে। মাষ্টার মশাই শুধু দুই পক্ষকে যুক্ত করে; মিল হবেই এমন নিশ্চয়তা দেয় না।</li>
        <li><b>খ.</b> যে প্যাকেজ আপনি আর ব্যবহার করছেন না, তার বাকি থাকা দিন।</li>
        <li><b>গ.</b> শর্তাবলি ভাঙার জন্য অ্যাকাউন্ট সাসপেন্ড বা বন্ধ করা হলে (ভুয়া কাগজ, হয়রানি, কোর্সের ভিডিও শেয়ার করা ইত্যাদি)।</li>
        <li><b>ঘ.</b> টিচার আর অভিভাবকের মধ্যে সমস্যা (পড়ানোর মান, বেতন, উপস্থিতি)। এগুলো তাদের নিজেদের মধ্যে মীমাংসা করতে হবে।</li>
        <li><b>ঙ.</b> একবার Apply করার ফি (৳২০), আবেদন পাঠানো হয়ে গেলে।</li>
        <li><b>চ.</b> কুপন বা রেফারেল ব্যালেন্স দিয়ে পাওয়া ছাড় নগদ টাকায় ফেরত দেওয়া হয় না।</li>
    </ul>

    <h2>৫. কিভাবে রিফান্ডের আবেদন করবেন</h2>
    <p>পেমেন্টের <b>৭ দিনের মধ্যে</b> আবেদন পাঠান — <a href="/Helpdesk/Contact#refundReq">Helpdesk এর রিফান্ড আবেদন ফর্ম</a>, WhatsApp (<a href="https://wa.me/8801805843825" target="_blank" rel="noopener">01805843825</a>) অথবা ইমেইলে (<a href="mailto:promashtermoshai@gmail.com">promashtermoshai@gmail.com</a>)। সাথে দিন আপনার অ্যাকাউন্টের নাম, প্যাকেজের নাম, Transaction ID আর কারণ। আমরা ৩ কর্মদিবসের মধ্যে উত্তর দিই।</p>

    <h2>৬. রিফান্ডের টাকা কিভাবে দেওয়া হয়</h2>
    <p>অনুমোদিত রিফান্ডের টাকা ৭–১০ কর্মদিবসের মধ্যে আমাদের পেমেন্ট গেটওয়ের মাধ্যমে যে মাধ্যমে পেমেন্ট করেছিলেন সেখানেই ফেরত যায়। অন্য কোনো অ্যাকাউন্টে আমরা রিফান্ড দিই না।</p>

    <h2>৭. পরিবর্তন</h2>
    <p>আমরা এই নীতি আপডেট করতে পারি। উপরের তারিখটা সর্বশেষ সংস্করণ দেখায়; ওই তারিখের আগে করা পেমেন্টে পরিবর্তনগুলো প্রযোজ্য হবে না।</p>

    <p><b>প্রশ্ন থাকলে:</b> <a href="/Helpdesk">Helpdesk</a>, 01805843825, promashtermoshai@gmail.com</p>
<?php else: ?>
    <p>Refund Policy of Mashter Moshai (mashtermoshai.com).</p>

    <h2>1. What we sell</h2>
    <p>Mashter Moshai sells digital services only: tutor and student subscription packages, single-apply fees, and online courses. Access starts as soon as the payment is confirmed.</p>

    <h2>2. General rule</h2>
    <p>Because access is delivered immediately, payments are non-refundable once the service has been used. "Used" means any of the following has happened after activation:</p>
    <ul>
        <li>a tutor's contact details were viewed,</li>
        <li>a Direct Request or application was sent,</li>
        <li>a Teacher Wanted or tuition post was published,</li>
        <li>a live class was started or joined,</li>
        <li>a course lesson was opened.</li>
    </ul>

    <h2>3. When we do refund (full amount)</h2>
    <ul>
        <li><b>a. Duplicate payment:</b> you were charged more than once for the same package.</li>
        <li><b>b. Payment taken but nothing activated:</b> your payment succeeded but the package or course was not activated within 24 hours and we could not fix it.</li>
        <li><b>c. Tutor verification refused:</b> you paid for a tutor package and your Become-a-Tutor application was rejected by Mashter Moshai.</li>
        <li><b>d. Wrong package bought by mistake:</b> reported within 24 hours of payment and the package is unused as defined in section 2. We can also swap it for the right one.</li>
        <li><b>e. Service unavailable:</b> the platform was down for more than 3 consecutive days during your package period. We extend the package by the lost days; if you prefer, we refund the unused portion on a pro-rata basis.</li>
    </ul>

    <h2>4. When we do not refund</h2>
    <ul>
        <li><b>a.</b> Change of mind, or not finding a suitable tutor or student. Mashter Moshai only connects both sides; it does not guarantee a match.</li>
        <li><b>b.</b> Unused days left on a package you stopped using.</li>
        <li><b>c.</b> Account suspended or closed for breaking the Terms (fake documents, harassment, sharing course videos, etc.).</li>
        <li><b>d.</b> Problems between tutor and guardian (lesson quality, fees, attendance). These are settled between them.</li>
        <li><b>e.</b> Single-apply fees (৳20) once the application has been sent.</li>
        <li><b>f.</b> Discounts given through coupons or referral balance are not paid out in cash.</li>
    </ul>

    <h2>5. How to request a refund</h2>
    <p>Send a request within <b>7 days</b> of the payment through the <a href="/Helpdesk/Contact#refundReq">Helpdesk refund request form</a>, by WhatsApp (<a href="https://wa.me/8801805843825" target="_blank" rel="noopener">01805843825</a>) or by email (<a href="mailto:promashtermoshai@gmail.com">promashtermoshai@gmail.com</a>) with your account name, the package name, the Transaction ID and the reason. We reply within 3 working days.</p>

    <h2>6. How refunds are paid</h2>
    <p>Approved refunds go back to the original payment method through our payment gateway within 7–10 working days. We do not refund to a different account.</p>

    <h2>7. Changes</h2>
    <p>We may update this policy. The date at the top shows the latest version; changes do not apply to payments made before that date.</p>

    <p><b>Questions:</b> <a href="/Helpdesk">Helpdesk</a>, 01805843825, promashtermoshai@gmail.com</p>
<?php endif; ?>
    <p class="pol-links"><a href="/Home/Terms"><?= t('শর্তাবলি দেখুন', 'Read the Terms of Service') ?> →</a> &nbsp;·&nbsp; <a href="/Home/Privacy"><?= t('গোপনীয়তা নীতি দেখুন', 'Read the Privacy Policy') ?> →</a></p>
</div>
