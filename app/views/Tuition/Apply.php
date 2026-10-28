<?php
$ViewData['Title'] = 'Apply for Tuition - Smart Tutor Hub';
$post = $ViewBag->Post;
?>

<style>
    /* ======================
               PAGE WRAPPER
               ====================== */
    .ap-page {
        background: #F5FBFF;
        padding: 40px 20px 70px;
        min-height: 80vh;
    }

    .ap-container {
        max-width: 850px;
        margin: 0 auto;
    }

    /* ======================
               PAGE HEADER
               ====================== */
    .ap-page-header {
        text-align: center;
        margin-bottom: 26px;
    }

        .ap-page-header h1 {
            font-family: 'Georgia', serif;
            font-size: 32px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 8px;
        }

        .ap-page-header p {
            color: #475569;
            font-size: 15px;
            margin: 0;
        }

    /* ======================
               POST PREVIEW CARD
               ====================== */
    .ap-post-card {
        background: linear-gradient(135deg, #FFF7E0 0%, #FFFFFF 100%);
        border-radius: 18px;
        padding: 24px 28px;
        margin-bottom: 22px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border-left: 6px solid #FFB300;
        position: relative;
    }

    .ap-post-title-wrap {
        position: relative;
        padding-right: 110px;
        margin-bottom: 14px;
    }

    .ap-post-card h2 {
        font-family: 'Georgia', serif;
        font-size: 21px;
        font-weight: 700;
        color: #08224A;
        margin: 0;
        line-height: 1.4;
    }

    .ap-code-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #08224A;
        color: #FFB300;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .ap-post-meta {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        font-size: 13.5px;
        color: #475569;
    }

        .ap-post-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

            .ap-post-meta span i {
                color: #028EA5;
                font-size: 13px;
            }

    /* ======================
               FORM CARD
               ====================== */
    .ap-form-card {
        background: white;
        border-radius: 18px;
        padding: 32px 30px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .ap-form-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 18px;
        border-bottom: 2px solid #F5FBFF;
        margin-bottom: 24px;
    }

        .ap-form-header i {
            font-size: 20px;
            color: #028EA5;
        }

        .ap-form-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #08224A;
            margin: 0;
            font-family: 'Georgia', serif;
        }

    /* ======================
               FORM FIELDS
               ====================== */
    .ap-field {
        margin-bottom: 20px;
    }

        .ap-field label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #08224A;
            margin-bottom: 8px;
            letter-spacing: 0.2px;
        }

            .ap-field label .req {
                color: #e74c3c;
                margin-left: 2px;
            }

        .ap-field input,
        .ap-field textarea {
            width: 100%;
            border-radius: 10px;
            border: 1.5px solid #DCECF5;
            padding: 13px 16px;
            font-size: 14px;
            background: #F5FBFF;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
            color: #08224A;
        }

            .ap-field input:focus,
            .ap-field textarea:focus {
                border-color: #02B8F5;
                background: white;
                box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
            }

            .ap-field input::placeholder,
            .ap-field textarea::placeholder {
                color: #A0B3C6;
            }

        .ap-field textarea {
            resize: vertical;
            min-height: 100px;
            line-height: 1.6;
        }

        .ap-field .hint {
            font-size: 12px;
            color: #7A8CA0;
            margin-top: 6px;
            display: block;
            line-height: 1.5;
        }

            .ap-field .hint i {
                color: #028EA5;
                margin-right: 3px;
            }

        .ap-field .err {
            font-size: 12.5px;
            color: #e74c3c;
            margin-top: 5px;
            display: block;
            font-weight: 500;
        }

    /* ======================
               WHATSAPP INPUT
               ====================== */
    .ap-wa-input {
        position: relative;
    }

        .ap-wa-input .wa-icon {
            position: absolute;
            left: 15px;
            top: 42px;
            color: #25D366;
            font-size: 18px;
            pointer-events: none;
            z-index: 2;
        }

        .ap-wa-input input {
            padding-left: 46px !important;
        }

    /* ======================
               FORM ROW (Two columns)
               ====================== */
    .ap-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* ======================
               SUBMIT BUTTON
               ====================== */
    .ap-btn-submit {
        width: 100%;
        padding: 15px;
        background: #02B8F5;
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 8px;
    }

        .ap-btn-submit:hover {
            background: #0099D6;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2,184,245,0.25);
        }

        .ap-btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

    /* ======================
               BACK LINK
               ====================== */
    .ap-back-wrap {
        text-align: center;
        margin-top: 20px;
    }

    .ap-back-link {
        color: #64748B;
        font-size: 13.5px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s;
    }

        .ap-back-link:hover {
            color: #08224A;
            background: #F5FBFF;
        }

    /* ======================
               ALERT
               ====================== */
    .ap-alert {
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 20px;
        font-size: 13.5px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

        .ap-alert.danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }

        .ap-alert.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

    /* ======================
               RESPONSIVE
               ====================== */
    @media (max-width: 640px) {
        .ap-page {
            padding: 26px 14px 60px;
        }

        .ap-page-header h1 {
            font-size: 24px;
        }

        .ap-page-header p {
            font-size: 13.5px;
        }

        .ap-post-card {
            padding: 20px 18px;
        }

        .ap-post-title-wrap {
            padding-right: 0;
            padding-top: 34px;
        }

        .ap-post-card h2 {
            font-size: 17px;
        }

        .ap-code-badge {
            top: 0;
            left: 0;
            right: auto;
        }

        .ap-form-card {
            padding: 22px 18px;
        }

        .ap-form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .ap-field input,
        .ap-field textarea {
            font-size: 15px; /* Prevents zoom on iOS */
        }
    }
</style>

<div class="ap-page">
    <div class="ap-container">

        <!-- ======================
             PAGE HEADER
             ====================== -->
        <div class="ap-page-header">
            <h1>📝 Apply for Tuition</h1>
            <p>নিচের form পূরণ করে আপনার application জমা দিন</p>
        </div>

        <!-- ======================
             POST PREVIEW
             ====================== -->
        <?php if ($post != null): ?>
            <div class="ap-post-card">
                <div class="ap-post-title-wrap">
                    <h2><?= e($post->Title) ?></h2>
                    <?php if (!is_blank($post->TuitionCode)): ?>
                        <span class="ap-code-badge"><?= e($post->TuitionCode) ?></span>
                    <?php endif; ?>
                </div>

                <div class="ap-post-meta">
                    <?php if (!is_blank($post->StudentClass)): ?>
                        <span><i class="fas fa-graduation-cap"></i> <?= e($post->StudentClass) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($post->Subjects)): ?>
                        <span><i class="fas fa-book"></i> <?= e($post->Subjects) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($post->Salary)): ?>
                        <span><i class="fas fa-money-bill-wave"></i> ৳<?= e($post->Salary) ?> /month</span>
                    <?php endif; ?>
                    <?php if (!is_blank($post->Area)): ?>
                        <span><i class="fas fa-map-marker-alt"></i> <?= e($post->Area) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($post->PreferredTime)): ?>
                        <span><i class="fas fa-clock"></i> <?= e($post->PreferredTime) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ======================
             FORM CARD
             ====================== -->
        <div class="ap-form-card">

            <div class="ap-form-header">
                <i class="fas fa-user-edit"></i>
                <h3>Your Information</h3>
            </div>

            <!-- Alerts -->
            <?php if ($TempData['Error'] != null): ?>
                <div class="ap-alert danger">
                    <i class="fas fa-exclamation-circle" style="font-size:16px; margin-top:1px;"></i>
                    <span><?= e($TempData['Error']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($GLOBALS['__ModelErrors'])): ?>
                <div class="ap-alert danger">
                    <i class="fas fa-exclamation-circle" style="font-size:16px; margin-top:1px;"></i>
                    <span>Please fix the highlighted errors below before submitting.</span>
                </div>
            <?php endif; ?>

            <?php if (session_get('UserName') != null): ?>
                <div class="ap-alert info">
                    <i class="fas fa-info-circle" style="font-size:16px; margin-top:1px;"></i>
                    <span>আপনার information auto-fill করা হয়েছে। প্রয়োজনে পরিবর্তন করুন।</span>
                </div>
            <?php endif; ?>

            <form method="post" action="/Tuition/Apply" id="applyForm">
                <?= csrf_field() ?>

                <!-- Hidden Fields -->
                <input type="hidden" name="TuitionPostId" id="TuitionPostId" value="<?= e($Model->TuitionPostId) ?>" />
                <input type="hidden" name="UserId" id="UserId" value="<?= e($Model->UserId) ?>" />

                <!-- ======================
                     NAME + PHONE
                     ====================== -->
                <div class="ap-form-row">
                    <div class="ap-field">
                        <label>Full Name <span class="req">*</span></label>
                        <input name="ApplicantName" id="ApplicantName" value="<?= e($Model->ApplicantName) ?>" type="text"
                               placeholder="আপনার সম্পূর্ণ নাম" autocomplete="name" />
                        <span class="err"><?= field_error('ApplicantName') ?></span>
                    </div>

                    <div class="ap-field">
                        <label>Phone Number <span class="req">*</span></label>
                        <input name="PhoneNumber" id="PhoneNumber" value="<?= e($Model->PhoneNumber) ?>" type="tel"
                               placeholder="01XXXXXXXXX" maxlength="11"
                               inputmode="numeric" pattern="[0-9]*"
                               autocomplete="tel" />
                        <span class="hint">
                            <i class="fas fa-info-circle"></i>
                            বাংলাদেশী format (e.g., 01712345678)
                        </span>
                        <span class="err"><?= field_error('PhoneNumber') ?></span>
                    </div>
                </div>

                <!-- ======================
                     WHATSAPP
                     ====================== -->
                <div class="ap-field ap-wa-input">
                    <label>WhatsApp Number</label>
                    <i class="fab fa-whatsapp wa-icon"></i>
                    <input name="WhatsAppNumber" id="WhatsAppNumber" value="<?= e($Model->WhatsAppNumber) ?>" type="tel"
                           placeholder="01XXXXXXXXX (Leave empty if same as phone)"
                           maxlength="11" inputmode="numeric" pattern="[0-9]*" />
                    <span class="hint">
                        <i class="fas fa-info-circle"></i>
                        Phone number এর সাথে একই হলে খালি রাখুন
                    </span>
                    <span class="err"><?= field_error('WhatsAppNumber') ?></span>
                </div>

                <!-- ======================
                     EMAIL
                     ====================== -->
                <div class="ap-field">
                    <label>Email Address</label>
                    <input name="Email" id="Email" value="<?= e($Model->Email) ?>" type="email"
                           placeholder="yourname@gmail.com" autocomplete="email" />
                    <span class="hint">
                        <i class="fas fa-info-circle"></i>
                        Optional — Admin যোগাযোগের জন্য ব্যবহার করবে
                    </span>
                    <span class="err"><?= field_error('Email') ?></span>
                </div>

                <!-- ======================
                     CV LINK
                     ====================== -->
                <div class="ap-field">
                    <label>CV Link <span class="req">*</span></label>
                    <input name="CvLink" id="CvLink" value="<?= e($Model->CvLink) ?>" type="url"
                           placeholder="https://drive.google.com/your-cv" />
                    <span class="hint">
                        <i class="fas fa-info-circle"></i>
                        Google Drive / Dropbox এ CV upload করে <strong>public</strong> link দিন
                    </span>
                    <span class="err"><?= field_error('CvLink') ?></span>
                </div>

                <!-- ======================
                     COVER NOTE
                     ====================== -->
                <div class="ap-field">
                    <label>Cover Note <span style="color:#7A8CA0; font-weight:400;">(Optional)</span></label>
                    <textarea name="CoverNote" id="CoverNote" rows="4"
                              placeholder="আপনার অভিজ্ঞতা, qualification, বা অন্য কোনো তথ্য সংক্ষেপে লিখুন..."><?= e($Model->CoverNote) ?></textarea>
                    <span class="hint">
                        <i class="fas fa-info-circle"></i>
                        Admin কে জানানোর জন্য কিছু বলার থাকলে এখানে লিখুন
                    </span>
                </div>

                <!-- ======================
                     SUBMIT BUTTON
                     ====================== -->
                <button type="submit" class="ap-btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </form>

            <!-- ======================
                 BACK LINK
                 ====================== -->
            <div class="ap-back-wrap">
                <a href="/Tuition/Circular" class="ap-back-link">
                    <i class="fas fa-arrow-left"></i> Back to Tuition Circular
                </a>
            </div>
        </div>

    </div>
</div>

<?php section_start('Scripts'); ?>
    <?php partial('Shared/_ValidationScriptsPartial'); ?>

    <script>
        (function () {
            'use strict';

            const phoneInput = document.getElementById('PhoneNumber');
            const waInput = document.getElementById('WhatsAppNumber');
            const form = document.getElementById('applyForm');
            const submitBtn = document.getElementById('submitBtn');

            // ============================================================
            // ===== 1. STRIP non-digit characters from phone inputs =====
            // ============================================================
            function cleanDigits(value) {
                return (value || '').replace(/\D/g, '');
            }

            function attachPhoneCleaner(input) {
                if (!input) return;

                // Clean on typing
                input.addEventListener('input', function () {
                    const cleaned = cleanDigits(this.value);
                    if (this.value !== cleaned) {
                        // Preserve cursor position roughly
                        const pos = this.selectionStart;
                        this.value = cleaned;
                        const newPos = Math.max(0, pos - 1);
                        try { this.setSelectionRange(newPos, newPos); } catch (e) { }
                    }
                });

                // Clean on paste
                input.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    const cleaned = cleanDigits(pasted);
                    const start = this.selectionStart || 0;
                    const end = this.selectionEnd || 0;
                    const current = this.value || '';
                    const newValue = current.slice(0, start) + cleaned + current.slice(end);
                    this.value = cleanDigits(newValue).slice(0, 11);
                });

                // Clean on blur — final safety net
                input.addEventListener('blur', function () {
                    this.value = cleanDigits(this.value).slice(0, 11);
                });
            }

            attachPhoneCleaner(phoneInput);
            attachPhoneCleaner(waInput);

            // ============================================================
            // ===== 2. Auto-fill WhatsApp from Phone (if empty) =====
            // ============================================================
            if (phoneInput && waInput) {
                phoneInput.addEventListener('input', function () {
                    if (!waInput.value.trim()) {
                        waInput.value = this.value;
                    }
                });
            }

            // ============================================================
            // ===== 3. Client-side validation before submit =====
            // ============================================================
            function isValidBdPhone(num) {
                return /^01[3-9]\d{8}$/.test(String(num || '').trim());
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function (e) {
                    // ✅ Final cleanup — trim + digits only
                    if (phoneInput) {
                        phoneInput.value = cleanDigits(phoneInput.value).slice(0, 11);
                    }
                    if (waInput) {
                        waInput.value = cleanDigits(waInput.value).slice(0, 11);
                    }

                    // If WhatsApp is empty → copy from phone
                    if (waInput && !waInput.value && phoneInput) {
                        waInput.value = phoneInput.value;
                    }

                    // ✅ Client-side phone validation
                    const phoneVal = phoneInput ? phoneInput.value : '';
                    const waVal = waInput ? waInput.value : '';

                    if (!isValidBdPhone(phoneVal)) {
                        e.preventDefault();
                        e.stopPropagation();
                        alert('⚠️ Please enter a valid Bangladeshi phone number (e.g., 01712345678).\n\nYou entered: ' + phoneVal);
                        phoneInput && phoneInput.focus();
                        return false;
                    }

                    if (!isValidBdPhone(waVal)) {
                        e.preventDefault();
                        e.stopPropagation();
                        alert('⚠️ Please enter a valid WhatsApp number (e.g., 01712345678).\n\nYou entered: ' + waVal);
                        waInput && waInput.focus();
                        return false;
                    }

                    // Disable button to prevent double submit
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                });
            }

            console.log('✅ Apply form loaded with phone validation');
        })();
    </script>
<?php section_end(); ?>
