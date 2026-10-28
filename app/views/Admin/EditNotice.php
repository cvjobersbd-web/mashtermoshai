<?php
$ViewData['Title'] = 'Edit Notice';
?>

<style>
    /* ===== Main Content Layout ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== Form Card ===== */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .form-label {
        font-weight: 700;
        color: #08224A;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 1.5px solid #DCECF5;
        padding: 11px 14px;
        font-size: 14px;
        background: #F5FBFF;
        transition: all 0.2s;
    }

        .form-control:focus,
        .form-select:focus {
            border-color: #02B8F5;
            box-shadow: 0 0 0 3px rgba(2,184,245,0.1);
            background: white;
        }

        .form-control::placeholder {
            color: #A0B3C6;
        }

    .btn-save {
        background: #02B8F5;
        color: white;
        padding: 12px 40px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

        .btn-save:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2,184,245,0.22);
            color: white;
        }

    .btn-cancel {
        background: white;
        color: #64748B;
        padding: 12px 32px;
        border-radius: 10px;
        border: 1.5px solid #DCECF5;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

        .btn-cancel:hover {
            background: #F5FBFF;
            color: #08224A;
            border-color: #02B8F5;
        }

    .upload-btn {
        display: block;
        width: 100%;
        padding: 11px;
        background-color: #F5FBFF;
        color: #08224A;
        border: 1.5px dashed #02B8F5;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

        .upload-btn:hover {
            background-color: #DCECF5;
        }

    .req {
        color: #e74c3c;
    }

    .info-note {
        font-size: 12px;
        color: #7A8CA0;
        margin-top: 6px;
        display: block;
    }

        .info-note i {
            color: #028EA5;
            margin-right: 4px;
        }

    /* ===== Type Selector Cards ===== */
    .type-card {
        border: 2px solid #DCECF5;
        border-radius: 12px;
        padding: 16px 18px;
        cursor: pointer;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        gap: 14px;
        background: #F5FBFF;
        user-select: none;
    }

        .type-card:hover {
            border-color: #02B8F5;
            transform: translateY(-2px);
        }

        .type-card.selected {
            border-color: #02B8F5;
            background: #CFF2FC;
            box-shadow: 0 4px 16px rgba(2,184,245,0.15);
        }

        .type-card .tc-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .type-card.news .tc-icon {
            background: #CFF2FC;
            color: #08224A;
        }

        .type-card.announcement .tc-icon {
            background: #FFEBB8;
            color: #8a5a00;
        }

        .type-card .tc-text h6 {
            font-size: 14px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 2px;
        }

        .type-card .tc-text p {
            font-size: 12px;
            color: #64748B;
            margin: 0;
        }

        .type-card .tc-check {
            margin-left: auto;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #B9D6E6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .type-card.selected .tc-check {
            background: #02B8F5;
            border-color: #02B8F5;
        }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }
    }

    @media (max-width: 576px) {
        .type-card {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }

            .type-card .tc-check {
                margin-left: 0;
            }
    }
</style>

<!-- ===== Admin Sidebar (Partial) ===== -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ===== Main Content ===== -->
<div class="admin-main-content">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color:#08224A; margin:0;"><i class="fas fa-edit me-2"></i>Edit Notice</h2>
            <p style="color:#64748B; margin:4px 0 0 0; font-size:14px;">Notice #<?= e($Model->Id) ?> - <?= e($Model->Title) ?></p>
        </div>
        <a href="/Admin/ManageNotices" class="btn-cancel">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <?php if ($ViewBag->Error !== null): ?>
        <div class="alert alert-danger" style="border-radius:10px;">
            <i class="fas fa-exclamation-circle me-2"></i><?= e($ViewBag->Error) ?>
        </div>
    <?php endif; ?>

    <div class="form-card p-4">
        <form method="post" action="/Admin/EditNotice" id="editNoticeForm">
            <?= csrf_field() ?>
            <input type="hidden" name="Id" value="<?= e($Model->Id) ?>" />
            <input type="hidden" name="CreatedAt" value="<?= e(fmt_date($Model->CreatedAt, 'c')) ?>" />

            <!-- ===== Type Selector (News vs Announcement) ===== -->
            <div class="mb-4">
                <label class="form-label">What are you posting? <span class="req">*</span></label>

                <input type="hidden" name="IsAnnouncement" id="isAnnouncementHidden" value="<?= $Model->IsAnnouncement ? 'true' : 'false' ?>" />

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="type-card news <?= $Model->IsAnnouncement ? '' : 'selected' ?>" id="cardNews" onclick="selectType('news')">
                            <div class="tc-icon"><i class="fas fa-newspaper"></i></div>
                            <div class="tc-text">
                                <h6>News</h6>
                                <p>Show in "Latest News" grid (left)</p>
                            </div>
                            <div class="tc-check"><i class="fas fa-check"></i></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="type-card announcement <?= $Model->IsAnnouncement ? 'selected' : '' ?>" id="cardAnnouncement" onclick="selectType('announcement')">
                            <div class="tc-icon"><i class="fas fa-bullhorn"></i></div>
                            <div class="tc-text">
                                <h6>Announcement</h6>
                                <p>Show in "Announcements" sidebar (right)</p>
                            </div>
                            <div class="tc-check"><i class="fas fa-check"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Title + Category ===== -->
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Title <span class="req">*</span></label>
                    <input name="Title" value="<?= e($Model->Title) ?>" class="form-control" placeholder="Enter notice title..." required />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category <span class="req">*</span></label>
                    <select name="Category" class="form-select" required>
                        <option value="News"<?= ($Model->Category ?? '') === 'News' ? ' selected="selected"' : '' ?>>News</option>
                        <option value="Workshop"<?= ($Model->Category ?? '') === 'Workshop' ? ' selected="selected"' : '' ?>>Workshop</option>
                        <option value="Course"<?= ($Model->Category ?? '') === 'Course' ? ' selected="selected"' : '' ?>>Course</option>
                        <option value="Event"<?= ($Model->Category ?? '') === 'Event' ? ' selected="selected"' : '' ?>>Event</option>
                        <option value="Result"<?= ($Model->Category ?? '') === 'Result' ? ' selected="selected"' : '' ?>>Result</option>
                    </select>
                </div>
            </div>

            <!-- ===== Content ===== -->
            <div class="mb-3">
                <label class="form-label">Content <span class="req">*</span></label>
                <textarea name="Content" class="form-control" rows="10" placeholder="Write full content here..." required><?= e($Model->Content) ?></textarea>
                <span class="info-note">
                    <i class="fas fa-info-circle"></i>
                    এখানে যা লিখবেন সেটাই Details পেজে দেখাবে।
                </span>
            </div>

            <!-- ===== Image Section ===== -->
            <div class="mb-3" id="imageSection">
                <label class="form-label">Image <span style="color:#7A8CA0; font-weight:400;">(Optional)</span></label>

                <div class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <input name="ImageUrl" id="imageUrlInput" value="<?= e($Model->ImageUrl) ?>" class="form-control" placeholder="https://example.com/image.jpg" />
                    </div>
                    <div class="col-md-4">
                        <label for="imageFileInput" class="upload-btn">
                            <i class="fas fa-upload me-1"></i> Upload from Device
                        </label>
                        <input type="file" id="imageFileInput" accept="image/*" style="display:none;" />
                    </div>
                </div>

                <span class="info-note">
                    <i class="fas fa-info-circle"></i>
                    Direct image link দিন অথবা ডিভাইস থেকে ছবি আপলোড করুন (Max 5MB)
                </span>

                <div id="imagePreviewWrapper" class="mt-3" style="<?= is_blank($Model->ImageUrl) ? 'display:none;' : '' ?>">
                    <p class="mb-1 fw-bold" style="font-size:12px; color:#08224A;">Preview:</p>
                    <img id="imagePreview" src="<?= e($Model->ImageUrl) ?>" alt="Preview"
                         style="max-width: 240px; max-height: 160px; border-radius: 10px; border: 2px solid #DCECF5; object-fit: cover;" />
                </div>
            </div>

            <!-- ===== Published Date + Active ===== -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Published Date <span class="req">*</span></label>
                    <input name="PublishedDate" type="date" value="<?= e(fmt_date($Model->PublishedDate, 'Y-m-d')) ?>" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">Active</label>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="IsActive" value="false" />
                        <input class="form-check-input" type="checkbox" name="IsActive" value="true" id="isActive" <?= $Model->IsActive ? 'checked' : '' ?> />
                        <label class="form-check-label" for="isActive" style="font-size:13px; color:#64748B;">Visible to users</label>
                    </div>
                </div>
            </div>

            <!-- ===== Action Buttons ===== -->
            <div class="d-flex gap-3 mt-4 flex-wrap">
                <a href="/Admin/ManageNotices" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn-save" id="submitBtn">
                    <i class="fas fa-save"></i> Update Notice
                </button>
            </div>
        </form>
    </div>
</div>

<?php section_start('Scripts'); ?>
    <script>
        // ===== Type Selector (News vs Announcement) =====
        function selectType(type) {
            const hidden = document.getElementById('isAnnouncementHidden');
            const cardNews = document.getElementById('cardNews');
            const cardAnnouncement = document.getElementById('cardAnnouncement');
            const imageSection = document.getElementById('imageSection');
            const imageUrlInput = document.getElementById('imageUrlInput');
            const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');
            const isActiveToggle = document.getElementById('isActive');

            if (type === 'announcement') {
                hidden.value = 'true';
                cardAnnouncement.classList.add('selected');
                cardNews.classList.remove('selected');

                // Announcement হলে ছবি লাগবে না
                if (imageSection) imageSection.style.display = 'none';
                if (imageUrlInput) imageUrlInput.value = '';
                if (imagePreviewWrapper) imagePreviewWrapper.style.display = 'none';

                // Active টগল auto-on
                if (isActiveToggle) isActiveToggle.checked = true;
            } else {
                hidden.value = 'false';
                cardNews.classList.add('selected');
                cardAnnouncement.classList.remove('selected');

                if (imageSection) imageSection.style.display = 'block';
            }

            console.log('✅ Type selected:', type, '| IsAnnouncement =', hidden.value);
        }

        // ===== Form Submit-এর আগে ভ্যালু নিশ্চিত করা =====
        document.getElementById('editNoticeForm').addEventListener('submit', function (e) {
            const hidden = document.getElementById('isAnnouncementHidden');
            const cardAnnouncement = document.getElementById('cardAnnouncement');
            const isActiveToggle = document.getElementById('isActive');

            if (cardAnnouncement.classList.contains('selected')) {
                hidden.value = 'true';
                if (isActiveToggle) isActiveToggle.checked = true;
            } else {
                hidden.value = 'false';
            }

            console.log('📤 Submitting with IsAnnouncement =', hidden.value, '| IsActive =', isActiveToggle?.checked);

            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
            }
        });

        // ===== পেজ লোড হওয়ার সময় Announcement হলে Image সেকশন hide =====
        document.addEventListener('DOMContentLoaded', function () {
            const isAnnouncement = '<?= $Model->IsAnnouncement ? 'true' : 'false' ?>' === 'true';
            if (isAnnouncement) {
                const imageSection = document.getElementById('imageSection');
                if (imageSection) imageSection.style.display = 'none';
                console.log('ℹ️ Announcement detected — Image section hidden');
            }
        });

        // ===== Image URL + Upload handling =====
        const imageUrlInput = document.getElementById('imageUrlInput');
        const imageFileInput = document.getElementById('imageFileInput');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');

        imageUrlInput?.addEventListener('input', function () {
            const url = this.value.trim();
            if (url) {
                imagePreview.src = url;
                imagePreviewWrapper.style.display = 'block';
            } else {
                imagePreviewWrapper.style.display = 'none';
            }
        });

        imageFileInput?.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG, GIF, or WEBP images are allowed.');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must be less than 5MB.');
                this.value = '';
                return;
            }

            // Local preview
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreviewWrapper.style.display = 'block';
            };
            reader.readAsDataURL(file);

            // Upload to server
            const formData = new FormData();
            formData.append('noticeImage', file);

            const token = document.querySelector('input[name="__RequestVerificationToken"]');
            const headers = {};
            if (token) headers['RequestVerificationToken'] = token.value;

            const uploadLabel = document.querySelector('label[for="imageFileInput"]');
            const originalHtml = uploadLabel.innerHTML;
            uploadLabel.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading...';
            uploadLabel.style.pointerEvents = 'none';

            fetch('/Admin/UploadNoticeImage', {
                method: 'POST',
                body: formData,
                headers: headers
            })
                .then(r => r.json())
                .then(data => {
                    uploadLabel.innerHTML = originalHtml;
                    uploadLabel.style.pointerEvents = 'auto';

                    if (data.success) {
                        imageUrlInput.value = data.imageUrl;
                        imagePreview.src = data.imageUrl;
                        imagePreviewWrapper.style.display = 'block';
                    } else {
                        alert(data.message || 'Failed to upload image.');
                        imageFileInput.value = '';
                    }
                })
                .catch(err => {
                    uploadLabel.innerHTML = originalHtml;
                    uploadLabel.style.pointerEvents = 'auto';
                    alert('Error uploading image: ' + err.message);
                    imageFileInput.value = '';
                });
        });
    </script>
<?php section_end(); ?>