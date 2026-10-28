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

        try {
            $url = save_upload($file, 'profiles', $userId . '_' . ticks(), $ext);
            $user = $this->db->GetUserById($userId);
            if ($user) {
                $user->ProfileImageLink = $url;
                if ($this->db->UpdateUserProfile($user)) {
                    session_set('ProfileImageLink', $url);
                    json_response(['success' => true, 'message' => 'Profile image uploaded successfully!', 'imageUrl' => $url]);
                }
            }
            json_response(['success' => false, 'message' => 'Failed to update profile image']);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
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
