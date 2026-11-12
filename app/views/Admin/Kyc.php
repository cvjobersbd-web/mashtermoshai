<?php
/** Admin → Verification: টিচার আর শিক্ষার্থীর পরিচয় যাচাই — আলাদা সেকশন; পুরো তথ্য + NID / ID কার্ডের ছবি দেখে Approve / Reject */
$ViewData['Title'] = 'Verification';
$list = is_array($Model) ? $Model : [];
$kind = (string)$ViewBag->Kind; $S = (string)$ViewBag->S; $C = $ViewBag->Counts; $apps = $ViewBag->Apps ?? [];
$classes = Db::KYC_CLASSES;
$fmt = fn($d) => $d ? date('d M Y, h:i A', strtotime((string)$d)) : '';
$exTxt = function ($x, $EX) { if (!is_array($x) || !$x) return null; return ($x['status'] ?? '') === 'running' ? "$EX — Running (এখন পড়ছে)" : "$EX {$x['year']} · GPA {$x['gpa']}" . (!empty($x['board']) ? " · {$x['board']}" : ''); };
$stBn = ['Pending' => ['যাচাই বাকি', 'p'], 'Approved' => ['Approved', 'ok'], 'Rejected' => ['Rejected', 'no']];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 90px; min-height: calc(100vh - 80px); }
    .kc h2 { color: #08224A; margin: 0; font-size: 24px; }
    .kc .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 900px; line-height: 1.6; }
    .kc-kinds { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 14px; }
    .kc-kind { display: flex; align-items: center; gap: 14px; background: #fff; border-radius: 16px; padding: 16px 18px; text-decoration: none; color: #0F172A; box-shadow: 0 4px 16px rgba(0,0,0,.05); border: 2px solid transparent; }
    .kc-kind.on { border-color: #08224A; }
    .kc-kind .ic { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .kc-kind.t .ic { background: #DCFCE7; color: #166534; } .kc-kind.s .ic { background: #E0F2FE; color: #075985; }
    .kc-kind b { font-size: 17px; display: block; } .kc-kind small { color: #64748B; font-size: 13px; }
    .kc-kind .pend { margin-left: auto; background: #F59E0B; color: #fff; font-weight: 800; font-size: 13px; border-radius: 999px; padding: 4px 12px; white-space: nowrap; }
    .kc-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
    .kc-tabs a { padding: 8px 15px; border-radius: 999px; background: #fff; color: #334155; font-weight: 700; font-size: 13.5px; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
    .kc-tabs a.on { background: #08224A; color: #fff; }
    .kc-c { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 14px; border-left: 5px solid var(--c, #F59E0B); }
    .kc-c.Approved { --c: #16A34A; } .kc-c.Rejected { --c: #DC2626; }
    .kc-top { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 12px; }
    .kc-ava { width: 46px; height: 46px; border-radius: 50%; background: #E0F2FE; color: #0369A1; display: flex; align-items: center; justify-content: center; font-weight: 800; overflow: hidden; flex-shrink: 0; }
    .kc-ava img { width: 100%; height: 100%; object-fit: cover; }
    .kc-top .nm { font-size: 17px; font-weight: 800; color: #0F172A; text-decoration: none; } .kc-top .nm:hover { text-decoration: underline; }
    .kc-top small { display: block; color: #64748B; font-size: 12.5px; }
    .kc-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; }
    .kc-pill.p { background: #FEF3C7; color: #92400E; } .kc-pill.ok { background: #DCFCE7; color: #166534; } .kc-pill.no { background: #FEE2E2; color: #991B1B; } .kc-pill.g { background: #F1F5F9; color: #475569; }
    .kc-body { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 18px; }
    .kc-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px 16px; align-content: start; }
    .kc-grid div small { display: block; font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
    .kc-grid div b { font-size: 14px; color: #0F172A; overflow-wrap: anywhere; font-weight: 700; }
    .kc-grid .w2 { grid-column: span 2; } .kc-grid .w3 { grid-column: 1 / -1; }
    .kc-grid a.tel { color: #0369A1; text-decoration: none; }
    .kc-ids { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .kc-ids a { display: block; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; background: #F1F5F9; position: relative; aspect-ratio: 1.58; cursor: zoom-in; }
    .kc-ids img { width: 100%; height: 100%; object-fit: contain; }
    .kc-ids a span { position: absolute; left: 6px; top: 6px; background: rgba(8,34,74,.8); color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; }
    .kc-app { margin-top: 12px; border-radius: 12px; padding: 10px 14px; font-size: 13.5px; line-height: 1.7; border: 1px solid; }
    .kc-app.ok { background: #F0FDF4; border-color: #86EFAC; color: #166534; } .kc-app.warn { background: #FFFBEB; border-color: #FDE68A; color: #92400E; } .kc-app.no { background: #FEF2F2; border-color: #FECACA; color: #991B1B; } .kc-app.g { background: #F8FAFC; border-color: #E2E8F0; color: #334155; }
    .kc-app a { color: inherit; font-weight: 800; }
    .kc-note { margin-top: 10px; font-size: 13px; background: #FEF2F2; color: #991B1B; border-radius: 10px; padding: 8px 12px; }
    .kc-act { margin-top: 14px; padding-top: 14px; border-top: 1px dashed #E2E8F0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .kc-act input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; flex: 1 1 260px; min-width: 0; outline: none; }
    .kc-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
    .kc-btn.ok { background: #16A34A; color: #fff; } .kc-btn.no { background: #FEE2E2; color: #B91C1C; }
    .kc-empty { text-align: center; color: #94A3B8; padding: 40px 10px; background: #fff; border-radius: 16px; }
    .kc-empty i { font-size: 34px; display: block; margin-bottom: 8px; color: #CBD5E1; }
    .kc-lb { position: fixed; inset: 0; background: rgba(2,6,23,.88); z-index: 3000; display: none; align-items: center; justify-content: center; padding: 20px; cursor: zoom-out; }
    .kc-lb.open { display: flex; } .kc-lb img { max-width: 100%; max-height: 100%; border-radius: 10px; }
    .kc-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; max-width: 92vw; text-align: center; }
    @media (max-width: 1200px) { .kc-body { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 80px; } .kc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 560px) { .kc-kinds, .kc-grid { grid-template-columns: minmax(0, 1fr); } .kc-grid .w2 { grid-column: auto; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content kc">
    <?= csrf_field() ?>
    <h2><i class="fas fa-id-card" style="color:#2563EB;"></i> Verification — টিচার ও শিক্ষার্থীর পরিচয় যাচাই</h2>
    <p class="sub">টিচার আর শিক্ষার্থীর জমা দেওয়া তথ্য, NID / ID কার্ডের ছবি দেখে Approve বা Reject করুন। নতুন টিচার আবেদনে: <b>Teacher Verification Approve</b> + <b>Teacher Payment Requests এ পেমেন্ট Accept</b> — দুটো হলেই ইউজার টিচার হয়ে যায়। Student Verification Approve হলে প্যাকেজ কিনতে ও টিউশন পোস্ট করতে পারে।</p>

    <div class="kc-kinds">
        <?php foreach (['teacher' => ['t', 'fa-chalkboard-user', 'Teacher Verification', 'Academic ID Card, মোবাইল, WhatsApp + আবেদনের ফর্ম'], 'student' => ['s', 'fa-user-graduate', 'Student Verification', 'বাবা-মা, ঠিকানা, ক্লাস, রেজাল্ট, ID কার্ড']] as $kk => [$cls, $ic, $lbl, $hint]): $pc = (int)$C[$kk]['Pending']; ?>
            <a class="kc-kind <?= $cls ?><?= $kind === $kk ? ' on' : '' ?>" href="/Admin/Kyc?k=<?= $kk ?>"><span class="ic"><i class="fas <?= $ic ?>"></i></span><span><b><?= $lbl ?></b><small><?= $hint ?> · মোট <?= array_sum($C[$kk]) ?></small></span><?php if ($pc): ?><span class="pend"><?= $pc ?> যাচাই বাকি</span><?php endif; ?></a>
        <?php endforeach; ?>
    </div>
    <div class="kc-tabs">
        <?php foreach (['Pending' => 'যাচাই বাকি', 'Approved' => 'Approved', 'Rejected' => 'Rejected', 'all' => 'সব'] as $sk => $sl): ?>
            <a class="<?= $S === $sk ? 'on' : '' ?>" href="/Admin/Kyc?k=<?= $kind ?>&amp;s=<?= $sk ?>"><?= $sl ?> <?= $sk !== 'all' ? '(' . (int)$C[$kind][$sk] . ')' : '' ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$list): ?><div class="kc-empty"><i class="fas fa-id-card"></i>এখানে কিছু নেই।</div><?php endif; ?>

    <?php foreach ($list as $k): $D = $k->D; $nm = (string)($k->UFullName ?: $k->UUserName ?: 'Deleted user'); [$sl, $sc] = $stBn[$k->Status] ?? [$k->Status, 'g']; ?>
        <div class="kc-c <?= e($k->Status) ?>" id="kyc-<?= (int)$k->Id ?>">
            <div class="kc-top">
                <span class="kc-ava"><?php if (!is_blank($k->UPhoto)): ?><img src="<?= e($k->UPhoto) ?>" alt="" onerror="this.remove()" /><?php else: ?><?= e(mb_strtoupper(mb_substr($nm, 0, 1))) ?><?php endif; ?></span>
                <div><a class="nm" href="/Profile/Show/<?= (int)$k->UserId ?>" target="_blank"><?= e($kind === 'student' && !empty($D['fullName']) ? $D['fullName'] : $nm) ?></a>
                    <small>@<?= e((string)$k->UUserName) ?> · <?= e((string)$k->UEmail) ?> · জমা: <?= e($fmt($k->CreatedAt)) ?></small></div>
                <span class="kc-pill g"><?= e(role_label($k->URole, $k->UserId)) ?></span>
                <span class="kc-pill <?= $sc ?>" style="margin-left:auto;"><?= e($sl) ?></span>
            </div>
            <div style="margin:-4px 0 10px;"><?= joined_via_full($k->UserId) ?></div>
            <div class="kc-body">
                <div>
                    <div class="kc-grid">
                        <?php if ($kind === 'teacher'): ?>
                            <div><small>মোবাইল</small><b><a class="tel" href="tel:<?= e($D['mobile'] ?? '') ?>"><?= e($D['mobile'] ?? '') ?></a></b></div>
                            <div><small>WhatsApp</small><b><a class="tel" href="https://wa.me/88<?= e($D['whatsapp'] ?? '') ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp" style="color:#25D366;"></i> <?= e($D['whatsapp'] ?? '') ?></a></b></div>
                            <div><small>NID নম্বর</small><b><?= e(($D['nid'] ?? '') ?: '—') ?></b></div>
                            <?php if (!empty($D['university'])): ?><div class="w3"><small>শিক্ষা প্রতিষ্ঠান (ID কার্ড অনুযায়ী)</small><b><?= e($D['university']) ?></b></div><?php endif; ?>
                            <?php if ($a = $apps[$k->Id] ?? null): $r = $a['req']; ?>
                                <div><small>শিক্ষা</small><b><?= e((string)$r->Education) ?></b></div>
                                <div><small>প্রতিষ্ঠান</small><b><?= e((string)$r->Institution) ?></b></div>
                                <div><small>অভিজ্ঞতা · বেতন</small><b><?= e((string)$r->Experience) ?><?= !is_blank($r->HourlyRate) ? ' · ৳' . e((string)$r->HourlyRate) . '/মাস' : '' ?></b></div>
                                <div class="w3"><small>বিষয়</small><b><?= e((string)$r->SubjectExpertise) ?></b></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div><small>ক্লাস</small><b><?= e($classes[$D['classLevel'] ?? ''] ?? '') ?><?= !empty($D['group']) ? ' · ' . e($D['group']) : '' ?></b></div>
                            <div class="w2"><small>প্রতিষ্ঠান</small><b><?= e($D['institution'] ?? '') ?></b></div>
                            <div><small>বাবা</small><b><?= e($D['fatherName'] ?? '') ?></b></div>
                            <div><small>বাবার মোবাইল</small><b><?= !empty($D['fatherMobile']) ? '<a class="tel" href="tel:' . e($D['fatherMobile']) . '">' . e($D['fatherMobile']) . '</a>' : '—' ?></b></div>
                            <div><small>নিজের মোবাইল</small><b><?= !empty($D['mobile']) ? '<a class="tel" href="tel:' . e($D['mobile']) . '">' . e($D['mobile']) . '</a>' : '—' ?></b></div>
                            <div><small>WhatsApp</small><b><?= !empty($D['whatsapp']) ? '<a class="tel" href="https://wa.me/88' . e($D['whatsapp']) . '" target="_blank" rel="noopener"><i class="fab fa-whatsapp" style="color:#25D366;"></i> ' . e($D['whatsapp']) . '</a>' : '—' ?></b></div>
                            <div><small>মা</small><b><?= e(($D['motherName'] ?? '') ?: '—') ?></b></div>
                            <div><small>মায়ের মোবাইল</small><b><?= !empty($D['motherMobile']) ? '<a class="tel" href="tel:' . e($D['motherMobile']) . '">' . e($D['motherMobile']) . '</a>' : '—' ?></b></div>
                            <div><small>জেলা</small><b><?= e($D['district'] ?? '') ?></b></div>
                            <div class="w3"><small>গ্রামের বাড়ি (স্থায়ী)</small><b><?= e($D['permanentAddress'] ?? '') ?></b></div>
                            <div class="w3"><small>বর্তমান ঠিকানা</small><b><?= e($D['presentAddress'] ?? '') ?></b></div>
                            <?php if ($t = $exTxt($D['ssc'] ?? null, 'SSC')): ?><div class="w2"><small>SSC</small><b><?= e($t) ?></b></div><?php endif; ?>
                            <?php if ($t = $exTxt($D['hsc'] ?? null, 'HSC')): ?><div class="w2"><small>HSC</small><b><?= e($t) ?></b></div><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($kind === 'teacher'):
                        $a = $apps[$k->Id] ?? null; ?>
                        <?php if ($a && in_array($a['req']->Status, ['Pending', 'AwaitingPayment'], true)): $p = $a['pay']; ?>
                            <?php if (!$p): ?>
                                <div class="kc-app warn"><i class="fas fa-hourglass-half"></i> নতুন টিচার আবেদন — এখনো টিচার প্যাকেজের পেমেন্ট করেননি।</div>
                            <?php else: $pc2 = $p->Status === 'Approved' ? 'ok' : ($p->Status === 'Rejected' ? 'no' : 'warn'); ?>
                                <div class="kc-app <?= $pc2 ?>"><i class="fas fa-receipt"></i> নতুন টিচার আবেদন · প্যাকেজ: <b><?= e($p->PackageName) ?></b> — <?= e(taka($p->Amount)) ?> ·
                                    পেমেন্ট: <b><?= $p->Status === 'Approved' ? 'Accepted' : ($p->Status === 'Rejected' ? 'Rejected' : 'যাচাই বাকি') ?></b>
                                    <?php if ($p->Status === 'Pending'): ?> — Teacher Verification Approve করার পর <a href="/Admin/TeacherPayments#ap-<?= (int)$p->Id ?>">Teacher Payment Requests এ Accept করুন</a><?php endif; ?></div>
                            <?php endif; ?>
                        <?php elseif ($a && $a['req']->Status === 'Approved'): ?>
                            <div class="kc-app g"><i class="fas fa-circle-check"></i> টিচার আবেদন Approved।</div>
                        <?php endif; ?>
                        <?php if ($a): ?><div style="margin-top:8px;font-size:13px;"><a href="/Tutors/Profile/<?= (int)$k->UserId ?>" target="_blank" style="font-weight:700;color:#0284C7;"><i class="fas fa-id-card"></i> পুরো ফর্ম (Tutor Details) দেখুন</a></div><?php endif; ?>
                    <?php endif; ?>
                    <?php if ($kind === 'student'): $sp = ($ViewBag->StuPays ?? [])[$k->Id] ?? null; ?>
                        <?php if (!$sp): ?>
                            <div class="kc-app warn"><i class="fas fa-hourglass-half"></i> এখনো শিক্ষার্থী প্যাকেজ কেনেননি (পেমেন্ট করেননি)।</div>
                        <?php else: $c3 = $sp->Status === 'Approved' ? 'ok' : ($sp->Status === 'Rejected' ? 'no' : 'warn'); ?>
                            <div class="kc-app <?= $c3 ?>"><i class="fas fa-receipt"></i> শিক্ষার্থী প্যাকেজ কিনেছেন: <b><?= e($sp->PackageName) ?></b> — <?= e(taka($sp->Amount)) ?> · <?= e((string)$sp->MethodName) ?> ·
                                পেমেন্ট: <b><?= $sp->Status === 'Approved' ? 'Accepted ✅' : ($sp->Status === 'Rejected' ? 'Rejected' : 'যাচাই বাকি') ?></b>
                                <?php if ($sp->Status === 'Pending'): ?> — Verification Approve করার পর <a href="/Admin/StudentPayments#ap-<?= (int)$sp->Id ?>">Student Payment Requests এ Accept করুন</a><?php endif; ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($k->Status === 'Rejected' && !is_blank($k->AdminNote)): ?><div class="kc-note"><i class="fas fa-circle-xmark"></i> বাতিলের কারণ: <?= e($k->AdminNote) ?></div><?php endif; ?>
                </div>
                <div class="kc-ids">
                    <?php
                    if (!empty($D['adminGranted'])) { $imgs = []; echo '<div style="grid-column:1/-1;font-size:13px;color:#7C3AED;font-weight:700;"><i class="fas fa-user-gear"></i> অ্যাডমিন User Role পেজ থেকে স্টুডেন্ট করেছেন — ফর্ম / ছবি নেই।</div>'; }
                    elseif ($kind !== 'teacher') $imgs = ['front' => 'ID কার্ড · সামনে', 'back' => 'ID কার্ড · পেছনে'];
                    elseif (!empty($D['uniCard'])) $imgs = ['front' => 'Academic ID · সামনে', 'back' => 'Academic ID · পেছনে'] + (!empty($D['nidFront']) ? ['nidfront' => 'NID · সামনে'] : []) + (!empty($D['nidBack']) ? ['nidback' => 'NID · পেছনে'] : []);
                    else $imgs = ['front' => 'NID · সামনে', 'back' => 'NID · পেছনে'];
                    foreach ($imgs as $side => $sl2): $u = '/Kyc/File/' . (int)$k->Id . '?side=' . $side; ?>
                        <a href="<?= e($u) ?>" data-lb><img src="<?= e($u) ?>" alt="" loading="lazy" /><span><?= e($sl2) ?></span></a>
                    <?php endforeach; ?>
                    <?php if ($kind === 'teacher' && !empty($D['uniCard']) && empty($D['nidFront']) && empty($D['nidBack'])): ?><div style="grid-column:1/-1;font-size:12.5px;color:#94A3B8;"><i class="fas fa-circle-info"></i> NID দেননি (ঐচ্ছিক)</div><?php endif; ?>
                </div>
            </div>
            <?php if ($k->Status !== 'Rejected'): ?>
            <div class="kc-act">
                <?php if ($k->Status === 'Pending'): ?><button type="button" class="kc-btn ok" data-kyc="<?= (int)$k->Id ?>" data-act="Approve"><i class="fas fa-check"></i> Approve</button><?php endif; ?>
                <input type="text" maxlength="300" placeholder="বাতিলের কারণ — ঐচ্ছিক (যেমন: ID কার্ডের ছবি ঝাপসা)" id="kycNote<?= (int)$k->Id ?>" />
                <button type="button" class="kc-btn no" data-kyc="<?= (int)$k->Id ?>" data-act="Reject"><i class="fas fa-xmark"></i> <?= $k->Status === 'Approved' ? 'Approve বাতিল করুন' : 'Reject' ?></button>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<div class="kc-lb" id="kcLb"><img alt="" /></div>
<script>
(function () {
    function toast(m) { var t = document.querySelector('.kc-toast'); if (!t) { t = document.createElement('div'); t.className = 'kc-toast'; document.body.appendChild(t); } t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 3500); }
    var lb = document.getElementById('kcLb');
    document.addEventListener('click', async function (e) {
        var a = e.target.closest('[data-lb]');
        if (a) { e.preventDefault(); lb.querySelector('img').src = a.getAttribute('href'); lb.classList.add('open'); return; }
        if (e.target.closest('#kcLb')) { lb.classList.remove('open'); return; }
        var b = e.target.closest('[data-kyc]'); if (!b) return;
        var id = b.dataset.kyc, act = b.dataset.act, note = (document.getElementById('kycNote' + id) || {}).value || '';
        if (act === 'Reject' && !confirm(note.trim() ? 'Reject করবেন?' : 'কারণ না লিখেই Reject করবেন?')) return;
        if (act === 'Approve' && !confirm('তথ্য আর ছবি দেখে নিশ্চিত হয়েছেন? Approve করবেন?')) return;
        b.disabled = true;
        var fd = new FormData(); fd.append('id', id); fd.append('action', act); fd.append('note', note);
        fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        var d; try { d = await (await fetch('/Admin/KycDecide', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); } catch (x) { d = { success: false, message: 'সার্ভারে সমস্যা' }; }
        toast(d.message || ''); b.disabled = false;
        if (d.success) setTimeout(function () { location.reload(); }, 1400);
    });
})();
</script>
