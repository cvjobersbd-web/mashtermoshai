<?php
/**
 * স্টাডি গোল (যেমন HSC 2027 — 60%)।
 * প্রগ্রেস: গোলে টাস্ক থাকলে টাস্ক শেষ হওয়ার হার, না থাকলে নিজে স্লাইডারে সেট করা মান।
 *
 *  POST /Goal/Save   (goalId?, title, category, targetDate, progress, primary)
 *  POST /Goal/Delete (goalId)
 *  POST /Goal/AddTask (goalId, title)
 *  POST /Goal/Task    (taskId, done | delete)
 * প্রতিটা উত্তরে আপডেট হওয়া গোল কার্ডের HTML থাকে।
 */
class GoalController extends Controller
{
    private function me(): int
    {
        $id = current_user_id();
        if ($id === null || current_role() === 'Admin') json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
        return $id;
    }

    private function myGoal(int $me, int $goalId): Obj
    {
        $g = $goalId > 0 ? $this->db->GetGoal($goalId) : null;
        if (!$g || $g->UserId !== $me) json_response(['success' => false, 'message' => 'গোলটি পাওয়া যায়নি।']);
        return $g;
    }

    private function done(int $me, ?int $goalId, string $msg): void
    {
        $html = '';
        if ($goalId) {
            $g = $this->db->GetGoal($goalId);
            ob_start(); partial('Profile/_GoalCard', ['g' => $g, 'isOwner' => true]); $html = ob_get_clean();
        }
        $primary = null;
        foreach ($this->db->GetGoals($me) as $g) if ($g->IsPrimary) { $primary = ['id' => $g->Id, 'title' => $g->Title, 'progress' => $g->Progress]; break; }
        json_response(['success' => true, 'message' => $msg, 'html' => $html, 'primary' => $primary]);
    }

    /** POST /Goal/Save */
    public function Save_POST(): void
    {
        $me = $this->me();
        $goalId = input_int('goalId', 0) ?: null;
        if ($goalId) $this->myGoal($me, $goalId);
        elseif ($this->db->CountGoals($me) >= 10) json_response(['success' => false, 'message' => 'সর্বোচ্চ ১০টা গোল রাখা যায়। পুরোনো একটা মুছে নতুন যোগ করুন।']);

        $title = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str('title', ''))), 0, 120);
        if ($title === '') json_response(['success' => false, 'message' => 'গোলের নাম লিখুন (যেমন: HSC 2027)।']);
        $cat = (string)input_str('category', 'Custom');
        if (!in_array($cat, Db::GOAL_CATEGORIES, true)) $cat = 'Custom';
        $date = trim((string)input_str('targetDate', ''));
        if ($date !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $date);
            if (!$d || $d->format('Y-m-d') !== $date) json_response(['success' => false, 'message' => 'তারিখ ঠিক নেই।']);
        }
        $id = $this->db->SaveGoal($me, $goalId, $title, $cat, $date === '' ? null : $date, input_int('progress', 0) ?? 0, input_bool('primary'));
        $this->db->TouchActivity($me, 1);
        $this->done($me, $id, $goalId ? 'গোল আপডেট হয়েছে।' : 'নতুন গোল যোগ হয়েছে। 🎯');
    }

    /** POST /Goal/Delete */
    public function Delete_POST(): void
    {
        $me = $this->me();
        $g = $this->myGoal($me, input_int('goalId', 0) ?? 0);
        $this->db->DeleteGoal($me, $g->Id);
        $this->done($me, null, 'গোল মুছে ফেলা হয়েছে।');
    }

    /** POST /Goal/AddTask */
    public function AddTask_POST(): void
    {
        $me = $this->me();
        $g = $this->myGoal($me, input_int('goalId', 0) ?? 0);
        $title = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str('title', ''))), 0, 150);
        if ($title === '') json_response(['success' => false, 'message' => 'কাজের নাম লিখুন।']);
        if ($this->db->CountGoalTasks($g->Id) >= 50) json_response(['success' => false, 'message' => 'একটা গোলে সর্বোচ্চ ৫০টা কাজ।']);
        $this->db->AddGoalTask($g->Id, $title);
        $this->done($me, $g->Id, 'কাজ যোগ হয়েছে।');
    }

    /** POST /Goal/Task (taskId, done=true|false | delete=true) */
    public function Task_POST(): void
    {
        $me = $this->me();
        $taskId = input_int('taskId', 0) ?? 0;
        $goalId = $this->db->GoalIdOfTask($taskId, $me);
        if (!$goalId) json_response(['success' => false, 'message' => 'কাজটি পাওয়া যায়নি।']);
        $delete = input_bool('delete');
        $this->db->SetGoalTask($taskId, input_bool('done'), $delete);
        if (!$delete && input_bool('done')) $this->db->TouchActivity($me, 2);
        $this->done($me, $goalId, $delete ? 'কাজ মুছে ফেলা হয়েছে।' : 'আপডেট হয়েছে।');
    }
}
