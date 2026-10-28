<?php
$ViewData['Title'] = 'Edit Tuition Post - Admin Panel';
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
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 26px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

            .page-header h2 i {
                color: #E09A00;
            }

        .page-header p {
            color: #64748B;
            margin: 6px 0 0 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

            .page-header p .edit-code {
                background: #08224A;
                color: #FFB300;
                padding: 2px 10px;
                border-radius: 12px;
                font-size: 11.5px;
                font-weight: 800;
                font-family: 'Courier New', monospace;
                letter-spacing: 0.4px;
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
        position: relative;
        overflow: hidden;
    }

        /* Top accent bar - different color for edit */
        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #8e63d9, #FFB300, #8e63d9);
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
            border-color: #8e63d9;
            box-shadow: 0 0 0 3px rgba(142,99,217,0.1);
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
            background: #E7DCF5;
            color: #5a3fa0;
            border-left: 4px solid #8e63d9;
        }

        .form-alert i {
            font-size: 16px;
            margin-top: 1px;
            flex-shrink: 0;
        }

    /* ======================
           META INFO CARD
           ====================== */
    .meta-info {
        background: #F5FBFF;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 22px;
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        font-size: 12.5px;
        color: #475569;
    }

        .meta-info .meta-item {
            display: flex;
            align-items: center;
            gap: 7px;
        }

            .meta-info .meta-item i {
                color: #028EA5;
                font-size: 13px;
            }

            .meta-info .meta-item strong {
                color: #08224A;
                font-weight: 700;
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

    .btn-update {
        background: #8e63d9;
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

        .btn-update:hover {
            background: #6f42c1;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(142,99,217,0.3);
        }

        .btn-update:disabled {
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

    .btn-danger-outline {
        background: white;
        color: #e74c3c;
        padding: 13px 24px;
        border-radius: 11px;
        border: 1.5px solid #e74c3c;
        font-weight: 600;
        font-size: 14.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.22s;
        margin-left: auto;
    }

        .btn-danger-outline:hover {
            background: #f8d7da;
            color: #e74c3c;
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

        .btn-update,
        .btn-cancel,
        .btn-danger-outline {
            width: 100%;
            justify-content: center;
            margin-left: 0;
        }

        .meta-info {
            flex-direction: column;
            gap: 10px;
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
                <i class="fas fa-edit"></i> Edit Tuition Post
            </h2>
            <p>
                Post ID: #<?= e($Model->Id) ?>
                <?php if (!is_blank($Model->TuitionCode)): ?>
                    <span class="edit-code">
                        <i class="fas fa-hashtag" style="font-size:9px;"></i><?= e($Model->TuitionCode) ?>
                    </span>
                <?php endif; ?>
            </p>
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

        <!-- ======================
             META INFO
             ====================== -->
        <div class="meta-info">
            <div class="meta-item">
                <i class="fas fa-calendar-plus"></i>
                <span>Posted: <strong><?= e(fmt_date($Model->PostedDate, 'd M Y, h:i A')) ?></strong></span>
            </div>
            <?php if (to_dt($Model->UpdatedAt) !== null): ?>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Last Updated: <strong><?= e(fmt_date($Model->UpdatedAt, 'd M Y, h:i A')) ?></strong></span>
                </div>
            <?php endif; ?>
            <div class="meta-item">
                <i class="fas fa-users"></i>
                <span>Applications: <strong><?= e($Model->Applications !== null ? count($Model->Applications) : 0) ?></strong></span>
            </div>
        </div>

        <form method="post" action="/Admin/EditTuitionPost" id="editTuitionForm">
            <?= csrf_field() ?>

            <!-- Hidden Fields -->
            <input type="hidden" name="Id" id="Id" value="<?= e($Model->Id) ?>" />
            <input type="hidden" name="PostedDate" id="PostedDate" value="<?= e($Model->PostedDate) ?>" />

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
                        Teachers এই Code দিয়েই খুঁজে পাবে
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
                </div>
                <div class="col-md-6 mb-3">
                    <label for="StudentClass" class="form-label">
                        Student Class
                    </label>
                    <input type="text" name="StudentClass" id="StudentClass" value="<?= e($Model->StudentClass) ?>" class="form-control"
                           placeholder="e.g. 9 & 5 (2 Students)" maxlength="200" />
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
                </div>
                <div class="col-md-6 mb-3">
                    <label for="MediaFee" class="form-label">
                        Media Fee
                    </label>
                    <input type="text" name="MediaFee" id="MediaFee" value="<?= e($Model->MediaFee) ?>" class="form-control"
                           placeholder="e.g. 50% (After Join) / Free" maxlength="200" />
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
            </div>

            <!-- ============================================================
                 SECTION: STATUS
                 ============================================================ -->
            <div class="form-section-title">
                <i class="fas fa-toggle-on"></i> Post Status
            </div>

            <div class="mb-3">
                <label for="Status" class="form-label">
                    Status
                </label>
                <select name="Status" id="Status" class="form-select">
                    <option value="Open" <?= ($Model->Status ?? '') === 'Open' ? 'selected' : '' ?>>🟢 Open (Visible to Teachers)</option>
                    <option value="Closed" <?= ($Model->Status ?? '') === 'Closed' ? 'selected' : '' ?>>🔴 Closed (Hidden)</option>
                </select>
                <span class="info-note">
                    <i class="fas fa-info-circle"></i>
                    Closed করলে Circular পেজে আর দেখা যাবে না
                </span>
            </div>

            <!-- ============================================================
                 FORM ACTIONS
                 ============================================================ -->
            <div class="form-actions">
                <a href="/Admin/TuitionPosts" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn-update" id="submitBtn">
                    <i class="fas fa-save"></i> Update Post
                </button>
                <button type="button" class="btn-danger-outline"
                        onclick="deletePostFromEdit(<?= (int)$Model->Id ?>, <?= e(js((string)$Model->Title)) ?>)">
                    <i class="fas fa-trash"></i> Delete Post
                </button>
            </div>

        </form>

    </div>

</div>

<!-- ============================================================ -->
<!-- HIDDEN TOKEN FOR AJAX DELETE                                  -->
<!-- ============================================================ -->
<?= csrf_field() ?>

<!-- ============================================================ -->
<!-- SCRIPTS                                                       -->
<!-- ============================================================ -->
<?php section_start('Scripts'); ?>
    <?php partial('Shared/_ValidationScriptsPartial'); ?>

    <script>
        (function () {
            'use strict';

            const form = document.getElementById('editTuitionForm');
            const submitBtn = document.getElementById('submitBtn');

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

                    // Disable button + show spinner
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                });
            }

            console.log('✅ Edit Tuition Post form loaded for ID: <?= (int)$Model->Id ?>');
        })();

        // ===== Delete from Edit Page =====
        function deletePostFromEdit(id, title) {
            const msg = '⚠️ Delete "' + title + '"?\n\n' +
                'This will also delete ALL its applications permanently.\n' +
                'This action CANNOT be undone.\n\n' +
                'Are you sure?';

            if (!confirm(msg)) return;

            // Get token from any available AntiForgeryToken input
            const tokenInput = document.querySelector('input[name="__RequestVerificationToken"]');
            const token = tokenInput ? tokenInput.value : '';

            const formData = new FormData();
            formData.append('id', id);
            formData.append('__RequestVerificationToken', token);

            fetch('/Admin/DeleteTuitionPost', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        window.location.href = '/Admin/TuitionPosts';
                    }
                })
                .catch(err => {
                    alert('Error deleting post: ' + err.message);
                });
        }
    </script>
<?php section_end(); ?>
