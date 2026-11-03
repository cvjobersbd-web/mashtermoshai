<?php
/**
 * স্টুডেন্ট ↔ টিচার চ্যাট (মেসেঞ্জারের মতো)।
 * স্টুডেন্ট টিচারের অ্যাপ্লিকেশন Accept করলে চ্যাট খোলে; Reject / Undo করলে চ্যাট বন্ধ হয়ে যায়।
 *
 *  GET  /Chat?c={key}                   চ্যাট পেজ (key: "12" = অ্যাপ্লিকেশন, "r5" = টিচার রিকোয়েস্ট) (বাঁয়ে তালিকা, ডানে কথোপকথন)
 *  GET  /Chat/Messages?c=&after=&before= মেসেজ (JSON) — নতুন মেসেজ দেখতে কয়েক সেকেন্ড পরপর ডাকা হয়
 *  GET  /Chat/Threads                    চ্যাট তালিকা (JSON)
 *  GET  /Chat/Unread                     কতগুলো চ্যাটে না-পড়া মেসেজ (নেভবারের ব্যাজ)
 *  POST /Chat/Send                       মেসেজ পাঠানো
 */
class ChatController extends Controller
{
    private const MAX_LEN = 2000;

    private function me(bool $json = false): int
    {
        $me = current_user_id();
        if ($me === null) {
            if ($json) json_response(['success' => false, 'message' => 'আগে লগইন করুন।', 'login' => true]);
            redirect('/Account/Login?returnUrl=' . rawurlencode(strtok($_SERVER['REQUEST_URI'] ?? '/Chat', '#')));
        }
        return (int)$me;
    }

    /** মেসেজ JSON আকারে (লেখা ব্রাউজারে textContent দিয়ে বসে — তাই HTML হিসেবে চলে না) */
    private function pack(array $msgs, int $me): array
    {
        return array_map(fn($m) => [
            'id' => $m->Id, 'mine' => $m->SenderId === $me, 'body' => (string)$m->Body,
            'at' => date('c', strtotime((string)$m->CreatedAt)), 'time' => date('h:i A', strtotime((string)$m->CreatedAt)),
            'day' => date('Y-m-d', strtotime((string)$m->CreatedAt)), 'cls' => (int)($m->ClassId ?? 0),
        ], $msgs);
    }

    /** ক্লাসগুলো JSON আকারে — কার্ড আর "পরের ক্লাস" বার এর জন্য */
    private function packClasses(Obj $t): array
    {
        return array_map(function ($c) {
            $st = strtotime((string)$c->StartsAt);
            return ['id' => $c->Id, 'title' => (string)$c->Title, 'link' => (string)$c->Link, 'platform' => (string)$c->Platform,
                'start' => date('c', $st), 'end' => date('c', $st + $c->DurationMin * 60), 'dur' => $c->DurationMin, 'status' => (string)$c->Status,
                'when' => date('d M Y, h:i A', $st), 'day' => date('Y-m-d', $st), 'time' => date('h:i A', $st)];
        }, $this->db->GetChatClasses($t));
    }

    /** শুধু Zoom / Google Meet / Microsoft Teams এর https লিংক — প্ল্যাটফর্মের নাম ফেরত দেয়, না মিললে null */
    public static function meetingPlatform(string $url): ?string
    {
        if (!preg_match('~^https://~i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) return null;
        $h = strtolower((string)parse_url($url, PHP_URL_HOST));
        if ($h === 'zoom.us' || str_ends_with($h, '.zoom.us')) return 'Zoom';
        if ($h === 'meet.google.com') return 'Google Meet';
        if ($h === 'teams.microsoft.com' || $h === 'teams.live.com') return 'Microsoft Teams';
        return null;
    }

    private function packThreads(array $threads): array
    {
        return array_map(fn($t) => [
            'id' => $t->Key, 'name' => $t->OtherName, 'photo' => $t->OtherPhoto, 'initials' => $t->Initials, 'role' => $t->OtherRole,
            'topic' => $t->Topic, 'last' => str_preview($t->LastBody, 70), 'lastMine' => $t->LastMine, 'ago' => time_ago($t->LastAt), 'unread' => $t->Unread,
        ], $threads);
    }

    /** GET /Chat */
    public function Index(): void
    {
        $me = $this->me();
        if (current_role() === 'Admin') redirect('/Admin/Dashboard');
        $threads = $this->db->GetChatThreads($me);
        $cid = trim((string)input_str('c', ''));
        $current = $cid !== '' ? $this->db->GetChatThread($cid, $me) : null;
        $closed = $cid !== '' && !$current; // লিংক আছে কিন্তু চ্যাট বন্ধ / অনুমতি নেই
        $msgs = [];
        if ($current) {
            $msgs = $this->db->GetChatMessages($current, 0, 0, 50);
            $this->db->MarkChatRead($current, $me);
            foreach ($threads as $t) if ($t->Key === $current->Key) $t->Unread = 0;
        }
        $this->view(null, $threads, [
            'Current' => $current, 'Closed' => $closed, 'Messages' => $this->pack($msgs, $me), 'HasOlder' => count($msgs) === 50,
            'SeenUpTo' => $current ? $this->db->GetChatSeenUpTo($current, $me) : 0, 'Me' => $me,
            'ThreadJson' => $this->packThreads($threads),
            'Classes' => $current ? $this->packClasses($current) : [], 'IsTeacher' => $current ? $current->TeacherId === $me : false,
        ]);
    }

    /** GET /Chat/Messages?c=&after=&before=&read=1 */
    public function Messages(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি এখন বন্ধ।']);
        $before = input_int('before', 0);
        $msgs = $this->db->GetChatMessages($t, $before > 0 ? 0 : input_int('after', 0), $before, 50);
        if (input_int('read', 0) === 1) $this->db->MarkChatRead($t, $me);
        json_response(['success' => true, 'messages' => $this->pack($msgs, $me), 'hasOlder' => $before > 0 && count($msgs) === 50,
            'seenUpTo' => $this->db->GetChatSeenUpTo($t, $me), 'classes' => $this->packClasses($t)]);
    }

    /** GET /Chat/Threads */
    public function Threads(): void
    {
        $me = $this->me(true);
        json_response(['success' => true, 'threads' => $this->packThreads($this->db->GetChatThreads($me))]);
    }

    /** GET /Chat/Unread */
    public function Unread(): void
    {
        $me = current_user_id();
        json_response(['success' => true, 'count' => $me === null || current_role() === 'Admin' ? 0 : $this->db->CountUnreadChats((int)$me)]);
    }

    /** POST /Chat/Send  (c, body) */
    public function Send_POST(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি বন্ধ হয়ে গেছে — আর মেসেজ পাঠানো যাবে না।']);
        $body = trim(str_replace(["\r\n", "\r"], "\n", (string)input_str('body', '')));
        $body = preg_replace("/\n{4,}/", "\n\n\n", $body);
        if ($body === '') json_response(['success' => false, 'message' => 'কিছু লিখুন।']);
        if (mb_strlen($body) > self::MAX_LEN) json_response(['success' => false, 'message' => 'মেসেজ সর্বোচ্চ ' . self::MAX_LEN . ' অক্ষর হতে পারে।']);
        if ($this->db->CountRecentChatMessages($me, 60) >= 30) json_response(['success' => false, 'message' => 'খুব দ্রুত মেসেজ পাঠাচ্ছেন — একটু পর আবার চেষ্টা করুন।']);

        $id = $this->db->AddChatMessage($t, $me, $body);
        // অন্যজনকে নোটিফিকেশন — শুধু প্রথম না-পড়া মেসেজে (প্রতিটা মেসেজে নয়)
        if (!$this->db->HasUnreadFrom($t, $me)) {
            $myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'User');
            $this->db->CreateNotification($t->OtherId, 'নতুন মেসেজ', "$myName আপনাকে মেসেজ পাঠিয়েছেন: " . str_preview($body, 60), 'info', '/Chat?c=' . $t->Key, 'fa-comment-dots');
        }
        $msgs = $this->db->GetChatMessages($t, max(0, input_int('after', 0)), 0, 50);
        json_response(['success' => true, 'id' => $id, 'messages' => $this->pack($msgs, $me)]);
    }

    /** POST /Chat/CreateClass (c, title, link, date, time, duration) — শুধু এই চ্যাটের টিচার */
    public function CreateClass_POST(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি বন্ধ হয়ে গেছে।']);
        if ($t->TeacherId !== $me) json_response(['success' => false, 'message' => 'শুধু টিচার ক্লাস সেট করতে পারবেন।']);
        $title = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str('title', ''))), 0, 150);
        $link = trim((string)input_str('link', ''));
        $platform = self::meetingPlatform($link);
        if ($platform === null || mb_strlen($link) > 500) json_response(['success' => false, 'message' => 'সঠিক Zoom, Google Meet বা Microsoft Teams লিংক দিন (https:// দিয়ে শুরু)।']);
        $date = (string)input_str('date', ''); $time = (string)input_str('time', '');
        $dt = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$dt || $dt->format('Y-m-d H:i') !== $date . ' ' . $time) json_response(['success' => false, 'message' => 'ক্লাসের তারিখ আর সময় দিন।']);
        if ($dt->getTimestamp() < time() - 600) json_response(['success' => false, 'message' => 'অতীতের সময়ে ক্লাস সেট করা যাবে না।']);
        if ($dt->getTimestamp() > time() + 180 * 86400) json_response(['success' => false, 'message' => '৬ মাসের বেশি পরের ক্লাস সেট করা যাবে না।']);
        $dur = input_int('duration', 60);
        if (!in_array($dur, [30, 45, 60, 90, 120], true)) $dur = 60;
        if ($title === '') $title = $t->Topic !== '' ? $t->Topic . ' ক্লাস' : 'অনলাইন ক্লাস';
        if ($this->db->CountRecentChatMessages($me, 60) >= 30) json_response(['success' => false, 'message' => 'একটু পর আবার চেষ্টা করুন।']);

        $cid = $this->db->CreateChatClass($t, $me, $title, $link, $platform, $dt->format('Y-m-d H:i:s'), $dur);
        $when = $dt->format('d M Y, h:i A');
        $this->db->AddChatMessage($t, $me, "📅 অনলাইন ক্লাস: $title — $when ($platform)\n$link", $cid);
        $myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'Teacher');
        $this->db->CreateNotification($t->OtherId, 'নতুন অনলাইন ক্লাস 📅', "$myName $when এ \"$title\" ক্লাস সেট করেছেন ($platform)।", 'success', '/Chat?c=' . $t->Key, 'fa-video');
        $msgs = $this->db->GetChatMessages($t, max(0, input_int('after', 0)), 0, 50);
        json_response(['success' => true, 'messages' => $this->pack($msgs, $me), 'classes' => $this->packClasses($t)]);
    }

    /** POST /Chat/CancelClass (c, id) — টিচার ক্লাস বাতিল করে */
    public function CancelClass_POST(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি বন্ধ হয়ে গেছে।']);
        $c = $this->db->GetChatClass(input_int('id', 0));
        if (!$c || $t->TeacherId !== $me || $c->TeacherId !== $me || $c->Kind !== $t->Kind || $c->AppId !== $t->RefId) json_response(['success' => false, 'message' => 'ক্লাসটি পাওয়া যায়নি।']);
        if ($c->Status !== 'Cancelled') {
            $this->db->CancelChatClass($c->Id);
            $when = date('d M Y, h:i A', strtotime((string)$c->StartsAt));
            $this->db->AddChatMessage($t, $me, "❌ ক্লাস বাতিল: {$c->Title} — $when");
            $this->db->CreateNotification($t->OtherId, 'ক্লাস বাতিল', "$when এর \"{$c->Title}\" ক্লাসটি বাতিল করা হয়েছে।", 'warning', '/Chat?c=' . $t->Key, 'fa-calendar-xmark');
        }
        $msgs = $this->db->GetChatMessages($t, max(0, input_int('after', 0)), 0, 50);
        json_response(['success' => true, 'messages' => $this->pack($msgs, $me), 'classes' => $this->packClasses($t)]);
    }
}
