# Smart Tutor Hub — PHP + MySQL সংস্করণ

আগের ASP.NET Core (C#) + PostgreSQL প্রজেক্টটি এখন **সাধারণ PHP 8 + MySQL** এ রূপান্তর করা হয়েছে।
কোনো Composer বা framework লাগবে না — cPanel (Hostnil) শেয়ার্ড হোস্টিং এ সরাসরি চলবে।
সব পেজের URL, ডিজাইন ও ফিচার আগের মতোই আছে (`/Admin/Dashboard`, `/Tuition/Circular`, `/FindTutor` …)।

---

## ফোল্ডার কাঠামো

```
public_html/
├── index.php            ← সব রিকোয়েস্ট এখানে আসে (Front Controller / Router)
├── config.php           ← ⚠️ ডাটাবেসের তথ্য এখানে বসাতে হবে
├── .htaccess            ← সুন্দর URL এর জন্য (অবশ্যই আপলোড করবেন)
├── .user.ini            ← বড় ফাইল আপলোডের PHP সেটিংস
├── database.sql         ← MySQL টেবিল তৈরি করার ফাইল (ইমপোর্ট করে পরে মুছে দিতে পারেন)
├── uploads/             ← ইউজারদের আপলোড করা ছবি/ভিডিও
└── app/
    ├── core/            ← helpers, View, base Controller
    ├── models/          ← Db.php (আগের DatabaseHelper.cs), SiteSettings.php
    ├── controllers/     ← Account, Admin, FindTutor, Helpdesk, Home, Notice,
    │                      Notification, Profile, Teacher, Tuition, User
    └── views/           ← সব পেজ (আগের .cshtml → এখন .php)
```

---

## cPanel (Hostnil) এ ইনস্টল করার ধাপ

### ধাপ ১ — PHP ভার্সন ঠিক করুন
cPanel → **Select PHP Version** (বা *MultiPHP Manager*) → **PHP 8.1 বা তার বেশি** সিলেক্ট করুন।
Extensions এ `pdo_mysql`, `mbstring`, `fileinfo` টিক দেওয়া আছে কিনা দেখে নিন।

### ধাপ ২ — ডাটাবেস তৈরি করুন
1. cPanel → **MySQL® Databases**
2. *Create New Database* → নাম দিন (যেমন `smarttutordb`) → cPanel নিজে থেকে নামের আগে আপনার ইউজারনেম যোগ করবে, যেমন `mashterm_smarttutordb`
3. নিচে *MySQL Users* → নতুন ইউজার ও শক্ত পাসওয়ার্ড তৈরি করুন
4. *Add User To Database* → ইউজার ও ডাটাবেস সিলেক্ট করে **ALL PRIVILEGES** দিন

### ধাপ ৩ — টেবিল তৈরি করুন
cPanel → **phpMyAdmin** → বাম পাশে আপনার ডাটাবেস সিলেক্ট করুন → উপরে **Import** →
`database.sql` ফাইল সিলেক্ট করে **Go/Import** চাপুন। সব টেবিল + ডিফল্ট সেটিংস + একটি অ্যাডমিন অ্যাকাউন্ট তৈরি হবে।

### ধাপ ৪ — ফাইল আপলোড করুন
1. cPanel → **File Manager** → `public_html` ফোল্ডারে যান (পুরোনো ডিফল্ট ফাইল থাকলে মুছে দিন)
2. **Upload** → `smart-tutor-hub-php.zip` আপলোড করুন
3. zip এর উপর রাইট ক্লিক → **Extract** → ফাইলগুলো যেন সরাসরি `public_html` এর ভিতরে থাকে
   (অর্থাৎ `public_html/index.php` — কোনো সাব-ফোল্ডারের ভিতরে নয়)
4. File Manager এর *Settings* থেকে **Show Hidden Files** চালু করে নিশ্চিত হোন যে `.htaccess` আছে

### ধাপ ৫ — config.php এডিট করুন
File Manager এ `config.php` → **Edit** → ধাপ ২ এর তথ্য বসান:
```php
'name' => 'mashterm_smarttutordb',
'user' => 'mashterm_dbuser',
'pass' => 'আপনার_ডাটাবেস_পাসওয়ার্ড',
```

### ধাপ ৬ — uploads ফোল্ডারের পারমিশন
`uploads` ফোল্ডার ও এর ভিতরের সব ফোল্ডারের Permission **755** রাখুন (সাধারণত ডিফল্টই থাকে)।

### ধাপ ৭ — সাইট চালু ✅
ব্রাউজারে `https://mashtermoshai.com` খুলুন।
অ্যাডমিন লগইন: **Username:** `admin`  **Password:** `Admin@123`
👉 লগইন করেই Admin Dashboard থেকে admin ইউজারের পাসওয়ার্ড বদলে ফেলুন।

### ধাপ ৮ (ঐচ্ছিক) — HTTPS বাধ্যতামূলক করা
cPanel → **SSL/TLS Status** থেকে ফ্রি SSL চালু করুন, তারপর `.htaccess` এ এই দুই লাইনের শুরুর `#` মুছে দিন:
```
# RewriteCond %{HTTPS} off
# RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## সমস্যা হলে

| সমস্যা | সমাধান |
|---|---|
| হোম পেজ খোলে কিন্তু অন্য সব পেজে **404** | `.htaccess` আপলোড হয়নি বা hidden ছিল — ধাপ ৪.৪ দেখুন |
| **500 Internal Server Error** / "An unexpected error occurred" | `config.php` এ সাময়িকভাবে `'debug' => true` করে আসল error দেখুন, ঠিক করে আবার `false` করুন। সাধারণত ডাটাবেসের নাম/ইউজার/পাসওয়ার্ড ভুল থাকে |
| "Security token expired" | পেজটি রিফ্রেশ করে আবার চেষ্টা করুন (সেশন ৬০ মিনিট পর শেষ হয়) |
| বড় ভিডিও (Helpdesk) আপলোড হয় না | cPanel → Select PHP Version → Options থেকে `upload_max_filesize` = 64M, `post_max_size` = 70M |
| ছবি আপলোড হয় না | `uploads` ফোল্ডারের পারমিশন 755 কিনা দেখুন |

---

## আগের সংস্করণ থেকে যা বদলেছে / উন্নত হয়েছে

- **পাসওয়ার্ড এখন নিরাপদ hash (bcrypt) করে রাখা হয়।** পুরোনো plain-text পাসওয়ার্ডওয়ালা ইউজাররাও
  আগের মতোই লগইন করতে পারবে — প্রথম লগইনেই তাদের পাসওয়ার্ড স্বয়ংক্রিয়ভাবে hash হয়ে যাবে।
- সব POST রিকোয়েস্টে CSRF (anti-forgery) টোকেন যাচাই হয় — আগের `[ValidateAntiForgeryToken]` এর মতো।
- `uploads/` ফোল্ডারে কোনো PHP/স্ক্রিপ্ট ফাইল চালানো যাবে না (হ্যাকিং প্রতিরোধ)। প্রোফাইল ছবিতেও এখন শুধু ছবি ফাইল নেওয়া হয়।
- প্রোফাইল আপডেট এখন লগইন করা ইউজারের নিজের অ্যাকাউন্টেই হয় (অন্য কারো Id পাঠিয়ে বদলানো যায় না)।
- `/Admin/TutorRequests` পেজটি আবার চালু করা হয়েছে (অ্যাডমিন নোটিফিকেশনগুলো এই পেজেই নিয়ে যায়)।
- সময় বাংলাদেশ সময় (Asia/Dhaka) অনুযায়ী সেভ ও দেখানো হয়।

## পুরোনো PostgreSQL ডাটা নিয়ে আসা (দরকার হলে)

টেবিল ও কলামের নাম হুবহু আগের মতো রাখা হয়েছে, তাই ডাটা সরাসরি নেওয়া যায়:
1. pgAdmin এ প্রতিটি টেবিলে রাইট ক্লিক → **Import/Export** → Export → Format **CSV**, Header **Yes**
2. phpMyAdmin → টেবিল সিলেক্ট → **Import** → CSV ফাইল → *"The first line of the file contains the table column names"* টিক দিন
3. `Users` → `TeacherRequests` → `TuitionPosts` → `TuitionApplications` → … এই ক্রমে ইমপোর্ট করুন
   (bool কলামগুলো CSV তে `t`/`f` থাকলে `1`/`0` করে নিন)

---

## লোকাল কম্পিউটারে চালাতে চাইলে (ঐচ্ছিক)
XAMPP/Laragon এ `htdocs` এর ভিতরে ফাইলগুলো রাখুন (ডোমেইনের root এ চলতে হবে, যেমন virtual host বা
`php -S localhost:8000 index.php`), MySQL এ `database.sql` ইমপোর্ট করুন এবং `config.php` আপডেট করুন।
