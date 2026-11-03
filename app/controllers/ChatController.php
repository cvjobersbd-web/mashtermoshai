<?php
/**
 * স্টুডেন্ট ↔ টিচার চ্যাট (মেসেঞ্জারের মতো)।
 * স্টুডেন্ট টিচারের অ্যাপ্লিকেশন Accept করলে চ্যাট খোলে; Reject / Undo করলে চ্যাট বন্ধ হয়ে যায়।
 *
 *  GET  /Chat?c={appId}                 চ্যাট পেজ (বাঁয়ে তালিকা, ডানে কথোপকথন)
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
            'day' => date('Y-m-d', strtotime((string)$m->CreatedAt)),
        ], $msgs);
    }

    private function packThreads(array $threads): array
    {
        return array_map(fn($t) => [
            'id' => $t->AppId, 'name' => $t->OtherName, 'photo' => $t->OtherPhoto, 'initials' => $t->Initials, 'role' => $t->OtherRole,
            'topic' => $t->Topic, 'last' => str_preview($t->LastBody, 70), 'lastMine' => $t->LastMine, 'ago' => time_ago($t->LastAt), 'unread' => $t->Unread,
        ], $threads);
    }

    /** GET /Chat */
    public function Index(): void
    {
        $me = $this->me();
        if (current_role() === 'Admin') redirect('/Admin/Dashboard');
        $threads = $this->db->GetChatThreads($me);
        $cid = input_int('c', 0);
        $current = $cid > 0 ? $this->db->GetChatThread($cid, $me) : null;
        $closed = $cid > 0 && !$current; // লিংক আছে কিন্তু চ্যাট বন্ধ / অনুমতি নেই
        $msgs = [];
        if ($current) {
            $msgs = $this->db->GetChatMessages($current->AppId, 0, 0, 50);
            $this->db->MarkChatRead($current->AppId, $me);
            foreach ($threads as $t) if ($t->AppId === $current->AppId) $t->Unread = 0;
        }
        $this->view(null, $threads, [
            'Current' => $current, 'Closed' => $closed, 'Messages' => $this->pack($msgs, $me), 'HasOlder' => count($msgs) === 50,
            'SeenUpTo' => $current ? $this->db->GetChatSeenUpTo($current->AppId, $me) : 0, 'Me' => $me,
            'ThreadJson' => $this->packThreads($threads),
        ]);
    }

    /** GET /Chat/Messages?c=&after=&before=&read=1 */
    public function Messages(): void
    {
        $me = $this->me(true);
        $t = $this->db->GetChatThread(input_int('c', 0), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি এখন বন্ধ।']);
        $before = input_int('before', 0);
        $msgs = $this->db->GetChatMessages($t->AppId, $before > 0 ? 0 : input_int('after', 0), $before, 50);
        if (input_int('read', 0) === 1) $this->db->MarkChatRead($t->AppId, $me);
        json_response(['success' => true, 'messages' => $this->pack($msgs, $me), 'hasOlder' => $before > 0 && count($msgs) === 50,
            'seenUpTo' => $this->db->GetChatSeenUpTo($t->AppId, $me)]);
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
        $t = $this->db->GetChatThread(input_int('c', 0), $me);
        if (!$t) json_response(['success' => false, 'closed' => true, 'message' => 'এই চ্যাটটি বন্ধ হয়ে গেছে — আর মেসেজ পাঠানো যাবে না।']);
        $body = trim(str_replace(["\r\n", "\r"], "\n", (string)input_str('body', '')));
        $body = preg_replace("/\n{4,}/", "\n\n\n", $body);
        if ($body === '') json_response(['success' => false, 'message' => 'কিছু লিখুন।']);
        if (mb_strlen($body) > self::MAX_LEN) json_response(['success' => false, 'message' => 'মেসেজ সর্বোচ্চ ' . self::MAX_LEN . ' অক্ষর হতে পারে।']);
        if ($this->db->CountRecentChatMessages($me, 60) >= 30) json_response(['success' => false, 'message' => 'খুব দ্রুত মেসেজ পাঠাচ্ছেন — একটু পর আবার চেষ্টা করুন।']);

        $id = $this->db->AddChatMessage($t->AppId, $me, $body);
        // অন্যজনকে নোটিফিকেশন — শুধু প্রথম না-পড়া মেসেজে (প্রতিটা মেসেজে নয়)
        if (!$this->db->HasUnreadFrom($t->AppId, $me)) {
            $myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? 'User');
            $this->db->CreateNotification($t->OtherId, 'নতুন মেসেজ', "$myName আপনাকে মেসেজ পাঠিয়েছেন: " . str_preview($body, 60), 'info', '/Chat?c=' . $t->AppId, 'fa-comment-dots');
        }
        $msgs = $this->db->GetChatMessages($t->AppId, max(0, input_int('after', 0)), 0, 50);
        json_response(['success' => true, 'id' => $id, 'messages' => $this->pack($msgs, $me)]);
    }
}
