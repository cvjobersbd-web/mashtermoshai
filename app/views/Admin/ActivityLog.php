<?php
/** Admin → Activity Log (শুধু Super Admin): কোন অ্যাডমিন কখন কী করেছেন */
$ViewData['Title'] = 'Activity Log';
$rows = is_array($Model) ? $Model : [];
$admins = is_array($ViewBag->Admins) ? $ViewBag->Admins : [];
$sel = (int)$ViewBag->Admin; $date = (string)$ViewBag->Date; $page = (int)$ViewBag->Page;
$qs = fn(array $o) => '/Admin/ActivityLog?' . http_build_query(array_filter(array_merge(['admin' => $sel ?: '', 'date' => $date], $o), fn($v) => $v !== '' && $v !== null));
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .al-head h2 { color: #08224A; margin: 0; font-size: 24px; }
    .al-head p { color: #64748B; margin: 4px 0 18px; font-size: 14px; }
    .al-filter { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; background: #fff; border-radius: 14px; padding: 12px 14px; box-shadow: 0 4px 16px rgba(0,0,0,.05); margin-bottom: 16px; }
    .al-filter select, .al-filter input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 10px; font-size: 14px; font-family: inherit; color: #08224A; background: #fff; }
    .al-filter button, .al-filter a { height: 40px; display: inline-flex; align-items: center; gap: 6px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 13.5px; font-family: inherit; }
    .al-filter a { background: #F1F5F9; color: #334155; }
    .al-list { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); overflow: hidden; }
    .al-row { display: grid; grid-template-columns: 150px 180px minmax(0, 1fr); gap: 14px; padding: 12px 16px; border-bottom: 1px solid #EEF2F7; font-size: 14px; align-items: baseline; }
    .al-row:last-child { border-bottom: none; }
    .al-row .t { color: #64748B; font-size: 12.5px; } .al-row .t b { display: block; color: #334155; font-size: 13px; }
    .al-row .w { font-weight: 700; color: #08224A; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .al-row .a b { color: #0F172A; } .al-row .a small { display: block; color: #94A3B8; font-size: 12px; margin-top: 2px; word-break: break-all; }
    .al-empty { padding: 40px 16px; text-align: center; color: #64748B; }
    .al-pg { display: flex; justify-content: center; gap: 10px; margin-top: 14px; }
    .al-pg a { padding: 9px 18px; border-radius: 10px; background: #fff; border: 1.5px solid #DCECF5; color: #08224A; font-weight: 700; text-decoration: none; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
    @media (max-width: 640px) { .al-row { grid-template-columns: 1fr; gap: 4px; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content">
    <div class="al-head">
        <h2><i class="fas fa-clock-rotate-left me-2" style="color:#1D7FE5;"></i>Activity Log</h2>
        <p>কোন অ্যাডমিন কখন কী পরিবর্তন করেছেন — পেমেন্ট Approve, প্যাকেজের দাম বদল, ইউজার বন্ধ ইত্যাদি সব এখানে থাকে।</p>
    </div>
    <form class="al-filter" method="get" action="/Admin/ActivityLog">
        <select name="admin" aria-label="অ্যাডমিন"><option value="">সব অ্যাডমিন</option>
            <?php foreach ($admins as $a): ?><option value="<?= (int)$a->Id ?>"<?= $sel === $a->Id ? ' selected' : '' ?>><?= e($a->Name) ?><?= $a->IsSuper ? ' (Super)' : '' ?></option><?php endforeach; ?></select>
        <input type="date" name="date" value="<?= e($date) ?>" aria-label="তারিখ" />
        <button type="submit"><i class="fas fa-filter"></i> দেখুন</button>
        <?php if ($sel || $date !== ''): ?><a href="/Admin/ActivityLog">সব</a><?php endif; ?>
    </form>
    <div class="al-list">
        <?php if (!$rows): ?><div class="al-empty"><i class="fas fa-inbox fa-2x" style="color:#B9D6E6;display:block;margin-bottom:10px;"></i>কোনো কাজের রেকর্ড নেই।</div><?php endif; ?>
        <?php foreach ($rows as $r): $n = !is_blank($r->FullName) ? $r->FullName : ($r->UserName ?? 'Deleted admin'); ?>
            <div class="al-row">
                <div class="t"><b><?= e(date('d M Y', strtotime((string)$r->CreatedAt))) ?></b><?= e(date('h:i A', strtotime((string)$r->CreatedAt))) ?> · <?= e(time_ago($r->CreatedAt)) ?></div>
                <div class="w" title="<?= e($n) ?>"><i class="fas fa-user-shield" style="color:#94A3B8;"></i> <?= e($n) ?></div>
                <div class="a"><b><?= e($r->Action) ?></b><?php if (!is_blank($r->Detail)): ?><small><?= e($r->Detail) ?></small><?php endif; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="al-pg">
        <?php if ($page > 1): ?><a href="<?= e($qs(['page' => $page - 1])) ?>"><i class="fas fa-chevron-left"></i> নতুনগুলো</a><?php endif; ?>
        <?php if ($ViewBag->HasMore): ?><a href="<?= e($qs(['page' => $page + 1])) ?>">পুরোনোগুলো <i class="fas fa-chevron-right"></i></a><?php endif; ?>
    </div>
</div>
