<?php
/**
 * প্রোফাইল টাইমলাইনের পোস্ট (ফেসবুকের মতো)।
 *
 *  GET  /Post/Details/{id}            → একটা পোস্ট আলাদা পেজে (নোটিফিকেশন থেকে আসে)
 *  GET  /Post/Feed?userId=&before= → "আরো পোস্ট" (HTML)
 *  GET  /Post/Reactors?type=post|comment&id=  → কে কী রিয়েক্ট দিয়েছে (JSON)
 *  POST /Post/Create | Update | Delete
 *  POST /Post/React          (postId, reaction — খালি হলে রিয়েক্ট তুলে নেয়)
 *  POST /Post/Comment        (postId, content, parentId?)
 *  POST /Post/EditComment | DeleteComment | ReactComment
 *  POST /Post/Share          (postId, content, privacy) — পাবলিক পোস্ট নিজের প্রোফাইলে শেয়ার
 *  GET  /Post/Sharers?id=     → কে কে শেয়ার করেছে (JSON)
 *  GET  /Post/MentionSuggest?postId=&q= → কমেন্টে @ লিখলে নামের তালিকা (JSON)
 *
 * প্রাইভেসি: Public = সবাই, Friends = শুধু ফ্রেন্ড, OnlyMe = শুধু নিজে। অ্যাডমিন সব দেখতে ও মুছতে পারে।
 */
class PostController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
    /** টিচারের Study Material এ যে ফাইল দেওয়া যায় */
    private const FILE_EXTS = ['.pdf', '.doc', '.docx', '.ppt', '.pptx', '.xls', '.xlsx', '.txt', '.jpg', '.jpeg', '.png', '.webp', '.zip'];
    private const MAX_FILE = 10 * 1024 * 1024;
    private const MAX_POST_LEN = 5000;
    private const MAX_COMMENT_LEN = 1500;

    private function isAdmin(): bool { return current_role() === 'Admin'; }

    private function me(): int
    {
        $id = current_user_id();
        if ($id === null) json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
        return $id;
    }

    private function fail(string $msg): void { json_response(['success' => false, 'message' => $msg]); }

    /** পোস্ট খুঁজে বের করে দেখার অনুমতি যাচাই করে */
    private function viewablePost(int $postId, ?int $me): Obj
    {
        $post = $postId > 0 ? $this->db->GetProfilePostById($postId) : null;
        if (!$post || !$this->db->CanViewProfilePost($post, $me, $this->isAdmin())) $this->fail('পোস্টটি পাওয়া যায়নি বা দেখার অনুমতি নেই।');
        return $post;
    }

    private function cleanText(?string $s): string
    {
        $s = str_replace(["\r\n", "\r"], "\n", (string)$s);
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s) ?? '';
        $s = preg_replace("/\n{4,}/", "\n\n\n", $s) ?? $s;
        return trim($s);
    }

    private function privacyInput(): string
    {
        $p = (string)input_str('privacy', 'Public');
        return in_array($p, Db::POST_PRIVACY, true) ? $p : 'Public';
    }

    /** ছবি যাচাই করে সেভ করে। ছবি না থাকলে null। */
    private function saveImage(int $userId): ?string
    {
        $file = upload_file('image');
        if (!$file) return null;
        $ext = strtolower('.' . pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::IMAGE_EXTS, true)) $this->fail('শুধু JPG, PNG, GIF বা WEBP ছবি দেওয়া যাবে।');
        if ($file['size'] > 5 * 1024 * 1024) $this->fail('ছবি ৫ MB এর কম হতে হবে।');
        if (@getimagesize($file['tmp_name']) === false) $this->fail('ফাইলটি সঠিক ছবি নয়।');
        return save_upload($file, 'profile-posts', $userId . '_' . ticks() . '_' . bin2hex(random_bytes(3)), $ext);
    }

    /** Study Material এর ফাইল সেভ করে → ['url','name','size','ext'] অথবা null */
    private function saveAttachment(int $userId): ?array
    {
        $file = upload_file('file');
        if (!$file) return null;
        $ext = strtolower('.' . pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::FILE_EXTS, true)) $this->fail('শুধু PDF, Word, PowerPoint, Excel, TXT, ZIP বা ছবি ফাইল দেওয়া যাবে।');
        if ($file['size'] > self::MAX_FILE) $this->fail('ফাইল ১০ MB এর কম হতে হবে।');
        if (in_array($ext, self::IMAGE_EXTS, true) && @getimagesize($file['tmp_name']) === false) $this->fail('ছবিটি সঠিক নয়।');
        if ($ext === '.pdf') {
            $head = (string)@file_get_contents($file['tmp_name'], false, null, 0, 5);
            if ($head !== '%PDF-') $this->fail('ফাইলটি সঠিক PDF নয়।');
        }
        $name = preg_replace('/[^\p{L}\p{N} ._()\-]+/u', '_', pathinfo((string)$file['name'], PATHINFO_FILENAME)) ?: 'file';
        $name = mb_substr(trim($name), 0, 80) . $ext;
        $url = save_upload($file, 'post-files', $userId . '_' . ticks() . '_' . bin2hex(random_bytes(4)), $ext);
        return ['url' => $url, 'name' => $name, 'size' => (int)$file['size'], 'ext' => ltrim($ext, '.')];
    }

    private function renderPost(Obj $post, int $me): string
    {
        $post = $this->db->hydrateProfilePosts([$post], $me, $this->isAdmin())[0];
        ob_start();
        partial('Post/_Post', ['post' => $post, 'viewerId' => $me, 'viewerIsAdmin' => $this->isAdmin()]);
        return ob_get_clean();
    }

    private function renderComment(Obj $c, Obj $post, int $me): string
    {
        ob_start();
        partial('Post/_Comment', ['c' => $c, 'post' => $post, 'viewerId' => $me, 'viewerIsAdmin' => $this->isAdmin(), 'isReply' => $c->ParentId !== null]);
        return ob_get_clean();
    }

    private function name(): string { return session_get('UserFullName') ?? session_get('UserName') ?? 'একজন ইউজার'; }

    private function postLink(int $postId): string { return '/Post/Details/' . $postId; }

    // ============================================================
    // PAGES / READ
    // ============================================================

    /** GET /Post/Details/{id} */
    public function Details(): void
    {
        $me = current_user_id();
        if ($me === null) redirect('/Account/Login?returnUrl=' . rawurlencode('/Post/Details/' . (int)input_int('id', 0)));
        $post = $this->db->GetProfilePostById(input_int('id', 0) ?? 0);
        if (!$post || !$this->db->CanViewProfilePost($post, $me, $this->isAdmin())) not_found('পোস্টটি পাওয়া যায়নি, মুছে ফেলা হয়েছে, অথবা আপনার দেখার অনুমতি নেই।');
        $post = $this->db->hydrateProfilePosts([$post], $me, $this->isAdmin())[0];
        $this->view('Post/Details', $post, ['ViewerIsAdmin' => $this->isAdmin()]);
    }

    /** GET /Post/Feed?userId=&before=  → পোস্টের HTML + পরের পেজ আছে কিনা (হেডারে) */
    public function Feed(): void
    {
        $me = current_user_id();
        if ($me === null) { http_response_code(401); exit; }
        $owner = input_int('userId', 0) ?? 0;
        $before = input_int('before', 0) ?: null;
        $types = ['materials' => ['material'], 'achievements' => ['achievement'], 'updates' => []][(string)input_str('tab', '')] ?? [];
        $posts = $this->db->GetProfilePosts($owner, $me, $this->isAdmin(), $before, 10, $types);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Has-More: ' . (count($posts) === 10 ? '1' : '0'));
        foreach ($posts as $p) partial('Post/_Post', ['post' => $p, 'viewerId' => $me, 'viewerIsAdmin' => $this->isAdmin()]);
        exit;
    }

    /** GET /Post/Reactors?type=post|comment&id= */
    public function Reactors(): void
    {
        $me = $this->me();
        $type = input_str('type', 'post') === 'comment' ? 'comment' : 'post';
        $id = input_int('id', 0) ?? 0;
        if ($type === 'comment') {
            $c = $this->db->GetPostCommentById($id);
            if (!$c) $this->fail('কমেন্টটি পাওয়া যায়নি।');
            $this->viewablePost($c->PostId, $me);
        } else {
            $this->viewablePost($id, $me);
        }
        $meta = reaction_meta();
        json_response(['success' => true, 'people' => array_map(fn($p) => [
            'id' => $p->Id, 'name' => $p->Name, 'image' => $p->Image, 'initials' => $p->Initials,
            'role' => $p->Role === 'Teacher' ? 'Teacher' : 'Student', 'reaction' => 'like', 'emoji' => like_badge_html(),
        ], $this->db->GetReactors($type, $id))]);
    }

    // ============================================================
    // POSTS
    // ============================================================

    /** POST /Post/Create */
    public function Create_POST(): void
    {
        $me = $this->me();
        if ($this->isAdmin()) $this->fail('অ্যাডমিন অ্যাকাউন্ট থেকে প্রোফাইল পোস্ট করা যায় না।');
        $content = $this->cleanText(input_str('content'));
        if (mb_strlen($content) > self::MAX_POST_LEN) $this->fail('পোস্ট সর্বোচ্চ ' . self::MAX_POST_LEN . ' অক্ষর হতে পারে।');
        if ($this->db->CountRecentProfileActivity('post', $me, 60) >= 20) $this->fail('এক ঘণ্টায় অনেক পোস্ট করেছেন। কিছুক্ষণ পর আবার চেষ্টা করুন।');
        $type = (string)input_str('type', 'post');
        if (!in_array($type, Db::POST_TYPES, true)) $type = 'post';
        if (in_array($type, Db::TEACHER_POST_TYPES, true) && current_role() !== 'Teacher') $this->fail('এই ধরনের পোস্ট শুধু টিচাররা দিতে পারেন।');
        [$meta, $options] = $this->typeInput($type, $content);
        if ($type === 'material') {
            $meta['file'] = $this->saveAttachment($me);
            if ($meta['file'] === null) { unset($meta['file']); if ($content === '') $this->fail('ফাইল (PDF/Doc) দিন অথবা কিছু লিখুন।'); }
        }
        $image = in_array($type, ['poll', 'material'], true) ? null : $this->saveImage($me);
        if ($type === 'post' && $content === '' && $image === null) $this->fail('কিছু লিখুন অথবা একটি ছবি দিন।');

        $id = $this->db->CreateProfilePost($me, $content, $image, $this->privacyInput(), null, $type, $meta);
        if ($type === 'poll') $this->db->CreatePollOptions($id, $options);
        $this->db->TouchActivity($me, $type === 'study' ? 5 : 3);
        $post = $this->db->GetProfilePostById($id);
        $msg = ['study' => 'স্টাডি আপডেট পোস্ট হয়েছে। দারুণ! 📚', 'material' => 'স্টাডি ম্যাটেরিয়াল শেয়ার হয়েছে। 📎', 'notice' => 'নোটিশ পোস্ট হয়েছে।', 'update' => 'ক্লাস আপডেট পোস্ট হয়েছে।', 'question' => 'প্রশ্ন পোস্ট হয়েছে।', 'achievement' => 'অভিনন্দন! 🏆', 'poll' => 'পোল পোস্ট হয়েছে।'][$type] ?? 'পোস্ট হয়েছে।';
        json_response(['success' => true, 'message' => $msg, 'id' => $id, 'html' => $this->renderPost($post, $me)]);
    }

    /**
     * পোস্টের ধরন অনুযায়ী বাড়তি ঘর যাচাই।
     * @return array{0: array, 1: string[]} [meta, poll options]
     */
    private function typeInput(string $type, string $content): array
    {
        $one = fn($k, $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str($k, ''))), 0, $max);
        switch ($type) {
            case 'study':
                $subject = $one('subject', 40);
                $minutes = input_int('minutes', 0) ?? 0;
                if ($minutes < 0 || $minutes > 960) $this->fail('পড়ার সময় ০ থেকে ৯৬০ মিনিটের মধ্যে দিন।');
                if ($subject === '' && $minutes === 0 && $content === '') $this->fail('কোন বিষয় পড়েছেন বা কতক্ষণ পড়েছেন লিখুন।');
                return [array_filter(['subject' => $subject, 'minutes' => $minutes]), []];
            case 'question':
                if ($content === '') $this->fail('আপনার প্রশ্নটি লিখুন।');
                $subject = $one('subject', 40);
                return [$subject !== '' ? ['subject' => $subject] : [], []];
            case 'achievement':
                $title = $one('title', 80);
                if ($title === '') $this->fail('অর্জনের শিরোনাম লিখুন (যেমন: SSC তে GPA 5)।');
                return [['title' => $title], []];
            case 'material':
                $title = $one('title', 100);
                $subject = $one('subject', 40);
                if ($title === '') $this->fail('ম্যাটেরিয়ালের নাম লিখুন (যেমন: Quadratic Equation Notes)।');
                return [array_filter(['title' => $title, 'subject' => $subject]), []];
            case 'update':
                if ($content === '') $this->fail('ক্লাস আপডেট লিখুন।');
                $subject = $one('subject', 40);
                return [$subject !== '' ? ['subject' => $subject] : [], []];
            case 'notice':
                if ($content === '') $this->fail('নোটিশটি লিখুন।');
                $title = $one('title', 80);
                return [$title !== '' ? ['title' => $title] : [], []];
            case 'poll':
                if ($content === '') $this->fail('পোলের প্রশ্ন লিখুন।');
                $raw = $_POST['options'] ?? [];
                if (!is_array($raw)) $raw = [];
                $opts = [];
                foreach ($raw as $o) {
                    $o = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)$o)), 0, 80);
                    if ($o !== '' && !in_array(mb_strtolower($o), array_map('mb_strtolower', $opts), true)) $opts[] = $o;
                }
                if (count($opts) < 2) $this->fail('অন্তত ২টা আলাদা অপশন দিন।');
                if (count($opts) > 6) $this->fail('সর্বোচ্চ ৬টা অপশন দেওয়া যায়।');
                return [[], $opts];
        }
        return [[], []];
    }

    /** POST /Post/Vote (postId, optionId — 0 দিলে ভোট তুলে নেয়) */
    public function Vote_POST(): void
    {
        $me = $this->me();
        $post = $this->viewablePost(input_int('postId', 0) ?? 0, $me);
        if ($post->PostType !== 'poll') $this->fail('এটা পোল নয়।');
        if (!$this->db->VotePoll($post->Id, $me, input_int('optionId', 0) ?? 0)) $this->fail('অপশনটি পাওয়া যায়নি।');
        $this->db->TouchActivity($me, 1);
        $poll = $this->db->GetPolls([$post->Id], $me)[$post->Id] ?? null;
        ob_start();
        partial('Post/_Poll', ['poll' => $poll, 'postId' => $post->Id]);
        json_response(['success' => true, 'html' => ob_get_clean()]);
    }

    /** POST /Post/Update  (postId, content, privacy, removeImage, image?) */
    public function Update_POST(): void
    {
        $me = $this->me();
        $post = $this->db->GetProfilePostById(input_int('postId', 0) ?? 0);
        if (!$post || $post->UserId !== $me) $this->fail('শুধু নিজের পোস্ট এডিট করা যায়।');

        $content = $this->cleanText(input_str('content'));
        if (mb_strlen($content) > self::MAX_POST_LEN) $this->fail('পোস্ট সর্বোচ্চ ' . self::MAX_POST_LEN . ' অক্ষর হতে পারে।');
        $isShare = $post->SharedPostId !== null;
        $type = $post->PostType;
        $meta = $post->Meta;
        $oldFile = $meta['file'] ?? null;
        if (!$isShare && in_array($type, ['study', 'question', 'achievement', 'material', 'update', 'notice'], true)) {
            [$meta] = $this->typeInput($type, $content);
            if ($type === 'material') {
                $newFile = $this->saveAttachment($me);
                if ($newFile) $meta['file'] = $newFile; elseif ($oldFile) $meta['file'] = $oldFile;
            }
        }
        if ($type === 'poll' && $content === '') $this->fail('পোলের প্রশ্ন খালি রাখা যাবে না।');
        $newImage = ($isShare || in_array($type, ['poll', 'material'], true)) ? null : $this->saveImage($me);
        $image = $post->ImageUrl;
        if ($newImage !== null || input_bool('removeImage')) $image = $newImage;
        if (!$isShare && $type === 'post' && $content === '' && $image === null) {
            if ($newImage) delete_upload($newImage);
            $this->fail('পোস্ট খালি রাখা যাবে না — কিছু লিখুন অথবা ছবি রাখুন।');
        }

        $changed = $content !== $post->Content || $image !== $post->ImageUrl || $meta != $post->Meta;
        $this->db->UpdateProfilePost($post->Id, $content, $image, $this->privacyInput(), $changed);
        if ($meta != $post->Meta) $this->db->UpdateProfilePostMeta($post->Id, $meta);
        if ($post->ImageUrl && $image !== $post->ImageUrl) delete_upload($post->ImageUrl);
        if ($oldFile && ($meta['file']['url'] ?? null) !== $oldFile['url']) delete_upload($oldFile['url']);

        $fresh = $this->db->GetProfilePostById($post->Id);
        json_response(['success' => true, 'message' => 'পোস্ট আপডেট হয়েছে।', 'html' => $this->renderPost($fresh, $me)]);
    }

    /** POST /Post/Delete */
    public function Delete_POST(): void
    {
        $me = $this->me();
        $post = $this->db->GetProfilePostById(input_int('postId', 0) ?? 0);
        if (!$post) $this->fail('পোস্টটি আগেই মুছে গেছে।');
        if ($post->UserId !== $me && !$this->isAdmin()) $this->fail('এই পোস্ট মোছার অনুমতি নেই।');
        if (!$this->db->DeleteProfilePost($post->Id)) $this->fail('মোছা যায়নি। আবার চেষ্টা করুন।');
        delete_upload($post->ImageUrl);
        if (!empty($post->Meta['file']['url'])) delete_upload((string)$post->Meta['file']['url']);
        if ($post->UserId !== $me) {
            $this->db->CreateNotification($post->UserId, 'Post Removed', 'আপনার একটি প্রোফাইল পোস্ট অ্যাডমিন সরিয়ে দিয়েছেন।', 'warning', '/Profile/Show/' . $post->UserId, 'fa-trash');
        }
        json_response(['success' => true, 'message' => 'পোস্ট মুছে ফেলা হয়েছে।']);
    }

    /** POST /Post/React (postId, reaction) */
    public function React_POST(): void
    {
        $me = $this->me();
        $post = $this->viewablePost(input_int('postId', 0) ?? 0, $me);
        $reaction = (string)input_str('reaction', '');
        $reaction = $reaction !== '' ? 'like' : null;   // শুধু Like
        $res = $this->db->SetPostReaction($post->Id, $me, $reaction);
        if ($res['prev'] === null && $reaction !== null && $post->UserId !== $me) {
            $this->db->CreateNotification($post->UserId, 'New Like', $this->name() . ' আপনার পোস্টে লাইক দিয়েছেন।', 'info', $this->postLink($post->Id), 'fa-thumbs-up');
        }
        json_response(['success' => true, 'mine' => $reaction, 'counts' => $res['counts'], 'total' => $res['total']]);
    }

    // ============================================================
    // COMMENTS
    // ============================================================

    /** POST /Post/Comment (postId, content, parentId?) */
    public function Comment_POST(): void
    {
        $me = $this->me();
        $post = $this->viewablePost(input_int('postId', 0) ?? 0, $me);
        $content = $this->cleanText(input_str('content'));
        if ($content === '') $this->fail('কমেন্ট লিখুন।');
        if (mb_strlen($content) > self::MAX_COMMENT_LEN) $this->fail('কমেন্ট সর্বোচ্চ ' . self::MAX_COMMENT_LEN . ' অক্ষর।');
        if ($this->db->CountRecentProfileActivity('comment', $me, 10) >= 60) $this->fail('খুব দ্রুত অনেক কমেন্ট করছেন। একটু পর চেষ্টা করুন।');

        $parentId = input_int('parentId', 0) ?: null;
        $parent = null;
        if ($parentId) {
            $parent = $this->db->GetPostCommentById($parentId);
            if (!$parent || $parent->PostId !== $post->Id) $this->fail('যে কমেন্টে রিপ্লাই দিচ্ছেন সেটি আর নেই।');
            if ($parent->ParentId) $parentId = $parent->ParentId; // রিপ্লাইয়ের রিপ্লাই → একই থ্রেডে
        }

        [$content, $mentioned] = $this->db->NormalizeMentions($content);
        $id = $this->db->AddPostComment($post->Id, $me, $parentId, $content);
        $this->db->TouchActivity($me, 1);
        $c = $this->db->GetPostCommentById($id, $me);

        $name = $this->name();
        $notified = [$me => true];
        $this->notifyMentions($mentioned, $post, $content, $notified);
        if ($parent && !isset($notified[$parent->UserId])) {
            $this->db->CreateNotification($parent->UserId, 'New Reply 💬', "$name আপনার কমেন্টে রিপ্লাই দিয়েছেন: " . str_preview($content, 60), 'info', $this->postLink($post->Id), 'fa-reply');
            $notified[$parent->UserId] = true;
        }
        if (!isset($notified[$post->UserId])) {
            $this->db->CreateNotification($post->UserId, 'New Comment 💬', "$name আপনার পোস্টে কমেন্ট করেছেন: " . str_preview($content, 60), 'info', $this->postLink($post->Id), 'fa-comment');
        }

        json_response(['success' => true, 'id' => $id, 'parentId' => $parentId, 'count' => $this->db->CountPostComments($post->Id),
            'html' => $this->renderComment($c, $post, $me)]);
    }

    /** POST /Post/EditComment (commentId, content) */
    public function EditComment_POST(): void
    {
        $me = $this->me();
        $c = $this->db->GetPostCommentById(input_int('commentId', 0) ?? 0, $me);
        if (!$c || $c->UserId !== $me) $this->fail('শুধু নিজের কমেন্ট এডিট করা যায়।');
        $post = $this->viewablePost($c->PostId, $me);
        $content = $this->cleanText(input_str('content'));
        if ($content === '') $this->fail('কমেন্ট খালি রাখা যাবে না।');
        if (mb_strlen($content) > self::MAX_COMMENT_LEN) $this->fail('কমেন্ট সর্বোচ্চ ' . self::MAX_COMMENT_LEN . ' অক্ষর।');
        [$content, $mentioned] = $this->db->NormalizeMentions($content);
        if ($content !== $c->Content) {
            [, $oldMap] = mention_plain($c->Content);
            $this->db->UpdatePostComment($c->Id, $content);
            $notified = [$me => true];
            foreach ($oldMap as $uid) $notified[$uid] = true; // আগে যাদের মেনশন করা ছিল তাদের আবার জানানো হয় না
            $this->notifyMentions($mentioned, $post, $content, $notified);
        }
        $c = $this->db->GetPostCommentById($c->Id, $me);
        [$plain, $map] = mention_plain($c->Content);
        json_response(['success' => true, 'contentHtml' => comment_text_html($c->Content), 'raw' => $plain, 'mentions' => (object)$map, 'isEdited' => $c->IsEdited]);
    }

    /** POST /Post/DeleteComment (commentId) — কমেন্টকারী, পোস্টের মালিক বা অ্যাডমিন */
    public function DeleteComment_POST(): void
    {
        $me = $this->me();
        $c = $this->db->GetPostCommentById(input_int('commentId', 0) ?? 0);
        if (!$c) $this->fail('কমেন্টটি আগেই মুছে গেছে।');
        $post = $this->db->GetProfilePostById($c->PostId);
        if (!$post) $this->fail('পোস্টটি আর নেই।');
        if ($c->UserId !== $me && $post->UserId !== $me && !$this->isAdmin()) $this->fail('এই কমেন্ট মোছার অনুমতি নেই।');
        $n = $this->db->DeletePostComment($c->Id);
        json_response(['success' => true, 'removed' => $n, 'count' => $this->db->CountPostComments($post->Id)]);
    }

    /** মেনশন হওয়া মানুষদের নোটিফিকেশন (যারা পোস্টটি দেখতে পারে শুধু তারা) */
    private function notifyMentions(array $userIds, Obj $post, string $content, array &$notified): void
    {
        $name = $this->name();
        [$plain] = mention_plain($content);
        foreach (array_slice($userIds, 0, 20) as $uid) {
            if (isset($notified[$uid])) continue;
            $notified[$uid] = true;
            $u = $this->db->GetUserById($uid);
            if (!$u || !$this->db->CanViewProfilePost($post, $uid, $u->Role === 'Admin')) continue;
            $this->db->CreateNotification($uid, 'You were mentioned 📣', "$name একটি কমেন্টে আপনাকে mention করেছেন: " . str_preview($plain, 60),
                'info', $this->postLink($post->Id), 'fa-at');
        }
    }

    /** GET /Post/MentionSuggest?postId=&q= */
    public function MentionSuggest(): void
    {
        $me = $this->me();
        $postId = input_int('postId', 0) ?: null;
        if ($postId) $this->viewablePost($postId, $me);
        $q = mb_substr(trim((string)input_str('q', '')), 0, 40);
        json_response(['success' => true, 'people' => $this->db->MentionSuggestions($me, $postId, $q, 8)]);
    }

    // ============================================================
    // SHARE
    // ============================================================

    /** POST /Post/Share (postId, content, privacy) — শুধু পাবলিক পোস্ট শেয়ার করা যায় */
    public function Share_POST(): void
    {
        $me = $this->me();
        if ($this->isAdmin()) $this->fail('অ্যাডমিন অ্যাকাউন্ট থেকে শেয়ার করা যায় না।');
        $post = $this->viewablePost(input_int('postId', 0) ?? 0, $me);
        // শেয়ার করা পোস্ট আবার শেয়ার করলে মূল পোস্টটাই শেয়ার হয়
        if ($post->SharedPostId) {
            $orig = $this->db->GetProfilePostById($post->SharedPostId);
            if (!$orig) $this->fail('মূল পোস্টটি আর নেই।');
            $post = $orig;
        }
        if ($post->Privacy !== 'Public') $this->fail('শুধু Public পোস্ট শেয়ার করা যায়।');
        $content = $this->cleanText(input_str('content'));
        if (mb_strlen($content) > self::MAX_POST_LEN) $this->fail('লেখা সর্বোচ্চ ' . self::MAX_POST_LEN . ' অক্ষর।');
        if ($this->db->CountRecentProfileActivity('post', $me, 60) >= 20) $this->fail('এক ঘণ্টায় অনেক পোস্ট করেছেন। কিছুক্ষণ পর আবার চেষ্টা করুন।');
        if ($this->db->CountRecentShares($me, $post->Id, 2) > 0) $this->fail('এই পোস্টটি এইমাত্র শেয়ার করেছেন।');

        $id = $this->db->CreateProfilePost($me, $content, null, $this->privacyInput(), $post->Id);
        if ($post->UserId !== $me) {
            $this->db->CreateNotification($post->UserId, 'Post Shared 🔁', $this->name() . ' আপনার পোস্ট শেয়ার করেছেন।', 'info', $this->postLink($id), 'fa-share');
        }
        $new = $this->db->GetProfilePostById($id);
        $shareCount = count($this->db->GetSharers($post->Id));
        json_response(['success' => true, 'message' => 'আপনার প্রোফাইলে শেয়ার হয়েছে।', 'id' => $id, 'originalId' => $post->Id,
            'shareCount' => $shareCount, 'html' => $this->renderPost($new, $me), 'ownerId' => $me]);
    }

    /** GET /Post/Sharers?id= */
    public function Sharers(): void
    {
        $me = $this->me();
        $this->viewablePost(input_int('id', 0) ?? 0, $me);
        $list = [];
        foreach ($this->db->GetSharers(input_int('id', 0) ?? 0) as $p) {
            // যার শেয়ার পোস্ট দর্শক দেখতে পারে না, তার নাম দেখাই না
            $share = $this->db->GetProfilePostById($p->ShareId);
            if (!$share || !$this->db->CanViewProfilePost($share, $me, $this->isAdmin())) continue;
            $list[] = ['id' => $p->Id, 'name' => $p->Name, 'image' => $p->Image, 'initials' => $p->Initials,
                'role' => $p->Role === 'Teacher' ? 'Teacher' : 'Student', 'shareId' => $p->ShareId];
        }
        json_response(['success' => true, 'people' => $list]);
    }

    /** POST /Post/ReactComment (commentId, reaction) */
    public function ReactComment_POST(): void
    {
        $me = $this->me();
        $c = $this->db->GetPostCommentById(input_int('commentId', 0) ?? 0);
        if (!$c) $this->fail('কমেন্টটি পাওয়া যায়নি।');
        $this->viewablePost($c->PostId, $me);
        $reaction = (string)input_str('reaction', '');
        $reaction = $reaction !== '' ? 'like' : null;   // শুধু Like
        $res = $this->db->SetCommentReaction($c->Id, $me, $reaction);
        if ($res['prev'] === null && $reaction !== null && $c->UserId !== $me) {
            $this->db->CreateNotification($c->UserId, 'New Like', $this->name() . ' আপনার কমেন্টে লাইক দিয়েছেন।', 'info', $this->postLink($c->PostId), 'fa-thumbs-up');
        }
        json_response(['success' => true, 'mine' => $reaction, 'counts' => $res['counts'], 'total' => $res['total']]);
    }
}
