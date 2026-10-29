<?php
/**
 * ফ্রেন্ড সিস্টেম — স্টুডেন্ট ↔ স্টুডেন্ট, টিচার ↔ টিচার, টিচার ↔ স্টুডেন্ট।
 *
 *  GET  /Friends                 → My Friends / Requests / Sent / Find People (ট্যাব)
 *  GET  /Friends/Person/{id}     → কারো প্রোফাইল + Add Friend বাটন
 *  GET  /Friends/Search?q=&role= → Find People এর লাইভ সার্চ (HTML কার্ড)
 *  POST /Friends/Send | Accept | Decline | Cancel | Unfriend   (userId)
 *
 * অ্যাডমিন অ্যাকাউন্ট ফ্রেন্ড সিস্টেমের বাইরে থাকে।
 * ফোন / ইমেইল / WhatsApp শুধু ফ্রেন্ড হলে দেখা যায়।
 */
class FriendsController extends Controller
{
    private const MAX_REQUESTS_PER_HOUR = 30;

    /** লগইন থাকা স্টুডেন্ট/টিচার না হলে থামিয়ে দেয় */
    private function me(bool $ajax = false): int
    {
        $id = current_user_id();
        if ($id === null) {
            if ($ajax) json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
            redirect('/Account/Login?returnUrl=' . rawurlencode(strtok($_SERVER['REQUEST_URI'] ?? '/Friends', '#')));
        }
        if (current_role() === 'Admin') {
            if ($ajax) json_response(['success' => false, 'message' => 'অ্যাডমিন অ্যাকাউন্টে ফ্রেন্ড সিস্টেম নেই।']);
        }
        return $id;
    }

    private function myName(): string
    {
        return session_get('UserFullName') ?? session_get('UserName') ?? 'একজন ইউজার';
    }

    /** POST থেকে অন্য ইউজারের Id নিয়ে যাচাই করে */
    private function otherId(int $me): int
    {
        $other = input_int('userId', 0) ?? 0;
        if ($other <= 0 || $other === $me) json_response(['success' => false, 'message' => 'ভুল ইউজার।']);
        return $other;
    }

    private function counts(int $me): array
    {
        return [
            'friends'  => count($this->db->GetFriendIds($me)),
            'requests' => $this->db->CountIncomingFriendRequests($me),
            'sent'     => count($this->db->GetSentFriendRequests($me)),
        ];
    }

    private function done(int $me, int $other, string $message): void
    {
        json_response([
            'success'  => true,
            'message'  => $message,
            'relation' => $this->db->GetFriendRelation($me, $other),
            'counts'   => $this->counts($me),
        ]);
    }

    // ============================================================
    // PAGES
    // ============================================================

    /** GET /Friends?tab=friends|requests|sent|find */
    public function Index(): void
    {
        $me = $this->me();
        if (current_role() === 'Admin') {
            $this->view('Friends/Index', obj(['IsAdmin' => true]));
        }
        $tab = input_str('tab', '') ?? '';
        $incoming = $this->db->GetIncomingFriendRequests($me);
        if (!in_array($tab, ['friends', 'requests', 'sent', 'find'], true)) {
            $tab = $incoming ? 'requests' : 'friends';
        }
        $q = trim((string)input_str('q', ''));
        $role = input_str('role', 'all') ?? 'all';
        if (!in_array($role, ['all', 'User', 'Teacher'], true)) $role = 'all';

        $friends = $this->db->GetFriends($me);
        $this->view('Friends/Index', obj([
            'IsAdmin'  => false,
            'Tab'      => $tab,
            'Friends'  => $friends,
            'Incoming' => $incoming,
            'Sent'     => $this->db->GetSentFriendRequests($me),
            'People'   => $this->db->SearchPeople($me, $q, $role, 30),
            'Query'    => $q,
            'Role'     => $role,
        ]));
    }

    /** GET /Friends/Search?q=&role=  → শুধু কার্ডগুলোর HTML */
    public function Search(): void
    {
        $me = $this->me(true);
        $q = mb_substr(trim((string)input_str('q', '')), 0, 60);
        $role = input_str('role', 'all') ?? 'all';
        if (!in_array($role, ['all', 'User', 'Teacher'], true)) $role = 'all';
        $people = $this->db->SearchPeople($me, $q, $role, 30);
        header('Content-Type: text/html; charset=utf-8');
        if (!$people) {
            partial('Friends/_Empty', ['icon' => 'fa-magnifying-glass', 'title' => 'কাউকে পাওয়া যায়নি',
                'text' => $q !== '' ? '"' . $q . '" নামে কেউ নেই। অন্য নাম বা username দিয়ে খুঁজুন।' : 'এখনো কেউ নেই।']);
            exit;
        }
        foreach ($people as $p) partial('Friends/_PersonCard', ['p' => $p]);
        exit;
    }

    /** GET /Friends/Person/{id} */
    public function Person(): void
    {
        $me = $this->me();
        $id = input_int('id', 0) ?? 0;
        $user = $id > 0 ? $this->db->GetUserById($id) : null;
        if (!$user || $user->Role === 'Admin') not_found('এই প্রোফাইলটি পাওয়া যায়নি।');

        $isAdminViewer = current_role() === 'Admin';
        $relation = $this->db->GetFriendRelation($me, $id);
        $suspended = $this->db->GetActiveSuspension($id) !== null || !$user->IsActive;
        if ($suspended && !$isAdminViewer && $relation !== 'self') not_found('এই প্রোফাইলটি এখন দেখা যাচ্ছে না।');

        $teacherInfo = null;
        if ($user->Role === 'Teacher') {
            try {
                $teacherInfo = $this->db->one('SELECT `Education`, `Institution`, `SubjectExpertise`, `Experience`, `TeachingStyle`, `AvailableDays`, `PreferredTime`
                    FROM `TeacherRequests` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 1', ['u' => $id]);
            } catch (Throwable $e) { $teacherInfo = null; }
        }

        $this->view('Friends/Person', obj([
            'User'          => $user,
            'Relation'      => $relation,
            'IsAdminViewer' => $isAdminViewer,
            'CanSeeContact' => $relation === 'friends' || $relation === 'self' || $isAdminViewer,
            'FriendCount'   => count($this->db->GetFriendIds($id)),
            'MutualCount'   => $relation === 'self' ? 0 : $this->db->CountMutualFriends($me, $id),
            'TeacherInfo'   => $teacherInfo,
        ]));
    }

    // ============================================================
    // ACTIONS (AJAX)
    // ============================================================

    /** POST /Friends/Send */
    public function Send_POST(): void
    {
        $me = $this->me(true);
        $other = $this->otherId($me);
        if (!$this->db->IsFriendEligibleUser($other)) json_response(['success' => false, 'message' => 'এই ইউজারকে ফ্রেন্ড রিকোয়েস্ট পাঠানো যাবে না।']);
        if ($this->db->GetFriendRelation($me, $other) === 'none'
            && $this->db->CountRecentFriendRequests($me, 60) >= self::MAX_REQUESTS_PER_HOUR) {
            json_response(['success' => false, 'message' => 'এক ঘণ্টায় অনেক রিকোয়েস্ট পাঠিয়েছেন। কিছুক্ষণ পর আবার চেষ্টা করুন।']);
        }

        $result = $this->db->SendFriendRequest($me, $other);
        $name = $this->myName();
        switch ($result) {
            case 'sent':
                $this->db->CreateNotification($other, 'New Friend Request 👋', "$name আপনাকে ফ্রেন্ড রিকোয়েস্ট পাঠিয়েছেন।",
                    'info', '/Friends?tab=requests', 'fa-user-plus');
                $this->done($me, $other, 'ফ্রেন্ড রিকোয়েস্ট পাঠানো হয়েছে।');
            case 'accepted':
                $this->db->CreateNotification($other, 'Friend Request Accepted 🎉', "$name আপনার ফ্রেন্ড রিকোয়েস্ট Accept করেছেন।",
                    'success', '/Friends/Person/' . $me, 'fa-user-check');
                $this->done($me, $other, 'আপনারা এখন ফ্রেন্ড! 🎉');
            case 'already_sent':
                $this->done($me, $other, 'রিকোয়েস্ট আগেই পাঠানো আছে।');
            case 'already_friends':
                $this->done($me, $other, 'আপনারা আগে থেকেই ফ্রেন্ড।');
        }
        json_response(['success' => false, 'message' => 'রিকোয়েস্ট পাঠানো যায়নি। আবার চেষ্টা করুন।']);
    }

    /** POST /Friends/Accept */
    public function Accept_POST(): void
    {
        $me = $this->me(true);
        $other = $this->otherId($me);
        if ($this->db->RespondFriendRequest($me, $other, true) === null) {
            json_response(['success' => false, 'message' => 'রিকোয়েস্টটি আর নেই।', 'relation' => $this->db->GetFriendRelation($me, $other)]);
        }
        $this->db->CreateNotification($other, 'Friend Request Accepted 🎉', $this->myName() . ' আপনার ফ্রেন্ড রিকোয়েস্ট Accept করেছেন।',
            'success', '/Friends/Person/' . $me, 'fa-user-check');
        $this->done($me, $other, 'আপনারা এখন ফ্রেন্ড! 🎉');
    }

    /** POST /Friends/Decline */
    public function Decline_POST(): void
    {
        $me = $this->me(true);
        $other = $this->otherId($me);
        $this->db->RespondFriendRequest($me, $other, false);
        $this->done($me, $other, 'রিকোয়েস্ট মুছে ফেলা হয়েছে।');
    }

    /** POST /Friends/Cancel */
    public function Cancel_POST(): void
    {
        $me = $this->me(true);
        $other = $this->otherId($me);
        $this->db->CancelFriendRequest($me, $other);
        $this->done($me, $other, 'রিকোয়েস্ট ফিরিয়ে নেওয়া হয়েছে।');
    }

    /** POST /Friends/Unfriend */
    public function Unfriend_POST(): void
    {
        $me = $this->me(true);
        $other = $this->otherId($me);
        $this->db->Unfriend($me, $other);
        $this->done($me, $other, 'ফ্রেন্ড লিস্ট থেকে সরানো হয়েছে।');
    }

    /** GET /Friends/Count — নেভবারের ব্যাজের জন্য */
    public function Count(): void
    {
        $me = current_user_id();
        if ($me === null || current_role() === 'Admin') json_response(['success' => true, 'requests' => 0]);
        json_response(['success' => true, 'requests' => $this->db->CountIncomingFriendRequests($me)]);
    }
}
