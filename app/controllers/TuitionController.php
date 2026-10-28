<?php
class TuitionController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

    /** GET /Tuition/Circular?search=&area= */
    public function Circular(): void
    {
        $search = input_str('search');
        $area = input_str('area');
        try {
            $posts = $this->db->GetOpenTuitionPosts($search, $area);
        } catch (Throwable $e) {
            tempdata_set('Error', 'Unable to load tuition posts. Please try again.');
            $posts = [];
        }
        $this->view(null, $posts, ['SearchQuery' => $search, 'SelectedArea' => $area ?? 'all']);
    }

    /** POST /Tuition/CreateCircularPost */
    public function CreateCircularPost_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login to post.']);
        $content = input_str('content');
        $image = upload_file('imageFile');
        if (is_blank($content) && !$image) json_response(['success' => false, 'message' => 'Content or image is required.']);

        $imageUrl = null;
        if ($image) {
            $ext = strtolower('.' . pathinfo($image['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::IMAGE_EXTS, true) || $image['size'] > 5 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'Invalid image or size over 5MB.']);
            }
            $imageUrl = save_upload($image, 'tuition-posts', 'post_' . ticks(), $ext);
        }
        $t = fn($n) => input_str($n) === null ? null : trim(input_str($n));
        $post = obj([
            'UserId' => $userId, 'Content' => trim((string)$content), 'ImageUrl' => $imageUrl,
            'PreferredArea' => $t('preferredArea'), 'ClassLevel' => $t('classLevel'), 'Subjects' => $t('subjects'), 'Budget' => $t('budget'),
        ]);
        $err = null;
        if ($this->db->CreateTuitionCircularPost($post, $err)) json_response(['success' => true]);
        json_response(['success' => false, 'message' => $err]);
    }

    /** POST /Tuition/AddCircularComment */
    public function AddCircularComment_POST(): void
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
            foreach ($this->db->GetTuitionCircularComments($postId) as $top) {
                if ($top->Id === $parentId) { $repliedTo = $top; $rootParentId = $top->Id; break; }
                foreach ($top->Replies as $r) {
                    if ($r->Id === $parentId) { $repliedTo = $r; $rootParentId = $top->Id; break 2; }
                }
            }
            if (!$repliedTo) json_response(['success' => false, 'message' => 'The comment you are replying to no longer exists.']);
        }

        $comment = obj(['PostId' => $postId, 'UserId' => $userId, 'Content' => $content, 'ParentId' => $rootParentId]);
        $err = null;
        if ($this->db->AddTuitionCircularComment($comment, $err)) {
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

    /** POST /Tuition/ToggleCircularLike */
    public function ToggleCircularLike_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login to like.']);
        [$liked, $count] = $this->db->ToggleTuitionCircularLike(input_int('postId', 0), $userId);
        json_response(['success' => true, 'isLiked' => $liked, 'count' => $count]);
    }

    /** POST /Tuition/ToggleCircularCommentLike */
    public function ToggleCircularCommentLike_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Please login to like.']);
        [$liked, $count] = $this->db->ToggleTuitionCircularCommentLike(input_int('commentId', 0), $userId);
        json_response(['success' => true, 'isLiked' => $liked, 'count' => $count]);
    }

    /**
     * POST /Tuition/ApplyCircular
     * টিচার স্টুডেন্টের সার্কুলার পোস্টে Apply করে — অ্যাপ্লিকেশন সরাসরি
     * পোস্টদাতার Student Dashboard এ যায় (অ্যাডমিনের কাছে নয়)।
     */
    public function ApplyCircular_POST(): void
    {
        $teacherId = current_user_id();
        if ($teacherId === null) json_response(['success' => false, 'message' => 'Apply করতে লগইন করুন।']);
        if (current_role() !== 'Teacher') json_response(['success' => false, 'message' => 'শুধু অনুমোদিত টিচাররা Apply করতে পারবেন।']);

        $postId = input_int('postId', 0);
        $message = trim((string)input_str('message', ''));
        $phone = preg_replace('/\D/', '', (string)input_str('phone', ''));
        if (mb_strlen($message) > 1000) json_response(['success' => false, 'message' => 'মেসেজ সর্বোচ্চ ১০০০ অক্ষর হতে পারবে।']);
        if (!preg_match('/^01[3-9]\d{8}$/', $phone)) json_response(['success' => false, 'message' => 'সঠিক মোবাইল নম্বর দিন (যেমন 01712345678)।']);

        $post = null;
        $result = $this->db->ApplyToCircularPost($postId, $teacherId, $message === '' ? null : $message, $phone, $post);
        switch ($result) {
            case 'ok':
                $teacherName = session_get('UserFullName') ?? session_get('UserName') ?? 'A teacher';
                $this->db->CreateNotification($post->UserId, 'New Teacher Application',
                    "$teacherName আপনার টিউশন পোস্টে Apply করেছেন।", 'success',
                    '/User/Dashboard?section=applications', 'fa-chalkboard-teacher');
                json_response(['success' => true, 'message' => 'আপনার অ্যাপ্লিকেশন স্টুডেন্টের কাছে পাঠানো হয়েছে।']);
            case 'exists':
                json_response(['success' => false, 'message' => 'আপনি এই পোস্টে আগেই Apply করেছেন।']);
            case 'own':
                json_response(['success' => false, 'message' => 'নিজের পোস্টে Apply করা যাবে না।']);
            case 'notfound':
                json_response(['success' => false, 'message' => 'পোস্টটি পাওয়া যায়নি।']);
        }
        json_response(['success' => false, 'message' => 'Apply করা যায়নি। আবার চেষ্টা করুন।']);
    }

    /** GET /Tuition/Details/{id} */
    public function Details(): void
    {
        $post = $this->db->GetTuitionPostById(input_int('id', 0), true);
        if (!$post) {
            tempdata_set('Error', 'Tuition post not found.');
            $this->redirectToAction('Circular');
        }
        $this->view(null, $post);
    }

    /** GET /Tuition/Apply/{id} */
    public function Apply(): void
    {
        $id = input_int('id', 0);
        if (session_get('UserName') === null) {
            tempdata_set('Error', 'Please login to apply for this tuition.');
            $this->redirectToAction('Login', 'Account', ['returnUrl' => "/Tuition/Apply/$id"]);
        }
        $post = $this->db->GetTuitionPostById($id);
        if (!$post || $post->Status !== 'Open') {
            tempdata_set('Error', 'This tuition is no longer available.');
            $this->redirectToAction('Circular');
        }
        $model = $this->emptyApplication($id);
        $userId = current_user_id();
        if ($userId !== null && ($user = $this->db->GetUserById($userId))) {
            $model->UserId = $userId;
            $model->ApplicantName = $user->FullName ?? '';
            $model->PhoneNumber = $user->MobileNumber ?? '';
            $model->WhatsAppNumber = $user->MobileNumber ?? '';
            $model->Email = $user->Email ?? '';
        }
        $this->view(null, $model, ['Post' => $post]);
    }

    private function emptyApplication(int $postId): Obj
    {
        return obj([
            'Id' => 0, 'TuitionPostId' => $postId, 'UserId' => null, 'ApplicantName' => '', 'PhoneNumber' => '',
            'WhatsAppNumber' => null, 'Email' => null, 'CvLink' => '', 'CoverNote' => null, 'Status' => 'Pending',
            'AdminNote' => null, 'AppliedAt' => now(), 'UpdatedAt' => null, 'TuitionPost' => null,
        ]);
    }

    /** POST /Tuition/Apply */
    public function Apply_POST(): void
    {
        if (session_get('UserName') === null) {
            tempdata_set('Error', 'Please login to submit your application.');
            $this->redirectToAction('Login', 'Account');
        }

        $model = $this->emptyApplication(input_int('TuitionPostId', 0) ?? 0);
        foreach (['ApplicantName', 'PhoneNumber', 'WhatsAppNumber', 'Email', 'CvLink', 'CoverNote'] as $f) {
            $v = input_str($f);
            $model->$f = ($v === null || trim($v) === '') ? (in_array($f, ['ApplicantName', 'PhoneNumber', 'CvLink'], true) ? '' : null) : $v;
        }

        try {
            // ===== মডেল ভ্যালিডেশন (DataAnnotations এর মতো) =====
            $errors = [];
            if (trim($model->ApplicantName) === '') $errors['ApplicantName'] = 'Name is required';
            if (trim($model->PhoneNumber) === '') $errors['PhoneNumber'] = 'Phone number is required';
            if (trim($model->CvLink) === '') $errors['CvLink'] = 'CV link is required';
            if ($model->Email !== null && !filter_var(trim($model->Email), FILTER_VALIDATE_EMAIL)) $errors['Email'] = 'The Email field is not a valid e-mail address.';
            if ($errors) {
                tempdata_set('Error', 'Validation Failed: ' . implode(' | ', $errors));
                $GLOBALS['__ModelErrors'] = $errors;
                $this->view(null, $model, ['Post' => $this->db->GetTuitionPostById($model->TuitionPostId)]);
            }

            $post = $this->db->GetTuitionPostById($model->TuitionPostId);
            if (!$post || $post->Status !== 'Open') {
                tempdata_set('Error', 'This tuition is no longer available.');
                $this->redirectToAction('Circular');
            }

            // ===== Manual validation =====
            $re = '/^01[3-9]\d{8}$/';
            if (!preg_match($re, trim($model->PhoneNumber))) $errors['PhoneNumber'] = 'Please enter a valid Bangladeshi phone number (e.g., 01712345678).';
            if (!is_blank($model->WhatsAppNumber) && !preg_match($re, trim($model->WhatsAppNumber))) $errors['WhatsAppNumber'] = 'Please enter a valid WhatsApp number (e.g., 01712345678).';
            $cv = trim($model->CvLink);
            if (!str_starts_with($cv, 'http://') && !str_starts_with($cv, 'https://')) $errors['CvLink'] = 'Please enter a valid URL starting with http:// or https://';
            if ($errors) {
                tempdata_set('Error', 'Please fix these errors: ' . implode(' | ', $errors));
                $GLOBALS['__ModelErrors'] = $errors;
                $this->view(null, $model, ['Post' => $post]);
            }

            // ===== Duplicate check =====
            $userId = current_user_id();
            if ($userId !== null && $this->db->HasUserApplied($model->TuitionPostId, $userId)) {
                tempdata_set('Error', 'You have already applied for this tuition.');
                $this->redirectToAction('Details', null, ['id' => $model->TuitionPostId]);
            }

            $model->UserId = $userId;
            $model->PhoneNumber = trim($model->PhoneNumber);
            $model->WhatsAppNumber = is_blank($model->WhatsAppNumber) ? $model->PhoneNumber : trim($model->WhatsAppNumber);
            $model->CvLink = $cv;
            $model->ApplicantName = trim($model->ApplicantName);
            $model->Email = is_blank($model->Email) ? null : trim($model->Email);
            $model->CoverNote = is_blank($model->CoverNote) ? null : trim($model->CoverNote);
            $model->Status = 'Pending';
            $model->Id = $this->db->CreateTuitionApplication($model);

            try {
                foreach ($this->db->GetAdmins() as $admin) {
                    $this->db->CreateNotification((int)$admin->Id, '📩 New Tuition Application',
                        "{$model->ApplicantName} ({$model->PhoneNumber}) applied for: {$post->Title}",
                        'info', '/Admin/TuitionApplications', 'fa-user-check');
                }
            } catch (Throwable $e) {}

            tempdata_set('Success', 'Application submitted successfully! Admin will contact you soon.');
            $this->redirectToAction('ApplySuccess', null, ['id' => $model->Id]);
        } catch (Throwable $e) {
            tempdata_set('Error', 'Error: ' . $e->getMessage());
            $this->view(null, $model, ['Post' => $this->db->GetTuitionPostById($model->TuitionPostId)]);
        }
    }

    /** GET /Tuition/ApplySuccess/{id} */
    public function ApplySuccess(): void
    {
        $app = $this->db->GetTuitionApplicationById(input_int('id', 0));
        if (!$app) {
            tempdata_set('Error', 'Application not found.');
            $this->redirectToAction('Circular');
        }
        $this->view(null, $app);
    }
}
