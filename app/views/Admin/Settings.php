<?php
$ViewData['Title'] = 'Site Settings';
$heroImages = $Model->GetHeroImageList();

// ===== Settings এর সাব-মেনু (প্রতিটা গ্রুপ আলাদা পেজের মতো দেখায়; সব একই পেজে থাকে, তাই সেভ আগের মতোই কাজ করে) =====
$sgGroups = admin_settings_groups();
$sgCur = (string)($ViewBag->Section ?? '');
if (!isset($sgGroups[$sgCur])) $sgCur = '';
?>

<style>
    /* ===== Main Content Layout ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== Settings Cards ===== */
    .settings-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        margin-bottom: 22px;
    }

        .settings-card h5 {
            color: #08224A;
            font-weight: 700;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

            .settings-card h5 i {
                color: #028EA5;
            }

    /* ===== Forms ===== */
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

    /* ===== Image Gallery ===== */
    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .image-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #DCECF5;
        aspect-ratio: 16/10;
        background: #F5FBFF;
        transition: all 0.25s;
    }

        .image-item:hover {
            border-color: #02B8F5;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-item .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(231,76,60,0.95);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

            .image-item .remove-btn:hover {
                background: #c0392b;
                transform: scale(1.1);
            }

        .image-item .primary-badge {
            position: absolute;
            bottom: 8px;
            left: 8px;
            background: #2ecc71;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

    /* ===== Upload Button ===== */
    .upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #02B8F5;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        white-space: nowrap;
    }

        .upload-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2,184,245,0.22);
            color: white;
        }

    /* ===== Save Button ===== */
    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 40px;
        background: #02B8F5;
        color: white;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

        .btn-save:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(2,184,245,0.25);
            color: white;
        }

        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

    /* ===== Toggle Switch ===== */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
        flex-shrink: 0;
    }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.3s;
        border-radius: 34px;
    }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }

    input:checked + .toggle-slider {
        background-color: #2ecc71;
    }

        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

    /* ===== Color Picker ===== */
    .color-picker-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
        padding: 12px 16px;
        background: #F5FBFF;
        border-radius: 10px;
        border: 1.5px solid #DCECF5;
        transition: border-color 0.2s;
    }

        .color-picker-row:hover {
            border-color: #02B8F5;
        }

        .color-picker-row label {
            flex: 1;
            font-weight: 600;
            color: #08224A;
            font-size: 13.5px;
            margin: 0;
        }

        .color-picker-row input[type="color"] {
            width: 50px;
            height: 40px;
            border: 2px solid #02B8F5;
            border-radius: 8px;
            cursor: pointer;
            background: transparent;
            padding: 2px;
        }

        .color-picker-row input[type="text"] {
            width: 110px;
            padding: 8px 12px;
            border: 1.5px solid #DCECF5;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            background: white;
            outline: none;
            font-weight: 600;
            color: #08224A;
            text-transform: uppercase;
        }

            .color-picker-row input[type="text"]:focus {
                border-color: #02B8F5;
                box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
            }

    /* ===== Info Note ===== */
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

    /* ===== Empty State ===== */
    .empty-gallery {
        grid-column: 1/-1;
        text-align: center;
        padding: 50px 20px;
        color: #7A8CA0;
        background: #F5FBFF;
        border: 2px dashed #DCECF5;
        border-radius: 12px;
    }

        .empty-gallery i {
            font-size: 48px;
            color: #DCECF5;
            display: block;
            margin-bottom: 12px;
        }

        .empty-gallery p {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
        }

    /* ===== Preview Color Chips ===== */
    .theme-preview {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding: 16px;
        background: #F5FBFF;
        border-radius: 12px;
        border: 1.5px dashed #DCECF5;
    }

    .theme-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

        .theme-chip .chip-color {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            border: 2px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            transition: transform 0.25s;
        }

            .theme-chip .chip-color:hover {
                transform: scale(1.08);
            }

        .theme-chip .chip-label {
            font-size: 11px;
            color: #64748B;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

    /* ===== ✅ NEW: Logo Shape Cards ===== */
    .shape-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 14px 10px;
        border: 2px solid #DCECF5;
        border-radius: 12px;
        background: #F5FBFF;
        cursor: pointer;
        transition: all 0.22s;
        font-size: 12.5px;
        font-weight: 600;
        color: #08224A;
        margin: 0;
    }

        .shape-card:hover {
            border-color: #02B8F5;
            transform: translateY(-2px);
        }

        .shape-card input[type="radio"] {
            display: none;
        }

        .shape-card:has(input:checked) {
            border-color: #02B8F5;
            background: #CFF2FC;
            box-shadow: 0 4px 14px rgba(2,184,245,0.15);
        }

    .shape-visual {
        width: 40px;
        height: 40px;
        background: #02B8F5;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    /* ===== ✅ NEW: Logo Preview Boxes ===== */
    .logo-preview-box {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #F5FBFF;
        border-radius: 10px;
        border: 1.5px solid #DCECF5;
        transition: all 0.2s;
        min-height: 70px;
    }

        .logo-preview-box.footer-style {
            background: #F5FBFF;
        }

    .logo-preview-img {
        object-fit: cover;
        background: white;
        flex-shrink: 0;
        transition: all 0.25s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .logo-preview-name {
        font-weight: 700;
        color: #08224A;
        line-height: 1.2;
    }

    /* ===== Range Slider ===== */
    .form-range {
        width: 100%;
        height: 6px;
        border-radius: 5px;
        background: #DCECF5;
        outline: none;
        -webkit-appearance: none;
    }

        .form-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #02B8F5;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        .form-range::-moz-range-thumb {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #02B8F5;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }

        .image-gallery {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        }
    }

    @media (max-width: 576px) {
        .settings-card {
            padding: 18px 16px;
        }

        .color-picker-row {
            flex-wrap: wrap;
        }

            .color-picker-row input[type="text"] {
                width: 100%;
            }

        .btn-save {
            width: 100%;
            justify-content: center;
        }

        .logo-preview-box {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* ===== Become a Tutor preview ===== */
    .bt-prev-wrap { margin-top: 18px; border: 1px dashed #B9D6E6; border-radius: 16px; padding: 14px; background: #F5FBFF; }
    .bt-prev-label { font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #028EA5; margin-bottom: 10px; }
    .bt-prev { display: grid; grid-template-columns: 1.1fr 1px 1.2fr 1px .9fr; gap: 18px; align-items: center;
        background: #fff; border: 1px solid #DCECF5; border-radius: 16px; padding: 18px 20px; }
    .bt-prev-div { width: 1px; align-self: stretch; background: #DCECF5; }
    .bt-prev-badge { display: inline-block; background: #CFF2FC; color: #028EA5; font-size: 10px; font-weight: 800; letter-spacing: 1px;
        text-transform: uppercase; padding: 3px 10px; border-radius: 20px; margin-bottom: 6px; }
    .bt-prev-badge:empty { display: none; }
    .bt-prev-kicker { font-size: 14px; color: #334155; }
    .bt-prev-big { font-family: var(--font-heading); font-weight: 800; font-size: 38px; line-height: 1; color: #08224A; }
    .bt-prev-big .hl { color: #02B8F5; }
    .bt-prev-tag { font-size: 16px; font-weight: 600; color: #08224A; line-height: 1.5; }
    .bt-prev-tag .l2 { display: block; padding-left: 40px; color: #028EA5; }
    .bt-prev-tag .hl { background: linear-gradient(transparent 60%, rgba(255,179,0,.4) 60%); }
    .bt-prev-perks { list-style: none; padding: 0; margin: 8px 0 0; display: flex; flex-wrap: wrap; gap: 4px 12px; font-size: 12px; color: #64748B; }
    .bt-prev-perks li::before { content: '✔'; color: #2ecc71; margin-right: 4px; }
    .bt-prev-btn { display: inline-flex; align-items: center; gap: 6px; background: #02B8F5; color: #fff; padding: 9px 18px; border-radius: 10px; font-weight: 700; font-size: 13px; }
    .bt-prev-off { display: none; text-align: center; color: #7A8CA0; font-size: 13px; padding: 10px; }
    .bt-prev-wrap.off .bt-prev { opacity: .35; filter: grayscale(1); }
    .bt-prev-wrap.off .bt-prev-off { display: block; }
    @media (max-width: 768px) {
        .bt-prev { grid-template-columns: 1fr; text-align: center; }
        .bt-prev-div { width: 50%; height: 1px; justify-self: center; }
        .bt-prev-tag .l2 { padding-left: 0; }
        .bt-prev-perks { justify-content: center; }
    }
    .tcb-prev-wrap { max-width: 760px; }
    .opt-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; border-bottom: 1px solid #E8EEF5; }
    .opt-tabs button { border: none; background: none; padding: 10px 14px; font-weight: 600; color: #64748B; border-bottom: 3px solid transparent; margin-bottom: -1px; cursor: pointer; }
    .opt-tabs button.active { color: #08224A; border-bottom-color: #02B8F5; }
    .opt-tabs span { font-size: 11.5px; background: #EEF3F9; color: #334155; padding: 1px 7px; border-radius: 9px; margin-left: 4px; }
    .opt-add { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
    .opt-add .form-control { flex: 1; min-width: 180px; }
    .opt-add .btn-save { white-space: nowrap; }
    .opt-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .opt-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 6px 5px 12px; border-radius: 16px; background: #EEF3F9; color: #1E293B; font-size: 13.5px; }
    .opt-chip button { width: 22px; height: 22px; border-radius: 50%; border: none; background: #fff; color: #64748B; cursor: pointer; font-size: 11px; line-height: 1; }
    .opt-chip button:hover { background: #FEE2E2; color: #DC2626; }
    .opt-locs { max-height: 460px; overflow-y: auto; border: 1px solid #E8EEF5; border-radius: 12px; }
    .opt-d { padding: 10px 14px; border-top: 1px solid #F1F5F9; }
    .opt-d:first-child { border-top: none; }
    .opt-d-h { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #08224A; font-size: 14.5px; }
    .opt-d-h small { font-weight: 500; color: #94A3B8; }
    .opt-d-h .del { margin-left: auto; border: none; background: none; color: #94A3B8; cursor: pointer; font-size: 13px; }
    .opt-d-h .del:hover { color: #DC2626; }
    .opt-d .opt-chips { margin-top: 8px; }
    .opt-d .opt-chip { font-size: 12.5px; padding: 3px 5px 3px 10px; }
    .pp-grid { display: grid; grid-template-columns: 240px 1fr; gap: 22px; align-items: start; }
    .pp-prev-label { font-size: 12px; font-weight: 700; color: #08224A; margin-bottom: 8px; }
    .pp-prev { display: block; border-radius: 16px; overflow: hidden; border: 1px solid #E8EEF5; background: #F4F7FB; box-shadow: 0 4px 16px rgba(15,27,45,.06); }
    .pp-prev img { display: block; width: 100%; height: auto; }
    .pp-prev-off { margin-top: 8px; font-size: 12.5px; color: #B45309; background: #FFF7E0; padding: 6px 10px; border-radius: 8px; }
    @media (max-width: 640px) { .pp-grid { grid-template-columns: 1fr; } .pp-prev-wrap { max-width: 260px; } }
</style>

<!-- ===== Admin Sidebar (Partial) ===== -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ===== Main Content ===== -->
<div class="admin-main-content" id="sgRoot" data-sg-cur="<?= e($sgCur) ?>">

    <!-- ===== Page Header ===== -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color:#08224A; margin:0;"><i class="fas fa-cog me-2"></i>Site Settings</h2>
            <p style="color:#64748B; margin:4px 0 0 0; font-size:14px;">
                ওয়েবসাইটের লোগো, রঙ, হোম পেজ, ব্যানার, ইমেইল — সব সেটিংস এক জায়গায়, ভাগ ভাগ করে
            </p>
        </div>
    </div>

    <?php if ($TempData['Success'] !== null): ?>
        <div class="alert alert-success" style="border-radius:10px;">
            <i class="fas fa-check-circle me-2"></i><?= e($TempData['Success']) ?>
        </div>
    <?php endif; ?>

    <?= csrf_field() ?>
    <input type="hidden" id="settingsId" value="<?= e($Model->Id) ?>" />

    <!-- ===== Settings সাব-মেনু ===== -->
    <style>
        <?php foreach (array_keys($sgGroups) as $g): ?>#sgRoot[data-sg-cur="<?= $g ?>"] [data-sg]:not([data-sg~="<?= $g ?>"]),
        <?php endforeach; ?>#sgRoot[data-sg-cur=""] [data-sg] { display: none !important; }
        #sgRoot:not([data-sg-cur=""]) .sg-overview { display: none; }
        #sgRoot[data-sg-cur=""] .sg-tabs, #sgRoot[data-sg-cur=""] .sg-intro { display: none; }
        .sg-overview { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px; margin-bottom: 30px; }
        .sg-tile { display: flex; gap: 14px; align-items: flex-start; background: #fff; border: 1.5px solid #E3ECF5; border-radius: 16px; padding: 18px; text-decoration: none; color: inherit;
            box-shadow: 0 4px 16px rgba(8,34,74,.05); transition: transform .2s, box-shadow .2s, border-color .2s; }
        .sg-tile:hover { transform: translateY(-3px); box-shadow: 0 14px 30px rgba(8,34,74,.1); border-color: #9FDDF5; color: inherit; }
        .sg-tile .ic { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .sg-tile b { display: block; color: #08224A; font-size: 16px; margin-bottom: 3px; }
        .sg-tile small { display: block; color: #64748B; font-size: 13px; line-height: 1.5; }
        .sg-tile .ext { font-size: 11px; color: #94A3B8; margin-left: 4px; }
        .sg-ov-h { font-size: 13px; font-weight: 800; color: #94A3B8; letter-spacing: .5px; text-transform: uppercase; margin: 6px 0 12px; grid-column: 1 / -1; }
        .sg-tabs { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; background: #fff; border: 1px solid #E3ECF5; border-radius: 14px; padding: 6px; margin-bottom: 18px;
            position: sticky; top: 86px; z-index: 50; box-shadow: 0 4px 16px rgba(8,34,74,.06); }
        .sg-tabs::-webkit-scrollbar { display: none; }
        .sg-tabs a { flex-shrink: 0; display: inline-flex; gap: 8px; align-items: center; padding: 9px 14px; border-radius: 10px; color: #475569; font-weight: 700; font-size: 13.5px; text-decoration: none; white-space: nowrap; }
        .sg-tabs a:hover { background: #F1F5F9; color: #08224A; }
        .sg-tabs a.on { background: #08224A; color: #fff; }
        .sg-tabs a.all { color: #1D63E0; }
        .sg-intro { display: flex; gap: 14px; align-items: center; background: linear-gradient(135deg, #F0F9FF, #fff); border: 1px solid #DCECF5; border-radius: 16px; padding: 16px 18px; margin-bottom: 18px; }
        .sg-intro .ic { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
        .sg-intro h3 { margin: 0; font-size: 19px; color: #08224A; font-weight: 800; }
        .sg-intro p { margin: 2px 0 0; color: #64748B; font-size: 13.5px; }
        @media (max-width: 576px) { .sg-overview { grid-template-columns: 1fr; } .sg-tabs { top: 70px; } }
    </style>

    <div class="sg-overview" aria-label="Settings">
        <div class="sg-ov-h">ওয়েবসাইট সেটিংস — যেটা বদলাতে চান সেটায় ক্লিক করুন</div>
        <?php foreach ($sgGroups as $k => $g): ?>
            <a class="sg-tile" href="/Admin/Settings?s=<?= $k ?>" data-sg-go="<?= $k ?>">
                <span class="ic" style="background:<?= $g['bg'] ?>;color:<?= $g['fg'] ?>;"><i class="fas <?= $g['icon'] ?>"></i></span>
                <span><b><?= e($g['title']) ?></b><small><?= e($g['desc']) ?></small></span>
            </a>
        <?php endforeach; ?>
        <div class="sg-ov-h" style="margin-top:14px;">অন্যান্য পেজ</div>
        <?php foreach ([['/Admin/Categories', 'fa-layer-group', '#EEF2FF', '#4F46E5', 'Categories', 'ক্যাটাগরি, ছবি আর ক্যাটাগরি পেজের লেখা'],
                        ['/Admin/Packages', 'fa-box-open', '#FFF4D6', '#B7791F', 'Packages', 'শিক্ষক ও শিক্ষার্থীর প্যাকেজ আর দাম'],
                        ['/Admin/PaymentMethods', 'fa-wallet', '#E7F8EF', '#157347', 'Payment Methods', 'bKash, Nagad ইত্যাদি পেমেন্টের নিয়ম'],
                        ['/Admin/EduFeed', 'fa-graduation-cap', '#E0F2FE', '#0369A1', 'Educational Feed', 'ইউজারদের পোস্ট Approve / Reject']] as [$h, $ic, $bg, $fg, $t, $d]): ?>
            <?php if (!admin_can(admin_action_rule(basename($h)))) continue; ?>
            <a class="sg-tile" href="<?= $h ?>">
                <span class="ic" style="background:<?= $bg ?>;color:<?= $fg ?>;"><i class="fas <?= $ic ?>"></i></span>
                <span><b><?= e($t) ?></b><small><?= e($d) ?></small></span>
            </a>
        <?php endforeach; ?>
    </div>

    <nav class="sg-tabs" aria-label="Settings sections">
        <a href="/Admin/Settings" class="all" data-sg-go=""><i class="fas fa-grip"></i> সব সেটিংস</a>
        <?php foreach ($sgGroups as $k => $g): ?><a href="/Admin/Settings?s=<?= $k ?>" data-sg-go="<?= $k ?>" class="<?= $sgCur === $k ? 'on' : '' ?>"><i class="fas <?= $g['icon'] ?>"></i> <?= e($g['short']) ?></a><?php endforeach; ?>
    </nav>
    <div class="sg-intro">
        <span class="ic" id="sgIntroIc" style="background:<?= $sgGroups[$sgCur]['bg'] ?? '#E0F2FE' ?>;color:<?= $sgGroups[$sgCur]['fg'] ?? '#0369A1' ?>;"><i class="fas <?= $sgGroups[$sgCur]['icon'] ?? 'fa-cog' ?>"></i></span>
        <div><h3 id="sgIntroT"><?= e($sgGroups[$sgCur]['title'] ?? '') ?></h3><p id="sgIntroD"><?= e($sgGroups[$sgCur]['desc'] ?? '') ?></p></div>
    </div>
    <script>
    (function () {
        var G = <?= js($sgGroups) ?>, root = document.getElementById('sgRoot');
        function go(k, push) {
            if (!(k in G)) k = '';
            root.setAttribute('data-sg-cur', k);
            document.querySelectorAll('.sg-tabs a[data-sg-go]').forEach(function (a) { a.classList.toggle('on', a.getAttribute('data-sg-go') === k && k !== ''); });
            document.querySelectorAll('[data-sg-side]').forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-sg-side') === k); });
            if (k) {
                var g = G[k], ic = document.getElementById('sgIntroIc');
                ic.style.background = g.bg; ic.style.color = g.fg; ic.innerHTML = '<i class="fas ' + g.icon + '"></i>';
                document.getElementById('sgIntroT').textContent = g.title; document.getElementById('sgIntroD').textContent = g.desc;
            }
            if (push) history.pushState({ sg: k }, '', '/Admin/Settings' + (k ? '?s=' + k : ''));
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        document.addEventListener('click', function (e) {
            var a = e.target.closest('[data-sg-go], [data-sg-side]'); if (!a || e.ctrlKey || e.metaKey || e.shiftKey) return;
            e.preventDefault();
            go(a.getAttribute(a.hasAttribute('data-sg-go') ? 'data-sg-go' : 'data-sg-side'), true);
            document.documentElement.classList.remove('a-open');
        });
        window.addEventListener('popstate', function () { go(new URLSearchParams(location.search).get('s') || '', false); });
    })();
    </script>

    <!-- ============================================================ -->
    <!-- ===== ✅ NEW: BRANDING & LOGO SECTION ===== -->
    <!-- ============================================================ -->
    <div class="settings-card" data-sg="general">
        <h5><i class="fas fa-fingerprint"></i> Branding &amp; Logo</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:16px;">
            সাইটের নাম এবং নেভবার/ফুটারের লোগো কাস্টমাইজ করুন। লোগোর সাইজ ও শেপও পরিবর্তন করা যাবে।
        </p>

        <div class="row">
            <!-- Site Name -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Site Name</label>
                <input type="text" id="siteNameInput" class="form-control"
                       value="<?= e($Model->SiteName) ?>" maxlength="100"
                       placeholder="e.g. Mashter Moshai" />
                <span class="info-note">
                    <i class="fas fa-info-circle"></i> নেভবারের পাশে ও ফুটারে দেখাবে
                </span>
            </div>

            <!-- Logo Size -->
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    Logo Size: <span id="logoSizeLabel" style="color:#08224A; font-weight:800;"><?= e($Model->LogoSizePx) ?> px</span>
                </label>
                <input type="range" id="logoSizeRange" min="24" max="100" step="2"
                       value="<?= e($Model->LogoSizePx) ?>" class="form-range"
                       oninput="document.getElementById('logoSizeLabel').textContent = this.value + ' px'; updateLogoPreview();" />
                <span class="info-note">
                    <i class="fas fa-info-circle"></i> 24–100 px এর মধ্যে
                </span>
            </div>
        </div>

        <!-- Logo Shape -->
        <div class="mb-3">
            <label class="form-label">Logo Shape</label>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="shape-card">
                        <input type="radio" name="logoShape" value="circle" <?= $Model->LogoShape === 'circle' ? 'checked' : '' ?> onchange="updateLogoPreview()" />
                        <div class="shape-visual" style="border-radius:50%;">
                            <i class="fas fa-circle"></i>
                        </div>
                        <span>Circle</span>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="shape-card">
                        <input type="radio" name="logoShape" value="rounded" <?= $Model->LogoShape === 'rounded' ? 'checked' : '' ?> onchange="updateLogoPreview()" />
                        <div class="shape-visual" style="border-radius:16px;">
                            <i class="fas fa-square"></i>
                        </div>
                        <span>Rounded</span>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="shape-card">
                        <input type="radio" name="logoShape" value="square" <?= $Model->LogoShape === 'square' ? 'checked' : '' ?> onchange="updateLogoPreview()" />
                        <div class="shape-visual" style="border-radius:0;">
                            <i class="fas fa-square"></i>
                        </div>
                        <span>Square</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Navbar Logo -->
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Navbar Logo</label>
                <div class="d-flex gap-2 mb-2 flex-wrap">
                    <label for="navbarLogoFile" class="upload-btn">
                        <i class="fas fa-upload"></i> Upload
                    </label>
                    <input type="file" id="navbarLogoFile" accept="image/*" style="display:none;" />
                    <input type="text" id="navbarLogoUrlInput" class="form-control"
                           value="<?= e($Model->NavbarLogoUrl) ?>"
                           placeholder="https://... or /uploads/logos/..." style="flex:1; min-width:150px;" />
                </div>
                <span class="info-note">
                    <i class="fas fa-info-circle"></i> Max 3MB. JPG / PNG / WEBP / SVG.
                </span>
                <div style="margin-top:12px;">
                    <p style="font-size:12px; color:#08224A; font-weight:700; margin-bottom:6px;">Preview (Navbar):</p>
                    <div class="logo-preview-box" id="navbarPreviewBox">
                        <img id="navbarLogoPreview" src="<?= e($Model->NavbarLogoUrl) ?>" alt="Navbar Logo"
                             class="logo-preview-img"
                             style="width:<?= e($Model->LogoSizePx) ?>px; height:<?= e($Model->LogoSizePx) ?>px;
                                    border-radius:<?= e($Model->LogoShape === 'circle' ? '50%' : ($Model->LogoShape === 'rounded' ? '16px' : '0')) ?>;" />
                        <span id="navbarSiteNamePreview" class="logo-preview-name" style="font-size:15px;"><?= e($Model->SiteName) ?></span>
                    </div>
                </div>
            </div>

            <!-- Footer Logo -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Footer Logo</label>
                <div class="d-flex gap-2 mb-2 flex-wrap">
                    <label for="footerLogoFile" class="upload-btn">
                        <i class="fas fa-upload"></i> Upload
                    </label>
                    <input type="file" id="footerLogoFile" accept="image/*" style="display:none;" />
                    <input type="text" id="footerLogoUrlInput" class="form-control"
                           value="<?= e($Model->FooterLogoUrl) ?>"
                           placeholder="https://... or /uploads/logos/..." style="flex:1; min-width:150px;" />
                </div>
                <span class="info-note">
                    <i class="fas fa-info-circle"></i> Max 3MB. JPG / PNG / WEBP / SVG.
                </span>
                <div style="margin-top:12px;">
                    <p style="font-size:12px; color:#08224A; font-weight:700; margin-bottom:6px;">Preview (Footer):</p>
                    <div class="logo-preview-box footer-style" id="footerPreviewBox">
                        <img id="footerLogoPreview" src="<?= e($Model->FooterLogoUrl) ?>" alt="Footer Logo"
                             class="logo-preview-img"
                             style="width:<?= e($Model->LogoSizePx) ?>px; height:<?= e($Model->LogoSizePx) ?>px;
                                    border-radius:<?= e($Model->LogoShape === 'circle' ? '50%' : ($Model->LogoShape === 'rounded' ? '16px' : '0')) ?>;" />
                        <div>
                            <div id="footerSiteNamePreview" class="logo-preview-name" style="font-size:16px;"><?= e($Model->SiteName) ?></div>
                            <div style="font-size:12px; color:#64748B;">Personalized learning experiences...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== ✅ HOME — সেকশন চালু / বন্ধ ===== -->
    <!-- ============================================================ -->
    <?php
        $hsOff = function_exists('home_sections_off') ? home_sections_off() : [];
        $hsList = function_exists('home_section_list') ? home_section_list() : [];
        $hcGroups = []; try { $hcGroups = db()->GetCategoryGroups(false); } catch (Throwable $e) { }
        $hcPicked = function_exists('home_category_ids') ? home_category_ids() : [];
        $hcAll = [];
        foreach ($hcGroups as $g) foreach ($g->Categories as $c) $hcAll[] = ['id' => $c->Id, 'name' => $c->Name, 'group' => $g->Name, 'icon' => $c->Icon, 'img' => $c->ImageUrl, 'on' => $c->IsActive && $g->IsActive];
    ?>
    <style>
        .hs-rows { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 10px; }
        .hs-row { display: flex; align-items: center; gap: 12px; border: 1.5px solid #E3ECF5; border-radius: 12px; padding: 11px 14px; background: #fff; cursor: pointer; margin: 0; transition: border-color .2s, background .2s; }
        .hs-row:hover { border-color: #9FDDF5; }
        .hs-row.off { background: #F8FAFC; }
        .hs-row.off .hs-t, .hs-row.off .hs-ic { opacity: .5; }
        .hs-ic { width: 36px; height: 36px; border-radius: 10px; background: #E3F6FD; color: #028EA5; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .hs-t { flex: 1; min-width: 0; } .hs-t b { display: block; color: #08224A; font-size: 14.5px; } .hs-t small { display: block; color: #64748B; font-size: 12.5px; line-height: 1.4; }
        .hs-sw { appearance: none; -webkit-appearance: none; width: 42px; height: 23px; border-radius: 20px; background: #CBD5E1; position: relative; cursor: pointer; transition: background .2s; margin: 0; flex-shrink: 0; }
        .hs-sw::after { content: ''; position: absolute; top: 2px; left: 2px; width: 19px; height: 19px; border-radius: 50%; background: #fff; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
        .hs-sw:checked { background: #22C55E; } .hs-sw:checked::after { transform: translateX(19px); }
        .hc-modes { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
        .hc-modes label { display: flex; gap: 10px; align-items: flex-start; border: 1.5px solid #E3ECF5; border-radius: 12px; padding: 12px 14px; cursor: pointer; margin: 0; flex: 1 1 260px; }
        .hc-modes label:has(input:checked) { border-color: #02B8F5; background: #F0FAFF; }
        .hc-modes input { margin-top: 4px; }
        .hc-modes b { display: block; color: #08224A; font-size: 14.5px; } .hc-modes small { color: #64748B; font-size: 12.5px; }
        .hc-pane { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; }
        .hc-pane[hidden] { display: none; }
        .hc-box { border: 1.5px solid #E3ECF5; border-radius: 12px; padding: 12px; min-width: 0; }
        .hc-box h6 { margin: 0 0 10px; font-size: 13.5px; font-weight: 800; color: #08224A; display: flex; justify-content: space-between; align-items: center; }
        .hc-sel { display: flex; flex-direction: column; gap: 6px; max-height: 360px; overflow: auto; }
        .hc-item { display: flex; align-items: center; gap: 9px; border: 1px solid #E3ECF5; border-radius: 10px; padding: 7px 9px; background: #fff; }
        .hc-item .n { width: 22px; height: 22px; border-radius: 6px; background: #08224A; color: #fff; font-size: 11.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .hc-item .t { flex: 1; min-width: 0; font-size: 13.5px; font-weight: 600; color: #1E293B; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .hc-item .t small { color: #94A3B8; font-weight: 500; }
        .hc-item button { border: 1px solid #DCECF5; background: #fff; border-radius: 6px; width: 26px; height: 24px; font-size: 11px; color: #475569; cursor: pointer; padding: 0; }
        .hc-item button:disabled { opacity: .35; cursor: default; }
        .hc-item button.x { color: #DC2626; }
        .hc-empty { color: #94A3B8; font-size: 13px; text-align: center; padding: 18px 8px; }
        .hc-all { max-height: 360px; overflow: auto; }
        .hc-all .g { font-size: 12px; font-weight: 800; color: #94A3B8; text-transform: uppercase; margin: 10px 0 4px; }
        .hc-all label { display: flex; gap: 8px; align-items: center; padding: 5px 6px; border-radius: 8px; cursor: pointer; margin: 0; font-size: 13.5px; color: #1E293B; }
        .hc-all label:hover { background: #F1F5F9; }
        .hc-all label.dis { opacity: .5; }
        @media (max-width: 768px) { .hc-pane { grid-template-columns: 1fr; } .hs-rows { grid-template-columns: 1fr; } }
    </style>
    <div class="settings-card" id="hsSettings" data-sg="home">
        <h5><i class="fas fa-toggle-on"></i> Home — সেকশন চালু / বন্ধ</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            হোম পেজের কোন সেকশন দেখাবে আর কোনটা লুকানো থাকবে — সুইচ দিয়ে ঠিক করুন, তারপর Save চাপুন। বন্ধ করা সেকশনের লেখা/ছবি মুছে যায় না, পরে আবার চালু করা যায়।
        </p>
        <div class="hs-rows" id="hsRows">
            <?php foreach ($hsList as $k => [$label, $desc, $icon]): $on = home_section_on($k); ?>
                <label class="hs-row<?= $on ? '' : ' off' ?>">
                    <span class="hs-ic"><i class="fas <?= e($icon) ?>"></i></span>
                    <span class="hs-t"><b><?= e($label) ?></b><small><?= e($desc) ?></small></span>
                    <input type="checkbox" class="hs-sw" data-hs="<?= e($k) ?>"<?= $on ? ' checked' : '' ?> aria-label="<?= e($label) ?>" />
                </label>
            <?php endforeach; ?>
        </div>
        <div class="d-flex gap-2 justify-content-end mt-3 flex-wrap">
            <a href="/" target="_blank" class="upload-btn" style="text-decoration:none;"><i class="fas fa-external-link-alt"></i> হোম পেজ দেখুন</a>
            <button type="button" class="btn-save" id="hsSave"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>

    <!-- ===== ✅ HOME — Explore Categories এ কোন ক্যাটাগরি ===== -->
    <div class="settings-card" id="hcSettings" data-sg="home">
        <h5><i class="fas fa-layer-group"></i> Home — Explore Categories</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            হোম পেজের "Explore Categories" সেকশনে কোন ক্যাটাগরিগুলো দেখাবে। নিজে বাছাই করলে যে ক্রমে সাজাবেন সেভাবেই দেখাবে (সর্বোচ্চ ১২টা)।
        </p>
        <div class="hc-modes">
            <label><input type="radio" name="hcMode" value="auto"<?= $hcPicked ? '' : ' checked' ?> /><span><b>অটোমেটিক</b><small>প্রত্যেক গ্রুপ থেকে মিলিয়ে ৮টা ক্যাটাগরি নিজে থেকে দেখাবে</small></span></label>
            <label><input type="radio" name="hcMode" value="manual"<?= $hcPicked ? ' checked' : '' ?> /><span><b>নিজে বাছাই করব</b><small>নিচের তালিকা থেকে টিক দিন, ▲▼ দিয়ে ক্রম ঠিক করুন</small></span></label>
        </div>
        <div class="hc-pane" id="hcPane"<?= $hcPicked ? '' : ' hidden' ?>>
            <div class="hc-box">
                <h6><span><i class="fas fa-list-ol"></i> হোম পেজে যা দেখাবে</span><span id="hcCount" style="color:#64748B;font-weight:700;"></span></h6>
                <div class="hc-sel" id="hcSel"></div>
            </div>
            <div class="hc-box">
                <h6><span><i class="fas fa-layer-group"></i> সব ক্যাটাগরি</span></h6>
                <input type="search" id="hcSearch" class="form-control" placeholder="খুঁজুন — যেমন: IELTS, Guitar, Class 5" style="margin-bottom:6px;" />
                <div class="hc-all" id="hcAll">
                    <?php $lastG = null; foreach ($hcAll as $c): ?>
                        <?php if ($c['group'] !== $lastG): $lastG = $c['group']; ?><div class="g"><?= e($c['group']) ?></div><?php endif; ?>
                        <label class="<?= $c['on'] ? '' : 'dis' ?>" data-name="<?= e(mb_strtolower($c['name'] . ' ' . $c['group'])) ?>"><input type="checkbox" value="<?= (int)$c['id'] ?>" /> <?= e($c['name']) ?><?= $c['on'] ? '' : ' <small>(বন্ধ)</small>' ?></label>
                    <?php endforeach; ?>
                    <?php if (!$hcAll): ?><div class="hc-empty">কোনো ক্যাটাগরি নেই — <a href="/Admin/Categories">Admin → Categories</a> থেকে যোগ করুন।</div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 justify-content-end mt-3 flex-wrap">
            <a href="/Admin/Categories" class="upload-btn" style="text-decoration:none;"><i class="fas fa-pen-to-square"></i> ক্যাটাগরি এডিট</a>
            <button type="button" class="btn-save" id="hcSave"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
    <script>
    (function () {
        function tok() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
        async function post(url, data) {
            var fd = new FormData(); Object.keys(data).forEach(function (k) { fd.append(k, data[k]); }); fd.append('__RequestVerificationToken', tok());
            try { var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); return await r.json(); }
            catch (e) { return { success: false, message: 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।' }; }
        }
        // ----- সেকশন চালু/বন্ধ -----
        document.querySelectorAll('#hsRows .hs-sw').forEach(function (sw) {
            sw.addEventListener('change', function () { sw.closest('.hs-row').classList.toggle('off', !sw.checked); });
        });
        document.getElementById('hsSave').addEventListener('click', async function () {
            var b = this, data = {};
            document.querySelectorAll('#hsRows .hs-sw').forEach(function (sw) { data[sw.getAttribute('data-hs')] = sw.checked; });
            b.disabled = true;
            var r = await post('/Admin/SaveHomeSections', { sections: JSON.stringify(data) });
            b.disabled = false;
            alert((r.success ? '✅ ' : '❌ ') + (r.message || ''));
            if (r.success) {
                // একই সুইচ অন্য কার্ডেও আছে — মিলিয়ে দিই
                var fq = document.getElementById('fqEnabled'); if (fq) { fq.checked = data.faq; if (typeof fqPreview === 'function') fqPreview(); }
                var bt = document.querySelector('#btSettings input[type="checkbox"][id*="nabled"], #btSettings input[name="bt_enabled"]'); if (bt) bt.checked = data.become;
            }
        });

        // FAQ / Become a Tutor কার্ডের নিজস্ব সুইচ বদলালে এখানেও মিলিয়ে দিই
        document.addEventListener('change', function (e) {
            var map = { fqEnabled: 'faq', bt_enabled: 'become' }, k = map[e.target.id]; if (!k) return;
            var sw = document.querySelector('#hsRows .hs-sw[data-hs="' + k + '"]'); if (sw) { sw.checked = e.target.checked; sw.closest('.hs-row').classList.toggle('off', !sw.checked); }
        });
        // ----- Explore Categories -----
        var ALL = <?= js(array_column($hcAll, null, 'id')) ?>, sel = <?= js($hcPicked) ?>, MAX = 12;
        sel = sel.filter(function (id) { return ALL[id]; });
        var selBox = document.getElementById('hcSel'), all = document.getElementById('hcAll'), pane = document.getElementById('hcPane');
        function esc(t) { return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
        function draw() {
            selBox.innerHTML = sel.length ? sel.map(function (id, i) {
                var c = ALL[id];
                return '<div class="hc-item" data-id="' + id + '"><span class="n">' + (i + 1) + '</span><span class="t">' + esc(c.name) + ' <small>· ' + esc(c.group) + (c.on ? '' : ' · বন্ধ') + '</small></span>'
                    + '<button type="button" data-mv="-1"' + (i === 0 ? ' disabled' : '') + ' aria-label="উপরে"><i class="fas fa-chevron-up"></i></button>'
                    + '<button type="button" data-mv="1"' + (i === sel.length - 1 ? ' disabled' : '') + ' aria-label="নিচে"><i class="fas fa-chevron-down"></i></button>'
                    + '<button type="button" class="x" data-rm aria-label="সরান"><i class="fas fa-xmark"></i></button></div>';
            }).join('') : '<div class="hc-empty">ডান পাশের তালিকা থেকে ক্যাটাগরিতে টিক দিন।</div>';
            document.getElementById('hcCount').textContent = sel.length + ' / ' + MAX;
            all.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = sel.indexOf(+cb.value) !== -1; });
        }
        all.addEventListener('change', function (e) {
            var cb = e.target; if (cb.type !== 'checkbox') return;
            var id = +cb.value, i = sel.indexOf(id);
            if (cb.checked && i === -1) { if (sel.length >= MAX) { cb.checked = false; alert('সর্বোচ্চ ' + MAX + 'টা ক্যাটাগরি বাছাই করা যাবে।'); return; } sel.push(id); }
            if (!cb.checked && i !== -1) sel.splice(i, 1);
            draw();
        });
        selBox.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            var id = +b.closest('[data-id]').getAttribute('data-id'), i = sel.indexOf(id);
            if (b.hasAttribute('data-rm')) sel.splice(i, 1);
            else { var j = i + +b.getAttribute('data-mv'); if (j < 0 || j >= sel.length) return; sel.splice(j, 0, sel.splice(i, 1)[0]); }
            draw();
        });
        document.getElementById('hcSearch').addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            all.querySelectorAll('label[data-name]').forEach(function (l) { l.style.display = !q || l.getAttribute('data-name').indexOf(q) !== -1 ? '' : 'none'; });
        });
        document.querySelectorAll('input[name="hcMode"]').forEach(function (r) { r.addEventListener('change', function () { pane.hidden = document.querySelector('input[name="hcMode"]:checked').value !== 'manual'; }); });
        document.getElementById('hcSave').addEventListener('click', async function () {
            var manual = document.querySelector('input[name="hcMode"]:checked').value === 'manual';
            if (manual && !sel.length) { alert('অন্তত ১টা ক্যাটাগরিতে টিক দিন, অথবা "অটোমেটিক" বেছে নিন।'); return; }
            var b = this; b.disabled = true;
            var r = await post('/Admin/SaveHomeCategories', { ids: manual ? sel.join(',') : '' });
            b.disabled = false;
            alert((r.success ? '✅ ' : '❌ ') + (r.message || ''));
            if (r.success && !manual) { sel = []; draw(); }
        });
        draw();
    })();
    </script>

    <!-- ===== Hero Text (হোম পেজের বড় লেখা) — নিজের মতো সাজানো ===== -->
    <?php $hxT = hero_texts(); ?>
    <style>
        .hxt-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .hxt-grid label, .hxt-l { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 5px; }
        .hxt-grid label small, .hxt-l small { font-weight: 500; color: #94A3B8; }
        .hxt-grid input { width: 100%; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 9px 12px; font-size: 14px; font-family: inherit; outline: none; background: #fff; }
        .hxt-grid input:focus { border-color: #02B8F5; }
        .hxt-grid .bad input { border-color: #DC2626; }
        .hxt-blk { margin-bottom: 14px; }
        @media (max-width: 992px) { .hxt-grid { grid-template-columns: minmax(0, 1fr); } }
    </style>
    <div class="settings-card" data-sg="home" id="heroTextCard">
        <h5><i class="fas fa-heading"></i> Hero Text (হোম পেজের উপরের লেখা)</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">শিরোনাম আর বর্ণনা নিজের মতো সাজান — লেখা মাউস দিয়ে বাছাই (select) করে উপরের বাটন থেকে <b>বোল্ড</b>, <i>ইটালিক</i>, সাইজ, রঙ বা হাইলাইট দিন। Enter চাপলে নতুন লাইন। যেমন দেখাচ্ছে, হোম পেজে প্রায় তেমনই দেখাবে।</p>
        <div class="hxt-blk"><span class="hxt-l">শিরোনাম *</span>
            <?php partial('Admin/_RichEditor', ['id' => 'hx_title_html', 'html' => $hxT['hx_title_html'], 'ph' => 'যেমন: Find the Right Tutor in Bangladesh',
                'box' => 'font-size:38px;font-weight:600;line-height:1.22;color:#1F2937;letter-spacing:-.3px;']); ?></div>
        <div class="hxt-blk"><span class="hxt-l">বর্ণনা *</span>
            <?php partial('Admin/_RichEditor', ['id' => 'hx_desc_html', 'html' => $hxT['hx_desc_html'], 'ph' => 'শিরোনামের নিচের লেখা',
                'box' => 'font-size:16px;line-height:1.65;color:#4B5563;']); ?></div>
        <div class="hxt-grid" id="hxtForm">
            <div data-f="hx_marquee"><label for="hx_marquee">সার্চ বক্সের চলমান লেখা <small>(খালি = কিছু চলবে না)</small></label><input id="hx_marquee" maxlength="120" value="<?= e($hxT['hx_marquee']) ?>" /></div>
            <div data-f="hx_btn"><label for="hx_btn">বাটনের লেখা *</label><input id="hx_btn" maxlength="40" value="<?= e($hxT['hx_btn']) ?>" /></div>
            <div data-f="hx_btn_link"><label for="hx_btn_link">বাটন / বক্সে ক্লিক করলে যাবে</label><input id="hx_btn_link" maxlength="300" value="<?= e($hxT['hx_btn_link']) ?>" placeholder="/FindTutor" /></div>
        </div>
        <div class="d-flex gap-2 mt-3 flex-wrap align-items-center">
            <button type="button" class="btn btn-primary" id="hxtSave"><i class="fas fa-floppy-disk me-1"></i> Save Hero Text</button>
            <button type="button" class="btn btn-outline-secondary" id="hxtReset"><i class="fas fa-rotate-left me-1"></i> আগের লেখায় ফেরত</button>
            <a href="/" target="_blank" rel="noopener" class="btn btn-link">হোম পেজ খুলে দেখুন <i class="fas fa-arrow-up-right-from-square"></i></a>
        </div>
    </div>
    <script>
    // পেজ রেডি হওয়ার আগেই চালু — নিচের Save কোড এগুলো ব্যবহার করে
    window.mmRichSave = function (url, data, btn, onOk) {
        var fd = new FormData(), t = document.querySelector('input[name="__RequestVerificationToken"]');
        if (t) fd.append('__RequestVerificationToken', t.value);
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        document.querySelectorAll('.rte.bad, [data-f].bad').forEach(function (x) { x.classList.remove('bad'); });
        btn.disabled = true;
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); }).then(function (d) {
            btn.disabled = false;
            if (d.success && onOk) onOk(d);
            if (!d.success && d.field) { var el = document.getElementById(d.field); if (el) { (el.closest('.rte') || el.closest('[data-f]') || el).classList.add('bad'); el.focus(); } }
            alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
        }).catch(function () { btn.disabled = false; alert('❌ সার্ভারে সমস্যা — আবার চেষ্টা করুন।'); });
    };
    (function () {
        function $(i) { return document.getElementById(i); }
        var plain = ['hx_marquee', 'hx_btn', 'hx_btn_link'];
        function fill(d) { ['hx_title_html', 'hx_desc_html'].forEach(function (k) { if (d[k] !== undefined) $(k).innerHTML = d[k]; }); plain.forEach(function (k) { if (d[k] !== undefined) $(k).value = d[k]; }); }
        $('hxtSave').addEventListener('click', function () {
            var data = { hx_title_html: $('hx_title_html').innerHTML, hx_desc_html: $('hx_desc_html').innerHTML };
            plain.forEach(function (k) { data[k] = $(k).value; });
            mmRichSave('/Admin/SaveHeroText', data, this, fill);
        });
        $('hxtReset').addEventListener('click', function () { if (confirm('হিরোর লেখা আগের (ডিফল্ট) অবস্থায় ফেরত নেবেন?')) mmRichSave('/Admin/SaveHeroText', { reset: 'true' }, this, fill); });
    })();
    </script>

    <!-- ===== Login / Signup পেজের ছবি আর লেখা ===== -->
    <?php $auT = auth_page_texts(); ?>
    <div class="settings-card" data-sg="auth" id="authPageCard">
        <h5><i class="fas fa-right-to-bracket"></i> Login / Signup পেজ — ছবি আর লেখা</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">লগইন আর সাইনআপ পেজের পাশের কার্টুন ছবি আর "Welcome to ..." লেখা। ছবি আপলোড করুন বা লিংক দিন, লেখা নিজের মতো সাজান, তারপর Save।</p>
        <div class="row g-3">
            <div class="col-lg-4">
                <span class="hxt-l">ছবি</span>
                <div style="border:1.5px dashed #CBD5E1;border-radius:14px;padding:10px;background:#F8FAFC;text-align:center;">
                    <img id="auImgPrev" src="<?= e($auT['auth_img']) ?>" alt="" style="max-width:100%;max-height:220px;border-radius:10px;" onerror="this.style.opacity=.3" />
                </div>
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <label for="auImgFile" class="btn btn-outline-primary btn-sm mb-0"><i class="fas fa-upload me-1"></i> ছবি আপলোড</label>
                    <input type="file" id="auImgFile" accept="image/*" style="display:none;" />
                </div>
                <div class="hxt-grid" style="grid-template-columns:1fr;margin-top:8px;"><div data-f="auth_img"><input id="auth_img" value="<?= e($auT['auth_img']) ?>" placeholder="অথবা ছবির লিংক (https://...)" /></div></div>
                <small style="color:#94A3B8;">সাদা / স্বচ্ছ ব্যাকগ্রাউন্ডের ছবি সবচেয়ে ভালো দেখায়। সর্বোচ্চ ৫ MB।</small>
            </div>
            <div class="col-lg-8">
                <div class="hxt-blk"><span class="hxt-l">শিরোনাম *</span>
                    <?php partial('Admin/_RichEditor', ['id' => 'auth_title_html', 'html' => $auT['auth_title_html'], 'ph' => 'যেমন: Welcome to Mashtermoshai',
                        'box' => 'font-size:34px;font-weight:600;line-height:1.15;color:#0B1F44;']); ?></div>
                <div class="hxt-blk"><span class="hxt-l">বর্ণনা <small>(ঐচ্ছিক)</small></span>
                    <?php partial('Admin/_RichEditor', ['id' => 'auth_desc_html', 'html' => $auT['auth_desc_html'], 'ph' => 'শিরোনামের নিচের ছোট লেখা',
                        'box' => 'font-size:16px;line-height:1.55;color:#475569;']); ?></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="auth_feats" <?= $auT['auth_feats'] !== '0' ? 'checked' : '' ?> /><label class="form-check-label fw-semibold" for="auth_feats">নিচের তিনটা আইকন দেখাবে (Find Tutor · Post Requirement · Tuition Circular)</label></div>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3 flex-wrap align-items-center">
            <button type="button" class="btn btn-primary" id="auSave"><i class="fas fa-floppy-disk me-1"></i> Save</button>
            <button type="button" class="btn btn-outline-secondary" id="auReset"><i class="fas fa-rotate-left me-1"></i> আগের ছবি ও লেখায় ফেরত</button>
            <a href="/Account/Login" target="_blank" rel="noopener" class="btn btn-link">লগইন পেজ খুলে দেখুন <i class="fas fa-arrow-up-right-from-square"></i></a>
        </div>
    </div>
    <script>
    (function () {
        function $(i) { return document.getElementById(i); }
        function fill(d) {
            ['auth_title_html', 'auth_desc_html'].forEach(function (k) { if (d[k] !== undefined) $(k).innerHTML = d[k]; });
            if (d.auth_img) { $('auth_img').value = d.auth_img; $('auImgPrev').src = d.auth_img; $('auImgPrev').style.opacity = 1; }
            if (d.auth_feats !== undefined) $('auth_feats').checked = d.auth_feats !== '0';
        }
        $('auth_img').addEventListener('change', function () { $('auImgPrev').src = this.value; $('auImgPrev').style.opacity = 1; });
        $('auImgFile').addEventListener('change', function () {
            var f = this.files[0]; if (!f) return;
            if (f.size > 5 * 1024 * 1024) { alert('ছবি ৫ MB এর কম হতে হবে।'); this.value = ''; return; }
            var fd = new FormData(), t = document.querySelector('input[name="__RequestVerificationToken"]');
            fd.append('authImage', f); if (t) fd.append('__RequestVerificationToken', t.value);
            var lb = document.querySelector('label[for="auImgFile"]'), h = lb.innerHTML; lb.innerHTML = '<i class="fas fa-spinner fa-spin"></i> আপলোড হচ্ছে...';
            fetch('/Admin/UploadAuthImage', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); }).then(function (d) {
                lb.innerHTML = h; $('auImgFile').value = '';
                if (d.success) { $('auth_img').value = d.imageUrl; $('auImgPrev').src = d.imageUrl; $('auImgPrev').style.opacity = 1; }
                alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
            }).catch(function () { lb.innerHTML = h; alert('❌ আপলোড হয়নি।'); });
        });
        $('auSave').addEventListener('click', function () {
            mmRichSave('/Admin/SaveAuthPage', { auth_img: $('auth_img').value, auth_title_html: $('auth_title_html').innerHTML, auth_desc_html: $('auth_desc_html').innerHTML, auth_feats: $('auth_feats').checked ? 'true' : 'false' }, this, fill);
        });
        $('auReset').addEventListener('click', function () { if (confirm('লগইন / সাইনআপ পেজের ছবি আর লেখা আগের (ডিফল্ট) অবস্থায় ফেরত নেবেন?')) mmRichSave('/Admin/SaveAuthPage', { reset: 'true' }, this, fill); });
    })();
    </script>

    <!-- ============================================================ -->
    <!-- ===== Hero Images Section ===== -->
    <div class="settings-card" data-sg="home">
        <h5><i class="fas fa-images"></i> Hero Images</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            হোম পেইজের বাম পাশের হিরো ইমেজ। একাধিক ইমেজ যোগ করলে স্লাইডার হিসেবে কাজ করবে।
        </p>

        <div class="d-flex gap-2 mb-3 flex-wrap">
            <label for="heroImageFile" class="upload-btn">
                <i class="fas fa-upload"></i> Upload New Image
            </label>
            <input type="file" id="heroImageFile" accept="image/*" style="display:none;" />

            <div class="d-flex gap-2" style="flex: 1; min-width: 250px;">
                <input type="text" id="heroImageUrlInput" class="form-control" placeholder="অথবা Direct URL দিন..." />
                <button type="button" class="upload-btn" onclick="addImageFromUrl()" style="white-space:nowrap;">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>

        <span class="info-note">
            <i class="fas fa-info-circle"></i>
            Max 5MB per image. JPG, PNG, GIF, WEBP allowed. প্রথম ইমেজটি Primary হিসেবে দেখাবে।
        </span>

        <!-- Image Gallery -->
        <div id="imageGallery" class="image-gallery">
            <?php if (count($heroImages) === 0): ?>
                <div class="empty-gallery">
                    <i class="fas fa-image"></i>
                    <p>No images yet. Upload your first hero image!</p>
                </div>
            <?php else: ?>
                <?php for ($i = 0; $i < count($heroImages); $i++): ?>
                    <div class="image-item" data-url="<?= e($heroImages[$i]) ?>">
                        <img src="<?= e($heroImages[$i]) ?>" alt="Hero Image <?= e($i + 1) ?>" />
                        <?php if ($i === 0): ?>
                            <span class="primary-badge"><i class="fas fa-star"></i> Primary</span>
                        <?php endif; ?>
                        <button type="button" class="remove-btn" onclick="removeImage(this)" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== ✅ Tutor Section Image ===== -->
    <div class="settings-card" data-sg="home">
        <h5><i class="fas fa-chalkboard-user"></i> Tutor Section Image</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            হোম পেইজের "Find Your Teacher" সেকশনের ইমেজ। এই ইমেজটি পুরো width জুড়ে দেখাবে।
        </p>

        <div class="d-flex gap-2 mb-3 flex-wrap">
            <label for="tutorSectionImageFile" class="upload-btn">
                <i class="fas fa-upload"></i> Upload New Image
            </label>
            <input type="file" id="tutorSectionImageFile" accept="image/*" style="display:none;" />

            <div class="d-flex gap-2" style="flex: 1; min-width: 250px;">
                <input type="text" id="tutorSectionImageUrlInput" class="form-control" placeholder="অথবা Direct URL দিন..." />
                <button type="button" class="upload-btn" onclick="applyTutorImageUrl()" style="white-space:nowrap;">
                    <i class="fas fa-check"></i> Apply
                </button>
            </div>
        </div>

        <span class="info-note">
            <i class="fas fa-info-circle"></i>
            Max 5MB. JPG, PNG, GIF, WEBP allowed. Recommended size: <strong>1500×700 px</strong>.
        </span>

        <!-- Preview -->
        <div id="tutorImagePreviewWrap" style="margin-top:16px; <?= is_blank($Model->TutorSectionImageUrl) ? 'display:none;' : '' ?>">
            <p class="mb-2 fw-bold" style="font-size:12px; color:#08224A;">Preview:</p>
            <div class="image-item" style="max-width:480px; aspect-ratio:16/8;" id="tutorImagePreviewBox">
                <img id="tutorImagePreview"
                     src="<?= e(is_blank($Model->TutorSectionImageUrl) ? 'https://i.ibb.co.com/Hfpwnx46/111.png' : $Model->TutorSectionImageUrl) ?>"
                     alt="Tutor Section Preview" />
            </div>
            <input type="hidden" id="tutorSectionImageUrl" value="<?= e($Model->TutorSectionImageUrl) ?>" />
        </div>
    </div>

    <!-- ===== Slider Settings ===== -->
    <div class="settings-card" data-sg="home">
        <h5><i class="fas fa-play-circle"></i> Slider Settings</h5>

        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
            <label class="toggle-switch">
                <input type="checkbox" id="sliderEnabled" <?= $Model->SliderEnabled ? 'checked' : '' ?> />
                <span class="toggle-slider"></span>
            </label>
            <div>
                <strong style="color:#08224A; font-size:14px;">Enable Image Slider</strong>
                <p style="color:#7A8CA0; font-size:12.5px; margin:0;">
                    একাধিক ইমেজ থাকলে অটো-স্লাইড হবে
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Slider Interval (Seconds)</label>
                <input type="number" id="sliderInterval" class="form-control"
                       value="<?= e($Model->SliderIntervalSeconds) ?>" min="1" max="60" />
                <span class="info-note">
                    <i class="fas fa-clock"></i>
                    কত সেকেন্ড পর পর ইমেজ পরিবর্তন হবে (1-60 সেকেন্ড)
                </span>
            </div>
        </div>
    </div>

    <!-- ===== ✅ Become a Tutor Section (Home Page) ===== -->
    <?php $btc = become_tutor_content(); ?>
    <div class="settings-card" id="btSettings" data-sg="home">
        <h5><i class="fas fa-chalkboard-teacher"></i> Home — "Become a Tutor" Section</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            হোম পেজের "Become a Tutor" সেকশনের লেখা এখান থেকে বদলান।
            কোনো অক্ষর বা শব্দ রঙিন করতে <code>[ ]</code> দিয়ে ঘিরে দিন — যেমন <code>TUT[O]R?</code> বা <code>Be a [master],</code>
        </p>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="bt_enabled" <?= $btc['bt_enabled'] !== '0' ? 'checked' : '' ?> oninput="btPreview()" />
            <label class="form-check-label fw-semibold" for="bt_enabled">হোম পেজে সেকশনটা দেখাও</label>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="bt_badge">ছোট ব্যাজ</label>
                <input type="text" class="form-control" id="bt_badge" maxlength="40" value="<?= e($btc['bt_badge']) ?>" oninput="btPreview()" placeholder="For Teachers" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="bt_kicker">উপরের লাইন</label>
                <input type="text" class="form-control" id="bt_kicker" maxlength="60" value="<?= e($btc['bt_kicker']) ?>" oninput="btPreview()" placeholder="Want to Become a" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="bt_title">বড় শিরোনাম</label>
                <input type="text" class="form-control" id="bt_title" maxlength="30" value="<?= e($btc['bt_title']) ?>" oninput="btPreview()" placeholder="TUT[O]R?" />
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="bt_line1">স্লোগান — প্রথম লাইন</label>
                <input type="text" class="form-control" id="bt_line1" maxlength="70" value="<?= e($btc['bt_line1']) ?>" oninput="btPreview()" placeholder="Be a [master]," />
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="bt_line2">স্লোগান — দ্বিতীয় লাইন</label>
                <input type="text" class="form-control" id="bt_line2" maxlength="70" value="<?= e($btc['bt_line2']) ?>" oninput="btPreview()" placeholder="to grow faster." />
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold" for="bt_perks">সুবিধাগুলো <span style="color:#7A8CA0; font-weight:400;">(প্রতি লাইনে একটা, সর্বোচ্চ ৬টা)</span></label>
                <textarea class="form-control" id="bt_perks" rows="3" oninput="btPreview()"><?= e($btc['bt_perks']) ?></textarea>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="bt-prev-wrap">
            <div class="bt-prev-label"><i class="fas fa-eye"></i> Live Preview</div>
            <div class="bt-prev" id="btPrev">
                <div>
                    <span class="bt-prev-badge" id="btpBadge"></span>
                    <div class="bt-prev-kicker" id="btpKicker"></div>
                    <div class="bt-prev-big" id="btpTitle"></div>
                </div>
                <div class="bt-prev-div"></div>
                <div>
                    <div class="bt-prev-tag"><span id="btpL1"></span><span class="l2" id="btpL2"></span></div>
                    <ul class="bt-prev-perks" id="btpPerks"></ul>
                </div>
                <div class="bt-prev-div"></div>
                <div style="text-align:center;"><span class="bt-prev-btn"><i class="fas fa-paper-plane"></i> Apply Now</span></div>
            </div>
            <div class="bt-prev-off" id="btPrevOff"><i class="fas fa-eye-slash"></i> সেকশনটা এখন হোম পেজে লুকানো থাকবে</div>
        </div>

        <div class="text-end mt-3">
            <button type="button" class="btn-save" id="btSaveBtn" onclick="saveBecomeTutor()">
                <i class="fas fa-save"></i> Save Section Text
            </button>
        </div>
    </div>

    <!-- ===== ✅ Home Page — FAQ (প্রশ্ন ও উত্তর) ===== -->
    <?php $fqSet = function_exists('home_faq_settings') ? home_faq_settings() : ['enabled' => true, 'items' => []]; ?>
    <style>
        .fq-list { display: flex; flex-direction: column; gap: 12px; }
        .fq-row { display: flex; gap: 12px; align-items: flex-start; border: 1.5px solid #DCECF5; border-radius: 14px; padding: 12px; background: #F8FCFF; }
        .fq-row.has-err { border-color: #e74c3c; background: #FFF5F4; }
        .fq-num { width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; background: #E3F6FD; color: #028EA5; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; margin-top: 2px; }
        .fq-mid { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
        .fq-mid input, .fq-mid textarea { width: 100%; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 9px 12px; font-size: 14px; outline: none; background: #fff; font-family: inherit; color: #08224A; }
        .fq-mid input { font-weight: 700; }
        .fq-mid textarea { min-height: 70px; resize: vertical; line-height: 1.6; }
        .fq-mid input:focus, .fq-mid textarea:focus { border-color: #02B8F5; }
        .fq-acts { display: flex; flex-direction: column; gap: 4px; flex-shrink: 0; }
        .fq-acts button { width: 32px; height: 32px; border: none; border-radius: 8px; background: #EAF5FB; color: #08224A; cursor: pointer; }
        .fq-acts button:hover { background: #DCECF5; }
        .fq-acts button.del { background: #FDECEA; color: #c0392b; }
        .fq-acts button:disabled { opacity: .35; cursor: default; }
        .fq-empty { text-align: center; color: #7A8CA0; font-size: 13.5px; border: 1.5px dashed #DCECF5; border-radius: 14px; padding: 18px; }
        .fq-add { margin-top: 12px; border: 1.5px dashed #02B8F5; background: #F5FBFF; color: #028EA5; font-weight: 700; font-size: 14px; border-radius: 12px; padding: 10px 16px; cursor: pointer; width: 100%; }
        .fq-add:hover { background: #E3F6FD; }
        .fq-foot { display: flex; gap: 10px; justify-content: flex-end; align-items: center; flex-wrap: wrap; margin-top: 16px; }
        .fq-reset { border: 1.5px solid #DCECF5; background: #fff; color: #64748B; font-weight: 600; font-size: 14px; border-radius: 10px; padding: 10px 16px; cursor: pointer; }
        .fq-reset:hover { border-color: #02B8F5; color: #08224A; }
        /* প্রিভিউ — হোম পেজের মতো */
        .fq-prev-wrap { margin-top: 18px; background: #F5FBFF; border-radius: 16px; padding: 18px; }
        .fq-prev-label { font-size: 12px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: #7A8CA0; margin-bottom: 12px; }
        .fq-prev { display: flex; flex-direction: column; gap: 10px; }
        .fq-prev details { background: #fff; border: 1px solid #DCECF5; border-radius: 16px; }
        .fq-prev details[open] { border-color: #9FDDF5; box-shadow: 0 10px 26px rgba(2,184,245,.1); }
        .fq-prev summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 14px; padding: 15px 18px; font-weight: 700; font-size: 15px; color: #08224A; }
        .fq-prev summary::-webkit-details-marker { display: none; }
        .fq-prev summary .q { width: 30px; height: 30px; border-radius: 10px; background: #E3F6FD; color: #028EA5; display: flex; align-items: center; justify-content: center; font-size: 12.5px; flex-shrink: 0; }
        .fq-prev summary b { flex: 1; font-weight: 700; }
        .fq-prev summary .pl { color: #028EA5; transition: transform .25s; }
        .fq-prev details[open] summary .pl { transform: rotate(45deg); }
        .fq-prev p { padding: 0 18px 16px 62px; margin: 0; font-size: 14.5px; line-height: 1.7; color: #475569; white-space: pre-line; }
        .fq-off { text-align: center; color: #7A8CA0; font-size: 13.5px; padding: 10px; }
        @media (max-width: 576px) { .fq-row { flex-wrap: wrap; } .fq-acts { flex-direction: row; width: 100%; justify-content: flex-end; } .fq-prev p { padding-left: 18px; } }
    </style>
    <div class="settings-card" id="catSettings" data-sg="home">
        <h5><i class="fas fa-layer-group"></i> Categories (ক্যাটাগরি)</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            নেভবারের <b>Categories</b> পেজের ক্যাটাগরি (Play, Class 1, IELTS, Guitar ...) — নতুন ক্যাটাগরি ছবি দিয়ে যোগ করা, গ্রুপ বানানো,
            আর প্রত্যেক ক্যাটাগরির “About This Category”, “Why Choose”, ব্যানার ইত্যাদি এডিট করা যায়।
        </p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/Admin/Categories" class="btn btn-primary"><i class="fas fa-pen-to-square me-1"></i> Categories ম্যানেজ করুন</a>
            <a href="/Categories" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-external-link-alt me-1"></i> সাইটে দেখুন</a>
        </div>
    </div>

    <div class="settings-card" id="fqSettings" data-sg="home">
        <h5><i class="fas fa-circle-question"></i> Home — FAQ (প্রশ্ন ও উত্তর)</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            হোম পেজের "Frequently Asked Questions" সেকশনের প্রশ্ন আর উত্তর এখান থেকে যোগ, এডিট, মুছে ফেলা বা ▲▼ দিয়ে সাজাতে পারবেন।
            এগুলো Google-এর জন্য FAQ Schema হিসেবেও যায়। (সর্বোচ্চ ২০টা)
        </p>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="fqEnabled" <?= $fqSet['enabled'] ? 'checked' : '' ?> onchange="fqPreview()" />
            <label class="form-check-label fw-semibold" for="fqEnabled">হোম পেজে FAQ সেকশনটা দেখাও</label>
        </div>

        <div class="fq-list" id="fqList"></div>
        <button type="button" class="fq-add" onclick="fqAdd()"><i class="fas fa-plus"></i> নতুন প্রশ্ন যোগ করুন</button>

        <div class="fq-prev-wrap">
            <div class="fq-prev-label"><i class="fas fa-eye"></i> Live Preview — হোম পেজে এভাবে দেখাবে</div>
            <div class="fq-prev" id="fqPrev"></div>
        </div>

        <div class="fq-foot">
            <button type="button" class="fq-reset" onclick="fqReset()"><i class="fas fa-rotate-left"></i> Default প্রশ্নগুলো</button>
            <button type="button" class="btn-save" id="fqSaveBtn" onclick="fqSave()"><i class="fas fa-save"></i> Save FAQ</button>
        </div>
    </div>
    <script>
        var fqItems = <?= json_encode(array_map(fn($f) => ['q' => $f[0], 'a' => $f[1]], $fqSet['items']), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        var fqErrIdx = -1;
        function fqEsc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
        function fqRender() {
            var list = document.getElementById('fqList');
            list.innerHTML = fqItems.length ? fqItems.map(function (f, i) {
                return '<div class="fq-row' + (i === fqErrIdx ? ' has-err' : '') + '">' +
                    '<span class="fq-num">Q' + (i + 1) + '</span>' +
                    '<div class="fq-mid">' +
                    '<input type="text" maxlength="200" data-i="' + i + '" data-k="q" placeholder="প্রশ্ন — যেমন: How can I find a tutor in Bangladesh?" value="' + fqEsc(f.q) + '" />' +
                    '<textarea maxlength="1500" data-i="' + i + '" data-k="a" placeholder="উত্তর লিখুন...">' + fqEsc(f.a) + '</textarea></div>' +
                    '<div class="fq-acts">' +
                    '<button type="button" title="উপরে" onclick="fqMove(' + i + ',-1)"' + (i === 0 ? ' disabled' : '') + '><i class="fas fa-arrow-up"></i></button>' +
                    '<button type="button" title="নিচে" onclick="fqMove(' + i + ',1)"' + (i === fqItems.length - 1 ? ' disabled' : '') + '><i class="fas fa-arrow-down"></i></button>' +
                    '<button type="button" class="del" title="মুছুন" onclick="fqDel(' + i + ')"><i class="fas fa-trash"></i></button>' +
                    '</div></div>';
            }).join('') : '<div class="fq-empty"><i class="fas fa-circle-question"></i> কোনো প্রশ্ন নেই — নিচের বাটনে চেপে যোগ করুন</div>';
            fqPreview();
        }
        document.getElementById('fqList').addEventListener('input', function (e) {
            var t = e.target, i = t.getAttribute('data-i'); if (i === null) return;
            fqItems[+i][t.getAttribute('data-k')] = t.value; fqPreview();
        });
        function fqPreview() {
            var pv = document.getElementById('fqPrev');
            var shown = fqItems.filter(function (f) { return (f.q || '').trim() && (f.a || '').trim(); });
            if (!document.getElementById('fqEnabled').checked) { pv.innerHTML = '<div class="fq-off"><i class="fas fa-eye-slash"></i> FAQ সেকশনটা এখন হোম পেজে লুকানো থাকবে</div>'; return; }
            if (!shown.length) { pv.innerHTML = '<div class="fq-off"><i class="fas fa-eye-slash"></i> কোনো প্রশ্ন না থাকলে সেকশনটা দেখাবে না</div>'; return; }
            var open = pv.querySelector('details[open]'), openIdx = open ? +open.getAttribute('data-i') : -1;
            pv.innerHTML = shown.map(function (f, i) {
                return '<details data-i="' + i + '"' + (i === openIdx ? ' open' : '') + '><summary><span class="q">Q' + (i + 1) + '</span><b>' + fqEsc(f.q.trim()) + '</b><i class="fas fa-plus pl"></i></summary><p>' + fqEsc(f.a.trim()) + '</p></details>';
            }).join('');
        }
        function fqAdd() {
            if (fqItems.length >= 20) { alert('সর্বোচ্চ ২০টা প্রশ্ন দেওয়া যাবে।'); return; }
            fqItems.push({ q: '', a: '' }); fqErrIdx = -1; fqRender();
            var inp = document.querySelector('#fqList input[data-i="' + (fqItems.length - 1) + '"]');
            if (inp) { inp.focus(); inp.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
        }
        function fqMove(i, d) { var j = i + d; if (j < 0 || j >= fqItems.length) return; var t = fqItems[i]; fqItems[i] = fqItems[j]; fqItems[j] = t; fqErrIdx = -1; fqRender(); }
        function fqDel(i) {
            var f = fqItems[i];
            if (((f.q || '').trim() || (f.a || '').trim()) && !confirm('এই প্রশ্নটা মুছে ফেলবেন?')) return;
            fqItems.splice(i, 1); fqErrIdx = -1; fqRender();
        }
        function fqSend(data, btn) {
            var fd = new FormData();
            Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
            var tok = document.querySelector('input[name="__RequestVerificationToken"]');
            if (tok) fd.append('__RequestVerificationToken', tok.value);
            var html = btn ? btn.innerHTML : '';
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; }
            fetch('/Admin/SaveHomeFaqs', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (btn) { btn.disabled = false; btn.innerHTML = html; }
                    if (d.success) {
                        fqErrIdx = -1; fqItems = d.items || []; document.getElementById('fqEnabled').checked = !!d.enabled; fqRender();
                    } else if (typeof d.index === 'number') {
                        fqErrIdx = d.index; fqRender();
                        var el = document.querySelector('#fqList [data-i="' + d.index + '"]'); if (el) el.focus();
                    }
                    alert(d.message || (d.success ? 'Saved' : 'Failed'));
                })
                .catch(function (e) { if (btn) { btn.disabled = false; btn.innerHTML = html; } alert('Error: ' + e.message); });
        }
        function fqSave() {
            fqSend({ enabled: document.getElementById('fqEnabled').checked ? 'true' : 'false',
                items: JSON.stringify(fqItems.map(function (f) { return { q: (f.q || '').trim(), a: (f.a || '').trim() }; })) }, document.getElementById('fqSaveBtn'));
        }
        function fqReset() { if (confirm('সব প্রশ্ন মুছে Default ৫টা প্রশ্ন-উত্তরে ফেরত যাবেন?')) fqSend({ reset: 'true' }, null); }
        fqRender();
    </script>

    <!-- ===== ✅ Profile Page — Promo Image ===== -->
    <?php $ppc = profile_promo(); ?>
    <div class="settings-card" id="ppSettings" data-sg="profile">
        <h5><i class="fas fa-image"></i> Student Profile — বাম পাশের ছবি</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            স্টুডেন্টের প্রোফাইল পেজে বাম মেনুর নিচের ছবি ("সঠিক Tutor সঠিক ভবিষ্যৎ")। নতুন ছবি আপলোড করুন বা লিংক দিন — সাথে সাথে সব প্রোফাইলে বদলে যাবে।
        </p>
        <div class="pp-grid">
            <div class="pp-prev-wrap">
                <div class="pp-prev-label"><i class="fas fa-eye"></i> Preview</div>
                <a class="pp-prev" id="ppPrevBox"><img id="ppPreview" src="<?= e($ppc['pp_image']) ?>" alt="Promo preview" onerror="this.style.opacity=.25" /></a>
                <div class="pp-prev-off" id="ppPrevOff" <?= $ppc['pp_enabled'] !== '0' ? 'hidden' : '' ?>><i class="fas fa-eye-slash"></i> ছবিটা এখন প্রোফাইলে লুকানো</div>
            </div>
            <div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="pp_enabled" <?= $ppc['pp_enabled'] !== '0' ? 'checked' : '' ?> onchange="document.getElementById('ppPrevOff').hidden = this.checked" />
                    <label class="form-check-label fw-semibold" for="pp_enabled">প্রোফাইল পেজে ছবিটা দেখাও</label>
                </div>
                <label class="form-label fw-semibold">ছবি</label>
                <div class="d-flex gap-2 mb-2 flex-wrap">
                    <label for="ppFile" class="upload-btn" id="ppUploadLbl"><i class="fas fa-upload"></i> Upload New Image</label>
                    <input type="file" id="ppFile" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" />
                </div>
                <input type="text" class="form-control mb-1" id="pp_image" value="<?= e($ppc['pp_image']) ?>" placeholder="অথবা ছবির লিংক দিন — https://..." oninput="document.getElementById('ppPreview').src = this.value; document.getElementById('ppPreview').style.opacity = 1;" />
                <span class="info-note"><i class="fas fa-info-circle"></i> Max 5MB. JPG, PNG, GIF, WEBP. খাড়া/বর্গাকার ছবি ভালো দেখায় (যেমন <strong>600×700 px</strong>)।</span>
                <label class="form-label fw-semibold mt-3" for="pp_link">ছবিতে ক্লিক করলে কোথায় যাবে <span style="color:#7A8CA0; font-weight:400;">(ঐচ্ছিক)</span></label>
                <input type="text" class="form-control" id="pp_link" value="<?= e($ppc['pp_link']) ?>" placeholder="খালি রাখলে: Find Tutor পেজে যাবে" />
                <div class="d-flex gap-2 justify-content-end mt-3 flex-wrap">
                    <button type="button" class="upload-btn" onclick="savePromo(true)"><i class="fas fa-rotate-left"></i> Default ছবি</button>
                    <button type="button" class="btn-save" id="ppSaveBtn" onclick="savePromo(false)"><i class="fas fa-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== ✅ টিউশনের অপশন: লোকেশন / ক্লাস / বিষয় ===== -->
    <?php $topt = tuition_options(); ?>
    <div class="settings-card" id="optSettings" data-sg="tuition">
        <h5><i class="fas fa-list-check"></i> টিউশনের অপশন — লোকেশন, ক্লাস ও বিষয়</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            Find Tutor আর Tuition Circular পেজের ফিল্টার আর পোস্ট লেখার ঘরে এই তালিকাগুলো দেখায়। নতুন যোগ করুন বা × দিয়ে বাদ দিন, তারপর <b>Save</b> চাপুন।
        </p>
        <div class="opt-tabs" role="tablist">
            <button type="button" class="active" data-opt-tab="locations"><i class="fas fa-location-dot"></i> লোকেশন <span id="optCntLoc"></span></button>
            <button type="button" data-opt-tab="classes"><i class="fas fa-graduation-cap"></i> ক্লাস <span id="optCntCls"></span></button>
            <button type="button" data-opt-tab="subjects"><i class="fas fa-book"></i> বিষয় <span id="optCntSub"></span></button>
        </div>

        <div class="opt-pane" data-opt-pane="locations">
            <div class="opt-add">
                <select id="optDistrict" class="form-control"><option value="">— জেলা বাছাই করুন —</option></select>
                <input type="text" id="optArea" class="form-control" maxlength="60" placeholder="এলাকার নাম (যেমন: Mirpur) — খালি রাখলে শুধু জেলা যোগ হবে" />
                <input type="text" id="optNewDistrict" class="form-control" maxlength="60" placeholder="নতুন জেলা/শহরের নাম" hidden />
                <button type="button" class="btn-save" id="optAddLoc"><i class="fas fa-plus"></i> Add</button>
            </div>
            <input type="search" id="optLocSearch" class="form-control mb-2" placeholder="জেলা বা এলাকা খুঁজুন..." />
            <div class="opt-locs" id="optLocs"></div>
        </div>
        <div class="opt-pane" data-opt-pane="classes" hidden>
            <div class="opt-add"><input type="text" id="optClassIn" class="form-control" maxlength="60" placeholder="নতুন ক্লাস (যেমন: Class 5, HSC, University)" />
                <button type="button" class="btn-save" data-opt-add="classes"><i class="fas fa-plus"></i> Add</button></div>
            <div class="opt-chips" id="optClasses"></div>
        </div>
        <div class="opt-pane" data-opt-pane="subjects" hidden>
            <div class="opt-add"><input type="text" id="optSubjectIn" class="form-control" maxlength="60" placeholder="নতুন বিষয় (যেমন: Physics, Bangla)" />
                <button type="button" class="btn-save" data-opt-add="subjects"><i class="fas fa-plus"></i> Add</button></div>
            <div class="opt-chips" id="optSubjects"></div>
        </div>
        <div class="d-flex gap-2 justify-content-end mt-3 flex-wrap">
            <button type="button" class="upload-btn" id="optReset"><i class="fas fa-rotate-left"></i> এই তালিকা Default করুন</button>
            <button type="button" class="btn-save" id="optSave"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>

    <!-- ===== ✅ Home — Key Features এর ছবি ===== -->
    <div class="settings-card" id="kfSettings" data-sg="home">
        <h5><i class="fas fa-star"></i> Key Features — কার্ডের ছবি</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            হোম পেজের "Key Features" সেকশনের ৬টা কার্ডের উপরের ছবি। প্রতিটার জন্য ছবি আপলোড করুন বা লিংক দিন, তারপর <strong>Save</strong> চাপুন।
            ছবি না দিলে কার্ডে আইকন সহ রঙিন প্যানেল দেখাবে।
        </p>
        <style>
            .kfa-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
            .kfa-it { border: 1px solid #E3ECF5; border-radius: 14px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
            .kfa-pv { position: relative; height: 130px; background: linear-gradient(135deg, #E0F7FF, #8FE3FB); display: flex; align-items: center; justify-content: center; }
            .kfa-pv > i { width: 54px; height: 54px; border-radius: 16px; background: #fff; color: #00B5E0; font-size: 22px; display: flex; align-items: center; justify-content: center; }
            .kfa-pv img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
            .kfa-pv img[hidden] { display: none; }
            .kfa-pv .n { position: absolute; top: 8px; left: 8px; z-index: 1; width: 26px; height: 26px; border-radius: 8px; background: rgba(8,34,74,.85); color: #fff; font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
            .kfa-bd { padding: 10px 12px 12px; display: flex; flex-direction: column; gap: 8px; }
            .kfa-bd b { font-size: 14px; color: #08224A; }
            .kfa-bd .btns { display: flex; gap: 6px; }
            .kfa-bd .btns .upload-btn { flex: 1; justify-content: center; font-size: 12.5px; padding: 7px 8px; margin: 0; }
            .kfa-bd input { font-size: 12.5px; }
            @media (max-width: 1100px) { .kfa-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            @media (max-width: 600px) { .kfa-grid { grid-template-columns: minmax(0, 1fr); } }
        </style>
        <div class="kfa-grid">
            <?php foreach (key_features() as $kfi => $kf): ?>
                <div class="kfa-it">
                    <div class="kfa-pv">
                        <span class="n"><?= (int)$kfi ?></span>
                        <i class="fas <?= e($kf['icon']) ?>"></i>
                        <img id="kfPv<?= (int)$kfi ?>" src="<?= e($kf['image']) ?>" alt=""<?= $kf['image'] === '' ? ' hidden' : '' ?> onerror="this.hidden = true" />
                    </div>
                    <div class="kfa-bd">
                        <b><?= e($kf['title']) ?></b>
                        <div class="btns">
                            <label for="kfFile<?= (int)$kfi ?>" class="upload-btn" id="kfUp<?= (int)$kfi ?>"><i class="fas fa-upload"></i> Upload</label>
                            <input type="file" id="kfFile<?= (int)$kfi ?>" data-kf="<?= (int)$kfi ?>" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" />
                            <button type="button" class="upload-btn" data-kf-clear="<?= (int)$kfi ?>"><i class="fas fa-xmark"></i> সরাও</button>
                        </div>
                        <input type="text" class="form-control" id="kf_img_<?= (int)$kfi ?>" data-kf-url="<?= (int)$kfi ?>" value="<?= e($kf['image']) ?>" placeholder="অথবা ছবির লিংক — https://..." />
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <span class="info-note mt-2 d-inline-block"><i class="fas fa-info-circle"></i> Max 5MB. JPG, PNG, GIF, WEBP. চওড়া ছবি ভালো দেখায় (যেমন <strong>800×450 px</strong>)।</span>
        <div class="d-flex gap-2 justify-content-end mt-3 flex-wrap">
            <button type="button" class="upload-btn" onclick="saveKeyFeatures(true)"><i class="fas fa-rotate-left"></i> Default ছবি</button>
            <button type="button" class="btn-save" id="kfSaveBtn" onclick="saveKeyFeatures(false)"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>

    <!-- ===== ✅ Tuition Circular / Find Tutor — Banner Image ===== -->
    <?php foreach ([['tc', 'circular', 'Tuition Circular', 'fa-bullhorn', circular_banner()], ['ft', 'findtutor', 'Find Tutor', 'fa-chalkboard-user', findtutor_banner()], ['pk', 'package', 'Package', 'fa-tags', package_banner()], ['hd', 'helpdesk', 'Help Desk', 'fa-headset', helpdesk_banner()]] as [$bk, $bw, $bname, $bicon, $bv]): ?>
    <div class="settings-card" id="<?= $bk ?>bSettings" data-sg="banners">
        <h5><i class="fas <?= $bicon ?>"></i> <?= $bname ?> — উপরের ব্যানার ছবি</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            <?= $bname ?> পেজের একদম উপরের বড় ছবি। নতুন ছবি আপলোড করুন বা লিংক দিন — সাথে সাথে পেজে বদলে যাবে।
        </p>
        <div class="tcb-prev-wrap">
            <div class="pp-prev-label"><i class="fas fa-eye"></i> Preview</div>
            <a class="pp-prev"><img id="<?= $bk ?>bPreview" src="<?= e($bv[$bk . '_banner']) ?>" alt="Banner preview" onerror="this.style.opacity=.25" /></a>
            <div class="pp-prev-off" id="<?= $bk ?>bPrevOff" <?= $bv[$bk . '_banner_enabled'] !== '0' ? 'hidden' : '' ?>><i class="fas fa-eye-slash"></i> ব্যানার এখন লুকানো — পেজে শুধু লেখা দেখাবে</div>
        </div>
        <div class="form-check form-switch my-3">
            <input class="form-check-input" type="checkbox" id="<?= $bk ?>_banner_enabled" <?= $bv[$bk . '_banner_enabled'] !== '0' ? 'checked' : '' ?> onchange="document.getElementById('<?= $bk ?>bPrevOff').hidden = this.checked" />
            <label class="form-check-label fw-semibold" for="<?= $bk ?>_banner_enabled"><?= $bname ?> পেজে ব্যানার দেখাও</label>
        </div>
        <label class="form-label fw-semibold">ছবি</label>
        <div class="d-flex gap-2 mb-2 flex-wrap">
            <label for="<?= $bk ?>bFile" class="upload-btn" id="<?= $bk ?>bUploadLbl"><i class="fas fa-upload"></i> Upload New Image</label>
            <input type="file" id="<?= $bk ?>bFile" data-banner="<?= $bk ?>" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" />
        </div>
        <input type="text" class="form-control mb-1" id="<?= $bk ?>_banner" value="<?= e($bv[$bk . '_banner']) ?>" placeholder="অথবা ছবির লিংক দিন — https://..." oninput="var p = document.getElementById('<?= $bk ?>bPreview'); p.src = this.value; p.style.opacity = 1;" />
        <?php if ($bk === 'hd'): ?>
        <span class="info-note"><i class="fas fa-info-circle"></i> Max 5MB. এই ব্যানারের <strong>ভেতরেই</strong> "Help Desk" লেখা আর ইলাস্ট্রেশন থাকে (যেমন <strong>1600×420 px</strong>, হালকা নীল ব্যাকগ্রাউন্ড)। ব্যানার বন্ধ করলে পেজে লেখার হেডার দেখাবে।</span>
        <?php elseif ($bk === 'pk'): ?>
        <span class="info-note"><i class="fas fa-info-circle"></i> Max 5MB. ছবির <strong>ডান দিকে</strong> ইলাস্ট্রেশন রাখুন — বাম দিকে “মাষ্টার মশাই প্যাকেজ” লেখা আর ট্যাব বসে (যেমন <strong>1600×420 px</strong>)।</span>
        <?php else: ?>
        <span class="info-note"><i class="fas fa-info-circle"></i> Max 5MB. চওড়া ছবি ভালো দেখায় (যেমন <strong>1500×280 px</strong>)।</span>
        <?php endif; ?>
        <div class="d-flex gap-2 justify-content-end mt-3 flex-wrap">
            <button type="button" class="upload-btn" onclick="saveBanner('<?= $bk ?>', '<?= $bw ?>', true)"><i class="fas fa-rotate-left"></i> Default ছবি</button>
            <button type="button" class="btn-save" id="<?= $bk ?>bSaveBtn" onclick="saveBanner('<?= $bk ?>', '<?= $bw ?>', false)"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- ===== ✅ Teacher Profile — Promo Image ===== -->
    <?php $tpc = teacher_promo(); ?>
    <div class="settings-card" id="tpSettings" data-sg="profile">
        <h5><i class="fas fa-chalkboard-user"></i> Teacher Profile — বাম পাশের ছবি</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            শুধু টিচারদের প্রোফাইল পেজে বাম মেনুর নিচে দেখায়। ছবি না দিলে ডিজাইন করা কার্ড ("আরও বেশি শিক্ষার্থীর কাছে পৌঁছান" + Create Tuition Post) দেখাবে।
            নিজের ছবি আপলোড করলে সেটা দেখাবে — সাথে সাথে সব টিচার প্রোফাইলে বদলে যাবে।
        </p>
        <div class="pp-grid">
            <div class="pp-prev-wrap">
                <div class="pp-prev-label"><i class="fas fa-eye"></i> Preview</div>
                <a class="pp-prev" id="tpPrevBox" <?= is_blank($tpc['tp_image']) ? 'hidden' : '' ?>><img id="tpPreview" src="<?= e($tpc['tp_image']) ?>" alt="Teacher promo preview" onerror="this.style.opacity=.25" /></a>
                <div id="tpPrevCard" <?= is_blank($tpc['tp_image']) ? '' : 'hidden' ?>><?php partial('Profile/_TeacherPromoCard', ['preview' => true]); ?></div>
                <div class="pp-prev-off" id="tpPrevOff" <?= $tpc['tp_enabled'] !== '0' ? 'hidden' : '' ?>><i class="fas fa-eye-slash"></i> এখন টিচার প্রোফাইলে লুকানো</div>
            </div>
            <div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="tp_enabled" <?= $tpc['tp_enabled'] !== '0' ? 'checked' : '' ?> onchange="document.getElementById('tpPrevOff').hidden = this.checked" />
                    <label class="form-check-label fw-semibold" for="tp_enabled">টিচার প্রোফাইলে দেখাও</label>
                </div>
                <label class="form-label fw-semibold">ছবি <span style="color:#7A8CA0; font-weight:400;">(খালি = ডিজাইন করা কার্ড)</span></label>
                <div class="d-flex gap-2 mb-2 flex-wrap">
                    <label for="tpFile" class="upload-btn" id="tpUploadLbl"><i class="fas fa-upload"></i> Upload New Image</label>
                    <input type="file" id="tpFile" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" />
                    <button type="button" class="upload-btn" id="tpClearImg"><i class="fas fa-xmark"></i> ছবি সরাও</button>
                </div>
                <input type="text" class="form-control mb-1" id="tp_image" value="<?= e($tpc['tp_image']) ?>" placeholder="অথবা ছবির লিংক দিন — https://..." />
                <span class="info-note"><i class="fas fa-info-circle"></i> Max 5MB. JPG, PNG, GIF, WEBP. খাড়া/বর্গাকার ছবি ভালো দেখায় (যেমন <strong>600×700 px</strong>)।</span>
                <label class="form-label fw-semibold mt-3" for="tp_link">ক্লিক করলে কোথায় যাবে <span style="color:#7A8CA0; font-weight:400;">(ঐচ্ছিক)</span></label>
                <input type="text" class="form-control" id="tp_link" value="<?= e($tpc['tp_link']) ?>" placeholder="খালি রাখলে: Find Tutor পেজে (টিউশন পোস্ট দেওয়ার জায়গা)" />
                <div class="d-flex gap-2 justify-content-end mt-3 flex-wrap">
                    <button type="button" class="upload-btn" onclick="saveTeacherPromo(true)"><i class="fas fa-rotate-left"></i> Default কার্ড</button>
                    <button type="button" class="btn-save" id="tpSaveBtn" onclick="saveTeacherPromo(false)"><i class="fas fa-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

<?php if (admin_is_super()): /* SMTP সেটিং — শুধু Super Admin */ ?>
    <!-- ===== ✅ Email (SMTP) Settings — Forgot Password ===== -->
    <?php $ms = mail_settings(); $msHasPass = $ms['mail_pass'] !== ''; ?>
    <div class="settings-card" id="mailSettings" data-sg="email">
        <h5><i class="fas fa-envelope"></i> Email (SMTP) Settings — Forgot Password</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:6px;">
            ইউজার "Forgot password?" দিলে তার ইমেইলে রিসেট লিংক যাবে — সেই ইমেইল পাঠানোর জন্য এখানে cPanel এর ইমেইল অ্যাকাউন্টের তথ্য দিন।
        </p>
        <div class="mail-tip">
            <i class="fas fa-lightbulb"></i>
            <div>cPanel → <b>Email Accounts</b> → <b>Create</b> দিয়ে <code>noreply@mashtermoshai.com</code> বানান। তারপর ঐ অ্যাকাউন্টের <b>Connect Devices</b> এ যে
                <b>Outgoing Server</b> ও <b>SMTP Port</b> দেখায় সেগুলো নিচে বসান (সাধারণত Host: <code>mail.mashtermoshai.com</code>, Port: <code>465</code>, SSL)।</div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="mail_host">SMTP Host (Outgoing Server)</label>
                <input type="text" class="form-control" id="mail_host" value="<?= e($ms['mail_host']) ?>" placeholder="mail.mashtermoshai.com" />
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold" for="mail_port">Port</label>
                <input type="number" class="form-control" id="mail_port" value="<?= e($ms['mail_port']) ?>" placeholder="465" />
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold" for="mail_secure">Security</label>
                <select class="form-select" id="mail_secure">
                    <?php foreach (['ssl' => 'SSL (465)', 'tls' => 'TLS (587)', 'none' => 'None'] as $k => $l): ?>
                        <option value="<?= e($k) ?>" <?= $ms['mail_secure'] === $k ? 'selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="mail_user">Username (পুরো ইমেইল ঠিকানা)</label>
                <input type="email" class="form-control" id="mail_user" value="<?= e($ms['mail_user']) ?>" placeholder="noreply@mashtermoshai.com" autocomplete="off" />
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="mail_pass">ইমেইল অ্যাকাউন্টের Password</label>
                <input type="password" class="form-control" id="mail_pass" value="" autocomplete="new-password"
                       placeholder="<?= $msHasPass ? '•••••••• (সেভ করা আছে — বদলাতে চাইলে নতুনটা লিখুন)' : 'ইমেইল অ্যাকাউন্টের পাসওয়ার্ড' ?>" />
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="mail_from_email">From Email <span style="color:#7A8CA0; font-weight:400;">(ঐচ্ছিক)</span></label>
                <input type="email" class="form-control" id="mail_from_email" value="<?= e($ms['mail_from_email']) ?>" placeholder="খালি রাখলে Username টাই" />
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="mail_from_name">From Name <span style="color:#7A8CA0; font-weight:400;">(ঐচ্ছিক)</span></label>
                <input type="text" class="form-control" id="mail_from_name" maxlength="80" value="<?= e($ms['mail_from_name']) ?>" placeholder="<?= e(site_settings()->GetSafeSiteName()) ?>" />
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold" for="mail_site_url">Site URL <span style="color:#7A8CA0; font-weight:400;">(ইমেইলের লিংক এই ঠিকানায় যাবে)</span></label>
                <input type="url" class="form-control" id="mail_site_url" value="<?= e($ms['mail_site_url']) ?>" placeholder="https://mashtermoshai.com" />
            </div>
        </div>

        <div class="mail-actions">
            <div class="mail-test">
                <input type="email" class="form-control" id="mail_test_to" value="<?= e(session_get('UserEmail') ?? '') ?>" placeholder="টেস্ট ইমেইল যাবে এই ঠিকানায়" />
                <button type="button" class="btn btn-outline-primary" id="mailTestBtn" onclick="mailTest()"><i class="fas fa-paper-plane"></i> Send Test Email</button>
            </div>
            <button type="button" class="btn-save" id="mailSaveBtn" onclick="mailSave()"><i class="fas fa-save"></i> Save Email Settings</button>
        </div>
        <div class="mail-result" id="mailResult"></div>
    </div>
    <style>
        .mail-tip { display: flex; gap: 10px; background: #F5FBFF; border: 1px solid #DCECF5; border-radius: 12px; padding: 12px 14px; font-size: 13px; color: #334155; line-height: 1.6; margin: 10px 0 16px; }
        .mail-tip > i { color: #FFB300; margin-top: 3px; }
        .mail-tip code { background: #EAF5FB; color: #028EA5; padding: 1px 6px; border-radius: 6px; }
        .mail-actions { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 18px; }
        .mail-test { display: flex; gap: 8px; flex: 1 1 340px; max-width: 520px; }
        .mail-test input { min-width: 0; }
        .mail-test .btn { white-space: nowrap; }
        .mail-result { margin-top: 12px; font-size: 13.5px; border-radius: 10px; padding: 0; }
        .mail-result.ok { background: #E8F8EF; color: #155724; border: 1px solid #A8E2C0; padding: 10px 14px; }
        .mail-result.err { background: #FDECEA; color: #8B1E15; border: 1px solid #F5B7B1; padding: 10px 14px; }
        @media (max-width: 576px) { .mail-test { flex-direction: column; } .mail-actions .btn-save { width: 100%; justify-content: center; } }
    </style>
    <script>
        function mailRes(ok, msg) { var r = document.getElementById('mailResult'); r.className = 'mail-result ' + (ok ? 'ok' : 'err'); r.textContent = msg; }
        function mailFd() {
            var fd = new FormData();
            ['mail_host', 'mail_port', 'mail_secure', 'mail_user', 'mail_pass', 'mail_from_email', 'mail_from_name', 'mail_site_url'].forEach(function (k) { fd.append(k, document.getElementById(k).value); });
            var tok = document.querySelector('input[name="__RequestVerificationToken"]'); if (tok) fd.append('__RequestVerificationToken', tok.value);
            return fd;
        }
        function mailPost(url, fd, btn) {
            var html = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> অপেক্ষা করুন...';
            return fetch(url, { method: 'POST', body: fd }).then(function (r) { return r.json(); })
                .then(function (d) { btn.disabled = false; btn.innerHTML = html; mailRes(!!d.success, d.message || (d.success ? 'Done' : 'Failed')); return d; })
                .catch(function (e) { btn.disabled = false; btn.innerHTML = html; mailRes(false, 'Error: ' + e.message); return {}; });
        }
        function mailSave() {
            return mailPost('/Admin/SaveMailSettings', mailFd(), document.getElementById('mailSaveBtn')).then(function (d) {
                if (d.success) { var p = document.getElementById('mail_pass'); p.value = ''; if (d.hasPass) p.placeholder = '•••••••• (সেভ করা আছে — বদলাতে চাইলে নতুনটা লিখুন)'; }
                return d;
            });
        }
        function mailTest() {
            // আগে সেভ, তারপর টেস্ট
            mailSave().then(function (d) {
                if (!d.success) return;
                var fd = new FormData(); fd.append('to', document.getElementById('mail_test_to').value.trim());
                var tok = document.querySelector('input[name="__RequestVerificationToken"]'); if (tok) fd.append('__RequestVerificationToken', tok.value);
                mailPost('/Admin/SendTestEmail', fd, document.getElementById('mailTestBtn'));
            });
        }
    </script>
<?php endif; ?>

    <!-- ===== ✅ Footer Social Media ===== -->
    <?php $smLinks = social_links(); $smPl = social_platforms(); ?>
    <style>
        .sm-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .sm-chip { display: inline-flex; align-items: center; gap: 7px; border: 1.5px solid #DCECF5; background: #fff; border-radius: 999px;
            padding: 7px 13px; font-size: 13px; font-weight: 600; color: #08224A; cursor: pointer; transition: border-color .15s, background .15s, transform .1s; }
        .sm-chip i { color: var(--c); font-size: 15px; }
        .sm-chip:hover { border-color: var(--c); background: #F5FBFF; }
        .sm-chip:active { transform: scale(.96); }
        .sm-chip .sm-added { display: none; color: #16A34A; font-size: 12px; }
        .sm-chip.is-added { background: #F0FDF4; border-color: #A8E2C0; }
        .sm-chip.is-added .sm-added { display: inline; }
        .sm-list { display: flex; flex-direction: column; gap: 10px; }
        .sm-row { display: flex; align-items: center; gap: 10px; border: 1.5px solid #DCECF5; border-radius: 14px; padding: 10px 12px; background: #F8FCFF; }
        .sm-row.has-err { border-color: #e74c3c; background: #FFF5F4; }
        .sm-ico { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 17px; }
        .sm-mid { flex: 1; min-width: 0; }
        .sm-name { font-weight: 700; font-size: 13.5px; color: #08224A; margin-bottom: 4px; }
        .sm-mid input { width: 100%; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 8px 11px; font-size: 14px; outline: none; background: #fff; }
        .sm-mid input:focus { border-color: #02B8F5; }
        .sm-acts { display: flex; gap: 4px; flex-shrink: 0; }
        .sm-acts button { width: 32px; height: 32px; border: none; border-radius: 8px; background: #EAF5FB; color: #08224A; cursor: pointer; }
        .sm-acts button:hover { background: #DCECF5; }
        .sm-acts button.del { background: #FDECEA; color: #c0392b; }
        .sm-acts button:disabled { opacity: .35; cursor: default; }
        .sm-empty { text-align: center; color: #7A8CA0; font-size: 13.5px; border: 1.5px dashed #DCECF5; border-radius: 14px; padding: 18px; }
        .sm-prev { margin-top: 16px; background: #08224A; border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .sm-prev > span { color: rgba(255,255,255,.75); font-size: 13px; }
        .sm-prev a { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.1); color: rgba(255,255,255,.85); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
        .sm-prev a:hover { background: #fff; color: var(--c); }
        @media (max-width: 576px) {
            .sm-row { flex-wrap: wrap; }
            .sm-mid { flex: 1 1 calc(100% - 52px); }
            .sm-acts { width: 100%; justify-content: flex-end; }
        }
    </style>
    <?php $ctI = contact_info(); ?>
    <div class="settings-card" id="ctSettings" data-sg="social">
        <h5><i class="fas fa-address-card"></i> যোগাযোগের তথ্য (Contact পেজ)</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">Help Desk এর <a href="/Helpdesk/Contact" target="_blank" rel="noopener">Contact পেজে</a> আর Help Desk পেজের উপরে এই নম্বর ও ইমেইল দেখায়। ঘর খালি রাখলে ঐ অংশ দেখাবে না।</p>
        <div class="hxt-grid" id="ctForm">
            <div data-f="ct_phone"><label for="ct_phone">মোবাইল নম্বর</label><input id="ct_phone" maxlength="30" value="<?= e($ctI['ct_phone']) ?>" placeholder="01805843825" /></div>
            <div data-f="ct_whatsapp"><label for="ct_whatsapp">WhatsApp নম্বর <small>(খালি = মোবাইল নম্বরই)</small></label><input id="ct_whatsapp" maxlength="30" value="<?= e($ctI['ct_whatsapp']) ?>" placeholder="01805843825" /></div>
            <div data-f="ct_email"><label for="ct_email">ইমেইল</label><input id="ct_email" maxlength="120" value="<?= e($ctI['ct_email']) ?>" placeholder="promashtermoshai@gmail.com" /></div>
            <div data-f="ct_hours"><label for="ct_hours">যোগাযোগের সময় <small>(ঐচ্ছিক)</small></label><input id="ct_hours" maxlength="120" value="<?= e($ctI['ct_hours']) ?>" placeholder="যেমন: প্রতিদিন সকাল ১০টা – রাত ১০টা" /></div>
            <div data-f="ct_address" style="grid-column:span 2;"><label for="ct_address">ঠিকানা <small>(ঐচ্ছিক)</small></label><input id="ct_address" maxlength="200" value="<?= e($ctI['ct_address']) ?>" /></div>
            <div data-f="ct_note" style="grid-column:1/-1;"><label for="ct_note">Contact পেজের উপরের লেখা <small>(ঐচ্ছিক — খালি = ডিফল্ট লেখা)</small></label><input id="ct_note" maxlength="300" value="<?= e($ctI['ct_note']) ?>" /></div>
        </div>
        <div class="d-flex gap-2 mt-3 flex-wrap align-items-center">
            <button type="button" class="btn btn-primary" id="ctSave"><i class="fas fa-floppy-disk me-1"></i> Save Contact</button>
            <a href="/Helpdesk/Contact" target="_blank" rel="noopener" class="btn btn-link">Contact পেজ খুলে দেখুন <i class="fas fa-arrow-up-right-from-square"></i></a>
        </div>
    </div>
    <script>
    (function () {
        document.getElementById('ctSave').addEventListener('click', function () {
            var d = {}; ['ct_phone', 'ct_whatsapp', 'ct_email', 'ct_hours', 'ct_address', 'ct_note'].forEach(function (k) { d[k] = document.getElementById(k).value; });
            mmRichSave('/Admin/SaveContactInfo', d, this);
        });
    })();
    </script>

    <div class="settings-card" id="smSettings" data-sg="social">
        <h5><i class="fas fa-share-alt"></i> Footer — Social Media Links</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            নিচ থেকে যে সোশ্যাল মিডিয়াগুলো ফুটারে দেখাতে চান সেগুলোতে ক্লিক করুন, তারপর প্রতিটার পাশে আপনার পেজের লিংক দিন।
            ▲▼ দিয়ে ক্রম বদলাতে পারবেন। কোনোটা না রাখলে ফুটারে "Follow us" অংশটা লুকানো থাকবে।
        </p>

        <div class="sm-chips" id="smChips">
            <?php foreach ($smPl as $k => $d): ?>
                <button type="button" class="sm-chip" data-p="<?= e($k) ?>" style="--c:<?= e($d[3]) ?>;" onclick="smAdd('<?= e($k) ?>')">
                    <i class="<?= e($d[1]) ?>"></i> <?= e($d[0]) ?> <span class="sm-added"><i class="fas fa-check"></i></span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="sm-list" id="smList"></div>

        <div class="sm-prev" id="smPrev"></div>

        <div class="text-end mt-3">
            <button type="button" class="btn-save" id="smSaveBtn" onclick="smSave()">
                <i class="fas fa-save"></i> Save Social Links
            </button>
        </div>
    </div>
    <script>
        var SM_PL = <?= json_encode(array_map(fn($d) => ['name' => $d[0], 'icon' => $d[1], 'ph' => $d[2], 'color' => $d[3]], $smPl), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        var smItems = <?= json_encode(array_map(fn($l) => ['p' => $l['p'], 'url' => preg_replace('/^(mailto|tel):/i', '', $l['url'])], $smLinks), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        var smErrIdx = -1;

        function smEsc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }

        function smRender() {
            var list = document.getElementById('smList');
            if (!smItems.length) {
                list.innerHTML = '<div class="sm-empty"><i class="fas fa-hand-pointer"></i> উপরের যেকোনো আইকনে ক্লিক করে সোশ্যাল মিডিয়া অ্যাড করুন</div>';
            } else {
                list.innerHTML = smItems.map(function (it, i) {
                    var d = SM_PL[it.p];
                    return '<div class="sm-row' + (i === smErrIdx ? ' has-err' : '') + '">' +
                        '<div class="sm-ico" style="background:' + d.color + '"><i class="' + d.icon + '"></i></div>' +
                        '<div class="sm-mid"><div class="sm-name">' + smEsc(d.name) + '</div>' +
                        '<input type="text" data-i="' + i + '" value="' + smEsc(it.url) + '" placeholder="' + smEsc(d.ph) + '" oninput="smItems[' + i + '].url=this.value; smPreview();" /></div>' +
                        '<div class="sm-acts">' +
                        '<button type="button" title="উপরে" onclick="smMove(' + i + ',-1)"' + (i === 0 ? ' disabled' : '') + '><i class="fas fa-arrow-up"></i></button>' +
                        '<button type="button" title="নিচে" onclick="smMove(' + i + ',1)"' + (i === smItems.length - 1 ? ' disabled' : '') + '><i class="fas fa-arrow-down"></i></button>' +
                        '<button type="button" class="del" title="মুছুন" onclick="smDel(' + i + ')"><i class="fas fa-trash"></i></button>' +
                        '</div></div>';
                }).join('');
            }
            document.querySelectorAll('#smChips .sm-chip').forEach(function (c) {
                c.classList.toggle('is-added', smItems.some(function (it) { return it.p === c.dataset.p; }));
            });
            smPreview();
        }

        function smPreview() {
            var pv = document.getElementById('smPrev');
            var shown = smItems.filter(function (it) { return (it.url || '').trim() !== ''; });
            pv.innerHTML = shown.length
                ? '<span>Footer Preview — Follow us</span>' + shown.map(function (it) { var d = SM_PL[it.p];
                    return '<a href="javascript:void(0)" title="' + smEsc(d.name) + '" style="--c:' + d.color + '"><i class="' + d.icon + '"></i></a>'; }).join('')
                : '<span><i class="fas fa-eye-slash"></i> ফুটারে কোনো সোশ্যাল আইকন দেখাবে না</span>';
        }

        function smAdd(p) {
            if (smItems.length >= 15) { alert('সর্বোচ্চ ১৫টা দেওয়া যাবে।'); return; }
            var idx = smItems.findIndex(function (it) { return it.p === p; });
            if (idx === -1) { smItems.push({ p: p, url: '' }); idx = smItems.length - 1; smRender(); }
            var inp = document.querySelector('#smList input[data-i="' + idx + '"]');
            if (inp) { inp.focus(); inp.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
        }
        function smMove(i, d) { var j = i + d; if (j < 0 || j >= smItems.length) return; var t = smItems[i]; smItems[i] = smItems[j]; smItems[j] = t; smErrIdx = -1; smRender(); }
        function smDel(i) { smItems.splice(i, 1); smErrIdx = -1; smRender(); }

        function smSave() {
            var fd = new FormData();
            fd.append('links', JSON.stringify(smItems.map(function (it) { return { p: it.p, url: (it.url || '').trim() }; })));
            var tok = document.querySelector('input[name="__RequestVerificationToken"]');
            if (tok) fd.append('__RequestVerificationToken', tok.value);
            var btn = document.getElementById('smSaveBtn'), html = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            fetch('/Admin/SaveSocialLinks', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    btn.disabled = false; btn.innerHTML = html;
                    if (d.success) {
                        smErrIdx = -1;
                        smItems = (d.links || []).map(function (l) { return { p: l.p, url: l.url.replace(/^(mailto|tel):/i, '') }; });
                        smRender();
                    } else if (typeof d.index === 'number') {
                        smErrIdx = d.index; smRender();
                        var inp = document.querySelector('#smList input[data-i="' + d.index + '"]'); if (inp) inp.focus();
                    }
                    alert(d.message || (d.success ? 'Saved' : 'Failed'));
                })
                .catch(function (e) { btn.disabled = false; btn.innerHTML = html; alert('Error: ' + e.message); });
        }
        smRender();
    </script>

    <!-- ===== Theme Colors ===== -->
    <div class="settings-card" data-sg="general">
        <h5><i class="fas fa-palette"></i> Theme Colors</h5>
        <p style="color:#64748B; font-size:13.5px; margin-bottom:14px;">
            ওয়েবসাইটের মূল রং পরিবর্তন করুন। প্রতিটি রং পরিবর্তন করলে সাথে সাথে প্রিভিউ আপডেট হবে।
        </p>

        <div class="row">
            <div class="col-md-6">
                <div class="color-picker-row">
                    <label>Primary Color</label>
                    <input type="color" id="primaryColor" value="<?= e($Model->PrimaryColor) ?>" oninput="syncColor('primaryColor')" />
                    <input type="text" id="primaryColorText" value="<?= e($Model->PrimaryColor) ?>" oninput="syncColorText('primaryColor')" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="color-picker-row">
                    <label>Secondary Color</label>
                    <input type="color" id="secondaryColor" value="<?= e($Model->SecondaryColor) ?>" oninput="syncColor('secondaryColor')" />
                    <input type="text" id="secondaryColorText" value="<?= e($Model->SecondaryColor) ?>" oninput="syncColorText('secondaryColor')" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="color-picker-row">
                    <label>Accent Color</label>
                    <input type="color" id="accentColor" value="<?= e($Model->AccentColor) ?>" oninput="syncColor('accentColor')" />
                    <input type="text" id="accentColorText" value="<?= e($Model->AccentColor) ?>" oninput="syncColorText('accentColor')" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="color-picker-row">
                    <label>Text Color</label>
                    <input type="color" id="textColor" value="<?= e($Model->TextColor) ?>" oninput="syncColor('textColor')" />
                    <input type="text" id="textColorText" value="<?= e($Model->TextColor) ?>" oninput="syncColorText('textColor')" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="color-picker-row">
                    <label>Navbar Background</label>
                    <input type="color" id="navbarBgColor" value="<?= e($Model->NavbarBgColor) ?>" oninput="syncColor('navbarBgColor')" />
                    <input type="text" id="navbarBgColorText" value="<?= e($Model->NavbarBgColor) ?>" oninput="syncColorText('navbarBgColor')" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="color-picker-row">
                    <label>Footer Background</label>
                    <input type="color" id="footerBgColor" value="<?= e($Model->FooterBgColor) ?>" oninput="syncColor('footerBgColor')" />
                    <input type="text" id="footerBgColorText" value="<?= e($Model->FooterBgColor) ?>" oninput="syncColorText('footerBgColor')" />
                </div>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="theme-preview">
            <div class="theme-chip">
                <div class="chip-color" id="previewPrimary" style="background:<?= e($Model->PrimaryColor) ?>;"></div>
                <span class="chip-label">Primary</span>
            </div>
            <div class="theme-chip">
                <div class="chip-color" id="previewSecondary" style="background:<?= e($Model->SecondaryColor) ?>;"></div>
                <span class="chip-label">Secondary</span>
            </div>
            <div class="theme-chip">
                <div class="chip-color" id="previewAccent" style="background:<?= e($Model->AccentColor) ?>;"></div>
                <span class="chip-label">Accent</span>
            </div>
            <div class="theme-chip">
                <div class="chip-color" id="previewText" style="background:<?= e($Model->TextColor) ?>;"></div>
                <span class="chip-label">Text</span>
            </div>
            <div class="theme-chip">
                <div class="chip-color" id="previewNavbar" style="background:<?= e($Model->NavbarBgColor) ?>;"></div>
                <span class="chip-label">Navbar</span>
            </div>
            <div class="theme-chip">
                <div class="chip-color" id="previewFooter" style="background:<?= e($Model->FooterBgColor) ?>;"></div>
                <span class="chip-label">Footer</span>
            </div>
        </div>
    </div>

    <!-- ===== Save Button (Branding/Theme আর Home এর ছবি/স্লাইডার একসাথে সেভ হয়) ===== -->
    <div class="text-end mb-5 sg-save" data-sg="general home">
        <button type="button" class="btn-save" id="saveBtn" onclick="saveAllSettings()">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </div>

</div>

<?php section_start('Scripts'); ?>
    <script>
        // ===== Anti-Forgery Token Helper =====
        function getToken() {
            const t = document.querySelector('input[name="__RequestVerificationToken"]');
            return t ? t.value : '';
        }

        // ===== Escape HTML =====
        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // ============================================================
        // ===== ✅ LOGO HELPERS =====
        // ============================================================
        function getCurrentShape() {
            const r = document.querySelector('input[name="logoShape"]:checked');
            return r ? r.value : 'circle';
        }

        function getShapeRadius() {
            const s = getCurrentShape();
            return s === 'circle' ? '50%' : s === 'rounded' ? '16px' : '0';
        }

        function getCurrentLogoSize() {
            return parseInt(document.getElementById('logoSizeRange').value) || 56;
        }

        function updateLogoPreview() {
            const size = getCurrentLogoSize();
            const radius = getShapeRadius();
            const name = (document.getElementById('siteNameInput').value || '').trim() || 'Mashter Moshai';

            const navImg = document.getElementById('navbarLogoPreview');
            const footImg = document.getElementById('footerLogoPreview');
            const navName = document.getElementById('navbarSiteNamePreview');
            const footName = document.getElementById('footerSiteNamePreview');

            if (navImg) {
                navImg.style.width = size + 'px';
                navImg.style.height = size + 'px';
                navImg.style.borderRadius = radius;
            }
            if (footImg) {
                footImg.style.width = size + 'px';
                footImg.style.height = size + 'px';
                footImg.style.borderRadius = radius;
            }
            if (navName) navName.textContent = name;
            if (footName) footName.textContent = name;

            const sizeLabel = document.getElementById('logoSizeLabel');
            if (sizeLabel) sizeLabel.textContent = size + ' px';
        }

        // Site name input → preview
        document.getElementById('siteNameInput')?.addEventListener('input', updateLogoPreview);

        // Logo URL inputs → preview
        document.getElementById('navbarLogoUrlInput')?.addEventListener('input', function () {
            const img = document.getElementById('navbarLogoPreview');
            if (img) img.src = this.value || '';
        });
        document.getElementById('footerLogoUrlInput')?.addEventListener('input', function () {
            const img = document.getElementById('footerLogoPreview');
            if (img) img.src = this.value || '';
        });

        // ===== Logo Upload =====
        async function uploadLogo(fileInputId, target, urlInputId, previewImgId) {
            const fileInput = document.getElementById(fileInputId);
            const file = fileInput.files[0];
            if (!file) return;

            if (file.size > 3 * 1024 * 1024) {
                alert('Logo must be less than 3MB.');
                fileInput.value = '';
                return;
            }

            const fd = new FormData();
            fd.append('logoImage', file);
            fd.append('target', target);
            fd.append('__RequestVerificationToken', getToken());

            const label = document.querySelector(`label[for="${fileInputId}"]`);
            const original = label.innerHTML;
            label.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            label.style.pointerEvents = 'none';

            try {
                const res = await fetch('/Admin/UploadLogo', { method: 'POST', body: fd });
                const data = await res.json();
                label.innerHTML = original;
                label.style.pointerEvents = 'auto';

                if (data.success) {
                    document.getElementById(urlInputId).value = data.imageUrl;
                    document.getElementById(previewImgId).src = data.imageUrl;
                } else {
                    alert(data.message || 'Upload failed.');
                    fileInput.value = '';
                }
            } catch (err) {
                label.innerHTML = original;
                label.style.pointerEvents = 'auto';
                alert('Error: ' + err.message);
            }
        }

        document.getElementById('navbarLogoFile')?.addEventListener('change', function () {
            uploadLogo('navbarLogoFile', 'navbar', 'navbarLogoUrlInput', 'navbarLogoPreview');
        });
        document.getElementById('footerLogoFile')?.addEventListener('change', function () {
            uploadLogo('footerLogoFile', 'footer', 'footerLogoUrlInput', 'footerLogoPreview');
        });

        // ============================================================
        // ===== Color Picker Sync =====
        // ============================================================
        function syncColor(id) {
            const val = document.getElementById(id).value;
            document.getElementById(id + 'Text').value = val.toUpperCase();
            updatePreview(id, val);
        }

        function syncColorText(id) {
            const val = document.getElementById(id + 'Text').value;
            if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                document.getElementById(id).value = val;
                updatePreview(id, val);
            }
        }

        function updatePreview(id, color) {
            const previewId = 'preview' + id.charAt(0).toUpperCase() + id.slice(1);
            const el = document.getElementById(previewId);
            if (el) el.style.background = color;
        }

        // ============================================================
        // ===== HERO IMAGE UPLOAD =====
        // ============================================================
        document.getElementById('heroImageFile').addEventListener('change', function () {
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

            const formData = new FormData();
            formData.append('heroImage', file);

            const token = document.querySelector('input[name="__RequestVerificationToken"]');
            if (token) formData.append('__RequestVerificationToken', token.value);

            const uploadLabel = document.querySelector('label[for="heroImageFile"]');
            const originalHtml = uploadLabel.innerHTML;
            uploadLabel.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadLabel.style.pointerEvents = 'none';

            fetch('/Admin/UploadHeroImage', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    uploadLabel.innerHTML = originalHtml;
                    uploadLabel.style.pointerEvents = 'auto';

                    if (data.success) {
                        addImageToGallery(data.imageUrl);
                        document.getElementById('heroImageFile').value = '';
                    } else {
                        alert(data.message || 'Failed to upload.');
                    }
                })
                .catch(err => {
                    uploadLabel.innerHTML = originalHtml;
                    uploadLabel.style.pointerEvents = 'auto';
                    alert('Error: ' + err.message);
                });
        });

        // ===== Add Image from URL =====
        function addImageFromUrl() {
            const input = document.getElementById('heroImageUrlInput');
            const url = input.value.trim();
            if (!url) {
                alert('Please enter an image URL.');
                return;
            }
            if (!/^https?:\/\//i.test(url)) {
                alert('URL must start with http:// or https://');
                return;
            }
            addImageToGallery(url);
            input.value = '';
        }

        function addImageToGallery(url) {
            const gallery = document.getElementById('imageGallery');

            // Remove empty state if present
            const emptyState = gallery.querySelector('.empty-gallery');
            if (emptyState) emptyState.remove();

            const div = document.createElement('div');
            div.className = 'image-item';
            div.setAttribute('data-url', url);
            div.innerHTML = `
                                <img src="${escapeHtml(url)}" alt="Hero Image" onerror="this.src='https://i.ibb.co/fd9s88Y/IMG-6916-1.png'" />
                                <button type="button" class="remove-btn" onclick="removeImage(this)" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
            gallery.appendChild(div);
            updatePrimaryBadge();
        }

        // ===== Remove Image =====
        function removeImage(btn) {
            if (!confirm('Remove this image?')) return;
            btn.closest('.image-item').remove();
            updatePrimaryBadge();

            // If no images left, show empty state
            const gallery = document.getElementById('imageGallery');
            if (gallery.querySelectorAll('.image-item').length === 0) {
                gallery.innerHTML = `
                                    <div class="empty-gallery">
                                        <i class="fas fa-image"></i>
                                        <p>No images yet. Upload your first hero image!</p>
                                    </div>
                                `;
            }
        }

        // ===== Update Primary Badge =====
        function updatePrimaryBadge() {
            document.querySelectorAll('.image-item .primary-badge').forEach(b => b.remove());
            const first = document.querySelector('.image-item');
            if (first) {
                const badge = document.createElement('span');
                badge.className = 'primary-badge';
                badge.innerHTML = '<i class="fas fa-star"></i> Primary';
                first.appendChild(badge);
            }
        }

        // ============================================================
        // ===== TUTOR SECTION IMAGE UPLOAD =====
        // ============================================================
        document.getElementById('tutorSectionImageFile').addEventListener('change', function () {
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

            const formData = new FormData();
            formData.append('tutorSectionImage', file);

            const token = document.querySelector('input[name="__RequestVerificationToken"]');
            if (token) formData.append('__RequestVerificationToken', token.value);

            const uploadLabel = document.querySelector('label[for="tutorSectionImageFile"]');
            const originalHtml = uploadLabel.innerHTML;
            uploadLabel.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadLabel.style.pointerEvents = 'none';

            fetch('/Admin/UploadTutorSectionImage', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    uploadLabel.innerHTML = originalHtml;
                    uploadLabel.style.pointerEvents = 'auto';

                    if (data.success) {
                        document.getElementById('tutorImagePreview').src = data.imageUrl;
                        document.getElementById('tutorSectionImageUrl').value = data.imageUrl;
                        document.getElementById('tutorImagePreviewWrap').style.display = 'block';
                        document.getElementById('tutorSectionImageFile').value = '';
                    } else {
                        alert(data.message || 'Failed to upload.');
                    }
                })
                .catch(err => {
                    uploadLabel.innerHTML = originalHtml;
                    uploadLabel.style.pointerEvents = 'auto';
                    alert('Error: ' + err.message);
                });
        });

        // ===== Apply Tutor Image URL =====
        function applyTutorImageUrl() {
            const input = document.getElementById('tutorSectionImageUrlInput');
            const url = input.value.trim();
            if (!url) { alert('Please enter an image URL.'); return; }
            if (!/^https?:\/\//i.test(url)) { alert('URL must start with http:// or https://'); return; }

            document.getElementById('tutorImagePreview').src = url;
            document.getElementById('tutorSectionImageUrl').value = url;
            document.getElementById('tutorImagePreviewWrap').style.display = 'block';
            input.value = '';
        }

        // ===== Enter key on tutor URL input =====
        document.getElementById('tutorSectionImageUrlInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyTutorImageUrl();
            }
        });

        // ============================================================
        // ===== SAVE ALL SETTINGS =====
        // ============================================================
        function saveAllSettings() {
            // Gather image URLs
            const imageUrls = Array.from(document.querySelectorAll('.image-item'))
                .map(el => el.getAttribute('data-url'))
                .filter(u => u);

            // Validation: at least one image required
            if (imageUrls.length === 0) {
                alert('অন্তত একটি হিরো ইমেজ যোগ করুন — "Home Page" সেকশনের "Hero Images" এ ছবি দিন, তারপর আবার Save চাপুন।');
                var hb = document.querySelector('.sg-tabs a[data-sg-go="home"]'); if (hb) hb.click();
                return;
            }

            // Validation: slider interval
            const sliderInterval = parseInt(document.getElementById('sliderInterval').value) || 5;
            if (sliderInterval < 1 || sliderInterval > 60) {
                alert('Slider interval must be between 1 and 60 seconds.');
                return;
            }

            const formData = new FormData();
            formData.append('Id', document.getElementById('settingsId').value);
            formData.append('HeroImageUrls', imageUrls.join(','));
            formData.append('TutorSectionImageUrl', document.getElementById('tutorSectionImageUrl').value || '');

            // ✅ Branding
            formData.append('SiteName', document.getElementById('siteNameInput').value.trim() || 'Mashter Moshai');
            formData.append('NavbarLogoUrl', document.getElementById('navbarLogoUrlInput').value.trim() || '');
            formData.append('FooterLogoUrl', document.getElementById('footerLogoUrlInput').value.trim() || '');
            formData.append('LogoSizePx', getCurrentLogoSize());
            formData.append('LogoShape', getCurrentShape());

            // Slider
            formData.append('SliderEnabled', document.getElementById('sliderEnabled').checked);
            formData.append('SliderIntervalSeconds', sliderInterval);

            // Colors
            formData.append('PrimaryColor', document.getElementById('primaryColor').value);
            formData.append('SecondaryColor', document.getElementById('secondaryColor').value);
            formData.append('AccentColor', document.getElementById('accentColor').value);
            formData.append('TextColor', document.getElementById('textColor').value);
            formData.append('NavbarBgColor', document.getElementById('navbarBgColor').value);
            formData.append('FooterBgColor', document.getElementById('footerBgColor').value);
            formData.append('__RequestVerificationToken', getToken());

            const btn = document.getElementById('saveBtn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            fetch('/Admin/SaveSettings', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;

                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ ' + (data.message || 'Failed to save settings.'));
                    }
                })
                .catch(err => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    alert('Error: ' + err.message);
                });
        }

        // ============================================================
        // ===== BECOME A TUTOR SECTION (Home) =====
        // ============================================================
        function btHighlight(text) {
            return escapeHtml(text || '').replace(/\[([^\[\]]{1,60})\]/g, '<span class="hl">$1</span>');
        }
        function btPreview() {
            var v = function (id) { return document.getElementById(id).value; };
            document.getElementById('btpBadge').textContent = v('bt_badge').trim();
            document.getElementById('btpKicker').textContent = v('bt_kicker');
            document.getElementById('btpTitle').innerHTML = btHighlight(v('bt_title'));
            document.getElementById('btpL1').innerHTML = btHighlight(v('bt_line1'));
            document.getElementById('btpL2').innerHTML = btHighlight(v('bt_line2'));
            document.getElementById('btpPerks').innerHTML = v('bt_perks').split(/\r?\n/).map(function (x) { return x.trim(); })
                .filter(Boolean).slice(0, 6).map(function (x) { return '<li>' + escapeHtml(x) + '</li>'; }).join('');
            document.querySelector('.bt-prev-wrap').classList.toggle('off', !document.getElementById('bt_enabled').checked);
        }
        document.addEventListener('DOMContentLoaded', btPreview);

        // ===== Profile promo image =====
        document.getElementById('ppFile').addEventListener('change', function () {
            var f = this.files[0]; if (!f) return;
            if (f.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); this.value = ''; return; }
            var fd = new FormData(); fd.append('promoImage', f); fd.append('__RequestVerificationToken', getToken());
            var lbl = document.getElementById('ppUploadLbl'), html = lbl.innerHTML;
            lbl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            var inp = this;
            fetch('/Admin/UploadProfilePromo', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    lbl.innerHTML = html; inp.value = '';
                    if (!d.success) { alert('❌ ' + (d.message || 'Upload failed')); return; }
                    document.getElementById('pp_image').value = d.imageUrl;
                    var pv = document.getElementById('ppPreview'); pv.src = d.imageUrl; pv.style.opacity = 1;
                    alert('✅ ছবি আপলোড হয়েছে — এখন Save চাপুন।');
                })
                .catch(function (e) { lbl.innerHTML = html; alert('Error: ' + e.message); });
        });
        function savePromo(reset) {
            if (reset && !confirm('আগের (ডিফল্ট) ছবিতে ফেরত যাবেন?')) return;
            var fd = new FormData();
            fd.append('pp_image', document.getElementById('pp_image').value);
            fd.append('pp_link', document.getElementById('pp_link').value);
            fd.append('pp_enabled', document.getElementById('pp_enabled').checked ? 'true' : 'false');
            if (reset) fd.append('reset', 'true');
            fd.append('__RequestVerificationToken', getToken());
            var btn = document.getElementById('ppSaveBtn'), html = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; btn.disabled = true;
            fetch('/Admin/SaveProfilePromo', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    btn.innerHTML = html; btn.disabled = false;
                    if (d.success && d.pp_image) {
                        document.getElementById('pp_image').value = d.pp_image;
                        document.getElementById('pp_link').value = d.pp_link || '';
                        document.getElementById('pp_enabled').checked = d.pp_enabled !== '0';
                        document.getElementById('ppPrevOff').hidden = d.pp_enabled !== '0';
                        var pv = document.getElementById('ppPreview'); pv.src = d.pp_image; pv.style.opacity = 1;
                    }
                    alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
                })
                .catch(function (e) { btn.innerHTML = html; btn.disabled = false; alert('Error: ' + e.message); });
        }

        // ===== টিউশনের অপশন (লোকেশন / ক্লাস / বিষয়) =====
        (function () {
            var OPT = { locations: <?= js((object)$topt['locations']) ?>, classes: <?= js($topt['classes']) ?>, subjects: <?= js($topt['subjects']) ?> };
            var cur = 'locations';
            function esc(t) { return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
            function has(list, v) { return list.some(function (x) { return x.toLowerCase() === v.toLowerCase(); }); }
            function counts() {
                var n = 0; Object.keys(OPT.locations).forEach(function (d) { n += 1 + OPT.locations[d].length; });
                document.getElementById('optCntLoc').textContent = n;
                document.getElementById('optCntCls').textContent = OPT.classes.length;
                document.getElementById('optCntSub').textContent = OPT.subjects.length;
            }
            function chips(list, kind) {
                return list.map(function (v, i) { return '<span class="opt-chip">' + esc(v) + '<button type="button" data-opt-rm="' + kind + '" data-i="' + i + '" aria-label="Remove">✕</button></span>'; }).join('');
            }
            function paintList(kind) { document.getElementById(kind === 'classes' ? 'optClasses' : 'optSubjects').innerHTML = chips(OPT[kind], kind); counts(); }
            function paintLocs() {
                var q = document.getElementById('optLocSearch').value.trim().toLowerCase();
                var sel = document.getElementById('optDistrict'), keep = sel.value;
                sel.innerHTML = '<option value="">— জেলা বাছাই করুন —</option>' + Object.keys(OPT.locations).map(function (d) { return '<option value="' + esc(d) + '">' + esc(d) + '</option>'; }).join('') + '<option value="__new">+ নতুন জেলা / শহর</option>';
                sel.value = keep;
                document.getElementById('optLocs').innerHTML = Object.keys(OPT.locations).filter(function (d) {
                    return !q || d.toLowerCase().indexOf(q) > -1 || OPT.locations[d].some(function (a) { return a.toLowerCase().indexOf(q) > -1; });
                }).map(function (d) {
                    var areas = OPT.locations[d];
                    return '<div class="opt-d"><div class="opt-d-h"><i class="fas fa-location-dot" style="color:#02B8F5;"></i> ' + esc(d) + ' <small>' + areas.length + ' এলাকা</small>'
                        + '<button type="button" class="del" data-opt-rmd="' + esc(d) + '"><i class="fas fa-trash-can"></i> জেলা বাদ দিন</button></div>'
                        + (areas.length ? '<div class="opt-chips">' + areas.map(function (a, i) { return '<span class="opt-chip">' + esc(a) + '<button type="button" data-opt-rma="' + esc(d) + '" data-i="' + i + '" aria-label="Remove">✕</button></span>'; }).join('') + '</div>' : '')
                        + '</div>';
                }).join('') || '<p style="padding:14px;margin:0;color:#94A3B8;">কিছু পাওয়া যায়নি।</p>';
                counts();
            }
            document.querySelectorAll('[data-opt-tab]').forEach(function (b) {
                b.addEventListener('click', function () {
                    cur = b.getAttribute('data-opt-tab');
                    document.querySelectorAll('[data-opt-tab]').forEach(function (x) { x.classList.toggle('active', x === b); });
                    document.querySelectorAll('[data-opt-pane]').forEach(function (p) { p.hidden = p.getAttribute('data-opt-pane') !== cur; });
                });
            });
            document.getElementById('optDistrict').addEventListener('change', function () { document.getElementById('optNewDistrict').hidden = this.value !== '__new'; });
            document.getElementById('optLocSearch').addEventListener('input', paintLocs);
            document.getElementById('optAddLoc').addEventListener('click', function () {
                var d = document.getElementById('optDistrict').value, a = document.getElementById('optArea').value.trim();
                if (d === '__new') d = document.getElementById('optNewDistrict').value.trim();
                if (!d) { alert('জেলা বাছাই করুন বা নতুন জেলার নাম লিখুন।'); return; }
                var exist = Object.keys(OPT.locations).filter(function (x) { return x.toLowerCase() === d.toLowerCase(); })[0];
                if (!exist) { OPT.locations[d] = []; exist = d; }
                if (a) { if (has(OPT.locations[exist], a)) { alert('এই এলাকা আগেই আছে।'); return; } OPT.locations[exist].push(a); }
                document.getElementById('optArea').value = ''; document.getElementById('optNewDistrict').value = '';
                document.getElementById('optDistrict').value = exist; document.getElementById('optNewDistrict').hidden = true;
                document.getElementById('optLocSearch').value = exist; paintLocs();
            });
            document.querySelectorAll('[data-opt-add]').forEach(function (b) {
                var kind = b.getAttribute('data-opt-add'), inp = document.getElementById(kind === 'classes' ? 'optClassIn' : 'optSubjectIn');
                function add() {
                    var v = inp.value.trim(); if (!v) return;
                    if (has(OPT[kind], v)) { alert('এটা আগেই আছে।'); return; }
                    OPT[kind].push(v); inp.value = ''; paintList(kind); inp.focus();
                }
                b.addEventListener('click', add);
                inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); add(); } });
            });
            document.getElementById('optSettings').addEventListener('click', function (e) {
                var r = e.target.closest('[data-opt-rm]');
                if (r) { var k = r.getAttribute('data-opt-rm'); OPT[k].splice(+r.getAttribute('data-i'), 1); paintList(k); return; }
                var ra = e.target.closest('[data-opt-rma]');
                if (ra) { OPT.locations[ra.getAttribute('data-opt-rma')].splice(+ra.getAttribute('data-i'), 1); paintLocs(); return; }
                var rd = e.target.closest('[data-opt-rmd]');
                if (rd && confirm(rd.getAttribute('data-opt-rmd') + ' জেলা আর এর সব এলাকা বাদ দেবেন?')) { delete OPT.locations[rd.getAttribute('data-opt-rmd')]; paintLocs(); }
            });
            async function send(reset) {
                if (reset && !confirm('এই তালিকা আগের (ডিফল্ট) অবস্থায় ফেরত যাবে। নিশ্চিত?')) return;
                var fd = new FormData(); fd.append('which', cur); fd.append('data', JSON.stringify(OPT[cur])); if (reset) fd.append('reset', 'true');
                fd.append('__RequestVerificationToken', getToken());
                var btn = document.getElementById('optSave'), html = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                try {
                    var d = await (await fetch('/Admin/SaveTuitionOptions', { method: 'POST', body: fd })).json();
                    if (d.success) { OPT[cur] = d.data; if (cur === 'locations') paintLocs(); else paintList(cur); }
                    alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
                } catch (err) { alert('Error: ' + err.message); }
                btn.disabled = false; btn.innerHTML = html;
            }
            document.getElementById('optSave').addEventListener('click', function () { send(false); });
            document.getElementById('optReset').addEventListener('click', function () { send(true); });
            paintLocs(); paintList('classes'); paintList('subjects');
        })();

        // ===== Home → Key Features এর ছবি =====
        function kfShow(i, url) { var p = document.getElementById('kfPv' + i); if (url) { p.src = url; p.hidden = false; } else { p.hidden = true; p.removeAttribute('src'); } }
        document.querySelectorAll('input[data-kf-url]').forEach(function (inp) { inp.addEventListener('input', function () { kfShow(inp.getAttribute('data-kf-url'), inp.value.trim()); }); });
        document.querySelectorAll('[data-kf-clear]').forEach(function (b) { b.addEventListener('click', function () { var i = b.getAttribute('data-kf-clear'); document.getElementById('kf_img_' + i).value = ''; kfShow(i, ''); }); });
        document.querySelectorAll('input[type=file][data-kf]').forEach(function (inp) {
            inp.addEventListener('change', function () {
                var i = inp.getAttribute('data-kf'), f = inp.files[0]; if (!f) return;
                if (f.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); inp.value = ''; return; }
                var fd = new FormData(); fd.append('promoImage', f); fd.append('__RequestVerificationToken', getToken());
                var lbl = document.getElementById('kfUp' + i), html = lbl.innerHTML;
                lbl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                fetch('/Admin/UploadProfilePromo', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        lbl.innerHTML = html; inp.value = '';
                        if (!d.success) { alert('❌ ' + (d.message || 'Upload failed')); return; }
                        document.getElementById('kf_img_' + i).value = d.imageUrl; kfShow(i, d.imageUrl);
                    })
                    .catch(function (e) { lbl.innerHTML = html; alert('Error: ' + e.message); });
            });
        });
        function saveKeyFeatures(reset) {
            if (reset && !confirm('সব Key Features এর ছবি ডিফল্টে ফেরত যাবে?')) return;
            var fd = new FormData();
            document.querySelectorAll('input[data-kf-url]').forEach(function (inp) { fd.append('kf_img_' + inp.getAttribute('data-kf-url'), inp.value.trim()); });
            if (reset) fd.append('reset', 'true');
            fd.append('__RequestVerificationToken', getToken());
            var btn = document.getElementById('kfSaveBtn'), html = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; btn.disabled = true;
            fetch('/Admin/SaveKeyFeatures', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    btn.innerHTML = html; btn.disabled = false;
                    if (d.success && d.images) Object.keys(d.images).forEach(function (k) { var i = k.replace('kf_img_', ''); document.getElementById(k).value = d.images[k]; kfShow(i, d.images[k]); });
                    alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
                })
                .catch(function (e) { btn.innerHTML = html; btn.disabled = false; alert('Error: ' + e.message); });
        }

        // ===== Tuition Circular / Find Tutor banner =====
        document.querySelectorAll('input[type=file][data-banner]').forEach(function (inp) {
            inp.addEventListener('change', function () {
                var k = inp.getAttribute('data-banner'), f = inp.files[0]; if (!f) return;
                if (f.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); inp.value = ''; return; }
                var fd = new FormData(); fd.append('promoImage', f); fd.append('__RequestVerificationToken', getToken());
                var lbl = document.getElementById(k + 'bUploadLbl'), html = lbl.innerHTML;
                lbl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                fetch('/Admin/UploadProfilePromo', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        lbl.innerHTML = html; inp.value = '';
                        if (!d.success) { alert('❌ ' + (d.message || 'Upload failed')); return; }
                        document.getElementById(k + '_banner').value = d.imageUrl;
                        var pv = document.getElementById(k + 'bPreview'); pv.src = d.imageUrl; pv.style.opacity = 1;
                        alert('✅ ছবি আপলোড হয়েছে — এখন Save চাপুন।');
                    })
                    .catch(function (e) { lbl.innerHTML = html; alert('Error: ' + e.message); });
            });
        });
        function saveBanner(k, which, reset) {
            if (reset && !confirm('আগের (ডিফল্ট) ব্যানারে ফেরত যাবেন?')) return;
            var fd = new FormData();
            fd.append('which', which);
            fd.append(k + '_banner', document.getElementById(k + '_banner').value);
            fd.append(k + '_banner_enabled', document.getElementById(k + '_banner_enabled').checked ? 'true' : 'false');
            if (reset) fd.append('reset', 'true');
            fd.append('__RequestVerificationToken', getToken());
            var btn = document.getElementById(k + 'bSaveBtn'), html = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; btn.disabled = true;
            fetch('/Admin/SaveCircularBanner', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    btn.innerHTML = html; btn.disabled = false;
                    if (d.success && d[k + '_banner']) {
                        document.getElementById(k + '_banner').value = d[k + '_banner'];
                        document.getElementById(k + '_banner_enabled').checked = d[k + '_banner_enabled'] !== '0';
                        document.getElementById(k + 'bPrevOff').hidden = d[k + '_banner_enabled'] !== '0';
                        var pv = document.getElementById(k + 'bPreview'); pv.src = d[k + '_banner']; pv.style.opacity = 1;
                    }
                    alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
                })
                .catch(function (e) { btn.innerHTML = html; btn.disabled = false; alert('Error: ' + e.message); });
        }

        // ===== Teacher profile promo =====
        function tpPaint() {
            var v = document.getElementById('tp_image').value.trim();
            document.getElementById('tpPrevBox').hidden = !v;
            document.getElementById('tpPrevCard').hidden = !!v;
            if (v) { var pv = document.getElementById('tpPreview'); pv.src = v; pv.style.opacity = 1; }
        }
        document.getElementById('tp_image').addEventListener('input', tpPaint);
        document.getElementById('tpClearImg').addEventListener('click', function () { document.getElementById('tp_image').value = ''; tpPaint(); });
        document.getElementById('tpFile').addEventListener('change', function () {
            var f = this.files[0]; if (!f) return;
            if (f.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); this.value = ''; return; }
            var fd = new FormData(); fd.append('promoImage', f); fd.append('__RequestVerificationToken', getToken());
            var lbl = document.getElementById('tpUploadLbl'), html = lbl.innerHTML;
            lbl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            var inp = this;
            fetch('/Admin/UploadProfilePromo', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    lbl.innerHTML = html; inp.value = '';
                    if (!d.success) { alert('❌ ' + (d.message || 'Upload failed')); return; }
                    document.getElementById('tp_image').value = d.imageUrl; tpPaint();
                    alert('✅ ছবি আপলোড হয়েছে — এখন Save চাপুন।');
                })
                .catch(function (e) { lbl.innerHTML = html; alert('Error: ' + e.message); });
        });
        function saveTeacherPromo(reset) {
            if (reset && !confirm('ডিফল্ট কার্ডে ফেরত যাবেন?')) return;
            var fd = new FormData();
            fd.append('which', 'teacher');
            fd.append('tp_image', document.getElementById('tp_image').value);
            fd.append('tp_link', document.getElementById('tp_link').value);
            fd.append('tp_enabled', document.getElementById('tp_enabled').checked ? 'true' : 'false');
            if (reset) fd.append('reset', 'true');
            fd.append('__RequestVerificationToken', getToken());
            var btn = document.getElementById('tpSaveBtn'), html = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; btn.disabled = true;
            fetch('/Admin/SaveProfilePromo', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    btn.innerHTML = html; btn.disabled = false;
                    if (d.success) {
                        document.getElementById('tp_image').value = d.tp_image || '';
                        document.getElementById('tp_link').value = d.tp_link || '';
                        document.getElementById('tp_enabled').checked = d.tp_enabled !== '0';
                        document.getElementById('tpPrevOff').hidden = d.tp_enabled !== '0';
                        tpPaint();
                    }
                    alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
                })
                .catch(function (e) { btn.innerHTML = html; btn.disabled = false; alert('Error: ' + e.message); });
        }

        function saveBecomeTutor() {
            var fd = new FormData();
            ['bt_badge', 'bt_kicker', 'bt_title', 'bt_line1', 'bt_line2', 'bt_perks'].forEach(function (id) {
                fd.append(id, document.getElementById(id).value);
            });
            fd.append('bt_enabled', document.getElementById('bt_enabled').checked ? 'true' : 'false');
            fd.append('__RequestVerificationToken', getToken());

            var btn = document.getElementById('btSaveBtn');
            var html = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;
            fetch('/Admin/SaveBecomeTutorSection', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    btn.innerHTML = html; btn.disabled = false;
                    alert((d.success ? '✅ ' : '❌ ') + (d.message || ''));
                })
                .catch(function (e) { btn.innerHTML = html; btn.disabled = false; alert('Error: ' + e.message); });
        }

        // ===== Enter key on hero URL input =====
        document.getElementById('heroImageUrlInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addImageFromUrl();
            }
        });

        // ===== Init on load =====
        document.addEventListener('DOMContentLoaded', function () {
            updateLogoPreview();
        });

        console.log('✅ Settings page loaded');
    </script>
<?php section_end(); ?>
