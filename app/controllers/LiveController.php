<?php
/**
 * লাইভ ক্লাস (BigBlueButton — live.mashtermoshai.com)
 *  GET /Live/Lesson/{lessonId}  — কোর্সের লাইভ ক্লাস: টিচার/অ্যাডমিন শুরু করে (মডারেটর), শিক্ষার্থী যোগ দেয়
 *  GET /Live/Record/{lessonId}  — রেকর্ডেড ক্লাস: টিচার একা রুমে ঢুকে রেকর্ড করে (রেকর্ডিং VPS এ জমা, পরে নিজে থেকে কোর্সে আসে)
 *  GET /Live/Chat/{classId}     — চ্যাটের লাইভ ক্লাস (প্রিমিয়াম টিচার ↔ তার শিক্ষার্থী)
 *  GET /Live/Status?t=lesson|chat&id= — শুরু হয়েছে কিনা (অপেক্ষার পেজ এটা দেখে নিজে থেকে ঢোকে)
 */
class LiveController extends Controller
{
    private function me(): int
    {
        $id = current_user_id();
        if ($id === null) redirect('/Account/Login?returnUrl=' . rawurlencode((string)($_SERVER['REQUEST_URI'] ?? '/')));
        return $id;
    }

    private function myName(): string { return (string)(session_get('UserFullName') ?: session_get('UserName') ?: 'User'); }

    private function problem(string $msg, string $back): void
    {
        $this->view('Waiting', null, ['Error' => $msg, 'Back' => $back]);
    }

    private function canManageCourse(Obj $c, int $me): bool { return (int)$c->TeacherId === $me || (is_admin() && admin_can(admin_action_rule('Courses'))); }

    /** কোর্সের লাইভ ক্লাস */
    public function Lesson(): void
    {
        $me = $this->me();
        $l = $this->db->GetLesson((int)input_int('id', 0));
        $c = $l ? $this->db->GetCourse($l->CourseId) : null;
        if (!$l || !$c || $l->Kind !== 'live') not_found('ক্লাসটি পাওয়া যায়নি।');
        $back = '/Course/Learn/' . $c->Id . '?lesson=' . $l->Id;
        $mod = $this->canManageCourse($c, $me);
        if (!$mod && !$this->db->IsEnrolled($c->Id, $me)) redirect('/Course/Details/' . $c->Id);
        if (!bbb_ready()) $this->problem('লাইভ ক্লাসের সার্ভার এখনো চালু করা হয়নি — অ্যাডমিনকে জানান।', $back);
        if ($mod) {
            $err = null;
            if (!bbb_create($l->MeetingId, $c->Title . ' — ' . $l->Title, ['record' => true, 'autoStart' => true, 'logoutURL' => site_public_url() . '/Course/Build/' . $c->Id,
                'duration' => $l->DurationMin + 60, 'meta' => ['course' => $c->Id, 'lesson' => $l->Id]], $err)) $this->problem($err ?: 'লাইভ ক্লাস শুরু করা যায়নি।', '/Course/Build/' . $c->Id);
            if ($this->db->SetLessonLiveStarted($l->Id)) {
                foreach ($this->db->GetEnrolledUserIds($c->Id) as $uid) {
                    try { $this->db->CreateNotification($uid, 'লাইভ ক্লাস শুরু হয়েছে 🔴', "\"{$c->Title}\" — \"{$l->Title}\" এখন চলছে। এখনই যোগ দিন!", 'success', '/Live/Lesson/' . $l->Id, 'fa-video'); } catch (Throwable $e) { }
                }
            }
            redirect(bbb_join_url($l->MeetingId, $this->myName(), true, $me));
        }
        if ($l->LiveState === 'upcoming' || $l->LiveState === 'none') $this->view('Waiting', null, ['Title' => $l->Title, 'Sub' => $c->Title, 'Starts' => $l->StartsAt, 'Type' => 'lesson', 'Id' => $l->Id, 'Back' => $back]);
        if (!bbb_running($l->MeetingId)) {
            if ($l->LiveState === 'ended') $this->problem('এই লাইভ ক্লাসটি শেষ হয়ে গেছে — রেকর্ডিং তৈরি হলে কোর্সের পেজে দেখতে পাবেন।', $back);
            $this->view('Waiting', null, ['Title' => $l->Title, 'Sub' => $c->Title, 'Starts' => $l->StartsAt, 'Type' => 'lesson', 'Id' => $l->Id, 'Back' => $back]);
        }
        $this->db->MarkLessonDone($c->Id, $l->Id, $me);
        try { if ($code = $this->db->CheckCertificate($c->Id, $me)) $this->db->CreateNotification($me, 'অভিনন্দন! সার্টিফিকেট পেয়েছেন 🎉', "\"{$c->Title}\" কোর্স সম্পূর্ণ।", 'success', '/Course/Certificate/' . $code, 'fa-award'); } catch (Throwable $e) { }
        redirect(bbb_join_url($l->MeetingId, $this->myName(), false, $me));
    }

    /** রেকর্ডেড ক্লাস রেকর্ড করা (শুধু টিচার / অ্যাডমিন) */
    public function Record(): void
    {
        $me = $this->me();
        $l = $this->db->GetLesson((int)input_int('id', 0));
        $c = $l ? $this->db->GetCourse($l->CourseId) : null;
        if (!$l || !$c || $l->Kind !== 'recorded' || !$this->canManageCourse($c, $me)) not_found('ক্লাসটি পাওয়া যায়নি।');
        $back = '/Course/Build/' . $c->Id;
        if (!bbb_ready()) $this->problem('লাইভ ক্লাসের সার্ভার এখনো চালু করা হয়নি — Admin → Live Class Server এ সার্ভারের secret দিন।', $back);
        if ($l->RecordingUrl) $this->problem('এই ক্লাসের রেকর্ডিং আগেই আছে। নতুন করে রেকর্ড করতে কোর্সের পেজে "আবার রেকর্ড" চাপুন।', $back);
        $err = null;
        if (!bbb_create($l->MeetingId, '[রেকর্ডিং] ' . $c->Title . ' — ' . $l->Title, ['record' => true, 'autoStart' => true, 'logoutURL' => site_public_url() . $back . '?sync=1',
            'welcome' => 'রেকর্ডিং চালু আছে — ক্লাস নেওয়া শেষ হলে উপরের মেনু থেকে <b>End meeting</b> চাপুন। কিছুক্ষণ পর রেকর্ডিং কোর্সে নিজে থেকে চলে আসবে।',
            'meta' => ['course' => $c->Id, 'lesson' => $l->Id]], $err)) $this->problem($err ?: 'রেকর্ডিং রুম খোলা যায়নি।', $back);
        redirect(bbb_join_url($l->MeetingId, $this->myName(), true, $me));
    }

    /** চ্যাটের লাইভ ক্লাস — টিচার (প্রিমিয়াম) শুরু করে, চ্যাটের শিক্ষার্থী যোগ দেয় */
    public function Chat(): void
    {
        $me = $this->me();
        $cl = $this->db->GetChatClass((int)input_int('id', 0));
        if (!$cl || $cl->Status === 'Cancelled') not_found('ক্লাসটি পাওয়া যায়নি বা বাতিল হয়েছে।');
        $key = ['grp' => 'g', 'dir' => 'd', 'req' => 'r', 'app' => ''][$cl->Kind] ?? 'd';
        $key .= $cl->AppId;
        $t = $this->db->GetChatThread($key, $me);
        if (!$t) not_found('ক্লাসটি পাওয়া যায়নি।');
        $back = '/Chat?c=' . $key;
        if (!bbb_ready()) $this->problem('লাইভ ক্লাসের সার্ভার এখনো চালু করা হয়নি — অ্যাডমিনকে জানান।', $back);
        $mid = 'mm-chat-' . $cl->Id;
        if ($cl->TeacherId === $me) {
            if (!teacher_live_enabled($me)) $this->problem('লাইভ ক্লাস এখন আলাদা পেইড ক্লাস হিসেবে চলে — Course থেকে লাইভ ক্লাস / ব্যাচ বানিয়ে নিজে দাম ঠিক করুন, শিক্ষার্থীরা পেমেন্ট করে যোগ দেবে।', '/Course/Manage');
            $err = null;
            if (!bbb_create($mid, (string)$cl->Title, ['record' => false, 'logoutURL' => site_public_url() . $back, 'duration' => $cl->DurationMin + 60], $err)) $this->problem($err ?: 'লাইভ ক্লাস শুরু করা যায়নি।', $back);
            if (empty($_SESSION['_live_notified'][$cl->Id])) {
                $_SESSION['_live_notified'][$cl->Id] = 1;
                $ids = $t->Kind === 'grp' ? array_map(fn($u) => (int)$u->Id, $this->db->GetChatGroupMembers($t->RefId)) : [$t->OtherId];
                foreach ($ids as $uid) if ($uid !== $me) { try { $this->db->CreateNotification($uid, 'লাইভ ক্লাস শুরু হয়েছে 🔴', $this->myName() . " \"{$cl->Title}\" ক্লাস শুরু করেছেন — এখনই যোগ দিন!", 'success', '/Live/Chat/' . $cl->Id, 'fa-video'); } catch (Throwable $e) { } }
            }
            redirect(bbb_join_url($mid, $this->myName(), true, $me));
        }
        if (!bbb_running($mid)) $this->view('Waiting', null, ['Title' => (string)$cl->Title, 'Sub' => 'অনলাইন ক্লাস', 'Starts' => $cl->StartsAt, 'Type' => 'chat', 'Id' => $cl->Id, 'Back' => $back]);
        redirect(bbb_join_url($mid, $this->myName(), false, $me));
    }

    /** GET /Live/Status?t=&id= — {running: bool} */
    public function Status(): void
    {
        $me = current_user_id();
        if ($me === null) json_response(['running' => false]);
        $id = (int)input_int('id', 0);
        $mid = null;
        if (input_str('t', '') === 'chat') { $cl = $this->db->GetChatClass($id); if ($cl) $mid = 'mm-chat-' . $cl->Id; }
        else { $l = $this->db->GetLesson($id); if ($l && $this->db->IsEnrolled($l->CourseId, $me)) $mid = $l->MeetingId; }
        json_response(['running' => $mid ? bbb_running($mid) : false]);
    }
}
