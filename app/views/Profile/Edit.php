<?php
$ViewData['Title'] = 'Edit Profile';
?>

<style>
    .profile-form-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .form-section {
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 2px solid #F5FBFF;
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .form-section-title {
        font-size: 18px;
        font-weight: 600;
        color: #08224A;
        margin-bottom: 20px;
    }

    .form-section-title i {
        margin-right: 10px;
        color: #028EA5;
    }

    .form-control:focus {
        border-color: #02B8F5;
        box-shadow: 0 0 0 0.2rem rgba(2,184,245, 0.25);
    }

    .btn-save {
        background-color: #02B8F5;
        color: white;
        padding: 12px 40px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        transition: opacity 0.3s;
        width: 100%;
    }

    .btn-save:hover {
        opacity: 0.8;
        color: white;
    }

    .btn-cancel {
        background-color: #6c757d;
        color: white;
        padding: 12px 40px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        transition: opacity 0.3s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-cancel:hover {
        opacity: 0.8;
        color: white;
    }

    .required-star {
        color: #dc3545;
        margin-left: 3px;
    }

    .field-validation-error {
        color: #dc3545;
        font-size: 12px;
    }
</style>

<div class="container py-5">
    <div class="profile-form-container">
        <h2 class="text-center mb-4" style="color:#08224A;">
            <i class="fas fa-user-edit me-2"></i>Complete Your Profile
        </h2>
        <p class="text-center text-muted mb-4">
            Fill in all the details to complete your profile
        </p>

        <?php if ($ViewBag->Error !== null): ?>
            <div class="alert alert-danger"><?= e($ViewBag->Error) ?></div>
        <?php endif; ?>

        <?php if ($TempData['Success'] !== null): ?>
            <div class="alert alert-success"><?= e($TempData['Success']) ?></div>
        <?php endif; ?>

        <form method="post" action="/Profile/Edit">
            <?= csrf_field() ?>
            <input type="hidden" name="Id" id="Id" value="<?= e($Model->Id) ?>" />
            <input type="hidden" name="UserName" id="UserName" value="<?= e($Model->UserName) ?>" />
            <input type="hidden" name="Role" id="Role" value="<?= e($Model->Role) ?>" />

            <!-- ===== বেসিক ইনফো ===== -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-id-card"></i> Basic Information</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="FullName" class="form-label fw-bold">Full Name <span class="required-star">*</span></label>
                        <input type="text" name="FullName" id="FullName" value="<?= e($Model->FullName) ?>" class="form-control" placeholder="Enter your full name" />
                        <span class="text-danger"><?= field_error('FullName') ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="Email" class="form-label fw-bold">Email <span class="required-star">*</span></label>
                        <input type="text" name="Email" id="Email" value="<?= e($Model->Email) ?>" class="form-control" placeholder="Enter your email" />
                        <span class="text-danger"><?= field_error('Email') ?></span>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="MobileNumber" class="form-label fw-bold">Mobile Number <span class="required-star">*</span></label>
                        <input type="text" name="MobileNumber" id="MobileNumber" value="<?= e($Model->MobileNumber) ?>" class="form-control" placeholder="Enter your mobile number" />
                        <span class="text-danger"><?= field_error('MobileNumber') ?></span>
                    </div>
                </div>
            </div>

            <!-- ===== অ্যাডিশনাল ইনফো ===== -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-info-circle"></i> Additional Information</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="Gender" class="form-label fw-bold">Gender <span class="required-star">*</span></label>
                        <select name="Gender" id="Gender" class="form-control">
                            <option value="" <?= (string)$Model->Gender === '' ? 'selected' : '' ?>>Select Gender</option>
                            <option value="Male" <?= (string)$Model->Gender === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= (string)$Model->Gender === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= (string)$Model->Gender === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="AgeRange" class="form-label fw-bold">Age Range <span class="required-star">*</span></label>
                        <select name="AgeRange" id="AgeRange" class="form-control">
                            <option value="" <?= (string)$Model->AgeRange === '' ? 'selected' : '' ?>>Select Age Range</option>
                            <option value="Under 18" <?= (string)$Model->AgeRange === 'Under 18' ? 'selected' : '' ?>>Under 18</option>
                            <option value="18-24" <?= (string)$Model->AgeRange === '18-24' ? 'selected' : '' ?>>18-24</option>
                            <option value="25-34" <?= (string)$Model->AgeRange === '25-34' ? 'selected' : '' ?>>25-34</option>
                            <option value="35-44" <?= (string)$Model->AgeRange === '35-44' ? 'selected' : '' ?>>35-44</option>
                            <option value="45-54" <?= (string)$Model->AgeRange === '45-54' ? 'selected' : '' ?>>45-54</option>
                            <option value="55+" <?= (string)$Model->AgeRange === '55+' ? 'selected' : '' ?>>55+</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="PrimaryDeviceType" class="form-label fw-bold">Primary Device <span class="required-star">*</span></label>
                        <select name="PrimaryDeviceType" id="PrimaryDeviceType" class="form-control">
                            <option value="" <?= (string)$Model->PrimaryDeviceType === '' ? 'selected' : '' ?>>Select Primary Device</option>
                            <option value="Mobile" <?= (string)$Model->PrimaryDeviceType === 'Mobile' ? 'selected' : '' ?>>Mobile</option>
                            <option value="Tablet" <?= (string)$Model->PrimaryDeviceType === 'Tablet' ? 'selected' : '' ?>>Tablet</option>
                            <option value="Laptop" <?= (string)$Model->PrimaryDeviceType === 'Laptop' ? 'selected' : '' ?>>Laptop</option>
                            <option value="Computer" <?= (string)$Model->PrimaryDeviceType === 'Computer' ? 'selected' : '' ?>>Computer</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="YearsOfExperience" class="form-label fw-bold">Years of Experience</label>
                        <select name="YearsOfExperience" id="YearsOfExperience" class="form-control">
                            <option value="" <?= (string)$Model->YearsOfExperience === '' ? 'selected' : '' ?>>Select Experience</option>
                            <option value="0-1 year" <?= (string)$Model->YearsOfExperience === '0-1 year' ? 'selected' : '' ?>>0-1 year</option>
                            <option value="1-2 years" <?= (string)$Model->YearsOfExperience === '1-2 years' ? 'selected' : '' ?>>1-2 years</option>
                            <option value="3-5 years" <?= (string)$Model->YearsOfExperience === '3-5 years' ? 'selected' : '' ?>>3-5 years</option>
                            <option value="5-10 years" <?= (string)$Model->YearsOfExperience === '5-10 years' ? 'selected' : '' ?>>5-10 years</option>
                            <option value="10+ years" <?= (string)$Model->YearsOfExperience === '10+ years' ? 'selected' : '' ?>>10+ years</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="AreaType" class="form-label fw-bold">Area Type</label>
                        <select name="AreaType" id="AreaType" class="form-control">
                            <option value="" <?= (string)$Model->AreaType === '' ? 'selected' : '' ?>>Select Area Type</option>
                            <option value="Village" <?= (string)$Model->AreaType === 'Village' ? 'selected' : '' ?>>Village</option>
                            <option value="Town" <?= (string)$Model->AreaType === 'Town' ? 'selected' : '' ?>>Town</option>
                            <option value="City" <?= (string)$Model->AreaType === 'City' ? 'selected' : '' ?>>City</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ===== অ্যাড্রেস ===== -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-map-marker-alt"></i> Address</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="Country" class="form-label fw-bold">Country <span class="required-star">*</span></label>
                        <input type="text" name="Country" id="Country" value="<?= e($Model->Country) ?>" class="form-control" placeholder="Enter your country" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="StreetAddress" class="form-label fw-bold">Street Address</label>
                        <input type="text" name="StreetAddress" id="StreetAddress" value="<?= e($Model->StreetAddress) ?>" class="form-control" placeholder="Enter your street address" />
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="PermanentAddress" class="form-label fw-bold">Permanent Address</label>
                        <input type="text" name="PermanentAddress" id="PermanentAddress" value="<?= e($Model->PermanentAddress) ?>" class="form-control" placeholder="Enter your permanent address" />
                    </div>
                </div>
            </div>

            <!-- ===== এডুকেশন ===== -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-graduation-cap"></i> Education</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="EducationLevel" class="form-label fw-bold">Education Level <span class="required-star">*</span></label>
                        <select name="EducationLevel" id="EducationLevel" class="form-control">
                            <option value="" <?= (string)$Model->EducationLevel === '' ? 'selected' : '' ?>>Select Education Level</option>
                            <option value="JSC/JDC/8 pass" <?= (string)$Model->EducationLevel === 'JSC/JDC/8 pass' ? 'selected' : '' ?>>JSC/JDC/8 pass</option>
                            <option value="Secondary" <?= (string)$Model->EducationLevel === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                            <option value="Higher Secondary" <?= (string)$Model->EducationLevel === 'Higher Secondary' ? 'selected' : '' ?>>Higher Secondary</option>
                            <option value="Diploma" <?= (string)$Model->EducationLevel === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                            <option value="Bachelor/Honors" <?= (string)$Model->EducationLevel === 'Bachelor/Honors' ? 'selected' : '' ?>>Bachelor/Honors</option>
                            <option value="Masters" <?= (string)$Model->EducationLevel === 'Masters' ? 'selected' : '' ?>>Masters</option>
                            <option value="PhD" <?= (string)$Model->EducationLevel === 'PhD' ? 'selected' : '' ?>>PhD (Doctor of Philosophy)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="CurrentStudyStatus" class="form-label fw-bold">Currently Studying</label>
                        <select name="CurrentStudyStatus" id="CurrentStudyStatus" class="form-control">
                            <option value="No" <?= (string)$Model->CurrentStudyStatus === 'No' ? 'selected' : '' ?>>No</option>
                            <option value="Yes" <?= (string)$Model->CurrentStudyStatus === 'Yes' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ExamDegreeTitle" class="form-label fw-bold">Exam/Degree Title</label>
                        <input type="text" name="ExamDegreeTitle" id="ExamDegreeTitle" value="<?= e($Model->ExamDegreeTitle) ?>" class="form-control" placeholder="e.g. BSc in Computer Science" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="InstitutionName" class="form-label fw-bold">Institution Name</label>
                        <input type="text" name="InstitutionName" id="InstitutionName" value="<?= e($Model->InstitutionName) ?>" class="form-control" placeholder="Enter institution name" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="PassingYear" class="form-label fw-bold">Passing Year</label>
                        <input type="text" name="PassingYear" id="PassingYear" value="<?= e($Model->PassingYear) ?>" class="form-control" placeholder="e.g. 2020" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="IsCSEStudent" class="form-label fw-bold">CSE/CS Student</label>
                        <select name="IsCSEStudent" id="IsCSEStudent" class="form-control">
                            <option value="false" <?= ($Model->IsCSEStudent ? 'true' : 'false') === 'false' ? 'selected' : '' ?>>No</option>
                            <option value="true" <?= ($Model->IsCSEStudent ? 'true' : 'false') === 'true' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ===== ইম্পোর্ট্যান্ট লিংক ===== -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-link"></i> Important Links</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="CvLink" class="form-label fw-bold">CV Link</label>
                        <input type="text" name="CvLink" id="CvLink" value="<?= e($Model->CvLink) ?>" class="form-control" placeholder="https://drive.google.com/your-cv" />
                        <span class="text-danger"><?= field_error('CvLink') ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="GithubProfile" class="form-label fw-bold">Github Profile</label>
                        <input type="text" name="GithubProfile" id="GithubProfile" value="<?= e($Model->GithubProfile) ?>" class="form-control" placeholder="https://github.com/username" />
                        <span class="text-danger"><?= field_error('GithubProfile') ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="PortfolioLink" class="form-label fw-bold">Portfolio Link</label>
                        <input type="text" name="PortfolioLink" id="PortfolioLink" value="<?= e($Model->PortfolioLink) ?>" class="form-control" placeholder="https://yourportfolio.com" />
                        <span class="text-danger"><?= field_error('PortfolioLink') ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="LinkedInProfile" class="form-label fw-bold">LinkedIn Profile</label>
                        <input type="text" name="LinkedInProfile" id="LinkedInProfile" value="<?= e($Model->LinkedInProfile) ?>" class="form-control" placeholder="https://linkedin.com/in/username" />
                        <span class="text-danger"><?= field_error('LinkedInProfile') ?></span>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="ProfileImageLink" class="form-label fw-bold">Profile Image Link</label>
                        <input type="text" name="ProfileImageLink" id="ProfileImageLink" value="<?= e($Model->ProfileImageLink) ?>" class="form-control" placeholder="https://i.ibb.co/your-image.jpg" />
                        <span class="text-danger"><?= field_error('ProfileImageLink') ?></span>
                    </div>
                </div>
            </div>

            <!-- ===== বাটন ===== -->
            <div class="d-flex gap-3 mt-4">
                <a href="/Profile" class="btn-cancel flex-grow-1">Cancel</a>
                <button type="submit" class="btn-save flex-grow-1">
                    <i class="fas fa-save me-2"></i>Save Profile
                </button>
            </div>
        </form>
    </div>
</div>

<?php section_start('Scripts'); ?>
    <?php partial('Shared/_ValidationScriptsPartial'); ?>
<?php section_end(); ?>