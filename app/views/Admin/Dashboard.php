<?php
$ViewData['Title'] = 'Admin Dashboard';

$pendingTeacherCount = 0;

try {
    $requests = db()->GetAllTeacherRequests();
    $pendingTeacherCount = count(array_filter($requests, fn($r) => $r->Status === 'Pending'));
} catch (Throwable $ex) { }

$ViewBag->PendingCount = $pendingTeacherCount;
?>

<style>
    /* ===== Main Content Layout (uses _AdminSidebar) ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== Stats Cards ===== */
    .stats-card {
        transition: transform 0.2s;
    }

        .stats-card:hover {
            transform: translateY(-5px);
        }

    /* ===== Table ===== */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, .02);
    }

    .badge {
        padding: 0.5em 0.75em;
        font-weight: 600;
    }

    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, .07);
    }

    .card-header {
        border-radius: 12px 12px 0 0 !important;
    }

    .table th {
        font-weight: 600;
        color: #08224A;
    }

    .btn-sm {
        margin: 2px;
    }

    /* ===== User Detail Modal ===== */
    .user-detail-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #02B8F5;
    }

    .detail-section {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }

        .detail-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

    .detail-section-title {
        font-weight: 600;
        color: #08224A;
        font-size: 14px;
        margin-bottom: 8px;
        padding: 5px 10px;
        background-color: #F5FBFF;
        border-radius: 5px;
    }

        .detail-section-title i {
            margin-right: 8px;
            color: #028EA5;
        }

    .detail-row {
        display: flex;
        padding: 6px 10px;
        font-size: 13px;
        border-bottom: 1px solid #f8f8f8;
    }

        .detail-row:last-child {
            border-bottom: none;
        }

    .detail-label {
        width: 140px;
        color: #64748B;
        flex-shrink: 0;
        font-weight: 500;
    }

    .detail-value {
        color: #08224A;
        word-break: break-all;
    }

        .detail-value a {
            color: #08224A;
            text-decoration: underline;
        }

    /* ===== Modals ===== */
    .modal-content {
        background-color: #ffffff !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
    }

    .modal-header {
        background-color: #134856 !important;
        color: white !important;
        border-radius: 16px 16px 0 0 !important;
        padding: 15px 20px !important;
    }

        .modal-header .btn-close {
            filter: brightness(0) invert(1) !important;
        }

    .modal-body {
        padding: 20px !important;
        max-height: 70vh !important;
        overflow-y: auto !important;
        background-color: #ffffff !important;
    }

        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #02B8F5;
            border-radius: 10px;
        }

            .modal-body::-webkit-scrollbar-thumb:hover {
                background: #0099D6;
            }

    .modal-footer {
        border-top: 1px solid #f0f0f0 !important;
        padding: 12px 20px !important;
        border-radius: 0 0 16px 16px !important;
        background-color: #fafafa !important;
    }

    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.4) !important;
    }

        .modal-backdrop.show {
            opacity: 1 !important;
        }

    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: scale(0.95);
    }

    .modal.show .modal-dialog {
        transform: scale(1);
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }
    }

    @media (max-width: 576px) {
        .detail-row {
            flex-direction: column;
            padding: 8px 10px;
        }

        .detail-label {
            width: 100%;
            font-weight: 600;
        }

        .detail-section-title {
            font-size: 13px;
        }

        .modal-body {
            max-height: 60vh !important;
            padding: 15px !important;
        }

        .user-detail-avatar {
            width: 60px;
            height: 60px;
        }
    }
</style>

<!-- ===== Admin Sidebar (Partial) ===== -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ===== Main Content ===== -->
<div class="admin-main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color:#08224A;"><i class="fas fa-chart-pie me-2"></i>Dashboard</h2>
        <div>
            <span class="me-3" style="color:#08224A;">
                <i class="fas fa-user-circle me-1"></i> <?= e(session_get('UserFullName')) ?>
            </span>
            <span class="badge bg-success"><?= e(session_get('UserRole')) ?></span>
        </div>
    </div>

    <!-- ===== Stats Cards ===== -->
    <div class="row mb-4 admin-dash-stats">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #08224A, #134856);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6><i class="fas fa-users me-2"></i>Total Users</h6>
                            <h2 class="fw-bold"><?= e(count($Model)) ?></h2>
                        </div>
                        <div style="font-size: 40px; opacity: 0.3;"><i class="fas fa-users"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #2d7d46, #3a9e5a);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6><i class="fas fa-user-check me-2"></i>Active Users</h6>
                            <?php
                                $activeCount = 0;
                                foreach ($Model as $u) { if ($u->IsActive) $activeCount++; }
                            ?>
                            <h2 class="fw-bold"><?= e($activeCount) ?></h2>
                        </div>
                        <div style="font-size: 40px; opacity: 0.3;"><i class="fas fa-user-check"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #b8860b, #daa520);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6><i class="fas fa-user-cog me-2"></i>Admins</h6>
                            <?php
                                $adminCount = 0;
                                foreach ($Model as $u) { if ($u->Role === 'Admin') $adminCount++; }
                            ?>
                            <h2 class="fw-bold"><?= e($adminCount) ?></h2>
                        </div>
                        <div style="font-size: 40px; opacity: 0.3;"><i class="fas fa-user-cog"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card text-white" style="background: linear-gradient(135deg, #6f42c1, #8e63d9);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6><i class="fas fa-chalkboard-teacher me-2"></i>Pending Teachers</h6>
                            <h2 class="fw-bold"><?= e($ViewBag->PendingCount ?? 0) ?></h2>
                        </div>
                        <div style="font-size: 40px; opacity: 0.3;"><i class="fas fa-chalkboard-teacher"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Quick Actions ===== -->
    <div class="card shadow mb-4">
        <div class="card-header" style="background-color:#F5FBFF; color:#08224A;">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <a href="/Admin/AddTuitionPost" class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                       style="background:#02B8F5; color:white; padding:14px; border-radius:10px; font-weight:600;">
                        <i class="fas fa-plus-circle"></i> Add Tuition Post
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/Admin/TuitionApplications" class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                       style="background:#0d6efd; color:white; padding:14px; border-radius:10px; font-weight:600;">
                        <i class="fas fa-user-check"></i> Applications
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/Admin/TeacherRequests" class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                       style="background:#8e63d9; color:white; padding:14px; border-radius:10px; font-weight:600;">
                        <i class="fas fa-user-graduate"></i> Teacher Requests
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="/Helpdesk" class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                       style="background:#2ecc71; color:white; padding:14px; border-radius:10px; font-weight:600;">
                        <i class="fas fa-headset"></i> Helpdesk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== All Users ===== -->
    <div class="card shadow">
        <div class="card-header" style="background-color:#134856; color:white;">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover admin-cards-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Model as $user): ?>
                            <tr>
                                <td data-label="ID"><?= e($user->Id) ?></td>
                                <td data-label="Username"><strong><?= e($user->UserName) ?></strong></td>
                                <td data-label="Full Name"><?= e($user->FullName) ?></td>
                                <td data-label="Email"><?= e($user->Email) ?></td>
                                <td data-label="Role">
                                    <?php
                                        $roleClass = $user->Role === 'Admin' ? 'bg-warning' : ($user->Role === 'Teacher' ? 'bg-info' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?= e($roleClass) ?>"><?= e($user->Role) ?></span>
                                </td>
                                <td data-label="Status">
                                    <?php
                                        $statusClass = $user->IsActive ? 'bg-success' : 'bg-danger';
                                        $statusText = $user->IsActive ? 'Active' : 'Inactive';
                                    ?>
                                    <span class="badge <?= e($statusClass) ?>"><?= e($statusText) ?></span>
                                </td>
                                <td data-label="Created"><?= e(fmt_date($user->CreatedAt, 'd/m/Y')) ?></td>
                                <td data-label="Last Login"><?= e(fmt_date($user->LastLoginAt, 'd/m/Y H:i', 'Never')) ?></td>
                                <td class="td-actions">
                                    <button class="btn btn-sm btn-info text-white" onclick="viewUser(<?= (int)$user->Id ?>)" title="View Full Profile">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="changePassword(<?= (int)$user->Id ?>)" title="Change Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <?php
                                        $toggleClass = $user->IsActive ? 'btn-secondary' : 'btn-success';
                                        $toggleIcon = $user->IsActive ? 'fa-pause' : 'fa-play';
                                        $toggleTitle = $user->IsActive ? 'Disable' : 'Enable';
                                    ?>
                                    <button class="btn btn-sm <?= e($toggleClass) ?>"
                                            onclick="toggleStatus(<?= (int)$user->Id ?>, <?= $user->IsActive ? 'false' : 'true' ?>)"
                                            title="<?= e($toggleTitle) ?>">
                                        <i class="fas <?= e($toggleIcon) ?>"></i>
                                    </button>
                                    <?php if ($user->UserName !== 'admin'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= (int)$user->Id ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== User Details Modal ===== -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>User Full Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetails">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading user profile...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Password Modal ===== -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#02B8F5; color:white;">
                <h5 class="modal-title" id="passwordModalLabel">
                    <i class="fas fa-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="passwordUserId" />
                <div class="text-center mb-4">
                    <i class="fas fa-lock" style="font-size: 48px; color: #08224A; opacity: 0.3;"></i>
                    <p class="text-muted mt-2">Enter new password for the user</p>
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label fw-bold" style="color: #08224A;">
                        <i class="fas fa-key me-1"></i> New Password
                    </label>
                    <input type="password" id="newPassword" class="form-control" placeholder="Min 6 characters" style="border-radius: 8px; padding: 12px 15px;" />
                    <small class="text-muted" style="display: block; margin-top: 5px;">
                        <i class="fas fa-info-circle me-1"></i> Password must be at least 6 characters
                    </small>
                </div>
                <div class="mb-3">
                    <label for="confirmNewPassword" class="form-label fw-bold" style="color: #08224A;">
                        <i class="fas fa-check-double me-1"></i> Confirm Password
                    </label>
                    <input type="password" id="confirmNewPassword" class="form-control" placeholder="Confirm new password" style="border-radius: 8px; padding: 12px 15px;" />
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px 25px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="submitPassword()" style="background-color: #02B8F5; border-color: #02B8F5; border-radius: 8px; padding: 10px 25px;">
                    <i class="fas fa-save me-1"></i> Update Password
                </button>
            </div>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
    <script>
        // ===== Change Admin Password (from Sidebar) =====
        function changeAdminPassword() {
            var currentUserId = <?= js((string)session_get('UserId')) ?>;
            if (currentUserId) { changePassword(parseInt(currentUserId)); }
            else { alert('User ID not found. Please login again.'); }
        }

        var userId = 0;

        function changePassword(id) {
            userId = id;
            document.getElementById('passwordUserId').value = id;
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmNewPassword').value = '';
            var modal = new bootstrap.Modal(document.getElementById('passwordModal'));
            modal.show();
        }

        function submitPassword() {
            var password = document.getElementById('newPassword').value;
            var confirmPassword = document.getElementById('confirmNewPassword').value;

            if (password.length < 6) { alert('Password must be at least 6 characters'); return; }
            if (password !== confirmPassword) { alert('Passwords do not match'); return; }

            var id = document.getElementById('passwordUserId').value;
            if (!id) { alert('User ID not found'); return; }

            var formData = new FormData();
            formData.append('id', id);
            formData.append('newPassword', password);

            var token = document.querySelector('input[name="__RequestVerificationToken"]');
            var headers = {};
            if (token) { headers['RequestVerificationToken'] = token.value; }

            var btn = document.querySelector('#passwordModal .btn-success');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
            btn.disabled = true;

            fetch('/Admin/UpdatePassword', {
                method: 'POST',
                body: formData,
                headers: headers
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert(data.message);
                    if (data.success) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
                        if (modal) { modal.hide(); }
                        location.reload();
                    }
                })
                .catch(function (error) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Error updating password: ' + error.message);
                });
        }

        // ===== View User Details =====
        function viewUser(id) {
            var detailsDiv = document.getElementById('userDetails');
            detailsDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading user profile...</p></div>';

            fetch('/Admin/UserDetails?id=' + id)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        var u = data.user;
                        var profileImage = u.profileImageLink || 'https://i.ibb.co/fd9s88Y/IMG-6916-1.png';
                        var roleBadge = u.role == 'Admin' ? 'bg-warning' : (u.role == 'Teacher' ? 'bg-info' : 'bg-secondary');
                        var statusBadge = u.isActive ? 'bg-success' : 'bg-danger';
                        var statusText = u.isActive ? 'Active' : 'Inactive';
                        var notProvided = 'Not provided';

                        var html = '';
                        html += '<div class="text-center mb-3">';
                        html += '<img src="' + profileImage + '" alt="Profile" class="user-detail-avatar" />';
                        html += '<h5 class="mt-2" style="color:#08224A;">' + u.fullName + '</h5>';
                        html += '<div><span class="badge ' + roleBadge + '">' + u.role + '</span><span class="badge ' + statusBadge + ' ms-1">' + statusText + '</span></div>';
                        html += '<p class="text-muted small mt-1">' + u.userName + ' • ' + u.email + '</p>';
                        html += '</div>';

                        html += '<div class="detail-section">';
                        html += '<div class="detail-section-title"><i class="fas fa-id-card"></i>Basic Information</div>';
                        html += '<div class="detail-row"><span class="detail-label">ID</span><span class="detail-value">' + u.id + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Username</span><span class="detail-value"><strong>' + u.userName + '</strong></span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Full Name</span><span class="detail-value">' + u.fullName + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">' + u.email + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Mobile Number</span><span class="detail-value">' + (u.mobileNumber || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Created At</span><span class="detail-value">' + u.createdAt + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Last Login</span><span class="detail-value">' + u.lastLoginAt + '</span></div>';
                        html += '</div>';

                        html += '<div class="detail-section">';
                        html += '<div class="detail-section-title"><i class="fas fa-info-circle"></i>Additional Information</div>';
                        html += '<div class="detail-row"><span class="detail-label">Gender</span><span class="detail-value">' + (u.gender || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Age Range</span><span class="detail-value">' + (u.ageRange || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Primary Device</span><span class="detail-value">' + (u.primaryDeviceType || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Years of Experience</span><span class="detail-value">' + (u.yearsOfExperience || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Area Type</span><span class="detail-value">' + (u.areaType || notProvided) + '</span></div>';
                        html += '</div>';

                        html += '<div class="detail-section">';
                        html += '<div class="detail-section-title"><i class="fas fa-map-marker-alt"></i>Address</div>';
                        html += '<div class="detail-row"><span class="detail-label">Country</span><span class="detail-value">' + (u.country || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Street Address</span><span class="detail-value">' + (u.streetAddress || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Permanent Address</span><span class="detail-value">' + (u.permanentAddress || notProvided) + '</span></div>';
                        html += '</div>';

                        html += '<div class="detail-section">';
                        html += '<div class="detail-section-title"><i class="fas fa-graduation-cap"></i>Education</div>';
                        html += '<div class="detail-row"><span class="detail-label">Education Level</span><span class="detail-value">' + (u.educationLevel || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Currently Studying</span><span class="detail-value">' + (u.currentStudyStatus || 'No') + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Exam/Degree Title</span><span class="detail-value">' + (u.examDegreeTitle || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Institution Name</span><span class="detail-value">' + (u.institutionName || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Passing Year</span><span class="detail-value">' + (u.passingYear || notProvided) + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">CSE/CS Student</span><span class="detail-value">' + (u.isCSEStudent ? 'Yes' : 'No') + '</span></div>';
                        html += '</div>';

                        html += '<div class="detail-section">';
                        html += '<div class="detail-section-title"><i class="fas fa-link"></i>Important Links</div>';
                        var cvLink = u.cvLink ? '<a href="' + u.cvLink + '" target="_blank">View CV <i class="fas fa-external-link-alt"></i></a>' : notProvided;
                        var githubLink = u.githubProfile ? '<a href="' + u.githubProfile + '" target="_blank">View Github <i class="fas fa-external-link-alt"></i></a>' : notProvided;
                        var portfolioLink = u.portfolioLink ? '<a href="' + u.portfolioLink + '" target="_blank">View Portfolio <i class="fas fa-external-link-alt"></i></a>' : notProvided;
                        var linkedinLink = u.linkedInProfile ? '<a href="' + u.linkedInProfile + '" target="_blank">View LinkedIn <i class="fas fa-external-link-alt"></i></a>' : notProvided;
                        var profileImageLink = u.profileImageLink ? '<a href="' + u.profileImageLink + '" target="_blank">View Image <i class="fas fa-external-link-alt"></i></a>' : notProvided;
                        html += '<div class="detail-row"><span class="detail-label">CV Link</span><span class="detail-value">' + cvLink + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Github Profile</span><span class="detail-value">' + githubLink + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Portfolio Link</span><span class="detail-value">' + portfolioLink + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">LinkedIn Profile</span><span class="detail-value">' + linkedinLink + '</span></div>';
                        html += '<div class="detail-row"><span class="detail-label">Profile Image</span><span class="detail-value">' + profileImageLink + '</span></div>';
                        html += '</div>';

                        detailsDiv.innerHTML = html;
                        var modal = new bootstrap.Modal(document.getElementById('userModal'));
                        modal.show();
                    } else {
                        detailsDiv.innerHTML = '<div class="alert alert-danger text-center">User not found</div>';
                    }
                })
                .catch(function (error) {
                    detailsDiv.innerHTML = '<div class="alert alert-danger text-center">Error loading user details</div>';
                });
        }

        // ===== Toggle User Status =====
        function toggleStatus(id, isActive) {
            var action = isActive ? 'activate' : 'deactivate';
            if (!confirm('Are you sure you want to ' + action + ' this user?')) { return; }

            var formData = new FormData();
            formData.append('id', id);
            formData.append('isActive', isActive);

            var token = document.querySelector('input[name="__RequestVerificationToken"]');
            var headers = {};
            if (token) { headers['RequestVerificationToken'] = token.value; }

            fetch('/Admin/ToggleUserStatus', { method: 'POST', body: formData, headers: headers })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    alert(data.message);
                    if (data.success) { location.reload(); }
                })
                .catch(function () { alert('Error toggling user status'); });
        }

        // ===== Delete User =====
        function deleteUser(id) {
            if (!confirm('Delete this user permanently? This action cannot be undone!')) { return; }

            var formData = new FormData();
            formData.append('id', id);
            var token = document.querySelector('input[name="__RequestVerificationToken"]');
            var headers = {};
            if (token) { headers['RequestVerificationToken'] = token.value; }

            fetch('/Admin/DeleteUser', { method: 'POST', body: formData, headers: headers })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    alert(data.message);
                    if (data.success) { location.reload(); }
                })
                .catch(function () { alert('Error deleting user'); });
        }

        // ===== Enter Key Handling in Password Modal =====
        document.addEventListener('DOMContentLoaded', function () {
            var passwordField = document.getElementById('newPassword');
            var confirmField = document.getElementById('confirmNewPassword');
            if (passwordField) {
                passwordField.addEventListener('keypress', function (e) { if (e.key === 'Enter') confirmField.focus(); });
            }
            if (confirmField) {
                confirmField.addEventListener('keypress', function (e) { if (e.key === 'Enter') submitPassword(); });
            }
        });
    </script>
<?php section_end(); ?>