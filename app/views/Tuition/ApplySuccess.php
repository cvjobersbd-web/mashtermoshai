<?php
$ViewData['Title'] = 'Application Submitted - Smart Tutor Hub';
?>

<style>
    /* ======================
               PAGE WRAPPER
               ====================== */
    .as-page {
        background: #F5FBFF;
        padding: 60px 20px 80px;
        min-height: 80vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .as-container {
        max-width: 640px;
        width: 100%;
    }

    /* ======================
               MAIN CARD
               ====================== */
    .as-card {
        background: white;
        border-radius: 22px;
        padding: 52px 44px 44px;
        text-align: center;
        box-shadow: 0 12px 44px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
    }

        /* Decorative top accent */
        .as-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2ecc71, #08224A, #FFB300);
        }

    /* ======================
               SUCCESS ICON
               ====================== */
    .as-icon {
        width: 110px;
        height: 110px;
        margin: 0 auto 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4edda, #b8e6c9);
        color: #2ecc71;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 52px;
        position: relative;
        animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 10px 30px rgba(46, 204, 113, 0.25);
    }

        /* Ripple ring */
        .as-icon::before {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            border: 2px solid rgba(46, 204, 113, 0.25);
            animation: ripple 2s ease-out infinite;
        }

    @keyframes popIn {
        0% {
            transform: scale(0.5);
            opacity: 0;
        }

        70% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes ripple {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        100% {
            transform: scale(1.3);
            opacity: 0;
        }
    }

    /* ======================
               HEADING
               ====================== */
    .as-heading {
        font-family: 'Georgia', serif;
        font-size: 28px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 12px;
        line-height: 1.3;
    }

    .as-message {
        color: #64748B;
        font-size: 15.5px;
        line-height: 1.7;
        margin: 0 auto 30px;
        max-width: 480px;
    }

    /* ======================
               DETAILS BOX
               ====================== */
    .as-details {
        background: #F5FBFF;
        border-radius: 14px;
        padding: 22px 24px;
        margin-bottom: 28px;
        text-align: left;
        border-left: 5px solid #FFB300;
    }

    .as-details-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        color: #7A8CA0;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

        .as-details-title i {
            color: #028EA5;
            font-size: 12px;
        }

    .as-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 11px 0;
        font-size: 14px;
        border-bottom: 1px solid #E6F1F8;
        gap: 16px;
    }

        .as-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .as-row:first-of-type {
            padding-top: 0;
        }

    .as-row-label {
        color: #64748B;
        flex-shrink: 0;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }

        .as-row-label i {
            color: #7A8CA0;
            font-size: 12px;
            width: 14px;
        }

    .as-row-value {
        color: #08224A;
        font-weight: 700;
        text-align: right;
        word-break: break-word;
        max-width: 65%;
    }

    /* Status Pill */
    .as-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background: #fff3cd;
        color: #856404;
    }

    /* ======================
               NEXT STEPS
               ====================== */
    .as-next-steps {
        background: #E8F4FD;
        border-radius: 14px;
        padding: 20px 22px;
        margin-bottom: 28px;
        text-align: left;
        border-left: 5px solid #3498db;
    }

    .as-next-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #0c6b7c;
        margin: 0 0 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

        .as-next-title i {
            font-size: 14px;
        }

    .as-next-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

        .as-next-list li {
            font-size: 13.5px;
            color: #0c5460;
            line-height: 1.6;
            display: flex;
            align-items: flex-start;
            gap: 9px;
        }

            .as-next-list li i {
                color: #17a2b8;
                font-size: 12px;
                margin-top: 4px;
                flex-shrink: 0;
            }

    /* ======================
               ACTION BUTTONS
               ====================== */
    .as-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .as-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 28px;
        border-radius: 11px;
        font-weight: 700;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.25s;
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .as-btn-primary {
        background: #02B8F5;
        color: white;
    }

        .as-btn-primary:hover {
            background: #0099D6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2,184,245,0.25);
        }

    .as-btn-outline {
        background: white;
        color: #08224A;
        border: 1.5px solid #02B8F5;
    }

        .as-btn-outline:hover {
            background: #F5FBFF;
            color: #08224A;
            transform: translateY(-2px);
        }

    /* ======================
               WHATSAPP CONTACT
               ====================== */
    .as-contact {
        margin-top: 30px;
        padding-top: 24px;
        border-top: 1px solid #f0f0f0;
    }

    .as-contact-text {
        font-size: 13px;
        color: #7A8CA0;
        margin-bottom: 12px;
    }

    .as-wa-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        background: #25D366;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        text-decoration: none;
        transition: all 0.25s;
    }

        .as-wa-btn:hover {
            background: #128C7E;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.3);
        }

        .as-wa-btn i {
            font-size: 16px;
        }

    /* ======================
               FOOTER NOTE
               ====================== */
    .as-footer-note {
        margin-top: 22px;
        font-size: 12.5px;
        color: #7A8CA0;
        line-height: 1.6;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

        .as-footer-note i {
            color: #2ecc71;
            font-size: 11px;
        }

    /* ======================
               RESPONSIVE
               ====================== */
    @media (max-width: 640px) {
        .as-page {
            padding: 30px 14px 60px;
        }

        .as-card {
            padding: 36px 22px 30px;
            border-radius: 18px;
        }

        .as-icon {
            width: 90px;
            height: 90px;
            font-size: 42px;
            margin-bottom: 20px;
        }

        .as-heading {
            font-size: 22px;
        }

        .as-message {
            font-size: 14px;
        }

        .as-details {
            padding: 18px 18px;
        }

        .as-row {
            flex-direction: column;
            gap: 4px;
            padding: 9px 0;
        }

        .as-row-value {
            text-align: left;
            max-width: 100%;
        }

        .as-actions {
            flex-direction: column;
        }

        .as-btn {
            width: 100%;
            justify-content: center;
        }

        .as-next-steps {
            padding: 16px 18px;
        }
    }
</style>

<div class="as-page">
    <div class="as-container">

        <div class="as-card">

            <!-- ======================
                 SUCCESS ICON
                 ====================== -->
            <div class="as-icon">
                <i class="fas fa-check"></i>
            </div>

            <!-- ======================
                 HEADING
                 ====================== -->
            <h1 class="as-heading">Application Submitted! 🎉</h1>
            <p class="as-message">
                আপনার Tuition application সফলভাবে জমা হয়েছে।<br />
                Admin যাচাই করে শীঘ্রই আপনার সাথে WhatsApp এ যোগাযোগ করবেন।
            </p>

            <!-- ======================
                 DETAILS BOX
                 ====================== -->
            <div class="as-details">

                <div class="as-details-title">
                    <i class="fas fa-file-alt"></i> Application Details
                </div>

                <div class="as-row">
                    <span class="as-row-label">
                        <i class="fas fa-user"></i> Applicant
                    </span>
                    <span class="as-row-value"><?= e($Model->ApplicantName) ?></span>
                </div>

                <div class="as-row">
                    <span class="as-row-label">
                        <i class="fas fa-phone"></i> Phone
                    </span>
                    <span class="as-row-value"><?= e($Model->PhoneNumber) ?></span>
                </div>

                <?php if (!is_blank($Model->WhatsAppNumber) && $Model->WhatsAppNumber != $Model->PhoneNumber): ?>
                    <div class="as-row">
                        <span class="as-row-label">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </span>
                        <span class="as-row-value"><?= e($Model->WhatsAppNumber) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($Model->TuitionPost != null): ?>
                    <div class="as-row">
                        <span class="as-row-label">
                            <i class="fas fa-clipboard-list"></i> Tuition
                        </span>
                        <span class="as-row-value"><?= e($Model->TuitionPost->Title) ?></span>
                    </div>

                    <?php if (!is_blank($Model->TuitionPost->TuitionCode)): ?>
                        <div class="as-row">
                            <span class="as-row-label">
                                <i class="fas fa-hashtag"></i> Code
                            </span>
                            <span class="as-row-value" style="font-family:'Courier New',monospace; letter-spacing:0.5px; color:#E09A00;">
                                <?= e($Model->TuitionPost->TuitionCode) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="as-row">
                    <span class="as-row-label">
                        <i class="fas fa-clock"></i> Applied At
                    </span>
                    <span class="as-row-value">
                        <?= e(fmt_date($Model->AppliedAt, 'd M Y, h:i A')) ?>
                    </span>
                </div>

                <div class="as-row">
                    <span class="as-row-label">
                        <i class="fas fa-info-circle"></i> Status
                    </span>
                    <span class="as-row-value">
                        <span class="as-status-pill">
                            <i class="fas fa-hourglass-half"></i> Pending
                        </span>
                    </span>
                </div>

            </div>

            <!-- ======================
                 NEXT STEPS
                 ====================== -->
            <div class="as-next-steps">
                <p class="as-next-title">
                    <i class="fas fa-list-check"></i> What Happens Next?
                </p>
                <ul class="as-next-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Admin আপনার CV এবং তথ্য যাচাই করবেন</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Shortlisted হলে WhatsApp এ যোগাযোগ করা হবে</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Selected হলে আপনি সরাসরি tuition শুরু করতে পারবেন</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Status update হলে আপনি Notification পাবেন</span>
                    </li>
                </ul>
            </div>

            <!-- ======================
                 ACTION BUTTONS
                 ====================== -->
            <div class="as-actions">
                <a href="/Tuition/Circular" class="as-btn as-btn-primary">
                    <i class="fas fa-list"></i> Browse More Tuitions
                </a>
                <a href="/" class="as-btn as-btn-outline">
                    <i class="fas fa-home"></i> Go Home
                </a>
            </div>

            <!-- ======================
                 CONTACT WHATSAPP
                 ====================== -->
            <?php if ($Model->TuitionPost != null): ?>
                <div class="as-contact">
                    <p class="as-contact-text">
                        <i class="fas fa-question-circle me-1"></i>
                        প্রশ্ন থাকলে সরাসরি Admin এর সাথে যোগাযোগ করুন
                    </p>
                    <a href="https://wa.me/8801000000000?text=Hello%20Admin,%20I%20applied%20for%20Tuition%20(<?= e($Model->TuitionPost?->TuitionCode) ?>)%20-%20<?= e($Model->ApplicantName) ?>"
                       target="_blank" class="as-wa-btn">
                        <i class="fab fa-whatsapp"></i> Contact Admin on WhatsApp
                    </a>
                </div>
            <?php endif; ?>

            <!-- ======================
                 FOOTER NOTE
                 ====================== -->
            <p class="as-footer-note">
                <i class="fas fa-shield-alt"></i>
                আপনার তথ্য সম্পূর্ণ সুরক্ষিত। শুধু Admin দেখতে পারবেন।
            </p>

        </div>

    </div>
</div>