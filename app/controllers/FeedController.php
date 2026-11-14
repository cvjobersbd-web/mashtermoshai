<?php
/**
 * Educational Feed — ইউজার/টিচার শিক্ষামূলক পোস্ট দেয় (লেখা আর ছবি); ভিডিও / YouTube শুধু অ্যাডমিন।
 * নতুন পোস্ট প্রথমে Pending থাকে → Admin → Educational Feed থেকে Approve করলে ফিডে দেখায়।
 *
 *  GET  /Feed                    ফিড (?tab=mine, ?subject=, ?q=)
 *  GET  /Feed/More?before=       "আরো পোস্ট" (HTML, হেডারে X-Has-More)
 *  GET  /Feed/Post/{id}          একটা পোস্ট + কমেন্ট
 *  GET  /Feed/Comments/{id}      কমেন্টের তালিকা (HTML)
 *  POST /Feed/Create | Delete | Like | Comment | DeleteComment
 */
class FeedController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
    private const VIDEO_EXTS = ['.mp4', '.webm', '.mov', '.m4v'];
    private const MAX_IMAGES = 6;
    private const MAX_IMAGE = 5 * 1024 * 1024;
    private const MAX_VIDEO = 50 * 1024 * 1024;
    private const MAX_POST_LEN = 5000;
    private const MAX_COMMENT_LEN = 1000;
    private const PAGE = 10;

    private function fail(string $m): void { json_response(['success' => false, 'message' => $m]); }

    private function me(): int
    {
        $id = current_user_id();
        if ($id === null) $this->fail('আগে লগইন করুন।');
        return $id;
    }

    /** সার্ভারে ভিডিও আপলোডের আসল সীমা (php.ini এর upload_max_filesize / post_max_size ও মানে) */
    public static function videoLimit(): int
    {
        $b = function ($v) {
            $v = trim((string)$v); if ($v === '' || $v === '-1' || $v === '0') return PHP_INT_MAX;
            $n = (float)$v; $u = strtolower(substr($v, -1));
            return (int)($u === 'g' ? $n * 1073741824 : ($u === 'm' ? $n * 1048576 : ($u === 'k' ? $n * 1024 : $n)));
        };
        return (int)min(self::MAX_VIDEO, $b(ini_get('upload_max_filesize')), max(1, $b(ini_get('post_max_size')) - 512 * 1024));
    }

    private function canSee(?Obj $p, ?int $me): bool
    {
        if (!$p) return false;
        if (is_admin() || ($me !== null && $p->UserId === $me)) return true;
        return $p->Status === 'Approved';
    }

    private function render(string $partial, array $vars): string
    {
        ob_start(); partial($partial, $vars); return ob_get_clean();
    }

    // ============================================================
    // PAGES
    // ============================================================

    /** GET /Feed */
    public function Index(): void
    {
        $me = current_user_id();
        $tab = input_str('tab', '') === 'mine' && $me !== null ? 'mine' : 'all';
        $subject = mb_substr(trim((string)input_str('subject', '')), 0, 60);
        $q = mb_substr(trim((string)input_str('q', '')), 0, 60);
        $posts = $tab === 'mine' ? $this->db->GetMyEduPosts($me, null, self::PAGE) : $this->db->GetEduFeed($me, null, self::PAGE, $subject, $q);
        $myPending = 0;
        if ($me !== null) {
            try { $myPending = (int)$this->db->scalar("SELECT COUNT(*) FROM `EduPosts` WHERE `UserId` = :u AND `Status` = 'Pending'", ['u' => $me]); } catch (Throwable $e) { }
        }
        $this->view(null, $posts, [
            'Tab' => $tab, 'Subject' => $subject, 'Q' => $q, 'Subjects' => $this->db->GetEduSubjects(),
            'MyPending' => $myPending, 'HasMore' => count($posts) === self::PAGE, 'VideoLimit' => self::videoLimit(),
        ]);
    }

    /** GET /Feed/More?before=&tab=&subject=&q= */
    public function More(): void
    {
        $me = current_user_id();
        $before = input_int('before', 0) ?: null;
        $author = input_int('author', 0);
        $posts = $author > 0 ? $this->db->GetUserEduPosts($author, $me, $before, self::PAGE)
            : (input_str('tab', '') === 'mine' && $me !== null
            ? $this->db->GetMyEduPosts($me, $before, self::PAGE)
            : $this->db->GetEduFeed($me, $before, self::PAGE, mb_substr(trim((string)input_str('subject', '')), 0, 60), mb_substr(trim((string)input_str('q', '')), 0, 60)));
        header('Content-Type: text/html; charset=utf-8');
        header('X-Has-More: ' . (count($posts) === self::PAGE ? '1' : '0'));
        foreach ($posts as $p) partial('Feed/_Post', ['p' => $p, 'me' => $me]);
        exit;
    }

    /** GET /Feed/Post/{id} */
    public function Post(): void
    {
        $me = current_user_id();
        $p = $this->db->GetEduPost((int)input_int('id', 0), $me);
        if (!$this->canSee($p, $me)) not_found('পোস্টটি পাওয়া যায়নি, মুছে ফেলা হয়েছে, অথবা এখনো অ্যাডমিনের রিভিউতে আছে।');
        $this->view(null, $p, ['Comments' => $p->Status === 'Approved' ? $this->db->GetEduComments($p->Id) : []]);
    }

    /** GET /Feed/Comments/{id} */
    public function Comments(): void
    {
        $me = current_user_id();
        $p = $this->db->GetEduPost((int)input_int('id', 0), $me);
        if (!$p || $p->Status !== 'Approved') { http_response_code(404); exit; }
        header('Content-Type: text/html; charset=utf-8');
        foreach ($this->db->GetEduComments($p->Id) as $c) partial('Feed/_Comment', ['c' => $c, 'p' => $p, 'me' => $me]);
        exit;
    }

    // ============================================================
    // ACTIONS
    // ============================================================

    /** POST /Feed/Create — content, subject, images[] / video / youtube */
    public function Create_POST(): void
    {
        $me = $this->me();
        $isAdmin = is_admin();
        $isInstructor = is_instructor($me);   // Instructor এর পোস্ট Approve ছাড়াই প্রকাশ হয়
        // টিচার, শিক্ষার্থী, ইউজার — সবাই পোস্ট করতে পারে; অ্যাডমিন Approve করলে ফিডে দেখায়
        $content = str_replace(["\r\n", "\r"], "\n", (string)input_str('content', ''));
        $content = trim(preg_replace("/\n{4,}/", "\n\n\n", preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content) ?? '') ?? '');
        if (mb_strlen($content) > self::MAX_POST_LEN) $this->fail('পোস্ট সর্বোচ্চ ' . self::MAX_POST_LEN . ' অক্ষর হতে পারে।');
        $subject = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str('subject', '')))), 0, 60);
        if (!$isAdmin && !$isInstructor && $this->db->CountRecentEdu('post', $me, 60) >= 10) $this->fail('এক ঘণ্টায় অনেক পোস্ট করেছেন। কিছুক্ষণ পর আবার চেষ্টা করুন।');

        // ----- YouTube লিংক -----
        $yt = '';
        $ytRaw = trim((string)input_str('youtube', ''));
        if ($ytRaw !== '') {
            $yt = self::youtubeId($ytRaw);
            if ($yt === '') $this->fail('YouTube লিংকটি সঠিক নয় (যেমন: https://youtu.be/xxxxxxxxxxx)।');
        }
        // ----- Facebook ভিডিওর লিংক -----
        $fb = '';
        $fbRaw = trim((string)input_str('facebook', ''));
        if ($fbRaw !== '') {
            $fb = self::facebookVideoUrl($fbRaw);
            if ($fb === '') $this->fail('Facebook ভিডিওর লিংকটি সঠিক নয় (যেমন: https://www.facebook.com/.../videos/... বা https://fb.watch/...)।');
        }

        // ----- ফাইলগুলো আগে যাচাই, তারপর সেভ -----
        $imgs = $this->collectFiles('images');
        $video = upload_file('video');
        // ভিডিও আর YouTube শুধু অ্যাডমিন অ্যাকাউন্ট থেকে — সাধারণ ইউজার/টিচার শুধু ছবি আর লেখা
        if (!$isAdmin && ($video || $yt !== '' || $ytRaw !== '' || $fbRaw !== '')) $this->fail('ভিডিও শুধু অ্যাডমিন পোস্ট করতে পারেন — আপনি ছবি আর লেখা দিয়ে পোস্ট করুন।');
        if (count($imgs) > self::MAX_IMAGES) $this->fail('সর্বোচ্চ ' . self::MAX_IMAGES . 'টা ছবি দেওয়া যাবে।');
        if (($imgs ? 1 : 0) + ($video ? 1 : 0) + ($yt !== '' ? 1 : 0) + ($fb !== '' ? 1 : 0) > 1) $this->fail('একটা পোস্টে ছবি, ভিডিও, YouTube অথবা Facebook লিংক — যেকোনো একটা দিন।');
        foreach ($imgs as $f) {
            $ext = strtolower('.' . pathinfo((string)$f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::IMAGE_EXTS, true)) $this->fail('শুধু JPG, PNG, GIF বা WEBP ছবি দেওয়া যাবে।');
            if ($f['size'] > self::MAX_IMAGE) $this->fail('প্রতিটা ছবি ৫ MB এর কম হতে হবে।');
            if (@getimagesize($f['tmp_name']) === false) $this->fail('"' . mb_substr((string)$f['name'], 0, 40) . '" সঠিক ছবি নয়।');
        }
        $vExt = '';
        if ($video) {
            $vExt = strtolower('.' . pathinfo((string)$video['name'], PATHINFO_EXTENSION));
            if (!in_array($vExt, self::VIDEO_EXTS, true)) $this->fail('শুধু MP4, WEBM বা MOV ভিডিও দেওয়া যাবে।');
            if ($video['size'] > self::videoLimit()) $this->fail('ভিডিও ' . round(self::videoLimit() / 1048576) . ' MB এর কম হতে হবে। বড় ভিডিও YouTube-এ দিয়ে লিংক শেয়ার করুন।');
            $head = (string)@file_get_contents($video['tmp_name'], false, null, 0, 12);
            $isMp4 = substr($head, 4, 4) === 'ftyp';
            $isWebm = substr($head, 0, 4) === "\x1A\x45\xDF\xA3";
            if (!($isMp4 || $isWebm)) $this->fail('ফাইলটি সঠিক ভিডিও নয়।');
            if ($vExt === '.m4v' || $vExt === '.mov') $vExt = $isWebm ? '.webm' : '.mp4';
            if ($isWebm) $vExt = '.webm';
        }
        if ($content === '' && !$imgs && !$video && $yt === '' && $fb === '') $this->fail('কিছু লিখুন অথবা ছবি/ভিডিও দিন।');

        $media = [];
        $saved = [];
        try {
            foreach ($imgs as $f) {
                $ext = strtolower('.' . pathinfo((string)$f['name'], PATHINFO_EXTENSION));
                $saved[] = $media['images'][] = save_upload($f, 'edu-feed', $me . '_' . ticks() . '_' . bin2hex(random_bytes(3)), $ext);
            }
            if ($video) $saved[] = $media['video'] = save_upload($video, 'edu-feed', 'v' . $me . '_' . ticks() . '_' . bin2hex(random_bytes(3)), $vExt);
        } catch (Throwable $e) {
            foreach ($saved as $u) delete_upload($u);
            error_log('[Feed upload] ' . $e->getMessage());
            $this->fail('ফাইল সেভ করা যায়নি। আবার চেষ্টা করুন।');
        }
        if ($yt !== '') $media['youtube'] = $yt;
        if ($fb !== '') $media['facebook'] = $fb;

        $status = $isAdmin || $isInstructor ? 'Approved' : 'Pending';
        $id = $this->db->CreateEduPost($me, $content, $subject, $media, $status);
        if ($status === 'Pending') {
            $name = session_get('UserFullName') ?? session_get('UserName') ?? 'একজন ইউজার';
            foreach ($this->db->GetAdminUserIds() as $aid) {
                $this->db->CreateNotification($aid, 'Educational Feed — নতুন পোস্ট', "$name একটি পোস্ট দিয়েছেন — রিভিউ করে Approve করুন।", 'info', '/Admin/EduFeed', 'fa-newspaper');
            }
        }
        $p = $this->db->GetEduPost($id, $me);
        json_response(['success' => true, 'status' => $status, 'id' => $id,
            'message' => $status === 'Approved' ? 'পোস্ট ফিডে প্রকাশ হয়েছে।' : 'পোস্ট পাঠানো হয়েছে! অ্যাডমিন রিভিউ করে Approve করলে ফিডে দেখাবে।',
            'html' => $this->render('Feed/_Post', ['p' => $p, 'me' => $me])]);
    }

    /** $_FILES['images'] (একাধিক) → ফাইলের তালিকা */
    private function collectFiles(string $field): array
    {
        $f = $_FILES[$field] ?? null;
        if (!$f) return [];
        $out = [];
        if (is_array($f['name'])) {
            foreach ($f['name'] as $i => $n) {
                if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
                if ($f['error'][$i] !== UPLOAD_ERR_OK) $this->fail('ছবি আপলোড হয়নি (ফাইল খুব বড় হতে পারে)।');
                $out[] = ['name' => $n, 'tmp_name' => $f['tmp_name'][$i], 'size' => (int)$f['size'][$i], 'error' => 0, 'type' => $f['type'][$i] ?? ''];
            }
        } elseif (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ($f['error'] !== UPLOAD_ERR_OK) $this->fail('ছবি আপলোড হয়নি (ফাইল খুব বড় হতে পারে)।');
            $out[] = $f;
        }
        return $out;
    }

    /** Facebook ভিডিও / Reel / fb.watch লিংক → পরিষ্কার https লিংক ('' = ভুল লিংক) */
    public static function facebookVideoUrl(string $url): string
    {
        $url = trim($url);
        if (!preg_match('~^https?://(?:(?:www|m|web|mbasic)\.)?(facebook\.com|fb\.watch|fb\.com)(/[^\s"\'<>]*)$~i', $url, $m)) return '';
        $host = strtolower($m[1]); $path = $m[2];
        if ($host === 'fb.watch') return preg_match('~^/[A-Za-z0-9_-]{4,}/?$~', $path) ? 'https://fb.watch' . rtrim($path, '/') . '/' : '';
        $q = parse_url($path, PHP_URL_QUERY) ?: ''; $p = (string)parse_url($path, PHP_URL_PATH);
        // watch?v=123 / video.php?v=123
        if (preg_match('~^/(?:watch/?|video\.php)$~i', $p) && preg_match('~(?:^|&)v=(\d{5,})~', $q, $v)) return 'https://www.facebook.com/watch/?v=' . $v[1];
        // /{page}/videos/{...}/123 · /reel/123 · /share/v/xxx · /share/r/xxx
        if (preg_match('~^/(?:[A-Za-z0-9.\-_]+/videos/(?:[^/]+/)?\d{5,}|reel/\d{5,}|share/[vr]/[A-Za-z0-9_-]{4,})/?$~i', $p)) return 'https://www.facebook.com' . rtrim($p, '/') . '/';
        return '';
    }

    /** YouTube লিংক → ১১ অক্ষরের ভিডিও id ('' = ভুল লিংক) */
    public static function youtubeId(string $url): string
    {
        $url = trim($url);
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $url)) return $url;
        if (!preg_match('~^https?://(?:www\.|m\.|music\.)?(?:youtube\.com|youtu\.be|youtube-nocookie\.com)/~i', $url)) return '';
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~[?&]v=([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
        if (preg_match('~/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})~', $url, $m)) return $m[1];
        return '';
    }

    /** POST /Feed/Delete — postId (লেখক বা অ্যাডমিন) */
    public function Delete_POST(): void
    {
        $me = $this->me();
        $p = $this->db->GetEduPost((int)input_int('postId', 0));
        if (!$p) $this->fail('পোস্টটি আগেই মুছে গেছে।');
        if ($p->UserId !== $me && !is_admin()) $this->fail('এই পোস্ট মোছার অনুমতি নেই।');
        $this->db->DeleteEduPost($p->Id);
        foreach ($p->Images as $u) delete_upload($u);
        if ($p->Video !== '') delete_upload($p->Video);
        if ($p->UserId !== $me) {
            $this->db->CreateNotification($p->UserId, 'Educational Feed', 'আপনার একটি Educational Feed পোস্ট অ্যাডমিন সরিয়ে দিয়েছেন।', 'warning', '/Feed?tab=mine', 'fa-trash');
        }
        json_response(['success' => true, 'message' => 'পোস্ট মুছে ফেলা হয়েছে।']);
    }

    /** POST /Feed/Like — postId */
    public function Like_POST(): void
    {
        $me = $this->me();
        $p = $this->db->GetEduPost((int)input_int('postId', 0));
        if (!$p || $p->Status !== 'Approved') $this->fail('পোস্টটি পাওয়া যায়নি।');
        [$liked, $count] = $this->db->ToggleEduLike($p->Id, $me);
        json_response(['success' => true, 'liked' => $liked, 'count' => $count]);
    }

    /** POST /Feed/Comment — postId, content */
    public function Comment_POST(): void
    {
        if (!feed_comments_on()) json_response(['success' => false, 'message' => 'এখানে কমেন্ট বন্ধ — শুধু লাইক দেওয়া যায়।']);
        $me = $this->me();
        $p = $this->db->GetEduPost((int)input_int('postId', 0));
        if (!$p || $p->Status !== 'Approved') $this->fail('পোস্টটি পাওয়া যায়নি।');
        $content = trim(preg_replace("/\n{3,}/", "\n\n", str_replace(["\r\n", "\r"], "\n", (string)input_str('content', ''))) ?? '');
        if ($content === '') $this->fail('কমেন্ট লিখুন।');
        if (mb_strlen($content) > self::MAX_COMMENT_LEN) $this->fail('কমেন্ট সর্বোচ্চ ' . self::MAX_COMMENT_LEN . ' অক্ষর।');
        if (!is_admin() && $this->db->CountRecentEdu('comment', $me, 10) >= 30) $this->fail('খুব দ্রুত অনেক কমেন্ট করছেন। একটু পর চেষ্টা করুন।');
        $id = $this->db->AddEduComment($p->Id, $me, $content);
        if ($p->UserId !== $me) {
            $name = session_get('UserFullName') ?? session_get('UserName') ?? 'একজন ইউজার';
            $this->db->CreateNotification($p->UserId, 'New Comment 💬', "$name আপনার Educational Feed পোস্টে কমেন্ট করেছেন: " . str_preview($content, 60), 'info', $p->Url, 'fa-comment');
        }
        $c = $this->db->GetEduComment($id);
        json_response(['success' => true, 'count' => count($this->db->GetEduComments($p->Id)), 'html' => $this->render('Feed/_Comment', ['c' => $c, 'p' => $p, 'me' => $me])]);
    }

    /** POST /Feed/DeleteComment — commentId (কমেন্টকারী, পোস্টের লেখক বা অ্যাডমিন) */
    public function DeleteComment_POST(): void
    {
        $me = $this->me();
        $c = $this->db->GetEduComment((int)input_int('commentId', 0));
        if (!$c) $this->fail('কমেন্টটি আগেই মুছে গেছে।');
        $p = $this->db->GetEduPost($c->PostId);
        if ($c->UserId !== $me && (!$p || $p->UserId !== $me) && !is_admin()) $this->fail('এই কমেন্ট মোছার অনুমতি নেই।');
        $this->db->DeleteEduComment($c->Id);
        json_response(['success' => true, 'count' => $p ? count($this->db->GetEduComments($p->Id)) : 0]);
    }
}
