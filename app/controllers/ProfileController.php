<?php
class ProfileController extends Controller
{
    private const IMAGE_EXTS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

    private function requireUser(): Obj
    {
        if (session_get('UserName') === null) $this->redirectToAction('Login', 'Account');
        $id = current_user_id();
        $user = $id ? $this->db->GetUserById($id) : null;
        if (!$user) $this->redirectToAction('Login', 'Account');
        return $user;
    }

    public static function isProfileComplete(Obj $p): bool
    {
        foreach (['FullName', 'Email', 'MobileNumber', 'Gender', 'AgeRange', 'PrimaryDeviceType', 'Country', 'EducationLevel'] as $f) {
            if ((string)$p->$f === '') return false;
        }
        return true;
    }

    /** GET /Profile/Index?section= */
    public function Index(): void
    {
        $profile = $this->buildProfile();
        $this->view('Index', $profile, ['IsProfileComplete' => self::isProfileComplete($profile)]);
    }

    /**
     * GET /Profile/Show/{id}?tab=posts|about|friends — ফেসবুকের মতো পাবলিক প্রোফাইল (টাইমলাইন)।
     * id না দিলে নিজের প্রোফাইল। ফোন / ইমেইল / ঠিকানা শুধু ফ্রেন্ড (আর নিজে) দেখতে পারে।
     */
    public function Show(): void
    {
        $me = current_user_id();
        if ($me === null) redirect('/Account/Login?returnUrl=' . rawurlencode(strtok($_SERVER['REQUEST_URI'] ?? '/Profile/Show', '#')));
        $id = input_int('id', 0) ?: $me;
        $user = $this->db->GetUserById($id);
        if (!$user || $user->Role === 'Admin') not_found('এই প্রোফাইলটি পাওয়া যায়নি।');

        $isAdminViewer = current_role() === 'Admin';
        $isOwner = $id === $me;
        $relation = $this->db->GetFriendRelation($me, $id);
        $suspended = $this->db->GetActiveSuspension($id) !== null || !$user->IsActive;
        if ($suspended && !$isAdminViewer && !$isOwner) not_found('এই প্রোফাইলটি এখন দেখা যাচ্ছে না।');

        $teacherInfo = null;
        $teacherStatus = $user->Role === 'Teacher' ? 'Approved' : 'None';
        try {
            $teacherInfo = $this->db->one('SELECT `Education`, `Institution`, `SubjectExpertise`, `Experience`, `TeachingStyle`, `AvailableDays`,
                    `PreferredTime`, `HourlyRate`, `Status` FROM `TeacherRequests` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 1', ['u' => $id]);
            if ($user->Role !== 'Teacher' && $teacherInfo) $teacherStatus = (string)($teacherInfo->Status ?? 'Pending');
        } catch (Throwable $e) { $teacherInfo = null; }

        // ট্যাব (পুরোনো লিংক posts/about ও কাজ করে)
        $tab = (string)input_str('tab', 'overview');
        $tab = ['posts' => 'overview', 'about' => 'profile'][$tab] ?? $tab;
        $isTeacherProfile = $user->Role === 'Teacher';
        $allowed = ['overview', 'profile', 'friends', 'goals', 'activities'];
        if ($isOwner) $allowed = array_merge($allowed, ['tutors', 'saved', 'settings'], $isTeacherProfile ? [] : ['circulars']);
        if (!in_array($tab, $allowed, true)) $tab = 'overview';

        $posts = $this->db->GetProfilePosts($id, $me, $isAdminViewer, null, 10);
        $friends = $this->db->GetFriendsOfUserForViewer($id, $me);

        $this->view('Profile/Show', obj([
            'User' => $user, 'IsOwner' => $isOwner, 'Relation' => $relation, 'IsAdminViewer' => $isAdminViewer,
            'CanSeePrivate' => $isOwner || $isAdminViewer || $relation === 'friends',
            'FriendCount' => count($friends), 'Friends' => $friends,
            'MutualCount' => $isOwner ? 0 : $this->db->CountMutualFriends($me, $id),
            'TeacherInfo' => $user->Role === 'Teacher' || $isOwner ? $teacherInfo : null, 'TeacherStatus' => $teacherStatus,
            'Posts' => $posts, 'HasMore' => count($posts) === 10,
            'PostCount' => $this->db->CountProfilePosts($id, $me, $isAdminViewer),
            'Tab' => $tab, 'IsSuspended' => $suspended, 'Extras' => $this->db->GetProfileExtras($id),
            'Intro' => $this->db->GetIntro($id),
            'Streak' => $this->db->GetStreak($id),
            'ActivityDays' => $this->db->GetActivityDays($id, 119),
            'ActivityStats' => $this->db->GetActivityStats($id),
            'Goals' => $this->db->GetGoals($id),
            'Connected' => $isOwner ? $this->db->GetConnectedPeople($id, $isTeacherProfile) : [],
            'SavedItems' => $isOwner ? $this->db->GetSavedItems($id, $isAdminViewer) : [],
            'MyCirculars' => $isOwner && !$isTeacherProfile ? $this->db->GetMyCirculars($id) : [],
            'TutorSaved' => !$isOwner && $isTeacherProfile && !$isAdminViewer ? $this->db->IsSaved($me, 'tutor', $id) : false,
        ]));
    }

    /** POST /Profile/ChangePassword — নিজের পাসওয়ার্ড বদলানো (পুরোনো পাসওয়ার্ড লাগবে) */
    public function ChangePassword_POST(): void
    {
        $me = current_user_id();
        if ($me === null) json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
        $current = (string)input_str('currentPassword', '');
        $new = (string)input_str('newPassword', '');
        $confirm = (string)input_str('confirmPassword', '');
        if ($current === '' || $new === '') json_response(['success' => false, 'message' => 'সব ঘর পূরণ করুন।']);
        if (mb_strlen($new) < 6) json_response(['success' => false, 'message' => 'নতুন পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।']);
        if ($new !== $confirm) json_response(['success' => false, 'message' => 'নতুন পাসওয়ার্ড দুইবার একই হয়নি।']);

        // ভুল পাসওয়ার্ড বারবার চেষ্টা ঠেকাতে (১৫ মিনিটে ৫ বার)
        $tries = array_filter($_SESSION['_pw_tries'] ?? [], fn($t) => $t > time() - 900);
        if (count($tries) >= 5) json_response(['success' => false, 'message' => 'অনেকবার ভুল হয়েছে। ১৫ মিনিট পর আবার চেষ্টা করুন।']);

        $user = $this->db->GetUserById($me);
        $auth = $user ? $this->db->AuthenticateUser($user->UserName, $current) : null;
        if (!$auth || (int)$auth->Id !== $me) {
            $tries[] = time();
            $_SESSION['_pw_tries'] = array_values($tries);
            json_response(['success' => false, 'message' => 'বর্তমান পাসওয়ার্ড ভুল।']);
        }
        if ($current === $new) json_response(['success' => false, 'message' => 'নতুন পাসওয়ার্ড আগেরটার মতো হতে পারবে না।']);
        unset($_SESSION['_pw_tries']);
        if (!$this->db->UpdatePassword($me, $new)) json_response(['success' => false, 'message' => 'পাসওয়ার্ড বদলানো যায়নি। আবার চেষ্টা করুন।']);
        $this->db->CreateNotification($me, '🔑 Password Changed', 'আপনার অ্যাকাউন্টের পাসওয়ার্ড বদলানো হয়েছে। আপনি না করে থাকলে সাথে সাথে অ্যাডমিনকে জানান।', 'warning', null, 'fa-key');
        json_response(['success' => true, 'message' => 'পাসওয়ার্ড সফলভাবে বদলানো হয়েছে।']);
    }

    /** GET /Profile/Edit */
    public function Edit(): void
    {
        $profile = $this->buildProfile();
        $this->view('Edit', $profile, ['IsProfileComplete' => self::isProfileComplete($profile)]);
    }

    private function buildProfile(): Obj
    {
        $user = $this->requireUser();
        $s = fn($v) => $v ?? '';
        $profile = obj([
            'Id' => $user->Id, 'FullName' => $user->FullName, 'Email' => $user->Email, 'UserName' => $user->UserName,
            'Role' => $user->Role, 'MobileNumber' => $s($user->MobileNumber), 'ProfileImage' => $s($user->ProfileImage),
            'Gender' => $s($user->Gender), 'AgeRange' => $s($user->AgeRange), 'PrimaryDeviceType' => $s($user->PrimaryDeviceType),
            'YearsOfExperience' => $s($user->YearsOfExperience), 'AreaType' => $s($user->AreaType), 'Country' => $s($user->Country),
            'StreetAddress' => $s($user->StreetAddress), 'PermanentAddress' => $s($user->PermanentAddress),
            'EducationLevel' => $s($user->EducationLevel), 'CurrentStudyStatus' => $user->CurrentStudyStatus ?? 'No',
            'ExamDegreeTitle' => $s($user->ExamDegreeTitle), 'InstitutionName' => $s($user->InstitutionName),
            'PassingYear' => $s($user->PassingYear), 'IsCSEStudent' => (bool)($user->IsCSEStudent ?? false),
            'CvLink' => $s($user->CvLink), 'GithubProfile' => $s($user->GithubProfile), 'PortfolioLink' => $s($user->PortfolioLink),
            'LinkedInProfile' => $s($user->LinkedInProfile), 'ProfileImageLink' => $s($user->ProfileImageLink),
        ]);
        return $profile;
    }

    /** POST /Profile/Edit — Edit পেজ থেকে সব ফিল্ড একসাথে সেভ */
    public function Edit_POST(): void
    {
        $user = $this->requireUser();
        if (is_blank(input_str('FullName')) || is_blank(input_str('Email'))) {
            tempdata_set('Error', 'Full Name and Email are required.');
            $this->redirectToAction('Edit');
        }
        foreach (['FullName', 'Email', 'MobileNumber', 'Gender', 'AgeRange', 'PrimaryDeviceType', 'YearsOfExperience', 'AreaType',
                     'Country', 'StreetAddress', 'PermanentAddress', 'EducationLevel', 'CurrentStudyStatus', 'ExamDegreeTitle',
                     'InstitutionName', 'PassingYear', 'CvLink', 'GithubProfile', 'PortfolioLink', 'LinkedInProfile'] as $f) {
            if (has_input($f)) $user->$f = trim((string)input_str($f));
        }
        if (has_input('IsCSEStudent')) $user->IsCSEStudent = input_bool('IsCSEStudent');
        if ($this->db->UpdateUserProfile($user)) {
            session_set('UserFullName', $user->FullName);
            session_set('UserEmail', $user->Email);
            tempdata_set('Success', 'Profile updated successfully!');
        } else {
            tempdata_set('Error', 'Failed to update profile.');
        }
        $this->redirectToAction('Index');
    }

    /** POST /Profile/UpdateBasic */
    public function UpdateBasic_POST(): void
    {
        $user = $this->requireUser();
        $fullName = input_str('FullName');
        $email = input_str('Email');
        if (is_blank($fullName)) { tempdata_set('Error', 'Full Name is required.'); $this->redirectToAction('Index', null, ['section' => 'basic']); }
        if (is_blank($email)) { tempdata_set('Error', 'Email is required.'); $this->redirectToAction('Index', null, ['section' => 'basic']); }

        $user->FullName = trim($fullName);
        $user->Email = trim($email);
        $user->MobileNumber = input_str('MobileNumber');
        if ($this->db->UpdateUserProfile($user)) {
            session_set('UserFullName', $user->FullName);
            session_set('UserEmail', $user->Email);
            tempdata_set('Success', 'Basic information updated successfully!');
        } else {
            tempdata_set('Error', 'Failed to update profile.');
        }
        $this->redirectToAction('Index', null, ['section' => 'basic']);
    }

    /** POST /Profile/UpdateAdditional */
    public function UpdateAdditional_POST(): void
    {
        $user = $this->requireUser();
        foreach (['Gender', 'AgeRange', 'PrimaryDeviceType', 'YearsOfExperience', 'AreaType'] as $f) $user->$f = input_str($f);
        if ($this->db->UpdateUserProfile($user)) tempdata_set('Success', 'Additional information updated successfully!');
        $this->redirectToAction('Index', null, ['section' => 'additional']);
    }

    /** POST /Profile/UpdateAddress */
    public function UpdateAddress_POST(): void
    {
        $user = $this->requireUser();
        foreach (['Country', 'StreetAddress', 'PermanentAddress'] as $f) $user->$f = input_str($f);
        if ($this->db->UpdateUserProfile($user)) tempdata_set('Success', 'Address updated successfully!');
        $this->redirectToAction('Index', null, ['section' => 'address']);
    }

    /** POST /Profile/UpdateEducation */
    public function UpdateEducation_POST(): void
    {
        $user = $this->requireUser();
        foreach (['EducationLevel', 'CurrentStudyStatus', 'ExamDegreeTitle', 'InstitutionName', 'PassingYear'] as $f) $user->$f = input_str($f);
        $user->IsCSEStudent = input_bool('IsCSEStudent');
        if ($this->db->UpdateUserProfile($user)) tempdata_set('Success', 'Education information updated successfully!');
        $this->redirectToAction('Index', null, ['section' => 'education']);
    }

    /** POST /Profile/UpdateLinks */
    public function UpdateLinks_POST(): void
    {
        $user = $this->requireUser();
        foreach (['CvLink', 'GithubProfile', 'PortfolioLink', 'LinkedInProfile'] as $f) $user->$f = input_str($f);
        if ($this->db->UpdateUserProfile($user)) tempdata_set('Success', 'Important links updated successfully!');
        $this->redirectToAction('Index', null, ['section' => 'links']);
    }

    /** POST /Profile/UploadProfileImage */
    public function UploadProfileImage_POST(): void
    {
        if (session_get('UserName') === null) json_response(['success' => false, 'message' => 'Unauthorized']);
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'User not found']);

        $file = upload_file('profileImage');
        if (!$file) json_response(['success' => false, 'message' => 'Please select an image']);

        $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::IMAGE_EXTS, true)) json_response(['success' => false, 'message' => 'Only JPG, PNG, GIF, or WEBP images are allowed.']);
        if ($file['size'] > 5 * 1024 * 1024) json_response(['success' => false, 'message' => 'Image size must be less than 5MB.']);
        if (@getimagesize($file['tmp_name']) === false) json_response(['success' => false, 'message' => 'ফাইলটি সঠিক ছবি নয়।']);

        try {
            $url = save_upload($file, 'profiles', $userId . '_' . ticks(), $ext);
            $user = $this->db->GetUserById($userId);
            if ($user) {
                $old = (string)$user->ProfileImageLink;
                $user->ProfileImageLink = $url;
                if ($this->db->UpdateUserProfile($user)) {
                    if (str_starts_with($old, '/uploads/profiles/')) delete_upload($old);
                    session_set('ProfileImageLink', $url);
                    json_response(['success' => true, 'message' => 'Profile image uploaded successfully!', 'imageUrl' => $url]);
                }
            }
            json_response(['success' => false, 'message' => 'Failed to update profile image']);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /Profile/SaveCover (cover = ছবি [ঐচ্ছিক], posY = 0–100)
     * ছবি দিলে নতুন কভার, না দিলে শুধু পজিশন (Reposition) সেভ হয়।
     */
    public function SaveCover_POST(): void
    {
        $me = current_user_id();
        if ($me === null || current_role() === 'Admin') json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
        $posY = max(0, min(100, input_int('posY', 50) ?? 50));
        $extras = $this->db->GetProfileExtras($me);
        $file = upload_file('cover');
        $url = null;
        if ($file) {
            $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['.jpg', '.jpeg', '.png', '.webp'], true)) json_response(['success' => false, 'message' => 'শুধু JPG, PNG বা WEBP ছবি দেওয়া যাবে।']);
            if ($file['size'] > 8 * 1024 * 1024) json_response(['success' => false, 'message' => 'কভার ছবি ৮ MB এর কম হতে হবে।']);
            $info = @getimagesize($file['tmp_name']);
            if ($info === false) json_response(['success' => false, 'message' => 'ফাইলটি সঠিক ছবি নয়।']);
            if ($info[0] < 400) json_response(['success' => false, 'message' => 'ছবিটা খুব ছোট — অন্তত ৪০০ পিক্সেল চওড়া ছবি দিন।']);
            try { $url = save_upload($file, 'covers', $me . '_' . ticks(), $ext); }
            catch (Throwable $e) { json_response(['success' => false, 'message' => 'ছবি সেভ করা যায়নি।']); }
        } elseif (!$extras->CoverImageUrl) {
            json_response(['success' => false, 'message' => 'একটি ছবি বেছে নিন।']);
        }
        $this->db->SaveCover($me, $url, $posY);
        if ($url && $extras->CoverImageUrl) delete_upload($extras->CoverImageUrl);
        json_response(['success' => true, 'message' => $url ? 'কভার ফটো আপডেট হয়েছে।' : 'কভার ফটোর পজিশন সেভ হয়েছে।',
            'url' => $url ?? $extras->CoverImageUrl, 'posY' => $posY]);
    }

    /** POST /Profile/SaveIntro (bio, city, subjects = কমা দিয়ে, quote) */
    public function SaveIntro_POST(): void
    {
        $me = current_user_id();
        if ($me === null || current_role() === 'Admin') json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
        $clean = fn($k, $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', (string)input_str($k, ''))), 0, $max);
        $bio = $clean('bio', 160); $city = $clean('city', 80); $quote = $clean('quote', 160);
        $subjects = [];
        foreach (explode(',', (string)input_str('subjects', '')) as $sj) {
            $sj = mb_substr(trim(preg_replace('/\s+/u', ' ', $sj)), 0, 30);
            if ($sj !== '' && !in_array(mb_strtolower($sj), array_map('mb_strtolower', $subjects), true)) $subjects[] = $sj;
        }
        if (count($subjects) > 10) json_response(['success' => false, 'message' => 'সর্বোচ্চ ১০টা বিষয় দেওয়া যায়।']);
        $this->db->SaveIntro($me, $bio, $city, $subjects, $quote);
        json_response(['success' => true, 'message' => 'আপডেট হয়েছে।', 'bio' => $bio, 'city' => $city, 'subjects' => $subjects, 'quote' => $quote]);
    }

    /** POST /Profile/RemoveCover */
    public function RemoveCover_POST(): void
    {
        $me = current_user_id();
        if ($me === null) json_response(['success' => false, 'message' => 'আগে লগইন করুন।']);
        $extras = $this->db->GetProfileExtras($me);
        $this->db->SaveCover($me, '', 50);
        delete_upload($extras->CoverImageUrl);
        json_response(['success' => true, 'message' => 'কভার ফটো সরানো হয়েছে।']);
    }

    /** GET /Profile/GetProfileData */
    public function GetProfileData(): void
    {
        if (session_get('UserName') === null) json_response(['success' => false, 'message' => 'Unauthorized']);
        $userId = current_user_id();
        $user = $userId ? $this->db->GetUserById($userId) : null;
        if (!$user) json_response(['success' => false, 'message' => 'User not found']);

        $keys = ['Id', 'UserName', 'FullName', 'Email', 'MobileNumber', 'Gender', 'AgeRange', 'PrimaryDeviceType', 'YearsOfExperience',
            'AreaType', 'Country', 'StreetAddress', 'PermanentAddress', 'EducationLevel', 'CurrentStudyStatus', 'ExamDegreeTitle',
            'InstitutionName', 'PassingYear', 'IsCSEStudent', 'CvLink', 'GithubProfile', 'PortfolioLink', 'LinkedInProfile',
            'ProfileImageLink', 'Role'];
        $out = [];
        foreach ($keys as $k) $out[$k] = $user->$k;
        json_camel(['success' => true, 'user' => $out]);
    }

    /** GET /Profile/GetTeacherRequestStatus */
    public function GetTeacherRequestStatus(): void
    {
        if (session_get('UserName') === null) json_response(['success' => false, 'isTeacher' => false, 'status' => 'None']);
        $userId = current_user_id() ?? 0;
        if ($userId === 0) json_response(['success' => false, 'isTeacher' => false, 'status' => 'None']);
        $user = $this->db->GetUserById($userId);
        if (!$user) json_response(['success' => false, 'isTeacher' => false, 'status' => 'None']);
        if ($user->Role === 'Teacher') json_response(['success' => true, 'isTeacher' => true, 'status' => 'Approved']);
        json_response(['success' => true, 'isTeacher' => false, 'status' => $this->db->GetUserTeacherRequestStatus($userId)]);
    }
}
