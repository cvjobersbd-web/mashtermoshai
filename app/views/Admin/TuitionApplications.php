<?php
$ViewData['Title'] = 'Tuition Applications - Admin Panel';
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
           STATS GRID
           ====================== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-box {
        background: white;
        border-radius: 14px;
        padding: 22px 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        border-left: 5px solid #02B8F5;
        transition: transform 0.25s;
    }

        .stat-box:hover {
            transform: translateY(-3px);
        }

        .stat-box.pending {
            border-left-color: #f39c12;
        }

        .stat-box.shortlisted {
            border-left-color: #3498db;
        }

        .stat-box.selected {
            border-left-color: #2ecc71;
        }

        .stat-box .num {
            font-size: 30px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.1;
            font-family: 'Georgia', serif;
        }

        .stat-box .lbl {
            font-size: 13px;
            color: #64748B;
            margin-top: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

            .stat-box .lbl i {
                color: #028EA5;
                font-size: 12px;
            }

    /* ======================
           FILTER TABS
           ====================== */
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 9px 20px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.25s;
        border: 1.5px solid #DCECF5;
        color: #475569;
        background: white;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

        .filter-tab:hover {
            border-color: #02B8F5;
            color: #08224A;
        }

        .filter-tab.active {
            background: #02B8F5;
            color: white;
            border-color: #02B8F5;
        }

        .filter-tab .badge-count {
            background: rgba(0,0,0,0.08);
            padding: 1px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .filter-tab.active .badge-count {
            background: rgba(255,255,255,0.2);
        }

    /* ======================
           APPLICATION CARD
           ====================== */
    .app-card {
        background: white;
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        border-left: 5px solid #f39c12;
        transition: all 0.25s;
    }

        .app-card:hover {
            box-shadow: 0 8px 26px rgba(2,184,245,0.1);
            transform: translateY(-2px);
        }

        .app-card.status-Shortlisted {
            border-left-color: #3498db;
        }

        .app-card.status-Selected {
            border-left-color: #2ecc71;
        }

        .app-card.status-Rejected {
            border-left-color: #e74c3c;
            opacity: 0.75;
        }

    /* ======================
           APP HEADER
           ====================== */
    .app-header {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .app-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: linear-gradient(135deg, #08224A, #134856);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 18px;
        flex-shrink: 0;
        letter-spacing: 0.5px;
    }

    .app-headinfo {
        flex: 1;
        min-width: 220px;
    }

    .app-name-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }

    .app-name {
        font-size: 16.5px;
        font-weight: 700;
        color: #08224A;
        margin: 0;
        line-height: 1.3;
    }

    .app-contact {
        font-size: 12.5px;
        color: #64748B;
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

        .app-contact span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

            .app-contact span i {
                color: #028EA5;
                font-size: 12px;
            }

    /* ======================
           STATUS PILL
           ====================== */
    .status-pill {
        padding: 5px 14px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }

        .status-pill.Pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-pill.Shortlisted {
            background: #cfe2ff;
            color: #084298;
        }

        .status-pill.Selected {
            background: #d4edda;
            color: #155724;
        }

        .status-pill.Rejected {
            background: #f8d7da;
            color: #721c24;
        }

    /* ======================
           TUITION REFERENCE BOX
           ====================== */
    .app-tuition-ref {
        background: #F5FBFF;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 10px;
        border-left: 4px solid #FFB300;
        font-size: 13px;
        color: #334155;
        line-height: 1.6;
    }

        .app-tuition-ref strong {
            color: #08224A;
            font-weight: 700;
        }

        .app-tuition-ref .ref-code {
            display: inline-block;
            background: #08224A;
            color: #FFB300;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            margin-left: 6px;
            letter-spacing: 0.4px;
        }

        .app-tuition-ref .ref-meta {
            display: block;
            font-size: 12px;
            color: #7A8CA0;
            margin-top: 4px;
        }

    /* ======================
           COVER NOTE
           ====================== */
    .app-note {
        background: #CFF2FC;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 10px;
        font-size: 13px;
        color: #08224A;
        border-left: 4px solid #2ecc71;
        line-height: 1.6;
    }

        .app-note strong {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #155724;
            margin-bottom: 4px;
            font-weight: 800;
        }

    /* ======================
           ADMIN NOTE
           ====================== */
    .app-admin-note {
        background: #FFF7E0;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 10px;
        font-size: 13px;
        color: #475569;
        border-left: 4px solid #FFB300;
        line-height: 1.6;
    }

        .app-admin-note strong {
            color: #8a5a00;
            font-weight: 700;
        }

    /* ======================
           APP ACTIONS
           ====================== */
    .app-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #f0f0f0;
    }

    .btn-act {
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.22s;
        white-space: nowrap;
    }

        .btn-act:hover {
            transform: translateY(-1px);
        }

    .btn-wa {
        background: #25D366;
        color: white;
    }

        .btn-wa:hover {
            background: #128C7E;
            color: white;
        }

    .btn-call {
        background: #3498db;
        color: white;
    }

        .btn-call:hover {
            background: #2980b9;
            color: white;
        }

    .btn-cv {
        background: #4285f4;
        color: white;
    }

        .btn-cv:hover {
            background: #3367d6;
            color: white;
        }

    .btn-mail {
        background: #6c757d;
        color: white;
    }

        .btn-mail:hover {
            background: #5a6268;
            color: white;
        }

    .btn-status {
        background: #F5FBFF;
        color: #08224A;
    }

        .btn-status:hover {
            background: #DCECF5;
            color: #08224A;
        }

    .btn-delete {
        background: white;
        color: #e74c3c;
        border: 1.5px solid #e74c3c;
        padding: 9px 14px;
    }

        .btn-delete:hover {
            background: #f8d7da;
            color: #e74c3c;
        }

    /* ======================
           EMPTY STATE
           ====================== */
    .empty-state {
        text-align: center;
        padding: 70px 24px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
    }

        .empty-state i {
            font-size: 64px;
            color: #DCECF5;
            display: block;
            margin-bottom: 18px;
        }

        .empty-state h4 {
            color: #08224A;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .empty-state p {
            color: #7A8CA0;
            font-size: 14.5px;
            margin: 0 0 22px;
            line-height: 1.6;
        }

    /* ======================
           STATUS MODAL
           ====================== */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }

    .modal-header {
        background: #134856;
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 16px 22px;
        border-bottom: none;
    }

        .modal-header .modal-title {
            font-size: 16px;
            font-weight: 700;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.85;
        }

    .modal-body {
        padding: 22px;
    }

        .modal-body label {
            font-weight: 700;
            color: #08224A;
            font-size: 13px;
            display: block;
            margin-bottom: 7px;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            border-radius: 10px;
            padding: 11px 14px;
            border: 1.5px solid #DCECF5;
            font-size: 14px;
            background: #F5FBFF;
            width: 100%;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
            color: #08224A;
        }

            .modal-body .form-control:focus,
            .modal-body .form-select:focus {
                border-color: #02B8F5;
                background: white;
                box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
            }

    .modal-footer {
        border-top: 1px solid #f0f0f0;
        padding: 14px 22px;
    }

    /* ======================
           RESPONSIVE
           ====================== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .app-card {
            padding: 18px;
        }

        .app-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .app-avatar {
            margin: 0 auto;
        }

        .app-contact {
            flex-direction: column;
            gap: 6px;
        }

        .app-actions {
            justify-content: flex-start;
        }

        .btn-act {
            flex: 1;
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
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 style="color:#08224A; margin:0; font-family:'Georgia',serif; font-weight:700;">
                <i class="fas fa-user-check me-2"></i>Tuition Applications
            </h2>
            <p style="color:#64748B; margin:6px 0 0 0; font-size:14px;">
                Teacher/User এর Tuition Application দেখুন এবং Manage করুন
            </p>
        </div>
        <a href="/Admin/TuitionPosts" class="btn-status" style="text-decoration:none; padding:11px 22px;">
            <i class="fas fa-clipboard-list me-1"></i> Manage Posts
        </a>
    </div>

    <!-- ======================
         STATS GRID
         ====================== -->
    <div class="stats-grid">

        <div class="stat-box">
            <div class="num"><?= e($ViewBag->TotalCount ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-list"></i> Total Applications
            </div>
        </div>

        <div class="stat-box pending">
            <div class="num"><?= e($ViewBag->PendingCount ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-clock"></i> Pending
            </div>
        </div>

        <div class="stat-box shortlisted">
            <div class="num"><?= e($ViewBag->ShortlistedCount ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-check"></i> Shortlisted
            </div>
        </div>

        <div class="stat-box selected">
            <div class="num"><?= e($ViewBag->SelectedCount ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-trophy"></i> Selected
            </div>
        </div>

    </div>

    <!-- ======================
         FILTER TABS
         ====================== -->
    <div class="filter-tabs">
        <a href="/Admin/TuitionApplications?status=all"
           class="filter-tab <?= $ViewBag->CurrentStatus === 'all' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> All
            <span class="badge-count"><?= e($ViewBag->TotalCount ?? 0) ?></span>
        </a>
        <a href="/Admin/TuitionApplications?status=Pending"
           class="filter-tab <?= $ViewBag->CurrentStatus === 'Pending' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i> Pending
            <span class="badge-count"><?= e($ViewBag->PendingCount ?? 0) ?></span>
        </a>
        <a href="/Admin/TuitionApplications?status=Shortlisted"
           class="filter-tab <?= $ViewBag->CurrentStatus === 'Shortlisted' ? 'active' : '' ?>">
            <i class="fas fa-check"></i> Shortlisted
            <span class="badge-count"><?= e($ViewBag->ShortlistedCount ?? 0) ?></span>
        </a>
        <a href="/Admin/TuitionApplications?status=Selected"
           class="filter-tab <?= $ViewBag->CurrentStatus === 'Selected' ? 'active' : '' ?>">
            <i class="fas fa-trophy"></i> Selected
            <span class="badge-count"><?= e($ViewBag->SelectedCount ?? 0) ?></span>
        </a>
        <a href="/Admin/TuitionApplications?status=Rejected"
           class="filter-tab <?= $ViewBag->CurrentStatus === 'Rejected' ? 'active' : '' ?>">
            <i class="fas fa-times"></i> Rejected
        </a>
    </div>

    <!-- ======================
         ANTI-FORGERY TOKEN
         ====================== -->
    <?= csrf_field() ?>

    <!-- ======================
         APPLICATIONS LIST
         ====================== -->
    <?php if ($Model === null || count($Model) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h4>No Applications Found</h4>
            <p>
                এই filter এ কোনো Application নেই।<br />
                Tuition Posts এ Apply এলে এখানে দেখা যাবে।
            </p>
            <a href="/Admin/TuitionPosts" class="btn-status" style="text-decoration:none; padding:11px 24px;">
                <i class="fas fa-clipboard-list me-1"></i> View Tuition Posts
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($Model as $app):
            // Generate initials
            $words = array_values(array_filter(explode(' ', $app->ApplicantName ?? 'A'), fn($w) => $w !== ''));
            $initials = count($words) >= 2
                ? mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1))
                : (count($words) === 1 && mb_strlen($words[0]) > 0 ? mb_strtoupper(mb_substr($words[0], 0, 1)) : 'A');
        ?>

            <div class="app-card status-<?= e($app->Status) ?>" id="app-<?= e($app->Id) ?>">

                <!-- ======================
                     HEADER
                     ====================== -->
                <div class="app-header">

                    <div class="app-avatar"><?= e($initials) ?></div>

                    <div class="app-headinfo">
                        <div class="app-name-row">
                            <h4 class="app-name"><?= e($app->ApplicantName) ?></h4>
                            <span class="status-pill <?= e($app->Status) ?>">
                                <?php switch ($app->Status):
                                    case 'Pending': ?>
                                        <i class="fas fa-hourglass-half"></i>
                                        <?php break;
                                    case 'Shortlisted': ?>
                                        <i class="fas fa-check"></i>
                                        <?php break;
                                    case 'Selected': ?>
                                        <i class="fas fa-trophy"></i>
                                        <?php break;
                                    case 'Rejected': ?>
                                        <i class="fas fa-times"></i>
                                        <?php break;
                                endswitch; ?>
                                <?= e($app->Status) ?>
                            </span>
                        </div>

                        <div class="app-contact">
                            <span><i class="fas fa-phone"></i><?= e($app->PhoneNumber) ?></span>

                            <?php if (!is_blank($app->WhatsAppNumber) && $app->WhatsAppNumber !== $app->PhoneNumber): ?>
                                <span><i class="fab fa-whatsapp" style="color:#25D366;"></i><?= e($app->WhatsAppNumber) ?></span>
                            <?php endif; ?>

                            <?php if (!is_blank($app->Email)): ?>
                                <span><i class="fas fa-envelope"></i><?= e($app->Email) ?></span>
                            <?php endif; ?>

                            <span><i class="fas fa-calendar"></i><?= e(fmt_date($app->AppliedAt, 'd M Y, h:i A')) ?></span>
                        </div>
                    </div>
                </div>

                <!-- ======================
                     TUITION REFERENCE
                     ====================== -->
                <?php if ($app->TuitionPost !== null): ?>
                    <div class="app-tuition-ref">
                        <i class="fas fa-clipboard-list me-1" style="color:#E09A00;"></i>
                        <strong>Applied For:</strong> <?= e($app->TuitionPost->Title) ?>

                        <?php if (!is_blank($app->TuitionPost->TuitionCode)): ?>
                            <span class="ref-code"><?= e($app->TuitionPost->TuitionCode) ?></span>
                        <?php endif; ?>

                        <?php if (!is_blank($app->TuitionPost->Area) || !is_blank($app->TuitionPost->Salary)): ?>
                            <span class="ref-meta">
                                <?php if (!is_blank($app->TuitionPost->Area)): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?= e($app->TuitionPost->Area) ?></span>
                                <?php endif; ?>
                                <?php if (!is_blank($app->TuitionPost->Salary)): ?>
                                    <span style="margin-left:12px;">
                                        <i class="fas fa-money-bill-wave"></i> ৳<?= e($app->TuitionPost->Salary) ?>/month
                                    </span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- ======================
                     COVER NOTE
                     ====================== -->
                <?php if (!is_blank($app->CoverNote)): ?>
                    <div class="app-note">
                        <strong><i class="fas fa-comment me-1"></i>Applicant's Note</strong>
                        <?= e($app->CoverNote) ?>
                    </div>
                <?php endif; ?>

                <!-- ======================
                     ADMIN NOTE
                     ====================== -->
                <?php if (!is_blank($app->AdminNote)): ?>
                    <div class="app-admin-note">
                        <strong>📝 Admin Note:</strong> <?= e($app->AdminNote) ?>
                    </div>
                <?php endif; ?>

                <!-- ======================
                     ACTIONS
                     ====================== -->
                <div class="app-actions">

                    <?php if (!is_blank($app->WhatsAppLink)): ?>
                        <a href="<?= e($app->WhatsAppLink) ?>" target="_blank" class="btn-act btn-wa">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    <?php endif; ?>

                    <a href="tel:<?= e($app->PhoneNumber) ?>" class="btn-act btn-call">
                        <i class="fas fa-phone"></i> Call
                    </a>

                    <?php if (!is_blank($app->CvLink)): ?>
                        <a href="<?= e($app->CvLink) ?>" target="_blank" class="btn-act btn-cv">
                            <i class="fas fa-file-pdf"></i> View CV
                        </a>
                    <?php endif; ?>

                    <?php if (!is_blank($app->Email)): ?>
                        <a href="mailto:<?= e($app->Email) ?>" class="btn-act btn-mail">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    <?php endif; ?>

                    <button class="btn-act btn-status"
                            onclick="openStatusModal(<?= (int)$app->Id ?>, <?= e(js((string)$app->Status)) ?>, <?= e(js($app->AdminNote ?? '')) ?>)">
                        <i class="fas fa-edit"></i> Update Status
                    </button>

                    <button class="btn-act btn-delete"
                            onclick="deleteApp(<?= (int)$app->Id ?>, <?= e(js((string)$app->ApplicantName)) ?>)">
                        <i class="fas fa-trash"></i>
                    </button>

                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- ============================================================ -->
<!-- STATUS MODAL                                                  -->
<!-- ============================================================ -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Update Application Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="statusAppId" />

                <div class="mb-3">
                    <label for="statusSelect">
                        <i class="fas fa-tag me-1"></i> Status
                    </label>
                    <select id="statusSelect" class="form-select">
                        <option value="Pending">⏱️ Pending (Awaiting Review)</option>
                        <option value="Shortlisted">✅ Shortlisted (Under Consideration)</option>
                        <option value="Selected">🏆 Selected (Approved)</option>
                        <option value="Rejected">❌ Rejected</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label for="statusNote">
                        <i class="fas fa-pen me-1"></i> Admin Note (Optional)
                    </label>
                    <textarea id="statusNote" class="form-control" rows="3"
                              placeholder="Add a note about this application..."></textarea>
                    <small style="color:#7A8CA0; font-size:12px; margin-top:4px; display:block;">
                        <i class="fas fa-info-circle me-1"></i>
                        এই note applicant কে notification এ জানানো হবে না, তবে পরবর্তী admin দেখতে পারবেন।
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="border-radius:9px; padding:10px 24px; font-weight:600;">
                    Cancel
                </button>
                <button type="button" class="btn" id="submitStatusBtn" onclick="submitStatus()"
                        style="background:#02B8F5; color:white; border-radius:9px; padding:10px 26px; font-weight:700;">
                    <i class="fas fa-save me-1"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SCRIPTS                                                       -->
<!-- ============================================================ -->
<?php section_start('Scripts'); ?>
    <script>
        (function () {
            'use strict';

            // ===== Anti-Forgery Token Helper =====
            window.getToken = function () {
                const t = document.querySelector('input[name="__RequestVerificationToken"]');
                return t ? t.value : '';
            };

            console.log('✅ Tuition Applications page loaded');
        })();

        // ===== Open Status Modal =====
        function openStatusModal(id, currentStatus, currentNote) {
            document.getElementById('statusAppId').value = id;
            document.getElementById('statusSelect').value = currentStatus || 'Pending';
            document.getElementById('statusNote').value = currentNote || '';
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        // ===== Submit Status Update =====
        function submitStatus() {
            const id = document.getElementById('statusAppId').value;
            const status = document.getElementById('statusSelect').value;
            const note = document.getElementById('statusNote').value;

            if (!id) {
                alert('Application ID missing.');
                return;
            }

            const btn = document.getElementById('submitStatusBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';

            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);
            formData.append('adminNote', note);
            formData.append('__RequestVerificationToken', window.getToken());

            fetch('/Admin/UpdateApplicationStatus', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;

                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    alert('Error: ' + err.message);
                });
        }

        // ===== Delete Application =====
        function deleteApp(id, name) {
            if (!confirm('⚠️ Delete application from "' + name + '"?\n\nThis action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('__RequestVerificationToken', window.getToken());

            fetch('/Admin/DeleteTuitionApplication', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                });
        }
    </script>
<?php section_end(); ?>
