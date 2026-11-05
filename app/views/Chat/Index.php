<?php
/**
 * চ্যাট (মেসেঞ্জারের মতো) — বাঁয়ে চ্যাটের তালিকা, ডানে কথোপকথন।
 * মেসেজগুলো JS দিয়ে textContent হিসেবে বসে, তাই কারো লেখা HTML/স্ক্রিপ্ট হিসেবে চলে না।
 */
$threads = is_array($Model) ? $Model : [];
$cur = $ViewBag->Current;
$closed = !empty($ViewBag->Closed);
$ViewData['Title'] = 'Messages';
$ViewData['FullTitle'] = ($cur ? $cur->OtherName . ' — ' : '') . 'Messages | মাষ্টার মশাই';
$ViewData['Robots'] = 'noindex, nofollow';
$role = current_role();
?>
<style>
    .ch { background: #EEF3F8; padding: 18px 0 26px; }
    .ch-wrap { max-width: 1240px; margin: 0 auto; padding: 0 16px; }
    .ch-box { display: grid; grid-template-columns: 340px minmax(0, 1fr); height: calc(100vh - 80px - 44px); min-height: 520px; background: #fff; border: 1px solid #E1E8F0; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 26px rgba(15,27,45,.06); }
    /* ===== তালিকা ===== */
    .ch-list { border-right: 1px solid #EAEFF5; display: flex; flex-direction: column; min-height: 0; }
    .ch-lh { padding: 18px 18px 12px; }
    .ch-lh h1 { margin: 0; font-size: 22px; font-weight: 800; color: #0F172A; font-family: var(--font-heading); }
    .ch-lh p { margin: 4px 0 0; font-size: 12.5px; color: #64748B; }
    .ch-threads { flex: 1; overflow-y: auto; padding: 4px 8px 10px; }
    .ch-t { display: flex; gap: 11px; align-items: center; padding: 10px; border-radius: 12px; text-decoration: none; color: inherit; position: relative; }
    .ch-t:hover { background: #F3F7FB; }
    .ch-t.on { background: #E6F9FF; }
    .ch-av { width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #08224A, #0A5A86); color: #fff; font-weight: 700; font-size: 16px; }
    .ch-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ch-av.sm { width: 40px; height: 40px; font-size: 14px; }
    .ch-tm { min-width: 0; flex: 1; }
    .ch-tn { display: flex; gap: 6px; align-items: baseline; }
    .ch-tn b { font-size: 14.5px; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ch-tn small { margin-left: auto; font-size: 11.5px; color: #94A3B8; white-space: nowrap; }
    .ch-tl { font-size: 13px; color: #64748B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    .ch-t.unread .ch-tl { color: #0F172A; font-weight: 700; }
    .ch-t .dot { width: 20px; height: 20px; border-radius: 10px; background: #00CFFF; color: #fff; font-size: 11px; font-weight: 800; display: none; align-items: center; justify-content: center; flex-shrink: 0; padding: 0 6px; }
    .ch-t.unread .dot { display: inline-flex; }
    .ch-tr { font-size: 10.5px; font-weight: 700; color: #0369A1; background: #E0F2FE; border-radius: 6px; padding: 0 6px; }
    .ch-none { padding: 30px 22px; text-align: center; color: #64748B; font-size: 14px; line-height: 1.6; }
    .ch-none i { font-size: 40px; color: #B9E9F7; display: block; margin-bottom: 12px; }
    .ch-none a { color: #0284C7; font-weight: 700; }
    /* ===== কথোপকথন ===== */
    .ch-main { display: flex; flex-direction: column; min-height: 0; min-width: 0; }
    .ch-mh { display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-bottom: 1px solid #EAEFF5; }
    .ch-back { display: none; width: 38px; height: 38px; border-radius: 50%; align-items: center; justify-content: center; color: #0F172A; text-decoration: none; flex-shrink: 0; }
    .ch-back:hover { background: #F1F5F9; }
    .ch-mh .who { min-width: 0; flex: 1; }
    .ch-mh .who a { font-weight: 800; color: #0F172A; text-decoration: none; font-size: 16px; }
    .ch-mh .who a:hover { text-decoration: underline; }
    .ch-mh .who div { font-size: 12.5px; color: #64748B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ch-mh .prof { margin-left: auto; height: 36px; padding: 0 14px; border-radius: 10px; border: 1px solid #DCE5EF; color: #0F172A; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
    .ch-mh .prof:hover { background: #F1F5F9; }
    .ch-msgs { flex: 1; overflow-y: auto; padding: 18px 18px 8px; background: #FAFCFE; display: flex; flex-direction: column; gap: 3px; }
    .ch-older { align-self: center; margin-bottom: 10px; border: 1px solid #DCE5EF; background: #fff; border-radius: 999px; padding: 6px 14px; font-size: 12.5px; font-weight: 700; color: #334155; cursor: pointer; font-family: inherit; }
    .ch-day { align-self: center; font-size: 11.5px; font-weight: 700; color: #64748B; background: #EEF2F7; border-radius: 999px; padding: 3px 12px; margin: 12px 0 8px; }
    .ch-intro { align-self: center; text-align: center; max-width: 420px; font-size: 13px; color: #64748B; background: #fff; border: 1px dashed #CFE3F0; border-radius: 14px; padding: 14px 16px; margin: 6px 0 12px; line-height: 1.6; }
    .ch-intro b { color: #0F172A; }
    .ch-m { max-width: min(72%, 560px); display: flex; flex-direction: column; }
    .ch-m .bb { padding: 9px 13px; border-radius: 18px; font-size: 14.5px; line-height: 1.45; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere; }
    .ch-m .bb a { color: inherit; text-decoration: underline; font-weight: 600; }
    .ch-m .tm { font-size: 10.5px; color: #94A3B8; margin: 2px 8px 6px; }
    .ch-m.them { align-self: flex-start; }
    .ch-m.them .bb { background: #fff; color: #0F172A; border: 1px solid #E5ECF3; border-bottom-left-radius: 6px; }
    .ch-m.me { align-self: flex-end; align-items: flex-end; }
    .ch-m.me .bb { background: #00CFFF; color: #fff; border-bottom-right-radius: 6px; }
    .ch-m.me .bb a { color: #fff; }
    .ch-m.pending .bb { opacity: .6; }
    .ch-m.failed .bb { background: #FEE2E2; color: #991B1B; }
    .ch-seen { align-self: flex-end; font-size: 11px; color: #64748B; margin: -2px 8px 4px; }
    .ch-form { display: flex; gap: 10px; align-items: flex-end; padding: 12px 14px; border-top: 1px solid #EAEFF5; background: #fff; }
    .ch-form textarea { flex: 1; resize: none; border: 1px solid #DCE5EF; border-radius: 20px; padding: 10px 16px; font-size: 14.5px; font-family: inherit; line-height: 1.4; max-height: 140px; outline: none; background: #F6F9FC; color: #0F172A; }
    .ch-form textarea:focus { border-color: #00CFFF; background: #fff; }
    .ch-form button { width: 44px; height: 44px; border-radius: 50%; border: none; background: #00CFFF; color: #fff; font-size: 17px; cursor: pointer; flex-shrink: 0; transition: background .2s; }
    .ch-form button:hover { background: #00B5E0; }
    .ch-form button:disabled { opacity: .5; cursor: default; }
    .ch-closed { margin: auto; text-align: center; max-width: 420px; padding: 30px 20px; color: #64748B; font-size: 14.5px; line-height: 1.65; }
    .ch-closed i { font-size: 44px; color: #CBD5E1; display: block; margin-bottom: 12px; }
    .ch-closed b { color: #0F172A; }
    .ch-closedbar { background: #FEF3C7; color: #92400E; font-size: 13.5px; font-weight: 600; padding: 12px 16px; text-align: center; border-top: 1px solid #FDE68A; }
    /* ===== অনলাইন ক্লাস ===== */
    .ch-mh .cls-btn { height: 36px; padding: 0 14px; border-radius: 10px; border: none; background: #00CFFF; color: #fff; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-family: inherit; white-space: nowrap; }
    .ch-mh .cls-btn:hover { background: #00B5E0; }
    .ch-mh .cls-btn + .prof { margin-left: 0; }
    .ch-next { display: flex; align-items: center; gap: 12px; padding: 10px 18px; background: #E6F9FF; border-bottom: 1px solid #C9F0FB; font-size: 13.5px; color: #0F172A; }
    .ch-next[hidden] { display: none; }
    .ch-next i.ic { color: #00A8D6; font-size: 18px; }
    .ch-next .tx { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ch-next .tx b { font-weight: 800; }
    .ch-next .live { color: #DC2626; font-weight: 800; }
    .ch-join { display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 16px; border-radius: 9px; background: #00CFFF; color: #fff !important; font-weight: 800; font-size: 13px; text-decoration: none; white-space: nowrap; }
    .ch-join:hover { background: #00B5E0; }
    .ch-join.live { background: #16A34A; } .ch-join.live:hover { background: #15803D; }
    .ch-cc { width: min(360px, 100%); border-radius: 16px; overflow: hidden; border: 1px solid #CFEFFA; background: #fff; box-shadow: 0 4px 14px rgba(15,27,45,.06); }
    .ch-m.me .ch-cc { border-color: #9BE7FA; }
    .ch-cc .hd { display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: linear-gradient(120deg, #08224A, #0A5A86 60%, #00CFFF); color: #fff; }
    .ch-cc .hd i { font-size: 20px; }
    .ch-cc .hd b { display: block; font-size: 14.5px; line-height: 1.3; }
    .ch-cc .hd small { font-size: 11.5px; opacity: .85; }
    .ch-cc .bd { padding: 12px 14px; font-size: 13.5px; color: #334155; display: grid; gap: 6px; }
    .ch-cc .bd i { width: 18px; color: #00A8D6; }
    .ch-cc .st { font-weight: 800; font-size: 12px; }
    .ch-cc .st.soon { color: #D97706; } .ch-cc .st.live { color: #16A34A; } .ch-cc .st.end { color: #64748B; } .ch-cc .st.cx { color: #DC2626; } .ch-cc .st.up { color: #0284C7; }
    .ch-cc .ft { display: flex; gap: 8px; padding: 0 14px 14px; }
    .ch-cc .ft .ch-join { flex: 1; justify-content: center; }
    .ch-cc .ft .cx-btn { height: 34px; padding: 0 12px; border-radius: 9px; border: 1px solid #FECACA; background: #fff; color: #DC2626; font-weight: 700; font-size: 12.5px; cursor: pointer; font-family: inherit; }
    .ch-cc.cancelled .hd { background: #94A3B8; }
    .ch-cc.cancelled b { text-decoration: line-through; }
    .ch-dlg { position: fixed; inset: 0; z-index: 2000; background: rgba(15,23,42,.5); display: none; align-items: center; justify-content: center; padding: 16px; }
    .ch-dlg.open { display: flex; }
    .ch-dlg form { background: #fff; border-radius: 18px; width: min(460px, 100%); max-height: 92vh; overflow-y: auto; padding: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
    .ch-dlg h3 { margin: 0 0 4px; font-size: 19px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 9px; }
    .ch-dlg h3 i { color: #00CFFF; }
    .ch-dlg p.sub { margin: 0 0 16px; font-size: 13px; color: #64748B; }
    .ch-dlg label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin: 12px 0 6px; }
    .ch-dlg input, .ch-dlg select { width: 100%; height: 44px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 12px; font-size: 14.5px; font-family: inherit; color: #0F172A; outline: none; background: #fff; }
    .ch-dlg input:focus, .ch-dlg select:focus { border-color: #00CFFF; box-shadow: 0 0 0 3px rgba(0,207,255,.15); }
    .ch-dlg .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ch-dlg .hint { font-size: 12px; color: #64748B; margin-top: 5px; }
    .ch-dlg .err { color: #DC2626; font-size: 13px; font-weight: 600; margin-top: 12px; min-height: 1px; }
    .ch-dlg .acts { display: flex; gap: 10px; margin-top: 18px; }
    .ch-dlg .acts button { flex: 1; height: 44px; border-radius: 10px; border: none; font-weight: 800; font-size: 14.5px; cursor: pointer; font-family: inherit; }
    .ch-dlg .acts .ok { background: #00CFFF; color: #fff; } .ch-dlg .acts .ok:hover { background: #00B5E0; } .ch-dlg .acts .ok:disabled { opacity: .6; }
    .ch-dlg .acts .no { background: #F1F5F9; color: #334155; }
    /* ===== গ্রুপ ===== */
    .ch-lh .grp-new { margin-top: 10px; width: 100%; height: 38px; border-radius: 10px; border: 1.5px dashed #9BE7FA; background: #F0FCFF; color: #0369A1; font-weight: 800; font-size: 13.5px; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; justify-content: center; gap: 7px; }
    .ch-lh .grp-new:hover { background: #E0F7FE; }
    .ch-av.grp { background: linear-gradient(135deg, #7C3AED, #0EA5E9); }
    .ch-m .from { font-size: 11.5px; font-weight: 700; color: #7C3AED; margin: 0 10px 2px; }
    .ch-mh .mem-btn { height: 36px; padding: 0 12px; border-radius: 10px; border: 1px solid #DCE5EF; background: #fff; color: #334155; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-family: inherit; white-space: nowrap; }
    .gp-list { max-height: 280px; overflow-y: auto; border: 1px solid #E5ECF3; border-radius: 12px; margin-top: 6px; }
    .gp-row { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-top: 1px solid #F1F5F9; cursor: pointer; }
    .gp-row:first-child { border-top: none; }
    .ch-dlg label.gp-row { display: flex; margin: 0; font-size: 14px; font-weight: 600; }
    .ch-dlg .gp-row input[type=checkbox] { width: 18px; height: 18px; padding: 0; }
    .gp-row input[type=checkbox] { width: 18px; height: 18px; flex-shrink: 0; accent-color: #00CFFF; }
    .gp-row .ch-av { width: 34px; height: 34px; font-size: 12px; }
    .gp-row b { flex: 1; font-size: 14px; color: #0F172A; font-weight: 600; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gp-row small { font-size: 11px; font-weight: 800; color: #0369A1; background: #E0F2FE; padding: 2px 8px; border-radius: 999px; }
    .gp-row .rm { border: none; background: #FEE2E2; color: #B91C1C; border-radius: 8px; height: 30px; padding: 0 10px; font-weight: 700; font-size: 12px; cursor: pointer; font-family: inherit; }
    .gp-empty { padding: 14px; text-align: center; color: #94A3B8; font-size: 13px; }
    .gp-sec { font-size: 12.5px; font-weight: 800; color: #64748B; margin: 16px 0 4px; text-transform: uppercase; letter-spacing: .3px; }
    .gp-danger { margin-top: 16px; width: 100%; height: 40px; border-radius: 10px; border: 1px solid #FECACA; background: #fff; color: #B91C1C; font-weight: 700; cursor: pointer; font-family: inherit; }
    .gp-rename { display: flex; gap: 8px; } .gp-rename input { flex: 1; } .gp-rename button { height: 44px; padding: 0 14px; border-radius: 10px; border: none; background: #0F172A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; }
    @media (max-width: 860px) {
        .ch { padding: 0; }
        .ch-wrap { padding: 0; }
        .ch-box { grid-template-columns: minmax(0, 1fr); border-radius: 0; border: none; height: calc(100vh - 80px); height: calc(100dvh - 80px); min-height: 0; }
        .ch-box.has-cur .ch-list { display: none; }
        .ch-box:not(.has-cur) .ch-main { display: none; }
        .ch-back { display: inline-flex; }
        .ch-m { max-width: 84%; }
        .ch-mh { padding: 10px 10px; gap: 8px; }
        .ch-mh .prof span, .ch-mh .cls-btn span { display: none; }
        .ch-mh .cls-btn { padding: 0 12px; }
        .ch-next { padding: 9px 10px; gap: 8px; font-size: 13px; }
        .ch-mh .prof { padding: 0 11px; }
        .ch-msgs { padding: 14px 10px 6px; }
        .ch-form { padding: 10px; }
    }
</style>

<div class="ch">
    <div class="ch-wrap">
        <div class="ch-box<?= ($cur || $closed) ? ' has-cur' : '' ?>" id="chBox">
            <aside class="ch-list">
                <div class="ch-lh">
                    <h1>Messages</h1>
                    <p><?= $role === 'Teacher' ? 'স্টুডেন্ট আপনার অ্যাপ্লিকেশন Accept করলে বা আপনার প্রোফাইল থেকে চ্যাট শুরু করলে এখানে চ্যাট খোলে।' : 'টিচারের অ্যাপ্লিকেশন Accept করলে বা টিচারের প্রোফাইল থেকে চ্যাট আনলক করলে এখানে চ্যাট খোলে।' ?></p>
                    <?php if ($role === 'Teacher'): ?><button type="button" class="grp-new" id="grpNewBtn"><i class="fas fa-users"></i> নতুন গ্রুপ তৈরি করুন</button><?php endif; ?>
                </div>
                <div class="ch-threads" id="chThreads">
                    <?php if (!$threads): ?>
                        <div class="ch-none">
                            <i class="fas fa-comments"></i>
                            এখনো কোনো চ্যাট নেই।<br>
                            <?php if ($role === 'Teacher'): ?>
                                <a href="/Tuition/Circular">Tuition Circular</a> থেকে স্টুডেন্টের পোস্টে Apply করুন — স্টুডেন্ট Accept করলে এখানে কথা বলতে পারবেন।
                            <?php else: ?>
                                <a href="/Tuition/Circular?post=1">টিউশন পোস্ট দিন</a> — টিচাররা Apply করবে। <a href="/User/Dashboard?section=applications">Applications</a> থেকে পছন্দের টিচারকে Accept করলে এখানে কথা বলতে পারবেন।
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($threads as $t): ?>
                            <a class="ch-t<?= $cur && $cur->Key === $t->Key ? ' on' : '' ?><?= $t->Unread ? ' unread' : '' ?>" href="/Chat?c=<?= e($t->Key) ?>" data-id="<?= e($t->Key) ?>">
                                <span class="ch-av<?= $t->Kind === 'grp' ? ' grp' : '' ?>"><?= $t->Kind === 'grp' ? '<i class="fas fa-users"></i>' : e($t->Initials) ?><?php if ($t->OtherPhoto !== ''): ?><img src="<?= e($t->OtherPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                                <span class="ch-tm">
                                    <span class="ch-tn"><b><?= e($t->OtherName) ?></b> <span class="ch-tr"><?= e($t->OtherRole) ?></span><small><?= e(time_ago($t->LastAt)) ?></small></span>
                                    <span class="ch-tl"><?= $t->LastBody !== '' ? ($t->LastMine ? 'আপনি: ' : '') . e(str_preview($t->LastBody, 70)) : e($t->Topic !== '' ? $t->Topic : 'চ্যাট শুরু করুন') ?></span>
                                </span>
                                <span class="dot"><?= (int)$t->Unread ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <section class="ch-main">
                <?php if ($cur): ?>
                    <header class="ch-mh">
                        <a class="ch-back" href="/Chat" aria-label="সব চ্যাট"><i class="fas fa-arrow-left"></i></a>
                        <?php $isGrp = $cur->Kind === 'grp'; ?>
                        <span class="ch-av sm<?= $isGrp ? ' grp' : '' ?>"><?= $isGrp ? '<i class="fas fa-users"></i>' : e($cur->Initials) ?><?php if ($cur->OtherPhoto !== ''): ?><img src="<?= e($cur->OtherPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                        <div class="who">
                            <?php if ($isGrp): ?><b id="grpTitle"><?= e($cur->OtherName) ?></b><?php else: ?><a href="/Profile/Show/<?= (int)$cur->OtherId ?>"><?= e($cur->OtherName) ?></a><?php endif; ?>
                            <div><?= $isGrp ? 'গ্রুপ' : e($cur->OtherRole) ?><?= $cur->Topic !== '' ? ' · ' . e($cur->Topic) : '' ?></div>
                        </div>
                        <?php if (!empty($ViewBag->IsTeacher)): ?><button type="button" class="cls-btn" id="chClsBtn" title="Zoom / Google Meet ক্লাস সেট করুন"><i class="fas fa-video"></i><span>অনলাইন ক্লাস</span></button><?php endif; ?>
                        <?php if ($isGrp): ?><button type="button" class="mem-btn" id="grpMemBtn"><i class="fas fa-users"></i><span>সদস্য</span></button>
                        <?php else: ?><a class="prof" href="/Profile/Show/<?= (int)$cur->OtherId ?>"><i class="fas fa-user"></i><span>প্রোফাইল</span></a><?php endif; ?>
                    </header>
                    <div class="ch-next" id="chNext" hidden></div>
                    <div class="ch-msgs" id="chMsgs" aria-live="polite">
                        <div class="ch-intro">
                            <?php if ($cur->Kind === 'grp'): ?><b><?= e($cur->OtherName) ?></b> গ্রুপ — <?= !empty($ViewBag->IsTeacher) ? 'এখানে মেসেজ দিলে গ্রুপের সব স্টুডেন্ট পাবে।' : 'টিচার এখানে ক্লাসের মেসেজ আর অনলাইন ক্লাসের লিংক দেবেন।' ?><br>
                            <?php elseif ($cur->Kind === 'dir'): ?><b><?= e($cur->OtherName) ?></b> এর সাথে সরাসরি চ্যাট — <?= $role === 'Teacher' ? 'শিক্ষার্থী আপনার প্রোফাইল থেকে চ্যাট শুরু করেছেন।' : 'টিউশনের বিষয়, সময় আর বেতন নিয়ে কথা বলুন।' ?><br>
                            <?php else: ?><b><?= e($cur->OtherName) ?></b> এর সাথে আপনার টিউশন ঠিক হয়েছে<?= $cur->Topic !== '' ? ' (' . e($cur->Topic) . ')' : '' ?>।<br><?php endif; ?>
                            <?= !empty($ViewBag->IsTeacher) ? 'উপরের <b>অনলাইন ক্লাস</b> বাটন দিয়ে Zoom / Google Meet ক্লাস সেট করুন — স্টুডেন্ট Join বাটন পাবে।' : 'টিচার Zoom / Google Meet ক্লাস সেট করলে এখানে <b>Join Class</b> বাটন আসবে।' ?> কেউ খারাপ আচরণ করলে <a href="/Helpdesk">Helpdesk</a> এ জানান।
                        </div>
                    </div>
                    <div class="ch-closedbar" id="chClosedBar" hidden><i class="fas fa-lock"></i> এই চ্যাটটি বন্ধ হয়ে গেছে — অ্যাপ্লিকেশনটি আর Accepted নেই।</div>
                    <form class="ch-form" id="chForm" autocomplete="off">
                        <textarea id="chText" rows="1" maxlength="2000" placeholder="মেসেজ লিখুন..." aria-label="মেসেজ লিখুন"></textarea>
                        <button type="submit" id="chSend" aria-label="পাঠান"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <?php if (!empty($ViewBag->IsTeacher)): ?>
                    <div class="ch-dlg" id="chDlg" role="dialog" aria-modal="true" aria-labelledby="chDlgT">
                        <form id="chClsForm" autocomplete="off" novalidate>
                            <h3 id="chDlgT"><i class="fas fa-video"></i> অনলাইন ক্লাস সেট করুন</h3>
                            <p class="sub">Zoom বা Google Meet-এ মিটিং বানিয়ে লিংকটা এখানে দিন। <?= e($cur->OtherName) ?> চ্যাটে <b>Join Class</b> বাটন পাবে।</p>
                            <label for="clsLink">মিটিং লিংক *</label>
                            <input id="clsLink" name="link" type="url" inputmode="url" maxlength="500" placeholder="https://meet.google.com/abc-defg-hij" required />
                            <div class="hint">শুধু Zoom, Google Meet বা Microsoft Teams এর লিংক।</div>
                            <label for="clsTitle">বিষয় / শিরোনাম</label>
                            <input id="clsTitle" name="title" maxlength="150" placeholder="<?= e($cur->Topic !== '' ? $cur->Topic . ' ক্লাস' : 'যেমন: Math — Algebra') ?>" />
                            <div class="row2">
                                <div><label for="clsDate">তারিখ *</label><input id="clsDate" name="date" type="date" required /></div>
                                <div><label for="clsTime">সময় *</label><input id="clsTime" name="time" type="time" required /></div>
                            </div>
                            <label for="clsDur">কতক্ষণ</label>
                            <select id="clsDur" name="duration"><option value="30">৩০ মিনিট</option><option value="45">৪৫ মিনিট</option><option value="60" selected>১ ঘণ্টা</option><option value="90">১.৫ ঘণ্টা</option><option value="120">২ ঘণ্টা</option></select>
                            <div class="err" id="clsErr"></div>
                            <div class="acts"><button type="button" class="no" id="clsNo">বাতিল</button><button type="submit" class="ok" id="clsOk"><i class="fas fa-paper-plane"></i> ক্লাস পাঠান</button></div>
                        </form>
                    </div>
                    <?php endif; ?>
                <?php elseif ($closed): ?>
                    <header class="ch-mh"><a class="ch-back" href="/Chat" aria-label="সব চ্যাট"><i class="fas fa-arrow-left"></i></a><div class="who"><b>Messages</b></div></header>
                    <div class="ch-closed">
                        <i class="fas fa-lock"></i>
                        <b>এই চ্যাটটি এখন বন্ধ।</b><br>
                        অ্যাপ্লিকেশনটি Reject করা হয়েছে বা আর Accepted নেই — তাই এখানে আর কথা বলা যাবে না।
                    </div>
                <?php else: ?>
                    <div class="ch-closed">
                        <i class="fas fa-comment-dots" style="color:#B9E9F7;"></i>
                        <?= $threads ? '<b>বাঁ পাশ থেকে একটা চ্যাট বেছে নিন।</b>' : '<b>এখনো কোনো চ্যাট নেই।</b><br>টিউশন ঠিক হলে (অ্যাপ্লিকেশন Accept হলে) এখানে টিচার আর স্টুডেন্ট কথা বলতে পারবেন।' ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php if ($role === 'Teacher'): $contacts = $ViewBag->Contacts ?? []; ?>
<div class="ch-dlg" id="grpDlg" role="dialog" aria-modal="true" aria-labelledby="grpDlgT">
    <form id="grpForm" autocomplete="off" novalidate>
        <h3 id="grpDlgT"><i class="fas fa-users"></i> নতুন গ্রুপ</h3>
        <p class="sub">আপনার স্টুডেন্টদের নিয়ে গ্রুপ বানান — এক মেসেজে সবাইকে জানাতে আর একসাথে অনলাইন ক্লাসের লিংক দিতে পারবেন।</p>
        <label for="grpName">গ্রুপের নাম *</label>
        <input id="grpName" name="name" maxlength="80" placeholder="যেমন: HSC Physics ব্যাচ — সন্ধ্যা ৬টা" required />
        <label>স্টুডেন্ট বাছাই করুন *</label>
        <div class="gp-list">
            <?php if (!$contacts): ?><div class="gp-empty">এখনো কোনো স্টুডেন্টের সাথে চ্যাট নেই — স্টুডেন্ট আপনার অ্যাপ্লিকেশন Accept করলে বা চ্যাট শুরু করলে এখানে আসবে।</div><?php endif; ?>
            <?php foreach ($contacts as $c): ?>
                <label class="gp-row"><input type="checkbox" name="members[]" value="<?= (int)$c->Id ?>" />
                    <span class="ch-av"><?= e($c->Initials) ?><?php if ($c->Photo !== ''): ?><img src="<?= e($c->Photo) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span><b><?= e($c->Name) ?></b></label>
            <?php endforeach; ?>
        </div>
        <div class="err" id="grpErr"></div>
        <div class="acts"><button type="button" class="no" data-close>বাতিল</button><button type="submit" class="ok" id="grpOk"<?= $contacts ? '' : ' disabled' ?>><i class="fas fa-check"></i> গ্রুপ তৈরি করুন</button></div>
    </form>
</div>
<?php endif; ?>
<?php if ($cur && $cur->Kind === 'grp'): ?>
<div class="ch-dlg" id="memDlg" role="dialog" aria-modal="true" aria-labelledby="memDlgT">
    <form id="memForm" autocomplete="off" novalidate>
        <h3 id="memDlgT"><i class="fas fa-users"></i> গ্রুপের সদস্য</h3>
        <div id="memBody"><div class="gp-empty">লোড হচ্ছে...</div></div>
        <div class="err" id="memErr"></div>
        <div class="acts"><button type="button" class="no" data-close>বন্ধ করুন</button></div>
    </form>
</div>
<?php endif; ?>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var token = function () { var i = document.querySelector('input[name="__RequestVerificationToken"]'); return i ? i.value : ''; };
    var threadsBox = document.getElementById('chThreads');
    var CUR = <?= js($cur ? $cur->Key : '') ?>;

    // ===== তালিকা রিফ্রেশ (নতুন মেসেজ / নতুন চ্যাট) =====
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
    function drawThreads(list) {
        if (!list.length) return;
        threadsBox.innerHTML = list.map(function (t) {
            var cls = 'ch-t' + (t.id === CUR ? ' on' : '') + (t.unread && t.id !== CUR ? ' unread' : '');
            var av = t.group ? '<span class="ch-av grp"><i class="fas fa-users"></i></span>' : '<span class="ch-av">' + esc(t.initials) + (t.photo ? '<img src="' + esc(t.photo) + '" alt="" onerror="this.remove()" />' : '') + '</span>';
            var last = t.last ? (t.lastMine ? 'আপনি: ' : '') + esc(t.last) : esc(t.topic || 'চ্যাট শুরু করুন');
            return '<a class="' + cls + '" href="/Chat?c=' + encodeURIComponent(t.id) + '" data-id="' + esc(t.id) + '">' + av + '<span class="ch-tm"><span class="ch-tn"><b>' + esc(t.name) + '</b> <span class="ch-tr">' + esc(t.role)
                + '</span><small>' + esc(t.ago) + '</small></span><span class="ch-tl">' + last + '</span></span><span class="dot">' + (t.unread || 0) + '</span></a>';
        }).join('');
    }
    async function refreshThreads() {
        if (document.hidden) return;
        try { var r = await (await fetch('/Chat/Threads', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); if (r.success) drawThreads(r.threads); } catch (e) { }
    }
    setInterval(refreshThreads, 15000);

    // ===== গ্রুপ: তৈরি / সদস্য =====
    function gpost(url, fd) { fd.append('__RequestVerificationToken', token()); return fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); }); }
    document.querySelectorAll('.ch-dlg [data-close]').forEach(function (b) { b.addEventListener('click', function () { b.closest('.ch-dlg').classList.remove('open'); }); });
    document.querySelectorAll('.ch-dlg').forEach(function (d) { d.addEventListener('click', function (e) { if (e.target === d) d.classList.remove('open'); }); });
    var gNew = document.getElementById('grpNewBtn'), gDlg = document.getElementById('grpDlg');
    if (gNew && gDlg) {
        gNew.addEventListener('click', function () { gDlg.classList.add('open'); document.getElementById('grpName').focus(); });
        document.getElementById('grpForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            var f = e.target, err = document.getElementById('grpErr'), ok = document.getElementById('grpOk');
            if (!f.name.value.trim()) { err.textContent = 'গ্রুপের নাম দিন।'; f.name.focus(); return; }
            if (!f.querySelector('input[name="members[]"]:checked')) { err.textContent = 'অন্তত একজন স্টুডেন্ট বাছাই করুন।'; return; }
            ok.disabled = true; err.textContent = '';
            try { var r = await gpost('/Chat/GroupCreate', new FormData(f)); if (!r.success) { err.textContent = r.message || 'তৈরি করা যায়নি।'; ok.disabled = false; return; } location.href = r.redirect; }
            catch (x) { err.textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।'; ok.disabled = false; }
        });
    }
    var mBtn = document.getElementById('grpMemBtn'), mDlg = document.getElementById('memDlg');
    if (mBtn && mDlg) {
        var mBody = document.getElementById('memBody'), mErr = document.getElementById('memErr');
        var av = function (u) { return '<span class="ch-av">' + esc(u.initials) + (u.photo ? '<img src="' + esc(u.photo) + '" alt="" onerror="this.remove()" />' : '') + '</span>'; };
        var loadMembers = async function () {
            mErr.textContent = '';
            var r = await (await fetch('/Chat/GroupInfo?c=' + encodeURIComponent(CUR), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!r.success) { mBody.innerHTML = '<div class="gp-empty">' + esc(r.message || 'পাওয়া যায়নি') + '</div>'; return; }
            var h = '';
            if (r.owner) h += '<div class="gp-sec">গ্রুপের নাম</div><div class="gp-rename"><input id="gpNameIn" maxlength="80" value="' + esc(r.name) + '" /><button type="button" id="gpRename">Save</button></div>';
            h += '<div class="gp-sec">সদস্য (' + r.members.length + ')</div><div class="gp-list">' + r.members.map(function (u) {
                return '<div class="gp-row">' + av(u) + '<b>' + esc(u.name) + '</b>' + (u.teacher ? '<small>Teacher</small>' : (r.owner ? '<button type="button" class="rm" data-rm="' + u.id + '">সরান</button>' : '')) + '</div>';
            }).join('') + '</div>';
            if (r.owner) {
                h += '<div class="gp-sec">আরো স্টুডেন্ট যোগ করুন</div><div class="gp-list">' + (r.canAdd.length ? r.canAdd.map(function (u) {
                    return '<label class="gp-row"><input type="checkbox" name="add" value="' + u.id + '" />' + av(u) + '<b>' + esc(u.name) + '</b></label>';
                }).join('') : '<div class="gp-empty">আপনার সব স্টুডেন্ট এই গ্রুপে আছে।</div>') + '</div>';
                if (r.canAdd.length) h += '<div class="acts" style="margin-top:10px;"><button type="button" class="ok" id="gpAdd"><i class="fas fa-user-plus"></i> যোগ করুন</button></div>';
                h += '<button type="button" class="gp-danger" id="gpDel"><i class="fas fa-trash"></i> গ্রুপ মুছে দিন</button>';
            }
            mBody.innerHTML = h;
        };
        mBtn.addEventListener('click', function () { mDlg.classList.add('open'); loadMembers(); });
        mBody.addEventListener('click', async function (e) {
            var fd = new FormData(); fd.append('c', CUR); var r = null;
            try {
                if (e.target.closest('#gpRename')) { fd.append('name', document.getElementById('gpNameIn').value); r = await gpost('/Chat/GroupRename', fd); if (r.success) document.getElementById('grpTitle').textContent = document.getElementById('gpNameIn').value.trim(); }
                else if (e.target.closest('[data-rm]')) { if (!confirm('এই স্টুডেন্টকে গ্রুপ থেকে সরাবেন?')) return; fd.append('userId', e.target.closest('[data-rm]').getAttribute('data-rm')); r = await gpost('/Chat/GroupRemove', fd); }
                else if (e.target.closest('#gpAdd')) { mBody.querySelectorAll('input[name="add"]:checked').forEach(function (c) { fd.append('members[]', c.value); }); r = await gpost('/Chat/GroupAdd', fd); }
                else if (e.target.closest('#gpDel')) { if (!confirm('গ্রুপটি আর এর সব মেসেজ মুছে যাবে। নিশ্চিত?')) return; r = await gpost('/Chat/GroupDelete', fd); if (r.success) { location.href = r.redirect; return; } }
                else return;
                if (!r.success) { mErr.textContent = r.message || 'হয়নি।'; return; }
                loadMembers();
            } catch (x) { mErr.textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।'; }
        });
    }

    if (!CUR) return;

    // ===== কথোপকথন =====
    var box = document.getElementById('chMsgs'), form = document.getElementById('chForm'), ta = document.getElementById('chText'), send = document.getElementById('chSend');
    var closedBar = document.getElementById('chClosedBar');
    var lastId = 0, firstId = 0, lastDay = '', seenUpTo = <?= (int)$ViewBag->SeenUpTo ?>, seen = {}, closed = false, busy = false;
    var URL_RE = /(https?:\/\/[^\s<>"']+)/g;

    function dayLabel(d) {
        var t = new Date(), y = new Date(Date.now() - 864e5);
        var f = function (x) { return x.getFullYear() + '-' + String(x.getMonth() + 1).padStart(2, '0') + '-' + String(x.getDate()).padStart(2, '0'); };
        if (d === f(t)) return 'আজ'; if (d === f(y)) return 'গতকাল';
        var p = d.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }
    // লেখা নিরাপদে বসানো — লিংক থাকলে (যেমন Zoom/Meet) ক্লিক করা যায়
    function fill(el, text) {
        var parts = String(text).split(URL_RE);
        parts.forEach(function (p, i) {
            if (i % 2 === 1) { var a = document.createElement('a'); a.href = p; a.textContent = p; a.target = '_blank'; a.rel = 'noopener nofollow ugc'; el.appendChild(a); }
            else if (p) el.appendChild(document.createTextNode(p));
        });
    }
    // ===== অনলাইন ক্লাস =====
    var CLASSES = {}, IS_TEACHER = <?= js(!empty($ViewBag->IsTeacher)) ?>, nextBar = document.getElementById('chNext');
    var PICON = { 'Zoom': 'fas fa-video', 'Google Meet': 'fas fa-video', 'Microsoft Teams': 'fas fa-users' };
    function clsState(c) {
        if (c.status === 'Cancelled') return ['cx', 'বাতিল করা হয়েছে'];
        var now = Date.now(), s = Date.parse(c.start), e = Date.parse(c.end);
        if (now >= e) return ['end', 'ক্লাস শেষ'];
        if (now >= s) return ['live', '● এখন চলছে'];
        if (s - now <= 15 * 60000) return ['soon', 'কিছুক্ষণের মধ্যে শুরু হবে'];
        return ['up', 'আসন্ন ক্লাস'];
    }
    function setClasses(list) { (list || []).forEach(function (c) { CLASSES[c.id] = c; }); drawCards(); drawNext(); }
    function drawCards() { box.querySelectorAll('.ch-cc[data-cls]').forEach(function (el) { var c = CLASSES[el.getAttribute('data-cls')]; if (c) el.replaceWith(card(c)); }); }
    function card(c) {
        var st = clsState(c), el = document.createElement('div'); el.className = 'ch-cc' + (c.status === 'Cancelled' ? ' cancelled' : ''); el.setAttribute('data-cls', c.id);
        var hd = document.createElement('div'); hd.className = 'hd'; hd.innerHTML = '<i class="' + (PICON[c.platform] || 'fas fa-video') + '"></i>';
        var t = document.createElement('div'); var b = document.createElement('b'); b.textContent = c.title; var sm = document.createElement('small'); sm.textContent = 'অনলাইন ক্লাস · ' + c.platform; t.appendChild(b); t.appendChild(sm); hd.appendChild(t); el.appendChild(hd);
        var bd = document.createElement('div'); bd.className = 'bd';
        var l1 = document.createElement('div'); l1.innerHTML = '<i class="far fa-calendar"></i> '; l1.appendChild(document.createTextNode(dayLabel(c.day) + ', ' + c.time));
        var l2 = document.createElement('div'); l2.innerHTML = '<i class="far fa-clock"></i> '; l2.appendChild(document.createTextNode(c.dur + ' মিনিট'));
        var l3 = document.createElement('div'); l3.className = 'st ' + st[0]; l3.textContent = st[1];
        bd.appendChild(l1); bd.appendChild(l2); bd.appendChild(l3); el.appendChild(bd);
        if (st[0] !== 'cx' && st[0] !== 'end') {
            var ft = document.createElement('div'); ft.className = 'ft';
            var j = document.createElement('a'); j.className = 'ch-join' + (st[0] === 'live' ? ' live' : ''); j.href = c.link; j.target = '_blank'; j.rel = 'noopener nofollow';
            j.innerHTML = '<i class="fas fa-right-to-bracket"></i> Join Class'; ft.appendChild(j);
            if (IS_TEACHER) { var x = document.createElement('button'); x.type = 'button'; x.className = 'cx-btn'; x.textContent = 'বাতিল'; x.setAttribute('data-cancel', c.id); ft.appendChild(x); }
            el.appendChild(ft);
        }
        return el;
    }
    function drawNext() {
        if (!nextBar) return;
        var now = Date.now(), list = Object.keys(CLASSES).map(function (k) { return CLASSES[k]; })
            .filter(function (c) { return c.status !== 'Cancelled' && Date.parse(c.end) > now; })
            .sort(function (a, b) { return Date.parse(a.start) - Date.parse(b.start); });
        if (!list.length) { nextBar.hidden = true; nextBar.innerHTML = ''; return; }
        var c = list[0], st = clsState(c);
        nextBar.innerHTML = '<i class="fas fa-calendar-check ic"></i><span class="tx"></span>';
        var tx = nextBar.querySelector('.tx');
        if (st[0] === 'live') { var lv = document.createElement('span'); lv.className = 'live'; lv.textContent = '● এখন ক্লাস চলছে: '; tx.appendChild(lv); }
        else tx.appendChild(document.createTextNode('পরের ক্লাস: '));
        var b = document.createElement('b'); b.textContent = dayLabel(c.day) + ', ' + c.time; tx.appendChild(b);
        tx.appendChild(document.createTextNode(' · ' + c.title));
        var j = document.createElement('a'); j.className = 'ch-join' + (st[0] === 'live' ? ' live' : ''); j.href = c.link; j.target = '_blank'; j.rel = 'noopener nofollow'; j.innerHTML = '<i class="fas fa-right-to-bracket"></i> Join';
        nextBar.appendChild(j); nextBar.hidden = false;
    }
    setInterval(function () { drawCards(); drawNext(); }, 30000);

    function bubble(m) {
        var w = document.createElement('div'); w.className = 'ch-m ' + (m.mine ? 'me' : 'them'); w.setAttribute('data-id', m.id || '');
        if (m.cls && CLASSES[m.cls]) { w.appendChild(card(CLASSES[m.cls])); var tm0 = document.createElement('div'); tm0.className = 'tm'; tm0.textContent = m.time || ''; w.appendChild(tm0); return w; }
        if (m.from && !m.mine) { var fr = document.createElement('div'); fr.className = 'from'; fr.textContent = m.from; w.appendChild(fr); }
        var b = document.createElement('div'); b.className = 'bb'; fill(b, m.body); w.appendChild(b);
        var tm = document.createElement('div'); tm.className = 'tm'; tm.textContent = m.time || ''; w.appendChild(tm);
        return w;
    }
    function nearBottom() { return box.scrollHeight - box.scrollTop - box.clientHeight < 120; }
    function toBottom() { box.scrollTop = box.scrollHeight; }
    function append(list) {
        var stick = nearBottom();
        list.forEach(function (m) {
            if (seen[m.id]) return; seen[m.id] = 1;
            if (m.day !== lastDay) { var d = document.createElement('div'); d.className = 'ch-day'; d.textContent = dayLabel(m.day); box.appendChild(d); lastDay = m.day; }
            box.appendChild(bubble(m));
            lastId = Math.max(lastId, m.id); if (!firstId || m.id < firstId) firstId = m.id;
        });
        drawSeen();
        if (stick || list.some(function (m) { return m.mine; })) toBottom();
    }
    function drawSeen() {
        var old = box.querySelector('.ch-seen'); if (old) old.remove();
        var mine = box.querySelectorAll('.ch-m.me[data-id]'); if (!mine.length) return;
        var last = mine[mine.length - 1]; if (+last.getAttribute('data-id') > seenUpTo || box.lastElementChild !== last) return;
        var s = document.createElement('div'); s.className = 'ch-seen'; s.textContent = 'Seen'; last.after(s);
    }
    function setClosed() {
        if (closed) return; closed = true;
        closedBar.hidden = false; form.remove();
    }

    // প্রথম মেসেজগুলো
    setClasses(<?= js($ViewBag->Classes ?? []) ?>);
    append(<?= js($ViewBag->Messages) ?>); toBottom();
    <?php if (!empty($ViewBag->HasOlder)): ?>
    (function () {
        var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'ch-older'; btn.textContent = 'আগের মেসেজ দেখুন';
        var intro = box.querySelector('.ch-intro'); box.insertBefore(btn, intro ? intro.nextSibling : box.firstChild);
        btn.addEventListener('click', async function () {
            btn.disabled = true; btn.textContent = 'লোড হচ্ছে...';
            try {
                var r = await (await fetch('/Chat/Messages?c=' + encodeURIComponent(CUR) + '&before=' + firstId)).json();
                if (!r.success) { if (r.closed) setClosed(); return; }
                var h = box.scrollHeight, frag = document.createDocumentFragment(), d0 = '';
                r.messages.forEach(function (m) {
                    if (seen[m.id]) return; seen[m.id] = 1;
                    if (m.day !== d0) { var d = document.createElement('div'); d.className = 'ch-day'; d.textContent = dayLabel(m.day); frag.appendChild(d); d0 = m.day; }
                    frag.appendChild(bubble(m)); if (m.id < firstId) firstId = m.id;
                });
                // একই দিনের লেবেল দুবার না দেখাতে
                var nextDay = btn.nextElementSibling; if (nextDay && nextDay.classList.contains('ch-day') && nextDay.textContent === dayLabel(d0)) nextDay.remove();
                btn.after(frag); box.scrollTop = box.scrollHeight - h;
                if (r.hasOlder) { btn.disabled = false; btn.textContent = 'আগের মেসেজ দেখুন'; } else btn.remove();
            } catch (e) { btn.disabled = false; btn.textContent = 'আবার চেষ্টা করুন'; }
        });
    })();
    <?php endif; ?>

    // নতুন মেসেজ — কয়েক সেকেন্ড পরপর
    async function poll() {
        if (closed || busy || document.hidden) return;
        busy = true;
        try {
            var r = await (await fetch('/Chat/Messages?c=' + encodeURIComponent(CUR) + '&after=' + lastId + '&read=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!r.success) { if (r.closed) setClosed(); return; }
            seenUpTo = r.seenUpTo || seenUpTo; setClasses(r.classes); append(r.messages);
        } catch (e) { } finally { busy = false; }
    }
    setInterval(poll, 3000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { poll(); refreshThreads(); } });

    // পাঠানো
    ta.addEventListener('input', function () { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 140) + 'px'; });
    ta.addEventListener('keydown', function (e) {
        // ডেস্কটপে Enter = পাঠান, Shift+Enter = নতুন লাইন (মোবাইলে Enter নতুন লাইন)
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing && window.matchMedia('(hover: hover)').matches) { e.preventDefault(); form.requestSubmit(); }
    });
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var text = ta.value.trim(); if (!text || closed) return;
        var temp = bubble({ mine: true, body: text, time: '...' }); temp.classList.add('pending'); box.appendChild(temp); toBottom();
        ta.value = ''; ta.style.height = ''; ta.focus();
        var fd = new FormData(); fd.append('c', CUR); fd.append('body', text); fd.append('after', lastId); fd.append('__RequestVerificationToken', token());
        try {
            var r = await (await fetch('/Chat/Send', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!r.success) {
                temp.classList.remove('pending'); temp.classList.add('failed'); temp.querySelector('.tm').textContent = r.message || 'পাঠানো যায়নি';
                if (r.closed) setClosed(); else if (!ta.value) ta.value = text;
                return;
            }
            temp.remove(); append(r.messages);
        } catch (er) { temp.classList.remove('pending'); temp.classList.add('failed'); temp.querySelector('.tm').textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন'; if (!ta.value) ta.value = text; }
    });
    // ===== টিচার: ক্লাস সেট / বাতিল =====
    var dlg = document.getElementById('chDlg'), cf = document.getElementById('chClsForm');
    async function post(url, data) {
        var fd = new FormData(); for (var k in data) fd.append(k, data[k]);
        fd.append('c', CUR); fd.append('after', lastId); fd.append('__RequestVerificationToken', token());
        return (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
    }
    if (dlg) {
        var pad = function (n) { return String(n).padStart(2, '0'); };
        var openDlg = function () {
            var d = new Date(Date.now() + 3600000); d.setMinutes(0);
            if (!cf.date.value) cf.date.value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
            if (!cf.time.value) cf.time.value = pad(d.getHours()) + ':00';
            var t = new Date(); cf.date.min = t.getFullYear() + '-' + pad(t.getMonth() + 1) + '-' + pad(t.getDate());
            document.getElementById('clsErr').textContent = ''; dlg.classList.add('open'); cf.link.focus();
        };
        var closeDlg = function () { dlg.classList.remove('open'); };
        document.getElementById('chClsBtn').addEventListener('click', openDlg);
        document.getElementById('clsNo').addEventListener('click', closeDlg);
        dlg.addEventListener('click', function (e) { if (e.target === dlg) closeDlg(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && dlg.classList.contains('open')) closeDlg(); });
        cf.addEventListener('submit', async function (e) {
            e.preventDefault();
            var err = document.getElementById('clsErr'), ok = document.getElementById('clsOk');
            if (!/^https:\/\/([a-z0-9-]+\.)*(zoom\.us|meet\.google\.com|teams\.microsoft\.com|teams\.live\.com)(\/|$)/i.test(cf.link.value.trim())) { err.textContent = 'সঠিক Zoom, Google Meet বা Microsoft Teams লিংক দিন (https:// দিয়ে শুরু)।'; cf.link.focus(); return; }
            if (!cf.date.value || !cf.time.value) { err.textContent = 'তারিখ আর সময় দিন।'; return; }
            ok.disabled = true; err.textContent = '';
            try {
                var r = await post('/Chat/CreateClass', { link: cf.link.value.trim(), title: cf.elements['title'].value.trim(), date: cf.date.value, time: cf.time.value, duration: cf.duration.value });
                if (!r.success) { err.textContent = r.message || 'সেট করা যায়নি।'; if (r.closed) { closeDlg(); setClosed(); } return; }
                setClasses(r.classes); append(r.messages); cf.reset(); closeDlg();
            } catch (er) { err.textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।'; }
            finally { ok.disabled = false; }
        });
    }
    box.addEventListener('click', async function (e) {
        var x = e.target.closest('[data-cancel]'); if (!x || !IS_TEACHER) return;
        if (!confirm('এই ক্লাসটি বাতিল করবেন? স্টুডেন্টকে জানানো হবে।')) return;
        x.disabled = true;
        try { var r = await post('/Chat/CancelClass', { id: x.getAttribute('data-cancel') }); if (!r.success) { alert(r.message || 'বাতিল করা যায়নি।'); x.disabled = false; return; } setClasses(r.classes); append(r.messages); }
        catch (er) { x.disabled = false; }
    });
    if (window.matchMedia('(hover: hover)').matches) ta.focus();
})();
</script>
<?php section_end(); ?>
