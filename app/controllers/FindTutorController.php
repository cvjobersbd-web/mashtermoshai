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
        if (!$user) json_response(['success' => false, 'message' => 'User not found']);
        $keys = ['Id', 'FullName', 'Email', 'MobileNumber', 'Gender', 'AgeRange', 'EducationLevel', 'ExamDegreeTitle',
            'InstitutionName', 'PassingYear', 'Country', 'StreetAddress', 'PermanentAddress', 'YearsOfExperience', 'CvLink',
            'GithubProfile', 'PortfolioLink', 'LinkedInProfile', 'ProfileImageLink'];
        $out = [];
        foreach ($keys as $k) $out[$k] = $user->$k;
        $me = current_user_id();
        $out['IsSaved'] = $me !== null && $user->Role === 'Teacher' ? $this->db->IsSaved($me, 'tutor', (int)$user->Id) : false;
        $out['CanSave'] = $me !== null && current_role() !== 'Admin' && $user->Role === 'Teacher' && $me !== (int)$user->Id;
        json_camel(['success' => true, 'user' => $out]);
    }

    /** POST /FindTutor/SubmitTutorRequest */
    public function SubmitTutorRequest_POST(): void
    {
        try {
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
                // ===== সব অ্যাডমিনকে নোটিফাই =====
                try {
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
                try {
                    if (!is_blank($req->TutorId) && str_starts_with($req->TutorId, 'TCH-')) {
                        $parts = explode('-', $req->TutorId);
                        if (count($parts) === 2 && ctype_digit($parts[1])) {
                            $tr = $this->db->GetTeacherRequestById((int)$parts[1] - 1000);
                            if ($tr && $this->db->GetUserById($tr->UserId)) {
                                $this->db->CreateNotification($tr->UserId, '📩 New Tutor Request!',
                                    "{$req->StudentName} ({$req->PhoneNumber}) wants to contact you for {$req->RequiredSubject} tuition.",
                                    'info', '/Teacher/TeacherDashboard?section=direct-requests', 'fa-envelope-open-text');
                            }
                        }
                    }
                } catch (Throwable $e) {}

                json_response([
                    'success' => true,
                    'message' => 'Request submitted successfully! Our admin team will contact you soon.',
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
        if ($content === '' && !$image && !$video) json_response(['success' => false, 'message' => 'কিছু লিখুন অথবা ছবি/ভিডিও দিন।']);
        if (mb_strlen($content) > 3000) json_response(['success' => false, 'message' => 'পোস্ট সর্বোচ্চ ৩০০০ অক্ষর।']);
        if ($image && $video) json_response(['success' => false, 'message' => 'একটা ছবি অথবা একটা ভিডিও দিন — দুটো একসাথে নয়।']);
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Invalid user.']);

        $imageUrl = $image ? $this->saveTeachImage($image) : null;
        $videoUrl = $video ? $this->saveTeachVideo($video) : null;
        $post = obj(['UserId' => $userId, 'Content' => $content, 'ImageUrl' => $imageUrl, 'VideoUrl' => $videoUrl]);
        $err = null;
        if ($this->db->CreateTeachWantedPost($post, $err)) json_response(['success' => true, 'id' => $post->Id]);
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
        $image = upload_file('imageFile');
        $imageChanged = false;
        $newImageUrl = null;
        if ($image) { $newImageUrl = $this->saveTeachImage($image); $imageChanged = true; }
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

    /** POST /FindTutor/AddTeachComment */
    public function AddTeachComment_POST(): void
    {
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
