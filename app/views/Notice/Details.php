<?php
$ViewData['Title'] = $Model->Title;
$moreNews = is_array($ViewBag->MoreNews) ? $ViewBag->MoreNews : [];
?>

<style>
    .detail-page {
        max-width: 1280px;
        margin: 0 auto;
        padding: 30px 20px 70px;
    }

    /* ===== Top Bar (Back + Badges) ===== */
    .detail-topbar {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #08224A;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 8px;
        transition: background 0.2s;
    }

        .back-link:hover {
            background: #CFF2FC;
            color: #08224A;
        }

    .topbar-divider {
        width: 1px;
        height: 20px;
        background: #B9D6E6;
    }

    .topbar-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

        .topbar-badge.category {
            background: #CFF2FC;
            color: #08224A;
        }

            .topbar-badge.category.workshop {
                background: #02B8F5;
                color: white;
            }

            .topbar-badge.category.course {
                background: #FFEBB8;
                color: #8a5a00;
            }

            .topbar-badge.category.event {
                background: #E7DCF5;
                color: #5a3fa0;
            }

            .topbar-badge.category.result {
                background: #D4EDDA;
                color: #155724;
            }

            .topbar-badge.category.announcement {
                background: #FFEBB8;
                color: #8a5a00;
            }

        .topbar-badge.icon-badge {
            background: #DCECF5;
            color: #64748B;
            padding: 6px 10px;
            border-radius: 50%;
            font-size: 12px;
        }

    /* ===== Layout Grid ===== */
    .detail-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 32px;
        align-items: start;
    }

    /* ===== Main Article ===== */
    .article-title {
        font-family: 'Georgia', serif;
        font-size: 40px;
        font-weight: 700;
        color: #08224A;
        line-height: 1.25;
        margin: 0 0 24px;
        letter-spacing: -0.5px;
    }

    .article-hero {
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

        .article-hero img {
            width: 100%;
            max-height: 520px;
            object-fit: cover;
            display: block;
        }

    .article-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #64748B;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #DCECF5;
    }

        .article-meta i {
            color: #028EA5;
        }

    .article-content {
        color: #334155;
        font-size: 16.5px;
        line-height: 1.9;
        white-space: pre-line;
        letter-spacing: 0.1px;
    }

        .article-content p {
            margin-bottom: 18px;
        }

    /* ===== Sidebar: More News ===== */
    .more-news-card {
        background: white;
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
        position: sticky;
        top: 100px;
    }

    .more-news-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 22px;
        border-bottom: 1px solid #f0f0f0;
    }

        .more-news-header h4 {
            font-family: 'Georgia', serif;
            font-size: 20px;
            font-weight: 700;
            color: #08224A;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

            .more-news-header h4 i {
                color: #028EA5;
                font-size: 18px;
            }

        .more-news-header .view-all {
            color: #08224A;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: opacity 0.2s;
        }

            .more-news-header .view-all:hover {
                opacity: 0.7;
                text-decoration: underline;
            }

    .more-news-list {
        padding: 8px 0;
        max-height: calc(100vh - 220px);
        overflow-y: auto;
    }

        .more-news-list::-webkit-scrollbar {
            width: 5px;
        }

        .more-news-list::-webkit-scrollbar-thumb {
            background: #B9D6E6;
            border-radius: 10px;
        }

    .mn-item {
        display: flex;
        gap: 12px;
        padding: 12px 22px;
        text-decoration: none;
        color: inherit;
        transition: background 0.2s;
        border-left: 3px solid transparent;
    }

        .mn-item:hover {
            background: #F5FBFF;
            border-left-color: #02B8F5;
        }

        .mn-item img {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: #DCECF5;
        }

        /* ✅ Icon-based placeholder (Announcement-এর জন্য, ছবি নেই) */
        .mn-item .mn-icon-box {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #08224A, #134856);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFB300;
            font-size: 26px;
        }

    .mn-content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

        .mn-content .mn-title {
            font-family: 'Georgia', serif;
            font-size: 14px;
            font-weight: 700;
            color: #08224A;
            line-height: 1.4;
            margin: 0 0 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .mn-content .mn-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            color: #64748B;
            font-weight: 500;
            margin-bottom: 6px;
        }

            .mn-content .mn-date i {
                color: #028EA5;
                font-size: 11px;
            }

        .mn-content .mn-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.2px;
            background: #CFF2FC;
            color: #08224A;
            align-self: flex-start;
        }

            .mn-content .mn-badge.workshop {
                background: #02B8F5;
                color: white;
            }

            .mn-content .mn-badge.course {
                background: #FFEBB8;
                color: #8a5a00;
            }

            .mn-content .mn-badge.event {
                background: #E7DCF5;
                color: #5a3fa0;
            }

            .mn-content .mn-badge.result {
                background: #D4EDDA;
                color: #155724;
            }

            .mn-content .mn-badge.announcement {
                background: #FFEBB8;
                color: #8a5a00;
            }

    .mn-empty {
        text-align: center;
        padding: 40px 20px;
        color: #7A8CA0;
        font-size: 13.5px;
    }

        .mn-empty i {
            font-size: 36px;
            color: #DCECF5;
            margin-bottom: 10px;
            display: block;
        }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .detail-layout {
            grid-template-columns: 1fr;
        }

        .more-news-card {
            position: static;
        }

        .article-title {
            font-size: 30px;
        }
    }

    @media (max-width: 576px) {
        .detail-page {
            padding: 20px 14px 60px;
        }

        .article-title {
            font-size: 24px;
        }

        .article-hero img {
            max-height: 260px;
        }

        .article-content {
            font-size: 15.5px;
            line-height: 1.8;
        }

        .mn-item {
            padding: 12px 16px;
        }

            .mn-item img,
            .mn-item .mn-icon-box {
                width: 60px;
                height: 60px;
                font-size: 22px;
            }

        .more-news-header {
            padding: 14px 16px;
        }

            .more-news-header h4 {
                font-size: 17px;
            }
    }
</style>

<div class="detail-page">

    <!-- ===== Top Bar ===== -->
    <div class="detail-topbar">
        <a href="/Notice/Index" class="back-link">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="topbar-divider"></div>

        <?php
            $catClass = $Model->IsAnnouncement ? 'announcement' : (in_array($c = mb_strtolower((string)$Model->Category), ['workshop', 'course', 'event', 'result'], true) ? $c : '');
        ?>
        <span class="topbar-badge category <?= e($catClass) ?>">
            <?= e($Model->IsAnnouncement ? 'Announcement' : $Model->Category) ?>
        </span>

        <span class="topbar-badge icon-badge">
            <?php if ($Model->IsAnnouncement): ?>
                <i class="fas fa-bullhorn"></i>
            <?php else: ?>
                <i class="fas fa-newspaper"></i>
            <?php endif; ?>
        </span>
    </div>

    <!-- ===== Layout ===== -->
    <div class="detail-layout">

        <!-- LEFT: Main Article -->
        <article class="article-main">

            <h1 class="article-title"><?= e($Model->Title) ?></h1>

            <?php /* ✅ News হলে ছবি দেখাবে, Announcement হলে কোনো ছবি দেখাবে না */ ?>
            <?php if (!$Model->IsAnnouncement && !is_blank($Model->ImageUrl)): ?>
                <div class="article-hero">
                    <img src="<?= e($Model->ImageUrl) ?>" alt="<?= e($Model->Title) ?>" />
                </div>
            <?php endif; ?>

            <div class="article-meta">
                <i class="far fa-calendar"></i>
                <span><?= e(fmt_date($Model->PublishedDate, 'F d, Y')) ?></span>
            </div>

            <div class="article-content">
                <?= e($Model->Content) ?>
            </div>

        </article>

        <!-- RIGHT: More News Sidebar -->
        <aside class="more-news-card">
            <div class="more-news-header">
                <h4><i class="fas fa-newspaper"></i> More News</h4>
                <a href="/Notice/Index" class="view-all">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="more-news-list">
                <?php if (count($moreNews) === 0): ?>
                    <div class="mn-empty">
                        <i class="fas fa-newspaper"></i>
                        No more news available
                    </div>
                <?php else: ?>
                    <?php foreach ($moreNews as $n):
                        $badgeClass = $n->IsAnnouncement ? 'announcement' : (in_array($c = mb_strtolower((string)$n->Category), ['workshop', 'course', 'event', 'result'], true) ? $c : '');
                    ?>
                        <a href="/Notice/Details/<?= e($n->Id) ?>" class="mn-item">
                            <?php /* ✅ News-এ ছবি থাকলে ছবি, নাহলে bullhorn আইকন */ ?>
                            <?php if (!$n->IsAnnouncement && !is_blank($n->ImageUrl)): ?>
                                <img src="<?= e($n->ImageUrl) ?>" alt="<?= e($n->Title) ?>" />
                            <?php else: ?>
                                <div class="mn-icon-box">
                                    <?php if ($n->IsAnnouncement): ?>
                                        <i class="fas fa-bullhorn"></i>
                                    <?php else: ?>
                                        <i class="fas fa-newspaper"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mn-content">
                                <p class="mn-title"><?= e($n->Title) ?></p>
                                <span class="mn-date">
                                    <i class="far fa-calendar"></i>
                                    <?= e(fmt_date($n->PublishedDate, 'M d, Y')) ?>
                                </span>
                                <span class="mn-badge <?= e($badgeClass) ?>">
                                    <?= e($n->IsAnnouncement ? 'Announcement' : $n->Category) ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

    </div>
</div>