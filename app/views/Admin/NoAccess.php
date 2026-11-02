<?php
/** অ্যাডমিনের এই অংশে অনুমতি নেই */
$ViewData['Title'] = 'অনুমতি নেই';
$rule = (string)($ViewBag->Rule ?? '');
$perm = admin_permissions()[$rule][0] ?? null;
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; }
    .na-box { background: #fff; border-radius: 20px; padding: 40px 30px; text-align: center; max-width: 480px; box-shadow: 0 10px 30px rgba(8,34,74,.08); }
    .na-box .ic { width: 76px; height: 76px; border-radius: 50%; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 18px; }
    .na-box h2 { color: #08224A; font-size: 22px; font-weight: 800; margin: 0 0 8px; }
    .na-box p { color: #64748B; font-size: 14.5px; line-height: 1.7; margin: 0 0 22px; }
    .na-box a { display: inline-flex; gap: 8px; align-items: center; padding: 11px 22px; border-radius: 12px; background: #08224A; color: #fff; text-decoration: none; font-weight: 700; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content">
    <div class="na-box">
        <div class="ic"><i class="fas fa-lock"></i></div>
        <h2>এই অংশে আপনার অনুমতি নেই</h2>
        <p><?= $rule === 'super' ? 'এই পেজটা শুধু <b>Super Admin</b> দেখতে পারেন।' : 'আপনাকে <b>' . e($perm ?? 'এই অংশের') . '</b> এর অনুমতি দেওয়া হয়নি।' ?><br>দরকার হলে Super Admin এর সাথে যোগাযোগ করুন।</p>
        <a href="/Admin/Dashboard"><i class="fas fa-house"></i> Dashboard এ ফিরে যান</a>
    </div>
</div>
