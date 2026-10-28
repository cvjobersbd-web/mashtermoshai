<?php
$ViewData['Title'] = 'Tutor Requests';
?>

<style>
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-box {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border-left: 4px solid #02B8F5;
    }

        .stat-box.pending {
            border-left-color: #f39c12;
        }

        .stat-box.contacted {
            border-left-color: #3498db;
        }

        .stat-box.completed {
            border-left-color: #2ecc71;
        }

        .stat-box .num {
            font-size: 28px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.2;
        }

        .stat-box .lbl {
            font-size: 13px;
            color: #64748B;
            margin-top: 4px;
        }

    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 9px 22px;
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
        gap: 5px;
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

    .req-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        border-left: 5px solid #FFB300;
        transition: all 0.25s;
    }

        .req-card:hover {
            box-shadow: 0 6px 22px rgba(2,184,245,0.08);
        }

        .req-card.status-Pending {
            border-left-color: #f39c12;
        }

        .req-card.status-Contacted {
            border-left-color: #3498db;
        }

        .req-card.status-Completed {
            border-left-color: #2ecc71;
        }

        .req-card.status-Cancelled {
            border-left-color: #e74c3c;
        }

    .req-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 10px;
    }

        .req-type-badge.SpecificTutor {
            background: #CFF2FC;
            color: #08224A;
        }

        .req-type-badge.NeedTutor {
            background: #FFEBB8;
            color: #8a5a00;
        }

        .req-type-badge.AreaTuition {
            background: #E7DCF5;
            color: #5a3fa0;
        }

    .req-card h5 {
        color: #08224A;
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .req-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        font-size: 13px;
        color: #64748B;
        margin-bottom: 10px;
    }

        .req-meta span i {
            color: #028EA5;
            margin-right: 4px;
        }

    .req-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .req-tag {
        background: #F5FBFF;
        color: #08224A;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .req-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #f0f0f0;
    }

    .btn-action {
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-whatsapp {
        background: #25D366;
        color: white;
    }

        .btn-whatsapp:hover {
            background: #128C7E;
            color: white;
            transform: translateY(-1px);
        }

    .btn-call {
        background: #3498db;
        color: white;
    }

        .btn-call:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-1px);
        }

    .btn-email {
        background: #6c757d;
        color: white;
    }

        .btn-email:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-1px);
        }

    .btn-status {
        background: #F5FBFF;
        color: #08224A;
    }

        .btn-status:hover {
            background: #DCECF5;
        }

    .btn-delete {
        background: white;
        color: #e74c3c;
        border: 1.5px solid #e74c3c;
    }

        .btn-delete:hover {
            background: #f8d7da;
        }

    .status-pill {
        padding: 4px 14px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

        .status-pill.Pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-pill.Contacted {
            background: #cfe2ff;
            color: #084298;
        }

        .status-pill.Completed {
            background: #d4edda;
            color: #155724;
        }

        .status-pill.Cancelled {
            background: #f8d7da;
            color: #721c24;
        }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

        .empty-state i {
            font-size: 56px;
            color: #DCECF5;
            display: block;
            margin-bottom: 16px;
        }

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
    }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color:#08224A; margin:0;">
                <i class="fas fa-user-plus me-2"></i>Tutor Requests
            </h2>
            <p style="color:#64748B; margin:4px 0 0 0; font-size:14px;">
                Students থেকে আসা সব টিউটর রিকোয়েস্ট দেখুন, WhatsApp এ যোগাযোগ করুন
            </p>
        </div>
        <?php if (($ViewBag->PendingCount ?? 0) > 0): ?>
            <span class="badge bg-warning text-dark" style="font-size:13px; padding:8px 14px;">
                <?= e($ViewBag->PendingCount) ?> Pending
            </span>
        <?php endif; ?>
    </div>

    <?= csrf_field() ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-box"><div class="num"><?= e($ViewBag->TotalCount) ?></div><div class="lbl"><i class="fas fa-list"></i> Total</div></div>
        <div class="stat-box pending"><div class="num"><?= e($ViewBag->PendingCount) ?></div><div class="lbl"><i class="fas fa-clock"></i> Pending</div></div>
        <div class="stat-box contacted"><div class="num"><?= e($ViewBag->ContactedCount) ?></div><div class="lbl"><i class="fas fa-phone"></i> Contacted</div></div>
        <div class="stat-box completed"><div class="num"><?= e($ViewBag->CompletedCount) ?></div><div class="lbl"><i class="fas fa-check-circle"></i> Completed</div></div>
    </div>

    <!-- Filters -->
    <div class="filter-tabs">
        <a href="/Admin/TutorRequests?status=all" class="filter-tab <?= $ViewBag->CurrentStatus === 'all' ? 'active' : '' ?>"><i class="fas fa-list"></i> All</a>
        <a href="/Admin/TutorRequests?status=Pending" class="filter-tab <?= $ViewBag->CurrentStatus === 'Pending' ? 'active' : '' ?>"><i class="fas fa-clock"></i> Pending</a>
        <a href="/Admin/TutorRequests?status=Contacted" class="filter-tab <?= $ViewBag->CurrentStatus === 'Contacted' ? 'active' : '' ?>"><i class="fas fa-phone"></i> Contacted</a>
        <a href="/Admin/TutorRequests?status=Completed" class="filter-tab <?= $ViewBag->CurrentStatus === 'Completed' ? 'active' : '' ?>"><i class="fas fa-check"></i> Completed</a>
    </div>

    <!-- Requests List -->
    <?php if ($Model === null || count($Model) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5 style="color:#08224A;">No requests found</h5>
            <p style="color:#7A8CA0;">There are no tutor requests matching your filter.</p>
        </div>
    <?php else: ?>
        <?php foreach ($Model as $r):
            $waLink = $r->WhatsAppLink;
            $waNum = !is_blank($r->WhatsAppNumber) ? $r->WhatsAppNumber : $r->PhoneNumber;
        ?>

            <div class="req-card status-<?= e($r->Status) ?>" id="req-<?= e($r->Id) ?>">
                <span class="req-type-badge <?= e($r->RequestType) ?>">
                    <?php if ($r->RequestType === 'SpecificTutor'): ?>
                        <i class="fas fa-user-tie"></i>
                    <?php elseif ($r->RequestType === 'NeedTutor'): ?>

                        <i class="fas fa-hands-helping"></i>
                    <?php else: ?>

                        <i class="fas fa-map-marked-alt"></i>
                    <?php endif; ?>
                    <?= e($r->RequestTypeLabel) ?>
                </span>

                <h5>
                    <i class="fas fa-user-graduate" style="color:#08224A;"></i>
                    <?= e($r->StudentName) ?>
                    <span class="status-pill <?= e($r->Status) ?>"><?= e($r->Status) ?></span>
                </h5>

                <div class="req-meta">
                    <span><i class="fas fa-phone"></i><?= e($r->PhoneNumber) ?></span>
                    <?php if (!is_blank($r->WhatsAppNumber) && $r->WhatsAppNumber !== $r->PhoneNumber): ?>
                        <span><i class="fab fa-whatsapp"></i><?= e($r->WhatsAppNumber) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($r->Email)): ?>
                        <span><i class="fas fa-envelope"></i><?= e($r->Email) ?></span>
                    <?php endif; ?>
                    <span><i class="fas fa-calendar"></i><?= e(fmt_date($r->CreatedAt, 'd M Y, h:i A')) ?></span>
                </div>

                <div class="req-tags">
                    <?php if (!is_blank($r->TutorName)): ?>
                        <span class="req-tag"><i class="fas fa-chalkboard-teacher"></i>Tutor: <?= e($r->TutorName) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($r->TutorId)): ?>
                        <span class="req-tag"><i class="fas fa-id-badge"></i><?= e($r->TutorId) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($r->StudentClass)): ?>
                        <span class="req-tag"><i class="fas fa-graduation-cap"></i><?= e($r->StudentClass) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($r->RequiredSubject)): ?>
                        <span class="req-tag"><i class="fas fa-book"></i><?= e($r->RequiredSubject) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($r->PreferredArea)): ?>
                        <span class="req-tag"><i class="fas fa-map-marker-alt"></i><?= e($r->PreferredArea) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($r->TeachingMode)): ?>
                        <span class="req-tag"><i class="fas fa-laptop"></i><?= e($r->TeachingMode) ?></span>
                    <?php endif; ?>
                    <?php if (!is_blank($r->MonthlyBudget)): ?>
                        <span class="req-tag"><i class="fas fa-money-bill-wave"></i>৳<?= e($r->MonthlyBudget) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!is_blank($r->AdditionalNotes)): ?>
                    <div style="margin-top:12px; padding:10px 14px; background:#F5FBFF; border-radius:8px; font-size:13px; color:#475569; border-left:3px solid #FFB300; line-height:1.6;">
                        <strong style="color:#08224A;">Notes:</strong> <?= e($r->AdditionalNotes) ?>
                    </div>
                <?php endif; ?>

                <?php if (!is_blank($r->AdminNote)): ?>
                    <div style="margin-top:8px; padding:10px 14px; background:#CFF2FC; border-radius:8px; font-size:13px; color:#08224A; border-left:3px solid #2ecc71;">
                        <strong>Admin Note:</strong> <?= e($r->AdminNote) ?>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="req-actions">
                    <?php if (!is_blank($waLink)): ?>
                        <a href="<?= e($waLink) ?>" target="_blank" class="btn-action btn-whatsapp">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    <?php endif; ?>
                    <?php if (!is_blank($r->PhoneNumber)): ?>
                        <a href="tel:<?= e($r->PhoneNumber) ?>" class="btn-action btn-call">
                            <i class="fas fa-phone"></i> Call
                        </a>
                    <?php endif; ?>
                    <?php if (!is_blank($r->Email)): ?>
                        <a href="mailto:<?= e($r->Email) ?>" class="btn-action btn-email">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    <?php endif; ?>
                    <button class="btn-action btn-status" onclick="openStatusModal(<?= (int)$r->Id ?>, <?= e(js((string)$r->Status)) ?>)">
                        <i class="fas fa-edit"></i> Update Status
                    </button>
                    <button class="btn-action btn-delete" onclick="deleteRequest(<?= (int)$r->Id ?>, <?= e(js((string)$r->StudentName)) ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header" style="background:#02B8F5; color:white; border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:22px;">
                <input type="hidden" id="statusRequestId" />
                <div class="mb-3">
                    <label style="font-weight:700; color:#08224A; font-size:13px; margin-bottom:6px; display:block;">
                        Status
                    </label>
                    <select id="statusSelect" class="form-control" style="border-radius:10px; padding:11px 14px; border:1.5px solid #DCECF5;">
                        <option value="Pending">Pending</option>
                        <option value="Contacted">Contacted</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight:700; color:#08224A; font-size:13px; margin-bottom:6px; display:block;">
                        Admin Note (Optional)
                    </label>
                    <textarea id="statusNote" class="form-control" rows="3"
                              placeholder="Add a note about this request..."
                              style="border-radius:10px; padding:11px 14px; border:1.5px solid #DCECF5;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:14px 22px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                <button type="button" class="btn" onclick="submitStatusUpdate()"
                        style="background:#02B8F5; color:white; border-radius:8px; padding:9px 28px; font-weight:600;">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
    <script>
        function getToken() {
            const t = document.querySelector('input[name="__RequestVerificationToken"]');
            return t ? t.value : '';
        }

        function openStatusModal(id, currentStatus) {
            document.getElementById('statusRequestId').value = id;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusNote').value = '';
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function submitStatusUpdate() {
            const id = document.getElementById('statusRequestId').value;
            const status = document.getElementById('statusSelect').value;
            const note = document.getElementById('statusNote').value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);
            formData.append('adminNote', note);
            formData.append('__RequestVerificationToken', getToken());

            fetch('/Admin/UpdateTutorRequestStatus', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(() => alert('Error updating status.'));
        }

        function deleteRequest(id, name) {
            if (!confirm(`Delete request from "${name}"?\n\nThis cannot be undone.`)) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('__RequestVerificationToken', getToken());

            fetch('/Admin/DeleteTutorRequest', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(() => alert('Error deleting request.'));
        }
    </script>
<?php section_end(); ?>
