<?php
$ViewData['Title'] = 'Become a Tutor';
?>
<!-- 📁 Views/Teacher/RequestForm.cshtml -->
<!-- লোকেশন: Tutorbub/Views/Teacher/RequestForm.cshtml -->


<style>
    .teacher-form-container {
        max-width: 800px;
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

        <form method="post" action="/Teacher/RequestForm" id="teacherForm">
            <?= csrf_field() ?>
            <input type="hidden" name="UserId" id="UserId" value="<?= e($Model->UserId) ?>" />
            <input type="hidden" name="FullName" id="FullName" value="<?= e($Model->FullName) ?>" />
            <input type="hidden" name="Email" id="Email" value="<?= e($Model->Email) ?>" />
            <input type="hidden" name="MobileNumber" id="MobileNumber" value="<?= e($Model->MobileNumber) ?>" />

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-id-card"></i> Basic Information</div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-bold">Full Name</label><input type="text" class="form-control" value="<?= e($Model->FullName) ?>" disabled /></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-bold">Email</label><input type="email" class="form-control" value="<?= e($Model->Email) ?>" disabled /></div>
                    <div class="col-md-12 mb-3"><label class="form-label fw-bold">Mobile Number</label><input type="text" class="form-control" value="<?= e($Model->MobileNumber) ?>" disabled /></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-graduation-cap"></i> Educational Qualification</div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="Education" class="form-label fw-bold">Education <span class="required-star">*</span></label><input type="text" name="Education" id="Education" value="<?= e($Model->Education) ?>" class="form-control" placeholder="e.g. BSc in Computer Science" required /></div>
                    <div class="col-md-6 mb-3"><label for="Institution" class="form-label fw-bold">Institution <span class="required-star">*</span></label><input type="text" name="Institution" id="Institution" value="<?= e($Model->Institution) ?>" class="form-control" placeholder="e.g. Dhaka University" required /></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-chalkboard"></i> Teaching Information</div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="SubjectExpertise" class="form-label fw-bold">Subject Expertise <span class="required-star">*</span></label><input type="text" name="SubjectExpertise" id="SubjectExpertise" value="<?= e($Model->SubjectExpertise) ?>" class="form-control" placeholder="e.g. Mathematics, Physics, Programming" required /></div>
                    <div class="col-md-6 mb-3"><label for="Experience" class="form-label fw-bold">Experience <span class="required-star">*</span></label><select name="Experience" id="Experience" class="form-control" required><option value="" <?= (string)$Model->Experience === '' ? 'selected' : '' ?>>Select Experience</option><option value="Fresher" <?= (string)$Model->Experience === 'Fresher' ? 'selected' : '' ?>>Fresher (No experience)</option><option value="1-2 years" <?= (string)$Model->Experience === '1-2 years' ? 'selected' : '' ?>>1-2 years</option><option value="3-5 years" <?= (string)$Model->Experience === '3-5 years' ? 'selected' : '' ?>>3-5 years</option><option value="5-10 years" <?= (string)$Model->Experience === '5-10 years' ? 'selected' : '' ?>>5-10 years</option><option value="10+ years" <?= (string)$Model->Experience === '10+ years' ? 'selected' : '' ?>>10+ years</option></select></div>
                    <div class="col-md-6 mb-3"><label for="TeachingStyle" class="form-label fw-bold">Teaching Style</label><input type="text" name="TeachingStyle" id="TeachingStyle" value="<?= e($Model->TeachingStyle) ?>" class="form-control" placeholder="e.g. Interactive, Lecture-based, Hands-on" /></div>
                    <div class="col-md-6 mb-3"><label for="HourlyRate" class="form-label fw-bold">Hourly Rate (BDT) <span class="required-star">*</span></label><input type="text" name="HourlyRate" id="HourlyRate" value="<?= e($Model->HourlyRate) ?>" class="form-control" placeholder="e.g. 500" required /></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-clock"></i> Availability</div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="AvailableDays" class="form-label fw-bold">Available Days <span class="required-star">*</span></label><input type="text" name="AvailableDays" id="AvailableDays" value="<?= e($Model->AvailableDays) ?>" class="form-control" placeholder="e.g. Sun, Mon, Wed, Fri" required /></div>
                    <div class="col-md-6 mb-3"><label for="PreferredTime" class="form-label fw-bold">Preferred Time</label><input type="text" name="PreferredTime" id="PreferredTime" value="<?= e($Model->PreferredTime) ?>" class="form-control" placeholder="e.g. 6 PM - 9 PM" /></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-link"></i> CV & Motivation</div>
                <div class="row">
                    <div class="col-md-12 mb-3"><label for="CvLink" class="form-label fw-bold">CV Link</label><input type="text" name="CvLink" id="CvLink" value="<?= e($Model->CvLink) ?>" class="form-control" placeholder="https://drive.google.com/your-cv" /></div>
                    <div class="col-md-12 mb-3"><label for="WhyTeach" class="form-label fw-bold">Why do you want to teach? <span class="required-star">*</span></label><textarea name="WhyTeach" id="WhyTeach" class="form-control" rows="4" placeholder="Tell us why you're passionate about teaching..." required><?= e($Model->WhyTeach) ?></textarea></div>
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