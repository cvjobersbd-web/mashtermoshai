<?php
/**
 * স্টুডেন্ট / টিচার নিজের ড্যাশবোর্ড থেকে রিভিউ জমা দেয়।
 * রিভিউ প্রথমে Pending থাকে — অ্যাডমিন Approve করলে হোম পেজে দেখায়।
 */
class ReviewController extends Controller
{
    /** POST /Review/Submit */
    public function Submit_POST(): void
    {
        $userId = current_user_id();
        $role = current_role();
        if ($userId === null) json_response(['success' => false, 'message' => 'রিভিউ দিতে লগইন করুন।']);
        if (!in_array($role, ['User', 'Teacher'], true)) json_response(['success' => false, 'message' => 'শুধু স্টুডেন্ট ও টিচাররা রিভিউ দিতে পারবেন।']);

        $rating = input_int('rating', 5) ?? 5;
        if ($rating < 1 || $rating > 5) json_response(['success' => false, 'message' => '১ থেকে ৫ স্টার দিন।']);
        $subtitle = trim((string)input_str('subtitle', ''));
        $content = trim(preg_replace("/\r\n|\r/", "\n", (string)input_str('content', '')));
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        if (mb_strlen($subtitle) > 80) json_response(['success' => false, 'message' => 'পরিচয় সর্বোচ্চ ৮০ অক্ষর।']);
        if (mb_strlen($content) < 20) json_response(['success' => false, 'message' => 'রিভিউ অন্তত ২০ অক্ষরের হতে হবে।']);
        if (mb_strlen($content) > 500) json_response(['success' => false, 'message' => 'রিভিউ সর্বোচ্চ ৫০০ অক্ষর।']);

        $isNew = null;
        if (!$this->db->SaveUserReview($userId, $rating, $subtitle === '' ? null : $subtitle, $content, $isNew)) {
            json_response(['success' => false, 'message' => 'রিভিউ সেভ করা যায়নি। আবার চেষ্টা করুন।']);
        }

        $name = session_get('UserFullName') ?? session_get('UserName') ?? 'A user';
        foreach ($this->db->GetAdmins() as $a) {
            $this->db->CreateNotification((int)$a->Id, $isNew ? 'New Review ⭐' : 'Review Updated',
                "$name একটি রিভিউ " . ($isNew ? 'দিয়েছেন' : 'আপডেট করেছেন') . " — Approve করুন।", 'info', '/Admin/Reviews?status=Pending', 'fa-star');
        }
        json_response(['success' => true, 'message' => 'ধন্যবাদ! আপনার রিভিউ অ্যাডমিনের কাছে পাঠানো হয়েছে। Approve হলে হোম পেজে দেখাবে।']);
    }
}
