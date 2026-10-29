<?php
/**
 * স্টুডেন্ট / টিচারের প্রোফাইল (লার্নিং প্রোফাইল ডিজাইন)
 * বাম: মেনু · মাঝে: স্ট্যাট কার্ড + পোস্ট · ডান: About Me + My Tutors
 * সেকশন: overview | profile | tutors | friends | circulars | saved | goals | activities | settings
 */
$u = $Model->User;
$rel = $Model->Relation;
$isOwner = $Model->IsOwner;
$isTeacher = $u->Role === 'Teacher';
$name = !is_blank($u->FullName) ? $u->FullName : $u->UserName;
$firstName = explode(' ', trim($name))[0];
$ViewData['Title'] = $name;
$img = is_blank($u->ProfileImageLink) ? null : $u->ProfileImageLink;
$ti = $Model->TeacherInfo;
$canPrivate = $Model->CanSeePrivate;
$tab = $Model->Tab;
$friends = $Model->Friends;
$intro = $Model->Intro;
$streak = $Model->Streak;
$goals = $Model->Goals;
$primaryGoal = $goals[0] ?? null;
if ($primaryGoal && !$primaryGoal->IsPrimary) foreach ($goals as $g) if ($g->IsPrimary) { $primaryGoal = $g; break; }
$connected = $Model->Connected;
$saved = $Model->SavedItems;
$cover = $Model->Extras->CoverImageUrl;
$coverY = $Model->Extras->CoverPosY;
$location = implode(', ', array_filter([trim($intro->City), trim((string)$u->Country)]));
$roleLabel = $isTeacher ? 'Teacher' : 'Student';
$peopleLabel = $isTeacher ? 'My Students' : 'My Tutors';
$isAdminViewer = $Model->IsAdminViewer;

$me = obj(['Id' => (int)current_user_id(), 'Name' => session_get('UserFullName') ?? session_get('UserName') ?? 'User', 'Image' => session_get('ProfileImageLink')]);

// বাম মেনু: [key, লেবেল, আইকন, শুধু-মালিক?]
$nav = [
    ['overview', 'Overview', 'fa-house', false],
    ['profile', $isOwner ? 'My Profile' : 'Profile', 'fa-user', false],
    ['tutors', $peopleLabel, $isTeacher ? 'fa-user-graduate' : 'fa-chalkboard-user', true],
    ['friends', 'Friends', 'fa-user-group', false],
    ['circulars', 'My Circulars', 'fa-file-lines', true],
    ['saved', 'Saved Items', 'fa-bookmark', true],
    ['goals', $isOwner ? 'My Goals' : 'Goals', 'fa-bullseye', false],
    ['activities', $isOwner ? 'My Activities' : 'Activities', 'fa-chart-simple', false],
    ['settings', 'Settings', 'fa-gear', true],
];
$nav = array_values(array_filter($nav, fn($n) => (!$n[3] || $isOwner) && !($n[0] === 'circulars' && $isTeacher)));

$days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$savedThumbs = array_slice(array_values(array_filter($saved, fn($s) => $s->Available && $s->Image)), 0, 3);
$typeLabel = ['post' => ['Post', 'fa-newspaper'], 'tutor' => ['Tutor', 'fa-chalkboard-user'], 'circular' => ['Circular', 'fa-bullhorn'],
              'tuition' => ['Tuition', 'fa-clipboard-list'], 'notice' => ['Notice', 'fa-bell']];
$fmtMin = function (int $m): string { $h = intdiv($m, 60); $r = $m % 60; return $h ? $h . 'h' . ($r ? ' ' . $r . 'm' : '') : $r . 'm'; };
?>
<?= csrf_field() ?>
<?php partial('Friends/_Assets'); ?>
<?php partial('Post/_Assets', ['me' => $me]); ?>

<?php partial('Profile/_ShowStyles'); ?>

<div class="pf-page">
    <div class="pf-wrap">
        <!-- ======================= HEADER ======================= -->
        <div class="pf-card pf-head">
            <div class="ps-cover<?= $cover ? ' has-img' : '' ?>" id="psCover">
                <img id="psCoverImg" alt="Cover photo" <?= $cover ? 'src="' . e($cover) . '"' : 'hidden' ?> style="object-position: 50% <?= (int)$coverY ?>%;" draggable="false" />
                <?php if ($isOwner): ?>
                    <div class="cv-hint" id="cvHint"><i class="fas fa-up-down"></i> উপরে-নিচে টেনে ছবিটা ঠিক জায়গায় বসান</div>
                    <div class="cv-bar" id="cvBar">
                        <button type="button" class="cv-btn ghost" data-cv="cancel">Cancel</button>
                        <button type="button" class="cv-btn primary" data-cv="save"><i class="fas fa-check"></i> Save changes</button>
                    </div>
                    <div class="cv-edit-wrap" id="cvEditWrap">
                        <button type="button" class="cv-btn light" data-cv="menu"><i class="fas fa-camera"></i> <span><?= $cover ? 'Edit Cover' : 'Add Cover' ?></span></button>
                        <div class="cv-menu" id="cvMenu">
                            <button type="button" data-cv="upload"><i class="fas fa-upload"></i> Upload photo</button>
                            <button type="button" data-cv="reposition" <?= $cover ? '' : 'hidden' ?>><i class="fas fa-arrows-up-down"></i> Reposition</button>
                            <button type="button" data-cv="remove" class="danger" <?= $cover ? '' : 'hidden' ?>><i class="fas fa-trash-can"></i> Remove</button>
                        </div>
                    </div>
                    <input type="file" id="cvFile" accept="image/jpeg,image/png,image/webp" hidden />
                <?php endif; ?>
            </div>

            <div class="pf-info">
                <div class="ps-avatar-wrap">
                    <div class="ps-avatar" id="psAvatar" role="button" tabindex="0" aria-label="Profile picture">
                        <?= e(initials($name, 'U')) ?>
                        <?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($name) ?>" onerror="this.remove()" /><?php endif; ?>
                    </div>
                    <?php if ($isOwner): ?>
                        <button type="button" class="ps-avatar-edit" id="avEditBtn" title="প্রোফাইল ছবি বদলান" aria-label="Update profile picture"><i class="fas fa-camera"></i></button>
                        <div class="av-menu" id="avMenu">
                            <button type="button" data-av="view" <?= $img ? '' : 'hidden' ?>><i class="fas fa-user-circle"></i> See profile picture</button>
                            <button type="button" data-av="upload"><i class="fas fa-images"></i> Update profile picture</button>
                        </div>
                        <input type="file" id="avFile" accept="image/jpeg,image/png,image/gif,image/webp" hidden />
                    <?php endif; ?>
                </div>

                <div class="pf-id">
                    <h1 class="pf-name"><?= e($name) ?>
                        <?php if ($isTeacher): ?><i class="fas fa-circle-check" title="Verified Teacher"></i><?php endif; ?>
                        <?php if ($Model->IsSuspended): ?><span class="pf-warn"><i class="fas fa-ban"></i> Suspended</span><?php endif; ?>
                    </h1>
                    <div class="pf-meta">
                        <span><i class="fas <?= $isTeacher ? 'fa-chalkboard-user' : 'fa-graduation-cap' ?>"></i> <?= $roleLabel ?></span>
                        <?php if ($location !== ''): ?><span><i class="fas fa-location-dot"></i> <span id="pfLoc"><?= e($location) ?></span></span><?php endif; ?>
                        <span><i class="far fa-calendar"></i> Joined <?= e(fmt_date($u->CreatedAt, 'F Y', '—')) ?></span>
                    </div>
                    <?php if ($intro->Bio !== ''): ?>
                        <div class="pf-bio"><span id="pfBio">“<?= e($intro->Bio) ?>”</span><?php if ($isOwner): ?><button type="button" class="pf-pen" data-intro="bio" aria-label="Edit bio"><i class="fas fa-pen"></i></button><?php endif; ?></div>
                    <?php elseif ($isOwner): ?>
                        <div class="pf-bio empty"><span id="pfBio">নিজের সম্পর্কে এক লাইন লিখুন...</span><button type="button" class="pf-pen" data-intro="bio" aria-label="Add bio"><i class="fas fa-pen"></i></button></div>
                    <?php endif; ?>
                </div>

                <div class="pf-actions">
                    <?php if ($isOwner): ?>
                        <a href="/Profile?section=basic" class="pf-btn"><i class="fas fa-pen"></i> Edit Profile</a>
                    <?php elseif (!$isAdminViewer): ?>
                        <div class="fr-actions" data-uid="<?= (int)$u->Id ?>" data-rel="<?= e($rel) ?>"><?= friend_actions_html((int)$u->Id, $rel) ?></div>
                        <?php if ($isTeacher): ?>
                            <button type="button" class="pf-btn<?= $Model->TutorSaved ? ' on' : '' ?>" data-save-type="tutor" data-save-id="<?= (int)$u->Id ?>">
                                <i class="<?= $Model->TutorSaved ? 'fas' : 'far' ?> fa-bookmark"></i> <span><?= $Model->TutorSaved ? 'Saved' : 'Save' ?></span>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="pf-body">
            <!-- ======================= LEFT NAV ======================= -->
            <div class="pf-nav-col">
                <nav class="pf-card pf-nav" aria-label="Profile sections">
                    <?php foreach ($nav as [$k, $label, $icon]): ?>
                        <a href="?tab=<?= $k ?>" data-tab="<?= $k ?>" class="<?= $tab === $k ? 'active' : '' ?>"><i class="fas <?= $icon ?>"></i> <?= e($label) ?>
                            <?php if ($k === 'saved' && $Model->SavedItems): ?><span class="pf-n" data-saved-count><?= count($saved) ?></span><?php endif; ?>
                            <?php if ($k === 'friends' && $Model->FriendCount): ?><span class="pf-n"><?= (int)$Model->FriendCount ?></span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <?php $promo = profile_promo(); if ($promo['pp_enabled'] !== '0' && !is_blank($promo['pp_image'])):
                    $promoLink = !is_blank($promo['pp_link']) ? $promo['pp_link'] : ($isTeacher ? '/Tuition/Circular' : '/FindTutor');
                    $promoExt = preg_match('#^https?://#i', $promoLink); ?>
                    <a class="pf-card pf-promo-img" href="<?= e($promoLink) ?>"<?= $promoExt ? ' target="_blank" rel="noopener"' : '' ?> aria-label="<?= $isTeacher ? 'Tuition Circular' : 'Find Tutor' ?>">
                        <img src="<?= e($promo['pp_image']) ?>" alt="সঠিক Tutor, সঠিক ভবিষ্যৎ" loading="lazy" onerror="this.closest('.pf-promo-img').remove()" />
                    </a>
                <?php endif; ?>
            </div>

            <div class="pf-main">
                <!-- ======================= OVERVIEW ======================= -->
                <section class="pf-sec <?= $tab === 'overview' ? 'active' : '' ?>" id="sec-overview">
                    <div class="pf-stats">
                        <!-- Learning streak -->
                        <a href="?tab=activities" data-tab="activities" class="pf-card st fire-card">
                            <span class="st-ico st-fire"><i class="fas fa-fire"></i></span>
                            <div class="st-body">
                                <div class="st-t">Learning Streak <i class="fas fa-circle-info info" title="টানা কতদিন প্ল্যাটফর্মে লার্নিং অ্যাক্টিভিটি (ভিজিট, পোস্ট, কমেন্ট, গোলের কাজ) হয়েছে। একদিন বাদ গেলে আবার ১ থেকে শুরু।"></i></div>
                                <div class="st-v"><b><?= (int)$streak['current'] ?></b><span><?= $streak['current'] === 1 ? 'day' : 'days' ?></span></div>
                                <div class="st-dots" title="শেষ ৭ দিন">
                                    <?php foreach ($streak['last7'] as $i => $on): ?>
                                        <i class="<?= $on ? 'on' : '' ?><?= $i === 6 ? ' today' : '' ?>" title="<?= $days[(int)date('w', strtotime('-' . (6 - $i) . ' days'))] ?><?= $on ? ' ✓' : '' ?>"></i>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </a>
                        <!-- Goal -->
                        <a href="?tab=goals" data-tab="goals" class="pf-card st" id="stGoal">
                            <span class="st-ico st-goal"><i class="fas fa-bullseye"></i></span>
                            <div class="st-body">
                                <div class="st-t"><?= $isOwner ? 'My Goal' : 'Goal' ?></div>
                                <?php if ($primaryGoal): ?>
                                    <div class="st-goal-t" id="stGoalTitle"><?= e($primaryGoal->Title) ?></div>
                                    <div class="st-prog"><div class="bar"><span id="stGoalBar" style="width: <?= (int)$primaryGoal->Progress ?>%"></span></div><b id="stGoalPct"><?= (int)$primaryGoal->Progress ?>%</b></div>
                                <?php else: ?>
                                    <div class="st-empty"><?= $isOwner ? 'একটা লক্ষ্য ঠিক করুন — <b>Set goal +</b>' : 'এখনো কোনো গোল নেই' ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php if ($isOwner): ?>
                            <!-- My Tutors / Students -->
                            <a href="?tab=tutors" data-tab="tutors" class="pf-card st">
                                <span class="st-ico st-tut"><i class="fas fa-user-group"></i></span>
                                <div class="st-body">
                                    <div class="st-t"><?= $peopleLabel ?></div>
                                    <div class="st-v"><b><?= count($connected) ?></b><span>Active</span></div>
                                    <div class="st-faces">
                                        <?php foreach (array_slice($connected, 0, 3) as $c): ?>
                                            <span class="fc" title="<?= e($c->Name) ?>"><?= e($c->Initials) ?><?php if ($c->Image): ?><img src="<?= e($c->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                                        <?php endforeach; ?>
                                        <span class="plus" title="<?= $isTeacher ? 'Tuition খুঁজুন' : 'Tutor খুঁজুন' ?>"><i class="fas fa-plus"></i></span>
                                    </div>
                                </div>
                            </a>
                            <!-- Saved -->
                            <a href="?tab=saved" data-tab="saved" class="pf-card st">
                                <span class="st-ico st-save"><i class="fas fa-bookmark"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Saved Items</div>
                                    <div class="st-v"><b data-saved-count><?= count($saved) ?></b><span>items</span></div>
                                </div>
                                <?php if ($savedThumbs): ?>
                                    <div class="st-thumbs"><?php foreach ($savedThumbs as $t): ?><span><img src="<?= e($t->Image) ?>" alt="" loading="lazy" onerror="this.parentNode.remove()" /></span><?php endforeach; ?></div>
                                <?php endif; ?>
                                <span class="st-arrow"><i class="fas fa-chevron-right"></i></span>
                            </a>
                        <?php else: ?>
                            <a href="?tab=friends" data-tab="friends" class="pf-card st">
                                <span class="st-ico st-friend"><i class="fas fa-user-group"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Friends</div>
                                    <div class="st-v"><b><?= (int)$Model->FriendCount ?></b><span><?= $Model->MutualCount ? (int)$Model->MutualCount . ' mutual' : 'friends' ?></span></div>
                                    <div class="st-faces">
                                        <?php foreach (array_slice($friends, 0, 4) as $f): ?>
                                            <span class="fc" title="<?= e($f->FullName) ?>"><?= e($f->Initials) ?><?php if ($f->Image): ?><img src="<?= e($f->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </a>
                            <div class="pf-card st">
                                <span class="st-ico st-post"><i class="fas fa-pen-to-square"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Posts</div>
                                    <div class="st-v"><b><?= (int)$Model->PostCount ?></b><span>posts</span></div>
                                    <div class="st-empty"><?= (int)$Model->ActivityStats['study'] ?> study updates</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="ov-grid">
                        <div class="ov-main">
                            <?php if ($isOwner): ?>
                                <div class="pf-card pf-compose">
                                    <div class="pf-compose-top">
                                        <span class="pc-avatar me-avatar"></span>
                                        <button type="button" class="pf-compose-in" data-compose="post">Share your learning journey...</button>
                                    </div>
                                    <div class="pf-chips">
                                        <button type="button" class="pf-chip ch-photo" data-compose="photo"><i class="fas fa-image"></i> Photo</button>
                                        <button type="button" class="pf-chip ch-study" data-compose="study"><i class="fas fa-file-lines"></i> Study Update</button>
                                        <button type="button" class="pf-chip ch-q" data-compose="question"><i class="fas fa-circle-question"></i> Question</button>
                                        <button type="button" class="pf-chip ch-ach" data-compose="achievement"><i class="fas fa-trophy"></i> Achievement</button>
                                        <button type="button" class="pf-chip ch-poll" data-compose="poll"><i class="fas fa-chart-column"></i> Poll</button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="pf-feed-h"><h3>Posts</h3><span id="psPostCount"><?= (int)$Model->PostCount ?> post<?= $Model->PostCount === 1 ? '' : 's' ?></span></div>
                            <div id="psFeed">
                                <?php foreach ($Model->Posts as $p) partial('Post/_Post', ['post' => $p, 'viewerId' => $me->Id, 'viewerIsAdmin' => $isAdminViewer]); ?>
                            </div>
                            <div class="pf-card pf-empty" id="psEmpty" <?= $Model->Posts ? 'hidden' : '' ?>>
                                <div class="ei"><i class="far fa-newspaper"></i></div>
                                <h5><?= $isOwner ? 'আপনার learning journey শুরু করুন' : 'দেখানোর মতো কোনো পোস্ট নেই' ?></h5>
                                <p><?= $isOwner ? 'আজ কী পড়লেন, কী শিখলেন — Study Update দিয়ে শেয়ার করুন।' : ($rel === 'friends' ? e($firstName) . ' এখনো কিছু পোস্ট করেননি।' : 'ফ্রেন্ড হলে শুধু-ফ্রেন্ডদের পোস্টও দেখতে পাবেন।') ?></p>
                            </div>
                            <?php if ($Model->HasMore): ?>
                                <button type="button" class="pc-loadmore" id="psMore"><i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন</button>
                            <?php endif; ?>
                        </div>

                        <aside class="ov-right">
                            <!-- About Me -->
                            <div class="pf-card rc">
                                <div class="rc-h"><h4><?= $isOwner ? 'About Me' : 'About' ?></h4><?php if ($isOwner): ?><button type="button" data-intro="all">Edit</button><?php endif; ?></div>
                                <ul class="am-list">
                                    <li><i class="fas <?= $isTeacher ? 'fa-chalkboard-user' : 'fa-graduation-cap' ?>"></i> <?= $roleLabel ?><?= !is_blank($u->InstitutionName) ? ' · ' . e($u->InstitutionName) : '' ?></li>
                                    <?php if ($location !== ''): ?><li><i class="fas fa-location-dot"></i> <?= e($location) ?></li>
                                    <?php elseif ($isOwner): ?><li class="muted"><i class="fas fa-location-dot"></i> শহর যোগ করুন</li><?php endif; ?>
                                    <li><i class="far fa-calendar"></i> Joined <?= e(fmt_date($u->CreatedAt, 'F Y', '—')) ?></li>
                                    <li><i class="fas fa-book-open"></i> Interested Subjects</li>
                                </ul>
                                <div class="am-subj">
                                    <?php foreach ($intro->Subjects as $sj): ?><span><?= e($sj) ?></span><?php endforeach; ?>
                                    <?php if (!$intro->Subjects): ?><span style="background:none;border:none;color:#94A3B8;padding-left:0;font-style:italic;"><?= $isOwner ? 'Edit চেপে বিষয় যোগ করুন' : 'যোগ করা হয়নি' ?></span><?php endif; ?>
                                </div>
                                <?php if ($intro->Quote !== ''): ?>
                                    <div class="am-quote"><?= e($intro->Quote) ?></div>
                                <?php elseif ($isOwner): ?>
                                    <div class="am-quote" style="font-style:italic;color:#94A3B8;">একটা প্রিয় উক্তি যোগ করুন — যেমন "Learning today for a brighter tomorrow."</div>
                                <?php endif; ?>
                            </div>

                            <?php if ($isOwner): ?>
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4><?= $peopleLabel ?></h4><?php if ($connected): ?><a href="?tab=tutors" data-tab="tutors">View all</a><?php endif; ?></div>
                                    <?php if (!$connected): ?>
                                        <p class="rc-empty"><?= $isTeacher
                                            ? 'স্টুডেন্টরা আপনার অ্যাপ্লিকেশন Accept করলে এখানে দেখাবে। <a href="/Tuition/Circular">Tuition Circular</a> দেখুন।'
                                            : 'টিচারের অ্যাপ্লিকেশন Accept করলে এখানে দেখাবে। <a href="/FindTutor">Tutor খুঁজুন</a> অথবা <a href="/Tuition/Circular">পোস্ট দিন</a>।' ?></p>
                                    <?php else: ?>
                                        <div class="tu-list">
                                            <?php foreach (array_slice($connected, 0, 3) as $c): ?>
                                                <div class="tu-item">
                                                    <a class="tu-av" href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Initials) ?><?php if ($c->Image): ?><img src="<?= e($c->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                                                    <div class="tu-who"><a href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Name) ?></a><small><?= e($c->Sub) ?></small></div>
                                                    <a class="tu-view" href="/Profile/Show/<?= $c->Id ?>">View</a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4>Friends</h4><?php if ($friends): ?><a href="?tab=friends" data-tab="friends">View all</a><?php endif; ?></div>
                                    <?php if (!$friends): ?>
                                        <p class="rc-empty">এখনো কোনো ফ্রেন্ড নেই।</p>
                                    <?php else: ?>
                                        <div class="tu-list">
                                            <?php foreach (array_slice($friends, 0, 3) as $f): ?>
                                                <div class="tu-item">
                                                    <a class="tu-av" href="/Profile/Show/<?= $f->Id ?>"><?= e($f->Initials) ?><?php if ($f->Image): ?><img src="<?= e($f->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                                                    <div class="tu-who"><a href="/Profile/Show/<?= $f->Id ?>"><?= e($f->FullName) ?></a><small><?= e($f->RoleLabel) ?></small></div>
                                                    <a class="tu-view" href="/Profile/Show/<?= $f->Id ?>">View</a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </aside>
                    </div>
                </section>

                <!-- ======================= PROFILE (About) ======================= -->
                <section class="pf-sec <?= $tab === 'profile' ? 'active' : '' ?>" id="sec-profile">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Profile' : 'Profile' ?></h2><p><?= $isOwner ? 'আপনার সব তথ্য — অন্যরা যা দেখতে পায়। ফোন, ইমেইল ও ঠিকানা শুধু ফ্রেন্ডরা দেখে।' : 'ফোন, ইমেইল ও ঠিকানা শুধু ফ্রেন্ডরা দেখতে পারেন।' ?></p></div>
                        <?php if ($isOwner): ?><a href="/Profile?section=basic" class="pf-btn"><i class="fas fa-pen"></i> Edit Profile</a><?php endif; ?></div>
                    <?php partial('Profile/_ShowAbout', ['u' => $u, 'isOwner' => $isOwner, 'isTeacher' => $isTeacher, 'ti' => $ti, 'canPrivate' => $canPrivate, 'Model' => $Model]); ?>
                </section>

                <?php if ($isOwner): ?>
                <!-- ======================= TUTORS / STUDENTS ======================= -->
                <section class="pf-sec <?= $tab === 'tutors' ? 'active' : '' ?>" id="sec-tutors">
                    <div class="pf-sec-h"><div><h2><?= $peopleLabel ?></h2><p><?= $isTeacher ? 'যেসব স্টুডেন্ট আপনার অ্যাপ্লিকেশন Accept করেছেন।' : 'যেসব টিচারের অ্যাপ্লিকেশন আপনি Accept করেছেন।' ?></p></div>
                        <a href="<?= $isTeacher ? '/Teacher/TeacherDashboard?section=applications' : '/User/Dashboard?section=applications' ?>" class="pf-btn"><i class="fas fa-list-check"></i> Applications</a></div>
                    <?php if (!$connected): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-chalkboard-user"></i></div>
                            <h5><?= $isTeacher ? 'এখনো কোনো স্টুডেন্ট নেই' : 'এখনো কোনো টিউটর নেই' ?></h5>
                            <p><?= $isTeacher ? 'Tuition Circular এ স্টুডেন্টদের পোস্টে Apply করুন — Accept হলে এখানে দেখাবে।' : 'Tuition Circular এ পোস্ট দিন, টিচাররা Apply করবেন। পছন্দের টিচারকে Accept করলে এখানে দেখাবে।' ?></p>
                            <a href="<?= $isTeacher ? '/Tuition/Circular' : '/FindTutor' ?>" class="pf-btn primary"><i class="fas fa-magnifying-glass"></i> <?= $isTeacher ? 'Tuition খুঁজুন' : 'Tutor খুঁজুন' ?></a></div>
                    <?php else: ?>
                        <div class="pf-card rc"><div class="tu-list">
                            <?php foreach ($connected as $c): ?>
                                <div class="tu-item">
                                    <a class="tu-av" href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Initials) ?><?php if ($c->Image): ?><img src="<?= e($c->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                                    <div class="tu-who"><a href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Name) ?></a><small><?= e($c->Sub) ?><?= $c->Institution !== '' ? ' · ' . e($c->Institution) : '' ?></small></div>
                                    <a class="tu-view" href="/Profile/Show/<?= $c->Id ?>">View</a>
                                </div>
                            <?php endforeach; ?>
                        </div></div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- ======================= FRIENDS ======================= -->
                <section class="pf-sec <?= $tab === 'friends' ? 'active' : '' ?>" id="sec-friends">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Friends' : e($firstName) . "'s Friends" ?></h2><p><?= (int)$Model->FriendCount ?> friend<?= $Model->FriendCount === 1 ? '' : 's' ?></p></div>
                        <?php if ($isOwner): ?><a href="/Friends?tab=find" class="pf-btn"><i class="fas fa-user-plus"></i> Find People</a><?php endif; ?></div>
                    <?php if ($friends): ?>
                        <div class="pf-card fr-wrap-h">
                            <label style="position:relative;display:block;flex:1;max-width:420px;margin:0;">
                                <i class="fas fa-magnifying-glass" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94A3B8;"></i>
                                <input type="search" id="psFriendFilter" placeholder="ফ্রেন্ডদের মধ্যে খুঁজুন..." autocomplete="off"
                                       style="width:100%;height:42px;border:1.5px solid #E8EEF5;border-radius:10px;padding:0 14px 0 40px;font-size:14.5px;outline:none;" />
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="fr-grid" id="psFriendsGrid">
                        <?php if (!$friends): ?>
                            <?php partial('Friends/_Empty', ['icon' => 'fa-user-group', 'title' => 'কোনো ফ্রেন্ড নেই',
                                'text' => $isOwner ? 'স্টুডেন্ট বা টিচার খুঁজে ফ্রেন্ড রিকোয়েস্ট পাঠান।' : $firstName . ' এর এখনো কোনো ফ্রেন্ড নেই।',
                                'btnText' => $isOwner ? 'Find People' : null, 'btnHref' => '/Friends?tab=find']); ?>
                        <?php else: ?>
                            <?php foreach ($friends as $f) partial('Friends/_PersonCard', ['p' => $f]); ?>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if ($isOwner && !$isTeacher): ?>
                <!-- ======================= MY CIRCULARS ======================= -->
                <section class="pf-sec <?= $tab === 'circulars' ? 'active' : '' ?>" id="sec-circulars">
                    <div class="pf-sec-h"><div><h2>My Circulars</h2><p>Tuition Circular এ আপনার দেওয়া পোস্ট আর কতজন টিচার Apply করেছেন।</p></div>
                        <a href="/Tuition/Circular" class="pf-btn primary"><i class="fas fa-plus"></i> নতুন পোস্ট</a></div>
                    <?php if (!$Model->MyCirculars): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-file-lines"></i></div><h5>এখনো কোনো সার্কুলার পোস্ট নেই</h5>
                            <p>কোন বিষয়ে, কোন এলাকায় টিউটর লাগবে লিখে পোস্ট দিন — টিচাররা Apply করবেন।</p>
                            <a href="/Tuition/Circular" class="pf-btn primary"><i class="fas fa-plus"></i> পোস্ট দিন</a></div>
                    <?php else: ?>
                        <?php foreach ($Model->MyCirculars as $c): ?>
                            <div class="pf-card ci-item">
                                <p class="ci-text"><?= e(str_preview($c->Content, 280)) ?></p>
                                <div class="ci-tags">
                                    <?php foreach (array_filter([['fa-location-dot', $c->Area], ['fa-graduation-cap', $c->Class], ['fa-book', $c->Subjects], ['fa-money-bill-wave', is_blank($c->Budget) ? null : '৳' . $c->Budget]], fn($x) => !is_blank($x[1])) as [$ic, $v]): ?>
                                        <span><i class="fas <?= $ic ?>"></i> <?= e($v) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="ci-foot">
                                    <span><i class="far fa-clock"></i> <?= e(time_ago($c->CreatedAt)) ?></span>
                                    <span><i class="fas fa-chalkboard-user"></i> <?= $c->Apps ?> জন Apply করেছেন</span>
                                    <?php if ($c->PendingApps): ?><span class="ci-pend"><?= $c->PendingApps ?> নতুন</span><?php endif; ?>
                                    <a href="/User/Dashboard?section=applications" class="tu-view">Applications দেখুন</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <?php if ($isOwner): ?>
                <!-- ======================= SAVED ======================= -->
                <section class="pf-sec <?= $tab === 'saved' ? 'active' : '' ?>" id="sec-saved">
                    <div class="pf-sec-h"><div><h2>Saved Items</h2><p>পরে দেখার জন্য যেগুলো Save করেছেন — পোস্ট, টিউটর, সার্কুলার, টিউশন, নোটিশ।</p></div></div>
                    <?php if (!$saved): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="far fa-bookmark"></i></div><h5>এখনো কিছু Save করেননি</h5>
                            <p>পোস্ট, টিউটরের প্রোফাইল, Tuition Circular, টিউশন বা নোটিশে <b>Save</b> বাটন চাপলে এখানে জমা থাকবে।</p></div>
                    <?php else:
                        $typeCounts = array_count_values(array_map(fn($s) => $s->Type, $saved)); ?>
                        <div class="sv-filters" id="svFilters">
                            <button type="button" class="sv-filter active" data-f="all">All (<?= count($saved) ?>)</button>
                            <?php foreach ($typeLabel as $k => [$lb]): if (empty($typeCounts[$k])) continue; ?>
                                <button type="button" class="sv-filter" data-f="<?= $k ?>"><?= $lb ?>s (<?= $typeCounts[$k] ?>)</button>
                            <?php endforeach; ?>
                        </div>
                        <div class="sv-list" id="svList">
                            <?php foreach ($saved as $s): [$tl, $ti2] = $typeLabel[$s->Type] ?? ['Item', 'fa-bookmark']; ?>
                                <div class="pf-card sv-item<?= $s->Available ? '' : ' unavail' ?>" data-type="<?= e($s->Type) ?>" data-key="<?= e($s->Type . ':' . $s->ItemId) ?>">
                                    <a class="sv-thumb" href="<?= e($s->Link) ?>"><?php if ($s->Available && $s->Image): ?><img src="<?= e($s->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php else: ?><i class="fas <?= $ti2 ?>"></i><?php endif; ?></a>
                                    <div class="sv-body">
                                        <span class="sv-type"><?= $tl ?></span>
                                        <a class="sv-title" href="<?= e($s->Link) ?>"><?= $s->Available ? e($s->Title) : 'This item is no longer available' ?></a>
                                        <small><?= $s->Available ? e($s->Sub) : 'মুছে ফেলা হয়েছে বা দেখার অনুমতি নেই' ?> · Saved <?= e(time_ago($s->SavedAt)) ?></small>
                                    </div>
                                    <button type="button" class="sv-un" data-save-type="<?= e($s->Type) ?>" data-save-id="<?= $s->ItemId ?>"><i class="fas fa-bookmark"></i> <span>Unsave</span></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- ======================= GOALS ======================= -->
                <section class="pf-sec <?= $tab === 'goals' ? 'active' : '' ?>" id="sec-goals">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Goals' : 'Goals' ?></h2><p><?= $isOwner ? 'HSC, SSC, Admission, IELTS — লক্ষ্য ঠিক করুন, কাজে ভাগ করুন, টিক দিয়ে এগিয়ে যান।' : e($firstName) . ' এর পড়াশোনার লক্ষ্য।' ?></p></div>
                        <?php if ($isOwner): ?><button type="button" class="pf-btn primary" data-goal="new"><i class="fas fa-plus"></i> Add goal</button><?php endif; ?></div>
                    <div class="gl-grid" id="glGrid">
                        <?php foreach ($goals as $g) partial('Profile/_GoalCard', ['g' => $g, 'isOwner' => $isOwner]); ?>
                    </div>
                    <div class="pf-card pf-empty" id="glEmpty" <?= $goals ? 'hidden' : '' ?>><div class="ei"><i class="fas fa-bullseye"></i></div>
                        <h5><?= $isOwner ? 'প্রথম লক্ষ্যটা ঠিক করুন' : 'এখনো কোনো গোল নেই' ?></h5>
                        <p><?= $isOwner ? 'যেমন "HSC 2027" — তারপর ছোট ছোট কাজ যোগ করুন। কাজ টিক দিলে প্রগ্রেস নিজে থেকে বাড়বে।' : '' ?></p>
                        <?php if ($isOwner): ?><button type="button" class="pf-btn primary" data-goal="new"><i class="fas fa-plus"></i> Add goal</button><?php endif; ?></div>
                </section>

                <!-- ======================= ACTIVITIES ======================= -->
                <section class="pf-sec <?= $tab === 'activities' ? 'active' : '' ?>" id="sec-activities">
                    <?php $as = $Model->ActivityStats; $ad = $Model->ActivityDays; ?>
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Activities' : 'Activities' ?></h2><p>প্রতিদিন একটু একটু — streak ধরে রাখুন! 🔥</p></div></div>
                    <div class="act-top">
                        <div class="pf-card act-big"><span class="st-ico st-fire"><i class="fas fa-fire"></i></span><div><b><?= (int)$streak['current'] ?> <small>days</small></b><small>Current streak<?= $isOwner && !$streak['activeToday'] ? ' — আজ এখনো অ্যাক্টিভ হননি' : '' ?></small></div></div>
                        <div class="pf-card act-big"><span class="st-ico st-post"><i class="fas fa-trophy"></i></span><div><b><?= (int)$streak['best'] ?> <small>days</small></b><small>Best streak</small></div></div>
                        <div class="pf-card act-big"><span class="st-ico st-goal"><i class="fas fa-calendar-check"></i></span><div><b><?= (int)$streak['totalDays'] ?></b><small>Active days (total)</small></div></div>
                    </div>
                    <div class="pf-card hm">
                        <h4>Last 17 weeks <small><?= count($ad) ?> active days</small></h4>
                        <div class="hm-scroll"><div class="hm-grid" aria-label="Activity heatmap">
                            <?php
                            $start = new DateTime('today'); $start->modify('-' . (16 * 7 + (int)$start->format('w')) . ' days');
                            $today = date('Y-m-d');
                            for ($i = 0; $i < 17 * 7; $i++) {
                                $d = $start->format('Y-m-d'); $pts = $ad[$d] ?? 0;
                                $lv = $pts <= 0 ? '' : ($pts <= 2 ? 'l1' : ($pts <= 5 ? 'l2' : ($pts <= 10 ? 'l3' : 'l4')));
                                echo '<i class="' . ($d > $today ? 'fut' : $lv) . '" title="' . e($start->format('d M') . ($pts ? " · $pts points" : '')) . '"></i>';
                                $start->modify('+1 day');
                            } ?>
                        </div></div>
                        <div class="hm-legend">Less <i style="background:#EEF2F7"></i><i style="background:#CDE2FF"></i><i style="background:#8DBBFB"></i><i style="background:#4A8DF2"></i><i style="background:#1D63E0"></i> More</div>
                    </div>
                    <div class="act-tiles">
                        <?php foreach ([['fa-file-lines', 'st-tut', $as['study'], 'Study updates'], ['fa-clock', 'st-goal', $fmtMin((int)$as['minutes']), 'Study time'],
                                        ['fa-circle-question', 'st-save', $as['questions'], 'Questions asked'], ['fa-trophy', 'st-post', $as['achievements'], 'Achievements'],
                                        ['fa-pen-to-square', 'st-friend', $as['posts'], 'Total posts'], ['fa-comment', 'st-fire', $as['comments'], 'Comments']] as [$ic, $cls, $v, $lb]): ?>
                            <div class="pf-card act-tile"><span class="st-ico <?= $cls ?>"><i class="fas <?= $ic ?>"></i></span><div><b><?= e((string)$v) ?></b><small><?= $lb ?></small></div></div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php if ($isOwner): ?>
                <!-- ======================= SETTINGS ======================= -->
                <section class="pf-sec <?= $tab === 'settings' ? 'active' : '' ?>" id="sec-settings">
                    <div class="pf-sec-h"><div><h2>Settings</h2><p>প্রোফাইল, পাসওয়ার্ড আর অ্যাকাউন্ট।</p></div></div>
                    <div class="set-grid">
                        <div class="pf-card set-card">
                            <h4><i class="fas fa-user-pen"></i> Profile</h4><p>অন্যরা আপনার প্রোফাইলে যা দেখে।</p>
                            <div class="set-links">
                                <button type="button" data-intro="all"><i class="fas fa-quote-left"></i> Bio, শহর, বিষয় ও উক্তি <i class="fas fa-chevron-right chev"></i></button>
                                <a href="/Profile?section=basic"><i class="fas fa-id-card"></i> নাম, ইমেইল, মোবাইল <i class="fas fa-chevron-right chev"></i></a>
                                <a href="/Profile?section=education"><i class="fas fa-graduation-cap"></i> Education <i class="fas fa-chevron-right chev"></i></a>
                                <a href="/Profile?section=address"><i class="fas fa-location-dot"></i> Address <i class="fas fa-chevron-right chev"></i></a>
                                <a href="/Profile?section=links"><i class="fas fa-link"></i> Important Links <i class="fas fa-chevron-right chev"></i></a>
                                <?php if (!$isTeacher): ?><a href="/Profile?section=become-tutor"><i class="fas fa-chalkboard-user"></i> Become a Tutor <i class="fas fa-chevron-right chev"></i></a><?php endif; ?>
                                <a href="/Friends"><i class="fas fa-user-group"></i> Friends & Requests <i class="fas fa-chevron-right chev"></i></a>
                            </div>
                        </div>
                        <div class="pf-card set-card" id="ab-password">
                            <h4><i class="fas fa-key"></i> Change Password</h4><p>বর্তমান পাসওয়ার্ড দিয়ে নতুন পাসওয়ার্ড সেট করুন।</p>
                            <form class="pw-form" id="pwForm" autocomplete="off">
                                <div class="pw-field"><label for="pwCur">বর্তমান পাসওয়ার্ড</label><input type="password" id="pwCur" autocomplete="current-password" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-field"><label for="pwNew">নতুন পাসওয়ার্ড (অন্তত ৬ অক্ষর)</label><input type="password" id="pwNew" autocomplete="new-password" minlength="6" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-field"><label for="pwConf">নতুন পাসওয়ার্ড আবার লিখুন</label><input type="password" id="pwConf" autocomplete="new-password" minlength="6" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-msg" id="pwMsg"></div>
                                <div><button type="submit" class="ps-btn primary" id="pwBtn"><i class="fas fa-check"></i> পাসওয়ার্ড বদলান</button>
                                    <a href="/Account/ForgotPassword" style="margin-left:12px;font-size:13.5px;">বর্তমান পাসওয়ার্ড মনে নেই?</a></div>
                            </form>
                        </div>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($isOwner): ?>
<!-- ===== Intro (Bio / শহর / বিষয় / উক্তি) ===== -->
<div class="pf-ov" id="introOv" aria-hidden="true">
    <form class="pf-modal" id="introForm" autocomplete="off">
        <div class="pf-modal-h"><h3>Edit intro</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <div class="pf-f"><label for="inBio">Bio <small>— নামের নিচে দেখাবে</small></label>
                <textarea id="inBio" maxlength="160" rows="2" placeholder="যেমন: শিখছি, এগিয়ে যাচ্ছি, আরও ভালো একটা আগামির জন্য।"><?= e($intro->Bio) ?></textarea><div class="cnt"><span id="inBioN"><?= mb_strlen($intro->Bio) ?></span>/160</div></div>
            <div class="pf-f"><label for="inCity">শহর / জেলা</label><input type="text" id="inCity" maxlength="80" value="<?= e($intro->City) ?>" placeholder="যেমন: Chattogram" list="inCityList" />
                <datalist id="inCityList"><?php foreach (['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Gazipur', 'Narayanganj', 'Bogura', "Cox's Bazar"] as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist></div>
            <div class="pf-f"><label>Interested Subjects <small>— Enter বা কমা দিয়ে আলাদা করুন</small></label>
                <div class="sj-box" id="sjBox"><input type="text" id="sjIn" maxlength="30" placeholder="Mathematics, Physics..." list="pmSubjList" /></div></div>
            <div class="pf-f"><label for="inQuote">প্রিয় উক্তি</label><input type="text" id="inQuote" maxlength="160" value="<?= e($intro->Quote) ?>" placeholder="Learning today for a brighter tomorrow." /></div>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary" id="introSave">Save</button></div>
    </form>
</div>

<!-- ===== Goal add / edit ===== -->
<div class="pf-ov" id="goalOv" aria-hidden="true">
    <form class="pf-modal" id="goalForm" autocomplete="off">
        <div class="pf-modal-h"><h3 id="goalTitleH">Add goal</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <input type="hidden" id="glId" />
            <div class="pf-f" id="glQuickWrap"><label>দ্রুত বাছাই</label><div class="pf-quick">
                <?php $y = (int)date('Y'); foreach ([["HSC $y", 'HSC'], ['HSC ' . ($y + 1), 'HSC'], ["SSC $y", 'SSC'], ['SSC ' . ($y + 1), 'SSC'], ['University Admission', 'Admission'], ['Medical Admission', 'Admission'], ['IELTS 7.0', 'IELTS'], ['Semester Final', 'University'], ['Math এ A+', 'Subject']] as [$qt, $qc]): ?>
                    <button type="button" data-q="<?= e($qt) ?>" data-c="<?= $qc ?>"><?= e($qt) ?></button>
                <?php endforeach; ?></div></div>
            <div class="pf-f"><label for="glTitle">গোলের নাম <b style="color:#e74c3c">*</b></label><input type="text" id="glTitle" maxlength="120" placeholder="যেমন: HSC 2027" required /></div>
            <div class="pf-2">
                <div class="pf-f"><label for="glCat">ধরন</label><select id="glCat"><?php foreach (Db::GOAL_CATEGORIES as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?></select></div>
                <div class="pf-f"><label for="glDate">টার্গেট তারিখ <small>(ঐচ্ছিক)</small></label><input type="date" id="glDate" /></div>
            </div>
            <div class="pf-f" id="glProgWrap"><label>প্রগ্রেস <small>— কাজ যোগ করলে নিজে থেকে হিসাব হবে</small></label>
                <div class="pf-range"><input type="range" id="glProg" min="0" max="100" step="5" value="0" /><b id="glProgV">0%</b></div></div>
            <label class="pf-check"><input type="checkbox" id="glPrimary" /> এটাই আমার Main goal (প্রোফাইলের উপরে দেখাবে)</label>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary" id="glSave">Save goal</button></div>
    </form>
</div>
<?php endif; ?>

<?php section_start('Scripts'); ?>
<?php partial('Profile/_ShowCoreJs', ['u' => $u, 'isOwner' => $isOwner, 'tab' => $tab, 'rel' => $rel, 'coverY' => $coverY]); ?>
<script>
(function () {
    var PF = window.PF; if (!PF || !PF.IS_OWNER) return;
    var $ = PF.$, $$ = PF.$$, postForm = PF.postForm, say = PF.say, hardReload = PF.hardReload, openOv = PF.openOv, closeOv = PF.closeOv, closeMenus = PF.closeMenus;

    // ----- Intro -----
    var introOv = $('#introOv'), sjBox = $('#sjBox'), sjIn = $('#sjIn');
    var subjects = <?= js($intro->Subjects) ?>;
    function paintSubj() {
        $$('span', sjBox).forEach(function (s) { s.remove(); });
        subjects.forEach(function (s, i) {
            var el = document.createElement('span');
            el.textContent = s;
            var x = document.createElement('button'); x.type = 'button'; x.innerHTML = '<i class="fas fa-xmark"></i>'; x.setAttribute('aria-label', 'Remove');
            x.addEventListener('click', function () { subjects.splice(i, 1); paintSubj(); });
            el.appendChild(x); sjBox.insertBefore(el, sjIn);
        });
    }
    function addSubj(v) {
        v = v.replace(/,/g, ' ').trim(); if (!v) return;
        if (subjects.length >= 10) { say('সর্বোচ্চ ১০টা বিষয়।', true); return; }
        if (!subjects.some(function (s) { return s.toLowerCase() === v.toLowerCase(); })) subjects.push(v.slice(0, 30));
        sjIn.value = ''; paintSubj();
    }
    sjBox.addEventListener('click', function () { sjIn.focus(); });
    sjIn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addSubj(sjIn.value); }
        else if (e.key === 'Backspace' && !sjIn.value && subjects.length) { subjects.pop(); paintSubj(); }
    });
    sjIn.addEventListener('change', function () { if (sjIn.value.trim()) addSubj(sjIn.value); });
    $('#inBio').addEventListener('input', function () { $('#inBioN').textContent = this.value.length; });
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-intro]'); if (!b) return;
        paintSubj(); openOv(introOv);
        setTimeout(function () { (b.getAttribute('data-intro') === 'bio' ? $('#inBio') : $('#inBio')).focus(); }, 40);
    });
    $('#introForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (sjIn.value.trim()) addSubj(sjIn.value);
        var btn = $('#introSave'); btn.disabled = true;
        try {
            var d = await postForm('/Profile/SaveIntro', { bio: $('#inBio').value, city: $('#inCity').value, subjects: subjects.join(','), quote: $('#inQuote').value });
            if (!d.success) { say(d.message, true); return; }
            say(d.message); closeOv(introOv);
            setTimeout(function () { hardReload(); }, 400);
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; }
    });

    // ----- Goals -----
    var goalOv = $('#goalOv'), grid = $('#glGrid');
    function paintPrimary(p) {
        var card = $('#stGoal .st-body');
        if (!p) { card.innerHTML = '<div class="st-t">My Goal</div><div class="st-empty">একটা লক্ষ্য ঠিক করুন — <b>Set goal +</b></div>'; return; }
        card.innerHTML = '<div class="st-t">My Goal</div><div class="st-goal-t" id="stGoalTitle"></div><div class="st-prog"><div class="bar"><span id="stGoalBar"></span></div><b id="stGoalPct"></b></div>';
        $('#stGoalTitle').textContent = p.title; $('#stGoalBar').style.width = p.progress + '%'; $('#stGoalPct').textContent = p.progress + '%';
    }
    function applyGoal(d, goalId) {
        if (d.html) {
            var t = document.createElement('template'); t.innerHTML = d.html.trim();
            var el = t.content.firstElementChild, old = document.getElementById('goal-' + goalId);
            if (old) old.replaceWith(el); else grid.prepend(el);
        }
        paintPrimary(d.primary);
        // main goal বদলালে অন্য কার্ডের ব্যাজ ঠিক করতে পুরো তালিকা রিফ্রেশ দরকার — সহজ রাখতে পেজের বাকিটা ঠিক রাখি
        $('#glEmpty').hidden = !!$('.gl-card', grid);
    }
    function openGoal(card) {
        $('#glId').value = card ? card.getAttribute('data-id') : '';
        $('#goalTitleH').textContent = card ? 'Edit goal' : 'Add goal';
        $('#glQuickWrap').hidden = !!card;
        $('#glTitle').value = card ? card.getAttribute('data-title') : '';
        $('#glCat').value = card ? card.getAttribute('data-category') : 'HSC';
        $('#glDate').value = card ? card.getAttribute('data-date') : '';
        $('#glProg').value = card ? card.getAttribute('data-progress') : 0; $('#glProgV').textContent = $('#glProg').value + '%';
        $('#glProgWrap').hidden = !!(card && card.getAttribute('data-tasks') === '1');
        $('#glPrimary').checked = card ? card.getAttribute('data-primary') === '1' : !$('.gl-card', grid);
        openOv(goalOv); setTimeout(function () { $('#glTitle').focus(); }, 40);
    }
    $('#glProg').addEventListener('input', function () { $('#glProgV').textContent = this.value + '%'; });
    $('#glQuickWrap').addEventListener('click', function (e) {
        var b = e.target.closest('button[data-q]'); if (!b) return;
        $('#glTitle').value = b.getAttribute('data-q'); $('#glCat').value = b.getAttribute('data-c');
    });
    $('#goalForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn = $('#glSave'); btn.disabled = true;
        var id = $('#glId').value;
        try {
            var d = await postForm('/Goal/Save', { goalId: id, title: $('#glTitle').value, category: $('#glCat').value, targetDate: $('#glDate').value,
                progress: $('#glProg').value, primary: $('#glPrimary').checked ? 'true' : 'false' });
            if (!d.success) { say(d.message, true); return; }
            closeOv(goalOv); say(d.message);
            // অন্য কার্ডে "Main goal" ব্যাজ ছিল, এখন এটা main হলো → পুরো তালিকা নতুন করে দেখাই
            var oldMain = $('.gl-card[data-primary="1"]', grid);
            if ($('#glPrimary').checked && oldMain && oldMain.getAttribute('data-id') !== String(id)) { setTimeout(function () { hardReload(); }, 400); return; }
            var t = document.createElement('template'); t.innerHTML = d.html.trim();
            applyGoal(d, t.content.firstElementChild.getAttribute('data-id'));
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; }
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-goal="new"]'); if (b) { openGoal(null); return; }
        var g = e.target.closest('[data-gl]'); if (!g || g.tagName === 'INPUT' || g.tagName === 'FORM') return;
        var card = g.closest('.gl-card'), act = g.getAttribute('data-gl'), id = card.getAttribute('data-id');
        if (act === 'menu') { var m = g.nextElementSibling; closeMenus(m); m.classList.toggle('open'); return; }
        closeMenus();
        if (act === 'edit') openGoal(card);
        else if (act === 'primary') {
            var d = await postForm('/Goal/Save', { goalId: id, title: card.getAttribute('data-title'), category: card.getAttribute('data-category'),
                targetDate: card.getAttribute('data-date'), progress: card.getAttribute('data-progress'), primary: 'true' });
            if (d.success) { say('Main goal বদলানো হয়েছে।'); setTimeout(function () { hardReload(); }, 300); } else say(d.message, true);
        } else if (act === 'delete') {
            if (!confirm('গোলটি আর এর সব কাজ মুছে ফেলবেন?')) return;
            var d2 = await postForm('/Goal/Delete', { goalId: id });
            if (!d2.success) { say(d2.message, true); return; }
            card.remove(); applyGoal(d2, null); say(d2.message);
        } else if (act === 'task-del') {
            var li = g.closest('li');
            var d3 = await postForm('/Goal/Task', { taskId: li.getAttribute('data-task'), delete: 'true' });
            if (d3.success) applyGoal(d3, id); else say(d3.message, true);
        }
    });
    document.addEventListener('change', async function (e) {
        var cb = e.target.closest('[data-gl="task"]'); if (!cb) return;
        var card = cb.closest('.gl-card'), li = cb.closest('li');
        li.classList.toggle('done', cb.checked);
        var d = await postForm('/Goal/Task', { taskId: li.getAttribute('data-task'), done: cb.checked ? 'true' : 'false' });
        if (d.success) { applyGoal(d, card.getAttribute('data-id')); if (cb.checked && d.html && d.html.indexOf('gl-ok') > -1) say('🎉 অভিনন্দন! গোল শেষ!'); }
        else { cb.checked = !cb.checked; li.classList.toggle('done', cb.checked); say(d.message, true); }
    });
    document.addEventListener('submit', async function (e) {
        var f = e.target.closest('[data-gl="add-task"]'); if (!f) return;
        e.preventDefault();
        var inp = $('input', f), v = inp.value.trim(); if (!v) return;
        var card = f.closest('.gl-card'); inp.disabled = true;
        try {
            var d = await postForm('/Goal/AddTask', { goalId: card.getAttribute('data-id'), title: v });
            if (!d.success) { say(d.message, true); return; }
            applyGoal(d, card.getAttribute('data-id'));
            var ni = $('#goal-' + card.getAttribute('data-id') + ' .gl-add input'); if (ni) ni.focus();
        } finally { inp.disabled = false; }
    });
})();
</script>
<?php section_end(); ?>
