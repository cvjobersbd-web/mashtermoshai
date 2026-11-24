<?php
$isStu = ($ViewBag->Mode ?? '') === 'student';
$ViewData['Title'] = $isStu ? 'Student Overview' : 'User Overview';
$ovBase = $isStu ? '/Admin/StudentOverview' : '/Admin/UserOverview';

$allUsers = $ViewBag->AllUsers ?? [];
$selected = $ViewBag->SelectedUser;

$stats = $ViewBag->UserStats ?? obj(['TotalPosts' => 0, 'TotalLikes' => 0, 'TotalComments' => 0, 'TotalTeachersConnected' => 0]);
$postStats = $ViewBag->UserPostStats ?? [];
$teachers = $ViewBag->TeachersEngaged ?? [];
$apps = $ViewBag->UserTuitionApps ?? [];
?>

<style>
    /* ===== Main Content Layout ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== User List Sidebar ===== */
    .user-list-card {
        background: white;
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.04);
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        height: fit-content;
        position: sticky;
        top: 100px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }

        .user-list-card::-webkit-scrollbar {
            width: 5px;
        }

        .user-list-card::-webkit-scrollbar-thumb {
            background: #B9D6E6;
            border-radius: 10px;
        }

    .user-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

        .user-item:hover {
            background: #F5FBFF;
        }

        .user-item.active {
            background: #CFF2FC;
            border-left: 4px solid #02B8F5;
        }

    .u-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        background: #02B8F5;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
        overflow: hidden;
    }

        .u-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

    .u-info .u-name {
        font-size: 14px;
        font-weight: 700;
        color: #08224A;
        line-height: 1.3;
    }

    .u-info .u-email {
        font-size: 11.5px;
        color: #7A8CA0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .u-role-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 2px;
    }

        .u-role-badge.teacher {
            background: #d1ecf1;
            color: #0c6b7c;
        }

        .u-role-badge.user {
            background: #eef2f6;
            color: #64748B;
        }

    /* ===== Dashboard Stats ===== */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        border-left: 5px solid #ccc;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        transition: transform 0.2s;
    }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card.posts {
            border-left-color: #3498db;
        }

        .stat-card.likes {
            border-left-color: #e74c3c;
        }

        .stat-card.comments {
            border-left-color: #f39c12;
        }

        .stat-card.teachers {
            border-left-color: #2ecc71;
        }

        .stat-card .num {
            font-size: 28px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.1;
            font-family: var(--font-heading);
        }

        .stat-card .lbl {
            font-size: 12px;
            color: #64748B;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

    /* ===== Detail Cards ===== */
    .detail-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .detail-title {
        font-size: 16px;
        font-weight: 700;
        color: #08224A;
        margin-bottom: 16px;
        border-bottom: 2px solid #F5FBFF;
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

        .detail-title i {
            font-size: 15px;
        }

    /* ===== Empty State ===== */
    .empty-placeholder {
        text-align: center;
        padding: 60px 20px;
        color: #7A8CA0;
        background: white;
        border-radius: 16px;
        border: 2px dashed #DCECF5;
    }

        .empty-placeholder i {
            font-size: 48px;
            color: #B9D6E6;
            margin-bottom: 16px;
            display: block;
        }

    /* ===== Scrollable List Areas ===== */
    .scroll-area {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 5px;
    }

        .scroll-area::-webkit-scrollbar {
            width: 4px;
        }

        .scroll-area::-webkit-scrollbar-thumb {
            background: #DCECF5;
            border-radius: 4px;
        }

    .list-item {
        background: #F5FBFF;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 3px solid #02B8F5;
    }

        .list-item.post {
            border-left-color: #3498db;
        }

        .list-item.teacher-item {
            border-left-color: #2ecc71;
        }

        .list-item.app {
            border-left-color: #f39c12;
        }

    /* ===== Badge helpers ===== */
    .badge-teacher-yes {
        background: #d4edda;
        color: #155724;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }

    .badge-teacher-no {
        background: #eef2f6;
        color: #64748B;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 15px;
        }

        .stat-grid {
            grid-template-columns: 1fr 1fr;
        }

        .user-list-card {
            position: static;
            margin-bottom: 20px;
            max-height: 400px;
        }
    }

    @media (max-width: 576px) {
        .stat-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ===== Admin Sidebar ===== -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ===== Main Content ===== -->
<div class="admin-main-content">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:#08224A; margin:0; font-weight:800;">
                <i class="fas <?= $isStu ? 'fa-user-graduate' : 'fa-users-viewfinder' ?> me-2"></i><?= $isStu ? 'Student Overview' : 'User Overview' ?>
            </h2>
            <p style="color:#64748B; margin:6px 0 0 0; font-size:14px;">
                <?= $isStu ? 'শুধু ভেরিফাইড শিক্ষার্থীরা — প্যাকেজ, টিচারকে রিকোয়েস্ট, কোর্স, পেমেন্ট আর পোস্টের অ্যাক্টিভিটি' : 'ইউজারদের পোস্ট, টিচার রেসপন্স এবং অ্যাক্টিভিটি ট্র্যাক করুন' ?>
            </p>
        </div>
    </div>

    <?php if (!$isStu && isset($ViewBag->NewUsers)):
        $NU = $ViewBag->NewUsers; [$nf, $nt, $nr, $nm] = $ViewBag->NewF; $td = date('Y-m-d');
        $nq = ['nf' => $nf, 'nt' => $nt] + ($nr !== '' ? ['nr' => $nr] : []) + ($nm ? ['nm' => '1'] : []);
        $nQuick = ['আজ' => [$td, $td], 'গতকাল' => [date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day'))], 'গত ৭ দিন' => [date('Y-m-d', strtotime('-6 days')), $td], 'গত ৩০ দিন' => [date('Y-m-d', strtotime('-29 days')), $td], 'এই মাস' => [date('Y-m-01'), $td]];
        $withMob = count(array_filter($NU, fn($u) => trim((string)($u->MobileNumber ?? '')) !== '')); ?>
    <details class="nu" id="newUsers"<?= !empty($ViewBag->NewOpen) ? ' open' : '' ?>>
        <summary><i class="fas fa-mobile-screen-button"></i> নতুন ইউজারদের মোবাইল নম্বর <span class="nu-pill"><?= count($NU) ?> জন · মোবাইল আছে <?= $withMob ?></span><small><?= e(date('d M', strtotime($nf)) . ($nf !== $nt ? ' – ' . date('d M Y', strtotime($nt)) : ', ' . date('Y', strtotime($nt)))) ?></small><i class="fas fa-chevron-down nu-ch"></i></summary>
        <form class="nu-flt" method="get" action="/Admin/UserOverview#newUsers">
            <label>শুরু <input type="date" name="nf" value="<?= e($nf) ?>" max="<?= $td ?>" /></label>
            <label>শেষ <input type="date" name="nt" value="<?= e($nt) ?>" max="<?= $td ?>" /></label>
            <select name="nr" aria-label="কারা"><option value="">সবাই</option><option value="teacher"<?= $nr === 'teacher' ? ' selected' : '' ?>>শুধু টিচার</option><option value="student"<?= $nr === 'student' ? ' selected' : '' ?>>শুধু শিক্ষার্থী</option></select>
            <label class="nu-ck"><input type="checkbox" name="nm" value="1"<?= $nm ? ' checked' : '' ?> /> শুধু যাদের মোবাইল আছে</label>
            <button type="submit" class="nu-go"><i class="fas fa-magnifying-glass"></i> দেখুন</button>
            <a class="nu-xl" href="/Admin/NewUsersExcel?<?= e(http_build_query($nq)) ?>"><i class="fas fa-file-excel"></i> Excel ডাউনলোড</a>
            <div class="nu-q"><?php foreach ($nQuick as $ql => [$qa, $qb]): ?><a class="<?= $nf === $qa && $nt === $qb ? 'on' : '' ?>" href="/Admin/UserOverview?<?= e(http_build_query(['nf' => $qa, 'nt' => $qb] + ($nr !== '' ? ['nr' => $nr] : []) + ($nm ? ['nm' => '1'] : []))) ?>#newUsers"><?= $ql ?></a><?php endforeach; ?></div>
        </form>
        <?php if (!$NU): ?>
            <div class="nu-empty">এই সময়ে কোনো নতুন ইউজার নেই।</div>
        <?php else: ?>
        <div class="nu-tw"><table class="nu-tbl">
            <thead><tr><th>#</th><th>সাইনআপ</th><th>নাম</th><th>মোবাইল নম্বর</th><th>ইমেইল</th><th>ধরন</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($NU as $i => $u): $mob = trim((string)($u->MobileNumber ?? '')); ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e(date('d M Y', strtotime((string)$u->CreatedAt))) ?><small><?= e(date('h:i A', strtotime((string)$u->CreatedAt))) ?></small></td>
                    <td><b><?= e((string)($u->FullName ?: $u->UserName)) ?></b><small>@<?= e((string)$u->UserName) ?></small></td>
                    <td><?php if ($mob !== ''): ?><a class="nu-mob" href="tel:<?= e($mob) ?>"><?= e($mob) ?></a> <button type="button" class="nu-cp" data-cp="<?= e($mob) ?>" title="কপি"><i class="far fa-copy"></i></button>
                        <a class="nu-wa" href="https://wa.me/<?= e('88' . ltrim(preg_replace('/\D/', '', $mob), '8')) ?>" target="_blank" rel="noopener" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <?php else: ?><span class="nu-none">দেয়নি</span><?php endif; ?></td>
                    <td><?= e((string)$u->Email) ?><?= !empty($u->EmailVerified) ? ' <i class="fas fa-circle-check" style="color:#16A34A;" title="ইমেইল ভেরিফাই করা"></i>' : '' ?></td>
                    <td><span class="u-role-badge <?= $u->Role === 'Teacher' ? 'teacher' : 'user' ?>"><?= $u->Role === 'Teacher' ? 'Teacher' : 'Student' ?></span></td>
                    <td><a class="nu-see" href="/Admin/UserOverview?id=<?= (int)$u->Id ?>">দেখুন</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
        <style>
            .nu { background: #fff; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,.05); margin-bottom: 20px; border: 1.5px solid #BAE6FD; }
            .nu summary { list-style: none; cursor: pointer; padding: 14px 18px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-weight: 800; color: #08224A; font-size: 16px; }
            .nu summary::-webkit-details-marker { display: none; } .nu summary > i:first-child { color: #0284C7; }
            .nu summary small { color: #64748B; font-weight: 600; font-size: 12.5px; } .nu-ch { margin-left: auto; color: #94A3B8; transition: transform .2s; } .nu[open] .nu-ch { transform: rotate(180deg); }
            .nu-pill { font-size: 12px; background: #E0F2FE; color: #075985; border-radius: 999px; padding: 3px 10px; }
            .nu-flt { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; padding: 0 18px 12px; }
            .nu-flt label { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #334155; margin: 0; }
            .nu-flt input[type=date], .nu-flt select { height: 36px; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 0 8px; font-size: 13px; font-family: inherit; }
            .nu-ck input { width: 16px; height: 16px; }
            .nu-go, .nu-xl { height: 36px; border: none; border-radius: 8px; color: #fff; font-weight: 700; padding: 0 14px; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; cursor: pointer; }
            .nu-go { background: #0284C7; } .nu-xl { background: #16A34A; margin-left: auto; } .nu-xl:hover { background: #15803D; color: #fff; }
            .nu-q { flex-basis: 100%; display: flex; gap: 6px; flex-wrap: wrap; }
            .nu-q a { font-size: 12.5px; font-weight: 700; padding: 4px 11px; border-radius: 999px; background: #EFF6FF; color: #1D4ED8; text-decoration: none; } .nu-q a.on { background: #1D4ED8; color: #fff; }
            .nu-tw { overflow: auto; max-height: 460px; border-top: 1px solid #E5ECF3; }
            .nu-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 760px; }
            .nu-tbl th { position: sticky; top: 0; background: #F8FAFC; text-align: left; font-size: 11.5px; color: #64748B; padding: 9px 12px; text-transform: uppercase; }
            .nu-tbl td { padding: 9px 12px; border-top: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
            .nu-tbl small { display: block; color: #94A3B8; font-size: 11.5px; }
            .nu-mob { font-weight: 800; color: #0F172A; text-decoration: none; font-size: 14px; letter-spacing: .3px; }
            .nu-cp { border: 1px solid #E2E8F0; background: #fff; border-radius: 6px; width: 26px; height: 26px; color: #475569; cursor: pointer; } .nu-cp.ok { color: #16A34A; border-color: #16A34A; }
            .nu-wa { color: #25D366; font-size: 18px; margin-left: 4px; vertical-align: middle; }
            .nu-none { color: #CBD5E1; font-size: 12.5px; } .nu-see { font-weight: 700; font-size: 12.5px; color: #0284C7; text-decoration: none; }
            .nu-empty { text-align: center; color: #94A3B8; padding: 18px; border-top: 1px solid #E5ECF3; }
            @media (max-width: 640px) { .nu-xl { margin-left: 0; } }
        </style>
        <script>
        document.addEventListener('click', function (e) {
            var b = e.target.closest('.nu-cp'); if (!b) return;
            var v = b.getAttribute('data-cp'), done = function () { b.classList.add('ok'); setTimeout(function () { b.classList.remove('ok'); }, 1200); };
            if (navigator.clipboard) navigator.clipboard.writeText(v).then(done, done); else { var t = document.createElement('textarea'); t.value = v; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); } catch (x) { } t.remove(); done(); }
        });
        </script>
    </details>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ===== Left Sidebar: User List ===== -->
        <div class="col-lg-3">
            <div class="user-list-card">
                <div style="padding: 16px 20px; background: #02B8F5; color: white; font-weight: 700; border-radius: 14px 14px 0 0; position: sticky; top: 0; z-index: 10;">
                    <?= $isStu ? 'All Students' : 'All Users' ?> (<?= e(count($allUsers)) ?>)
                </div>
                <div style="padding:10px 12px 4px;"><?php partial('Admin/_ListSearch', ['items' => '.user-item', 'ph' => 'নাম, ইউজারনেম বা ইমেইল', 'margin' => '0']); ?></div>

                <?php if (count($allUsers) === 0): ?>
                    <div class="text-center p-4 text-muted" style="font-size: 13px;">
                        No users found.
                    </div>
                <?php else: ?>
                    <?php foreach ($allUsers as $u): ?>
                        <?php
                        $isActive = $selected !== null && $selected->Id === $u->Id;
                        $roleClass = $u->Role === 'Teacher' ? 'teacher' : 'user';
                        ?>

                        <a href="<?= $ovBase ?>?id=<?= e($u->Id) ?>" class="user-item <?= $isActive ? 'active' : '' ?>" data-s="<?= e(mb_strtolower(implode(' ', [(string)$u->FullName, (string)$u->UserName, '@' . $u->UserName, (string)$u->Email, (string)($u->MobileNumber ?? '')]))) ?>">
                            <div class="u-avatar">
                                <?php if (!is_blank($u->ProfileImageLink)): ?>
                                    <img src="<?= e($u->ProfileImageLink) ?>" alt="<?= e($u->FullName) ?>" />
                                <?php else: ?>
                                    <?= e(mb_strlen((string)$u->FullName) > 0 ? mb_strtoupper(mb_substr((string)$u->FullName, 0, 1)) : 'U') ?>
                                <?php endif; ?>
                            </div>
                            <div class="u-info" style="min-width: 0;">
                                <div class="u-name"><?= e($u->FullName) ?></div>
                                <div class="u-email" style="font-weight:700;">@<?= e((string)$u->UserName) ?></div>
                                <div class="u-email">
                                    <i class="fas fa-envelope me-1" style="font-size:10px;"></i><?= e($u->Email) ?>
                                </div>
                                <span class="u-role-badge <?= e($roleClass) ?>"><?= e($isStu ? "Student" : $u->Role) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== Right Content Area ===== -->
        <div class="col-lg-9">
            <?php if ($selected === null): ?>
                <div class="empty-placeholder">
                    <i class="fas fa-hand-pointer"></i>
                    <h4 style="color:#08224A; font-weight:700;"><?= $isStu ? 'Select a Student' : 'Select a User' ?></h4>
                    <p style="font-size: 15px;">বাম দিকের তালিকা থেকে যেকোনো একজন <?= $isStu ? 'শিক্ষার্থী' : 'ইউজার' ?> সিলেক্ট করুন তার অ্যাক্টিভিটি দেখতে।</p>
                </div>
            <?php else: ?>
                <!-- ১. ইউজার প্রোফাইল হেডার -->
                <div class="detail-card d-flex align-items-center gap-4 flex-wrap">
                    <img src="<?= e(is_blank($selected->ProfileImageLink) ? 'https://ui-avatars.com/api/?name=' . rawurlencode((string)$selected->FullName) . '&background=1F3B2C&color=fff&size=100' : $selected->ProfileImageLink) ?>"
                         alt="<?= e((string)$selected->FullName . ' এর প্রোফাইল ছবি') ?>" style="width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 4px solid #CFF2FC;" />
                    <div style="flex: 1; min-width: 200px;">
                        <h3 style="margin: 0 0 6px; color: #08224A; font-weight: 800;"><a href="/Profile/Show/<?= (int)$selected->Id ?>" target="_blank" rel="noopener" style="color:inherit;text-decoration:none;" title="প্রোফাইল দেখুন"><?= e($selected->FullName) ?> <i class="fas fa-arrow-up-right-from-square" style="font-size:13px;color:#0EA5E9;"></i></a></h3>
                        <div style="font-size: 13.5px; color: #64748B; display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-envelope me-1 text-muted"></i> <?= e($selected->Email) ?></span>
                            <span><i class="fas fa-phone me-1 text-muted"></i> <?= e(is_blank($selected->MobileNumber) ? '—' : $selected->MobileNumber) ?></span>
                            <span><i class="far fa-calendar me-1 text-muted"></i> Joined: <?= e(fmt_date($selected->CreatedAt, 'M Y')) ?></span>
                        </div>
                        <div class="mt-2"><?= joined_via_full((int)$selected->Id) ?></div>
                        <div class="mt-2">
                            <span class="badge bg-success" style="font-size: 11px; padding: 5px 12px;"><?= e(role_label($selected->Role, $selected->Id)) ?></span>
                            <?php if ($selected->IsActive): ?>
                                <span class="badge bg-primary" style="font-size: 11px; padding: 5px 12px;">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger" style="font-size: 11px; padding: 5px 12px;">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ২. স্ট্যাটস গ্রিড -->
                <div class="stat-grid">
                    <div class="stat-card posts">
                        <div class="num"><?= e($stats->TotalPosts ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-file-alt text-primary"></i> Total Posts</div>
                    </div>
                    <div class="stat-card likes">
                        <div class="num"><?= e($stats->TotalLikes ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-heart text-danger"></i> Total Likes</div>
                    </div>
                    <div class="stat-card comments">
                        <div class="num"><?= e($stats->TotalComments ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-comment text-warning"></i> Comments</div>
                    </div>
                    <div class="stat-card teachers">
                        <div class="num"><?= e($stats->TotalTeachersConnected ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-chalkboard-teacher text-success"></i> Teachers Responded</div>
                    </div>
                </div>

                <?php if ($isStu): $sp = $ViewBag->StuPackage ?? null; $sReq = $ViewBag->StuRequests ?? []; $sCo = $ViewBag->StuCourses ?? []; $sPay = $ViewBag->StuPayments ?? [];
                    $rqLbl = ['Pending' => ['#FEF3C7', '#92400E', 'অপেক্ষায়'], 'Accepted' => ['#DCFCE7', '#166534', 'গ্রহণ করেছে'], 'Approved' => ['#DCFCE7', '#166534', 'গ্রহণ করেছে'], 'Rejected' => ['#FEE2E2', '#991B1B', 'বাতিল'], 'Declined' => ['#FEE2E2', '#991B1B', 'বাতিল']];
                    $pyLbl = ['Approved' => ['#DCFCE7', '#166534', 'Approved'], 'Pending' => ['#FEF3C7', '#92400E', 'অপেক্ষায়'], 'Rejected' => ['#FEE2E2', '#991B1B', 'Rejected']];
                    $pill = fn($m, $k) => '<span style="display:inline-block;font-size:11px;font-weight:800;padding:3px 9px;border-radius:999px;background:' . ($m[$k][0] ?? '#F1F5F9') . ';color:' . ($m[$k][1] ?? '#475569') . ';">' . e($m[$k][2] ?? $k) . '</span>'; ?>
                <div class="detail-card">
                    <div class="detail-title"><i class="fas fa-box-open text-primary"></i> প্যাকেজ</div>
                    <?php if (!$sp): ?><span style="color:#94A3B8;">কোনো প্যাকেজ নেয়নি।</span>
                    <?php else: ?><b><?= e($sp['name']) ?></b> &nbsp; <?= $sp['active'] ? '<span class="badge bg-success">চালু</span>' : '<span class="badge bg-danger">মেয়াদ শেষ</span>' ?>
                        <span style="color:#64748B;font-size:13.5px;margin-left:8px;"><?= $sp['ends'] ? 'মেয়াদ: ' . e(fmt_date($sp['ends'], 'd M Y')) . ' পর্যন্ত' : 'মেয়াদ শেষ নেই' ?></span><?php endif; ?>
                </div>

                <div class="detail-card">
                    <div class="detail-title"><i class="fas fa-paper-plane text-success"></i> যেসব টিচারকে রিকোয়েস্ট দিয়েছে (<?= count($sReq) ?>)</div>
                    <?php if (!$sReq): ?><span style="color:#94A3B8;">এখনো কোনো টিচারকে রিকোয়েস্ট দেয়নি।</span><?php else: ?>
                    <div style="overflow-x:auto;"><table class="table table-sm align-middle" style="font-size:13.5px;margin:0;">
                        <thead><tr style="color:#64748B;font-size:12px;"><th>টিচার</th><th>বিষয়</th><th>ক্লাস</th><th>তারিখ</th><th>অবস্থা</th></tr></thead><tbody>
                        <?php foreach ($sReq as $r): $tn = $r->TeacherFullName ?: $r->TutorName; ?>
                            <tr><td><?php if ($r->TeacherUserId): ?><a href="/Admin/TeacherOverview?id=<?= (int)$r->TeacherUserId ?>" style="font-weight:700;color:#0F172A;"><?= e($tn) ?></a><?php else: ?><?= e($tn) ?><?php endif; ?></td>
                                <td><?= e((string)$r->RequiredSubject) ?: '—' ?></td><td><?= e((string)$r->StudentClass) ?: '—' ?></td>
                                <td style="white-space:nowrap;"><?= e(fmt_date($r->CreatedAt, 'd M Y, h:i A')) ?></td><td><?= $pill($rqLbl, (string)$r->Status) ?></td></tr>
                        <?php endforeach; ?></tbody></table></div>
                    <?php endif; ?>
                </div>

                <div class="detail-card">
                    <div class="detail-title"><i class="fas fa-book-open-reader text-info"></i> কেনা কোর্স (<?= count($sCo) ?>)</div>
                    <?php if (!$sCo): ?><span style="color:#94A3B8;">কোনো কোর্স কেনেনি।</span><?php else: ?>
                    <div style="overflow-x:auto;"><table class="table table-sm align-middle" style="font-size:13.5px;margin:0;">
                        <thead><tr style="color:#64748B;font-size:12px;"><th>কোর্স</th><th>টিচার</th><th>দিয়েছে</th><th>কবে</th><th>অগ্রগতি</th></tr></thead><tbody>
                        <?php foreach ($sCo as $c): $en = $c->Enrollment ?? null; ?>
                            <tr><td><a href="/Admin/CourseView/<?= (int)$c->Id ?>" style="font-weight:700;color:#0F172A;"><?= e($c->Title) ?></a></td><td><?= e($c->TeacherName ?: $c->TeacherUser) ?></td>
                                <td>৳<?= number_format((int)($en->Price ?? 0)) ?></td><td style="white-space:nowrap;"><?= $en ? e(fmt_date($en->CreatedAt, 'd M Y')) : '—' ?></td>
                                <td><?= (int)($c->Progress['percent'] ?? 0) ?>%</td></tr>
                        <?php endforeach; ?></tbody></table></div>
                    <?php endif; ?>
                </div>

                <div class="detail-card">
                    <div class="detail-title"><i class="fas fa-money-bill-wave text-warning"></i> পেমেন্ট (<?= count($sPay) ?>)</div>
                    <?php if (!$sPay): ?><span style="color:#94A3B8;">কোনো পেমেন্ট করেনি।</span><?php else: ?>
                    <div style="overflow-x:auto;"><table class="table table-sm align-middle" style="font-size:13.5px;margin:0;">
                        <thead><tr style="color:#64748B;font-size:12px;"><th>কী কিনেছে</th><th>টাকা</th><th>মাধ্যম</th><th>কুপন</th><th>তারিখ</th><th>অবস্থা</th></tr></thead><tbody>
                        <?php foreach ($sPay as $p): ?>
                            <tr><td><?= e((string)$p->PackageName) ?></td><td><b>৳<?= number_format((int)$p->Amount) ?></b></td>
                                <td><?= e((string)$p->MethodName) ?><?= $p->TxnId ? '<br><small style="color:#64748B;">' . e($p->TxnId) . '</small>' : '' ?></td>
                                <td><?= !empty($p->CouponCode) ? '<code>' . e($p->CouponCode) . '</code>' : '—' ?></td>
                                <td style="white-space:nowrap;"><?= e(fmt_date($p->CreatedAt, 'd M Y, h:i A')) ?></td><td><?= $pill($pyLbl, (string)$p->Status) ?></td></tr>
                        <?php endforeach; ?></tbody></table></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- ৩. বাম কলাম: Posts -->
                    <div class="col-md-7">
                        <div class="detail-card" style="height: calc(100% - 20px);">
                            <div class="detail-title">
                                <i class="fas fa-file-alt text-primary"></i>
                                Posts (<?= e(count($postStats)) ?>)
                            </div>

                            <?php if (count($postStats) === 0): ?>
                                <p class="text-muted text-center py-4" style="font-size: 14px;">কোনো পোস্ট পাওয়া যায়নি।</p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 480px;">
                                    <?php foreach ($postStats as $p): ?>
                                        <div class="list-item post">
                                            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-1">
                                                <span style="font-size: 11.5px; color: #7A8CA0;">
                                                    <i class="far fa-clock"></i> <?= e($p->TimeAgo) ?>
                                                </span>
                                                <?php if ($p->HasTeacherResponse): ?>
                                                    <span class="badge-teacher-yes">
                                                        <i class="fas fa-chalkboard-teacher me-1"></i><?= e($p->TeacherResponses) ?> teacher(s) responded
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge-teacher-no">
                                                        <i class="fas fa-hourglass-half me-1"></i>No teacher response yet
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px; line-height: 1.5;">
                                                <?= e($p->ContentPreview) ?>
                                            </p>

                                            <?php if (!is_blank($p->PreferredArea) || !is_blank($p->Subjects) || !is_blank($p->ClassLevel)): ?>
                                                <div style="font-size: 11.5px; color: #64748B; display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 6px;">
                                                    <?php if (!is_blank($p->PreferredArea)): ?>
                                                        <span><i class="fas fa-map-marker-alt"></i> <?= e($p->PreferredArea) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!is_blank($p->ClassLevel)): ?>
                                                        <span><i class="fas fa-graduation-cap"></i> <?= e($p->ClassLevel) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!is_blank($p->Subjects)): ?>
                                                        <span><i class="fas fa-book"></i> <?= e($p->Subjects) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div style="font-size: 11.5px; color: #7A8CA0; display: flex; gap: 12px;">
                                                <span><i class="fas fa-heart" style="color:#e74c3c;"></i> <?= e($p->LikeCount) ?></span>
                                                <span><i class="fas fa-comment" style="color:#3498db;"></i> <?= e($p->CommentCount) ?></span>
                                                <?php if (!is_blank($p->Budget)): ?>
                                                    <span><i class="fas fa-money-bill-wave" style="color:#2ecc71;"></i> ৳<?= e($p->Budget) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ৪. ডান কলাম: Teachers Responded + Applications -->
                    <div class="col-md-5">
                        <!-- Teachers Responded -->
                        <div class="detail-card">
                            <div class="detail-title">
                                <i class="fas fa-chalkboard-teacher text-success"></i>
                                Teachers Responded (<?= e(count($teachers)) ?>)
                            </div>

                            <?php if (count($teachers) === 0): ?>
                                <p class="text-muted text-center py-3" style="font-size: 13px;">
                                    কোনো টিচার এখনো রেসপন্স করেনি।
                                </p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 220px;">
                                    <?php foreach ($teachers as $t): ?>
                                        <div class="list-item teacher-item d-flex align-items-center gap-2">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #02B8F5; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; overflow: hidden;">
                                                <?php if (!is_blank($t->ProfileImageLink)): ?>
                                                    <img alt="<?= e(($t->FullName ?? 'টিচার') . ' এর প্রোফাইল ছবি') ?>" src="<?= e($t->ProfileImageLink) ?>" style="width:100%; height:100%; object-fit:cover;" />
                                                <?php else: ?>
                                                    <?= e($t->Initials) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-size: 13px; font-weight: 700; color: #08224A;"><a href="/Profile/Show/<?= (int)$t->TeacherId ?>" target="_blank" rel="noopener" style="color:inherit;"><?= e($t->FullName) ?></a></div>
                                                <div style="font-size: 11px; color: #7A8CA0;">
                                                    <i class="fas fa-comment me-1"></i><?= e($t->ResponseCount) ?> response(s) • <?= e($t->LastResponseAgo) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tuition Applications -->
                        <div class="detail-card">
                            <div class="detail-title">
                                <i class="fas fa-user-check text-warning"></i>
                                Tuition Applications (<?= e(count($apps)) ?>)
                            </div>

                            <?php if (count($apps) === 0): ?>
                                <p class="text-muted text-center py-3" style="font-size: 13px;">
                                    কোনো অ্যাপ্লিকেশন নেই।
                                </p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 220px;">
                                    <?php foreach ($apps as $a): ?>
                                        <?php
                                        $statusBg = $a->Status === 'Pending' ? 'warning'
                                                     : ($a->Status === 'Selected' ? 'success'
                                                     : ($a->Status === 'Shortlisted' ? 'primary'
                                                     : 'secondary'));
                                        ?>

                                        <div class="list-item app">
                                            <div class="d-flex justify-content-between align-items-center mb-1 gap-1">
                                                <strong style="color: #08224A; font-size: 12.5px;">
                                                    <?= e($a->TuitionPost !== null ? $a->TuitionPost->Title : 'Tuition #' . $a->TuitionPostId) ?>
                                                </strong>
                                                <span class="badge bg-<?= e($statusBg) ?> text-dark" style="font-size: 9px; padding: 3px 8px;"><?= e($a->Status) ?></span>
                                            </div>
                                            <?php if ($a->TuitionPost !== null): ?>
                                                <div style="font-size: 11.5px; color: #64748B; display: flex; flex-wrap: wrap; gap: 10px;">
                                                    <?php if (!is_blank($a->TuitionPost->Area)): ?>
                                                        <span><i class="fas fa-map-marker-alt"></i> <?= e($a->TuitionPost->Area) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!is_blank($a->TuitionPost->Salary)): ?>
                                                        <span><i class="fas fa-money-bill-wave"></i> ৳<?= e($a->TuitionPost->Salary) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="font-size: 10.5px; color: #7A8CA0; margin-top: 4px;">
                                                <i class="far fa-calendar me-1"></i><?= e(fmt_date($a->AppliedAt, 'd M Y')) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
