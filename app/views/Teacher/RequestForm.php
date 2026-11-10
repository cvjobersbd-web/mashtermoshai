<?php
$ViewData['Title'] = 'Become a Tutor';
?>
<!-- 📁 Views/Teacher/RequestForm.cshtml -->
<!-- লোকেশন: Tutorbub/Views/Teacher/RequestForm.cshtml -->


<style>
    .teacher-form-container {
        max-width: 980px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .form-section { margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #F5FBFF; }
    .form-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .form-section-title { font-size: 18px; font-weight: 600; color: #08224A; margin-bottom: 15px; }
    .form-section-title i { margin-right: 10px; color: #028EA5; }
    .form-control:focus { border-color: #02B8F5; box-shadow: 0 0 0 0.2rem rgba(2,184,245, 0.25); }
    .btn-submit { background-color: #02B8F5; color: white; padding: 12px 40px; border-radius: 8px; border: none; font-weight: 600; transition: opacity 0.3s; width: 100%; }
    .btn-submit:hover { opacity: 0.8; color: white; }
    .btn-cancel { background-color: #6c757d; color: white; padding: 12px 40px; border-radius: 8px; border: none; font-weight: 600; transition: opacity 0.3s; text-decoration: none; display: inline-block; text-align: center; }
    .btn-cancel:hover { opacity: 0.8; color: white; }
    .required-star { color: #dc3545; margin-left: 3px; }

    /* Success Popup/Modal */
    .success-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .success-modal-overlay.active {
        display: flex;
    }
    .success-modal-content {
        background: white;
        border-radius: 16px;
        padding: 40px 50px;
        max-width: 450px;
        text-align: center;
        animation: popIn 0.5s ease;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    @keyframes popIn {
        0% { transform: scale(0.7); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .success-modal-content .icon {
        font-size: 72px;
        color: #2ecc71;
        margin-bottom: 15px;
    }
    .success-modal-content h3 {
        color: #08224A;
        margin-bottom: 10px;
        font-size: 24px;
    }
    .success-modal-content p {
        color: #64748B;
        margin-bottom: 25px;
        font-size: 15px;
        line-height: 1.6;
    }
    .success-modal-content .btn-ok {
        background: #02B8F5;
        color: white;
        border: none;
        padding: 12px 50px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: opacity 0.3s;
    }
    .success-modal-content .btn-ok:hover {
        opacity: 0.8;
    }
</style>

<div class="container py-5">
    <div class="teacher-form-container">
        <h2 class="text-center mb-4" style="color:#08224A;">
            <i class="fas fa-chalkboard-teacher me-2"></i>Become a Tutor
        </h2>
        <p class="text-center text-muted mb-4">
            Fill in the details below to apply as a tutor. Your application will be reviewed by our admin team.
        </p>

        <?php if ($ViewBag->Error !== null): ?>
            <div class="alert alert-danger"><?= e($ViewBag->Error) ?></div>
        <?php endif; ?>
        <?php if ($TempData['Info'] !== null): ?>
            <div class="alert alert-info"><?= e($TempData['Info']) ?></div>
        <?php endif; ?>

        <form method="post" action="/Teacher/RequestForm" id="teacherForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="UserId" id="UserId" value="<?= e($Model->UserId) ?>" />
            <input type="hidden" name="FullName" id="FullName" value="<?= e($Model->FullName) ?>" />
            <input type="hidden" name="Email" id="Email" value="<?= e($Model->Email) ?>" />
            <input type="hidden" name="MobileNumber" id="MobileNumber" value="<?= e($Model->MobileNumber) ?>" />

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-graduation-cap"></i> Highest Education</div>
                <div class="row">
                    <div class="col-md-12 mb-3"><label for="Education" class="form-label fw-bold">Highest Education <span class="required-star">*</span></label><input type="text" name="Education" id="Education" value="<?= e($Model->Education) ?>" class="form-control" placeholder="e.g. BSc in Computer Science (running)" required /></div>
                </div>
            </div>

            <!-- এই তথ্যগুলো Tutor List → See (ডিটেইলস পেজে) দেখাবে -->
            <?php partial('Teacher/_DetailsForm', ['d' => $ViewBag->Details ?? db()->GetTeacherDetails((int)$Model->UserId)]); ?>

            <?php $kOld = db()->GetKyc((int)$Model->UserId, 'teacher'); $kD = $kOld ? $kOld->D : []; ?>
            <div class="form-section kyc-sec">
                <div class="form-section-title"><i class="fas fa-id-card"></i> পরিচয় যাচাই (Profile Verification)</div>
                <p class="text-muted" style="font-size:13.5px;margin-top:-8px;"><i class="fas fa-lock"></i> NID এর ছবি শুধু অ্যাডমিন যাচাইয়ের জন্য দেখবেন — সাইটে কোথাও দেখানো হবে না।</p>
                <div class="row">
                    <div class="col-md-4 mb-3"><label for="kycMobile" class="form-label fw-bold">মোবাইল নম্বর <span class="required-star">*</span></label><input type="tel" name="kycMobile" id="kycMobile" class="form-control" maxlength="16" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e((string)input_str('kycMobile', $kD['mobile'] ?? $Model->MobileNumber)) ?>" required /></div>
                    <div class="col-md-4 mb-3"><label for="kycWhatsapp" class="form-label fw-bold"><i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp নম্বর <span class="required-star">*</span></label><input type="tel" name="kycWhatsapp" id="kycWhatsapp" class="form-control" maxlength="16" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e((string)input_str('kycWhatsapp', $kD['whatsapp'] ?? '')) ?>" required />
                        <label style="font-size:12.5px;margin-top:5px;color:#475569;"><input type="checkbox" id="kycSameWa" /> মোবাইল নম্বরটাই WhatsApp</label></div>
                    <div class="col-md-4 mb-3"><label for="kycNid" class="form-label fw-bold">NID নম্বর <span style="color:#94A3B8;font-weight:500;">(ঐচ্ছিক)</span></label><input type="text" name="kycNid" id="kycNid" class="form-control" maxlength="20" inputmode="numeric" placeholder="১০ / ১৩ / ১৭ ডিজিট" value="<?= e((string)input_str('kycNid', $kD['nid'] ?? '')) ?>" /></div>
                </div>
                <div class="row">
                    <?php foreach (['Front' => 'NID কার্ডের সামনের ছবি', 'Back' => 'NID কার্ডের পেছনের ছবি'] as $side => $lbl): $has = $kOld && $kOld->Status !== 'Approved' && kyc_file_path($kOld->{'File' . $side}); ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold"><?= $lbl ?> <span class="required-star">*</span></label>
                            <label class="kyc-drop" for="kycFile<?= $side ?>">
                                <img alt="" id="kycPrev<?= $side ?>"<?= $has ? ' src="/Kyc/File/' . (int)$kOld->Id . '?side=' . strtolower($side) . '"' : ' hidden' ?> />
                                <span id="kycPh<?= $side ?>"<?= $has ? ' hidden' : '' ?>><i class="fas fa-id-card"></i><b>ছবি বাছাই করুন</b><small>JPG / PNG · ৬ MB এর মধ্যে · চার কোণা যেন দেখা যায়</small></span>
                            </label>
                            <input type="file" name="kycFile<?= $side ?>" id="kycFile<?= $side ?>" accept="image/jpeg,image/png,image/webp" hidden<?= $has ? '' : ' required' ?> />
                            <?php if ($has): ?><small class="text-muted">আগের ছবিটাই থাকবে — বদলাতে চাইলে উপরে চাপুন।</small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <style>
                .kyc-drop { display: flex; align-items: center; justify-content: center; border: 2px dashed #BFE3F2; border-radius: 14px; background: #F5FBFF; min-height: 170px; cursor: pointer; overflow: hidden; text-align: center; padding: 10px; }
                .kyc-drop:hover { border-color: #028EA5; }
                .kyc-drop img { max-width: 100%; max-height: 220px; border-radius: 10px; }
                .kyc-drop span { display: flex; flex-direction: column; align-items: center; gap: 4px; color: #475569; }
                .kyc-drop span i { font-size: 30px; color: #028EA5; } .kyc-drop small { color: #94A3B8; }
            </style>
            <script>
            (function () {
                ['Front', 'Back'].forEach(function (s) {
                    var f = document.getElementById('kycFile' + s);
                    f.addEventListener('change', function () { var x = f.files[0]; if (!x) return; var i = document.getElementById('kycPrev' + s); i.src = URL.createObjectURL(x); i.hidden = false; document.getElementById('kycPh' + s).hidden = true; });
                });
                var same = document.getElementById('kycSameWa'), m = document.getElementById('kycMobile'), w = document.getElementById('kycWhatsapp');
                same.addEventListener('change', function () { if (same.checked) w.value = m.value; });
                m.addEventListener('input', function () { if (same.checked) w.value = m.value; });
                document.getElementById('teacherForm').addEventListener('submit', function (e) {
                    var miss = ['Front', 'Back'].filter(function (s) { return document.getElementById('kycFile' + s).required && !document.getElementById('kycFile' + s).files.length; });
                    if (miss.length) { e.preventDefault(); alert('NID কার্ডের ' + (miss.length === 2 ? 'সামনের আর পেছনের' : (miss[0] === 'Front' ? 'সামনের' : 'পেছনের')) + ' ছবি দিন।'); document.querySelector('.kyc-sec').scrollIntoView({ behavior: 'smooth' }); }
                });
            })();
            </script>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-link"></i> CV (ঐচ্ছিক)</div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="CvLink" class="form-label fw-bold">CV Link</label><input type="text" name="CvLink" id="CvLink" value="<?= e($Model->CvLink) ?>" class="form-control" placeholder="https://drive.google.com/your-cv" /></div>
                    <div class="col-md-6 mb-3"><label for="TeachingStyle" class="form-label fw-bold">Teaching Style</label><input type="text" name="TeachingStyle" id="TeachingStyle" value="<?= e($Model->TeachingStyle) ?>" class="form-control" placeholder="e.g. Interactive, Hands-on" /></div>
                </div>
            </div>

            <div class="d-flex gap-3 mt-4">
                <a href="/Profile" class="btn-cancel flex-grow-1">Cancel</a>
                <button type="submit" class="btn-submit flex-grow-1" id="submitBtn">
                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Success Popup Modal ===== -->
<div class="success-modal-overlay" id="successModal">
    <div class="success-modal-content">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <h3>Application Submitted!</h3>
        <p>Your teacher request has been submitted successfully. Please wait for admin approval.</p>
        <button class="btn-ok" onclick="closeSuccessModal()">OK</button>
    </div>
</div>

<script>
    // Check if success message exists and show modal
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($TempData['Success'] !== null): ?>
                document.getElementById('successModal').classList.add('active');
                console.log('Success modal shown');
            <?php else: ?>
                console.log('No success message found');
            <?php endif; ?>
    });

    function closeSuccessModal() {
        document.getElementById('successModal').classList.remove('active');
        window.location.href = '/Profile/Index';
    }

    // Close modal on outside click
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSuccessModal();
        }
    });
</script>