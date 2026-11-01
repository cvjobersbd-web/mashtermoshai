<?php
class TuitionController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

    private const VIDEO_EXTS = ['.mp4', '.webm', '.mov', '.m4v'];
    private const MAX_VIDEO = 25 * 1024 * 1024;
    public const PAGE_SIZE = 15;

    /**
     * GET /Tuition/Circular?location=&class=&subject=&medium[]=&type[]=&sort=recent|views|comments&page=
     * ছবির মতো নতুন ডিজাইন: বামে ফিল্টার, মাঝে পোস্ট, ডানে Need a Teacher / How It Works।
     */
    public function Circular(): void
    {
        $arr = fn($k) => array_values(array_filter(array_map('strval', (array)($_GET[$k] ?? [])), 'strlen'));
        $f = [
            'location' => mb_substr(trim((string)input_str('location', '')), 0, 60),
            'class' => mb_substr(trim((string)input_str('class', '')), 0, 60),
            'subject' => mb_substr(trim((string)input_str('subject', '')), 0, 60),
            'medium' => array_values(array_intersect($arr('medium'), Db::CIRCULAR_MEDIUMS)),
            'type' => array_values(array_intersect($arr('type'), Db::CIRCULAR_TYPES)),
            'q' => mb_substr(trim((string)input_str('q', '')), 0, 80),
        ];
        $sort = in_array(input_str('sort', 'recent'), ['recent', 'views', 'comments'], true) ? input_str('sort', 'recent') : 'recent';
        $page = max(1, min(20, input_int('page', 1) ?? 1));
        $feed = $this->db->GetCircularFeed($f, $sort, current_user_id(), self::PAGE_SIZE * $page);
        try { $adminPosts = $this->db->GetOpenTuitionPosts(null, null); } catch (Throwable $e) { $adminPosts = []; }
        $this->view(null, $adminPosts, [
            'Feed' => $feed['posts'], 'FeedTotal' => $feed['total'], 'Filters' => $f, 'Sort' => $sort, 'Page' => $page,
            'FilterValues' => $this->db->CircularFilterValues(),
            'IsFiltered' => $f['location'] !== '' || $f['class'] !== '' || $f['subject'] !== '' || $f['medium'] || $f['type'] || $f['q'] !== '',
        ]);
    }

    private function fail(string $m): void { json_response(['success' => false, 'message' => $m]); }

    /** POST /Tuition/CreateCircularPost (title, content, preferredArea, classLevel, subjects, budget, medium, tuitionType, imageFile | videoFile) */
    public function CreateCircularPost_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) $this->fail('পোস্ট করতে লগইন করুন।');
        $clean = fn($k, $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str($k, ''))), 0, $max);
        $title = $clean('title', 150);
        $content = trim(preg_replace("/\n{4,}/", "\n\n\n", str_replace("\r", '', (string)input_str('content', ''))));
        if ($title === '') $this->fail('পোস্টের শিরোনাম লিখুন (যেমন: HSC এর জন্য Mathematics টিউটর প্রয়োজন)।');
        if ($content === '') $this->fail('টিউশনের বিস্তারিত লিখুন।');
        if (mb_strlen($content) > 3000) $this->fail('বিস্তারিত সর্বোচ্চ ৩০০০ অক্ষর।');
        if ($this->db->CountRecentCircularPosts($userId, 60) >= 10) $this->fail('এক ঘণ্টায় অনেক পোস্ট করেছেন। কিছুক্ষণ পর চেষ্টা করুন।');
        // পেমেন্টের নিয়ম চালু থাকলে পোস্ট করতে ক্রেডিট লাগবে (অ্যাডমিন বাদে)
        $needCredit = session_get('UserRole') !== 'Admin' && $this->db->PaymentRules()['student'];
        if ($needCredit && $this->db->GetStudentPostCredits($userId) === 0) json_response(['success' => false, 'needPackage' => '/Package?for=student',
            'message' => 'Tuition Post করতে পোস্ট প্যাকেজ লাগবে। প্যাকেজ নিয়ে পেমেন্ট করুন — অ্যাডমিন Accept করলেই পোস্ট করতে পারবেন।']);
        $medium = (string)input_str('medium', '');
        $type = (string)input_str('tuitionType', '');
        $budget = preg_replace('/[^0-9]/', '', (string)input_str('budget', ''));
        if (strlen($budget) > 7) $this->fail('বাজেট সঠিক নয়।');

        $imageUrl = null; $videoUrl = null;
        $image = upload_file('imageFile');
        $video = upload_file('videoFile');
        if ($image && $video) $this->fail('একটা ছবি অথবা একটা ভিডিও দিন — দুটো একসাথে নয়।');
        if ($image) {
            $ext = strtolower('.' . pathinfo($image['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::IMAGE_EXTS, true) || $image['size'] > 5 * 1024 * 1024) $this->fail('শুধু JPG, PNG, GIF, WEBP ছবি (৫ MB এর কম)।');
            if (@getimagesize($image['tmp_name']) === false) $this->fail('ছবিটি সঠিক নয়।');
            $imageUrl = save_upload($image, 'tuition-posts', 'post_' . ticks(), $ext);
        }
        if ($video) {
            $ext = strtolower('.' . pathinfo($video['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::VIDEO_EXTS, true)) $this->fail('শুধু MP4, WEBM বা MOV ভিডিও দেওয়া যাবে।');
            if ($video['size'] > self::MAX_VIDEO) $this->fail('ভিডিও ২৫ MB এর কম হতে হবে।');
            $head = (string)@file_get_contents($video['tmp_name'], false, null, 0, 16);
            $isMp4 = substr($head, 4, 4) === 'ftyp';
            $isWebm = substr($head, 0, 4) === "\x1A\x45\xDF\xA3";
            if (!$isMp4 && !$isWebm) $this->fail('ভিডিও ফাইলটি সঠিক নয়।');
            $videoUrl = save_upload($video, 'tuition-videos', 'vid_' . ticks() . '_' . bin2hex(random_bytes(3)), $ext === '.mov' ? '.mov' : $ext);
        }
        $post = obj([
            'UserId' => $userId, 'Content' => $content, 'ImageUrl' => $imageUrl, 'VideoUrl' => $videoUrl, 'Title' => $title,
            'PreferredArea' => $clean('preferredArea', 100) ?: null, 'ClassLevel' => $clean('classLevel', 60) ?: null,
            'Subjects' => $clean('subjects', 120) ?: null, 'Budget' => $budget !== '' ? $budget : null,
            'Medium' => in_array($medium, Db::CIRCULAR_MEDIUMS, true) ? $medium : null,
            'TuitionType' => in_array($type, Db::CIRCULAR_TYPES, true) ? $type : null,
        ]);
        $err = null;
        if ($this->db->CreateTuitionCircularPost($post, $err)) {
            if ($needCredit) { try { $this->db->ConsumeStudentPostCredit($userId); } catch (Throwable $e) { error_log('[post credit] ' . $e->getMessage()); } }
            json_response(['success' => true, 'id' => $post->Id, 'message' => 'পোস্ট হয়েছে! আগ্রহী শিক্ষকরা আপনার সাথে যোগাযোগ করবেন।']);
        }
        delete_upload($imageUrl); delete_upload($videoUrl);
        $this->fail('পোস্ট করা যায়নি। আবার চেষ্টা করুন।');
    }

    /** POST /Tuition/EditCircularComment (commentId, content) */
    public function EditCircularComment_POST(): void { feed_comment_action('circular', 'edit'); }

    /** POST /Tuition/DeleteCircularComment (commentId) */
    public function DeleteCircularComment_POST(): void { feed_comment_action('circular', 'delete'); }

    /** POST /Tuition/ViewCircular (ids = কমা দিয়ে) — একই সেশনে একবারই গোনা হয় */
    public function ViewCircular_POST(): void
    {
        $seen = $_SESSION['_tc_seen'] ?? [];
        $new = [];
        foreach (array_slice(explode(',', (string)input_str('ids', '')), 0, 50) as $id) {
            $id = (int)$id;
            if ($id > 0 && !isset($seen[$id])) { $seen[$id] = 1; $new[] = $id; }
        }
        if (count($seen) > 2000) $seen = array_slice($seen, -1000, null, true);
        $_SESSION['_tc_seen'] = $seen;
        $this->db->AddCircularViews($new);
        json_response(['success' => true, 'counted' => count($new)]);
    }

    /** POST /Tuition/DeleteCircularPost (postId) — নিজের পোস্ট অথবা অ্যাডমিন */
    public function DeleteCircularPost_POST(): void
    {
        $me = current_user_id();
        if ($me === null) $this->fail('আগে লগইন করুন।');
        $post = $this->db->GetTuitionCircularPostById(input_int('postId', 0) ?? 0);
        if (!$post) $this->fail('পোস্টটি পাওয়া যায়নি।');
        $isAdmin = current_role() === 'Admin';
        if ($post->UserId !== $me && !$isAdmin) $this->fail('এই পোস্ট মোছার অনুমতি নেই।');
        if (!$this->db->DeleteCircularPost($post->Id)) $this->fail('মোছা যায়নি। আবার চেষ্টা করুন।');
        delete_upload($post->ImageUrl ?? null);
        delete_upload($post->VideoUrl ?? null);
        if ($isAdmin && $post->UserId !== $me) {
            $this->db->CreateNotification($post->UserId, 'Post Removed', 'আপনার একটি Tuition Circular পোস্ট অ্যাডমিন সরিয়ে দিয়েছেন।', 'warning', '/Tuition/Circular', 'fa-trash');
        }
        json_response(['success' => true, 'message' => 'পোস্ট মুছে ফেলা হয়েছে।']);
    }

    /** POST /Tuition/AddCircularComment */
    public function AddCircularComment_POST(): void
    {
        if (session_get('UserName') === null) json_response(['success' => false, 'message' => 'Please login to comment.']);
        $content = input_str('content');
        if (is_blank($content)) json_response(['success' => false, 'message' => 'Comment cannot be empty.']);
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Invalid user.']);
        $content = trim($content);
        if (mb_strlen($content) > 2000) json_response(['success' => false, 'message' => 'Comment is too long.']);

        $postId = input_int('postId', 0);
        $parentId = input_int('parentId', null);
        $sender = session_get('UserFullName') ?? session_get('UserName') ?? 'Someone';

        $rootParentId = null;
        $repliedTo = null;
        if ($parentId !== null) {
            foreach ($this->db->GetTuitionCircularComments($postId) as $top) {
                if ($top->Id === $parentId) { $repliedTo = $top; $rootParentId = $top->Id; break; }
                foreach ($top->Replies as $r) {
                    if ($r->Id === $parentId) { $repliedTo = $r; $rootParentId = $top->Id; break 2; }
                }
            }
            if (!$repliedTo) json_response(['success' => false, 'message' => 'The comment you are replying to no longer exists.']);
        }

        $comment = obj(['PostId' => $postId, 'UserId' => $userId, 'Content' => $content, 'ParentId' => $rootParentId]);
        $err = null;
        if ($this->db->AddTuitionCircularComment($comment, $err)) {
            json_response(['success' => true, 'comment' => [
                'id' => $comment->Id,
                'postId' => $postId,
                'parentId' => $rootParentId,
                'userFullName' => $sender,
                'userProfileImage' => session_get('ProfileImageLink') ?? '',
                'content' => $comment->Content,
                'createdAtUtc' => gmdate('Y-m-d\TH:i:s\Z'),
                'likeCount' => 0,
                'isLiked' => false,
            ]]);
        }
        json_response(['success' => false, 'message' => $err]);
    }

    /** POST /Tuition/ToggleCircularLike */
    public function ToggleCircularLike_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login to like.']);
        $postId = input_int('postId', 0) ?? 0;
        $post = $this->db->GetTuitionCircularPostById($postId);
        if (!$post) json_response(['success' => false, 'message' => 'পোস্টটি পাওয়া যায়নি।']);
        [$liked, $count] = $this->db->ToggleTuitionCircularLike($postId, $userId);
        // প্রথম লাইক (একই মানুষ বারবার লাইক-আনলাইক করলে বারবার নয়)
        if ($liked && $post->UserId !== $userId && empty($_SESSION['_tc_liked'][$postId])) {
            $_SESSION['_tc_liked'][$postId] = 1;
            $who = session_get('UserFullName') ?? session_get('UserName') ?? 'একজন';
            $this->db->CreateNotification($post->UserId, 'New Like', "$who আপনার Tuition Circular পোস্টে লাইক দিয়েছেন।", 'info', '/Tuition/Circular#circular-post-' . $postId, 'fa-thumbs-up');
        }
        json_response(['success' => true, 'isLiked' => $liked, 'count' => $count]);
    }

    /** POST /Tuition/ToggleCircularCommentLike */
    public function ToggleCircularCommentLike_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login to like.']);
        [$liked, $count] = $this->db->ToggleTuitionCircularCommentLike(input_int('commentId', 0), $userId);
        json_response(['success' => true, 'isLiked' => $liked, 'count' => $count]);
    }

    /**
     * POST /Tuition/ApplyCircular
     * টিচার স্টুডেন্টের সার্কুলার পোস্টে Apply করে — অ্যাপ্লিকেশন সরাসরি
     * পোস্টদাতার Student Dashboard এ যায় (অ্যাডমিনের কাছে নয়)।
     */
    public function ApplyCircular_POST(): void
    {
        $teacherId = current_user_id();
        if ($teacherId === null) json_response(['success' => false, 'message' => 'Apply করতে লগইন করুন।']);
        if (current_role() !== 'Teacher') json_response(['success' => false, 'message' => 'শুধু অনুমোদিত টিচাররা Apply করতে পারবেন।']);

        $postId = input_int('postId', 0);
        $message = trim((string)input_str('message', ''));
        $phone = preg_replace('/\D/', '', (string)input_str('phone', ''));
        if (mb_strlen($message) > 1000) json_response(['success' => false, 'message' => 'মেসেজ সর্বোচ্চ ১০০০ অক্ষর হতে পারবে।']);
        if (!preg_match('/^01[3-9]\d{8}$/', $phone)) json_response(['success' => false, 'message' => 'সঠিক মোবাইল নম্বর দিন (যেমন 01712345678)।']);

        $post = null;
        $result = $this->db->ApplyToCircularPost($postId, $teacherId, $message === '' ? null : $message, $phone, $post);
        switch ($result) {
            case 'ok':
                $teacherName = session_get('UserFullName') ?? session_get('UserName') ?? 'A teacher';
                $this->db->CreateNotification($post->UserId, 'New Teacher Application',
                    "$teacherName আপনার টিউশন পোস্টে Apply করেছেন।", 'success',
                    '/User/Dashboard?section=applications', 'fa-chalkboard-teacher');
                json_response(['success' => true, 'message' => 'আপনার অ্যাপ্লিকেশন স্টুডেন্টের কাছে পাঠানো হয়েছে।']);
            case 'exists':
                json_response(['success' => false, 'message' => 'আপনি এই পোস্টে আগেই Apply করেছেন।']);
            case 'own':
                json_response(['success' => false, 'message' => 'নিজের পোস্টে Apply করা যাবে না।']);
            case 'notfound':
                json_response(['success' => false, 'message' => 'পোস্টটি পাওয়া যায়নি।']);
        }
        json_response(['success' => false, 'message' => 'Apply করা যায়নি। আবার চেষ্টা করুন।']);
    }

    /** GET /Tuition/Details/{id} */
    public function Details(): void
    {
        $post = $this->db->GetTuitionPostById(input_int('id', 0), true);
        if (!$post) {
            tempdata_set('Error', 'Tuition post not found.');
            $this->redirectToAction('Circular');
        }
        $this->view(null, $post);
    }

    /** GET /Tuition/Apply/{id} */
    public function Apply(): void
    {
        $id = input_int('id', 0);
        if (session_get('UserName') === null) {
            tempdata_set('Error', 'Please login to apply for this tuition.');
            $this->redirectToAction('Login', 'Account', ['returnUrl' => "/Tuition/Apply/$id"]);
        }
        $post = $this->db->GetTuitionPostById($id);
        if (!$post || $post->Status !== 'Open') {
            tempdata_set('Error', 'This tuition is no longer available.');
            $this->redirectToAction('Circular');
        }
        $model = $this->emptyApplication($id);
        $userId = current_user_id();
        if ($userId !== null && ($user = $this->db->GetUserById($userId))) {
            $model->UserId = $userId;
            $model->ApplicantName = $user->FullName ?? '';
            $model->PhoneNumber = $user->MobileNumber ?? '';
            $model->WhatsAppNumber = $user->MobileNumber ?? '';
            $model->Email = $user->Email ?? '';
        }
        $this->view(null, $model, ['Post' => $post]);
    }

    private function emptyApplication(int $postId): Obj
    {
        return obj([
            'Id' => 0, 'TuitionPostId' => $postId, 'UserId' => null, 'ApplicantName' => '', 'PhoneNumber' => '',
            'WhatsAppNumber' => null, 'Email' => null, 'CvLink' => '', 'CoverNote' => null, 'Status' => 'Pending',
            'AdminNote' => null, 'AppliedAt' => now(), 'UpdatedAt' => null, 'TuitionPost' => null,
        ]);
    }

    /** POST /Tuition/Apply */
    public function Apply_POST(): void
    {
        if (session_get('UserName') === null) {
            tempdata_set('Error', 'Please login to submit your application.');
            $this->redirectToAction('Login', 'Account');
        }

        $model = $this->emptyApplication(input_int('TuitionPostId', 0) ?? 0);
        foreach (['ApplicantName', 'PhoneNumber', 'WhatsAppNumber', 'Email', 'CvLink', 'CoverNote'] as $f) {
            $v = input_str($f);
            $model->$f = ($v === null || trim($v) === '') ? (in_array($f, ['ApplicantName', 'PhoneNumber', 'CvLink'], true) ? '' : null) : $v;
        }

        try {
            // ===== মডেল ভ্যালিডেশন (DataAnnotations এর মতো) =====
            $errors = [];
            if (trim($model->ApplicantName) === '') $errors['ApplicantName'] = 'Name is required';
            if (trim($model->PhoneNumber) === '') $errors['PhoneNumber'] = 'Phone number is required';
            if (trim($model->CvLink) === '') $errors['CvLink'] = 'CV link is required';
            if ($model->Email !== null && !filter_var(trim($model->Email), FILTER_VALIDATE_EMAIL)) $errors['Email'] = 'The Email field is not a valid e-mail address.';
            if ($errors) {
                tempdata_set('Error', 'Validation Failed: ' . implode(' | ', $errors));
                $GLOBALS['__ModelErrors'] = $errors;
                $this->view(null, $model, ['Post' => $this->db->GetTuitionPostById($model->TuitionPostId)]);
            }

            $post = $this->db->GetTuitionPostById($model->TuitionPostId);
            if (!$post || $post->Status !== 'Open') {
                tempdata_set('Error', 'This tuition is no longer available.');
                $this->redirectToAction('Circular');
            }

            // ===== Manual validation =====
            $re = '/^01[3-9]\d{8}$/';
            if (!preg_match($re, trim($model->PhoneNumber))) $errors['PhoneNumber'] = 'Please enter a valid Bangladeshi phone number (e.g., 01712345678).';
            if (!is_blank($model->WhatsAppNumber) && !preg_match($re, trim($model->WhatsAppNumber))) $errors['WhatsAppNumber'] = 'Please enter a valid WhatsApp number (e.g., 01712345678).';
            $cv = trim($model->CvLink);
            if (!str_starts_with($cv, 'http://') && !str_starts_with($cv, 'https://')) $errors['CvLink'] = 'Please enter a valid URL starting with http:// or https://';
            if ($errors) {
                tempdata_set('Error', 'Please fix these errors: ' . implode(' | ', $errors));
                $GLOBALS['__ModelErrors'] = $errors;
                $this->view(null, $model, ['Post' => $post]);
            }

            // ===== Duplicate check =====
            $userId = current_user_id();
            if ($userId !== null && $this->db->HasUserApplied($model->TuitionPostId, $userId)) {
                tempdata_set('Error', 'You have already applied for this tuition.');
                $this->redirectToAction('Details', null, ['id' => $model->TuitionPostId]);
            }

            $model->UserId = $userId;
            $model->PhoneNumber = trim($model->PhoneNumber);
            $model->WhatsAppNumber = is_blank($model->WhatsAppNumber) ? $model->PhoneNumber : trim($model->WhatsAppNumber);
            $model->CvLink = $cv;
            $model->ApplicantName = trim($model->ApplicantName);
            $model->Email = is_blank($model->Email) ? null : trim($model->Email);
            $model->CoverNote = is_blank($model->CoverNote) ? null : trim($model->CoverNote);
            $model->Status = 'Pending';
            $model->Id = $this->db->CreateTuitionApplication($model);

            try {
                foreach ($this->db->GetAdmins() as $admin) {
                    $this->db->CreateNotification((int)$admin->Id, '📩 New Tuition Application',
                        "{$model->ApplicantName} ({$model->PhoneNumber}) applied for: {$post->Title}",
                        'info', '/Admin/TuitionApplications', 'fa-user-check');
                }
            } catch (Throwable $e) {}

            tempdata_set('Success', 'Application submitted successfully! Admin will contact you soon.');
            $this->redirectToAction('ApplySuccess', null, ['id' => $model->Id]);
        } catch (Throwable $e) {
            tempdata_set('Error', 'Error: ' . $e->getMessage());
            $this->view(null, $model, ['Post' => $this->db->GetTuitionPostById($model->TuitionPostId)]);
        }
    }

    /** GET /Tuition/ApplySuccess/{id} */
    public function ApplySuccess(): void
    {
        $app = $this->db->GetTuitionApplicationById(input_int('id', 0));
        if (!$app) {
            tempdata_set('Error', 'Application not found.');
            $this->redirectToAction('Circular');
        }
        $this->view(null, $app);
    }
}
