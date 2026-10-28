<?php
$ViewData['Title'] = 'Teacher Requests';
?>

<style>
    /* ===== Main Content Layout ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== Status Badges ===== */
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        padding: 5px 14px;
        border-radius: 30px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .status-approved {
        background-color: #d4edda;
        color: #155724;
        padding: 5px 14px;
        border-radius: 30px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
        padding: 5px 14px;
        border-radius: 30px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    /* ===== Request Card ===== */
    .request-card {
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 14px;
        background: white;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.25s;
    }

        .request-card:hover {
            box-shadow: 0 6px 22px rgba(2,184,245,0.08);
            transform: translateY(-2px);
        }

        .request-card h5 {
            color: #08224A;
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 8px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

            .request-card h5 i {
                color: #028EA5;
            }

        .request-card .info-line {
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }

            .request-card .info-line i {
                color: #028EA5;
                margin-right: 5px;
                width: 14px;
                text-align: center;
            }

            .request-card .info-line .sep {
                color: #B9D6E6;
                margin: 0 8px;
            }

        .request-card .tag-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .request-card .tag {
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

            .request-card .tag i {
                font-size: 11px;
            }

    /* ===== Action Buttons ===== */
    .req-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

        .req-actions .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

            .req-actions .btn:hover {
                transform: translateY(-1px);
            }

    .btn-approve-req {
        background: #2ecc71;
        color: white;
    }

        .btn-approve-req:hover {
            background: #27ae60;
            color: white;
        }

    .btn-reject-req {
        background: #e74c3c;
        color: white;
    }

        .btn-reject-req:hover {
            background: #c0392b;
            color: white;
        }

    .btn-view {
        background: #0d6efd;
        color: white;
    }

        .btn-view:hover {
            background: #0b5ed7;
            color: white;
        }

    .btn-delete-teacher {
        background: white;
        color: #e74c3c;
        border: 1.5px solid #e74c3c !important;
    }

        .btn-delete-teacher:hover {
            background: #f8d7da;
            color: #e74c3c;
        }

    /* ===== Admin Note Box ===== */
    .admin-note-box {
        margin-top: 12px;
        padding: 10px 14px;
        background: #F5FBFF;
        border-radius: 8px;
        border-left: 3px solid #FFB300;
        font-size: 12.5px;
        color: #475569;
    }

        .admin-note-box strong {
            color: #08224A;
        }

    /* ===== Empty State ===== */
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

        .empty-state h5 {
            color: #08224A;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .empty-state p {
            color: #7A8CA0;
            font-size: 14px;
            margin: 0;
        }

    /* ===== Modals ===== */
    .modal-content {
        background-color: #ffffff !important;
        border-radius: 16px !important;
        border: none !important;
    }

    .modal-header {
        background-color: #134856 !important;
        color: white !important;
        border-radius: 16px 16px 0 0 !important;
        padding: 16px 22px !important;
    }

        .modal-header .modal-title {
            font-size: 16px;
            font-weight: 700;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1) !important;
        }

    .modal-body {
        background-color: #ffffff !important;
        padding: 22px !important;
    }

        .modal-body .form-control,
        .modal-body .form-select {
            border-radius: 10px;
            border: 1.5px solid #DCECF5;
            padding: 11px 14px;
            font-size: 14px;
            background: #F5FBFF;
        }

            .modal-body .form-control:focus {
                border-color: #02B8F5;
                background: white;
                box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
            }

        .modal-body label {
            font-weight: 700;
            color: #08224A;
            font-size: 13px;
            margin-bottom: 6px;
        }

    .modal-footer {
        border-top: 1px solid #f0f0f0 !important;
        padding: 14px 22px !important;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }
    }

    @media (max-width: 576px) {
        .request-card {
            padding: 16px;
        }

        .req-actions {
            width: 100%;
        }

            .req-actions .btn {
                flex: 1;
                justify-content: center;
            }
    }
</style>

<!-- ===== Admin Sidebar (Partial) ===== -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ===== Main Content ===== -->
<div class="admin-main-content">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color:#08224A; margin:0;"><i class="fas fa-user-graduate me-2"></i>Teacher Requests</h2>
            <p style="color:#64748B; margin:4px 0 0 0; font-size:14px;">সব Teacher Request দেখুন, Approve বা Reject করুন</p>
        </div>
        <span class="badge bg-warning text-dark" style="font-size:13px; padding:8px 14px;">
            <?= e($ViewBag->PendingCount) ?> Pending
        </span>
    </div>

    <?php if ($TempData['Success'] !== null): ?>
        <div class="alert alert-success" style="border-radius:10px;">
            <i class="fas fa-check-circle me-2"></i><?= e($TempData['Success']) ?>
        </div>
    <?php endif; ?>
    <?php if ($TempData['Error'] !== null): ?>
        <div class="alert alert-danger" style="border-radius:10px;">
            <i class="fas fa-exclamation-circle me-2"></i><?= e($TempData['Error']) ?>
        </div>
    <?php endif; ?>

    <?= csrf_field() ?>

    <?php if ($Model === null || count($Model) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-user-graduate"></i>
            <h5>No teacher requests found</h5>
            <p>Total requests in database: <?= e(count($Model ?? [])) ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($Model as $request): ?>
            <?php $statusClass = $request->Status === 'Pending' ? 'status-pending' : ($request->Status === 'Approved' ? 'status-approved' : 'status-rejected'); ?>

            <div class="request-card" id="request-<?= e($request->Id) ?>">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div style="flex:1; min-width:250px;">
                        <h5>
                            <i class="fas fa-user"></i>
                            <?= e($request->FullName) ?>
                            <span class="<?= e($statusClass) ?>"><?= e($request->Status) ?></span>
                        </h5>

                        <div class="info-line">
                            <i class="fas fa-envelope"></i><?= e($request->Email) ?>
                            <span class="sep">|</span>
                            <i class="fas fa-phone"></i><?= e($request->MobileNumber) ?>
                            <span class="sep">|</span>
                            <i class="fas fa-calendar"></i><?= e(fmt_date($request->RequestDate, 'd M Y')) ?>
                        </div>

                        <div class="tag-group">
                            <?php if (!is_blank($request->SubjectExpertise)): ?>
                                <span class="tag"><i class="fas fa-book"></i><?= e($request->SubjectExpertise) ?></span>
                            <?php endif; ?>
                            <?php if (!is_blank($request->Experience)): ?>
                                <span class="tag"><i class="fas fa-clock"></i><?= e($request->Experience) ?></span>
                            <?php endif; ?>
                            <?php if (!is_blank($request->HourlyRate)): ?>
                                <span class="tag"><i class="fas fa-money-bill-wave"></i>৳<?= e($request->HourlyRate) ?>/hr</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="req-actions">
                        <?php if ($request->Status === 'Pending'): ?>
                            <button class="btn btn-approve-req"
                                    onclick="processRequest(<?= (int)$request->Id ?>, 'Approve')"
                                    title="Approve Request">
                                <i class="fas fa-check"></i>Approve
                            </button>
                            <button class="btn btn-reject-req"
                                    onclick="processRequest(<?= (int)$request->Id ?>, 'Reject')"
                                    title="Reject Request">
                                <i class="fas fa-times"></i>Reject
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-view"
                                onclick="viewRequestDetails(<?= (int)$request->Id ?>)"
                                title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-delete-teacher"
                                data-userid="<?= e($request->UserId) ?>"
                                data-requestid="<?= e($request->Id) ?>"
                                data-name="<?= e(substr(json_encode((string)$request->FullName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES), 1, -1)) ?>"
                                title="Delete Teacher Account">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <?php if (!is_blank($request->AdminNote)): ?>
                    <div class="admin-note-box">
                        <strong>Admin Note:</strong> <?= e($request->AdminNote) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- ===== Details Modal ===== -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-graduate me-2"></i>Teacher Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetails">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading request details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:8px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Admin Note Modal ===== -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalTitle"><i class="fas fa-pen me-2"></i>Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="noteRequestId" />
                <input type="hidden" id="noteAction" />
                <p id="noteMessage" style="font-size:13.5px; color:#475569; margin-bottom:14px;"></p>
                <label for="adminNote">Admin Note</label>
                <textarea id="adminNote" class="form-control" rows="3"
                          placeholder="Add a note about this request (visible to the user)..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                <button type="button" class="btn" id="noteConfirmBtn"
                        onclick="submitProcessRequest()"
                        style="background:#02B8F5; color:white; border-radius:8px; padding:9px 26px; font-weight:600;">
                    <i class="fas fa-check me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
    <script>
        // ===== Anti-Forgery Token Helper =====
        function getToken() {
            const t = document.querySelector('input[name="__RequestVerificationToken"]');
            return t ? t.value : '';
        }

        // ===== Process Request (Approve/Reject) =====
        function processRequest(id, action) {
            const modal = new bootstrap.Modal(document.getElementById('noteModal'));
            document.getElementById('noteRequestId').value = id;
            document.getElementById('noteAction').value = action;
            document.getElementById('adminNote').value = '';

            const title = document.getElementById('noteModalTitle');
            const msg = document.getElementById('noteMessage');
            const btn = document.getElementById('noteConfirmBtn');

            if (action === 'Approve') {
                title.innerHTML = '<i class="fas fa-check-circle me-2"></i>Approve Teacher Request';
                msg.innerHTML = 'The user will become a Teacher and gain access to the Teacher Dashboard.';
                btn.style.background = '#2ecc71';
            } else {
                title.innerHTML = '<i class="fas fa-times-circle me-2"></i>Reject Teacher Request';
                msg.innerHTML = 'The user will be notified. You can add a reason below.';
                btn.style.background = '#e74c3c';
            }

            modal.show();
        }

        // ===== Submit Process Request =====
        function submitProcessRequest() {
            const id = document.getElementById('noteRequestId').value;
            const action = document.getElementById('noteAction').value;
            const note = document.getElementById('adminNote').value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', action);
            formData.append('adminNote', note);
            formData.append('__RequestVerificationToken', getToken());

            const btn = document.getElementById('noteConfirmBtn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            btn.disabled = true;

            fetch('/Admin/ProcessTeacherRequest', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(err => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    alert('Error processing request: ' + err.message);
                });
        }

        // ===== Delete Teacher =====
        document.querySelectorAll('.btn-delete-teacher').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const userId = this.dataset.userid;
                const requestId = this.dataset.requestid;
                const name = this.dataset.name;
                deleteTeacher(userId, requestId, name);
            });
        });

        function deleteTeacher(userId, requestId, name) {
            if (!confirm('আপনি কি নিশ্চিত যে "' + name + '"-কে স্থায়ীভাবে ডিলিট করতে চান?\n\nএটি ইউজার অ্যাকাউন্টসহ তার সব Teacher Request ডাটাবেস থেকে সম্পূর্ণ মুছে ফেলবে। এই কাজটি আর ফিরিয়ে আনা যাবে না।')) {
                return;
            }

            const formData = new FormData();
            formData.append('id', userId);
            formData.append('requestId', requestId);
            formData.append('__RequestVerificationToken', getToken());

            fetch('/Admin/DeleteTeacherAccount', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(() => alert('Error deleting teacher account.'));
        }

        // ===== View Request Details =====
        function viewRequestDetails(id) {
            const detailsDiv = document.getElementById('requestDetails');
            detailsDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading request details...</p></div>';

            fetch('/Admin/GetTeacherRequestDetails?id=' + id)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const r = data.request;
                        const statusClass = r.status == 'Pending' ? 'status-pending' : (r.status == 'Approved' ? 'status-approved' : 'status-rejected');

                        let html = '';
                        html += '<div class="row g-3">';
                        html += '<div class="col-md-6"><strong>Full Name:</strong> ' + (r.fullName || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Status:</strong> <span class="' + statusClass + '">' + r.status + '</span></div>';
                        html += '<div class="col-md-6"><strong>Email:</strong> ' + (r.email || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Mobile:</strong> ' + (r.mobileNumber || '-') + '</div>';
                        html += '<div class="col-12"><hr style="margin:10px 0;" /></div>';
                        html += '<div class="col-md-6"><strong>Education:</strong> ' + (r.education || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Institution:</strong> ' + (r.institution || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Subject Expertise:</strong> ' + (r.subjectExpertise || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Experience:</strong> ' + (r.experience || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Teaching Style:</strong> ' + (r.teachingStyle || 'Not specified') + '</div>';
                        html += '<div class="col-md-6"><strong>Hourly Rate:</strong> ৳' + (r.hourlyRate || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Available Days:</strong> ' + (r.availableDays || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Preferred Time:</strong> ' + (r.preferredTime || 'Not specified') + '</div>';
                        html += '<div class="col-12"><hr style="margin:10px 0;" /></div>';
                        html += '<div class="col-12"><strong>CV Link:</strong> ' + (r.cvLink ? '<a href="' + r.cvLink + '" target="_blank">View CV <i class="fas fa-external-link-alt"></i></a>' : 'Not provided') + '</div>';
                        html += '<div class="col-12"><strong>Why Teach:</strong> ' + (r.whyTeach || '-') + '</div>';
                        html += '<div class="col-12"><hr style="margin:10px 0;" /></div>';
                        html += '<div class="col-md-6"><strong>Request Date:</strong> ' + (r.requestDate || '-') + '</div>';
                        html += '<div class="col-md-6"><strong>Admin Note:</strong> ' + (r.adminNote || 'No note') + '</div>';
                        html += '</div>';

                        detailsDiv.innerHTML = html;
                        new bootstrap.Modal(document.getElementById('requestModal')).show();
                    } else {
                        detailsDiv.innerHTML = '<div class="alert alert-danger text-center">' + (data.message || 'Error') + '</div>';
                    }
                })
                .catch(() => {
                    detailsDiv.innerHTML = '<div class="alert alert-danger text-center">Error loading details</div>';
                });
        }
    </script>
<?php section_end(); ?>
