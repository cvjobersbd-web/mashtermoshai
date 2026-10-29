<?php
/**
 * সেভ করা জিনিস (পরে দেখার জন্য)।
 *  POST /Saved/Toggle (type = post|tutor|circular|tuition|notice, id) → {saved, count}
 */
class SavedController extends Controller
{
    /** POST /Saved/Toggle */
    public function Toggle_POST(): void
    {
        $me = current_user_id();
        if ($me === null) json_response(['success' => false, 'message' => 'সেভ করতে লগইন করুন।']);
        $type = (string)input_str('type', '');
        $id = input_int('id', 0) ?? 0;
        if (!in_array($type, Db::SAVE_TYPES, true) || $id <= 0) json_response(['success' => false, 'message' => 'ভুল রিকোয়েস্ট।']);

        // জিনিসটা আছে কিনা / দেখার অনুমতি আছে কিনা (আনসেভ সবসময় করা যায়)
        if (!$this->db->IsSaved($me, $type, $id) && !$this->exists($type, $id, $me)) {
            json_response(['success' => false, 'message' => 'এটা আর পাওয়া যাচ্ছে না।']);
        }
        $saved = $this->db->ToggleSaved($me, $type, $id);
        json_response(['success' => true, 'saved' => $saved, 'count' => $this->db->CountSaved($me),
            'message' => $saved ? 'Saved — প্রোফাইলের "Saved Items" এ পাবেন।' : 'Saved থেকে সরানো হয়েছে।']);
    }

    private function exists(string $type, int $id, int $me): bool
    {
        try {
            switch ($type) {
                case 'post':
                    $p = $this->db->GetProfilePostById($id);
                    return $p !== null && $this->db->CanViewProfilePost($p, $me, current_role() === 'Admin');
                case 'tutor':
                    $u = $this->db->GetUserById($id);
                    return $u !== null && $u->Role === 'Teacher' && $u->Id !== $me;
                case 'circular': return (int)$this->db->scalar('SELECT COUNT(*) FROM `TuitionCircularPosts` WHERE `Id` = :id', ['id' => $id]) > 0;
                case 'tuition':  return (int)$this->db->scalar('SELECT COUNT(*) FROM `TuitionPosts` WHERE `Id` = :id', ['id' => $id]) > 0;
                case 'notice':   return (int)$this->db->scalar('SELECT COUNT(*) FROM `Notices` WHERE `Id` = :id AND `IsActive` = 1', ['id' => $id]) > 0;
            }
        } catch (Throwable $e) { }
        return false;
    }
}
