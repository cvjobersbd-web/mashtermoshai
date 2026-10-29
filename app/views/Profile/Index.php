<?php
$ViewData['Title'] = 'My Profile';
$activeSection = (string)(input_str('section') ?? '');
?>

<style>
    .profile-wrapper { display: flex; gap: 30px; max-width: 1200px; margin: 0 auto; padding: 20px; min-height: calc(100vh - 200px); }
    .profile-sidebar { width: 280px; flex-shrink: 0; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 25px 0; height: fit-content; position: sticky; top: 100px; }

    /* ===== Avatar Section (fully centered) ===== */
    .profile-avatar-section {
        text-align: center;
        padding: 0 20px 20px;
        border-bottom: 2px solid #F5FBFF;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #02B8F5;
        margin-bottom: 12px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .profile-name-sidebar {
        font-size: 18px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 6px 0;
        text-align: center;
        width: 100%;
        line-height: 1.3;
    }

    .profile-role-sidebar {
        font-size: 13px;
        color: #64748B;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin: 0 0 4px 0;
        width: 100%;
        text-align: center;
    }

    .sidebar-menu-list { list-style: none; padding: 0; margin: 15px 0 0; }
    .sidebar-menu-list li a {
        display: flex; align-items: center; padding: 12px 25px;
        color: #334155; text-decoration: none; transition: all 0.3s ease;
        border-left: 3px solid transparent; font-size: 14px; font-weight: 500; cursor: pointer;
    }
    .sidebar-menu-list li a:hover { background-color: #F5FBFF; color: #08224A; }
    .sidebar-menu-list li a.active { background-color: #F5FBFF; color: #08224A; border-left-color: #02B8F5; font-weight: 600; }
    .sidebar-menu-list li a i { width: 24px; margin-right: 12px; font-size: 16px; text-align: center; color: #64748B; }
    .sidebar-menu-list li a.active i { color: #028EA5; }
    .sidebar-menu-list li a .badge-status { margin-left: auto; font-size: 10px; padding: 2px 10px; border-radius: 12px; background-color: #DCECF5; color: #08224A; }

    .profile-content { flex: 1; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; min-height: 500px; }
    .section-title { font-size: 22px; font-weight: 700; color: #08224A; margin-bottom: 5px; }
    .section-subtitle { color: #64748B; font-size: 14px; margin-bottom: 25px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { font-weight: 600; color: #08224A; font-size: 14px; margin-bottom: 5px; display: block; }
    .form-group .required { color: #dc3545; }
    .form-control { border-radius: 8px; border: 1px solid #ddd; padding: 10px 14px; width: 100%; transition: border-color 0.3s; }
    .form-control:focus { border-color: #02B8F5; box-shadow: 0 0 0 3px rgba(2,184,245, 0.1); outline: none; }

    .btn-save { background-color: #02B8F5; color: white; padding: 12px 40px; border-radius: 8px; border: none; font-weight: 600; transition: opacity 0.3s; cursor: pointer; }
    .btn-save:hover { opacity: 0.8; color: white; }
    .btn-save i { margin-right: 8px; }

    .image-upload-container { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .image-preview { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #02B8F5; }
    .image-upload-btn { display: inline-block; padding: 10px 24px; background-color: #F5FBFF; color: #08224A; border: 2px dashed #02B8F5; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; text-align: center; }
    .image-upload-btn:hover { background-color: #02B8F5; color: white; border-color: #02B8F5; }
    .image-upload-btn input[type="file"] { display: none; }

    .profile-section { display: none; animation: fadeIn 0.3s ease; }
    .profile-section.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .alert { padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; }
    .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

    @media (max-width: 992px) {
        .profile-wrapper { flex-direction: column; padding: 15px; }
        .profile-sidebar { width: 100%; position: static; }
        .profile-content { padding: 20px; }
        .image-upload-container { justify-content: center; }
    }
</style>

<div class="profile-wrapper">
    <div class="profile-sidebar">
        <div class="profile-avatar-section">
            <img src="<?= e((string)$Model->ProfileImageLink === '' ? 'https://i.ibb.co/fd9sY88Y/IMG-6916-1.png' : $Model->ProfileImageLink) ?>"
                 alt="Profile" class="profile-avatar" id="profileAvatar" />

            <h3 class="profile-name-sidebar"><?= e($Model->FullName) ?></h3>

            <p class="profile-role-sidebar">
                <span class="badge bg-success"><?= e($Model->Role) ?></span>
                <span class="badge bg-info text-white"><?= e($Model->UserName) ?></span>
            </p>
            <a href="/Profile/Show" style="display:inline-flex;align-items:center;gap:7px;margin-top:10px;padding:8px 16px;border-radius:20px;background:#02B8F5;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">
                <i class="fas fa-eye"></i> View my profile
            </a>
        </div>

        <ul class="sidebar-menu-list">
            <li><a href="#" class="<?= $activeSection === 'basic' ? 'active' : '' ?>" onclick="showSection('basic')"><i class="fas fa-id-card"></i> Basic Information <span class="badge-status"><?= (string)$Model->FullName === '' ? 'Incomplete' : 'Complete' ?></span></a></li>
            <li><a href="#" class="<?= $activeSection === 'additional' ? 'active' : '' ?>" onclick="showSection('additional')"><i class="fas fa-info-circle"></i> Additional Information <span class="badge-status"><?= (string)$Model->Gender === '' ? 'Incomplete' : 'Complete' ?></span></a></li>
            <li><a href="#" class="<?= $activeSection === 'address' ? 'active' : '' ?>" onclick="showSection('address')"><i class="fas fa-map-marker-alt"></i> Address <span class="badge-status"><?= (string)$Model->Country === '' ? 'Incomplete' : 'Complete' ?></span></a></li>
            <li><a href="#" class="<?= $activeSection === 'education' ? 'active' : '' ?>" onclick="showSection('education')"><i class="fas fa-graduation-cap"></i> Education <span class="badge-status"><?= (string)$Model->EducationLevel === '' ? 'Incomplete' : 'Complete' ?></span></a></li>
            <li><a href="#" class="<?= $activeSection === 'links' ? 'active' : '' ?>" onclick="showSection('links')"><i class="fas fa-link"></i> Important Links <span class="badge-status"><?= (string)$Model->CvLink === '' ? 'Incomplete' : 'Complete' ?></span></a></li>
            
            <!-- ✅ Order History ও My Progress মেনু ডিলিট করা হয়েছে -->
            
            <li><a href="#" class="<?= $activeSection === 'become-tutor' ? 'active' : '' ?>" onclick="showSection('become-tutor')"><i class="fas fa-chalkboard-teacher"></i> Become a Tutor <span class="badge-status" id="tutorStatusBadge">Check</span></a></li>
            <li style="border-top: 2px solid #F5FBFF; margin-top: 10px; padding-top: 10px;">
                <a href="/Profile/Show?tab=about#ab-password" style="color: #b8860b;"><i class="fas fa-key"></i> Change Password</a>
            </li>
        </ul>
    </div>

    <div class="profile-content">
        <?php if ($TempData['Success'] !== null): ?> <div class="alert alert-success"><?= e($TempData['Success']) ?></div> <?php endif; ?>
        <?php if ($ViewBag->Error !== null): ?> <div class="alert alert-danger"><?= e($ViewBag->Error) ?></div> <?php endif; ?>
        <?php if ($TempData['Info'] !== null): ?> <div class="alert alert-info"><?= e($TempData['Info']) ?></div> <?php endif; ?>

        <!-- Basic Information -->
        <div id="section-basic" class="profile-section <?= $activeSection === 'basic' ? 'active' : '' ?>">
            <h2 class="section-title"><i class="fas fa-id-card me-2" style="color:#08224A;"></i>Basic Information</h2>
            <p class="section-subtitle">Update your basic profile information</p>
            <form method="post" action="/Profile/UpdateBasic">
                <?= csrf_field() ?>
                <input type="hidden" name="Id" value="<?= e($Model->Id) ?>" />
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Full Name <span class="required">*</span></label><input type="text" name="FullName" class="form-control" value="<?= e($Model->FullName) ?>" required /></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Email <span class="required">*</span></label><input type="email" name="Email" class="form-control" value="<?= e($Model->Email) ?>" required /></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Mobile Number <span class="required">*</span></label><input type="tel" name="MobileNumber" class="form-control" value="<?= e($Model->MobileNumber) ?>" placeholder="+880 1234 567890" required /></div></div>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Update Basic Info</button>
            </form>
        </div>

        <!-- Additional Information -->
        <div id="section-additional" class="profile-section <?= $activeSection === 'additional' ? 'active' : '' ?>">
            <h2 class="section-title"><i class="fas fa-info-circle me-2" style="color:#08224A;"></i>Additional Information</h2>
            <p class="section-subtitle">Tell us more about yourself</p>
            <form method="post" action="/Profile/UpdateAdditional">
                <?= csrf_field() ?>
                <input type="hidden" name="Id" value="<?= e($Model->Id) ?>" />
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Gender</label><select name="Gender" class="form-control"><option value="">Select Gender</option><option value="Male"<?= $Model->Gender === 'Male' ? ' selected="selected"' : '' ?>>Male</option><option value="Female"<?= $Model->Gender === 'Female' ? ' selected="selected"' : '' ?>>Female</option><option value="Other"<?= $Model->Gender === 'Other' ? ' selected="selected"' : '' ?>>Other</option></select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Age Range</label><select name="AgeRange" class="form-control"><option value="">Select Age Range</option><option value="Under 18"<?= $Model->AgeRange === 'Under 18' ? ' selected="selected"' : '' ?>>Under 18</option><option value="18-24"<?= $Model->AgeRange === '18-24' ? ' selected="selected"' : '' ?>>18-24</option><option value="25-34"<?= $Model->AgeRange === '25-34' ? ' selected="selected"' : '' ?>>25-34</option><option value="35-44"<?= $Model->AgeRange === '35-44' ? ' selected="selected"' : '' ?>>35-44</option><option value="45-54"<?= $Model->AgeRange === '45-54' ? ' selected="selected"' : '' ?>>45-54</option><option value="55+"<?= $Model->AgeRange === '55+' ? ' selected="selected"' : '' ?>>55+</option></select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Primary Device Type</label><select name="PrimaryDeviceType" class="form-control"><option value="">Select Primary Device</option><option value="Mobile"<?= $Model->PrimaryDeviceType === 'Mobile' ? ' selected="selected"' : '' ?>>Mobile</option><option value="Tablet"<?= $Model->PrimaryDeviceType === 'Tablet' ? ' selected="selected"' : '' ?>>Tablet</option><option value="Laptop"<?= $Model->PrimaryDeviceType === 'Laptop' ? ' selected="selected"' : '' ?>>Laptop</option><option value="Computer"<?= $Model->PrimaryDeviceType === 'Computer' ? ' selected="selected"' : '' ?>>Computer</option></select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Years of Experience</label><select name="YearsOfExperience" class="form-control"><option value="">Select Experience</option><option value="0-1 year"<?= $Model->YearsOfExperience === '0-1 year' ? ' selected="selected"' : '' ?>>0-1 year</option><option value="1-2 years"<?= $Model->YearsOfExperience === '1-2 years' ? ' selected="selected"' : '' ?>>1-2 years</option><option value="3-5 years"<?= $Model->YearsOfExperience === '3-5 years' ? ' selected="selected"' : '' ?>>3-5 years</option><option value="5-10 years"<?= $Model->YearsOfExperience === '5-10 years' ? ' selected="selected"' : '' ?>>5-10 years</option><option value="10+ years"<?= $Model->YearsOfExperience === '10+ years' ? ' selected="selected"' : '' ?>>10+ years</option></select></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Area Type</label><select name="AreaType" class="form-control"><option value="">Select Area Type</option><option value="Village"<?= $Model->AreaType === 'Village' ? ' selected="selected"' : '' ?>>Village</option><option value="Town"<?= $Model->AreaType === 'Town' ? ' selected="selected"' : '' ?>>Town</option><option value="City"<?= $Model->AreaType === 'City' ? ' selected="selected"' : '' ?>>City</option></select></div></div>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Update Additional Info</button>
            </form>
        </div>

        <!-- Address -->
        <div id="section-address" class="profile-section <?= $activeSection === 'address' ? 'active' : '' ?>">
            <h2 class="section-title"><i class="fas fa-map-marker-alt me-2" style="color:#08224A;"></i>Address</h2>
            <p class="section-subtitle">Update your address information</p>
            <form method="post" action="/Profile/UpdateAddress">
                <?= csrf_field() ?>
                <input type="hidden" name="Id" value="<?= e($Model->Id) ?>" />
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Country</label><input type="text" name="Country" class="form-control" value="<?= e($Model->Country) ?>" placeholder="Enter your country" /></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Street Address</label><input type="text" name="StreetAddress" class="form-control" value="<?= e($Model->StreetAddress) ?>" placeholder="Enter your street address" /></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Permanent Address</label><input type="text" name="PermanentAddress" class="form-control" value="<?= e($Model->PermanentAddress) ?>" placeholder="Enter your permanent address" /></div></div>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Update Address</button>
            </form>
        </div>

        <!-- Education -->
        <div id="section-education" class="profile-section <?= $activeSection === 'education' ? 'active' : '' ?>">
            <h2 class="section-title"><i class="fas fa-graduation-cap me-2" style="color:#08224A;"></i>Education</h2>
            <p class="section-subtitle">Update your educational information</p>
            <form method="post" action="/Profile/UpdateEducation">
                <?= csrf_field() ?>
                <input type="hidden" name="Id" value="<?= e($Model->Id) ?>" />
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Education Level</label><select name="EducationLevel" class="form-control"><option value="">Select Education Level</option><option value="JSC/JDC/8 pass"<?= $Model->EducationLevel === 'JSC/JDC/8 pass' ? ' selected="selected"' : '' ?>>JSC/JDC/8 pass</option><option value="Secondary"<?= $Model->EducationLevel === 'Secondary' ? ' selected="selected"' : '' ?>>Secondary</option><option value="Higher Secondary"<?= $Model->EducationLevel === 'Higher Secondary' ? ' selected="selected"' : '' ?>>Higher Secondary</option><option value="Diploma"<?= $Model->EducationLevel === 'Diploma' ? ' selected="selected"' : '' ?>>Diploma</option><option value="Bachelor/Honors"<?= $Model->EducationLevel === 'Bachelor/Honors' ? ' selected="selected"' : '' ?>>Bachelor/Honors</option><option value="Masters"<?= $Model->EducationLevel === 'Masters' ? ' selected="selected"' : '' ?>>Masters</option><option value="PhD"<?= $Model->EducationLevel === 'PhD' ? ' selected="selected"' : '' ?>>PhD</option></select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Currently Studying</label><select name="CurrentStudyStatus" class="form-control"><option value="No"<?= $Model->CurrentStudyStatus === 'No' ? ' selected="selected"' : '' ?>>No</option><option value="Yes"<?= $Model->CurrentStudyStatus === 'Yes' ? ' selected="selected"' : '' ?>>Yes</option></select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Exam/Degree Title</label><input type="text" name="ExamDegreeTitle" class="form-control" value="<?= e($Model->ExamDegreeTitle) ?>" placeholder="e.g. BSc in Computer Science" /></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Institution Name</label><input type="text" name="InstitutionName" class="form-control" value="<?= e($Model->InstitutionName) ?>" placeholder="Enter institution name" /></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Passing Year</label><input type="text" name="PassingYear" class="form-control" value="<?= e($Model->PassingYear) ?>" placeholder="e.g. 2020" /></div></div>
                    <div class="col-md-6"><div class="form-group"><label>CSE/CS Student</label><select name="IsCSEStudent" class="form-control"><option value="false"<?= $Model->IsCSEStudent === false ? ' selected="selected"' : '' ?>>No</option><option value="true"<?= $Model->IsCSEStudent === true ? ' selected="selected"' : '' ?>>Yes</option></select></div></div>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Update Education</button>
            </form>
        </div>

        <!-- Important Links -->
        <div id="section-links" class="profile-section <?= $activeSection === 'links' ? 'active' : '' ?>">
            <h2 class="section-title"><i class="fas fa-link me-2" style="color:#08224A;"></i>Important Links</h2>
            <p class="section-subtitle">Add your professional links</p>
            <form method="post" action="/Profile/UpdateLinks">
                <?= csrf_field() ?>
                <input type="hidden" name="Id" value="<?= e($Model->Id) ?>" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Profile Image</label>
                            <div class="image-upload-container">
                                <img src="<?= e((string)$Model->ProfileImageLink === '' ? 'https://i.ibb.co/fd9sY88Y/IMG-6916-1.png' : $Model->ProfileImageLink) ?>" alt="Profile" class="image-preview" id="previewImage" />
                                <div>
                                    <label class="image-upload-btn"><i class="fas fa-upload me-2"></i> Upload Image<input type="file" id="profileImageInput" accept="image/*" /></label>
                                    <p class="text-muted" style="font-size:12px; margin-top:5px;">Max 5MB. JPG, PNG, GIF</p>
                                </div>
                            </div>
                            <input type="hidden" name="ProfileImageLink" value="<?= e($Model->ProfileImageLink) ?>" />
                        </div>
                    </div>
                    <div class="col-md-6"><div class="form-group"><label>CV Link</label><input type="url" name="CvLink" class="form-control" value="<?= e($Model->CvLink) ?>" placeholder="https://drive.google.com/your-cv" /></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Github Profile</label><input type="url" name="GithubProfile" class="form-control" value="<?= e($Model->GithubProfile) ?>" placeholder="https://github.com/username" /></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Portfolio Link</label><input type="url" name="PortfolioLink" class="form-control" value="<?= e($Model->PortfolioLink) ?>" placeholder="https://yourportfolio.com" /></div></div>
                    <div class="col-md-12"><div class="form-group"><label>LinkedIn Profile</label><input type="url" name="LinkedInProfile" class="form-control" value="<?= e($Model->LinkedInProfile) ?>" placeholder="https://linkedin.com/in/username" /></div></div>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Update Links</button>
            </form>
        </div>

        <!-- Become a Tutor -->
        <div id="section-become-tutor" class="profile-section <?= $activeSection === 'become-tutor' ? 'active' : '' ?>">
            <h2 class="section-title"><i class="fas fa-chalkboard-teacher me-2" style="color:#08224A;"></i>Become a Tutor</h2>
            <p class="section-subtitle">Share your knowledge and earn money by teaching others</p>

            <div class="card shadow-sm p-4" id="tutorStatusCard">
                <div class="text-center py-3" id="tutorStatusLoading">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Checking your status...</p>
                </div>
                <div id="tutorStatusContent" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function showSection(section) {
        document.querySelectorAll('.profile-section').forEach(el => el.classList.remove('active'));
        var target = document.getElementById('section-' + section);
        if (target) target.classList.add('active');
        document.querySelectorAll('.sidebar-menu-list li a').forEach(el => el.classList.remove('active'));
        var url = new URL(window.location.href);
        url.searchParams.set('section', section);
        window.history.pushState({}, '', url);

        if (section === 'become-tutor') {
            checkTutorStatus();
        }
    }

    // Profile Image Upload
    document.getElementById('profileImageInput').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) { alert('Image size must be less than 5MB'); return; }
        var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) { alert('Please upload JPG, PNG, GIF, or WEBP image'); return; }

        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('profileAvatar').src = e.target.result;
        };
        reader.readAsDataURL(file);

        var formData = new FormData();
        formData.append('profileImage', file);

        var token = document.querySelector('input[name="__RequestVerificationToken"]');
        var headers = {};
        if (token) { headers['RequestVerificationToken'] = token.value; }

        fetch('/Profile/UploadProfileImage', {
            method: 'POST',
            body: formData,
            headers: headers
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var img = document.getElementById('previewImage');
                img.src = data.imageUrl + '?t=' + new Date().getTime();
                document.getElementById('profileAvatar').src = data.imageUrl + '?t=' + new Date().getTime();
                alert('Profile image uploaded successfully!');
            } else {
                alert(data.message || 'Failed to upload image');
            }
        })
        .catch(error => { alert('Error uploading image'); });
    });

    // ===== Tutor Status Check =====
    function checkTutorStatus() {
        var statusDiv = document.getElementById('tutorStatusContent');
        var loadingDiv = document.getElementById('tutorStatusLoading');

        fetch('/Profile/GetTeacherRequestStatus')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            loadingDiv.style.display = 'none';
            statusDiv.style.display = 'block';

            if (data.success) {
                if (data.isTeacher) {
                    statusDiv.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-check-circle" style="font-size: 48px; color: #2ecc71;"></i>
                            <h5 class="mt-3" style="color:#08224A;">You are a Teacher!</h5>
                            <p class="text-muted">You have been approved as a tutor. Access your teacher dashboard below.</p>
                            <a href="/Teacher/TeacherDashboard" class="btn btn-success mt-2" style="background-color:#02B8F5; border-color:#02B8F5;">
                                <i class="fas fa-chart-pie me-2"></i>Go to Teacher Dashboard
                            </a>
                        </div>
                    `;
                } else if (data.status === 'Pending') {
                    statusDiv.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-clock" style="font-size: 48px; color: #f39c12;"></i>
                            <h5 class="mt-3" style="color:#08224A;">Application Pending</h5>
                            <p class="text-muted">Your teacher application is under review by the admin team.</p>
                            <p class="text-muted small">You will be notified once a decision is made.</p>
                        </div>
                    `;
                } else if (data.status === 'Rejected') {
                    statusDiv.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-times-circle" style="font-size: 48px; color: #e74c3c;"></i>
                            <h5 class="mt-3" style="color:#e74c3c;">Application Rejected</h5>
                            <p class="text-muted">Your teacher application was not approved. You can try again.</p>
                            <a href="/Teacher/RequestForm" class="btn btn-primary mt-2" style="background-color:#02B8F5; border-color:#02B8F5;">
                                <i class="fas fa-redo me-2"></i>Apply Again
                            </a>
                        </div>
                    `;
                } else {
                    statusDiv.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-chalkboard-teacher" style="font-size: 48px; color: #08224A; opacity:0.3;"></i>
                            <h5 class="mt-3" style="color:#08224A;">Become a Tutor</h5>
                            <p class="text-muted">Share your knowledge with the world! Apply to become a tutor and start earning.</p>
                            <a href="/Teacher/RequestForm" class="btn btn-primary mt-2" style="background-color:#02B8F5; border-color:#02B8F5;">
                                <i class="fas fa-paper-plane me-2"></i>Apply Now
                            </a>
                        </div>
                    `;
                }
            } else {
                statusDiv.innerHTML = `<div class="text-center"><i class="fas fa-exclamation-circle" style="font-size: 48px; color: #e74c3c;"></i><h5 class="mt-3" style="color:#e74c3c;">Error</h5><p class="text-muted">Could not load your status. Please try again.</p><button onclick="checkTutorStatus()" class="btn btn-secondary mt-2"><i class="fas fa-sync me-2"></i>Retry</button></div>`;
            }
        })
        .catch(function(error) {
            loadingDiv.style.display = 'none';
            statusDiv.style.display = 'block';
            statusDiv.innerHTML = `<div class="text-center"><i class="fas fa-exclamation-circle" style="font-size: 48px; color: #e74c3c;"></i><h5 class="mt-3" style="color:#e74c3c;">Error</h5><p class="text-muted">Could not load your status. Please try again.</p><button onclick="checkTutorStatus()" class="btn btn-secondary mt-2"><i class="fas fa-sync me-2"></i>Retry</button></div>`;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var section = urlParams.get('section') || 'basic';
        showSection(section);

        if (section === 'become-tutor') {
            checkTutorStatus();
        }
    });

    document.querySelector('a[onclick*="showSection(\'become-tutor\')"]')?.addEventListener('click', function() {
        setTimeout(checkTutorStatus, 300);
    });
</script>