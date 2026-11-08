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
    private function pack(array $msgs, int $me, ?Obj $t = null): array
    {
        // গ্রুপে কে পাঠিয়েছে তার নাম দেখাতে হয়
        $names = [];
        if ($t && $t->Kind === 'grp') foreach ($this->db->GetChatGroupMembers($t->RefId) as $u) $names[$u->Id] = $u->Name . ($u->IsTeacher ? ' (Teacher)' : '');
        return array_map(fn($m) => [
            'id' => $m->Id, 'mine' => $m->SenderId === $me, 'body' => (string)$m->Body, 'from' => $names ? ($names[$m->SenderId] ?? 'সাবেক সদস্য') : null,
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

    /** এই চ্যাটের অন্য সদস্যরা (নোটিফিকেশন পাঠাতে) */
    private function recipients(Obj $t, int $me): array
    {
        if ($t->Kind !== 'grp') return [$t->OtherId];
        return array_values(array_filter(array_map(fn($u) => $u->Id, $this->db->GetChatGroupMembers($t->RefId)), fn($id) => $id !== $me));
    }

    private function packThreads(array $threads): array
    {
        return array_map(fn($t) => [
            'id' => $t->Key, 'name' => $t->OtherName, 'photo' => $t->OtherPhoto, 'initials' => $t->Initials, 'role' => $t->OtherRole, 'group' => $t->Kind === 'grp',
            'topic' => $t->Topic, 'last' => $t->LastBody !== '' ? str_preview($t->LastBody, 70) : '', 'lastMine' => $t->LastMine, 'ago' => time_ago($t->LastAt), 'unread' => $t->Unread,
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
            'Current' => $current, 'Closed' => $closed, 'Messages' => $this->pack($msgs, $me, $current), 'HasOlder' => count($msgs) === 50,
            'SeenUpTo' => $current ? $this->db->GetChatSeenUpTo($current, $me) : 0, 'Me' => $me,
            'ThreadJson' => $this->packThreads($threads),
            'Classes' => $current ? $this->packClasses($current) : [], 'IsTeacher' => $current ? $current->TeacherId === $me : false, 'LiveOk' => teacher_live_enabled($me) && bbb_ready(),
            'Contacts' => current_role() === 'Teacher' ? array_values($this->db->TeacherChatContacts($me)) : [],
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
        json_response(['success' => true, 'messages' => $this->pack($msgs, $me, $t), 'hasOlder' => $before > 0 && count($msgs) === 50,
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

    /**
     * POST /Chat/Start (teacherId) — টিচারের প্রোফাইল থেকে "চ্যাট আনলক": প্যাকেজ নেওয়া শিক্ষার্থী সরাসরি চ্যাট শুরু করে
     */
    public function Start_POST(): void
    {
        $me = $this->me();
        $tid = input_int('teacherId', 0);
        $back = '/Tutors/Profile/' . $tid;
        if (current_role() !== 'User') { tempdata_set('Error', 'শুধু শিক্ষার্থীরা টিচারের সাথে চ্যাট শুরু করতে পারেন।'); redirect($back); }
        if (!viewer_can_contact_teachers()) {
            tempdata_set('Error', 'টিচারের সাথে চ্যাট আনলক করতে শিক্ষার্থী প্যাকেজ নিন (১ মাস ৳50, ৩ মাস ৳100)।');
            redirect('/Package?for=student');
        }
        $t = $tid > 0 ? $this->db->GetUserById($tid) : null;
        if (!$t || $t->Role !== 'Teacher' || !(int)$t->IsActive || $this->db->GetActiveSuspension($tid)) { tempdata_set('Error', 'এই টিচারের সাথে এখন চ্যাট করা যাচ্ছে না।'); redirect($back); }
        redirect('/Chat?c=' . $this->db->OpenDirectChat($me, $tid));
    }

    /** POST /Chat/Send  (c, body) */
    public function Send_POST(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি বন্ধ হয়ে গেছে — আর মেসেজ পাঠানো যাবে না।']);
        // সরাসরি চ্যাট: শিক্ষার্থীর প্যাকেজের মেয়াদ শেষ হলে শিক্ষার্থী আর মেসেজ পাঠাতে পারবে না (টিচার উত্তর দিতে পারবে)
        if ($t->Kind === 'dir' && $t->StudentId === $me && !viewer_can_contact_teachers()) {
            json_response(['success' => false, 'needPackage' => '/Package?for=student', 'message' => 'আপনার প্যাকেজের মেয়াদ শেষ — টিচারকে মেসেজ পাঠাতে আবার প্যাকেজ নিন।']);
        }
        $body = trim(str_replace(["\r\n", "\r"], "\n", (string)input_str('body', '')));
        $body = preg_replace("/\n{4,}/", "\n\n\n", $body);
        if ($body === '') json_response(['success' => false, 'message' => 'কিছু লিখুন।']);
        if (mb_strlen($body) > self::MAX_LEN) json_response(['success' => false, 'message' => 'মেসেজ সর্বোচ্চ ' . self::MAX_LEN . ' অক্ষর হতে পারে।']);
        if ($this->db->CountRecentChatMessages($me, 60) >= 30) json_response(['success' => false, 'message' => 'খুব দ্রুত মেসেজ পাঠাচ্ছেন — একটু পর আবার চেষ্টা করুন।']);

        $id = $this->db->AddChatMessage($t, $me, $body);
        // অন্যজনকে নোটিফিকেশন — শুধু প্রথম না-পড়া মেসেজে (প্রতিটা মেসেজে নয়)
        $myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'User');
        if ($t->Kind === 'grp') {
            // গ্রুপ: যার কাছে এটাই প্রথম না-পড়া মেসেজ, শুধু তাকে নোটিফিকেশন
            foreach ($this->db->GroupUnreadFor($t->RefId, $me) as $uid => $n) {
                if ($n === 1) $this->db->CreateNotification($uid, 'নতুন গ্রুপ মেসেজ', "$myName — {$t->OtherName}: " . str_preview($body, 60), 'info', '/Chat?c=' . $t->Key, 'fa-users');
            }
        } elseif (!$this->db->HasUnreadFrom($t, $me)) {
            $this->db->CreateNotification($t->OtherId, 'নতুন মেসেজ', "$myName আপনাকে মেসেজ পাঠিয়েছেন: " . str_preview($body, 60), 'info', '/Chat?c=' . $t->Key, 'fa-comment-dots');
        }
        $msgs = $this->db->GetChatMessages($t, max(0, input_int('after', 0)), 0, 50);
        json_response(['success' => true, 'id' => $id, 'messages' => $this->pack($msgs, $me, $t)]);
    }

    /** POST /Chat/CreateClass (c, title, link, date, time, duration) — শুধু এই চ্যাটের টিচার */
    public function CreateClass_POST(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি বন্ধ হয়ে গেছে।']);
        if ($t->TeacherId !== $me) json_response(['success' => false, 'message' => 'শুধু টিচার ক্লাস সেট করতে পারবেন।']);
        // Zoom / Google Meet আর না — সাইটের নিজের লাইভ ক্লাস (BigBlueButton), শুধু Premium টিচার প্যাকেজে
        if (!teacher_live_enabled($me)) json_response(['success' => false, 'premium' => true, 'message' => 'লাইভ ক্লাস নিতে "Premium" টিচার প্যাকেজ লাগবে (১ মাস ৳200 / ৩ মাস ৳500)।']);
        if (!bbb_ready()) json_response(['success' => false, 'message' => 'লাইভ ক্লাসের সার্ভার এখনো চালু হয়নি — অ্যাডমিনকে জানান।']);
        $title = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str('title', ''))), 0, 150);
        $platform = 'মাষ্টার মশাই লাইভ';
        $link = '#';
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
        $link = '/Live/Chat/' . $cid;
        $this->db->exec('UPDATE `ChatClasses` SET `Link` = :l WHERE `Id` = :i', ['l' => $link, 'i' => $cid]);
        $when = $dt->format('d M Y, h:i A');
        $this->db->AddChatMessage($t, $me, "📅 লাইভ ক্লাস: $title — $when ($platform)\n" . site_public_url() . $link, $cid);
        $myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'Teacher');
        foreach ($this->recipients($t, $me) as $uid) $this->db->CreateNotification($uid, 'নতুন অনলাইন ক্লাস 📅', "$myName $when এ \"$title\" ক্লাস সেট করেছেন ($platform)" . ($t->Kind === 'grp' ? " — গ্রুপ: {$t->OtherName}" : '') . '।', 'success', '/Chat?c=' . $t->Key, 'fa-video');
        $msgs = $this->db->GetChatMessages($t, max(0, input_int('after', 0)), 0, 50);
        json_response(['success' => true, 'messages' => $this->pack($msgs, $me, $t), 'classes' => $this->packClasses($t)]);
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
            foreach ($this->recipients($t, $me) as $uid) $this->db->CreateNotification($uid, 'ক্লাস বাতিল', "$when এর \"{$c->Title}\" ক্লাসটি বাতিল করা হয়েছে।", 'warning', '/Chat?c=' . $t->Key, 'fa-calendar-xmark');
        }
        $msgs = $this->db->GetChatMessages($t, max(0, input_int('after', 0)), 0, 50);
        json_response(['success' => true, 'messages' => $this->pack($msgs, $me, $t), 'classes' => $this->packClasses($t)]);
    }

    // ============================================================
    // ===== গ্রুপ চ্যাট — টিচার নিজের স্টুডেন্টদের নিয়ে গ্রুপ বানায় =====
    // ============================================================
    private function cleanGroupName(): string
    {
        $n = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)input_str('name', '')))), 0, 80);
        if ($n === '') json_response(['success' => false, 'message' => 'গ্রুপের নাম দিন (যেমন: HSC Physics ব্যাচ — সন্ধ্যা ৬টা)।']);
        return $n;
    }

    /** শুধু টিচারের নিজের স্টুডেন্ট (যাদের সাথে চ্যাট আছে) */
    private function pickStudents(int $me): array
    {
        $contacts = $this->db->TeacherChatContacts($me);
        return array_values(array_filter(array_map('intval', (array)($_POST['members'] ?? [])), fn($id) => isset($contacts[$id])));
    }

    /** গ্রুপটা এই টিচারের কিনা */
    private function ownGroup(int $me): Obj
    {
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t || $t->Kind !== 'grp') json_response(['success' => false, 'message' => 'গ্রুপটি পাওয়া যায়নি।']);
        if ($t->TeacherId !== $me) json_response(['success' => false, 'message' => 'শুধু গ্রুপের টিচার এটা করতে পারবেন।']);
        return $t;
    }

    /** POST /Chat/GroupCreate (name, members[]) */
    public function GroupCreate_POST(): void
    {
        $me = $this->me(true);
        if (current_role() !== 'Teacher') json_response(['success' => false, 'message' => 'শুধু টিচাররা গ্রুপ বানাতে পারবেন।']);
        $name = $this->cleanGroupName();
        $ids = $this->pickStudents($me);
        if (!$ids) json_response(['success' => false, 'message' => 'অন্তত একজন স্টুডেন্ট বাছাই করুন।']);
        if ((int)$this->db->scalar('SELECT COUNT(*) FROM `ChatGroups` WHERE `TeacherId` = :t', ['t' => $me]) >= 50) json_response(['success' => false, 'message' => 'সর্বোচ্চ ৫০টি গ্রুপ বানানো যায়।']);
        $gid = $this->db->CreateChatGroup($me, $name, $ids);
        $t = $this->db->GetChatThread('g' . $gid, $me);
        $myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'Teacher');
        $wid = $this->db->AddChatMessage($t, $me, "👋 $myName গ্রুপটি তৈরি করেছেন। এখানে ক্লাসের মেসেজ আর অনলাইন ক্লাসের লিংক দেওয়া হবে।");
        // স্বাগত মেসেজটা "পড়া" ধরা — স্টুডেন্টরা আলাদা "গ্রুপে যোগ" নোটিফিকেশন পায়; পরের মেসেজে আবার নোটিফিকেশন যাবে
        $this->db->exec('UPDATE `ChatGroupMembers` SET `LastReadId` = :l WHERE `GroupId` = :g', ['l' => $wid, 'g' => $gid]);
        foreach ($ids as $uid) $this->db->CreateNotification($uid, 'নতুন গ্রুপে যোগ করা হয়েছে', "$myName আপনাকে \"$name\" গ্রুপে যোগ করেছেন।", 'info', '/Chat?c=g' . $gid, 'fa-users');
        json_response(['success' => true, 'redirect' => '/Chat?c=g' . $gid]);
    }

    /** GET /Chat/GroupInfo?c=g12 — সদস্য তালিকা (+ টিচারের জন্য যোগ করা যায় এমন স্টুডেন্ট) */
    public function GroupInfo(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(trim((string)input_str('c', '')), $me);
        if (!$t || $t->Kind !== 'grp') json_response(['success' => false, 'message' => 'গ্রুপটি পাওয়া যায়নি।']);
        $members = $this->db->GetChatGroupMembers($t->RefId);
        $in = array_flip(array_map(fn($u) => $u->Id, $members));
        $canAdd = $t->TeacherId === $me ? array_values(array_filter($this->db->TeacherChatContacts($me), fn($c) => !isset($in[$c->Id]))) : [];
        $map = fn($u) => ['id' => $u->Id, 'name' => $u->Name, 'photo' => $u->Photo, 'initials' => $u->Initials, 'teacher' => !empty($u->IsTeacher)];
        json_response(['success' => true, 'name' => $t->OtherName, 'owner' => $t->TeacherId === $me, 'members' => array_map($map, $members), 'canAdd' => array_map($map, $canAdd)]);
    }

    /** POST /Chat/GroupAdd (c, members[]) */
    public function GroupAdd_POST(): void
    {
        $me = $this->me(true);
        $t = $this->ownGroup($me);
        $ids = $this->pickStudents($me);
        if (!$ids) json_response(['success' => false, 'message' => 'যোগ করার জন্য স্টুডেন্ট বাছাই করুন।']);
        $n = $this->db->AddChatGroupMembers($t->RefId, $ids);
        $myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'Teacher');
        foreach ($ids as $uid) $this->db->CreateNotification($uid, 'নতুন গ্রুপে যোগ করা হয়েছে', "$myName আপনাকে \"{$t->OtherName}\" গ্রুপে যোগ করেছেন।", 'info', '/Chat?c=' . $t->Key, 'fa-users');
        json_response(['success' => true, 'message' => "$n জনকে যোগ করা হয়েছে।"]);
    }

    /** POST /Chat/GroupRemove (c, userId) */
    public function GroupRemove_POST(): void
    {
        $me = $this->me(true);
        $t = $this->ownGroup($me);
        $uid = input_int('userId', 0);
        if ($uid === $me) json_response(['success' => false, 'message' => 'নিজেকে সরানো যাবে না — গ্রুপ মুছে দিতে পারেন।']);
        $this->db->RemoveChatGroupMember($t->RefId, $uid);
        json_response(['success' => true, 'message' => 'সদস্য সরানো হয়েছে।']);
    }

    /** POST /Chat/GroupRename (c, name) */
    public function GroupRename_POST(): void
    {
        $me = $this->me(true);
        $t = $this->ownGroup($me);
        $this->db->RenameChatGroup($t->RefId, $this->cleanGroupName());
        json_response(['success' => true, 'message' => 'নাম বদলানো হয়েছে।']);
    }

    /** POST /Chat/GroupDelete (c) */
    public function GroupDelete_POST(): void
    {
        $me = $this->me(true);
        $t = $this->ownGroup($me);
        $this->db->DeleteChatGroup($t->RefId);
        json_response(['success' => true, 'redirect' => '/Chat']);
    }
}
