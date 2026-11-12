<?php
/**
 * Admin → User Role: নাম / ইউজারনেম / ইমেইল দিয়ে খুঁজে রোল বদল — স্টুডেন্ট / টিচার / অ্যাডমিন / Instructor (শুধু Super Admin)।
 */
$ViewData['Title'] = 'User Role';
$list = is_array($Model) ? $Model : [];
$q = (string)$ViewBag->Q; $inst = (array)$ViewBag->Instructors;
$roles = ['User' => 'ইউজার', 'Student' => 'স্টুডেন্ট', 'Teacher' => 'টিচার', 'Instructor' => 'Instructor', 'Admin' => 'অ্যাডমিন'];
$tagCls = ['Teacher' => 't', 'Admin' => 'a', 'Instructor' => 'i', 'Student' => 's', 'User' => 'u'];
$me = (int)current_user_id();
$rowHtml = function (Obj $u) use ($roles, $tagCls, $me) {
    $lock = $u->Id === $me ? 'নিজের রোল বদলানো যায় না' : ($u->IsSuper ? 'Super Admin — বদলানো যায় না' : '');
    ob_start(); ?>
    <tr>
        <td><div class="ur-u"><span class="ur-av"><?= e(initials($u->Name, 'U')) ?><?php if (!is_blank($u->ProfileImageLink)): ?><img src="<?= e($u->ProfileImageLink) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
            <div><a href="/Profile/Show/<?= $u->Id ?>" target="_blank"><?= e($u->Name) ?></a><small>@<?= e($u->UserName) ?> · <?= e((string)$u->Email) ?><?= !is_blank($u->MobileNumber) ? ' · ' . e($u->MobileNumber) : '' ?></small></div></div></td>
        <td><span class="ur-tag <?= $tagCls[$u->RoleLabel] ?? 'u' ?>"><?= e($u->IsSuper ? 'Super Admin' : $u->RoleLabel) ?></span></td>
        <td><?php if ($lock): ?><small style="color:#94A3B8;"><?= $lock ?></small><?php else: ?>
            <div class="ur-ch" data-uid="<?= $u->Id ?>">
                <select><?php foreach ($roles as $k => $l): ?><option value="<?= $k ?>"<?= $u->RoleKey === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
                <button type="button" data-save><i class="fas fa-check"></i> বদলান</button>
                <div class="ur-msg"></div>
            </div><?php endif; ?></td>
    </tr>
<?php return ob_get_clean(); };
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .ur h2 { color: #08224A; margin: 0; font-size: 24px; font-weight: 800; }
    .ur .sub { color: #64748B; margin: 4px 0 16px; font-size: 14px; max-width: 820px; }
    .ur-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 16px; margin-bottom: 14px; }
    .ur-card h3 { margin: 0 0 10px; font-size: 15.5px; font-weight: 800; color: #08224A; }
    .ur-search { display: flex; gap: 8px; max-width: 560px; }
    .ur-search input { flex: 1; height: 46px; border: 1.5px solid #DCE7F0; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 15px; }
    .ur-search button { height: 46px; padding: 0 18px; border-radius: 12px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; }
    .ur-roles { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 14px; }
    .ur-roles div { border: 1px solid #EEF2F7; border-radius: 12px; padding: 10px 12px; font-size: 13px; color: #475569; background: #FAFCFE; }
    .ur-roles b { display: block; color: #08224A; font-size: 14px; margin-bottom: 2px; }
    .ur-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ur-tbl th { text-align: left; font-size: 11.5px; color: #64748B; padding: 8px; border-bottom: 1px solid #E2E8F0; text-transform: uppercase; }
    .ur-tbl td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
    .ur-u { display: flex; gap: 10px; align-items: center; min-width: 220px; }
    .ur-av { width: 38px; height: 38px; border-radius: 50%; background: #E0F2FE; color: #0369A1; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0; }
    .ur-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ur-u a { color: #08224A; font-weight: 800; text-decoration: none; } .ur-u a:hover { text-decoration: underline; }
    .ur-u small { display: block; color: #64748B; font-size: 12px; word-break: break-all; }
    .ur-tag { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .ur-tag.t { background: #DBEAFE; color: #1D4ED8; } .ur-tag.a { background: #FEE2E2; color: #991B1B; } .ur-tag.i { background: #F3E8FF; color: #7E22CE; } .ur-tag.s { background: #DCFCE7; color: #166534; } .ur-tag.u { background: #F1F5F9; color: #475569; }
    .ur-ch { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; min-width: 240px; }
    .ur-ch select { height: 36px; border: 1.5px solid #DCE7F0; border-radius: 9px; padding: 0 8px; font-family: inherit; font-size: 13.5px; background: #fff; }
    .ur-ch button { height: 36px; padding: 0 14px; border: none; border-radius: 9px; background: #7C3AED; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; }
    .ur-msg { flex-basis: 100%; font-size: 12px; font-weight: 700; }
    .ur-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content ur">
    <?= csrf_field() ?>
    <h2><i class="fas fa-user-gear" style="color:#7C3AED;"></i> User Role</h2>
    <p class="sub">নাম, ইউজারনেম, ইমেইল, মোবাইল বা ID লিখে খুঁজুন, তারপর রোল বাছাই করে "বদলান" চাপুন। ইউজার নোটিফিকেশন পাবে।</p>

    <div class="ur-card">
        <form class="ur-search" method="get" action="/Admin/UserRoles">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="যেমন: Rita Akter, ritamam, 017..." autofocus />
            <button type="submit"><i class="fas fa-magnifying-glass"></i> খুঁজুন</button>
        </form>
        <div class="ur-roles">
            <div><b><i class="fas fa-user" style="color:#64748B;"></i> ইউজার</b>সাধারণ অ্যাকাউন্ট — এখনো Student Verification হয়নি।</div>
            <div><b><i class="fas fa-user-graduate" style="color:#16A34A;"></i> স্টুডেন্ট</b>Student Verification Approve হলে নিজে থেকেই স্টুডেন্ট হয়। এখান থেকে দিলে ফর্ম ছাড়াই স্টুডেন্ট হয়ে যাবে।</div>
            <div><b><i class="fas fa-chalkboard-user" style="color:#2563EB;"></i> টিচার</b>Teacher Dashboard, Tutor List, টিউশনে আবেদন — সাথে সাথে চালু।</div>
            <div><b><i class="fas fa-person-chalkboard" style="color:#9333EA;"></i> Instructor</b>Helpdesk আর Edu Feed এ পোস্ট — Edu Feed পোস্ট Approve ছাড়াই সাথে সাথে প্রকাশ, নামের পাশে "Instructor" ব্যাজ।</div>
            <div><b><i class="fas fa-user-shield" style="color:#DC2626;"></i> অ্যাডমিন</b>আবার লগইন করলে অ্যাডমিন প্যানেল। কোন পেজ দেখবে Admins পেজ থেকে ঠিক করুন।</div>
        </div>
    </div>

    <?php if ($q !== ''): ?>
    <div class="ur-card">
        <h3><i class="fas fa-magnifying-glass"></i> "<?= e($q) ?>" — <?= count($list) ?> জন পাওয়া গেছে</h3>
        <?php if (!$list): ?><div class="ur-empty">কাউকে পাওয়া যায়নি — অন্যভাবে লিখে দেখুন।</div><?php else: ?>
        <div style="overflow-x:auto;"><table class="ur-tbl"><tr><th>ইউজার</th><th>এখনকার রোল</th><th>রোল বদলান</th></tr>
            <?php foreach ($list as $u) echo $rowHtml($u); ?>
        </table></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="ur-card">
        <h3><i class="fas fa-person-chalkboard" style="color:#9333EA;"></i> Instructor তালিকা (<?= count($inst) ?>)</h3>
        <?php if (!$inst): ?><div class="ur-empty">এখনো কাউকে Instructor করা হয়নি।</div><?php else: ?>
        <div style="overflow-x:auto;"><table class="ur-tbl"><tr><th>ইউজার</th><th>এখনকার রোল</th><th>রোল বদলান</th></tr>
            <?php foreach ($inst as $u) echo $rowHtml($u); ?>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<?php section_start('Scripts'); ?>
<script>
document.addEventListener('click', async function (e) {
    var b = e.target.closest('[data-save]'); if (!b) return;
    var box = b.closest('.ur-ch'), sel = box.querySelector('select'), msg = box.querySelector('.ur-msg'), lb = sel.options[sel.selectedIndex].text;
    if (!confirm('এই ইউজারের রোল "' + lb + '" করবেন?' + (sel.value === 'Admin' ? '\n\nঅ্যাডমিন বানালে সে অ্যাডমিন প্যানেলে ঢুকতে পারবে (কোন পেজ দেখবে Admins পেজ থেকে ঠিক করবেন)।' : ''))) return;
    var fd = new FormData(); fd.append('userId', box.dataset.uid); fd.append('role', sel.value);
    fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
    b.disabled = true; msg.style.color = '#64748B'; msg.textContent = '...';
    try {
        var r = await (await fetch('/Admin/ChangeUserRole', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
        msg.style.color = r.success ? '#15803D' : '#DC2626'; msg.textContent = r.message || '';
        if (r.success) setTimeout(function () { location.reload(); }, 1300); else b.disabled = false;
    } catch (x) { msg.style.color = '#DC2626'; msg.textContent = 'সমস্যা হয়েছে, আবার চেষ্টা করুন।'; b.disabled = false; }
});
</script>
<?php section_end(); ?>
