<?php
class FindTutorController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

    /** GET /FindTutor  (view: FindTutor) */
    public function Index(): void
    {
        $tutors = $this->db->GetTutorDirectory();
        $me = current_user_id();
        $saved = $me !== null && current_role() !== 'Admin' ? $this->db->SavedKeys($me, 'tutor') : [];
        $this->view('FindTutor', $tutors, ['TutorCount' => count($tutors), 'SavedTutors' => $saved]);
    }

    /** GET /FindTutor/GetTutor?id= */
    public function GetTutor(): void
    {
        $id = input_int('id', 0);
        foreach ($this->db->GetTutorDirectory() as $t) {
            if ($t->Id === $id) json_camel(['success' => true, 'tutor' => $t]);
        }
        json_response(['success' => false, 'message' => 'Tutor not found']);
    }

    /** GET /FindTutor/GetTutorByTutorId?tutorId= */
    public function GetTutorByTutorId(): void
    {
        $tutorId = input_str('tutorId');
        if (is_blank($tutorId)) json_response(['success' => false, 'message' => 'Tutor ID is required']);
        foreach ($this->db->GetTutorDirectory() as $t) {
            if (strcasecmp($t->TutorId, trim($tutorId)) === 0) {
                json_response(['success' => true, 'tutorId' => $t->TutorId, 'fullName' => $t->FullName]);
            }
        }
        json_response(['success' => false, 'message' => 'Tutor not found with this ID']);
    }

    /** GET /FindTutor/GetTeacherProfile?userId= */
    public function GetTeacherProfile(): void
    {
        $user = $this->db->GetUserById(input_int('userId', 0));
        $me = current_user_id();
        $full = is_admin() || ($me !== null && $me === (int)($user->Id ?? 0));   // অ্যাডমিন আর টিচার নিজে সব দেখেন
        // শুধু টিচারের প্রোফাইল দেখা যায় — শিক্ষার্থী / অন্য ইউজারের তথ্য এখান থেকে না
        if (!$user || (!$full && $user->Role !== 'Teacher')) json_response(['success' => false, 'message' => 'User not found']);
        // যোগাযোগের তথ্য (ইমেইল, মোবাইল, ঠিকানা, লিংক) কাউকে দেখানো হয় না — যোগাযোগ শুধু রিকোয়েস্ট / চ্যাটে
        $keys = $full
            ? ['Id', 'FullName', 'Email', 'MobileNumber', 'Gender', 'AgeRange', 'EducationLevel', 'ExamDegreeTitle',
                'InstitutionName', 'PassingYear', 'Country', 'StreetAddress', 'PermanentAddress', 'YearsOfExperience', 'CvLink',
                'GithubProfile', 'PortfolioLink', 'LinkedInProfile', 'ProfileImageLink']
            : ['Id', 'FullName', 'Gender', 'AgeRange', 'EducationLevel', 'ExamDegreeTitle',
                'InstitutionName', 'PassingYear', 'Country', 'YearsOfExperience', 'ProfileImageLink'];
        $out = [];
        foreach ($keys as $k) $out[$k] = $user->$k;
        // প্যাকেজ ছাড়া দর্শক টিচারের আসল ছবি পান না — ছোট থাম্বনেইল
        if (!$full && !viewer_sees_teacher_photos() && !empty($out['ProfileImageLink'])) $out['ProfileImageLink'] = teacher_thumb_url((int)$user->Id, $out['ProfileImageLink']) ?: null;
        $out['IsSaved'] = $me !== null && $user->Role === 'Teacher' ? $this->db->IsSaved($me, 'tutor', (int)$user->Id) : false;
        $out['CanSave'] = $me !== null && current_role() !== 'Admin' && $user->Role === 'Teacher' && $me !== (int)$user->Id;
        json_camel(['success' => true, 'user' => $out]);
    }

    /** POST /FindTutor/SubmitTutorRequest */
    public function SubmitTutorRequest_POST(): void
    {
        try {
            // নির্দিষ্ট টিচারকে Direct Request — প্যাকেজ নেওয়া শিক্ষার্থী, অথবা ৳২০ এর "১টা Apply" থাকলে (একটা খরচ হয়)
            $useApply = false;
            if (!is_blank(input_str('tutorId')) && !viewer_can_contact_teachers()
                && current_user_id() !== null && current_role() === 'User' && $this->db->GetApplyCredits((int)current_user_id(), 'SApply') > 0) $useApply = true;
            if (!is_blank(input_str('tutorId')) && !viewer_can_contact_teachers() && !$useApply) {
                json_response(['success' => false, 'needPackage' => student_package_url(),
                    'message' => current_user_id() === null ? 'টিচারকে রিকোয়েস্ট পাঠাতে আগে লগইন করুন।' : 'টিচারকে সরাসরি রিকোয়েস্ট পাঠাতে শিক্ষার্থী প্যাকেজ নিন (' . package_price_hint('Student') . ')।']);
            }
            // টিচারের প্যাকেজ না থাকলেও রিকোয়েস্ট যায় (টিচার প্যাকেজ কিনলে দেখবে)। একই টিচারকে ২৪ ঘণ্টায় একবারই — সাড়া না পেলে পরে আবার পাঠানো যায়
            if (!is_blank(input_str('tutorId')) && preg_match('/^TCH-(\d+)$/', trim((string)input_str('tutorId')), $tm)) {
                $ttr = $this->db->GetTeacherRequestById((int)$tm[1] - 1000);
                $sMe = current_user_id();
                if ($ttr && $sMe !== null && current_role() !== 'Admin') {
                    $last = $this->db->LastPendingRequestAt((int)$sMe, (int)$ttr->UserId);
                    if ($last && strtotime($last) > time() - 86400) {
                        $h = max(1, (int)ceil((strtotime($last) + 86400 - time()) / 3600));
                        json_response(['success' => false, 'message' => "এই টিচারকে আপনার রিকোয়েস্ট পাঠানো আছে। ২৪ ঘণ্টার মধ্যে সাড়া না পেলে আবার পাঠাতে পারবেন (আর প্রায় $h ঘণ্টা পর)।"]);
                    }
                }
            }
            $studentName = input_str('studentName');
            $phone = input_str('phoneNumber');
            if (is_blank($studentName)) json_response(['success' => false, 'message' => 'Student name is required.']);
            if (is_blank($phone)) json_response(['success' => false, 'message' => 'Phone number is required.']);

            $phoneClean = trim($phone);
            $re = '/^01[3-9]\d{8}$/';
            if (!preg_match($re, $phoneClean)) json_response(['success' => false, 'message' => 'Please enter a valid Bangladeshi phone number (e.g., 01712345678).']);
            $wa = input_str('whatsAppNumber');
            $waClean = is_blank($wa) ? $phoneClean : trim($wa);
            if (!preg_match($re, $waClean)) json_response(['success' => false, 'message' => 'Please enter a valid WhatsApp number (e.g., 01712345678).']);

            $opt = fn($name) => is_blank(input_str($name)) ? null : trim(input_str($name));
            $req = obj([
                'TutorId' => $opt('tutorId'), 'TutorName' => $opt('tutorName'), 'StudentName' => trim($studentName),
                'PhoneNumber' => $phoneClean, 'WhatsAppNumber' => $waClean, 'Email' => $opt('email'),
                'StudentClass' => $opt('studentClass'), 'RequiredSubject' => $opt('requiredSubject'),
                'PreferredArea' => $opt('preferredArea'), 'TeachingMode' => $opt('teachingMode'),
                'PreferredDays' => $opt('preferredDays'), 'PreferredTime' => $opt('preferredTime'),
                'MonthlyBudget' => $opt('monthlyBudget'), 'AdditionalNotes' => $opt('additionalNotes'),
                'RequestType' => $opt('requestType') ?? 'SpecificTutor',
                'RequestId' => $opt('requestId') ?? ('REQ-' . date('Ymd') . '-' . random_int(100, 998)),
                'Status' => 'Pending',
            ]);

            $err = null;
            if ($this->db->CreateTutorRequest($req, $err)) {
                if ($useApply) { try { $this->db->ConsumeApplyCredit((int)current_user_id(), 'SApply'); } catch (Throwable $e) { error_log('[apply credit] ' . $e->getMessage()); } }
                // ===== সব অ্যাডমিনকে নোটিফাই — নির্দিষ্ট টিচারের রিকোয়েস্ট (TCH-) সরাসরি টিচারের কাছে যায়, অ্যাডমিনের কাছে না =====
                $toTeacher = !is_blank($req->TutorId) && str_starts_with((string)$req->TutorId, 'TCH-');
                if (!$toTeacher) try {
                    $typeLabel = match ($req->RequestType) {
                        'NeedTutor' => 'New Tutor Request',
                        'AreaTuition' => 'Area Tuition Request',
                        default => 'Tutor Request',
                    };
                    $msg = "{$req->StudentName} ({$req->PhoneNumber}) submitted a request.";
                    if (!is_blank($req->TutorName)) $msg .= " Tutor: {$req->TutorName}.";
                    if (!is_blank($req->RequiredSubject)) $msg .= " Subject: {$req->RequiredSubject}.";
                    foreach ($this->db->GetAdmins() as $admin) {
                        $this->db->CreateNotification((int)$admin->Id, "📩 $typeLabel", $msg, 'info', '/Admin/TutorRequests', 'fa-user-plus');
                    }
                } catch (Throwable $e) {}

                // ===== নির্দিষ্ট টিচারকে নোটিফাই (TutorId = TCH-{1000+requestId}) =====
                $studentUid = current_user_id() !== null && current_role() !== 'Admin' ? (int)current_user_id() : null;
                try { $this->db->SetTutorRequestUsers((int)$req->Id, $studentUid, null); } catch (Throwable $e) { }
                try {
                    if (!is_blank($req->TutorId) && str_starts_with($req->TutorId, 'TCH-')) {
                        $parts = explode('-', $req->TutorId);
                        if (count($parts) === 2 && ctype_digit($parts[1])) {
                            $tr = $this->db->GetTeacherRequestById((int)$parts[1] - 1000);
                            // কে পাঠাল আর কোন টিচারের কাছে গেল — টিচার Accept করলে দুজনের চ্যাট খোলে
                            if ($tr) $this->db->SetTutorRequestUsers((int)$req->Id, $studentUid !== (int)$tr->UserId ? $studentUid : null, (int)$tr->UserId);
                            if ($tr && $this->db->GetUserById($tr->UserId)) {
                                if (teacher_profile_open((int)$tr->UserId)) {
                                    $this->db->CreateNotification($tr->UserId, '📩 New Tutor Request!',
                                        "{$req->StudentName} ({$req->PhoneNumber}) wants to contact you for {$req->RequiredSubject} tuition. Dashboard থেকে Accept বা Reject করুন।",
                                        'info', '/Teacher/TeacherDashboard?section=direct-requests', 'fa-envelope-open-text');
                                } else {
                                    // প্যাকেজ নেই — শিক্ষার্থীর কোনো তথ্য না দিয়ে শুধু কতজন রিকোয়েস্ট পাঠিয়েছে
                                    $nReq = 0; try { $nReq = (int)$this->db->scalar("SELECT COUNT(*) FROM `TutorRequests` WHERE `TeacherUserId` = :t AND `Status` = 'Pending'", ['t' => (int)$tr->UserId]); } catch (Throwable $e) { }
                                    $this->db->CreateNotification($tr->UserId, '📩 আপনি টিউশন রিকোয়েস্ট পেয়েছেন',
                                        'আপনার কাছে ' . bn_num(max(1, $nReq)) . ' জন শিক্ষার্থী টিউশন রিকোয়েস্ট পাঠিয়েছে — শিক্ষার্থীর পুরো ডিটেইলস দেখতে টিচার প্যাকেজ কিনুন।',
                                        'info', '/Package?for=teacher', 'fa-envelope-open-text');
                                }
                            }
                        }
                    }
                } catch (Throwable $e) {}

                pixel_track('Lead', ['content_name' => 'Tutor Request', 'content_category' => (string)($req->RequiredSubject ?? '')]);
                json_response([
                    'success' => true,
                    'message' => $toTeacher ? 'রিকোয়েস্ট টিচারের কাছে পাঠানো হয়েছে! টিচার Accept করলে চ্যাটে কথা বলতে পারবেন।' : 'Request submitted successfully! Our admin team will contact you soon.',
                    'requestId' => $req->RequestId,
                    'id' => $req->Id,
                ]);
            }
            json_response(['success' => false, 'message' => is_blank($err) ? 'Failed to submit request. Please try again.' : "Failed to submit request: $err"]);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    /** ছবি ভ্যালিডেট + সেভ; ভুল হলে JSON error দিয়ে থামে */
    private function saveTeachImage(array $file): string
    {
        $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::IMAGE_EXTS, true) || $file['size'] > 5 * 1024 * 1024) {
            json_response(['success' => false, 'message' => 'Invalid image or size over 5MB.']);
        }
        return save_upload($file, 'teach-posts', 'post_' . ticks(), $ext);
    }

    /** ভিডিও (MP4/WEBM/MOV, ২৫ MB) যাচাই + সেভ */
    private function saveTeachVideo(array $file): string
    {
        $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['.mp4', '.webm', '.mov', '.m4v'], true)) json_response(['success' => false, 'message' => 'শুধু MP4, WEBM বা MOV ভিডিও দেওয়া যাবে।']);
        if ($file['size'] > 25 * 1024 * 1024) json_response(['success' => false, 'message' => 'ভিডিও ২৫ MB এর কম হতে হবে।']);
        $head = (string)@file_get_contents($file['tmp_name'], false, null, 0, 16);
        if (substr($head, 4, 4) !== 'ftyp' && substr($head, 0, 4) !== "\x1A\x45\xDF\xA3") json_response(['success' => false, 'message' => 'ভিডিও ফাইলটি সঠিক নয়।']);
        return save_upload($file, 'teach-videos', 'vid_' . ticks() . '_' . bin2hex(random_bytes(3)), $ext);
    }

    /** POST /FindTutor/CreateTeachPost */
    public function CreateTeachPost_POST(): void
    {
        if (session_get('UserRole') !== 'Teacher') json_response(['success' => false, 'message' => 'শুধু টিচাররা এখানে পোস্ট করতে পারবেন।']);
        $content = trim((string)input_str('content', ''));
        $image = upload_file('imageFile');
        $video = upload_file('videoFile');
        // "পড়াতে চাই" — শুধু লেখা (ছবি / ভিডিও বন্ধ)
        if ($image || $video) json_response(['success' => false, 'message' => 'এখানে শুধু লেখা দিয়ে পোস্ট করা যাবে — ছবি বা ভিডিও দেওয়া যাবে না।']);
        if ($content === '') json_response(['success' => false, 'message' => 'কিছু লিখুন।']);
        if (mb_strlen($content) > 3000) json_response(['success' => false, 'message' => 'পোস্ট সর্বোচ্চ ৩০০০ অক্ষর।']);
        if ($image && $video) json_response(['success' => false, 'message' => 'একটা ছবি অথবা একটা ভিডিও দিন — দুটো একসাথে নয়।']);
        // মোবাইল নম্বর, ইমেইল, ফেসবুক, ঠিকানা ইত্যাদি থাকলে পোস্ট হবে না — যোগাযোগ শুধু সাইটের Apply দিয়ে
        if ($why = contact_info_error([$content], 'teacher')) json_response(['success' => false, 'contact' => true, 'message' => $why]);
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Invalid user.']);
        // "পড়াতে চাই" এ পোস্ট করা ফ্রি — প্যাকেজ লাগে শিক্ষার্থীর রিকোয়েস্টের ডিটেইলস দেখতে

        $imageUrl = $image ? $this->saveTeachImage($image) : null;
        $videoUrl = $video ? $this->saveTeachVideo($video) : null;
        $post = obj(['UserId' => $userId, 'Content' => $content, 'ImageUrl' => $imageUrl, 'VideoUrl' => $videoUrl]);
        $err = null;
        if ($this->db->CreateTeachWantedPost($post, $err)) {
            pixel_track('PostRequest', ['content_name' => 'Teach Post (পড়াতে চাই)', 'post_type' => 'teacher']);
            tg_notify_post('🧑‍🏫 "পড়াতে চাই" তে নতুন পোস্ট', tg_who() . ' (টিচার) পোস্ট করেছেন' . ($content !== '' ? ":\n" . $content : ($videoUrl ? ' — ভিডিও' : ' — ছবি')), '/FindTutor');
            json_response(['success' => true, 'id' => $post->Id]);
        }
        if ($imageUrl) $this->deleteTeachImage($imageUrl);
        if ($videoUrl) delete_upload($videoUrl);
        json_response(['success' => false, 'message' => $err]);
    }

    /** POST /FindTutor/EditTeachPost */
    public function EditTeachPost_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login.']);

        $postId = input_int('postId', 0);
        $content = trim((string)input_str('content', ''));
        if (!is_admin() && ($why = contact_info_error([$content], 'teacher'))) json_response(['success' => false, 'contact' => true, 'message' => $why]);
        $image = upload_file('imageFile');
        $imageChanged = false;
        $newImageUrl = null;
        if ($image) json_response(['success' => false, 'message' => 'এখানে শুধু লেখা দিয়ে পোস্ট করা যাবে — ছবি বা ভিডিও দেওয়া যাবে না।']);
        elseif (input_bool('removeImage')) $imageChanged = true;

        $old = $final = $err = null;
        if ($this->db->UpdateTeachWantedPost($postId, $userId, $content, $imageChanged, $newImageUrl, $old, $final, $err)) {
            if ($imageChanged) $this->deleteTeachImage($old);
            json_response(['success' => true, 'content' => $content, 'imageUrl' => $final ?? '']);
        }
        if ($newImageUrl !== null) $this->deleteTeachImage($newImageUrl);
        json_response(['success' => false, 'message' => $err ?? 'Failed to update post.']);
    }

    /** POST /FindTutor/DeleteTeachPost */
    public function DeleteTeachPost_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login.']);
        $img = $err = null;
        if ($this->db->DeleteTeachWantedPost(input_int('postId', 0), $userId, is_admin(), $img, $err)) {
            $this->deleteTeachImage($img);
            delete_upload($this->db->lastDeletedTeachVideo);
            json_response(['success' => true]);
        }
        json_response(['success' => false, 'message' => $err ?? 'Failed to delete post.']);
    }

    private function deleteTeachImage(?string $url): void
    {
        if ($url && str_starts_with($url, '/uploads/teach-posts/')) delete_upload('/uploads/teach-posts/' . basename($url));
    }

    /** POST /FindTutor/EditTeachComment (commentId, content) */
    public function EditTeachComment_POST(): void { feed_comment_action('teach', 'edit'); }

    /** POST /FindTutor/DeleteTeachComment (commentId) */
    public function DeleteTeachComment_POST(): void { feed_comment_action('teach', 'delete'); }

    /** POST /FindTutor/AddTeachComment */
    public function AddTeachComment_POST(): void
    {
        if (!feed_comments_on()) json_response(['success' => false, 'message' => 'এখানে কমেন্ট বন্ধ — শুধু লাইক দেওয়া যায়।']);
        if (session_get('UserName') === null) json_response(['success' => false, 'message' => 'Please login to comment.']);
        $content = input_str('content');
        if (is_blank($content)) json_response(['success' => false, 'message' => 'Comment cannot be empty.']);
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Invalid user.']);
        $content = trim($content);
        if (mb_strlen($content) > 2000) json_response(['success' => false, 'message' => 'Comment is too long.']);

        $postId = input_int('postId', 0);
        $parentId = input_int('parentId', null);
        $sender = session_get('UserFullName') ?? session_get('UserName') ?? 'Someone';

        $rootParentId = null;
        $repliedTo = null;
        if ($parentId !== null) {
            foreach ($this->db->GetTeachWantedComments($postId) as $top) {
                if ($top->Id === $parentId) { $repliedTo = $top; $rootParentId = $top->Id; break; }
                foreach ($top->Replies as $r) {
                    if ($r->Id === $parentId) { $repliedTo = $r; $rootParentId = $top->Id; break 2; }
                }
            }
            if (!$repliedTo) json_response(['success' => false, 'message' => 'The comment you are replying to no longer exists.']);
        }

        $comment = obj(['PostId' => $postId, 'UserId' => $userId, 'Content' => $content, 'ParentId' => $rootParentId]);
        $err = null;
        if ($this->db->AddTeachWantedComment($comment, $err)) {
            try {
                $post = $this->db->GetTeachWantedPostById($postId);
                if ($post) {
                    $preview = preg_replace('/^@\[[^\]]+\]\s*/u', '', $content);
                    $msg = "$sender commented: $preview";
                    if ($post->UserId !== $userId) {
                        $this->db->CreateNotification($post->UserId, '💬 New Comment', $msg, 'info', '/FindTutor', 'fa-comment');
                    }
                    if ($repliedTo && $repliedTo->UserId !== $userId && $repliedTo->UserId !== $post->UserId) {
                        $this->db->CreateNotification($repliedTo->UserId, '↩️ New Reply', $msg, 'info', '/FindTutor', 'fa-reply');
                    }
                }
            } catch (Throwable $e) {}

            json_response(['success' => true, 'comment' => [
                'id' => $comment->Id,
                'postId' => $postId,
                'parentId' => $rootParentId,
                'userFullName' => $sender,
                'userProfileImage' => session_get('ProfileImageLink') ?? '',
                'content' => $comment->Content,
                'createdAtUtc' => gmdate('Y-m-d\TH:i:s\Z'),
                'likeCount' => 0,
                'isLiked' => false,
            ]]);
        }
        json_response(['success' => false, 'message' => $err]);
    }

    /** POST /FindTutor/ToggleTeachLike */
    public function ToggleTeachLike_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login to like.']);
        [$liked, $count] = $this->db->ToggleTeachWantedLike(input_int('postId', 0), $userId);
        json_response(['success' => true, 'isLiked' => $liked, 'count' => $count]);
    }

    /** POST /FindTutor/ToggleTeachCommentLike */
    public function ToggleTeachCommentLike_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login to like.']);
        [$liked, $count] = $this->db->ToggleTeachCommentLike(input_int('commentId', 0), $userId);
        json_response(['success' => true, 'isLiked' => $liked, 'count' => $count]);
    }
}
