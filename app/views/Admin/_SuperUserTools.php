<?php
/**
 * শুধু Super Admin: কারো প্যাকেজের মেয়াদ বাড়ানো আর রোল বদল (টিচার / শিক্ষার্থীর সুবিধা যাচাইয়ের জন্য)।
 * দরকার: $user (Obj — Id, Role, FullName)
 */
if (!admin_can('super') || empty($user)) return;
$st = db()->GetUserPackageState((int)$user->Id);
$audBn = ['Teacher' => 'টিচার প্যাকেজ', 'Student' => 'শিক্ষার্থী প্যাকেজ'];
?>
<style>
    .sut { background: #fff; border: 1.5px solid #E9D5FF; border-radius: 16px; padding: 16px 18px; margin: 16px 0; box-shadow: 0 4px 16px rgba(124,58,237,.06); }
    .sut h4 { margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #5B21B6; }
    .sut .sub { margin: 0 0 12px; font-size: 13px; color: #64748B; }
    .sut-g { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; }
    .sut-box { border: 1px solid #EEF2F7; border-radius: 12px; padding: 12px; background: #FBFAFF; }
    .sut-box b.t { display: block; font-size: 14px; color: #1E293B; margin-bottom: 8px; }
    .sut-st { font-size: 13px; color: #475569; margin-bottom: 4px; }
    .sut-st .on { color: #15803D; font-weight: 700; } .sut-st .off { color: #B91C1C; font-weight: 700; }
    .sut-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .sut-row select, .sut-row input { height: 38px; border: 1.5px solid #DCE7F0; border-radius: 9px; padding: 0 8px; font-family: inherit; font-size: 13.5px; background: #fff; }
    .sut-row input { width: 90px; }
    .sut-row button { height: 38px; padding: 0 14px; border: none; border-radius: 9px; background: #7C3AED; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; }
    .sut-msg { margin-top: 8px; font-size: 13px; font-weight: 700; }
</style>
<div class="sut" data-sut="<?= (int)$user->Id ?>">
    <?= csrf_field() ?>
    <h4><i class="fas fa-user-shield"></i> Super Admin টুলস</h4>
    <p class="sub">শুধু আপনি (Super Admin) দেখছেন। প্যাকেজের মেয়াদ বাড়ানো আর রোল বদল — টিচার / শিক্ষার্থীর সুবিধা যাচাই করার জন্য।</p>
    <div class="sut-g">
        <div class="sut-box">
            <b class="t"><i class="fas fa-calendar-plus" style="color:#7C3AED;"></i> প্যাকেজের মেয়াদ বাড়ান</b>
            <?php foreach ($audBn as $a => $lb): $s = $st[$a] ?? null; ?>
                <div class="sut-st"><?= $lb ?>:
                    <?php if (!$s): ?><span class="off">নেই</span>
                    <?php elseif ($s['ends'] === null): ?><span class="on">চালু (মেয়াদহীন)</span>
                    <?php else: ?><span class="<?= $s['active'] ? 'on' : 'off' ?>"><?= $s['active'] ? 'চালু' : 'মেয়াদ শেষ' ?> — <?= e(date('d M Y', strtotime((string)$s['ends']))) ?><?= $s['active'] ? ' পর্যন্ত' : '' ?></span><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="sut-row">
                <select data-k="aud"><option value="<?= $user->Role === 'Teacher' ? 'Teacher' : 'Student' ?>"><?= $user->Role === 'Teacher' ? 'টিচার প্যাকেজ' : 'শিক্ষার্থী প্যাকেজ' ?></option><option value="<?= $user->Role === 'Teacher' ? 'Student' : 'Teacher' ?>"><?= $user->Role === 'Teacher' ? 'শিক্ষার্থী প্যাকেজ' : 'টিচার প্যাকেজ' ?></option></select>
                <select data-k="preset"><option value="7">৭ দিন</option><option value="30" selected>১ মাস</option><option value="90">৩ মাস</option><option value="180">৬ মাস</option><option value="365">১ বছর</option><option value="0">নিজে লিখুন</option></select>
                <input type="number" data-k="days" min="1" max="3650" placeholder="দিন" hidden />
                <button type="button" data-act="extend"><i class="fas fa-plus"></i> বাড়ান</button>
            </div>
            <div class="sut-msg" data-msg="extend"></div>
        </div>
        <div class="sut-box">
            <b class="t"><i class="fas fa-user-gear" style="color:#7C3AED;"></i> রোল বদলান</b>
            <div class="sut-st">এখন: <b><?= e(role_label($user->Role, (int)$user->Id)) ?></b></div>
            <div class="sut-row">
                <select data-k="role">
                    <?php foreach (['User' => 'শিক্ষার্থী / ইউজার', 'Teacher' => 'টিচার', 'Admin' => 'অ্যাডমিন'] as $r => $lb): ?>
                        <option value="<?= $r ?>"<?= $user->Role === $r ? ' selected' : '' ?>><?= $lb ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" data-act="role"><i class="fas fa-check"></i> বদলান</button>
            </div>
            <div class="sut-msg" data-msg="role"></div>
        </div>
    </div>
</div>
<script>
(function () {
    var box = document.querySelector('[data-sut="<?= (int)$user->Id ?>"]');
    if (!box || box.dataset.ready) return; box.dataset.ready = '1';
    var q = function (k) { return box.querySelector('[data-k="' + k + '"]'); };
    q('preset').addEventListener('change', function () { q('days').hidden = this.value !== '0'; if (this.value === '0') q('days').focus(); });
    async function send(url, data, key, ask) {
        if (ask && !confirm(ask)) return;
        var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        var t = box.querySelector('input[name="__RequestVerificationToken"]'); if (t) fd.append('__RequestVerificationToken', t.value);
        var m = box.querySelector('[data-msg="' + key + '"]'); m.style.color = '#64748B'; m.textContent = '...';
        try {
            var r = await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            m.style.color = r.success ? '#15803D' : '#DC2626'; m.textContent = r.message || '';
            if (r.success) setTimeout(function () { location.reload(); }, 1400);
        } catch (e) { m.style.color = '#DC2626'; m.textContent = 'সমস্যা হয়েছে, আবার চেষ্টা করুন।'; }
    }
    box.querySelector('[data-act="extend"]').addEventListener('click', function () {
        var d = q('preset').value === '0' ? q('days').value : q('preset').value;
        send('/Admin/ExtendPackage', { userId: <?= (int)$user->Id ?>, aud: q('aud').value, days: d }, 'extend');
    });
    box.querySelector('[data-act="role"]').addEventListener('click', function () {
        var r = q('role'), lb = r.options[r.selectedIndex].text;
        send('/Admin/ChangeUserRole', { userId: <?= (int)$user->Id ?>, role: r.value }, 'role', 'এই ইউজারের রোল "' + lb + '" করবেন?' + (r.value === 'Admin' ? '\n\nঅ্যাডমিন বানালে সে অ্যাডমিন প্যানেলে ঢুকতে পারবে (কোন পেজ দেখবে Admins পেজ থেকে ঠিক করবেন)।' : ''));
    });
})();
</script>
