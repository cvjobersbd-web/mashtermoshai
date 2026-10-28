<?php
/**
 * =============================================================
 *  Db — C# DatabaseHelper.cs এর PHP (PDO/MySQL) ভার্সন
 *  মেথডের নাম C# এর মতোই রাখা হয়েছে।
 *  প্রতিটি row একটি Obj (stdClass) হিসেবে ফেরত আসে, যার
 *  property নাম ডাটাবেস কলামের নামের মতো (PascalCase)।
 * =============================================================
 */
class Db
{
    private PDO $pdo;

    public const USER_COLUMNS = '`Id`, `UserName`, `Password`, `FullName`, `Email`, `CreatedAt`, `LastLoginAt`, `Role`, `IsActive`,
        `MobileNumber`, `ProfileImage`, `Gender`, `AgeRange`, `PrimaryDeviceType`, `YearsOfExperience`, `AreaType`,
        `Country`, `StreetAddress`, `PermanentAddress`, `EducationLevel`, `CurrentStudyStatus`, `ExamDegreeTitle`,
        `InstitutionName`, `PassingYear`, `IsCSEStudent`, `CvLink`, `GithubProfile`, `PortfolioLink`, `LinkedInProfile`, `ProfileImageLink`';

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function pdo(): PDO { return $this->pdo; }

    // ------------------------------------------------------------
    // Low-level helpers
    // ------------------------------------------------------------
    public function query(string $sql, array $params = []): PDOStatement
    {
        $st = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $name = is_int($k) ? $k + 1 : (str_starts_with($k, ':') ? $k : ':' . $k);
            if (is_bool($v)) $st->bindValue($name, $v ? 1 : 0, PDO::PARAM_INT);
            elseif (is_int($v)) $st->bindValue($name, $v, PDO::PARAM_INT);
            elseif ($v === null) $st->bindValue($name, null, PDO::PARAM_NULL);
            else $st->bindValue($name, (string)$v, PDO::PARAM_STR);
        }
        $st->execute();
        return $st;
    }

    /** @return Obj[] */
    public function all(string $sql, array $params = []): array
    {
        return array_map(fn($r) => new Obj($r), $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC));
    }

    public function one(string $sql, array $params = []): ?Obj
    {
        $r = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $r ? new Obj($r) : null;
    }

    public function scalar(string $sql, array $params = [])
    {
        $v = $this->query($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    public function exec(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return (int)$this->pdo->lastInsertId();
    }

    // ------------------------------------------------------------
    // Mappers (computed property গুলো যোগ করা)
    // ------------------------------------------------------------
    private function mapUser(?Obj $u): ?Obj
    {
        if (!$u) return null;
        $u->Id = (int)$u->Id;
        $u->Role = $u->Role ?? 'User';
        $u->IsActive = $u->IsActive === null ? true : (bool)$u->IsActive;
        $u->IsCSEStudent = $u->IsCSEStudent === null ? null : (bool)$u->IsCSEStudent;
        foreach (['UserName', 'FullName', 'Email', 'Password'] as $f) $u->$f = $u->$f ?? '';
        return $u;
    }

    private function mapTeacherRequest(Obj $r): Obj
    {
        $r->Id = (int)$r->Id;
        $r->UserId = (int)$r->UserId;
        foreach (['FullName', 'Email', 'MobileNumber', 'Education', 'Institution', 'SubjectExpertise', 'Experience',
                     'TeachingStyle', 'AvailableDays', 'PreferredTime', 'HourlyRate', 'CvLink', 'WhyTeach'] as $f) {
            $r->$f = $r->$f ?? '';
        }
        $r->Status = $r->Status ?? 'Pending';
        return $r;
    }

    public static function mapTutorRequest(Obj $r): Obj
    {
        $r->Id = (int)$r->Id;
        $r->StudentName = $r->StudentName ?? '';
        $r->RequestType = $r->RequestType ?? 'SpecificTutor';
        $r->Status = $r->Status ?? 'Pending';
        $r->WhatsAppLink = whatsapp_link($r->WhatsAppNumber, $r->PhoneNumber);
        $r->RequestTypeLabel = match ($r->RequestType) {
            'SpecificTutor' => 'Specific Tutor',
            'NeedTutor' => 'Need a Tutor',
            'AreaTuition' => 'Area Tuition',
            default => $r->RequestType,
        };
        return $r;
    }

    private function mapNotice(Obj $n): Obj
    {
        $n->Id = (int)$n->Id;
        $n->Title = $n->Title ?? '';
        $n->Content = $n->Content ?? '';
        $n->Category = $n->Category ?? 'News';
        $n->ImageUrl = $n->ImageUrl ?? '';
        $n->IsAnnouncement = (bool)$n->IsAnnouncement;
        $n->IsActive = $n->IsActive === null ? true : (bool)$n->IsActive;
        return $n;
    }

    public static function mapTuitionPost(Obj $p): Obj
    {
        $p->Id = (int)$p->Id;
        $p->Title = $p->Title ?? '';
        $p->Status = $p->Status ?? 'Open';
        $p->ShowOnHome = (bool)$p->ShowOnHome;
        if (!isset($p->Applications)) $p->Applications = [];
        return $p;
    }

    public static function mapTuitionApplication(Obj $a): Obj
    {
        $a->Id = (int)$a->Id;
        $a->TuitionPostId = (int)$a->TuitionPostId;
        $a->UserId = $a->UserId === null ? null : (int)$a->UserId;
        $a->ApplicantName = $a->ApplicantName ?? '';
        $a->PhoneNumber = $a->PhoneNumber ?? '';
        $a->CvLink = $a->CvLink ?? '';
        $a->Status = $a->Status ?? 'Pending';
        $a->WhatsAppLink = whatsapp_link($a->WhatsAppNumber, $a->PhoneNumber);
        $a->StatusLabel = $a->Status;
        if (!isset($a->TuitionPost)) $a->TuitionPost = null;
        return $a;
    }

    private function mapNotification(Obj $n): Obj
    {
        $n->Id = (int)$n->Id;
        $n->UserId = (int)$n->UserId;
        $n->Title = $n->Title ?? '';
        $n->Message = $n->Message ?? '';
        $n->Type = $n->Type ?? 'info';
        $n->Icon = $n->Icon ?? 'fa-bell';
        $n->IsRead = (bool)$n->IsRead;
        return $n;
    }

    // ============================================================
    // ===== USER RELATED METHODS =====
    // ============================================================

    /** @return Obj[] */
    public function GetAllUsers(): array
    {
        return array_map(fn($u) => $this->mapUser($u), $this->all('SELECT ' . self::USER_COLUMNS . ' FROM `Users` ORDER BY `Id`'));
    }

    /**
     * লগইন যাচাই। পুরোনো (plain text) পাসওয়ার্ড এবং নতুন (hashed) — দুটোই সাপোর্ট করে।
     * plain text মিললে স্বয়ংক্রিয়ভাবে hash করে সেভ করে দেয়।
     */
    public function AuthenticateUser(string $username, string $password): ?Obj
    {
        $u = $this->one('SELECT ' . self::USER_COLUMNS . ' FROM `Users` WHERE LOWER(`UserName`) = LOWER(:u) AND `IsActive` = 1', ['u' => $username]);
        if (!$u) return null;
        $stored = (string)$u->Password;
        $ok = false;
        $info = password_get_info($stored);
        if (!empty($info['algo'])) {
            $ok = password_verify($password, $stored);
        } elseif ($stored !== '' && hash_equals($stored, $password)) {
            $ok = true;
            // পুরোনো plain text পাসওয়ার্ড → hash এ রূপান্তর
            try { $this->exec('UPDATE `Users` SET `Password` = :p WHERE `Id` = :id', ['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => (int)$u->Id]); } catch (Throwable $e) {}
        }
        return $ok ? $this->mapUser($u) : null;
    }

    public function UpdateUserProfile(Obj $user): bool
    {
        $sql = 'UPDATE `Users` SET
                `FullName` = :fullName, `Email` = :email, `MobileNumber` = :mobileNumber, `Gender` = :gender,
                `AgeRange` = :ageRange, `PrimaryDeviceType` = :primaryDeviceType, `YearsOfExperience` = :yearsOfExperience,
                `AreaType` = :areaType, `Country` = :country, `StreetAddress` = :streetAddress, `PermanentAddress` = :permanentAddress,
                `EducationLevel` = :educationLevel, `CurrentStudyStatus` = :currentStudyStatus, `ExamDegreeTitle` = :examDegreeTitle,
                `InstitutionName` = :institutionName, `PassingYear` = :passingYear, `IsCSEStudent` = :isCSEStudent,
                `CvLink` = :cvLink, `GithubProfile` = :githubProfile, `PortfolioLink` = :portfolioLink,
                `LinkedInProfile` = :linkedInProfile, `ProfileImageLink` = :profileImageLink
                WHERE `Id` = :id';
        $this->query($sql, [
            'id' => (int)$user->Id,
            'fullName' => $user->FullName, 'email' => $user->Email, 'mobileNumber' => $user->MobileNumber,
            'gender' => $user->Gender, 'ageRange' => $user->AgeRange, 'primaryDeviceType' => $user->PrimaryDeviceType,
            'yearsOfExperience' => $user->YearsOfExperience, 'areaType' => $user->AreaType, 'country' => $user->Country,
            'streetAddress' => $user->StreetAddress, 'permanentAddress' => $user->PermanentAddress,
            'educationLevel' => $user->EducationLevel, 'currentStudyStatus' => $user->CurrentStudyStatus,
            'examDegreeTitle' => $user->ExamDegreeTitle, 'institutionName' => $user->InstitutionName,
            'passingYear' => $user->PassingYear,
            'isCSEStudent' => $user->IsCSEStudent === null ? null : (bool)$user->IsCSEStudent,
            'cvLink' => $user->CvLink, 'githubProfile' => $user->GithubProfile, 'portfolioLink' => $user->PortfolioLink,
            'linkedInProfile' => $user->LinkedInProfile, 'profileImageLink' => $user->ProfileImageLink,
        ]);
        // MySQL: কোনো মান না বদলালে rowCount 0 হয় — তাই ইউজার আছে কিনা দেখেই true দেওয়া হচ্ছে
        return (bool)$this->scalar('SELECT COUNT(*) FROM `Users` WHERE `Id` = :id', ['id' => (int)$user->Id]);
    }

    public function GetUserById(int $userId): ?Obj
    {
        return $this->mapUser($this->one('SELECT ' . self::USER_COLUMNS . ' FROM `Users` WHERE `Id` = :id', ['id' => $userId]));
    }

    public function RegisterUser(Obj $user): bool
    {
        $email = trim((string)$user->Email);
        if ($email === '' || !str_ends_with($email, '@gmail.com')) throw new Exception('Email must end with @gmail.com (all lowercase).');
        if (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/', $email)) throw new Exception('Invalid Gmail address format.');
        $this->query("INSERT INTO `Users` (`UserName`, `Password`, `FullName`, `Email`, `Role`, `IsActive`, `CreatedAt`)
                      VALUES (:u, :p, :f, :e, 'User', 1, :c)", [
            'u' => $user->UserName, 'p' => password_hash((string)$user->Password, PASSWORD_DEFAULT),
            'f' => $user->FullName, 'e' => $email, 'c' => now(),
        ]);
        return true;
    }

    public function DeleteUser(int $userId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $this->exec('DELETE FROM `TeacherRequests` WHERE `UserId` = :id', ['id' => $userId]);
            $n = $this->exec('DELETE FROM `Users` WHERE `Id` = :id', ['id' => $userId]);
            $this->pdo->commit();
            return $n > 0;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw new Exception('Error deleting user: ' . $e->getMessage());
        }
    }

    public function UpdatePassword(int $userId, string $newPassword): bool
    {
        $this->exec('UPDATE `Users` SET `Password` = :p WHERE `Id` = :id', ['p' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $userId]);
        return (bool)$this->scalar('SELECT COUNT(*) FROM `Users` WHERE `Id` = :id', ['id' => $userId]);
    }

    public function ToggleUserStatus(int $userId, bool $isActive): bool
    {
        $this->exec('UPDATE `Users` SET `IsActive` = :a WHERE `Id` = :id', ['a' => $isActive, 'id' => $userId]);
        return (bool)$this->scalar('SELECT COUNT(*) FROM `Users` WHERE `Id` = :id', ['id' => $userId]);
    }

    public function UsernameExists(string $username): bool
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `Users` WHERE LOWER(`UserName`) = LOWER(:u)', ['u' => $username]) > 0;
    }

    public function EmailExists(string $email): bool
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `Users` WHERE LOWER(`Email`) = LOWER(:e)', ['e' => trim($email)]) > 0;
    }

    public function UpdateLastLogin(string $username): void
    {
        $this->exec('UPDATE `Users` SET `LastLoginAt` = :t WHERE `UserName` = :u', ['t' => now(), 'u' => $username]);
    }

    /** @return Obj[] */
    public function GetAllTeachers(): array
    {
        try {
            return array_map(fn($u) => $this->mapUser($u), $this->all('SELECT ' . self::USER_COLUMNS . " FROM `Users` WHERE `Role` = 'Teacher' ORDER BY `Id` DESC"));
        } catch (Throwable $e) { return []; }
    }

    /** @return Obj[] */
    public function GetAllNonAdminUsers(): array
    {
        try {
            return array_map(fn($u) => $this->mapUser($u), $this->all('SELECT ' . self::USER_COLUMNS . " FROM `Users` WHERE `Role` <> 'Admin' ORDER BY `Id` DESC"));
        } catch (Throwable $e) { return []; }
    }

    /** @return Obj[] সব অ্যাডমিন */
    public function GetAdmins(): array
    {
        return $this->all("SELECT `Id`, `FullName` FROM `Users` WHERE `Role` = 'Admin'");
    }

    // ============================================================
    // ===== TEACHER REQUEST RELATED METHODS =====
    // ============================================================

    public function CreateTeacherRequest(Obj $r, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $r->Id = $this->insert("INSERT INTO `TeacherRequests`
                (`UserId`, `FullName`, `Email`, `MobileNumber`, `Education`, `Institution`, `SubjectExpertise`, `Experience`,
                 `TeachingStyle`, `AvailableDays`, `PreferredTime`, `HourlyRate`, `CvLink`, `WhyTeach`, `Status`, `RequestDate`)
                VALUES (:userId, :fullName, :email, :mobileNumber, :education, :institution, :subjectExpertise, :experience,
                 :teachingStyle, :availableDays, :preferredTime, :hourlyRate, :cvLink, :whyTeach, 'Pending', :requestDate)", [
                'userId' => (int)$r->UserId, 'fullName' => $r->FullName, 'email' => $r->Email, 'mobileNumber' => $r->MobileNumber,
                'education' => $r->Education, 'institution' => $r->Institution, 'subjectExpertise' => $r->SubjectExpertise,
                'experience' => $r->Experience, 'teachingStyle' => $r->TeachingStyle, 'availableDays' => $r->AvailableDays,
                'preferredTime' => $r->PreferredTime, 'hourlyRate' => $r->HourlyRate, 'cvLink' => $r->CvLink,
                'whyTeach' => $r->WhyTeach, 'requestDate' => $r->RequestDate ?? now(),
            ]);
            return true;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            return false;
        }
    }

    public function GetUserTeacherRequestStatus(int $userId): string
    {
        try {
            $s = $this->scalar('SELECT `Status` FROM `TeacherRequests` WHERE `UserId` = :id ORDER BY `RequestDate` DESC, `Id` DESC LIMIT 1', ['id' => $userId]);
            return $s === null ? 'None' : (string)$s;
        } catch (Throwable $e) { return 'None'; }
    }

    /** @return Obj[] */
    public function GetAllTeacherRequests(): array
    {
        return array_map(fn($r) => $this->mapTeacherRequest($r), $this->all('SELECT * FROM `TeacherRequests` ORDER BY `RequestDate` DESC, `Id` DESC'));
    }

    public function GetPendingTeacherRequestCount(): int
    {
        try { return (int)$this->scalar("SELECT COUNT(*) FROM `TeacherRequests` WHERE `Status` = 'Pending'"); } catch (Throwable $e) { return 0; }
    }

    public function UpdateTeacherRequest(int $requestId, string $status, ?string $adminNote): bool
    {
        return $this->exec('UPDATE `TeacherRequests` SET `Status` = :s, `ResponseDate` = :d, `AdminNote` = :n WHERE `Id` = :id',
                ['s' => $status, 'd' => now(), 'n' => $adminNote, 'id' => $requestId]) > 0;
    }

    public function GetTeacherRequestById(int $requestId): ?Obj
    {
        $r = $this->one('SELECT * FROM `TeacherRequests` WHERE `Id` = :id', ['id' => $requestId]);
        return $r ? $this->mapTeacherRequest($r) : null;
    }

    public function MakeUserTeacher(int $userId): bool
    {
        $this->exec("UPDATE `Users` SET `Role` = 'Teacher' WHERE `Id` = :id", ['id' => $userId]);
        return true;
    }

    public function DeleteTeacherRequestById(int $requestId): bool
    {
        return $this->exec('DELETE FROM `TeacherRequests` WHERE `Id` = :id', ['id' => $requestId]) > 0;
    }

    // ============================================================
    // ===== FIND TUTOR — APPROVED TEACHERS =====
    // ============================================================
    /** @return Obj[] TutorProfile list */
    public function GetApprovedTutors(): array
    {
        $tutors = [];
        try {
            $rows = $this->all("SELECT tr.`Id`, tr.`UserId`, tr.`FullName`, tr.`Email`, tr.`MobileNumber`, tr.`Education`,
                    tr.`Institution`, tr.`SubjectExpertise`, tr.`Experience`, tr.`TeachingStyle`, tr.`AvailableDays`,
                    tr.`PreferredTime`, tr.`HourlyRate`, tr.`CvLink`, tr.`WhyTeach`, tr.`Status`, tr.`RequestDate`,
                    u.`ProfileImageLink`
                FROM `TeacherRequests` tr
                LEFT JOIN `Users` u ON tr.`UserId` = u.`Id`
                WHERE tr.`Status` = 'Approved'
                ORDER BY tr.`RequestDate` DESC, tr.`Id` DESC");
        } catch (Throwable $e) { return []; }

        $index = 1;
        foreach ($rows as $r) {
            $fullName = $r->FullName ?? 'Tutor';
            if ($fullName === '') $fullName = 'Tutor';
            $requestId = (int)$r->Id;

            $subjects = array_values(array_filter(array_map('trim', preg_split('/[,\/&]/', (string)$r->SubjectExpertise)), fn($s) => $s !== ''));
            if (!$subjects) $subjects = ['General'];

            $expYears = 0;
            $digits = preg_replace('/\D/', '', (string)$r->Experience);
            if ($digits !== '') $expYears = (int)substr($digits, 0, 2);

            $rating = round(4.5 + ($index % 5) * 0.1, 1);
            $reviews = 20 + $index * 3;
            $monthlyFee = 5000 + ($index % 6) * 1000;
            $hr = trim((string)$r->HourlyRate);
            if (is_numeric($hr) && (float)$hr > 0) $monthlyFee = round((float)$hr * 30, 0);

            $tutors[] = new Obj([
                'Id' => $requestId,
                'UserId' => (int)$r->UserId,
                'TutorId' => 'TCH-' . (1000 + $requestId),
                'FullName' => $fullName,
                'Initials' => initials($fullName, 'T'),
                'Gender' => 'Any',
                'PhotoUrl' => (string)($r->ProfileImageLink ?? ''),
                'Subjects' => $subjects,
                'Classes' => ['All Classes'],
                'Qualification' => $r->Education ?? 'Not specified',
                'Institution' => $r->Institution ?? 'Not specified',
                'ExperienceYears' => $expYears,
                'Location' => 'Bangladesh',
                'TeachingMode' => 'Both',
                'MonthlyFee' => $monthlyFee,
                'Rating' => $rating,
                'TotalReviews' => $reviews,
                'AvailableTime' => !is_blank($r->PreferredTime) ? $r->PreferredTime : ($r->AvailableDays ?? 'Contact for schedule'),
                'About' => !is_blank($r->WhyTeach) ? $r->WhyTeach : (!is_blank($r->TeachingStyle) ? $r->TeachingStyle : 'Experienced tutor ready to help students excel.'),
                'IsVerified' => true,
                'IsTopRated' => $index <= 3,
            ]);
            $index++;
        }
        return $tutors;
    }

    // ============================================================
    // ===== TUTOR REQUESTS (Student → Admin/Teacher) =====
    // ============================================================
    public function CreateTutorRequest(Obj $req, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $req->Id = $this->insert("INSERT INTO `TutorRequests`
                (`TutorId`, `TutorName`, `StudentName`, `PhoneNumber`, `WhatsAppNumber`, `Email`, `StudentClass`, `RequiredSubject`,
                 `PreferredArea`, `TeachingMode`, `PreferredDays`, `PreferredTime`, `MonthlyBudget`, `AdditionalNotes`,
                 `RequestType`, `RequestId`, `Status`, `CreatedAt`)
                VALUES (:tutorId, :tutorName, :studentName, :phone, :wa, :email, :cls, :subj, :area, :mode, :days, :time,
                 :budget, :notes, :type, :rid, 'Pending', :created)", [
                'tutorId' => $req->TutorId, 'tutorName' => $req->TutorName, 'studentName' => $req->StudentName ?? '',
                'phone' => $req->PhoneNumber, 'wa' => $req->WhatsAppNumber, 'email' => $req->Email,
                'cls' => $req->StudentClass, 'subj' => $req->RequiredSubject, 'area' => $req->PreferredArea,
                'mode' => $req->TeachingMode, 'days' => $req->PreferredDays, 'time' => $req->PreferredTime,
                'budget' => $req->MonthlyBudget, 'notes' => $req->AdditionalNotes,
                'type' => $req->RequestType ?? 'SpecificTutor', 'rid' => $req->RequestId, 'created' => now(),
            ]);
            return $req->Id > 0;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            return false;
        }
    }

    /** @return Obj[] */
    public function GetAllTutorRequests(?string $statusFilter = null): array
    {
        try {
            if (!is_blank($statusFilter) && $statusFilter !== 'all') {
                $rows = $this->all('SELECT * FROM `TutorRequests` WHERE `Status` = :s ORDER BY `CreatedAt` DESC', ['s' => $statusFilter]);
            } else {
                $rows = $this->all('SELECT * FROM `TutorRequests` ORDER BY `CreatedAt` DESC');
            }
            return array_map([self::class, 'mapTutorRequest'], $rows);
        } catch (Throwable $e) { return []; }
    }

    /** @return Obj[] টিচারের কাছে আসা Direct Requests */
    public function GetTeacherDirectRequests(string $tutorName): array
    {
        try {
            return array_map([self::class, 'mapTutorRequest'],
                $this->all('SELECT * FROM `TutorRequests` WHERE `TutorName` = :n ORDER BY `CreatedAt` DESC', ['n' => $tutorName]));
        } catch (Throwable $e) { return []; }
    }

    public function GetTutorRequestById(int $id): ?Obj
    {
        try {
            $r = $this->one('SELECT * FROM `TutorRequests` WHERE `Id` = :id', ['id' => $id]);
            return $r ? self::mapTutorRequest($r) : null;
        } catch (Throwable $e) { return null; }
    }

    public function UpdateTutorRequestStatus(int $id, ?string $status, ?string $adminNote): bool
    {
        try {
            return $this->exec('UPDATE `TutorRequests` SET `Status` = :s, `AdminNote` = :n, `UpdatedAt` = :u WHERE `Id` = :id',
                    ['s' => $status ?? 'Pending', 'n' => $adminNote, 'u' => now(), 'id' => $id]) > 0;
        } catch (Throwable $e) { return false; }
    }

    public function DeleteTutorRequest(int $id): bool
    {
        try { return $this->exec('DELETE FROM `TutorRequests` WHERE `Id` = :id', ['id' => $id]) > 0; } catch (Throwable $e) { return false; }
    }

    public function GetTutorRequestStats(): Obj
    {
        try {
            $r = $this->one("SELECT COUNT(*) AS Total,
                    SUM(CASE WHEN `Status` = 'Pending' THEN 1 ELSE 0 END) AS Pending,
                    SUM(CASE WHEN `Status` = 'Contacted' THEN 1 ELSE 0 END) AS Contacted,
                    SUM(CASE WHEN `Status` = 'Completed' THEN 1 ELSE 0 END) AS Completed
                FROM `TutorRequests`");
            return obj(['Total' => (int)$r->Total, 'Pending' => (int)$r->Pending, 'Contacted' => (int)$r->Contacted, 'Completed' => (int)$r->Completed]);
        } catch (Throwable $e) {
            return obj(['Total' => 0, 'Pending' => 0, 'Contacted' => 0, 'Completed' => 0]);
        }
    }

    // ============================================================
    // ===== TUITION POSTS (EF Core এর বদলে) =====
    // ============================================================
    public function GetTuitionPostById(int $id, bool $withApplications = false): ?Obj
    {
        $p = $this->one('SELECT * FROM `TuitionPosts` WHERE `Id` = :id', ['id' => $id]);
        if (!$p) return null;
        $p = self::mapTuitionPost($p);
        if ($withApplications) {
            $p->Applications = array_map([self::class, 'mapTuitionApplication'],
                $this->all('SELECT * FROM `TuitionApplications` WHERE `TuitionPostId` = :id ORDER BY `AppliedAt` DESC', ['id' => $id]));
        }
        return $p;
    }

    /** @return Obj[] সব পোস্ট (Applications সহ) */
    public function GetAllTuitionPostsWithApplications(): array
    {
        $posts = array_map([self::class, 'mapTuitionPost'], $this->all('SELECT * FROM `TuitionPosts` ORDER BY `PostedDate` DESC, `Id` DESC'));
        if (!$posts) return [];
        $byId = [];
        foreach ($posts as $p) $byId[$p->Id] = $p;
        foreach ($this->all('SELECT * FROM `TuitionApplications` ORDER BY `AppliedAt` DESC') as $a) {
            $a = self::mapTuitionApplication($a);
            if (isset($byId[$a->TuitionPostId])) $byId[$a->TuitionPostId]->Applications[] = $a;
        }
        return $posts;
    }

    /** @return Obj[] Open পোস্ট (search + area filter) */
    public function GetOpenTuitionPosts(?string $search = null, ?string $area = null): array
    {
        $sql = "SELECT * FROM `TuitionPosts` WHERE `Status` = 'Open'";
        $params = [];
        if (!is_blank($search)) {
            $sql .= " AND (LOWER(COALESCE(`Title`,'')) LIKE :q1 OR LOWER(COALESCE(`Subjects`,'')) LIKE :q2 OR LOWER(COALESCE(`Area`,'')) LIKE :q3
                      OR LOWER(COALESCE(`StudentClass`,'')) LIKE :q4 OR LOWER(COALESCE(`TuitionCode`,'')) LIKE :q5)";
            $q = '%' . mb_strtolower(trim($search)) . '%';
            $params += ['q1' => $q, 'q2' => $q, 'q3' => $q, 'q4' => $q, 'q5' => $q];
        }
        if (!is_blank($area) && $area !== 'all') {
            $sql .= " AND LOWER(COALESCE(`Area`,'')) LIKE :a";
            $params['a'] = '%' . mb_strtolower($area) . '%';
        }
        $sql .= ' ORDER BY `PostedDate` DESC, `Id` DESC';
        return array_map([self::class, 'mapTuitionPost'], $this->all($sql, $params));
    }

    public function CreateTuitionPost(Obj $m): int
    {
        return $this->insert('INSERT INTO `TuitionPosts`
            (`Title`, `StudentGender`, `StudentClass`, `Subjects`, `DaysPerWeek`, `PreferredTime`, `Salary`, `Area`, `MediaFee`,
             `TuitionCode`, `SpecialNote`, `TutorGenderPreference`, `Status`, `ShowOnHome`, `PostedDate`)
            VALUES (:Title, :StudentGender, :StudentClass, :Subjects, :DaysPerWeek, :PreferredTime, :Salary, :Area, :MediaFee,
             :TuitionCode, :SpecialNote, :TutorGenderPreference, :Status, :ShowOnHome, :PostedDate)', [
            'Title' => $m->Title, 'StudentGender' => $m->StudentGender, 'StudentClass' => $m->StudentClass,
            'Subjects' => $m->Subjects, 'DaysPerWeek' => $m->DaysPerWeek, 'PreferredTime' => $m->PreferredTime,
            'Salary' => $m->Salary, 'Area' => $m->Area, 'MediaFee' => $m->MediaFee, 'TuitionCode' => $m->TuitionCode,
            'SpecialNote' => $m->SpecialNote, 'TutorGenderPreference' => $m->TutorGenderPreference,
            'Status' => $m->Status ?: 'Open', 'ShowOnHome' => (bool)$m->ShowOnHome, 'PostedDate' => $m->PostedDate ?? now(),
        ]);
    }

    public function UpdateTuitionPost(Obj $m): bool
    {
        $this->exec('UPDATE `TuitionPosts` SET `Title` = :Title, `StudentGender` = :StudentGender, `StudentClass` = :StudentClass,
                `Subjects` = :Subjects, `DaysPerWeek` = :DaysPerWeek, `PreferredTime` = :PreferredTime, `Salary` = :Salary,
                `Area` = :Area, `MediaFee` = :MediaFee, `TuitionCode` = :TuitionCode, `SpecialNote` = :SpecialNote,
                `TutorGenderPreference` = :TutorGenderPreference, `Status` = :Status, `UpdatedAt` = :UpdatedAt
            WHERE `Id` = :Id', [
            'Id' => (int)$m->Id,
            'Title' => $m->Title, 'StudentGender' => $m->StudentGender, 'StudentClass' => $m->StudentClass,
            'Subjects' => $m->Subjects, 'DaysPerWeek' => $m->DaysPerWeek, 'PreferredTime' => $m->PreferredTime,
            'Salary' => $m->Salary, 'Area' => $m->Area, 'MediaFee' => $m->MediaFee, 'TuitionCode' => $m->TuitionCode,
            'SpecialNote' => $m->SpecialNote, 'TutorGenderPreference' => $m->TutorGenderPreference,
            'Status' => $m->Status ?: 'Open', 'UpdatedAt' => now(),
        ]);
        return true;
    }

    public function SetTuitionPostStatus(int $id, string $status): void
    {
        $this->exec('UPDATE `TuitionPosts` SET `Status` = :s, `UpdatedAt` = :u WHERE `Id` = :id', ['s' => $status, 'u' => now(), 'id' => $id]);
    }

    public function DeleteTuitionPost(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            $this->exec('DELETE FROM `TuitionApplications` WHERE `TuitionPostId` = :id', ['id' => $id]);
            $n = $this->exec('DELETE FROM `TuitionPosts` WHERE `Id` = :id', ['id' => $id]);
            $this->pdo->commit();
            return $n > 0;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function GetHomePageTuitionPosts(int $limit = 3): array
    {
        try {
            $limit = max(1, $limit);
            return array_map([self::class, 'mapTuitionPost'],
                $this->all("SELECT * FROM `TuitionPosts` WHERE `Status` = 'Open' AND `ShowOnHome` = 1 ORDER BY `PostedDate` DESC, `Id` DESC LIMIT $limit"));
        } catch (Throwable $e) { return []; }
    }

    public function ToggleShowOnHome(int $postId, bool $showOnHome): bool
    {
        try {
            return $this->exec('UPDATE `TuitionPosts` SET `ShowOnHome` = :s, `UpdatedAt` = :u WHERE `Id` = :id',
                    ['s' => $showOnHome, 'u' => now(), 'id' => $postId]) > 0;
        } catch (Throwable $e) { return false; }
    }

    // ============================================================
    // ===== TUITION APPLICATIONS =====
    // ============================================================
    private function attachPostToApplications(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $a = self::mapTuitionApplication($r);
            if ($r->P_Id !== null) {
                $a->TuitionPost = self::mapTuitionPost(obj([
                    'Id' => $r->P_Id, 'Title' => $r->P_Title, 'StudentGender' => $r->P_StudentGender, 'StudentClass' => $r->P_StudentClass,
                    'Subjects' => $r->P_Subjects, 'DaysPerWeek' => $r->P_DaysPerWeek, 'PreferredTime' => $r->P_PreferredTime,
                    'Salary' => $r->P_Salary, 'Area' => $r->P_Area, 'MediaFee' => $r->P_MediaFee, 'TuitionCode' => $r->P_TuitionCode,
                    'SpecialNote' => $r->P_SpecialNote, 'TutorGenderPreference' => $r->P_TutorGenderPreference,
                    'Status' => $r->P_Status, 'ShowOnHome' => $r->P_ShowOnHome, 'PostedDate' => $r->P_PostedDate, 'UpdatedAt' => $r->P_UpdatedAt,
                ]));
            }
            foreach (get_object_vars($a) as $k => $v) if (str_starts_with($k, 'P_')) unset($a->$k);
            $out[] = $a;
        }
        return $out;
    }

    private const APP_JOIN_SELECT = 'SELECT a.*, p.`Id` AS P_Id, p.`Title` AS P_Title, p.`StudentGender` AS P_StudentGender,
            p.`StudentClass` AS P_StudentClass, p.`Subjects` AS P_Subjects, p.`DaysPerWeek` AS P_DaysPerWeek,
            p.`PreferredTime` AS P_PreferredTime, p.`Salary` AS P_Salary, p.`Area` AS P_Area, p.`MediaFee` AS P_MediaFee,
            p.`TuitionCode` AS P_TuitionCode, p.`SpecialNote` AS P_SpecialNote, p.`TutorGenderPreference` AS P_TutorGenderPreference,
            p.`Status` AS P_Status, p.`ShowOnHome` AS P_ShowOnHome, p.`PostedDate` AS P_PostedDate, p.`UpdatedAt` AS P_UpdatedAt
        FROM `TuitionApplications` a LEFT JOIN `TuitionPosts` p ON a.`TuitionPostId` = p.`Id`';

    /** @return Obj[] (TuitionPost সহ) */
    public function GetTuitionApplications(?string $status = null): array
    {
        if (!is_blank($status) && $status !== 'all') {
            $rows = $this->all(self::APP_JOIN_SELECT . ' WHERE a.`Status` = :s ORDER BY a.`AppliedAt` DESC', ['s' => $status]);
        } else {
            $rows = $this->all(self::APP_JOIN_SELECT . ' ORDER BY a.`AppliedAt` DESC');
        }
        return $this->attachPostToApplications($rows);
    }

    public function GetTuitionApplicationById(int $id): ?Obj
    {
        $rows = $this->all(self::APP_JOIN_SELECT . ' WHERE a.`Id` = :id', ['id' => $id]);
        return $rows ? $this->attachPostToApplications($rows)[0] : null;
    }

    /** @return Obj[] নির্দিষ্ট ইউজারের Tuition Applications */
    public function GetUserTuitionApplications(int $userId): array
    {
        try {
            return $this->attachPostToApplications($this->all(self::APP_JOIN_SELECT . ' WHERE a.`UserId` = :u ORDER BY a.`AppliedAt` DESC', ['u' => $userId]));
        } catch (Throwable $e) { return []; }
    }

    public function CountTuitionApplications(?string $status = null): int
    {
        if ($status === null) return (int)$this->scalar('SELECT COUNT(*) FROM `TuitionApplications`');
        return (int)$this->scalar('SELECT COUNT(*) FROM `TuitionApplications` WHERE `Status` = :s', ['s' => $status]);
    }

    public function HasUserApplied(int $postId, int $userId): bool
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `TuitionApplications` WHERE `TuitionPostId` = :p AND `UserId` = :u', ['p' => $postId, 'u' => $userId]) > 0;
    }

    public function CreateTuitionApplication(Obj $m): int
    {
        return $this->insert('INSERT INTO `TuitionApplications`
            (`TuitionPostId`, `UserId`, `ApplicantName`, `PhoneNumber`, `WhatsAppNumber`, `Email`, `CvLink`, `CoverNote`, `Status`, `AppliedAt`)
            VALUES (:p, :u, :n, :ph, :wa, :e, :cv, :note, :s, :at)', [
            'p' => (int)$m->TuitionPostId, 'u' => $m->UserId, 'n' => $m->ApplicantName, 'ph' => $m->PhoneNumber,
            'wa' => $m->WhatsAppNumber, 'e' => $m->Email, 'cv' => $m->CvLink, 'note' => $m->CoverNote,
            's' => $m->Status ?? 'Pending', 'at' => now(),
        ]);
    }

    public function UpdateApplicationStatus(int $id, string $status, ?string $adminNote): void
    {
        $this->exec('UPDATE `TuitionApplications` SET `Status` = :s, `AdminNote` = :n, `UpdatedAt` = :u WHERE `Id` = :id',
            ['s' => $status, 'n' => $adminNote, 'u' => now(), 'id' => $id]);
    }

    public function DeleteTuitionApplication(int $id): bool
    {
        return $this->exec('DELETE FROM `TuitionApplications` WHERE `Id` = :id', ['id' => $id]) > 0;
    }

    // ============================================================
    // ===== TEACHER DASHBOARD STATS =====
    // ============================================================
    public function GetTeacherDashboardStats(int $userId): Obj
    {
        $s = obj(['TotalPosts' => 0, 'TotalComments' => 0, 'TotalLikes' => 0, 'TotalReplies' => 0, 'TotalTuitionApplications' => 0, 'TotalResponses' => 0]);
        try {
            $p = ['uid' => $userId];
            $s->TotalPosts = (int)$this->scalar('SELECT COUNT(*) FROM `TeachWantedPosts` WHERE `UserId` = :uid', $p);
            $s->TotalComments = (int)$this->scalar('SELECT COUNT(*) FROM `TeachWantedComments` c INNER JOIN `TeachWantedPosts` p ON c.`PostId` = p.`Id` WHERE p.`UserId` = :uid', $p);
            $s->TotalLikes = (int)$this->scalar('SELECT COALESCE(SUM(`LikeCount`), 0) FROM `TeachWantedPosts` WHERE `UserId` = :uid', $p);
            $s->TotalReplies = (int)$this->scalar('SELECT COUNT(*) FROM `TeachWantedComments` c INNER JOIN `TeachWantedPosts` p ON c.`PostId` = p.`Id` WHERE p.`UserId` = :uid AND c.`ParentId` IS NOT NULL', $p);
            $s->TotalTuitionApplications = (int)$this->scalar('SELECT COUNT(*) FROM `TuitionApplications` WHERE `UserId` = :uid', $p);
            $s->TotalResponses = $s->TotalComments + $s->TotalLikes;
        } catch (Throwable $e) {}
        return $s;
    }

    /** @return Obj[] TeacherPostStat */
    public function GetTeacherPostStats(int $userId): array
    {
        try {
            $rows = $this->all('SELECT p.`Id`, p.`Content`, p.`ImageUrl`, p.`LikeCount`, p.`CreatedAt`,
                    (SELECT COUNT(*) FROM `TeachWantedComments` WHERE `PostId` = p.`Id`) AS CommentCount
                FROM `TeachWantedPosts` p WHERE p.`UserId` = :uid ORDER BY p.`CreatedAt` DESC', ['uid' => $userId]);
        } catch (Throwable $e) { return []; }
        return array_map(function ($r) {
            $like = (int)$r->LikeCount; $cc = (int)$r->CommentCount;
            return obj([
                'PostId' => (int)$r->Id, 'Content' => $r->Content ?? '', 'ImageUrl' => $r->ImageUrl,
                'LikeCount' => $like, 'CommentCount' => $cc, 'CreatedAt' => $r->CreatedAt,
                'TimeAgo' => time_ago($r->CreatedAt), 'ContentPreview' => str_preview($r->Content, 120),
                'TotalEngagement' => $like + $cc,
            ]);
        }, $rows);
    }

    // ============================================================
    // ===== USER DASHBOARD STATS =====
    // ============================================================
    public function GetUserDashboardStats(int $userId): Obj
    {
        $s = obj(['TotalPosts' => 0, 'TotalLikes' => 0, 'TotalComments' => 0, 'TotalTeachersConnected' => 0, 'TotalApplications' => 0, 'PendingApplications' => 0]);
        try {
            $p = ['uid' => $userId];
            $s->TotalPosts = (int)$this->scalar('SELECT COUNT(*) FROM `TuitionCircularPosts` WHERE `UserId` = :uid', $p);
            $s->TotalLikes = (int)$this->scalar('SELECT COALESCE(SUM(`LikeCount`), 0) FROM `TuitionCircularPosts` WHERE `UserId` = :uid', $p);
            $s->TotalComments = (int)$this->scalar('SELECT COUNT(*) FROM `TuitionCircularComments` c INNER JOIN `TuitionCircularPosts` p ON c.`PostId` = p.`Id` WHERE p.`UserId` = :uid', $p);
            $s->TotalTeachersConnected = (int)$this->scalar("SELECT COUNT(DISTINCT c.`UserId`) FROM `TuitionCircularComments` c
                INNER JOIN `TuitionCircularPosts` p ON c.`PostId` = p.`Id`
                INNER JOIN `Users` u ON c.`UserId` = u.`Id`
                WHERE p.`UserId` = :uid AND u.`Role` = 'Teacher'", $p);
            $this->EnsureCircularApplicationsTable();
            $s->TotalApplications = (int)$this->scalar('SELECT COUNT(*) FROM `TuitionCircularApplications` a INNER JOIN `TuitionCircularPosts` p ON a.`PostId` = p.`Id` WHERE p.`UserId` = :uid', $p);
            $s->PendingApplications = (int)$this->scalar("SELECT COUNT(*) FROM `TuitionCircularApplications` a INNER JOIN `TuitionCircularPosts` p ON a.`PostId` = p.`Id` WHERE p.`UserId` = :uid AND a.`Status` = 'Pending'", $p);
        } catch (Throwable $e) {}
        return $s;
    }

    public function GetUserOverviewStats(int $userId): Obj { return $this->GetUserDashboardStats($userId); }

    private function mapFeedPost(Obj $r, string $defaultName): Obj
    {
        $r->Id = (int)$r->Id;
        $r->UserId = (int)$r->UserId;
        $r->Content = $r->Content ?? '';
        $r->LikeCount = (int)$r->LikeCount;
        $r->UserFullName = $r->FullName ?? $defaultName;
        $r->UserName = $r->UserName ?? '';
        $r->UserProfileImage = $r->ProfileImageLink;
        unset($r->FullName, $r->ProfileImageLink);
        $r->Comments = [];
        return $r;
    }

    /** @return Obj[] */
    public function GetUserCircularPosts(int $userId, int $limit = 10): array
    {
        try {
            $limit = max(1, $limit);
            $rows = $this->all("SELECT p.`Id`, p.`UserId`, p.`Content`, p.`ImageUrl`, p.`PreferredArea`, p.`ClassLevel`, p.`Subjects`,
                    p.`Budget`, p.`LikeCount`, p.`CreatedAt`, u.`FullName`, u.`UserName`, u.`ProfileImageLink`
                FROM `TuitionCircularPosts` p LEFT JOIN `Users` u ON p.`UserId` = u.`Id`
                WHERE p.`UserId` = :uid ORDER BY p.`CreatedAt` DESC LIMIT $limit", ['uid' => $userId]);
            return array_map(function ($r) {
                $post = $this->mapFeedPost($r, 'User');
                $post->Comments = $this->GetTuitionCircularComments($post->Id, current_user_id());
                return $post;
            }, $rows);
        } catch (Throwable $e) { return []; }
    }

    /** @return Obj[] UserPostStat */
    public function GetUserPostStats(int $userId): array
    {
        try {
            $rows = $this->all("SELECT p.`Id`, p.`Content`, p.`ImageUrl`, p.`PreferredArea`, p.`ClassLevel`, p.`Subjects`, p.`Budget`,
                    p.`LikeCount`, p.`CreatedAt`,
                    (SELECT COUNT(*) FROM `TuitionCircularComments` WHERE `PostId` = p.`Id`) AS CommentCount,
                    (SELECT COUNT(DISTINCT c.`UserId`) FROM `TuitionCircularComments` c INNER JOIN `Users` u ON c.`UserId` = u.`Id`
                        WHERE c.`PostId` = p.`Id` AND u.`Role` = 'Teacher') AS TeacherResponses
                FROM `TuitionCircularPosts` p WHERE p.`UserId` = :uid ORDER BY p.`CreatedAt` DESC", ['uid' => $userId]);
        } catch (Throwable $e) { return []; }
        return array_map(function ($r) {
            $like = (int)$r->LikeCount; $cc = (int)$r->CommentCount; $tr = (int)$r->TeacherResponses;
            return obj([
                'PostId' => (int)$r->Id, 'Content' => $r->Content ?? '', 'ImageUrl' => $r->ImageUrl,
                'PreferredArea' => $r->PreferredArea, 'ClassLevel' => $r->ClassLevel, 'Subjects' => $r->Subjects, 'Budget' => $r->Budget,
                'LikeCount' => $like, 'CommentCount' => $cc, 'TeacherResponses' => $tr, 'CreatedAt' => $r->CreatedAt,
                'TimeAgo' => time_ago($r->CreatedAt), 'ContentPreview' => str_preview($r->Content, 140),
                'TotalEngagement' => $like + $cc, 'HasTeacherResponse' => $tr > 0,
            ]);
        }, $rows);
    }

    /** @return Obj[] TeacherEngagement */
    public function GetTeachersEngagedWithUser(int $userId): array
    {
        try {
            $rows = $this->all("SELECT u.`Id` AS TeacherId, u.`FullName`, u.`Email`, u.`ProfileImageLink`,
                    COUNT(c.`Id`) AS ResponseCount, MAX(c.`CreatedAt`) AS LastResponseAt
                FROM `TuitionCircularComments` c
                INNER JOIN `TuitionCircularPosts` p ON c.`PostId` = p.`Id`
                INNER JOIN `Users` u ON c.`UserId` = u.`Id`
                WHERE p.`UserId` = :uid AND u.`Role` = 'Teacher'
                GROUP BY u.`Id`, u.`FullName`, u.`Email`, u.`ProfileImageLink`
                ORDER BY MAX(c.`CreatedAt`) DESC", ['uid' => $userId]);
        } catch (Throwable $e) { return []; }
        return array_map(function ($r) {
            $name = $r->FullName ?? 'Teacher';
            return obj([
                'TeacherId' => (int)$r->TeacherId, 'FullName' => $name, 'Email' => $r->Email ?? '',
                'ProfileImageLink' => $r->ProfileImageLink, 'ResponseCount' => (int)$r->ResponseCount,
                'LastResponseAt' => $r->LastResponseAt, 'Initials' => initials($name, 'T'),
                'LastResponseAgo' => time_ago($r->LastResponseAt),
            ]);
        }, $rows);
    }

    // ============================================================
    // ===== NOTIFICATIONS =====
    // ============================================================
    public function CreateNotification(int $userId, ?string $title, ?string $message, ?string $type = 'info', ?string $link = null, ?string $icon = 'fa-bell'): bool
    {
        try {
            $this->query('INSERT INTO `Notifications` (`UserId`, `Title`, `Message`, `Type`, `Icon`, `Link`, `IsRead`, `CreatedAt`)
                          VALUES (:u, :t, :m, :ty, :i, :l, 0, :c)', [
                'u' => $userId, 't' => $title, 'm' => $message, 'ty' => $type ?? 'info', 'i' => $icon ?? 'fa-bell', 'l' => $link, 'c' => now(),
            ]);
            return true;
        } catch (Throwable $e) { return false; }
    }

    /** @return Obj[] */
    public function GetUserNotifications(int $userId, int $limit = 20): array
    {
        try {
            $limit = max(1, $limit);
            return array_map(fn($n) => $this->mapNotification($n),
                $this->all("SELECT `Id`, `UserId`, `Title`, `Message`, `Type`, `Icon`, `Link`, `IsRead`, `CreatedAt`
                            FROM `Notifications` WHERE `UserId` = :u ORDER BY `CreatedAt` DESC, `Id` DESC LIMIT $limit", ['u' => $userId]));
        } catch (Throwable $e) { return []; }
    }

    public function GetUnreadNotificationCount(int $userId): int
    {
        try { return (int)$this->scalar('SELECT COUNT(*) FROM `Notifications` WHERE `UserId` = :u AND `IsRead` = 0', ['u' => $userId]); } catch (Throwable $e) { return 0; }
    }

    public function MarkNotificationAsRead(int $id, int $userId): bool
    {
        try {
            $this->exec('UPDATE `Notifications` SET `IsRead` = 1 WHERE `Id` = :id AND `UserId` = :u', ['id' => $id, 'u' => $userId]);
            return (int)$this->scalar('SELECT COUNT(*) FROM `Notifications` WHERE `Id` = :id AND `UserId` = :u', ['id' => $id, 'u' => $userId]) > 0;
        } catch (Throwable $e) { return false; }
    }

    public function MarkAllNotificationsAsRead(int $userId): bool
    {
        try { $this->exec('UPDATE `Notifications` SET `IsRead` = 1 WHERE `UserId` = :u AND `IsRead` = 0', ['u' => $userId]); return true; } catch (Throwable $e) { return false; }
    }

    public function ClearUserNotifications(int $userId): bool
    {
        try { $this->exec('DELETE FROM `Notifications` WHERE `UserId` = :u', ['u' => $userId]); return true; } catch (Throwable $e) { return false; }
    }

    // ============================================================
    // ===== NOTICE / NEWS / ANNOUNCEMENT =====
    // ============================================================
    private function normalizeNotice(Obj $n): void
    {
        if (strcasecmp((string)$n->Category, 'News') === 0) $n->IsAnnouncement = false;
        $d = to_dt($n->PublishedDate);
        if (!$d || (int)$d->format('Y') < 2000 || (int)$d->format('Y') > 2100) $n->PublishedDate = now();
        else $n->PublishedDate = $d->format('Y-m-d H:i:s');
    }

    public function CreateNotice(Obj $n, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        $this->normalizeNotice($n);
        try {
            $n->Id = $this->insert('INSERT INTO `Notices` (`Title`, `Content`, `Category`, `ImageUrl`, `PublishedDate`, `IsAnnouncement`, `IsActive`, `CreatedAt`)
                VALUES (:t, :c, :cat, :img, :pd, :ann, :act, :cr)', [
                't' => $n->Title ?? '', 'c' => $n->Content ?? '', 'cat' => is_blank($n->Category) ? 'News' : $n->Category,
                'img' => $n->ImageUrl ?? '', 'pd' => $n->PublishedDate, 'ann' => (bool)$n->IsAnnouncement,
                'act' => (bool)$n->IsActive, 'cr' => now(),
            ]);
            return $n->Id > 0;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            return false;
        }
    }

    /** @return Obj[] সক্রিয় সব notice */
    public function GetAllNotices(): array
    {
        try {
            return array_map(fn($n) => $this->mapNotice($n), $this->all('SELECT * FROM `Notices` WHERE `IsActive` = 1 ORDER BY `PublishedDate` DESC, `Id` DESC'));
        } catch (Throwable $e) { return []; }
    }

    public function GetNoticeById(int $id): ?Obj
    {
        try {
            $n = $this->one('SELECT * FROM `Notices` WHERE `Id` = :id', ['id' => $id]);
            return $n ? $this->mapNotice($n) : null;
        } catch (Throwable $e) { return null; }
    }

    public function UpdateNotice(Obj $n, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        $this->normalizeNotice($n);
        try {
            $this->exec('UPDATE `Notices` SET `Title` = :t, `Content` = :c, `Category` = :cat, `ImageUrl` = :img, `PublishedDate` = :pd,
                    `IsAnnouncement` = :ann, `IsActive` = :act, `UpdatedAt` = :u WHERE `Id` = :id', [
                'id' => (int)$n->Id, 't' => $n->Title ?? '', 'c' => $n->Content ?? '', 'cat' => is_blank($n->Category) ? 'News' : $n->Category,
                'img' => $n->ImageUrl ?? '', 'pd' => $n->PublishedDate, 'ann' => (bool)$n->IsAnnouncement,
                'act' => (bool)$n->IsActive, 'u' => now(),
            ]);
            if ((int)$this->scalar('SELECT COUNT(*) FROM `Notices` WHERE `Id` = :id', ['id' => (int)$n->Id]) === 0) {
                $errorMessage = 'Notice not found.';
                return false;
            }
            return true;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            return false;
        }
    }

    public function DeleteNotice(int $id): bool
    {
        try { return $this->exec('DELETE FROM `Notices` WHERE `Id` = :id', ['id' => $id]) > 0; } catch (Throwable $e) { return false; }
    }

    // ============================================================
    // ===== SITE SETTINGS =====
    // ============================================================
    public function GetSiteSettings(): SiteSettings
    {
        try {
            $r = $this->query('SELECT * FROM `SiteSettings` ORDER BY `Id` LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            return SiteSettings::fromRow($r ?: null);
        } catch (Throwable $e) { return new SiteSettings(); }
    }

    public function SaveSiteSettings(SiteSettings $s, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $existingId = $this->scalar('SELECT `Id` FROM `SiteSettings` ORDER BY `Id` LIMIT 1');
            $shape = strtolower(trim($s->LogoShape ?: 'circle'));
            if (!in_array($shape, ['circle', 'square', 'rounded'], true)) $shape = 'circle';
            $p = [
                'hero' => $s->HeroImageUrls ?? '', 'slider' => $s->SliderEnabled, 'interval' => $s->SliderIntervalSeconds,
                'primary' => $s->PrimaryColor ?: '#02B8F5', 'secondary' => $s->SecondaryColor ?: '#F5FBFF',
                'accent' => $s->AccentColor ?: '#FFB300', 'text' => $s->TextColor ?: '#334155',
                'navbg' => $s->NavbarBgColor ?: '#134856', 'footbg' => $s->FooterBgColor ?: '#08224A',
                'tutor' => is_blank($s->TutorSectionImageUrl) ? SiteSettings::DEFAULT_TUTOR_IMG : $s->TutorSectionImageUrl,
                'site' => is_blank($s->SiteName) ? 'SMART TUTOR HUB' : trim($s->SiteName),
                'navlogo' => is_blank($s->NavbarLogoUrl) ? SiteSettings::DEFAULT_LOGO : trim($s->NavbarLogoUrl),
                'footlogo' => is_blank($s->FooterLogoUrl) ? SiteSettings::DEFAULT_LOGO : trim($s->FooterLogoUrl),
                'logosize' => max(24, min(100, $s->LogoSizePx)), 'shape' => $shape, 'ts' => now(),
            ];
            if ($existingId !== null) {
                $p['id'] = (int)$existingId;
                $this->exec('UPDATE `SiteSettings` SET `HeroImageUrls` = :hero, `SliderEnabled` = :slider, `SliderIntervalSeconds` = :interval,
                        `PrimaryColor` = :primary, `SecondaryColor` = :secondary, `AccentColor` = :accent, `TextColor` = :text,
                        `NavbarBgColor` = :navbg, `FooterBgColor` = :footbg, `TutorSectionImageUrl` = :tutor, `SiteName` = :site,
                        `NavbarLogoUrl` = :navlogo, `FooterLogoUrl` = :footlogo, `LogoSizePx` = :logosize, `LogoShape` = :shape,
                        `UpdatedAt` = :ts WHERE `Id` = :id', $p);
            } else {
                $this->exec('INSERT INTO `SiteSettings` (`HeroImageUrls`, `SliderEnabled`, `SliderIntervalSeconds`, `PrimaryColor`, `SecondaryColor`,
                        `AccentColor`, `TextColor`, `NavbarBgColor`, `FooterBgColor`, `TutorSectionImageUrl`, `SiteName`, `NavbarLogoUrl`,
                        `FooterLogoUrl`, `LogoSizePx`, `LogoShape`, `CreatedAt`)
                    VALUES (:hero, :slider, :interval, :primary, :secondary, :accent, :text, :navbg, :footbg, :tutor, :site, :navlogo,
                        :footlogo, :logosize, :shape, :ts)', $p);
            }
            return true;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            return false;
        }
    }

    // ============================================================
    // ===== COMMENTS (shared for TeachWanted / TuitionCircular) =====
    // ============================================================
    /** Flat comment list → root-level tree (Facebook style, এক লেভেল reply) */
    private function buildCommentTree(array $all): array
    {
        $byId = [];
        foreach ($all as $c) { $c->Replies = []; $byId[$c->Id] = $c; }
        $top = [];
        foreach ($all as $c) {
            if ($c->ParentId === null || !isset($byId[$c->ParentId])) $top[$c->Id] = true;
        }
        foreach ($all as $c) {
            if (isset($top[$c->Id])) continue;
            $root = $c; $guard = 0;
            while ($root->ParentId !== null && isset($byId[$root->ParentId]) && $guard++ < 50) $root = $byId[$root->ParentId];
            $root->Replies[] = $c;
        }
        return array_values(array_filter($all, fn($c) => isset($top[$c->Id])));
    }

    private function getFeedComments(string $commentTable, string $likeTable, int $postId, $currentUserId): array
    {
        $uid = is_numeric($currentUserId) ? (int)$currentUserId : -1;
        try {
            $rows = $this->all("SELECT c.`Id`, c.`PostId`, c.`UserId`, c.`Content`, c.`ParentId`, c.`CreatedAt`,
                    u.`FullName`, u.`UserName`, u.`ProfileImageLink`,
                    (SELECT COUNT(*) FROM `$likeTable` cl WHERE cl.`CommentId` = c.`Id`) AS LikeCount,
                    (SELECT COUNT(*) FROM `$likeTable` cl2 WHERE cl2.`CommentId` = c.`Id` AND cl2.`UserId` = :uid) AS IsLiked
                FROM `$commentTable` c LEFT JOIN `Users` u ON c.`UserId` = u.`Id`
                WHERE c.`PostId` = :pid ORDER BY c.`CreatedAt` ASC, c.`Id` ASC", ['pid' => $postId, 'uid' => $uid]);
        } catch (Throwable $e) { return []; }
        $list = array_map(fn($r) => obj([
            'Id' => (int)$r->Id, 'PostId' => (int)$r->PostId, 'UserId' => (int)$r->UserId, 'Content' => $r->Content ?? '',
            'ParentId' => $r->ParentId === null ? null : (int)$r->ParentId, 'CreatedAt' => $r->CreatedAt,
            'UserFullName' => $r->FullName ?? 'User', 'UserName' => $r->UserName ?? '', 'UserProfileImage' => $r->ProfileImageLink,
            'LikeCount' => (int)$r->LikeCount, 'IsLikedByMe' => (int)$r->IsLiked > 0, 'Replies' => [],
        ]), $rows);
        return $this->buildCommentTree($list);
    }

    private function toggleCommentLike(string $likeTable, int $commentId, int $userId): array
    {
        try {
            $p = ['cid' => $commentId, 'uid' => $userId];
            $exists = (int)$this->scalar("SELECT COUNT(*) FROM `$likeTable` WHERE `CommentId` = :cid AND `UserId` = :uid", $p) > 0;
            if ($exists) $this->exec("DELETE FROM `$likeTable` WHERE `CommentId` = :cid AND `UserId` = :uid", $p);
            else $this->exec("INSERT INTO `$likeTable` (`CommentId`, `UserId`, `CreatedAt`) VALUES (:cid, :uid, :c)", $p + ['c' => now()]);
            $count = (int)$this->scalar("SELECT COUNT(*) FROM `$likeTable` WHERE `CommentId` = :cid", ['cid' => $commentId]);
            return [!$exists, $count];
        } catch (Throwable $e) { return [false, 0]; }
    }

    private function togglePostLike(string $likeTable, string $postTable, int $postId, int $userId): array
    {
        try {
            $p = ['pid' => $postId, 'uid' => $userId];
            $exists = (int)$this->scalar("SELECT COUNT(*) FROM `$likeTable` WHERE `PostId` = :pid AND `UserId` = :uid", $p) > 0;
            if ($exists) {
                $this->exec("DELETE FROM `$likeTable` WHERE `PostId` = :pid AND `UserId` = :uid", $p);
                $this->exec("UPDATE `$postTable` SET `LikeCount` = CASE WHEN `LikeCount` > 0 THEN `LikeCount` - 1 ELSE 0 END WHERE `Id` = :pid", ['pid' => $postId]);
            } else {
                $this->exec("INSERT INTO `$likeTable` (`PostId`, `UserId`, `CreatedAt`) VALUES (:pid, :uid, :c)", $p + ['c' => now()]);
                $this->exec("UPDATE `$postTable` SET `LikeCount` = `LikeCount` + 1 WHERE `Id` = :pid", ['pid' => $postId]);
            }
            $count = (int)$this->scalar("SELECT `LikeCount` FROM `$postTable` WHERE `Id` = :pid", ['pid' => $postId]);
            return [!$exists, $count];
        } catch (Throwable $e) { return [false, 0]; }
    }

    /** কোন কোন পোস্ট বর্তমান ইউজার লাইক করেছে → [postId => true] */
    public function GetLikedPostIds(string $type, $currentUserId): array
    {
        if (!is_numeric($currentUserId)) return [];
        $table = $type === 'teach' ? 'TeachWantedLikes' : 'TuitionCircularLikes';
        try {
            $ids = $this->query("SELECT `PostId` FROM `$table` WHERE `UserId` = :u", ['u' => (int)$currentUserId])->fetchAll(PDO::FETCH_COLUMN);
            return array_fill_keys(array_map('intval', $ids), true);
        } catch (Throwable $e) { return []; }
    }

    // ============================================================
    // ===== TEACH WANTED POSTS (FindTutor Page) =====
    // ============================================================
    /** @return Obj[] */
    public function GetAllTeachWantedPosts($currentUserId = null): array
    {
        try {
            $rows = $this->all('SELECT p.`Id`, p.`UserId`, p.`Content`, p.`ImageUrl`, p.`LikeCount`, p.`CreatedAt`,
                    u.`FullName`, u.`UserName`, u.`ProfileImageLink`
                FROM `TeachWantedPosts` p LEFT JOIN `Users` u ON p.`UserId` = u.`Id`
                ORDER BY p.`CreatedAt` DESC, p.`Id` DESC');
        } catch (Throwable $e) { return []; }
        $liked = $this->GetLikedPostIds('teach', $currentUserId);
        $list = [];
        foreach ($rows as $r) {
            $post = $this->mapFeedPost($r, 'Teacher');
            $post->IsLikedByMe = isset($liked[$post->Id]);
            $post->Comments = $this->GetTeachWantedComments($post->Id, $currentUserId);
            $list[] = $post;
        }
        return $list;
    }

    public function GetTeachWantedPostById(int $id): ?Obj
    {
        $r = $this->one('SELECT `Id`, `UserId`, `Content`, `ImageUrl` FROM `TeachWantedPosts` WHERE `Id` = :id', ['id' => $id]);
        if ($r) { $r->Id = (int)$r->Id; $r->UserId = (int)$r->UserId; }
        return $r;
    }

    public function GetTeachWantedComments(int $postId, $currentUserId = null): array
    {
        return $this->getFeedComments('TeachWantedComments', 'TeachWantedCommentLikes', $postId, $currentUserId);
    }

    public function ToggleTeachCommentLike(int $commentId, int $userId): array
    {
        return $this->toggleCommentLike('TeachWantedCommentLikes', $commentId, $userId);
    }

    public function CreateTeachWantedPost(Obj $post, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $post->Id = $this->insert('INSERT INTO `TeachWantedPosts` (`UserId`, `Content`, `ImageUrl`, `LikeCount`, `CreatedAt`) VALUES (:u, :c, :i, 0, :t)',
                ['u' => (int)$post->UserId, 'c' => $post->Content ?? '', 'i' => $post->ImageUrl, 't' => now()]);
            return $post->Id > 0;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

    public function UpdateTeachWantedPost(int $postId, int $requesterId, string $content, bool $imageChanged, ?string $newImageUrl,
                                          ?string &$oldImageUrl = null, ?string &$finalImageUrl = null, ?string &$errorMessage = null): bool
    {
        $oldImageUrl = $finalImageUrl = $errorMessage = null;
        try {
            $r = $this->one('SELECT `UserId`, `ImageUrl` FROM `TeachWantedPosts` WHERE `Id` = :id', ['id' => $postId]);
            if (!$r) { $errorMessage = 'Post not found.'; return false; }
            $oldImageUrl = $r->ImageUrl;
            if ((int)$r->UserId !== $requesterId) { $errorMessage = 'You can only edit your own post.'; return false; }
            $finalImageUrl = $imageChanged ? $newImageUrl : $oldImageUrl;
            if (trim($content) === '' && is_blank($finalImageUrl)) { $errorMessage = 'Post cannot be empty.'; return false; }
            $this->exec('UPDATE `TeachWantedPosts` SET `Content` = :c, `ImageUrl` = :i WHERE `Id` = :id', ['c' => $content, 'i' => $finalImageUrl, 'id' => $postId]);
            return true;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

    public function DeleteTeachWantedPost(int $postId, int $requesterId, bool $isAdmin, ?string &$imageUrl = null, ?string &$errorMessage = null): bool
    {
        $imageUrl = $errorMessage = null;
        try {
            $r = $this->one('SELECT `UserId`, `ImageUrl` FROM `TeachWantedPosts` WHERE `Id` = :id', ['id' => $postId]);
            if (!$r) { $errorMessage = 'Post not found.'; return false; }
            $imageUrl = $r->ImageUrl;
            if ((int)$r->UserId !== $requesterId && !$isAdmin) { $errorMessage = 'You can only delete your own post.'; return false; }
            $this->pdo->beginTransaction();
            $p = ['id' => $postId];
            $this->exec('DELETE FROM `TeachWantedCommentLikes` WHERE `CommentId` IN (SELECT `Id` FROM `TeachWantedComments` WHERE `PostId` = :id)', $p);
            $this->exec('DELETE FROM `TeachWantedComments` WHERE `PostId` = :id', $p);
            $this->exec('DELETE FROM `TeachWantedLikes` WHERE `PostId` = :id', $p);
            $this->exec('DELETE FROM `TeachWantedPosts` WHERE `Id` = :id', $p);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $errorMessage = $e->getMessage();
            return false;
        }
    }

    public function AddTeachWantedComment(Obj $c, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $c->Id = $this->insert('INSERT INTO `TeachWantedComments` (`PostId`, `UserId`, `Content`, `ParentId`, `CreatedAt`) VALUES (:p, :u, :c, :pa, :t)',
                ['p' => (int)$c->PostId, 'u' => (int)$c->UserId, 'c' => $c->Content ?? '', 'pa' => $c->ParentId, 't' => now()]);
            return $c->Id > 0;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

    public function ToggleTeachWantedLike(int $postId, int $userId): array
    {
        return $this->togglePostLike('TeachWantedLikes', 'TeachWantedPosts', $postId, $userId);
    }

    // ============================================================
    // ===== TUITION CIRCULAR POSTS (Student Posts) =====
    // ============================================================
    /** @return Obj[] */
    public function GetAllTuitionCircularPosts($currentUserId = null): array
    {
        try {
            $rows = $this->all('SELECT p.`Id`, p.`UserId`, p.`Content`, p.`ImageUrl`, p.`PreferredArea`, p.`ClassLevel`, p.`Subjects`,
                    p.`Budget`, p.`LikeCount`, p.`CreatedAt`, u.`FullName`, u.`UserName`, u.`ProfileImageLink`
                FROM `TuitionCircularPosts` p LEFT JOIN `Users` u ON p.`UserId` = u.`Id`
                ORDER BY p.`CreatedAt` DESC, p.`Id` DESC');
        } catch (Throwable $e) { return []; }
        $liked = $this->GetLikedPostIds('circular', $currentUserId);
        $list = [];
        foreach ($rows as $r) {
            $post = $this->mapFeedPost($r, 'Student');
            $post->IsLikedByMe = isset($liked[$post->Id]);
            $post->Comments = $this->GetTuitionCircularComments($post->Id, $currentUserId);
            $list[] = $post;
        }
        return $list;
    }

    public function GetTuitionCircularComments(int $postId, $currentUserId = null): array
    {
        return $this->getFeedComments('TuitionCircularComments', 'TuitionCircularCommentLikes', $postId, $currentUserId);
    }

    public function CreateTuitionCircularPost(Obj $post, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $post->Id = $this->insert('INSERT INTO `TuitionCircularPosts` (`UserId`, `Content`, `ImageUrl`, `PreferredArea`, `ClassLevel`, `Subjects`, `Budget`, `LikeCount`, `CreatedAt`)
                VALUES (:u, :c, :i, :a, :cl, :s, :b, 0, :t)', [
                'u' => (int)$post->UserId, 'c' => $post->Content ?? '', 'i' => $post->ImageUrl, 'a' => $post->PreferredArea,
                'cl' => $post->ClassLevel, 's' => $post->Subjects, 'b' => $post->Budget, 't' => now(),
            ]);
            return $post->Id > 0;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

    public function AddTuitionCircularComment(Obj $c, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $c->Id = $this->insert('INSERT INTO `TuitionCircularComments` (`PostId`, `UserId`, `Content`, `ParentId`, `CreatedAt`) VALUES (:p, :u, :c, :pa, :t)',
                ['p' => (int)$c->PostId, 'u' => (int)$c->UserId, 'c' => $c->Content ?? '', 'pa' => $c->ParentId, 't' => now()]);
            return $c->Id > 0;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

    public function ToggleTuitionCircularLike(int $postId, int $userId): array
    {
        return $this->togglePostLike('TuitionCircularLikes', 'TuitionCircularPosts', $postId, $userId);
    }

    public function ToggleTuitionCircularCommentLike(int $commentId, int $userId): array
    {
        return $this->toggleCommentLike('TuitionCircularCommentLikes', $commentId, $userId);
    }

    // ============================================================
    // ===== TUITION CIRCULAR — TEACHER APPLICATIONS =====
    // টিচার স্টুডেন্টের পোস্টে Apply করলে সেটা সরাসরি পোস্টদাতার
    // (স্টুডেন্টের) ড্যাশবোর্ডে যায় — অ্যাডমিনের কাছে নয়।
    // ============================================================
    private bool $circularAppsReady = false;

    /** টেবিল না থাকলে নিজে থেকেই তৈরি করে (আলাদা করে SQL ইমপোর্ট লাগবে না) */
    public function EnsureCircularApplicationsTable(): void
    {
        if ($this->circularAppsReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "TuitionCircularApplications" (
                "Id" INTEGER PRIMARY KEY AUTOINCREMENT, "PostId" INTEGER NOT NULL, "TeacherId" INTEGER NOT NULL,
                "Message" TEXT NULL, "PhoneNumber" TEXT NULL, "Status" TEXT NOT NULL DEFAULT \'Pending\',
                "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL, UNIQUE ("PostId", "TeacherId"))');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `TuitionCircularApplications` (
                `Id` INT NOT NULL AUTO_INCREMENT,
                `PostId` INT NOT NULL,
                `TeacherId` INT NOT NULL,
                `Message` TEXT NULL,
                `PhoneNumber` VARCHAR(30) NULL,
                `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`),
                UNIQUE KEY `UX_TCA_PostTeacher` (`PostId`, `TeacherId`),
                KEY `IX_TCA_Teacher` (`TeacherId`),
                CONSTRAINT `FK_TCA_Post` FOREIGN KEY (`PostId`) REFERENCES `TuitionCircularPosts` (`Id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->circularAppsReady = true;
    }

    public function GetTuitionCircularPostById(int $postId): ?Obj
    {
        try {
            $r = $this->one('SELECT p.*, u.`FullName` AS OwnerName, u.`MobileNumber` AS OwnerPhone, u.`Email` AS OwnerEmail
                FROM `TuitionCircularPosts` p LEFT JOIN `Users` u ON p.`UserId` = u.`Id` WHERE p.`Id` = :id', ['id' => $postId]);
            if ($r) { $r->Id = (int)$r->Id; $r->UserId = (int)$r->UserId; }
            return $r;
        } catch (Throwable $e) { return null; }
    }

    /**
     * টিচার একটি সার্কুলার পোস্টে Apply করে।
     * @return string 'ok' | 'exists' | 'own' | 'notfound' | 'error'
     */
    public function ApplyToCircularPost(int $postId, int $teacherId, ?string $message, ?string $phone, ?Obj &$post = null): string
    {
        try {
            $this->EnsureCircularApplicationsTable();
            $post = $this->GetTuitionCircularPostById($postId);
            if (!$post) return 'notfound';
            if ($post->UserId === $teacherId) return 'own';
            $exists = $this->scalar('SELECT COUNT(*) FROM `TuitionCircularApplications` WHERE `PostId` = :p AND `TeacherId` = :t',
                ['p' => $postId, 't' => $teacherId]);
            if ((int)$exists > 0) return 'exists';
            $this->insert('INSERT INTO `TuitionCircularApplications` (`PostId`, `TeacherId`, `Message`, `PhoneNumber`, `Status`, `CreatedAt`)
                VALUES (:p, :t, :m, :ph, \'Pending\', :c)', ['p' => $postId, 't' => $teacherId, 'm' => $message, 'ph' => $phone, 'c' => now()]);
            return 'ok';
        } catch (Throwable $e) {
            error_log('[ApplyToCircularPost] ' . $e->getMessage());
            return 'error';
        }
    }

    /** @return array<int,string> postId => Status (যে পোস্টগুলোতে এই টিচার Apply করেছে) */
    public function GetCircularApplicationStatusesByTeacher(?int $teacherId): array
    {
        if (!$teacherId) return [];
        try {
            $this->EnsureCircularApplicationsTable();
            $map = [];
            foreach ($this->all('SELECT `PostId`, `Status` FROM `TuitionCircularApplications` WHERE `TeacherId` = :t', ['t' => $teacherId]) as $r) {
                $map[(int)$r->PostId] = (string)$r->Status;
            }
            return $map;
        } catch (Throwable $e) { return []; }
    }

    private const CIRCULAR_APP_SELECT = "SELECT a.`Id`, a.`PostId`, a.`TeacherId`, a.`Message`, a.`PhoneNumber`, a.`Status`, a.`CreatedAt`, a.`UpdatedAt`,
            p.`UserId` AS OwnerId, p.`Content` AS PostContent, p.`PreferredArea`, p.`ClassLevel`, p.`Subjects`, p.`Budget`,
            t.`FullName` AS TeacherName, t.`Email` AS TeacherEmail, t.`MobileNumber` AS TeacherMobile, t.`ProfileImageLink` AS TeacherImage,
            t.`EducationLevel` AS TeacherEducationLevel, t.`InstitutionName` AS TeacherInstitution,
            o.`FullName` AS OwnerName, o.`MobileNumber` AS OwnerPhone, o.`Email` AS OwnerEmail,
            tr.`Education` AS TrEducation, tr.`Institution` AS TrInstitution, tr.`SubjectExpertise`, tr.`Experience`, tr.`HourlyRate`
        FROM `TuitionCircularApplications` a
        INNER JOIN `TuitionCircularPosts` p ON a.`PostId` = p.`Id`
        LEFT JOIN `Users` t ON a.`TeacherId` = t.`Id`
        LEFT JOIN `Users` o ON p.`UserId` = o.`Id`
        LEFT JOIN `TeacherRequests` tr ON tr.`Id` = (SELECT MAX(x.`Id`) FROM `TeacherRequests` x WHERE x.`UserId` = a.`TeacherId`)";

    private function mapCircularApp(Obj $r): Obj
    {
        foreach (['Id', 'PostId', 'TeacherId', 'OwnerId'] as $k) $r->$k = (int)$r->$k;
        $r->TeacherName = $r->TeacherName ?? 'Teacher';
        $r->Education = !is_blank($r->TrEducation) ? $r->TrEducation : $r->TeacherEducationLevel;
        $r->Institution = !is_blank($r->TrInstitution) ? $r->TrInstitution : $r->TeacherInstitution;
        $r->Phone = !is_blank($r->PhoneNumber) ? $r->PhoneNumber : $r->TeacherMobile;
        $r->Initials = initials($r->TeacherName, 'T');
        $r->TimeAgo = time_ago($r->CreatedAt);
        $r->PostPreview = str_preview($r->PostContent, 90);
        return $r;
    }

    /** স্টুডেন্টের (পোস্টদাতার) সব পোস্টে আসা টিচার অ্যাপ্লিকেশন @return Obj[] */
    public function GetCircularApplicationsForOwner(int $ownerId): array
    {
        try {
            $this->EnsureCircularApplicationsTable();
            $rows = $this->all(self::CIRCULAR_APP_SELECT . ' WHERE p.`UserId` = :u ORDER BY a.`CreatedAt` DESC, a.`Id` DESC', ['u' => $ownerId]);
            return array_map(fn($r) => $this->mapCircularApp($r), $rows);
        } catch (Throwable $e) { error_log('[GetCircularApplicationsForOwner] ' . $e->getMessage()); return []; }
    }

    /** টিচার নিজে যেসব স্টুডেন্ট পোস্টে Apply করেছে @return Obj[] */
    public function GetCircularApplicationsByTeacher(int $teacherId): array
    {
        try {
            $this->EnsureCircularApplicationsTable();
            $rows = $this->all(self::CIRCULAR_APP_SELECT . ' WHERE a.`TeacherId` = :t ORDER BY a.`CreatedAt` DESC, a.`Id` DESC', ['t' => $teacherId]);
            return array_map(fn($r) => $this->mapCircularApp($r), $rows);
        } catch (Throwable $e) { error_log('[GetCircularApplicationsByTeacher] ' . $e->getMessage()); return []; }
    }

    /** শুধু পোস্টের মালিক স্ট্যাটাস বদলাতে পারবে। সফল হলে অ্যাপ্লিকেশনটি ফেরত দেয়। */
    public function UpdateCircularApplicationStatus(int $appId, int $ownerId, string $status): ?Obj
    {
        try {
            $this->EnsureCircularApplicationsTable();
            $app = $this->one(self::CIRCULAR_APP_SELECT . ' WHERE a.`Id` = :id', ['id' => $appId]);
            if (!$app || (int)$app->OwnerId !== $ownerId) return null;
            $this->exec('UPDATE `TuitionCircularApplications` SET `Status` = :s, `UpdatedAt` = :u WHERE `Id` = :id',
                ['s' => $status, 'u' => now(), 'id' => $appId]);
            $app->Status = $status;
            return $this->mapCircularApp($app);
        } catch (Throwable $e) { error_log('[UpdateCircularApplicationStatus] ' . $e->getMessage()); return null; }
    }

    // ============================================================
    // ===== HELPDESK — POSTS =====
    // ============================================================
    private function mapHelpdeskPost(Obj $r): Obj
    {
        return obj([
            'Id' => (int)$r->Id, 'UserId' => (int)$r->UserId, 'Title' => $r->Title ?? '', 'Content' => $r->Content ?? '',
            'MediaUrl' => $r->MediaUrl, 'MediaType' => $r->MediaType, 'IsAdminPost' => (bool)$r->IsAdminPost,
            'IsAcknowledged' => (bool)$r->IsAcknowledged, 'Status' => $r->Status ?? 'Open',
            'CreatedAt' => $r->CreatedAt, 'UpdatedAt' => $r->UpdatedAt,
            'UserFullName' => $r->FullName ?? 'Unknown', 'UserName' => $r->UserName ?? '', 'UserRole' => $r->Role ?? 'User',
            'UserProfileImage' => $r->ProfileImageLink, 'CommentCount' => (int)$r->CommentCount, 'Comments' => [],
            'Initials' => initials($r->FullName ?? 'U', 'U'), 'TimeAgo' => time_ago($r->CreatedAt),
        ]);
    }

    private const HELPDESK_POST_SELECT = 'SELECT p.`Id`, p.`UserId`, p.`Title`, p.`Content`, p.`MediaUrl`, p.`MediaType`,
            p.`IsAdminPost`, p.`IsAcknowledged`, p.`Status`, p.`CreatedAt`, p.`UpdatedAt`,
            u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink`,
            (SELECT COUNT(*) FROM `HelpdeskComments` WHERE `PostId` = p.`Id`) AS CommentCount
        FROM `HelpdeskPosts` p LEFT JOIN `Users` u ON p.`UserId` = u.`Id`';

    public function CreateHelpdeskPost(Obj $post, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $post->Id = $this->insert("INSERT INTO `HelpdeskPosts` (`UserId`, `Title`, `Content`, `MediaUrl`, `MediaType`, `IsAdminPost`, `IsAcknowledged`, `Status`, `CreatedAt`)
                VALUES (:u, :t, :c, :mu, :mt, :adm, 0, 'Open', :cr)", [
                'u' => (int)$post->UserId, 't' => $post->Title ?? '', 'c' => $post->Content ?? '', 'mu' => $post->MediaUrl,
                'mt' => $post->MediaType, 'adm' => (bool)$post->IsAdminPost, 'cr' => now(),
            ]);
            return $post->Id > 0;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

    /** @return Obj[] */
    public function GetHelpdeskPosts(string $filter = 'all', ?int $userId = null, ?string $search = null): array
    {
        $sql = self::HELPDESK_POST_SELECT . ' WHERE 1=1';
        $p = [];
        if ($filter === 'mine' && $userId !== null) { $sql .= ' AND p.`UserId` = :uid'; $p['uid'] = $userId; }
        if ($filter === 'admin') $sql .= ' AND p.`IsAdminPost` = 1';
        if (!is_blank($search)) {
            $sql .= ' AND (LOWER(p.`Title`) LIKE :s1 OR LOWER(p.`Content`) LIKE :s2)';
            $q = '%' . mb_strtolower(trim($search)) . '%';
            $p['s1'] = $q; $p['s2'] = $q;
        }
        $sql .= ' ORDER BY p.`CreatedAt` DESC, p.`Id` DESC';
        try { return array_map(fn($r) => $this->mapHelpdeskPost($r), $this->all($sql, $p)); } catch (Throwable $e) { return []; }
    }

    public function GetHelpdeskPostById(int $id): ?Obj
    {
        try {
            $r = $this->one(self::HELPDESK_POST_SELECT . ' WHERE p.`Id` = :id', ['id' => $id]);
            return $r ? $this->mapHelpdeskPost($r) : null;
        } catch (Throwable $e) { return null; }
    }

    public function DeleteHelpdeskPost(int $id, int $requestingUserId, bool $isAdmin): bool
    {
        try {
            $owner = $this->scalar('SELECT `UserId` FROM `HelpdeskPosts` WHERE `Id` = :id', ['id' => $id]);
            if ($owner === null || (!$isAdmin && (int)$owner !== $requestingUserId)) return false;
            $this->pdo->beginTransaction();
            $this->exec('DELETE FROM `HelpdeskComments` WHERE `PostId` = :id', ['id' => $id]);
            $n = $this->exec('DELETE FROM `HelpdeskPosts` WHERE `Id` = :id', ['id' => $id]);
            $this->pdo->commit();
            return $n > 0;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }

    public function AcknowledgeHelpdeskPost(int $id, bool $acknowledged = true): bool
    {
        try { return $this->exec('UPDATE `HelpdeskPosts` SET `IsAcknowledged` = :a, `UpdatedAt` = :u WHERE `Id` = :id', ['a' => $acknowledged, 'u' => now(), 'id' => $id]) > 0; } catch (Throwable $e) { return false; }
    }

    public function UpdateHelpdeskPostStatus(int $id, ?string $status): bool
    {
        try { return $this->exec('UPDATE `HelpdeskPosts` SET `Status` = :s, `UpdatedAt` = :u WHERE `Id` = :id', ['s' => $status ?? 'Open', 'u' => now(), 'id' => $id]) > 0; } catch (Throwable $e) { return false; }
    }

    // ============================================================
    // ===== HELPDESK — COMMENTS =====
    // ============================================================
    public function CreateHelpdeskComment(Obj $c, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $c->Id = $this->insert('INSERT INTO `HelpdeskComments` (`PostId`, `UserId`, `Content`, `MediaUrl`, `MediaType`, `IsAdminReply`, `ParentCommentId`, `CreatedAt`)
                VALUES (:p, :u, :c, :mu, :mt, :adm, :pa, :t)', [
                'p' => (int)$c->PostId, 'u' => (int)$c->UserId, 'c' => $c->Content ?? '', 'mu' => $c->MediaUrl, 'mt' => $c->MediaType,
                'adm' => (bool)$c->IsAdminReply, 'pa' => $c->ParentCommentId, 't' => now(),
            ]);
            return $c->Id > 0;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

    /** @return Obj[] top-level comments, প্রতিটিতে Replies */
    public function GetHelpdeskComments(int $postId): array
    {
        try {
            $rows = $this->all('SELECT c.`Id`, c.`PostId`, c.`UserId`, c.`Content`, c.`MediaUrl`, c.`MediaType`, c.`IsAdminReply`,
                    c.`ParentCommentId`, c.`CreatedAt`, u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink`
                FROM `HelpdeskComments` c LEFT JOIN `Users` u ON c.`UserId` = u.`Id`
                WHERE c.`PostId` = :pid ORDER BY c.`CreatedAt` ASC, c.`Id` ASC', ['pid' => $postId]);
        } catch (Throwable $e) { return []; }
        $all = array_map(fn($r) => obj([
            'Id' => (int)$r->Id, 'PostId' => (int)$r->PostId, 'UserId' => (int)$r->UserId, 'Content' => $r->Content ?? '',
            'MediaUrl' => $r->MediaUrl, 'MediaType' => $r->MediaType, 'IsAdminReply' => (bool)$r->IsAdminReply,
            'ParentCommentId' => $r->ParentCommentId === null ? null : (int)$r->ParentCommentId, 'CreatedAt' => $r->CreatedAt,
            'UserFullName' => $r->FullName ?? 'Unknown', 'UserName' => $r->UserName ?? '', 'UserRole' => $r->Role ?? 'User',
            'UserProfileImage' => $r->ProfileImageLink, 'ReplyCount' => 0, 'Replies' => [],
            'Initials' => initials($r->FullName ?? 'U', 'U'), 'TimeAgo' => time_ago($r->CreatedAt),
        ]), $rows);
        $top = array_values(array_filter($all, fn($c) => $c->ParentCommentId === null));
        foreach ($top as $parent) {
            $parent->Replies = array_values(array_filter($all, fn($c) => $c->ParentCommentId === $parent->Id));
            $parent->ReplyCount = count($parent->Replies);
        }
        return $top;
    }

    public function DeleteHelpdeskComment(int $commentId, int $requestingUserId, bool $isAdmin): bool
    {
        try {
            $owner = $this->scalar('SELECT `UserId` FROM `HelpdeskComments` WHERE `Id` = :id', ['id' => $commentId]);
            if ($owner === null || (!$isAdmin && (int)$owner !== $requestingUserId)) return false;
            $this->exec('DELETE FROM `HelpdeskComments` WHERE `ParentCommentId` = :id', ['id' => $commentId]);
            return $this->exec('DELETE FROM `HelpdeskComments` WHERE `Id` = :id', ['id' => $commentId]) > 0;
        } catch (Throwable $e) { return false; }
    }
}
