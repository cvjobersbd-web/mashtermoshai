<?php
/**
 * টিচারের বিস্তারিত তথ্যের ফর্ম (td[...]) — "Become a Tutor" আবেদন আর "Tutor Details এডিট" দুই জায়গায় একই।
 * এই তথ্যগুলো Tutor List → See (/Tutors/Profile/{id}) পেজে দেখায়।
 * @var array $d  Db::GetTeacherDetails() — খালি হলে নতুন
 */
$opts = tuition_options();
$dists = array_keys($opts['locations']);
$chk = function (string $name, array $list, array $sel, string $cls = '') {
    $out = '';
    foreach ($list as $v) {
        $on = in_array($v, $sel, true);
        $out .= '<label class="td-chip' . ($on ? ' on' : '') . $cls . '"><input type="checkbox" name="td[' . $name . '][]" value="' . e($v) . '"' . ($on ? ' checked' : '') . ' /> ' . e($v) . '</label>';
    }
    return $out;
};
$sel = function (string $name, array $list, string $cur, string $ph) {
    $o = '<select name="td[' . $name . ']" class="form-control"><option value="">' . e($ph) . '</option>';
    foreach ($list as $v) $o .= '<option value="' . e($v) . '"' . ($v === $cur ? ' selected' : '') . '>' . e($v) . '</option>';
    return $o . '</select>';
};
$req = '<span class="required-star">*</span>';
// SSC / HSC এর বোর্ড আর গ্রুপ — টাইপ না করে তালিকা থেকে বাছাই (আগের লেখা মান তালিকায় না থাকলেও রাখা হয়)
$eduBoards = ['Dhaka', 'Rajshahi', 'Cumilla', 'Jashore', 'Chattogram', 'Barishal', 'Sylhet', 'Dinajpur', 'Mymensingh', 'Madrasah', 'Technical', 'Cambridge / Edexcel (O/A Level)'];
$eduGroups = ['Science', 'Arts', 'Commerce'];
$eduSel = function (string $name, array $list, string $cur, string $ph) {
    $o = '<select name="td[' . $name . ']" class="form-select" style="min-width:130px;font-size:14px;color:#334155;"><option value="">' . e($ph) . '</option>';
    foreach ($list as $v) $o .= '<option value="' . e($v) . '"' . (strcasecmp($v, $cur) === 0 ? ' selected' : '') . '>' . e($v) . '</option>';
    if ($cur !== '' && !in_array(strtolower($cur), array_map('strtolower', $list), true)) $o .= '<option value="' . e($cur) . '" selected>' . e($cur) . '</option>';
    return $o . '</select>';
};
// আগে বাছাই করা কিন্তু তালিকায় নেই এমন বিষয় / ক্লাস (পুরোনো তথ্য) হারিয়ে না যায়
$classList = array_values(array_unique(array_merge($opts['classes'], $d['classes'])));
$subjList = array_values(array_unique(array_merge($opts['subjects'], $d['subjects'])));
?>
<style>
    .td-sec { margin-bottom: 26px; padding-bottom: 22px; border-bottom: 2px solid #F1F7FC; }
    .td-sec:last-of-type { border-bottom: none; }
    .td-sec h4 { font-size: 17px; font-weight: 700; color: #08224A; margin: 0 0 4px; display: flex; align-items: center; gap: 9px; }
    .td-sec h4 i { color: #00B5E0; }
    .td-sec .hint { font-size: 12.5px; color: #64748B; margin: 0 0 14px; }
    .td-sec label.form-label { font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 5px; }
    .td-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .td-chips.scroll { max-height: 210px; overflow-y: auto; padding: 4px; border: 1px solid #E5ECF3; border-radius: 10px; }
    .td-chip { display: inline-flex; align-items: center; gap: 6px; border: 1.5px solid #DCE5EF; border-radius: 999px; padding: 6px 13px; font-size: 13px; cursor: pointer; margin: 0; user-select: none; background: #fff; color: #334155; }
    .td-chip input { width: 14px; height: 14px; accent-color: #00CFFF; margin: 0; }
    .td-chip.on { border-color: #00CFFF; background: #E6F9FF; color: #0369A1; font-weight: 600; }
    .td-edu { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .td-edu th { font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; padding: 0 6px; white-space: nowrap; }
    .td-edu td { padding: 0 4px; vertical-align: middle; }
    .td-edu td:first-child { font-weight: 700; font-size: 13px; color: #08224A; white-space: nowrap; padding-right: 10px; }
    .td-edu input { font-size: 13px; padding: 7px 9px; }
    .td-edu tr.edu-off td { opacity: .45; } .td-edu .edu-na { display: none; font-weight: 600; color: #94A3B8; font-size: 11px; margin-left: 6px; }
    .td-edu tr.edu-off .edu-na { display: inline; }
    .td-private { font-size: 12.5px; color: #92400E; background: #FEF3C7; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; }
    @media (max-width: 760px) {
        .td-edu, .td-edu tbody, .td-edu tr, .td-edu td { display: block; width: 100%; }
        .td-edu thead { display: none; }
        .td-edu tr { border: 1px solid #E5ECF3; border-radius: 10px; padding: 10px; margin-bottom: 10px; }
        .td-edu td { padding: 3px 0; }
        .td-edu td[data-l]::before { content: attr(data-l); display: block; font-size: 11.5px; color: #64748B; font-weight: 600; }
    }
</style>

<div class="td-sec">
    <h4><i class="fas fa-user-pen"></i> নিজের সম্পর্কে (Preface)</h4>
    <p class="hint">স্টুডেন্ট আর অভিভাবকরা আপনার ডিটেইলস পেজে এগুলো প্রথমে দেখবে।</p>
    <div class="mb-3"><label class="form-label">Preface — নিজের পরিচয়</label><textarea name="td[preface]" class="form-control" rows="3" maxlength="1500" placeholder="যেমন: আমি ঢাকা বিশ্ববিদ্যালয়ে পদার্থবিজ্ঞানে পড়ছি..."><?= e($d['preface']) ?></textarea></div>
    <div class="mb-3"><label class="form-label">Tuition Job Experience — পড়ানোর অভিজ্ঞতা</label><textarea name="td[tuitionExperience]" class="form-control" rows="3" maxlength="1500" placeholder="কোন ক্লাস, কত বছর, কেমন ফলাফল..."><?= e($d['tuitionExperience']) ?></textarea></div>
    <div><label class="form-label">Reasons to be Hired — কেন আপনাকে নেবে <?= $req ?></label><textarea name="td[whyHire]" class="form-control" rows="3" maxlength="1500" required placeholder="আপনার পড়ানোর ধরন আর বিশেষত্ব..."><?= e($d['whyHire']) ?></textarea></div>
</div>

<div class="td-sec">
    <h4><i class="fas fa-building-columns"></i> বর্তমান প্রতিষ্ঠান</h4>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">University / College <?= $req ?></label><input name="td[university]" class="form-control" maxlength="150" value="<?= e($d['university']) ?>" required placeholder="যেমন: University of Dhaka" /></div>
        <div class="col-md-6 mb-3"><label class="form-label">Department</label><input name="td[department]" class="form-control" maxlength="120" value="<?= e($d['department']) ?>" placeholder="যেমন: Computer Science & Engineering" /></div>
    </div>
</div>

<div class="td-sec">
    <h4><i class="fas fa-chalkboard-user"></i> Tutoring Information</h4>
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Living Country</label><input name="td[livingCountry]" class="form-control" maxlength="60" value="<?= e($d['livingCountry'] !== '' ? $d['livingCountry'] : 'Bangladesh') ?>" /></div>
        <div class="col-md-4 mb-3"><label class="form-label">Living City (জেলা) <?= $req ?></label>
            <input name="td[livingCity]" class="form-control" list="tdDists" maxlength="60" value="<?= e($d['livingCity']) ?>" required placeholder="যেমন: Dhaka" />
            <datalist id="tdDists"><?php foreach ($dists as $x): ?><option value="<?= e($x) ?>"></option><?php endforeach; ?></datalist></div>
        <div class="col-md-4 mb-3"><label class="form-label">Living Location (এলাকা)</label><input name="td[livingArea]" class="form-control" maxlength="80" value="<?= e($d['livingArea']) ?>" placeholder="যেমন: Mirpur" /></div>
        <div class="col-12 mb-3"><label class="form-label">Preferred Tutoring Locations <span style="color:#94A3B8; font-weight:400;">(কমা দিয়ে আলাদা করুন)</span></label><input name="td[preferredAreas]" class="form-control" maxlength="400" value="<?= e(implode(', ', $d['preferredAreas'])) ?>" placeholder="যেমন: Mirpur, Pallabi, Kafrul" /></div>
    </div>
    <div class="mb-3"><label class="form-label d-block">Preferred Tutoring Category (Medium)</label><div class="td-chips"><?= $chk('mediums', Db::TD_MEDIUMS, $d['mediums']) ?></div></div>
    <div class="mb-3"><label class="form-label d-block">Preferred Classes & Courses <?= $req ?></label><div class="td-chips scroll"><?= $chk('classes', $classList, $d['classes']) ?></div></div>
    <div class="mb-3"><label class="form-label d-block">Preferred Subjects <?= $req ?></label><div class="td-chips scroll"><?= $chk('subjects', $subjList, $d['subjects']) ?></div></div>
    <div class="mb-3"><label class="form-label d-block">Preferred Tutoring Method <?= $req ?></label><div class="td-chips"><?= $chk('methods', Db::TD_METHODS, $d['methods']) ?></div></div>
    <div class="mb-3"><label class="form-label d-block">Availability Days <?= $req ?></label><div class="td-chips"><?= $chk('days', Db::TD_DAYS, $d['days']) ?></div></div>
    <div class="row">
        <div class="col-6 col-md-3 mb-3"><label class="form-label">Available From</label><input type="time" name="td[timeFrom]" class="form-control" value="<?= e($d['timeFrom']) ?>" /></div>
        <div class="col-6 col-md-3 mb-3"><label class="form-label">Available To</label><input type="time" name="td[timeTo]" class="form-control" value="<?= e($d['timeTo']) ?>" /></div>
        <div class="col-6 col-md-3 mb-3"><label class="form-label">Tutoring Experience <?= $req ?></label><?= $sel('experienceYears', ['Fresher', '1 Year', '2 Years', '3 Years', '4 Years', '5 Years', '6-10 Years', '10+ Years'], $d['experienceYears'], 'বাছাই করুন') ?></div>
        <div class="col-6 col-md-3 mb-3"><label class="form-label">Expected Salary (৳/মাস) <?= $req ?></label><input name="td[expectedSalary]" class="form-control" inputmode="numeric" maxlength="20" value="<?= e($d['expectedSalary']) ?>" required placeholder="যেমন: 5000" /></div>
    </div>
</div>

<div class="td-sec">
    <h4><i class="fas fa-graduation-cap"></i> Educational Information</h4>
    <p class="hint"><b>বাধ্যতামূলক</b> — SSC থেকে শুরু করে যতটুকু পড়েছেন। এখনো শেষ না হলে সালের ঘরে <b>Running</b> দিন: SSC পড়ছেন → SSC তে Running · HSC পড়ছেন → SSC এর রেজাল্ট + HSC তে Running · অনার্স / ডিগ্রি পড়ছেন → SSC, HSC এর রেজাল্ট + Graduation এ Running (বা কত বর্ষ)। Masters ঐচ্ছিক।</p>
    <div style="overflow-x:auto;">
        <table class="td-edu">
            <thead><tr><th></th><th>Exam Title</th><th>Institute</th><th>Board</th><th>Group</th><th>Year</th><th>GPA</th></tr></thead>
            <tbody>
                <?php foreach (['ssc' => ['SSC / O Level / Dakhil', 'SSC'], 'hsc' => ['HSC / A Level / Alim', 'HSC']] as $k => [$lbl, $ph]): ?>
                    <tr data-edu="<?= $k ?>">
                        <td><?= e($lbl) ?><?= $k !== 'post' ? ' <span style="color:#e74c3c">*</span>' : '' ?><small class="edu-na">প্রযোজ্য নয়</small></td>
                        <td data-l="Exam Title"><input name="td[<?= $k ?>Title]" class="form-control" maxlength="40" value="<?= e($d[$k . 'Title']) ?>" placeholder="<?= $ph ?>" /></td>
                        <td data-l="Institute"><input name="td[<?= $k ?>Institute]" class="form-control" maxlength="150" value="<?= e($d[$k . 'Institute']) ?>" /></td>
                        <td data-l="Board"><?= $eduSel($k . 'Board', $eduBoards, (string)$d[$k . 'Board'], '— বোর্ড —') ?></td>
                        <td data-l="Group"><?= $eduSel($k . 'Group', $eduGroups, (string)$d[$k . 'Group'], '— গ্রুপ —') ?></td>
                        <td data-l="Year"><?php $yv = (string)$d[$k . 'Year']; $run = strcasecmp($yv, 'Running') === 0; ?>
                            <select name="td[<?= $k ?>Year]" class="form-select td-year" data-res="td[<?= $k ?>Result]" data-study="0" style="width:140px;font-size:14px;color:#334155;">
                                <option value="">— সাল —</option>
                                <option value="Running"<?= $run ? ' selected' : '' ?>>Running (পড়ছি)</option>
                                <?php for ($y = (int)date('Y') + 1; $y >= 1975; $y--): ?><option<?= $yv === (string)$y ? ' selected' : '' ?>><?= $y ?></option><?php endfor; ?>
                                <?php if ($yv !== '' && !$run && !ctype_digit($yv)): ?><option selected><?= e($yv) ?></option><?php endif; ?>
                            </select></td>
                        <td data-l="GPA"><input name="td[<?= $k ?>Result]" class="form-control" maxlength="10" value="<?= e($run ? '' : $d[$k . 'Result']) ?>" placeholder="<?= $run ? 'Running' : '5.00' ?>" style="width:72px;"<?= $run ? ' disabled' : '' ?> /></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <table class="td-edu mt-2">
            <thead><tr><th></th><th>Exam Title</th><th>Institute</th><th>Study Type</th><th>Department</th><th>Year</th><th>CGPA</th></tr></thead>
            <tbody>
                <?php foreach (['grad' => ['Graduation / Diploma', 'BSc / BA / Diploma'], 'post' => ['Postgraduate / Masters', 'MSc / MA']] as $k => [$lbl, $ph]): ?>
                    <tr data-edu="<?= $k ?>">
                        <td><?= e($lbl) ?><?= $k !== 'post' ? ' <span style="color:#e74c3c">*</span>' : '' ?><small class="edu-na">প্রযোজ্য নয়</small></td>
                        <td data-l="Exam Title"><input name="td[<?= $k ?>Title]" class="form-control" maxlength="40" value="<?= e($d[$k . 'Title']) ?>" placeholder="<?= $ph ?>" /></td>
                        <td data-l="Institute"><input name="td[<?= $k ?>Institute]" class="form-control" maxlength="150" value="<?= e($d[$k . 'Institute']) ?>" /></td>
                        <td data-l="Study Type"><input name="td[<?= $k ?>StudyType]" class="form-control" maxlength="40" value="<?= e($d[$k . 'StudyType']) ?>" placeholder="Engineering" /></td>
                        <td data-l="Department"><input name="td[<?= $k ?>Department]" class="form-control" maxlength="120" value="<?= e($d[$k . 'Department']) ?>" /></td>
                        <td data-l="Year"><?php $gv = (string)$d[$k . 'Year']; $gOpts = ['Running' => 'Running (পড়ছি)', '1st Year' => '1st Year', '2nd Year' => '2nd Year', '3rd Year' => '3rd Year', '4th Year' => '4th Year', 'Final Year' => 'Final Year']; ?>
                            <select name="td[<?= $k ?>Year]" class="form-select td-year" data-res="td[<?= $k ?>Result]" data-study="1" style="width:140px;font-size:14px;color:#334155;">
                                <option value="">— সাল / বর্ষ —</option>
                                <optgroup label="এখনো পড়ছি"><?php foreach ($gOpts as $gk => $gl): ?><option value="<?= $gk ?>"<?= strcasecmp($gv, $gk) === 0 ? ' selected' : '' ?>><?= $gl ?></option><?php endforeach; ?></optgroup>
                                <optgroup label="পাস করেছি (সাল)"><?php for ($y = (int)date('Y') + 1; $y >= 1975; $y--): ?><option<?= $gv === (string)$y ? ' selected' : '' ?>><?= $y ?></option><?php endfor; ?></optgroup>
                                <?php if ($gv !== '' && !isset($gOpts[$gv]) && !ctype_digit($gv) && strcasecmp($gv, 'Running') !== 0): ?><option selected><?= e($gv) ?></option><?php endif; ?>
                            </select></td>
                        <td data-l="CGPA"><input name="td[<?= $k ?>Result]" class="form-control" maxlength="10" value="<?= e($d[$k . 'Result']) ?>" placeholder="3.50" style="width:72px;" /></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <script>
        (function () {
            var ORDER = ['ssc', 'hsc', 'grad', 'post'];
            function row(k) { return document.querySelector('tr[data-edu="' + k + '"]'); }
            function state(k) {   // '' | running | passed
                var sel = row(k).querySelector('select.td-year'), v = sel.value;
                if (v === '') return '';
                if (v === 'Running' || (sel.dataset.study === '1' && !/^\d{4}$/.test(v))) return 'running';
                return 'passed';
            }
            function refresh() {
                var blocked = false;
                ORDER.forEach(function (k) {
                    var r = row(k); if (!r) return;
                    r.classList.toggle('edu-off', blocked);
                    r.querySelectorAll('input, select').forEach(function (x) { x.disabled = blocked; });
                    if (blocked) return;
                    var st = state(k), res = r.querySelector('input[name$="Result]"]');
                    res.disabled = st === 'running'; if (st === 'running') res.value = '';
                    res.placeholder = st === 'running' ? 'Running' : (k === 'grad' || k === 'post' ? '3.50' : '5.00');
                    if (st !== 'passed') blocked = true;   // পড়া শেষ হয়নি / খালি → পরের ধাপ প্রযোজ্য নয়
                });
            }
            document.querySelectorAll('select.td-year').forEach(function (s) { s.addEventListener('change', refresh); });
            refresh();
        })();
        </script>
    </div>
</div>

<div class="td-sec">
    <h4><i class="fas fa-id-card"></i> Personal Information</h4>
    <div class="row">
        <div class="col-6 col-md-4 mb-3"><label class="form-label">Gender <?= $req ?></label><?= $sel('gender', ['Male', 'Female'], $d['gender'], 'বাছাই করুন') ?></div>
        <div class="col-6 col-md-4 mb-3"><label class="form-label">Blood Group</label><?= $sel('bloodGroup', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $d['bloodGroup'], 'বাছাই করুন') ?></div>
        <div class="col-6 col-md-4 mb-3"><label class="form-label">Religion</label><?= $sel('religion', ['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Other'], $d['religion'], 'বাছাই করুন') ?></div>
        <div class="col-6 col-md-4 mb-3"><label class="form-label">Nationality</label><input name="td[nationality]" class="form-control" maxlength="40" value="<?= e($d['nationality'] !== '' ? $d['nationality'] : 'Bangladeshi') ?>" /></div>
        <div class="col-6 col-md-4 mb-3"><label class="form-label">Date of Birth</label><input type="date" name="td[dob]" class="form-control" value="<?= e($d['dob']) ?>" max="<?= date('Y-m-d', strtotime('-14 years')) ?>" /></div>
    </div>
    <div class="td-private"><i class="fas fa-lock"></i> নিচের ঠিকানা শুধু আপনি আর অ্যাডমিন দেখতে পাবেন — অন্যরা ডিটেইলস পেজে দেখবে না।</div>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Present Full Address</label><input name="td[presentAddress]" class="form-control" maxlength="250" value="<?= e($d['presentAddress']) ?>" /></div>
        <div class="col-md-6 mb-3"><label class="form-label">Permanent Full Address</label><input name="td[permanentAddress]" class="form-control" maxlength="250" value="<?= e($d['permanentAddress']) ?>" /></div>
    </div>
</div>

<script>
    // চিপ চেক করলে রং বদলায়
    document.querySelectorAll('.td-chip input').forEach(function (i) { i.addEventListener('change', function () { i.parentNode.classList.toggle('on', i.checked); }); });
</script>
