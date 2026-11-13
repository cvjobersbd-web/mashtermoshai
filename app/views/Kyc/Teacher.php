<?php
/** টিচারের Teacher Verification — মোবাইল, WhatsApp, NID এর ছবি (পুরোনো টিচার / আগের Teacher Verification বাতিল হলে আবার জমা) */
$k = $Model; $st = $k ? (string)$k->Status : 'None'; $u = $ViewBag->User;
$ViewData['Title'] = 'টিচারের Teacher Verification';
$ViewData['Robots'] = 'noindex, nofollow';
$D = $k ? $k->D : [];
$showForm = $st === 'None' || $st === 'Rejected' || $st === 'Pending';  // যাচাই বাকি থাকলেও ছবি / তথ্য বদলানো যায়
?>
<?php partial('Kyc/_Styles'); ?>
<?= csrf_field() ?>
<div class="ky-page">
    <div class="ky-wrap">
        <h1 class="ky-h1"><i class="fas fa-id-card" style="color:#0EA5E9;"></i> টিচারের পরিচয় যাচাই (Teacher Verification)</h1>
        <p class="ky-sub">শিক্ষার্থী ও অভিভাবকদের নিরাপত্তার জন্য টিচারের শিক্ষা প্রতিষ্ঠানের (বিশ্ববিদ্যালয় / কলেজ / স্কুল) Academic ID Card, NID কার্ড আর যোগাযোগের নম্বর যাচাই করা হয়।</p>
        <?php if ($st === 'Pending'): ?>
            <div class="ky-state p"><i class="fas fa-hourglass-half"></i><div><b>তথ্য যাচাই হচ্ছে</b><p>অ্যাডমিন দেখে Approve করলে নোটিফিকেশন পাবেন। ভুল ছবি দিয়ে থাকলে (যেমন Academic ID Card এর জায়গায় NID) — Approve হওয়ার আগে নিচে ছবিতে চাপ দিয়ে বদলে "আপডেট করুন" চাপুন।</p></div></div>
        <?php elseif ($st === 'Approved'): ?>
            <div class="ky-state ok"><i class="fas fa-circle-check"></i><div><b>আপনার Teacher Verification Approve হয়েছে</b><p>ধন্যবাদ! আপনার পরিচয় যাচাই করা হয়েছে।</p></div></div>
        <?php elseif ($st === 'Rejected'): ?>
            <div class="ky-state no"><i class="fas fa-circle-xmark"></i><div><b>তথ্য গ্রহণ করা হয়নি</b><p><?= !is_blank($k->AdminNote) ? 'কারণ: ' . e($k->AdminNote) . '<br>' : '' ?>নিচে ঠিক করে আবার জমা দিন।</p></div></div>
        <?php endif; ?>

        <?php $isUni = !empty($D['uniCard']); $reuse = $k && $isUni;
            $imgs = $isUni ? ['front' => 'Academic ID · সামনে', 'back' => 'Academic ID · পেছনে'] + (!empty($D['nidFront']) ? ['nidfront' => 'NID · সামনে'] : []) + (!empty($D['nidBack']) ? ['nidback' => 'NID · পেছনে'] : []) : ['front' => 'NID · সামনে', 'back' => 'NID · পেছনে']; ?>
        <?php if (!$showForm): ?>
            <div class="ky-card">
                <div class="ky-sum">
                    <div><small>মোবাইল</small><b><?= e($D['mobile'] ?? '') ?></b></div>
                    <div><small>WhatsApp</small><b><?= e($D['whatsapp'] ?? '') ?></b></div>
                    <?php if ($isUni): ?><div><small>শিক্ষা প্রতিষ্ঠান</small><b><?= e($D['university'] ?? '') ?></b></div><?php endif; ?>
                    <div><small>NID নম্বর</small><b><?= e(($D['nid'] ?? '') ?: '—') ?></b></div>
                </div>
                <div class="ky-ids"><?php foreach ($imgs as $sd => $lb): ?><img src="/Kyc/File/<?= (int)$k->Id ?>?side=<?= $sd ?>" alt="<?= e($lb) ?>" title="<?= e($lb) ?>" /><?php endforeach; ?></div>
            </div>
        <?php else: ?>
        <form id="kyForm" autocomplete="off" novalidate class="ky-card">
            <div class="ky-g3">
                <div class="ky-f" data-f="kycMobile"><label class="l" for="kycMobile">মোবাইল নম্বর <span class="r">*</span></label><input type="tel" id="kycMobile" name="kycMobile" maxlength="16" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e((string)($D['mobile'] ?? ($u->MobileNumber ?? ''))) ?>" /></div>
                <div class="ky-f" data-f="kycWhatsapp"><label class="l" for="kycWhatsapp"><i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp নম্বর <span class="r">*</span></label><input type="tel" id="kycWhatsapp" name="kycWhatsapp" maxlength="16" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e((string)($D['whatsapp'] ?? '')) ?>" />
                    <label class="ky-chk"><input type="checkbox" id="kySameWa" /> মোবাইলটাই WhatsApp</label></div>
                <div class="ky-f" data-f="kycUniversity"><label class="l" for="kycUniversity">শিক্ষা প্রতিষ্ঠান <span class="r">*</span></label><input type="text" id="kycUniversity" name="kycUniversity" maxlength="150" placeholder="বিশ্ববিদ্যালয় / কলেজ / স্কুলের নাম" value="<?= e((string)($D['university'] ?? '')) ?>" /></div>
            </div>
            <div class="ky-lock"><i class="fas fa-lock"></i> ছবিগুলো শুধু অ্যাডমিন যাচাইয়ের জন্য দেখবেন — সাইটে কোথাও দেখানো হবে না।</div>
            <div class="ky-g2">
                <?php foreach (['UniFront' => ['front', 'Academic ID Card — সামনে', true], 'UniBack' => ['back', 'Academic ID Card — পেছনে', true], 'FileFront' => ['nidfront', 'NID — সামনে', true], 'FileBack' => ['nidback', 'NID — পেছনে', true]] as $nm => [$sd, $lbl, $req]):
                    $oldName = $reuse ? ($sd === 'front' ? $k->FileFront : ($sd === 'back' ? $k->FileBack : ($D[$sd === 'nidfront' ? 'nidFront' : 'nidBack'] ?? null))) : null;
                    $has = $oldName && kyc_file_path($oldName); ?>
                    <div class="ky-f" data-f="kyc<?= $nm ?>"><label class="l"><?= $lbl ?> <?= $req ? '<span class="r">*</span>' : '<small>(ঐচ্ছিক)</small>' ?></label>
                        <label class="ky-drop" id="kyDrop<?= $nm ?>" for="kyc<?= $nm ?>">
                            <img alt=""<?= $has ? ' src="/Kyc/File/' . (int)$k->Id . '?side=' . $sd . '"' : ' hidden' ?> />
                            <span<?= $has ? ' hidden' : '' ?>><i class="fas <?= str_starts_with($nm, 'Uni') ? 'fa-building-columns' : 'fa-id-card' ?>"></i><b>ছবি বাছাই করুন</b><small>JPG / PNG · ৬ MB এর মধ্যে</small></span>
                        </label>
                        <input type="file" id="kyc<?= $nm ?>" name="kyc<?= $nm ?>" accept="image/jpeg,image/png,image/webp" data-file="kyDrop<?= $nm ?>" hidden />
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="ky-f" data-f="kycNid" style="max-width:300px;"><label class="l" for="kycNid">NID নম্বর <small>(ঐচ্ছিক)</small></label><input type="text" id="kycNid" name="kycNid" maxlength="20" inputmode="numeric" value="<?= e((string)($D['nid'] ?? '')) ?>" /></div>
            <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;">
                <button type="submit" class="ky-btn" id="kyBtn"><?= $st === 'Pending' ? '<i class="fas fa-rotate"></i> আপডেট করুন' : '<i class="fas fa-paper-plane"></i> জমা দিন' ?></button>
                <span class="ky-msg" id="kyMsg"></span>
            </div>
        </form>
        <script>
        (function () {
            var s = document.getElementById('kySameWa'), m = document.getElementById('kycMobile'), w = document.getElementById('kycWhatsapp');
            s.addEventListener('change', function () { if (s.checked) w.value = m.value; });
            kyBind(document.getElementById('kyForm'), '/Kyc/Teacher', document.getElementById('kyBtn'), document.getElementById('kyMsg'));
        })();
        </script>
        <?php endif; ?>
    </div>
</div>
