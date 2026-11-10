<?php
/** শিক্ষার্থীর Profile Verification ফর্ম — Play / Nursery / KG থেকে University; SSC / HSC এর রেজাল্ট বা Running; ID কার্ডের ছবি */
$k = $Model; $st = $k ? (string)$k->Status : 'None';
$cls = $ViewBag->Classes; $dists = $ViewBag->Districts; $boards = $ViewBag->Boards;
$ViewData['Title'] = 'শিক্ষার্থীর Profile Verification';
$ViewData['Robots'] = 'noindex, nofollow';
$D = $k ? $k->D : [];
$u = db()->GetUserById((int)current_user_id());
$v = fn($key, $def = '') => (string)($D[$key] ?? $def);
$showForm = $st === 'None' || $st === 'Rejected';
$next = (string)($ViewBag->Next ?? '');
$groups = ['প্রাক-প্রাথমিক' => ['play', 'nursery', 'kg'], 'প্রাথমিক (Class 1–5)' => ['c1', 'c2', 'c3', 'c4', 'c5'], 'মাধ্যমিক (Class 6–10)' => ['c6', 'c7', 'c8', 'c9', 'c10'],
    'উচ্চ মাধ্যমিক' => ['c11', 'c12'], 'ডিপ্লোমা / বিশ্ববিদ্যালয়' => ['diploma', 'uni1', 'uni2', 'uni3', 'uni4', 'masters']];
$exam = function ($ex) use ($D) { return is_array($D[$ex] ?? null) ? $D[$ex] : []; };
$examTxt = function ($x, $EX) { if (!$x) return '—'; return ($x['status'] ?? '') === 'running' ? "$EX — Running (এখন পড়ছে)" : "$EX {$x['year']} · GPA {$x['gpa']}" . (!empty($x['board']) ? " · {$x['board']} বোর্ড" : ''); };
?>
<?php partial('Kyc/_Styles'); ?>
<?= csrf_field() ?>
<div class="ky-page">
    <div class="ky-wrap">
        <h1 class="ky-h1"><i class="fas fa-id-card" style="color:#0EA5E9;"></i> শিক্ষার্থীর তথ্য ও পরিচয় যাচাই (Profile Verification)</h1>
        <p class="ky-sub">প্যাকেজ কিনতে আর টিউশন পোস্ট করতে শিক্ষার্থীর তথ্য যাচাই করা লাগে। Play / Nursery / KG থেকে বিশ্ববিদ্যালয় — সবাই এই ফর্ম পূরণ করতে পারবে। ছোটদের ক্ষেত্রে বাবা / মা পূরণ করে দিন।</p>

        <?php if ($st === 'Pending'): ?>
            <div class="ky-state p"><i class="fas fa-hourglass-half"></i><div><b>ফর্ম যাচাই হচ্ছে</b><p>আপনার তথ্য অ্যাডমিনের কাছে আছে। Approve হলে নোটিফিকেশন পাবেন — তখন প্যাকেজ কিনতে পারবেন।</p></div></div>
        <?php elseif ($st === 'Approved'): ?>
            <div class="ky-state ok"><i class="fas fa-circle-check"></i><div><b>আপনার Profile Verification Approve হয়েছে</b><p>এখন প্যাকেজ কিনতে ও সব সুবিধা ব্যবহার করতে পারবেন।<?php if ($next !== ''): ?> <a href="<?= e($next) ?>" style="font-weight:800;color:inherit;">চালিয়ে যান →</a><?php endif; ?></p></div></div>
        <?php elseif ($st === 'Rejected'): ?>
            <div class="ky-state no"><i class="fas fa-circle-xmark"></i><div><b>ফর্মটা গ্রহণ করা হয়নি</b><p><?= !is_blank($k->AdminNote) ? 'কারণ: ' . e($k->AdminNote) . '<br>' : '' ?>নিচে তথ্য ঠিক করে আবার জমা দিন।</p></div></div>
        <?php endif; ?>

        <?php if (!$showForm): ?>
            <div class="ky-card">
                <h3><i class="fas fa-user-graduate" style="color:#0EA5E9;"></i> জমা দেওয়া তথ্য</h3>
                <div class="ky-sum">
                    <div><small>নাম</small><b><?= e($v('fullName')) ?></b></div>
                    <div><small>ক্লাস</small><b><?= e($cls[$v('classLevel')] ?? '') ?><?= $v('group') !== '' ? ' · ' . e($v('group')) : '' ?></b></div>
                    <div><small>প্রতিষ্ঠান</small><b><?= e($v('institution')) ?></b></div>
                    <div><small>নিজের মোবাইল</small><b><?= e($v('mobile') ?: '—') ?></b></div>
                    <div><small>বাবা</small><b><?= e($v('fatherName')) ?><?= $v('fatherMobile') !== '' ? ' · ' . e($v('fatherMobile')) : '' ?></b></div>
                    <div><small>মা</small><b><?= e($v('motherName')) ?><?= $v('motherMobile') !== '' ? ' · ' . e($v('motherMobile')) : '' ?></b></div>
                    <div><small>গ্রামের বাড়ি (স্থায়ী ঠিকানা)</small><b><?= e($v('permanentAddress')) ?></b></div>
                    <div><small>বর্তমান ঠিকানা</small><b><?= e($v('presentAddress')) ?>, <?= e($v('district')) ?></b></div>
                    <?php if ($exam('ssc')): ?><div><small>SSC</small><b><?= e($examTxt($exam('ssc'), 'SSC')) ?></b></div><?php endif; ?>
                    <?php if ($exam('hsc')): ?><div><small>HSC</small><b><?= e($examTxt($exam('hsc'), 'HSC')) ?></b></div><?php endif; ?>
                </div>
                <div class="ky-ids"><img src="/Kyc/File/<?= (int)$k->Id ?>?side=front" alt="ID front" /><img src="/Kyc/File/<?= (int)$k->Id ?>?side=back" alt="ID back" /></div>
            </div>
        <?php else: ?>
        <form id="kyForm" autocomplete="off" novalidate>
            <div class="ky-card">
                <h3><span class="n">১</span> শিক্ষার্থীর তথ্য</h3>
                <div class="ky-g2">
                    <div class="ky-f" data-f="fullName"><label class="l" for="fullName">শিক্ষার্থীর পুরো নাম <span class="r">*</span></label><input type="text" id="fullName" name="fullName" maxlength="80" value="<?= e($v('fullName', (string)($u->FullName ?? ''))) ?>" /></div>
                    <div class="ky-f" data-f="mobile"><label class="l" for="mobile">নিজের মোবাইল নম্বর <small>(থাকলে)</small></label><input type="tel" id="mobile" name="mobile" maxlength="16" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e($v('mobile', (string)($u->MobileNumber ?? ''))) ?>" /></div>
                </div>
            </div>

            <div class="ky-card">
                <h3><span class="n">২</span> বাবা-মা</h3>
                <div class="ky-g2">
                    <div class="ky-f" data-f="fatherName"><label class="l" for="fatherName">বাবার নাম <span class="r">*</span></label><input type="text" id="fatherName" name="fatherName" maxlength="80" value="<?= e($v('fatherName')) ?>" /></div>
                    <div class="ky-f" data-f="fatherMobile"><label class="l" for="fatherMobile">বাবার মোবাইল নম্বর</label><input type="tel" id="fatherMobile" name="fatherMobile" maxlength="16" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e($v('fatherMobile')) ?>" /></div>
                    <div class="ky-f" data-f="motherName"><label class="l" for="motherName">মায়ের নাম <span class="r">*</span></label><input type="text" id="motherName" name="motherName" maxlength="80" value="<?= e($v('motherName')) ?>" /></div>
                    <div class="ky-f" data-f="motherMobile"><label class="l" for="motherMobile">মায়ের মোবাইল নম্বর</label><input type="tel" id="motherMobile" name="motherMobile" maxlength="16" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e($v('motherMobile')) ?>" /></div>
                </div>
                <p class="ky-f h" style="margin:-4px 0 0;color:#64748B;font-size:12.5px;"><i class="fas fa-circle-info"></i> বাবা অথবা মায়ের — অন্তত একজনের মোবাইল নম্বর দিতে হবে।</p>
            </div>

            <div class="ky-card">
                <h3><span class="n">৩</span> ঠিকানা</h3>
                <div class="ky-f" data-f="permanentAddress"><label class="l" for="permanentAddress">গ্রামের বাড়ি / স্থায়ী ঠিকানা <span class="r">*</span></label><textarea id="permanentAddress" name="permanentAddress" maxlength="200" placeholder="গ্রাম, ডাকঘর, উপজেলা, জেলা"><?= e($v('permanentAddress')) ?></textarea></div>
                <div class="ky-f" data-f="presentAddress"><label class="l" for="presentAddress">বর্তমান ঠিকানা <span class="r">*</span></label><textarea id="presentAddress" name="presentAddress" maxlength="200" placeholder="বাসা / রোড, এলাকা"><?= e($v('presentAddress')) ?></textarea>
                    <label class="ky-chk"><input type="checkbox" id="kySameAddr" /> গ্রামের বাড়ির ঠিকানাই বর্তমান ঠিকানা</label></div>
                <div class="ky-f" data-f="district" style="max-width:360px;"><label class="l" for="district">বর্তমান জেলা <span class="r">*</span></label><select id="district" name="district"><option value="">— জেলা বাছাই করুন —</option><?php foreach ($dists as $d): ?><option<?= $v('district') === $d ? ' selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?></select></div>
            </div>

            <div class="ky-card">
                <h3><span class="n">৪</span> পড়াশোনা</h3>
                <div class="ky-g2">
                    <div class="ky-f" data-f="classLevel"><label class="l" for="classLevel">এখন কোন ক্লাসে পড়ে <span class="r">*</span></label>
                        <select id="classLevel" name="classLevel"><option value="">— ক্লাস বাছাই করুন —</option>
                            <?php foreach ($groups as $gl => $keys): ?><optgroup label="<?= e($gl) ?>"><?php foreach ($keys as $ck): ?><option value="<?= $ck ?>"<?= $v('classLevel') === $ck ? ' selected' : '' ?>><?= e($cls[$ck]) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?>
                        </select></div>
                    <div class="ky-f" data-f="institution"><label class="l" for="institution">স্কুল / কলেজ / বিশ্ববিদ্যালয়ের নাম <span class="r">*</span></label><input type="text" id="institution" name="institution" maxlength="120" value="<?= e($v('institution')) ?>" /></div>
                </div>
                <div class="ky-f" id="kyGroup" hidden style="max-width:360px;"><label class="l" for="group">বিভাগ</label><select id="group" name="group"><option value="">— বাছাই করুন —</option><?php foreach (['Science' => 'বিজ্ঞান (Science)', 'Commerce' => 'ব্যবসায় শিক্ষা (Commerce)', 'Arts' => 'মানবিক (Arts)', 'Other' => 'অন্যান্য'] as $gk => $gl): ?><option value="<?= $gk ?>"<?= $v('group') === $gk ? ' selected' : '' ?>><?= $gl ?></option><?php endforeach; ?></select></div>

                <?php foreach (['ssc' => 'SSC / দাখিল', 'hsc' => 'HSC / আলিম'] as $ex => $EX): $x = $exam($ex); $xs = (string)($x['status'] ?? ''); ?>
                    <div class="ky-ex" id="kyEx_<?= $ex ?>" data-f="<?= $ex ?>" hidden>
                        <h4><?= $EX ?> <span class="r" style="color:#DC2626;">*</span>
                            <span class="ky-seg">
                                <label><input type="radio" name="<?= $ex ?>Status" value="passed"<?= $xs === 'passed' ? ' checked' : '' ?> /> <i class="fas fa-check"></i> পাস করেছি</label>
                                <label class="run" data-run><input type="radio" name="<?= $ex ?>Status" value="running"<?= $xs === 'running' ? ' checked' : '' ?> /> <i class="fas fa-person-running"></i> Running (এখন পড়ছি)</label>
                            </span></h4>
                        <div class="ky-g3" data-res hidden>
                            <div class="ky-f"><label class="l">পাসের সাল <span class="r">*</span></label><select name="<?= $ex ?>Year"><option value="">— সাল —</option><?php for ($y = (int)date('Y'); $y >= 1980; $y--): ?><option<?= (int)($x['year'] ?? 0) === $y ? ' selected' : '' ?>><?= $y ?></option><?php endfor; ?></select></div>
                            <div class="ky-f"><label class="l">রেজাল্ট (GPA) <span class="r">*</span></label><input type="text" name="<?= $ex ?>Gpa" inputmode="decimal" maxlength="4" placeholder="যেমন 4.50" value="<?= e((string)($x['gpa'] ?? '')) ?>" /></div>
                            <div class="ky-f"><label class="l">বোর্ড</label><select name="<?= $ex ?>Board"><option value="">— বোর্ড —</option><?php foreach ($boards as $b): ?><option<?= ($x['board'] ?? '') === $b ? ' selected' : '' ?>><?= e($b) ?></option><?php endforeach; ?></select></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ky-card">
                <h3><span class="n">৫</span> স্টুডেন্ট ID কার্ডের ছবি</h3>
                <div class="ky-lock"><i class="fas fa-lock"></i> ছবি শুধু অ্যাডমিন যাচাইয়ের জন্য দেখবেন — সাইটে কোথাও দেখানো হবে না। ID কার্ড না থাকলে (ছোটদের ক্ষেত্রে) জন্ম নিবন্ধন সনদের ছবি দিন।</div>
                <div class="ky-g2">
                    <?php foreach (['Front' => 'সামনের দিক', 'Back' => 'পেছনের দিক'] as $side => $lbl): $has = $k && kyc_file_path($k->{'File' . $side}); ?>
                        <div class="ky-f" data-f="file<?= $side ?>"><label class="l">ID কার্ড — <?= $lbl ?> <span class="r">*</span></label>
                            <label class="ky-drop" id="kyDrop<?= $side ?>" for="file<?= $side ?>">
                                <img alt=""<?= $has ? ' src="/Kyc/File/' . (int)$k->Id . '?side=' . strtolower($side) . '"' : ' hidden' ?> />
                                <span<?= $has ? ' hidden' : '' ?>><i class="fas fa-camera"></i><b>ছবি বাছাই করুন</b><small>JPG / PNG · ৬ MB এর মধ্যে · চার কোণা যেন দেখা যায়</small></span>
                            </label>
                            <input type="file" id="file<?= $side ?>" name="file<?= $side ?>" accept="image/jpeg,image/png,image/webp" data-file="kyDrop<?= $side ?>" hidden />
                            <?php if ($has): ?><div class="h">আগের ছবিটাই থাকবে — বদলাতে চাইলে উপরে চাপুন।</div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($next !== ''): ?><input type="hidden" name="next" value="<?= e($next) ?>" /><?php endif; ?>
            <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;">
                <button type="submit" class="ky-btn" id="kyBtn"><i class="fas fa-paper-plane"></i> অ্যাডমিনের কাছে জমা দিন</button>
                <span class="ky-msg" id="kyMsg"></span>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php if ($showForm): ?>
<script>
(function () {
    var ORDER = <?= json_encode(array_keys($cls)) ?>;
    function idx(k) { return ORDER.indexOf(k); }
    function rule(cls, ex) {
        var i = idx(cls); if (i < 0) return [false, false];
        if (ex === 'ssc') return [i >= idx('c9'), cls === 'c9' || cls === 'c10'];
        if (cls === 'diploma') return [false, false];
        return [i >= idx('c11'), cls === 'c11' || cls === 'c12'];
    }
    var f = document.getElementById('kyForm'), cl = document.getElementById('classLevel');
    function refresh() {
        var c = cl.value;
        document.getElementById('kyGroup').hidden = !(idx(c) >= idx('c9'));
        ['ssc', 'hsc'].forEach(function (ex) {
            var r = rule(c, ex), box = document.getElementById('kyEx_' + ex);
            box.hidden = !r[0];
            var run = box.querySelector('[data-run]'); run.hidden = !r[1];
            if (!r[1] && run.querySelector('input').checked) run.querySelector('input').checked = false;
            var st = (f.querySelector('input[name="' + ex + 'Status"]:checked') || {}).value;
            box.querySelector('[data-res]').hidden = st !== 'passed';
        });
    }
    cl.addEventListener('change', refresh);
    f.querySelectorAll('input[name$="Status"]').forEach(function (r) { r.addEventListener('change', refresh); });
    refresh();
    var same = document.getElementById('kySameAddr');
    same.addEventListener('change', function () { if (same.checked) f.elements.presentAddress.value = f.elements.permanentAddress.value; });
    kyBind(f, '/Kyc/Student', document.getElementById('kyBtn'), document.getElementById('kyMsg'));
})();
</script>
<?php endif; ?>
