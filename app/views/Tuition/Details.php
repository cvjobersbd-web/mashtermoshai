<?php
$ViewData['Title'] = $Model->Title . ' - Tuition Details';
$isLoggedIn = session_get('UserName') != null;
?>

<style>
    /* ======================
               PAGE WRAPPER
               ====================== */
    .td-page {
        background: #F5FBFF;
        padding: 40px 20px 70px;
        min-height: 80vh;
    }

    .td-container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* ======================
               BACK LINK
               ====================== */
    .td-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #64748B;
        font-size: 13.5px;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s;
        margin-bottom: 18px;
    }

        .td-back-link:hover {
            color: #08224A;
            background: #DCECF5;
        }

    /* ======================
               MAIN CARD
               ====================== */
    .td-card {
        background: white;
        border-radius: 20px;
        padding: 32px 34px 30px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
    }

        .td-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #08224A, #FFB300, #08224A);
        }

    /* ======================
               HEADER
               ====================== */
    .td-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
        padding-bottom: 22px;
        border-bottom: 2px solid #F5FBFF;
        flex-wrap: wrap;
    }

    .td-header-left {
        flex: 1;
        min-width: 0;
    }

    .td-code-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #08224A;
        color: #FFB300;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .td-title {
        font-family: var(--font-heading);
        font-size: 26px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 12px;
        line-height: 1.35;
    }

    .td-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 20px;
        font-size: 13.5px;
        color: #64748B;
    }

        .td-meta-row span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

            .td-meta-row span i {
                color: #028EA5;
                font-size: 13px;
            }

    /* Status Badge */
    .td-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        flex-shrink: 0;
    }

        .td-status-badge.open {
            background: #d4edda;
            color: #155724;
        }

        .td-status-badge.closed {
            background: #f8d7da;
            color: #721c24;
        }

    /* ======================
               TUTOR PREFERENCE
               ====================== */
    .td-pref-banner {
        background: linear-gradient(135deg, #FFF7E0 0%, #FFFFFF 100%);
        border-left: 5px solid #FFB300;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

        .td-pref-banner i {
            font-size: 22px;
            color: #E09A00;
            flex-shrink: 0;
        }

        .td-pref-banner strong {
            color: #08224A;
            font-size: 15px;
            font-weight: 700;
        }

        .td-pref-banner small {
            display: block;
            color: #7A8CA0;
            font-size: 12.5px;
            margin-top: 2px;
        }

    /* ======================
               INFO SECTION
               ====================== */
    .td-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 10px;
        border-bottom: 1px solid #F5FBFF;
    }

        .td-section-title i {
            color: #028EA5;
            font-size: 15px;
        }

    .td-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 20px;
        margin-bottom: 24px;
        align-items: start;
    }

    .td-info-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        color: #334155;
        line-height: 1.6;
    }

        .td-info-item i {
            color: #028EA5;
            font-size: 14px;
            width: 20px;
            text-align: center;
            margin-top: 4px;
            flex-shrink: 0;
        }

        .td-info-item strong {
            color: #08224A;
            font-weight: 700;
            margin-right: 5px;
        }

        .td-info-item.full {
            grid-column: 1 / -1;
        }

    /* Salary Highlight */
    .td-salary {
        background: #d4edda;
        color: #155724 !important;
        padding: 14px 18px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 10px;
    }

        .td-salary i {
            font-size: 18px;
            color: #155724;
        }

    /* Special Note */
    .td-note {
        background: #FFF7E0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        font-size: 14px;
        color: #475569;
        line-height: 1.7;
        border-left: 4px solid #FFB300;
        white-space: pre-line;
    }

        .td-note strong {
            color: #8a5a00;
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 800;
        }

    /* ======================
               APPLY CTA
               ====================== */
    .td-apply-section {
        background: #F5FBFF;
        border-radius: 14px;
        padding: 22px;
        text-align: center;
        border: 1.5px dashed #B9D6E6;
    }

    .td-apply-text {
        font-size: 14px;
        color: #475569;
        margin-bottom: 14px;
        line-height: 1.6;
    }

    .td-btn-apply {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 15px 40px;
        background: #02B8F5;
        color: white;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.25s;
        border: none;
        cursor: pointer;
        width: 100%;
        max-width: 340px;
    }

        .td-btn-apply:hover {
            background: #0099D6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(2,184,245,0.25);
        }

        .td-btn-apply.locked {
            background: #7A8CA0;
        }

            .td-btn-apply.locked:hover {
                background: #64748B;
                box-shadow: 0 8px 22px rgba(138,145,136,0.35);
            }

        .td-btn-apply.disabled {
            background: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }

    /* ======================
               RESPONSIVE
               ====================== */
    @media (max-width: 640px) {
        .td-page {
            padding: 24px 14px 60px;
        }

        .td-card {
            padding: 24px 20px;
        }

        .td-title {
            font-size: 20px;
        }

        .td-info-grid {
            grid-template-columns: 1fr;
        }

        .td-header {
            flex-direction: column;
        }

        .td-status-badge {
            align-self: flex-start;
        }

        .td-btn-apply {
            padding: 13px 24px;
            font-size: 14px;
        }
    }
</style>

<div class="td-page">
    <div class="td-container">

        <!-- Back Link -->
        <a href="/Tuition/Circular" class="td-back-link">
            <i class="fas fa-arrow-left"></i> Back to Tuition Circular
        </a>

        <!-- Main Card -->
        <div class="td-card">

            <!-- ======================
                 HEADER
                 ====================== -->
            <div class="td-header">
                <div class="td-header-left">
                    <?php if (!is_blank($Model->TuitionCode)): ?>
                        <div class="td-code-badge">
                            <i class="fas fa-hashtag"></i> <?= e($Model->TuitionCode) ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="td-title"><?= e($Model->Title) ?></h1>

                    <div class="td-meta-row">
                        <?php if (!is_blank($Model->Area)): ?>
                            <span><i class="fas fa-map-marker-alt"></i><?= e($Model->Area) ?></span>
                        <?php endif; ?>
                        <span><i class="fas fa-calendar"></i>Posted: <?= e(fmt_date($Model->PostedDate, 'd M Y')) ?></span>
                        <?php if ($Model->Applications !== null): ?>
                            <span><i class="fas fa-user-check"></i><?= e(count($Model->Applications ?? [])) ?> Applications</span>
                        <?php endif; ?>
                    </div>
                </div>

                <span class="td-status-badge <?= e($Model->Status == 'Open' ? 'open' : 'closed') ?>">
                    <?php if ($Model->Status == 'Open'): ?>
                        <i class="fas fa-circle" style="font-size: 7px;"></i>
                        Open
                    <?php else: ?>
                        <i class="fas fa-lock"></i>
                        Closed
                    <?php endif; ?>
                </span>
            </div>

            <!-- ======================
                 TUTOR PREFERENCE
                 ====================== -->
            <?php if (!is_blank($Model->TutorGenderPreference)): ?>
                <div class="td-pref-banner">
                    <i class="fas fa-user-tie"></i>
                    <div>
                        <strong><?= e($Model->TutorGenderPreference) ?></strong>
                        <small>Student's tutor preference</small>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ======================
                 STUDENT INFORMATION
                 ====================== -->
            <h3 class="td-section-title">
                <i class="fas fa-users"></i> Student Information
            </h3>

            <div class="td-info-grid">
                <?php if (!is_blank($Model->StudentGender)): ?>
                    <div class="td-info-item">
                        <i class="fas fa-venus-mars"></i>
                        <span><strong>Gender:</strong> <?= e($Model->StudentGender) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!is_blank($Model->StudentClass)): ?>
                    <div class="td-info-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span><strong>Class:</strong> <?= e($Model->StudentClass) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!is_blank($Model->Subjects)): ?>
                    <div class="td-info-item full">
                        <i class="fas fa-book"></i>
                        <span><strong>Subjects:</strong> <?= e($Model->Subjects) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ======================
                 SCHEDULE & PAYMENT
                 ====================== -->
            <h3 class="td-section-title">
                <i class="fas fa-clock"></i> Schedule & Payment
            </h3>

            <div class="td-info-grid">
                <?php if (!is_blank($Model->DaysPerWeek)): ?>
                    <div class="td-info-item">
                        <i class="fas fa-calendar-week"></i>
                        <span><strong>Days/Week:</strong> <?= e($Model->DaysPerWeek) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!is_blank($Model->PreferredTime)): ?>
                    <div class="td-info-item">
                        <i class="fas fa-clock"></i>
                        <span><strong>Time:</strong> <?= e($Model->PreferredTime) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!is_blank($Model->MediaFee)): ?>
                    <div class="td-info-item full">
                        <i class="fas fa-percentage"></i>
                        <span><strong>Media Fee:</strong> <?= e($Model->MediaFee) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!is_blank($Model->Salary)): ?>
                    <div class="td-salary">
                        <i class="fas fa-money-bill-wave"></i>
                        <span><strong>Monthly Salary:</strong> ৳ <?= e($Model->Salary) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ======================
                 SPECIAL NOTE
                 ====================== -->
            <?php if (!is_blank($Model->SpecialNote)): ?>
                <div class="td-note">
                    <strong>বিঃদ্রঃ / Special Note</strong>
                    <?= e($Model->SpecialNote) ?>
                </div>
            <?php endif; ?>

            <!-- ======================
                 APPLY BUTTON
                 ====================== -->
            <div class="td-apply-section">
                <?php if ($Model->Status != 'Open'): ?>
                    <p class="td-apply-text">
                        <i class="fas fa-lock me-1"></i>
                        This tuition is currently <strong>closed</strong>. You cannot apply.
                    </p>
                    <span class="td-btn-apply disabled">
                        <i class="fas fa-ban"></i> Application Closed
                    </span>
                <?php elseif ($isLoggedIn): ?>
                    <p class="td-apply-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Interested in this tuition? Submit your application now.
                    </p>
                    <a href="/Tuition/Apply/<?= e($Model->Id) ?>" class="td-btn-apply">
                        <i class="fas fa-paper-plane"></i> Apply for This Tuition
                    </a>
                <?php else: ?>
                    <p class="td-apply-text">
                        <i class="fas fa-lock me-1"></i>
                        <strong>Login required</strong> to apply for this tuition.
                    </p>
                    <a href="/Account/Login?returnUrl=/Tuition/Apply/<?= e($Model->Id) ?>" class="td-btn-apply locked">
                        <i class="fas fa-sign-in-alt"></i> Login to Apply
                    </a>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>