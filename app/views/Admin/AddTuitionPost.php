<?php
$ViewData['Title'] = 'Add Tuition Post - Admin Panel';
?>

<style>
    /* ======================
               MAIN CONTENT LAYOUT
               ====================== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ======================
               PAGE HEADER
               ====================== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 14px;
    }

        .page-header h2 {
            color: #08224A;
            margin: 0;
            font-family: 'Georgia', serif;
            font-weight: 700;
            font-size: 26px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

            .page-header h2 i {
                color: #028EA5;
            }

        .page-header p {
            color: #64748B;
            margin: 6px 0 0 0;
            font-size: 14px;
        }

    /* ======================
               FORM CARD
               ====================== */
    .form-card {
        background: white;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        padding: 32px;
    }

    /* ======================
               SECTION TITLES
               ====================== */
    .form-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #08224A;
        margin: 28px 0 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #F5FBFF;
        display: flex;
        align-items: center;
        gap: 9px;
        letter-spacing: 0.2px;
    }

        .form-section-title:first-of-type {
            margin-top: 0;
        }

        .form-section-title i {
            color: #028EA5;
            font-size: 15px;
        }

    /* ======================
               FORM LABELS
               ====================== */
    .form-label {
        font-weight: 700;
        color: #08224A;
        font-size: 13px;
        margin-bottom: 7px;
        display: block;
        letter-spacing: 0.2px;
    }

        .form-label .req {
            color: #e74c3c;
            margin-left: 3px;
        }

    /* ======================
               FORM CONTROLS
               ====================== */
    .form-control,
    .form-select {
        border-radius: 11px;
        border: 1.5px solid #DCECF5;
        padding: 12px 15px;
        font-size: 14px;
        background: #F5FBFF;
        transition: all 0.22s;
        width: 100%;
        outline: none;
        font-family: inherit;
        color: #08224A;
    }

        .form-control:focus,
        .form-select:focus {
            border-color: #02B8F5;
            box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
            background: white;
        }

        .form-control::placeholder {
            color: #A0B3C6;
        }

    textarea.form-control {
        resize: vertical;
        min-height: 110px;
        line-height: 1.6;
    }

    /* ======================
               INFO NOTE
               ====================== */
    .info-note {
        font-size: 12px;
        color: #7A8CA0;
        margin-top: 6px;
        display: block;
        line-height: 1.5;
    }

        .info-note i {
            color: #028EA5;
            margin-right: 4px;
        }

    /* ======================
               VALIDATION ERROR
               ====================== */
    .field-error {
        font-size: 12.5px;
        color: #e74c3c;
        margin-top: 5px;
        display: block;
        font-weight: 500;
    }

    /* ======================
               ALERT BOXES
               ====================== */
    .form-alert {
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 22px;
        font-size: 13.5px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.5;
    }

        .form-alert.danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }

        .form-alert.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .form-alert i {
            font-size: 16px;
            margin-top: 1px;
            flex-shrink: 0;
        }

    /* ======================
               BUTTONS
               ====================== */
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 2px solid #F5FBFF;
        flex-wrap: wrap;
    }

    .btn-save {
        background: #02B8F5;
        color: white;
        padding: 13px 40px;
        border-radius: 11px;
        border: none;
        font-weight: 700;
        font-size: 14.5px;
        cursor: pointer;
        transition: all 0.22s;
        display: inline-flex;
        align-items: center;
        gap: 9px;
    }

        .btn-save:hover {
            background: #0099D6;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2,184,245,0.25);
        }

        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

    .btn-cancel {
        background: white;
        color: #64748B;
        padding: 13px 28px;
        border-radius: 11px;
        border: 1.5px solid #DCECF5;
        font-weight: 600;
        font-size: 14.5px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.22s;
    }

        .btn-cancel:hover {
            background: #F5FBFF;
            color: #08224A;
            border-color: #02B8F5;
        }

    /* ======================
               LIVE PREVIEW
               ====================== */
    .preview-box {
        background: linear-gradient(135deg, #FFF7E0 0%, #FFFFFF 100%);
        border-radius: 14px;
        padding: 20px 22px;
        margin-top: 24px;
        border-left: 5px solid #FFB300;
        box-shadow: 0 2px 12px rgba(244,199,68,0.15);
    }

    .preview-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        color: #8a5a00;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

        .preview-title i {
            font-size: 12px;
        }

    .preview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 18px;
        font-size: 13px;
        color: #334155;
    }

    .preview-item {
        display: flex;
        align-items: flex-start;
        gap: 7px;
    }

        .preview-item i {
            color: #028EA5;
            margin-top: 3px;
            font-size: 12px;
            width: 14px;
            text-align: center;
            flex-shrink: 0;
        }

        .preview-item strong {
            color: #08224A;
            margin-right: 4px;
        }

    /* ======================
               RESPONSIVE
               ====================== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }
    }

    @media (max-width: 576px) {
        .form-card {
            padding: 22px 18px;
            border-radius: 14px;
        }

        .page-header h2 {
            font-size: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-save,
        .btn-cancel {
            width: 100%;
            justify-content: center;
        }

        .preview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ============================================================ -->
<!-- ADMIN SIDEBAR                                                 -->
<!-- ============================================================ -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ============================================================ -->
<!-- MAIN CONTENT                                                  -->
<!-- ============================================================ -->
<div class="admin-main-content">

    <!-- ======================
         PAGE HEADER
         ====================== -->
    <div class="page-header">
        <div>
            <h2>
                <i class="fas fa-plus-circle"></i> Add New Tuition Post
            </h2>
            <p>নতুন একটি Tuition Circular পোস্ট তৈরি করুন</p>
        </div>
        <a href="/Admin/TuitionPosts" class="btn-cancel">
            <i class="fas fa-arrow-left"></i> Back to Posts
        </a>
    </div>

    <!-- ======================
         VALIDATION ERRORS
         ====================== -->
    <?php if (!empty($GLOBALS['__ModelErrors'])): ?>
        <div class="form-alert danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin:8px 0 0 18px; padding:0;">
                    <?php foreach ($GLOBALS['__ModelErrors'] as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($ViewBag->Error !== null): ?>
        <div class="form-alert danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= e($ViewBag->Error) ?></span>
        </div>
    <?php endif; ?>

    <!-- ======================
         FORM CARD
         ====================== -->
    <div class="form-card">

        <form method="post" action="/Admin/AddTuitionPost" id="addTuitionForm">
            <?= csrf_field() ?>

            <!-- ============================================================
                 SECTION: BASIC INFO
                 ============================================================ -->
            <div class="form-section-title">
                <i class="fas fa-info-circle"></i> Basic Information
            </div>

            <div class="mb-3">
                <label for="Title" class="form-label">
                    Post Title <span class="req">*</span>
                </label>
                <input type="text" name="Title" id="Title" value="<?= e($Model->Title) ?>" class="form-control"
                       placeholder="e.g. Madrasha Background Female Teacher Needed"
                       required maxlength="300" />
                <span class="info-note">
                    <i class="fas fa-info-circle"></i>
                    একটি সংক্ষিপ্ত কিন্তু descriptive title দিন
                </span>
                <span class="field-error"><?= field_error('Title') ?></span>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="TutorGenderPreference" class="form-label">
                        Tutor Gender Preference
                    </label>
                    <select name="TutorGenderPreference" id="TutorGenderPreference" class="form-select">
                        <option value="">— Select Preference —</option>
                        <option value="Female Teacher Needed" <?= ($Model->TutorGenderPreference ?? '') === 'Female Teacher Needed' ? 'selected' : '' ?>>Female Teacher Needed</option>
                        <option value="Male Teacher Needed" <?= ($Model->TutorGenderPreference ?? '') === 'Male Teacher Needed' ? 'selected' : '' ?>>Male Teacher Needed</option>
                        <option value="Any Gender" <?= ($Model->TutorGenderPreference ?? '') === 'Any Gender' ? 'selected' : '' ?>>Any Gender</option>
                    </select>
                    <span class="info-note">
                        <i class="fas fa-info-circle"></i>
                        কোন লিঙ্গের শিক্ষক চান?
                    </span>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="TuitionCode" class="form-label">
                        Tuition Code
                    </label>
                    <input type="text" name="TuitionCode" id="TuitionCode" value="<?= e($Model->TuitionCode) ?>" class="form-control"
                           placeholder="e.g. Oxygen8304" maxlength="50" />
                    <span class="info-note">
                        <i class="fas fa-info-circle"></i>
                        খালি রাখলে auto-generate হবে
                    </span>
                </div>
            </div>

            <!-- ============================================================
                 SECTION: STUDENT INFORMATION
                 ============================================================ -->
            <div class="form-section-title">
                <i class="fas fa-users"></i> Student Information
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="StudentGender" class="form-label">
                        Student Gender
                    </label>
                    <input type="text" name="StudentGender" id="StudentGender" value="<?= e($Model->StudentGender) ?>" class="form-control"
                           placeholder="e.g. Female, Male" maxlength="100" />
                    <span class="info-note">
                        <i class="fas fa-info-circle"></i>
                        একাধিক হলে কমা দিয়ে লিখুন
                    </span>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="StudentClass" class="form-label">
                        Student Class
                    </label>
                    <input type="text" name="StudentClass" id="StudentClass" value="<?= e($Model->StudentClass) ?>" class="form-control"
                           placeholder="e.g. 9 & 5 (2 Students)" maxlength="200" />
                    <span class="info-note">
                        <i class="fas fa-info-circle"></i>
                        Class + Students number
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <label for="Subjects" class="form-label">
                    Subjects
                </label>
                <input type="text" name="Subjects" id="Subjects" value="<?= e($Model->Subjects) ?>" class="form-control"
                       placeholder="e.g. All / Math, Physics, Chemistry" maxlength="300" />
                <span class="info-note">
                    <i class="fas fa-info-circle"></i>
                    All subject হলে "All" লিখুন, নাহলে comma দিয়ে আলাদা করুন
                </span>
            </div>

            <!-- ============================================================
                 SECTION: SCHEDULE & PAYMENT
                 ============================================================ -->
            <div class="form-section-title">
                <i class="fas fa-clock"></i> Schedule & Payment
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="DaysPerWeek" class="form-label">
                        Days Per Week
                    </label>
                    <input type="text" name="DaysPerWeek" id="DaysPerWeek" value="<?= e($Model->DaysPerWeek) ?>" class="form-control"
                           placeholder="e.g. 5" maxlength="50" />
                </div>
                <div class="col-md-4 mb-3">
                    <label for="PreferredTime" class="form-label">
                        Preferred Time
                    </label>
                    <input type="text" name="PreferredTime" id="PreferredTime" value="<?= e($Model->PreferredTime) ?>" class="form-control"
                           placeholder="e.g. Negotiable / Evening 5-8 PM" maxlength="200" />
                </div>
                <div class="col-md-4 mb-3">
                    <label for="Salary" class="form-label">
                        Monthly Salary (৳)
                    </label>
                    <input type="text" name="Salary" id="Salary" value="<?= e($Model->Salary) ?>" class="form-control"
                           placeholder="e.g. 4000" maxlength="100" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Area" class="form-label">
                        Area / Location
                    </label>
                    <input type="text" name="Area" id="Area" value="<?= e($Model->Area) ?>" class="form-control"
                           placeholder="e.g. অক্সিজেন, বটতল, মদিনা মসজিদ" maxlength="300" />
                    <span class="info-note">
                        <i class="fas fa-info-circle"></i>
                        একাধিক area হলে কমা দিয়ে লিখুন
                    </span>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="MediaFee" class="form-label">
                        Media Fee
                    </label>
                    <input type="text" name="MediaFee" id="MediaFee" value="<?= e($Model->MediaFee) ?>" class="form-control"
                           placeholder="e.g. 50% (After Join) / Free" maxlength="200" />
                    <span class="info-note">
                        <i class="fas fa-info-circle"></i>
                        Media/Platform fee কত?
                    </span>
                </div>
            </div>

            <!-- ============================================================
                 SECTION: SPECIAL NOTE
                 ============================================================ -->
            <div class="form-section-title">
                <i class="fas fa-sticky-note"></i> Special Note (Optional)
            </div>

            <div class="mb-3">
                <label for="SpecialNote" class="form-label">
                    Additional Details / বিঃদ্রঃ
                </label>
                <textarea name="SpecialNote" id="SpecialNote" class="form-control" rows="5"
                          placeholder="e.g. দু'জনেই মাদ্রাসার ছাত্র-ছাত্রী, সকল বিষয় পড়বে।"><?= e($Model->SpecialNote) ?></textarea>
                <span class="info-note">
                    <i class="fas fa-info-circle"></i>
                    Extra তথ্য যা Teachers কে জানানো দরকার
                </span>
            </div>

            <!-- ============================================================
                 SECTION: STATUS
                 ============================================================ -->
            <div class="form-section-title">
                <i class="fas fa-toggle-on"></i> Post Status
            </div>

            <div class="mb-3">
                <label for="Status" class="form-label">
                    Status <span class="req">*</span>
                </label>
                <select name="Status" id="Status" class="form-select" required>
                    <option value="Open" selected>🟢 Open (Visible to Teachers)</option>
                    <option value="Closed" <?= ($Model->Status ?? '') === 'Closed' ? 'selected' : '' ?>>🔴 Closed (Hidden)</option>
                </select>
                <span class="info-note">
                    <i class="fas fa-info-circle"></i>
                    Open থাকলে Circular পেজে দেখা যাবে
                </span>
            </div>

            <!-- ============================================================
                 LIVE PREVIEW
                 ============================================================ -->
            <div class="preview-box" id="livePreview" style="display:none;">
                <div class="preview-title">
                    <i class="fas fa-eye"></i> Live Preview
                </div>
                <div class="preview-grid">
                    <div class="preview-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span><strong>Class:</strong> <span id="pvClass">—</span></span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-book"></i>
                        <span><strong>Subjects:</strong> <span id="pvSubjects">—</span></span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-calendar-week"></i>
                        <span><strong>Days:</strong> <span id="pvDays">—</span></span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-clock"></i>
                        <span><strong>Time:</strong> <span id="pvTime">—</span></span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span><strong>Salary:</strong> ৳<span id="pvSalary">—</span></span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><strong>Area:</strong> <span id="pvArea">—</span></span>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 FORM ACTIONS
                 ============================================================ -->
            <div class="form-actions">
                <a href="/Admin/TuitionPosts" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn-save" id="submitBtn">
                    <i class="fas fa-save"></i> Publish Tuition Post
                </button>
            </div>

        </form>

    </div>

</div>

<!-- ============================================================ -->
<!-- SCRIPTS                                                       -->
<!-- ============================================================ -->
<?php section_start('Scripts'); ?>
    <?php partial('Shared/_ValidationScriptsPartial'); ?>

    <script>
        (function () {
            'use strict';

            // ===== Elements =====
            const form = document.getElementById('addTuitionForm');
            const submitBtn = document.getElementById('submitBtn');
            const previewBox = document.getElementById('livePreview');

            const fldClass = document.getElementById('StudentClass');
            const fldSubjects = document.getElementById('Subjects');
            const fldDays = document.getElementById('DaysPerWeek');
            const fldTime = document.getElementById('PreferredTime');
            const fldSalary = document.getElementById('Salary');
            const fldArea = document.getElementById('Area');

            // ===== Live Preview =====
            function updatePreview() {
                const cls = (fldClass?.value || '').trim();
                const sub = (fldSubjects?.value || '').trim();
                const days = (fldDays?.value || '').trim();
                const time = (fldTime?.value || '').trim();
                const sal = (fldSalary?.value || '').trim();
                const area = (fldArea?.value || '').trim();

                // Show preview if any field has value
                const hasAny = cls || sub || days || time || sal || area;
                if (previewBox) {
                    previewBox.style.display = hasAny ? 'block' : 'none';
                }

                // Update each field
                document.getElementById('pvClass').textContent = cls || '—';
                document.getElementById('pvSubjects').textContent = sub || '—';
                document.getElementById('pvDays').textContent = days ? days + ' days/week' : '—';
                document.getElementById('pvTime').textContent = time || '—';
                document.getElementById('pvSalary').textContent = sal || '—';
                document.getElementById('pvArea').textContent = area || '—';
            }

            // ===== Attach Live Preview Listeners =====
            [fldClass, fldSubjects, fldDays, fldTime, fldSalary, fldArea].forEach(el => {
                if (el) {
                    el.addEventListener('input', updatePreview);
                }
            });

            // Initial call
            updatePreview();

            // ===== Submit Button Loading State =====
            if (form && submitBtn) {
                form.addEventListener('submit', function (e) {
                    // Basic required validation for Title
                    const titleField = document.getElementById('Title');
                    if (titleField && !titleField.value.trim()) {
                        e.preventDefault();
                        alert('Please enter a Post Title.');
                        titleField.focus();
                        return;
                    }

                    // ✅ Ensure Status has a value (fallback to "Open")
                    const statusField = document.getElementById('Status');
                    if (statusField) {
                        const val = (statusField.value || '').trim();
                        if (val !== 'Open' && val !== 'Closed') {
                            statusField.value = 'Open';
                        }
                    }

                    // Disable button + show spinner
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
                });
            }

            // ===== Auto-generate Tuition Code Preview (Optional Helper) =====
            const codeField = document.getElementById('TuitionCode');
            const areaField = document.getElementById('Area');

            if (codeField && areaField) {
                areaField.addEventListener('blur', function () {
                    if (!codeField.value.trim() && this.value.trim()) {
                        // Just for user info — final generation happens in Controller
                        const areaPart = this.value
                            .split(',')[0]
                            .replace(/[^a-zA-Z]/g, '')
                            .substring(0, 8)
                            .toUpperCase() || 'TUITION';

                        const rand = Math.floor(1000 + Math.random() * 9000);
                        codeField.placeholder = 'Auto: ' + areaPart + rand;
                    }
                });
            }

            console.log('✅ Add Tuition Post form loaded');
        })();
    </script>
<?php section_end(); ?>
