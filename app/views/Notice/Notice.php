<?php
$ViewData['Title'] = 'News & Announcements';
$news = is_array($Model) ? $Model : [];
$announcements = is_array($ViewBag->Announcements) ? $ViewBag->Announcements : [];
$searchQuery = (string)($ViewBag->SearchQuery ?? '');
$selectedCategory = (string)($ViewBag->SelectedCategory ?? 'all');
?>

<style>
    .notice-page {
        max-width: 1280px;
        margin: 0 auto;
        padding: 40px 20px 70px;
    }

    /* ===== Header ===== */
    .notice-header h1 {
        font-family: var(--font-heading);
        font-size: 42px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 10px;
        letter-spacing: -0.5px;
    }

    .notice-header p {
        color: #475569;
        font-size: 16px;
        line-height: 1.6;
        margin: 0 0 26px;
        max-width: 720px;
    }

    /* ===== Search Bar ===== */
    .notice-search {
        display: flex;
        align-items: center;
        background: white;
        border: 1.5px solid #DCECF5;
        border-radius: 12px;
        padding: 6px 6px 6px 16px;
        gap: 10px;
        transition: border-color 0.25s;
        max-width: 100%;
    }

        .notice-search:focus-within {
            border-color: #02B8F5;
            box-shadow: 0 0 0 3px rgba(2,184,245,0.1);
        }

        .notice-search i {
            color: #7A8CA0;
            font-size: 16px;
            flex-shrink: 0;
        }

        .notice-search input {
            flex: 1;
            border: none;
            outline: none;
            padding: 10px 0;
            font-size: 14.5px;
            background: transparent;
            color: #08224A;
            min-width: 0;
        }

            .notice-search input::placeholder {
                color: #A0B3C6;
            }

        .notice-search button {
            background: #02B8F5;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 9px;
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
            transition: opacity 0.2s;
            flex-shrink: 0;
        }

            .notice-search button:hover {
                opacity: 0.9;
            }

    /* ===== Layout Grid ===== */
    .notice-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
        margin-top: 30px;
        align-items: start;
    }

    /* ===== Left: Latest News ===== */
    .notice-left-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

        .notice-left-header h2 {
            font-family: var(--font-heading);
            font-size: 26px;
            font-weight: 700;
            color: #08224A;
            margin: 0;
        }

        .notice-left-header .count {
            color: #64748B;
            font-size: 14px;
            font-weight: 500;
        }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
    }

    .news-card {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        transition: all 0.3s;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
    }

        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(2,184,245,0.12);
            border-color: rgba(2,184,245,0.25);
        }

        .news-card .card-img {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #DCECF5;
        }

            .news-card .card-img img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s;
            }

        .news-card:hover .card-img img {
            transform: scale(1.05);
        }

        /* ✅ News ছবি ছাড়া হলে icon placeholder */
        .news-card .card-img-placeholder {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: linear-gradient(135deg, #CFF2FC, #CFF2FC);
            display: flex;
            align-items: center;
            justify-content: center;
        }

            .news-card .card-img-placeholder i {
                font-size: 56px;
                color: #028EA5;
                opacity: 0.35;
            }

        .news-card .card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: white;
            color: #08224A;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

            .news-card .card-badge.workshop {
                background: #02B8F5;
                color: white;
            }

            .news-card .card-badge.course {
                background: #E09A00;
                color: white;
            }

            .news-card .card-badge.event {
                background: #6f42c1;
                color: white;
            }

            .news-card .card-badge.result {
                background: #2e8b57;
                color: white;
            }

            .news-card .card-badge.announcement {
                background: #FFB300;
                color: #08224A;
            }

        .news-card .card-body {
            padding: 18px 20px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .news-card .card-date {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #08224A;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 8px;
        }

            .news-card .card-date i {
                font-size: 12px;
            }

        .news-card .card-title {
            font-family: var(--font-heading);
            font-size: 16px;
            font-weight: 700;
            color: #08224A;
            line-height: 1.4;
            margin: 0 0 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-card .card-excerpt {
            color: #475569;
            font-size: 13.5px;
            line-height: 1.6;
            margin: 0 0 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .news-card .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
            color: #08224A;
            font-size: 13px;
            font-weight: 600;
        }

            .news-card .card-footer i {
                font-size: 11px;
                transition: transform 0.2s;
            }

        .news-card:hover .card-footer i {
            transform: translateX(3px);
        }

    /* ===== Right: Announcements ===== */
    .announcements-card {
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
        position: sticky;
        top: 100px;
    }

    .ann-header {
        background: #134856;
        color: white;
        padding: 22px 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .ann-header-left h3 {
        font-family: var(--font-heading);
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

        .ann-header-left h3 i {
            font-size: 20px;
            color: #FFB300;
        }

    .ann-header-left p {
        margin: 0;
        font-size: 13px;
        opacity: 0.85;
    }

    .ann-header-count {
        background: rgba(244,199,68,0.2);
        color: #FFB300;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ann-body {
        padding: 22px 20px;
        min-height: 200px;
        max-height: 620px;
        overflow-y: auto;
    }

        .ann-body::-webkit-scrollbar {
            width: 5px;
        }

        .ann-body::-webkit-scrollbar-thumb {
            background: #B9D6E6;
            border-radius: 10px;
        }

    .ann-item {
        display: flex;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #f2f2f2;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s;
    }

        .ann-item:last-child {
            border-bottom: none;
        }

        .ann-item:hover {
            padding-left: 4px;
        }

    .ann-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #FFEBB8;
        color: #8a5a00;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .ann-item-content {
        flex: 1;
        min-width: 0;
    }

        .ann-item-content .ann-title {
            font-size: 14px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 4px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ann-item-content .ann-desc {
            font-size: 12.5px;
            color: #64748B;
            margin: 0 0 4px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ann-item-content .ann-date {
            font-size: 11.5px;
            color: #08224A;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

    .ann-empty {
        text-align: center;
        padding: 50px 20px;
        color: #7A8CA0;
    }

        .ann-empty i {
            font-size: 44px;
            color: #B9D6E6;
            margin-bottom: 12px;
            display: block;
        }

        .ann-empty p {
            margin: 0;
            font-size: 14px;
        }

    /* ===== Empty state (News) ===== */
    .news-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 14px;
        border: 1px dashed #DCECF5;
    }

        .news-empty i {
            font-size: 52px;
            color: #DCECF5;
            margin-bottom: 14px;
            display: block;
        }

        .news-empty h4 {
            color: #08224A;
            margin: 0 0 6px;
            font-size: 18px;
        }

        .news-empty p {
            color: #7A8CA0;
            font-size: 14px;
            margin: 0;
        }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .notice-layout {
            grid-template-columns: 1fr;
        }

        .announcements-card {
            position: static;
            order: -1;
        }

        .notice-header h1 {
            font-size: 32px;
        }

        .news-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .notice-page {
            padding: 24px 14px 60px;
        }

        .notice-header h1 {
            font-size: 26px;
        }

        .notice-header p {
            font-size: 14px;
        }

        .notice-search {
            padding: 5px 5px 5px 14px;
        }

            .notice-search button {
                padding: 9px 16px;
                font-size: 13px;
            }

        .news-card .card-img,
        .news-card .card-img-placeholder {
            height: 170px;
        }

        .ann-header {
            padding: 18px 18px;
        }

        .ann-header-left h3 {
            font-size: 18px;
        }
    }
</style>

<section class="notice-page">

    <!-- Header -->
    <div class="notice-header">
        <h1>News &amp; Announcements</h1>
        <p>Stay updated with the latest workshops, new courses, events, competition results, and important announcements from <?= e(site_settings()->GetSafeSiteName()) ?>.</p>
    </div>

    <!-- Search Bar -->
    <form method="get" action="/Notice">
        <div class="notice-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Search news by title, content or category..." />
            <button type="submit">
                <i class="fas fa-search me-1"></i> Search
            </button>
        </div>
    </form>

    <!-- Layout Grid -->
    <div class="notice-layout">

        <!-- Left: Latest News -->
        <div>
            <div class="notice-left-header">
                <h2>Latest News</h2>
                <span class="count"><?= count($news) ?> News</span>
            </div>

            <div class="news-grid">
                <?php if (count($news) === 0): ?>
                    <div class="news-empty">
                        <i class="fas fa-newspaper"></i>
                        <h4>No news found</h4>
                        <p>Try a different search keyword or check back later.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($news as $n):
                        $catClass = $n->IsAnnouncement ? 'announcement' : (in_array($c = mb_strtolower((string)$n->Category), ['workshop', 'course', 'event', 'result'], true) ? $c : '');
                    ?>
                        <a href="/Notice/Details/<?= e($n->Id) ?>" class="news-card">
                            <?php /* ✅ News-এ ছবি থাকলে ছবি, নাহলে icon placeholder */ ?>
                            <?php if (!is_blank($n->ImageUrl)): ?>
                                <div class="card-img">
                                    <img src="<?= e($n->ImageUrl) ?>" alt="<?= e($n->Title) ?>" />
                                    <span class="card-badge <?= e($catClass) ?>">
                                        <?= e($n->IsAnnouncement ? 'Announcement' : $n->Category) ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="card-img-placeholder">
                                    <i class="fas fa-newspaper"></i>
                                    <span class="card-badge <?= e($catClass) ?>">
                                        <?= e($n->IsAnnouncement ? 'Announcement' : $n->Category) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="card-date">
                                    <i class="far fa-calendar"></i>
                                    <?= e(fmt_date($n->PublishedDate, 'F d, Y')) ?>
                                </div>
                                <h3 class="card-title"><?= e($n->Title) ?></h3>
                                <p class="card-excerpt"><?= e($n->Content) ?></p>
                                <div class="card-footer">
                                    <span>Read more</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Announcements -->
        <aside class="announcements-card">
            <div class="ann-header">
                <div class="ann-header-left">
                    <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
                    <p>Important updates from <?= e(site_settings()->GetSafeSiteName()) ?></p>
                </div>
                <span class="ann-header-count"><?= count($announcements) ?> Active</span>
            </div>
            <div class="ann-body">
                <?php if (count($announcements) === 0): ?>
                    <div class="ann-empty">
                        <i class="fas fa-bullhorn"></i>
                        <p>No announcements available</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $a): ?>
                        <a href="/Notice/Details/<?= e($a->Id) ?>" class="ann-item">
                            <div class="ann-item-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="ann-item-content">
                                <p class="ann-title"><?= e($a->Title) ?></p>
                                <p class="ann-desc"><?= e($a->Content) ?></p>
                                <span class="ann-date">
                                    <i class="far fa-calendar"></i>
                                    <?= e(fmt_date($a->PublishedDate, 'F d, Y')) ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

    </div>
</section>