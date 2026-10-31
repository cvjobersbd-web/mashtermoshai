<?php
/**
 * =============================================================
 *  Db — C# DatabaseHelper.cs এর PHP (PDO/MySQL) ভার্সন
 *  মেথডের নাম C# এর মতোই রাখা হয়েছে।
 *  প্রতিটি row একটি Obj (stdClass) হিসেবে ফেরত আসে, যার
 *  property নাম ডাটাবেস কলামের নামের মতো (PascalCase)।
 * =============================================================
 */
require_once __DIR__ . '/TeacherProfileData.php';
require_once __DIR__ . '/CircularData.php';
require_once __DIR__ . '/FindTutorData.php';
require_once __DIR__ . '/PackageData.php';
require_once __DIR__ . '/PaymentData.php';

class Db
{
    use TeacherProfileData;
    use CircularData;
    use FindTutorData;
    use PackageData;
    use PaymentData;

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
        // ✅ Username, Email অথবা মোবাইল নম্বর — যেকোনোটা দিয়ে লগইন
        $id = trim($username);
        if ($id === '' || $password === '') return null;
        $cols = 'SELECT ' . self::USER_COLUMNS . ' FROM `Users` WHERE `IsActive` = 1 AND ';
        $candidates = $this->all($cols . 'LOWER(`UserName`) = LOWER(:u)', ['u' => $id]);
        if (!$candidates && str_contains($id, '@')) {
            $candidates = $this->all($cols . 'LOWER(`Email`) = LOWER(:e) ORDER BY `Id`', ['e' => $id]);
        }
        if (!$candidates && ($digits = phone_digits($id)) !== '') {
            // শেষ ১০ ডিজিট দিয়ে খুঁজে PHP তে মিলিয়ে দেখা (+880 / 0 / স্পেস / ড্যাশ যেভাবেই লেখা থাকুক)
            // প্রতিটা ডিজিটের মাঝে % — তাই নম্বরে স্পেস/ড্যাশ থাকলেও খুঁজে পাবে
            $rows = $this->all($cols . '`MobileNumber` LIKE :m ORDER BY `Id` LIMIT 50', ['m' => '%' . implode('%', str_split(substr($digits, -10))) . '%']);
            $candidates = array_values(array_filter($rows, fn($r) => phone_digits((string)$r->MobileNumber) === $digits));
        }
        foreach ($candidates as $u) {
            if ($this->verifyUserPassword($u, $password)) return $this->mapUser($u);
        }
        return null;
    }

    /** পুরোনো (plain text) পাসওয়ার্ড এবং নতুন (hashed) — দুটোই সাপোর্ট করে; plain text মিললে hash করে সেভ */
    private function verifyUserPassword(Obj $u, string $password): bool
    {
        $stored = (string)$u->Password;
        $info = password_get_info($stored);
        if (!empty($info['algo'])) return password_verify($password, $stored);
        if ($stored !== '' && hash_equals($stored, $password)) {
            try { $this->exec('UPDATE `Users` SET `Password` = :p WHERE `Id` = :id', ['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => (int)$u->Id]); } catch (Throwable $e) {}
            return true;
        }
        return false;
    }

    // ============================================================
    // ===== PASSWORD RESET (Forgot Password — ইমেইলে লিংক) =====
    // ============================================================
    private bool $resetReady = false;

    public function EnsurePasswordResetsTable(): void
    {
        if ($this->resetReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PasswordResets" (
                "Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "TokenHash" TEXT NOT NULL,
                "ExpiresAt" TEXT NOT NULL, "UsedAt" TEXT NULL, "RequestIp" TEXT NULL, "CreatedAt" TEXT NOT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PasswordResets` (
                `Id` INT NOT NULL AUTO_INCREMENT,
                `UserId` INT NOT NULL,
                `TokenHash` CHAR(64) NOT NULL,
                `ExpiresAt` DATETIME NOT NULL,
                `UsedAt` DATETIME NULL,
                `RequestIp` VARCHAR(64) NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`),
                UNIQUE KEY `UX_PasswordResets_Token` (`TokenHash`),
                KEY `IX_PasswordResets_User` (`UserId`, `CreatedAt`),
                KEY `IX_PasswordResets_Ip` (`RequestIp`, `CreatedAt`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->resetReady = true;
    }

    /** @return Obj[] রিসেট লিংক পাঠানোর মতো অ্যাকাউন্ট (ইমেইল বা ইউজারনেম দিয়ে) */
    public function FindUsersForReset(string $emailOrUsername): array
    {
        $v = trim($emailOrUsername);
        if ($v === '') return [];
        $sql = 'SELECT `Id`, `UserName`, `FullName`, `Email` FROM `Users` WHERE `IsActive` = 1 AND `Email` IS NOT NULL AND `Email` <> \'\' AND ';
        return str_contains($v, '@')
            ? $this->all($sql . 'LOWER(`Email`) = LOWER(:v) ORDER BY `Id` LIMIT 5', ['v' => $v])
            : $this->all($sql . 'LOWER(`UserName`) = LOWER(:v) LIMIT 1', ['v' => $v]);
    }

    public function CountRecentResets(?int $userId, ?string $ip, int $minutes): int
    {
        $this->EnsurePasswordResetsTable();
        $since = date('Y-m-d H:i:s', time() - $minutes * 60);
        if ($userId !== null) return (int)$this->scalar('SELECT COUNT(*) FROM `PasswordResets` WHERE `UserId` = :u AND `CreatedAt` >= :s', ['u' => $userId, 's' => $since]);
        return (int)$this->scalar('SELECT COUNT(*) FROM `PasswordResets` WHERE `RequestIp` = :ip AND `CreatedAt` >= :s', ['ip' => (string)$ip, 's' => $since]);
    }

    /** নতুন টোকেন বানায় (ডাটাবেসে শুধু hash থাকে) — আসল টোকেন রিটার্ন করে */
    public function CreatePasswordReset(int $userId, string $ip, int $validMinutes = 30): string
    {
        $this->EnsurePasswordResetsTable();
        $token = bin2hex(random_bytes(32));
        // আগের না-ব্যবহৃত লিংকগুলো বাতিল
        $this->exec('UPDATE `PasswordResets` SET `UsedAt` = :t WHERE `UserId` = :u AND `UsedAt` IS NULL', ['t' => now(), 'u' => $userId]);
        $this->insert('INSERT INTO `PasswordResets` (`UserId`, `TokenHash`, `ExpiresAt`, `RequestIp`, `CreatedAt`) VALUES (:u, :h, :x, :ip, :c)', [
            'u' => $userId, 'h' => hash('sha256', $token), 'x' => date('Y-m-d H:i:s', time() + $validMinutes * 60), 'ip' => mb_substr($ip, 0, 64), 'c' => now(),
        ]);
        return $token;
    }

    /** বৈধ (মেয়াদ আছে, ব্যবহার হয়নি) টোকেন হলে রিসেট + ইউজারের তথ্য */
    public function GetValidPasswordReset(string $token): ?Obj
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
        $this->EnsurePasswordResetsTable();
        return $this->one('SELECT r.`Id` AS ResetId, r.`UserId`, r.`ExpiresAt`, u.`UserName`, u.`FullName`, u.`Email`
            FROM `PasswordResets` r JOIN `Users` u ON u.`Id` = r.`UserId`
            WHERE r.`TokenHash` = :h AND r.`UsedAt` IS NULL AND r.`ExpiresAt` > :n AND u.`IsActive` = 1', ['h' => hash('sha256', $token), 'n' => now()]);
    }

    public function CompletePasswordReset(int $resetId, int $userId, string $newPassword): bool
    {
        $this->pdo->beginTransaction();
        try {
            $n = $this->exec('UPDATE `PasswordResets` SET `UsedAt` = :t WHERE `Id` = :id AND `UsedAt` IS NULL', ['t' => now(), 'id' => $resetId]);
            if ($n < 1) { $this->pdo->rollBack(); return false; }   // একই লিংক দুবার ব্যবহার করা যাবে না
            $this->exec('UPDATE `Users` SET `Password` = :p WHERE `Id` = :u', ['p' => password_hash($newPassword, PASSWORD_DEFAULT), 'u' => $userId]);
            $this->exec('UPDATE `PasswordResets` SET `UsedAt` = :t WHERE `UserId` = :u AND `UsedAt` IS NULL', ['t' => now(), 'u' => $userId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) { $this->pdo->rollBack(); error_log('[CompletePasswordReset] ' . $e->getMessage()); return false; }
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
            if ($n > 0) { $this->DeleteFriendshipsOfUser($userId); $this->DeleteProfileContentOfUser($userId); $this->DeleteTeacherDataOf($userId); }
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
    // ============================================================
    // ===== USER SUSPENSION (অ্যাডমিন নির্দিষ্ট সময় বা স্থায়ীভাবে Suspend করে) =====
    // ============================================================
    private bool $suspensionsReady = false;

    public function EnsureSuspensionsTable(): void
    {
        if ($this->suspensionsReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "UserSuspensions" (
                "Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "Reason" TEXT NULL,
                "SuspendedUntil" TEXT NULL, "IsPermanent" INTEGER NOT NULL DEFAULT 0, "CreatedBy" INTEGER NULL,
                "CreatedAt" TEXT NOT NULL, "LiftedAt" TEXT NULL, "LiftedBy" INTEGER NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `UserSuspensions` (
                `Id` INT NOT NULL AUTO_INCREMENT,
                `UserId` INT NOT NULL,
                `Reason` TEXT NULL,
                `SuspendedUntil` DATETIME NULL,
                `IsPermanent` TINYINT(1) NOT NULL DEFAULT 0,
                `CreatedBy` INT NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `LiftedAt` DATETIME NULL,
                `LiftedBy` INT NULL,
                PRIMARY KEY (`Id`),
                KEY `IX_UserSuspensions_User` (`UserId`, `LiftedAt`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->suspensionsReady = true;
    }

    private const ACTIVE_SUSPENSION_WHERE = '`LiftedAt` IS NULL AND (`IsPermanent` = 1 OR `SuspendedUntil` > :now)';

    private function mapSuspension(Obj $r): Obj
    {
        $r->Id = (int)$r->Id; $r->UserId = (int)$r->UserId; $r->IsPermanent = (bool)(int)$r->IsPermanent;
        return $r;
    }

    /** এই মুহূর্তে চালু থাকা Suspension (না থাকলে null) */
    public function GetActiveSuspension(int $userId): ?Obj
    {
        try {
            $this->EnsureSuspensionsTable();
            $r = $this->one('SELECT * FROM `UserSuspensions` WHERE `UserId` = :u AND ' . self::ACTIVE_SUSPENSION_WHERE . ' ORDER BY `Id` DESC LIMIT 1',
                ['u' => $userId, 'now' => now()]);
            return $r ? $this->mapSuspension($r) : null;
        } catch (Throwable $e) { error_log('[GetActiveSuspension] ' . $e->getMessage()); return null; }
    }

    /** @return array<int,Obj> userId => চালু Suspension (অ্যাডমিন তালিকার জন্য) */
    public function GetActiveSuspensionsMap(): array
    {
        try {
            $this->EnsureSuspensionsTable();
            $map = [];
            foreach ($this->all('SELECT * FROM `UserSuspensions` WHERE ' . self::ACTIVE_SUSPENSION_WHERE . ' ORDER BY `Id`', ['now' => now()]) as $r) {
                $r = $this->mapSuspension($r);
                $map[$r->UserId] = $r;
            }
            return $map;
        } catch (Throwable $e) { return []; }
    }

    public function SuspendUser(int $userId, ?string $until, bool $permanent, ?string $reason, ?int $adminId): bool
    {
        try {
            $this->EnsureSuspensionsTable();
            $this->exec('UPDATE `UserSuspensions` SET `LiftedAt` = :t, `LiftedBy` = :a WHERE `UserId` = :u AND `LiftedAt` IS NULL',
                ['t' => now(), 'a' => $adminId, 'u' => $userId]);
            $this->insert('INSERT INTO `UserSuspensions` (`UserId`, `Reason`, `SuspendedUntil`, `IsPermanent`, `CreatedBy`, `CreatedAt`)
                VALUES (:u, :r, :until, :p, :a, :t)',
                ['u' => $userId, 'r' => $reason, 'until' => $permanent ? null : $until, 'p' => $permanent ? 1 : 0, 'a' => $adminId, 't' => now()]);
            return true;
        } catch (Throwable $e) { error_log('[SuspendUser] ' . $e->getMessage()); return false; }
    }

    public function LiftSuspension(int $userId, ?int $adminId): bool
    {
        try {
            $this->EnsureSuspensionsTable();
            return $this->exec('UPDATE `UserSuspensions` SET `LiftedAt` = :t, `LiftedBy` = :a WHERE `UserId` = :u AND `LiftedAt` IS NULL',
                ['t' => now(), 'a' => $adminId, 'u' => $userId]) > 0;
        } catch (Throwable $e) { return false; }
    }

    // ============================================================
    // ===== REVIEWS (স্টুডেন্ট/টিচারের রিভিউ → অ্যাডমিন Approve → হোম পেজ) =====
    // ============================================================
    private bool $reviewsReady = false;

    public function EnsureReviewsTable(): void
    {
        if ($this->reviewsReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "Reviews" (
                "Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL UNIQUE, "Rating" INTEGER NOT NULL DEFAULT 5,
                "Subtitle" TEXT NULL, "Content" TEXT NOT NULL, "Status" TEXT NOT NULL DEFAULT \'Pending\', "AdminNote" TEXT NULL,
                "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `Reviews` (
                `Id` INT NOT NULL AUTO_INCREMENT,
                `UserId` INT NOT NULL,
                `Rating` TINYINT NOT NULL DEFAULT 5,
                `Subtitle` VARCHAR(120) NULL,
                `Content` TEXT NOT NULL,
                `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
                `AdminNote` TEXT NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`),
                UNIQUE KEY `UX_Reviews_User` (`UserId`),
                KEY `IX_Reviews_Status` (`Status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        // রিভিউ এখন সরাসরি হোম পেজে যায় — আগের Pending গুলোও প্রকাশ করে দাও
        try { $this->pdo->exec("UPDATE `Reviews` SET `Status` = 'Approved' WHERE `Status` = 'Pending'"); } catch (Throwable $e) { }
        $this->reviewsReady = true;
    }

    private const REVIEW_SELECT = 'SELECT r.`Id`, r.`UserId`, r.`Rating`, r.`Subtitle`, r.`Content`, r.`Status`, r.`AdminNote`, r.`CreatedAt`, r.`UpdatedAt`,
            u.`FullName`, u.`UserName`, u.`Email`, u.`Role`, u.`ProfileImageLink`, u.`InstitutionName`
        FROM `Reviews` r LEFT JOIN `Users` u ON r.`UserId` = u.`Id`';

    private function mapReview(Obj $r): Obj
    {
        $r->Id = (int)$r->Id; $r->UserId = (int)$r->UserId; $r->Rating = max(1, min(5, (int)$r->Rating));
        $r->FullName = $r->FullName ?: ($r->UserName ?: 'User');
        $r->RoleLabel = $r->Role === 'Teacher' ? 'Tutor' : ($r->Role === 'Admin' ? 'Admin' : 'Student / Parent');
        $r->Initials = initials($r->FullName, 'U');
        $r->TimeAgo = time_ago($r->UpdatedAt ?: $r->CreatedAt);
        return $r;
    }

    public function GetReviewByUser(int $userId): ?Obj
    {
        try {
            $this->EnsureReviewsTable();
            $r = $this->one(self::REVIEW_SELECT . ' WHERE r.`UserId` = :u', ['u' => $userId]);
            return $r ? $this->mapReview($r) : null;
        } catch (Throwable $e) { error_log('[GetReviewByUser] ' . $e->getMessage()); return null; }
    }

    /** নতুন রিভিউ বা আগেরটা আপডেট — আপডেট করলে আবার Pending হয়ে অ্যাডমিনের কাছে যায় */
    public function SaveUserReview(int $userId, int $rating, ?string $subtitle, string $content, ?bool &$isNew = null): bool
    {
        try {
            $this->EnsureReviewsTable();
            $existing = $this->scalar('SELECT `Id` FROM `Reviews` WHERE `UserId` = :u', ['u' => $userId]);
            $p = ['u' => $userId, 'r' => max(1, min(5, $rating)), 's' => $subtitle, 'c' => $content, 't' => now()];
            if ($existing) {
                $isNew = false;
                $this->exec("UPDATE `Reviews` SET `Rating` = :r, `Subtitle` = :s, `Content` = :c, `Status` = 'Approved', `AdminNote` = NULL, `UpdatedAt` = :t WHERE `UserId` = :u", $p);
            } else {
                $isNew = true;
                $this->insert("INSERT INTO `Reviews` (`UserId`, `Rating`, `Subtitle`, `Content`, `Status`, `CreatedAt`) VALUES (:u, :r, :s, :c, 'Approved', :t)", $p);
            }
            return true;
        } catch (Throwable $e) { error_log('[SaveUserReview] ' . $e->getMessage()); return false; }
    }

    /** @return Obj[] */
    public function GetReviews(?string $status = null): array
    {
        try {
            $this->EnsureReviewsTable();
            $sql = self::REVIEW_SELECT;
            $p = [];
            if ($status !== null && $status !== 'all') { $sql .= ' WHERE r.`Status` = :s'; $p['s'] = $status; }
            $sql .= " ORDER BY CASE r.`Status` WHEN 'Pending' THEN 0 WHEN 'Approved' THEN 1 ELSE 2 END, COALESCE(r.`UpdatedAt`, r.`CreatedAt`) DESC";
            return array_map(fn($r) => $this->mapReview($r), $this->all($sql, $p));
        } catch (Throwable $e) { error_log('[GetReviews] ' . $e->getMessage()); return []; }
    }

    /** @return Obj[] হোম পেজের জন্য */
    public function GetApprovedReviews(int $limit = 12): array
    {
        try {
            $this->EnsureReviewsTable();
            $limit = max(1, min(50, $limit));
            $rows = $this->all(self::REVIEW_SELECT . " WHERE r.`Status` = 'Approved' AND u.`Id` IS NOT NULL AND u.`IsActive` = 1
                ORDER BY COALESCE(r.`UpdatedAt`, r.`CreatedAt`) DESC LIMIT $limit");
            return array_map(fn($r) => $this->mapReview($r), $rows);
        } catch (Throwable $e) { error_log('[GetApprovedReviews] ' . $e->getMessage()); return []; }
    }

    public function CountReviews(?string $status = null): int
    {
        try {
            $this->EnsureReviewsTable();
            return $status === null ? (int)$this->scalar('SELECT COUNT(*) FROM `Reviews`')
                : (int)$this->scalar('SELECT COUNT(*) FROM `Reviews` WHERE `Status` = :s', ['s' => $status]);
        } catch (Throwable $e) { return 0; }
    }

    public function UpdateReviewStatus(int $id, string $status, ?string $note = null): ?Obj
    {
        try {
            $this->EnsureReviewsTable();
            $n = $this->exec('UPDATE `Reviews` SET `Status` = :s, `AdminNote` = :n, `UpdatedAt` = :t WHERE `Id` = :id',
                ['s' => $status, 'n' => $note, 't' => now(), 'id' => $id]);
            if ($n < 1) return null;
            $r = $this->one(self::REVIEW_SELECT . ' WHERE r.`Id` = :id', ['id' => $id]);
            return $r ? $this->mapReview($r) : null;
        } catch (Throwable $e) { error_log('[UpdateReviewStatus] ' . $e->getMessage()); return null; }
    }

    public function DeleteReview(int $id): bool
    {
        try { $this->EnsureReviewsTable(); return $this->exec('DELETE FROM `Reviews` WHERE `Id` = :id', ['id' => $id]) > 0; }
        catch (Throwable $e) { return false; }
    }

    // ============================================================
    // ===== SITE CONTENT (অ্যাডমিন থেকে এডিট করা যায় এমন লেখা) =====
    // ============================================================
    private bool $siteContentReady = false;

    public function EnsureSiteContentTable(): void
    {
        if ($this->siteContentReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "SiteContents" ("ContentKey" TEXT PRIMARY KEY, "ContentValue" TEXT NULL, "UpdatedAt" TEXT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `SiteContents` (
                `ContentKey` VARCHAR(100) NOT NULL,
                `ContentValue` TEXT NULL,
                `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`ContentKey`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->siteContentReady = true;
    }

    /** @param array<string,string> $defaults  @return array<string,string> (ডাটাবেসে না থাকলে ডিফল্ট) */
    public function GetSiteContent(array $defaults): array
    {
        try {
            $this->EnsureSiteContentTable();
            if (!$defaults) return [];
            $keys = array_keys($defaults);
            $in = implode(',', array_fill(0, count($keys), '?'));
            $st = $this->pdo->prepare("SELECT `ContentKey`, `ContentValue` FROM `SiteContents` WHERE `ContentKey` IN ($in)");
            $st->execute($keys);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if ($r['ContentValue'] !== null) $defaults[$r['ContentKey']] = (string)$r['ContentValue'];
            }
        } catch (Throwable $e) { error_log('[GetSiteContent] ' . $e->getMessage()); }
        return $defaults;
    }

    /** @param array<string,string> $values */
    public function SaveSiteContent(array $values, ?string &$errorMessage = null): bool
    {
        $errorMessage = null;
        try {
            $this->EnsureSiteContentTable();
            foreach ($values as $k => $v) {
                $exists = (int)$this->scalar('SELECT COUNT(*) FROM `SiteContents` WHERE `ContentKey` = :k', ['k' => $k]) > 0;
                $this->exec($exists
                    ? 'UPDATE `SiteContents` SET `ContentValue` = :v, `UpdatedAt` = :t WHERE `ContentKey` = :k'
                    : 'INSERT INTO `SiteContents` (`ContentKey`, `ContentValue`, `UpdatedAt`) VALUES (:k, :v, :t)',
                    ['k' => $k, 'v' => $v, 't' => now()]);
            }
            return true;
        } catch (Throwable $e) { $errorMessage = $e->getMessage(); return false; }
    }

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
                'site' => is_blank($s->SiteName) ? 'Mashter Moshai' : trim($s->SiteName),
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
            $this->EnsureTutorDirectory();
            $rows = $this->all('SELECT p.`Id`, p.`UserId`, p.`Content`, p.`ImageUrl`, p.`VideoUrl`, p.`LikeCount`, p.`CreatedAt`,
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
            $this->EnsureTutorDirectory();
            $post->Id = $this->insert('INSERT INTO `TeachWantedPosts` (`UserId`, `Content`, `ImageUrl`, `VideoUrl`, `LikeCount`, `CreatedAt`) VALUES (:u, :c, :i, :v, 0, :t)',
                ['u' => (int)$post->UserId, 'c' => $post->Content ?? '', 'i' => $post->ImageUrl, 'v' => $post->VideoUrl ?? null, 't' => now()]);
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

    public ?string $lastDeletedTeachVideo = null;

    public function DeleteTeachWantedPost(int $postId, int $requesterId, bool $isAdmin, ?string &$imageUrl = null, ?string &$errorMessage = null): bool
    {
        $imageUrl = $errorMessage = null;
        try {
            $this->EnsureTutorDirectory();
            $r = $this->one('SELECT `UserId`, `ImageUrl`, `VideoUrl` FROM `TeachWantedPosts` WHERE `Id` = :id', ['id' => $postId]);
            if (!$r) { $errorMessage = 'Post not found.'; return false; }
            $imageUrl = $r->ImageUrl;
            $this->lastDeletedTeachVideo = $r->VideoUrl;
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
            $this->EnsureCircularExtras();
            $post->Id = $this->insert('INSERT INTO `TuitionCircularPosts` (`UserId`, `Content`, `ImageUrl`, `PreferredArea`, `ClassLevel`, `Subjects`, `Budget`, `LikeCount`, `CreatedAt`,
                    `Title`, `Medium`, `TuitionType`, `VideoUrl`, `ViewCount`)
                VALUES (:u, :c, :i, :a, :cl, :s, :b, 0, :t, :ti, :me, :ty, :v, 0)', [
                'u' => (int)$post->UserId, 'c' => $post->Content ?? '', 'i' => $post->ImageUrl, 'a' => $post->PreferredArea,
                'cl' => $post->ClassLevel, 's' => $post->Subjects, 'b' => $post->Budget, 't' => now(),
                'ti' => $post->Title ?? null, 'me' => $post->Medium ?? null, 'ty' => $post->TuitionType ?? null, 'v' => $post->VideoUrl ?? null,
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

    // ============================================================
    // ===== FRIENDS (স্টুডেন্ট ↔ স্টুডেন্ট, টিচার ↔ টিচার, টিচার ↔ স্টুডেন্ট) =====
    // প্রতি জোড়া ইউজারের জন্য একটাই সারি (UserLow, UserHigh ইউনিক)।
    // Pending = রিকোয়েস্ট পাঠানো হয়েছে, Accepted = ফ্রেন্ড।
    // Decline / Cancel / Unfriend করলে সারিটা মুছে যায় — পরে আবার রিকোয়েস্ট দেওয়া যায়।
    // ============================================================
    private bool $friendsReady = false;

    public function EnsureFriendshipsTable(): void
    {
        if ($this->friendsReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "Friendships" (
                "Id" INTEGER PRIMARY KEY AUTOINCREMENT, "RequesterId" INTEGER NOT NULL, "AddresseeId" INTEGER NOT NULL,
                "UserLow" INTEGER NOT NULL, "UserHigh" INTEGER NOT NULL, "Status" TEXT NOT NULL DEFAULT \'Pending\',
                "CreatedAt" TEXT NOT NULL, "RespondedAt" TEXT NULL, UNIQUE ("UserLow", "UserHigh"))');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `Friendships` (
                `Id` INT NOT NULL AUTO_INCREMENT,
                `RequesterId` INT NOT NULL,
                `AddresseeId` INT NOT NULL,
                `UserLow` INT NOT NULL,
                `UserHigh` INT NOT NULL,
                `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `RespondedAt` DATETIME NULL,
                PRIMARY KEY (`Id`),
                UNIQUE KEY `UX_Friendships_Pair` (`UserLow`, `UserHigh`),
                KEY `IX_Friendships_Addressee` (`AddresseeId`, `Status`),
                KEY `IX_Friendships_Requester` (`RequesterId`, `Status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->friendsReady = true;
    }

    private const FRIEND_USER_COLS = 'u.`Id` AS PersonId, u.`UserName` AS PersonUserName, u.`FullName` AS PersonName, u.`Role` AS PersonRole,
        u.`ProfileImageLink` AS PersonImage, u.`InstitutionName` AS PersonInstitution, u.`EducationLevel` AS PersonEducation,
        u.`Country` AS PersonCountry';

    /** ফ্রেন্ড কার্ডে দেখানোর জন্য একজন মানুষের তথ্য গুছিয়ে দেয় */
    private function mapFriendPerson(Obj $r): Obj
    {
        $name = (string)($r->PersonName ?? '');
        if ($name === '') $name = (string)($r->PersonUserName ?? 'User');
        $role = (string)($r->PersonRole ?? 'User');
        return obj([
            'Id' => (int)$r->PersonId,
            'FriendshipId' => isset($r->FriendshipId) ? (int)$r->FriendshipId : null,
            'UserName' => (string)($r->PersonUserName ?? ''),
            'FullName' => $name,
            'Role' => $role,
            'RoleLabel' => $role === 'Teacher' ? 'Teacher' : 'Student',
            'Image' => is_blank($r->PersonImage ?? null) ? null : (string)$r->PersonImage,
            'Initials' => initials($name, 'U'),
            'Institution' => (string)($r->PersonInstitution ?? ''),
            'Education' => (string)($r->PersonEducation ?? ''),
            'Since' => $r->Since ?? null,
            'TimeAgo' => isset($r->Since) ? time_ago($r->Since) : '',
            'Relation' => $r->Relation ?? 'none',
        ]);
    }

    /** দুজনের মধ্যে সারি (না থাকলে null) */
    public function GetFriendship(int $a, int $b): ?Obj
    {
        $this->EnsureFriendshipsTable();
        $r = $this->one('SELECT * FROM `Friendships` WHERE `UserLow` = :l AND `UserHigh` = :h', ['l' => min($a, $b), 'h' => max($a, $b)]);
        if ($r) { foreach (['Id', 'RequesterId', 'AddresseeId'] as $k) $r->$k = (int)$r->$k; }
        return $r;
    }

    /** আমার দিক থেকে অন্যজনের সাথে সম্পর্ক: self | none | friends | sent | received */
    public function GetFriendRelation(int $me, int $other): string
    {
        if ($me === $other) return 'self';
        try {
            $f = $this->GetFriendship($me, $other);
        } catch (Throwable $e) { return 'none'; }
        if (!$f) return 'none';
        if ($f->Status === 'Accepted') return 'friends';
        return $f->RequesterId === $me ? 'sent' : 'received';
    }

    /** ফ্রেন্ড হওয়ার যোগ্য কিনা (অ্যাডমিন বা নিষ্ক্রিয়/সাসপেন্ড অ্যাকাউন্ট নয়) */
    public function IsFriendEligibleUser(int $userId): bool
    {
        $u = $this->one('SELECT `Id`, `Role`, `IsActive` FROM `Users` WHERE `Id` = :id', ['id' => $userId]);
        if (!$u || ($u->Role ?? 'User') === 'Admin') return false;
        if ($u->IsActive !== null && !(bool)(int)$u->IsActive) return false;
        return $this->GetActiveSuspension($userId) === null;
    }

    /** শেষ X মিনিটে কয়টা রিকোয়েস্ট পাঠিয়েছে (স্প্যাম ঠেকাতে) */
    public function CountRecentFriendRequests(int $me, int $minutes): int
    {
        $this->EnsureFriendshipsTable();
        return (int)$this->scalar('SELECT COUNT(*) FROM `Friendships` WHERE `RequesterId` = :u AND `CreatedAt` >= :t',
            ['u' => $me, 't' => date('Y-m-d H:i:s', time() - $minutes * 60)]);
    }

    /**
     * ফ্রেন্ড রিকোয়েস্ট পাঠায়। অন্যজন আগেই আমাকে পাঠিয়ে থাকলে সরাসরি Accept হয়ে যায়।
     * @return string 'sent' | 'accepted' | 'already_sent' | 'already_friends' | 'error'
     */
    public function SendFriendRequest(int $me, int $other): string
    {
        try {
            $this->EnsureFriendshipsTable();
            $f = $this->GetFriendship($me, $other);
            if ($f) {
                if ($f->Status === 'Accepted') return 'already_friends';
                if ($f->RequesterId === $me) return 'already_sent';
                $this->exec('UPDATE `Friendships` SET `Status` = \'Accepted\', `RespondedAt` = :t WHERE `Id` = :id', ['t' => now(), 'id' => $f->Id]);
                return 'accepted';
            }
            $this->insert('INSERT INTO `Friendships` (`RequesterId`, `AddresseeId`, `UserLow`, `UserHigh`, `Status`, `CreatedAt`)
                VALUES (:r, :a, :l, :h, \'Pending\', :t)',
                ['r' => $me, 'a' => $other, 'l' => min($me, $other), 'h' => max($me, $other), 't' => now()]);
            return 'sent';
        } catch (Throwable $e) {
            error_log('[SendFriendRequest] ' . $e->getMessage());
            // দুইজন একই সময়ে চাপলে ইউনিক কী-তে আটকায় — তখন বর্তমান অবস্থা ফেরত দিই
            $rel = $this->GetFriendRelation($me, $other);
            return $rel === 'friends' ? 'already_friends' : ($rel === 'sent' ? 'already_sent' : 'error');
        }
    }

    /** আমার কাছে আসা রিকোয়েস্ট Accept / Decline। সফল হলে রিকোয়েস্ট পাঠানো ইউজারের Id দেয়। */
    public function RespondFriendRequest(int $me, int $other, bool $accept): ?int
    {
        try {
            $f = $this->GetFriendship($me, $other);
            if (!$f || $f->Status !== 'Pending' || $f->AddresseeId !== $me) return null;
            if ($accept) {
                $this->exec('UPDATE `Friendships` SET `Status` = \'Accepted\', `RespondedAt` = :t WHERE `Id` = :id', ['t' => now(), 'id' => $f->Id]);
            } else {
                $this->exec('DELETE FROM `Friendships` WHERE `Id` = :id', ['id' => $f->Id]);
            }
            return $f->RequesterId;
        } catch (Throwable $e) { error_log('[RespondFriendRequest] ' . $e->getMessage()); return null; }
    }

    /** নিজের পাঠানো রিকোয়েস্ট ফিরিয়ে নেওয়া */
    public function CancelFriendRequest(int $me, int $other): bool
    {
        try {
            $this->EnsureFriendshipsTable();
            return $this->exec('DELETE FROM `Friendships` WHERE `UserLow` = :l AND `UserHigh` = :h AND `Status` = \'Pending\' AND `RequesterId` = :me',
                ['l' => min($me, $other), 'h' => max($me, $other), 'me' => $me]) > 0;
        } catch (Throwable $e) { return false; }
    }

    public function Unfriend(int $me, int $other): bool
    {
        try {
            $this->EnsureFriendshipsTable();
            return $this->exec('DELETE FROM `Friendships` WHERE `UserLow` = :l AND `UserHigh` = :h AND `Status` = \'Accepted\'',
                ['l' => min($me, $other), 'h' => max($me, $other)]) > 0;
        } catch (Throwable $e) { return false; }
    }

    /** @return Obj[] আমার ফ্রেন্ড লিস্ট */
    public function GetFriends(int $me): array
    {
        try {
            $this->EnsureFriendshipsTable();
            $rows = $this->all('SELECT f.`Id` AS FriendshipId, COALESCE(f.`RespondedAt`, f.`CreatedAt`) AS Since, \'friends\' AS Relation, ' . self::FRIEND_USER_COLS . '
                FROM `Friendships` f
                INNER JOIN `Users` u ON u.`Id` = CASE WHEN f.`UserLow` = :me1 THEN f.`UserHigh` ELSE f.`UserLow` END
                WHERE (f.`UserLow` = :me2 OR f.`UserHigh` = :me3) AND f.`Status` = \'Accepted\'
                ORDER BY u.`FullName`', ['me1' => $me, 'me2' => $me, 'me3' => $me]);
            return array_map(fn($r) => $this->mapFriendPerson($r), $rows);
        } catch (Throwable $e) { error_log('[GetFriends] ' . $e->getMessage()); return []; }
    }

    /** @return int[] আমার ফ্রেন্ডদের Id */
    public function GetFriendIds(int $me): array
    {
        try {
            $this->EnsureFriendshipsTable();
            $ids = [];
            foreach ($this->all('SELECT `UserLow`, `UserHigh` FROM `Friendships` WHERE (`UserLow` = :a OR `UserHigh` = :b) AND `Status` = \'Accepted\'',
                ['a' => $me, 'b' => $me]) as $r) {
                $ids[] = (int)$r->UserLow === $me ? (int)$r->UserHigh : (int)$r->UserLow;
            }
            return $ids;
        } catch (Throwable $e) { return []; }
    }

    /** @return Obj[] আমার কাছে আসা রিকোয়েস্ট */
    public function GetIncomingFriendRequests(int $me): array
    {
        try {
            $this->EnsureFriendshipsTable();
            $rows = $this->all('SELECT f.`Id` AS FriendshipId, f.`CreatedAt` AS Since, \'received\' AS Relation, ' . self::FRIEND_USER_COLS . '
                FROM `Friendships` f INNER JOIN `Users` u ON u.`Id` = f.`RequesterId`
                WHERE f.`AddresseeId` = :me AND f.`Status` = \'Pending\' ORDER BY f.`CreatedAt` DESC, f.`Id` DESC', ['me' => $me]);
            return array_map(fn($r) => $this->mapFriendPerson($r), $rows);
        } catch (Throwable $e) { error_log('[GetIncomingFriendRequests] ' . $e->getMessage()); return []; }
    }

    /** @return Obj[] আমার পাঠানো (এখনো Accept হয়নি) রিকোয়েস্ট */
    public function GetSentFriendRequests(int $me): array
    {
        try {
            $this->EnsureFriendshipsTable();
            $rows = $this->all('SELECT f.`Id` AS FriendshipId, f.`CreatedAt` AS Since, \'sent\' AS Relation, ' . self::FRIEND_USER_COLS . '
                FROM `Friendships` f INNER JOIN `Users` u ON u.`Id` = f.`AddresseeId`
                WHERE f.`RequesterId` = :me AND f.`Status` = \'Pending\' ORDER BY f.`CreatedAt` DESC, f.`Id` DESC', ['me' => $me]);
            return array_map(fn($r) => $this->mapFriendPerson($r), $rows);
        } catch (Throwable $e) { error_log('[GetSentFriendRequests] ' . $e->getMessage()); return []; }
    }

    public function CountIncomingFriendRequests(int $me): int
    {
        try {
            $this->EnsureFriendshipsTable();
            return (int)$this->scalar('SELECT COUNT(*) FROM `Friendships` WHERE `AddresseeId` = :me AND `Status` = \'Pending\'', ['me' => $me]);
        } catch (Throwable $e) { return 0; }
    }

    /**
     * মানুষ খোঁজা (নাম / username / প্রতিষ্ঠান দিয়ে)। অ্যাডমিন, নিষ্ক্রিয় ও সাসপেন্ড অ্যাকাউন্ট বাদ।
     * কিছু না লিখলে নতুন যোগ দেওয়া মানুষ দেখায় (People you may know)।
     * @return Obj[] প্রত্যেকের সাথে Relation সহ
     */
    public function SearchPeople(int $me, string $q, string $role = 'all', int $limit = 30): array
    {
        try {
            $this->EnsureFriendshipsTable();
            $this->EnsureSuspensionsTable();
            $where = ["u.`Role` <> 'Admin'", 'u.`Id` <> :me', '(u.`IsActive` IS NULL OR u.`IsActive` = 1)',
                'NOT EXISTS (SELECT 1 FROM `UserSuspensions` s WHERE s.`UserId` = u.`Id` AND s.`LiftedAt` IS NULL AND (s.`IsPermanent` = 1 OR s.`SuspendedUntil` > :now))'];
            $p = ['me' => $me, 'now' => now()];
            if ($role === 'Teacher') $where[] = "u.`Role` = 'Teacher'";
            elseif ($role === 'User') $where[] = "u.`Role` = 'User'";
            $q = trim($q);
            if ($q !== '') {
                $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
                $where[] = '(u.`FullName` LIKE :q1 OR u.`UserName` LIKE :q2 OR u.`InstitutionName` LIKE :q3)';
                $p += ['q1' => $like, 'q2' => $like, 'q3' => $like];
            }
            $limit = max(1, min(60, $limit));
            $rows = $this->all('SELECT u.`CreatedAt` AS Since, ' . self::FRIEND_USER_COLS . ' FROM `Users` u WHERE ' . implode(' AND ', $where)
                . ' ORDER BY ' . ($q !== '' ? 'u.`FullName`' : 'u.`Id` DESC') . " LIMIT $limit", $p);

            // প্রত্যেকের সাথে আমার সম্পর্ক এক কুয়েরিতে বের করি
            $rel = [];
            foreach ($this->all('SELECT `RequesterId`, `UserLow`, `UserHigh`, `Status` FROM `Friendships` WHERE `UserLow` = :a OR `UserHigh` = :b',
                ['a' => $me, 'b' => $me]) as $f) {
                $other = (int)$f->UserLow === $me ? (int)$f->UserHigh : (int)$f->UserLow;
                $rel[$other] = $f->Status === 'Accepted' ? 'friends' : ((int)$f->RequesterId === $me ? 'sent' : 'received');
            }
            $myFriends = array_flip($this->GetFriendIds($me));
            $out = [];
            foreach ($rows as $r) {
                $r->Relation = $rel[(int)$r->PersonId] ?? 'none';
                $person = $this->mapFriendPerson($r);
                $person->TimeAgo = '';
                $person->MutualCount = $myFriends ? $this->countMutual((int)$r->PersonId, $myFriends) : 0;
                $out[] = $person;
            }
            return $out;
        } catch (Throwable $e) { error_log('[SearchPeople] ' . $e->getMessage()); return []; }
    }

    private function countMutual(int $other, array $myFriendSet): int
    {
        $n = 0;
        foreach ($this->GetFriendIds($other) as $id) if (isset($myFriendSet[$id])) $n++;
        return $n;
    }

    /** @return array<int,string> অন্য ইউজার Id => আমার সাথে সম্পর্ক (friends|sent|received) */
    public function GetRelationMap(int $me): array
    {
        try {
            $this->EnsureFriendshipsTable();
            $rel = [];
            foreach ($this->all('SELECT `RequesterId`, `UserLow`, `UserHigh`, `Status` FROM `Friendships` WHERE `UserLow` = :a OR `UserHigh` = :b',
                ['a' => $me, 'b' => $me]) as $f) {
                $other = (int)$f->UserLow === $me ? (int)$f->UserHigh : (int)$f->UserLow;
                $rel[$other] = $f->Status === 'Accepted' ? 'friends' : ((int)$f->RequesterId === $me ? 'sent' : 'received');
            }
            return $rel;
        } catch (Throwable $e) { return []; }
    }

    /** কারো ফ্রেন্ড লিস্ট, প্রত্যেকের পাশে দর্শকের সাথে সম্পর্ক (বাটনের জন্য) @return Obj[] */
    public function GetFriendsOfUserForViewer(int $ownerId, int $viewerId): array
    {
        $list = $this->GetFriends($ownerId);
        $rel = $this->GetRelationMap($viewerId);
        foreach ($list as $p) {
            $p->Relation = $p->Id === $viewerId ? 'self' : ($rel[$p->Id] ?? 'none');
            $p->TimeAgo = '';
        }
        return $list;
    }

    /** দুজনের কমন ফ্রেন্ড সংখ্যা */
    public function CountMutualFriends(int $me, int $other): int
    {
        $mine = array_flip($this->GetFriendIds($me));
        return $mine ? $this->countMutual($other, $mine) : 0;
    }

    /** কোনো ইউজার মুছে গেলে তার সব ফ্রেন্ড সারি মুছে ফেলা */
    public function DeleteFriendshipsOfUser(int $userId): void
    {
        try {
            $this->EnsureFriendshipsTable();
            $this->exec('DELETE FROM `Friendships` WHERE `UserLow` = :a OR `UserHigh` = :b', ['a' => $userId, 'b' => $userId]);
        } catch (Throwable $e) { /* ignore */ }
    }

    // ============================================================
    // ===== PROFILE POSTS (ফেসবুকের মতো টাইমলাইন পোস্ট) =====
    // Privacy: Public (সবাই) | Friends (শুধু ফ্রেন্ড) | OnlyMe (শুধু নিজে)
    // Reaction: like | love | care | haha | wow | sad | angry
    // কমেন্টে এক লেভেলের রিপ্লাই (রিপ্লাইয়ের রিপ্লাই হলে @নাম দিয়ে একই থ্রেডে যায়)
    // ============================================================
    public const POST_PRIVACY = ['Public', 'Friends', 'OnlyMe'];
    public const REACTIONS = ['like'];
    public const POST_TYPES = ['post', 'study', 'question', 'achievement', 'poll', 'material', 'update', 'notice'];
    /** শুধু টিচাররা যে ধরনের পোস্ট দিতে পারে */
    public const TEACHER_POST_TYPES = ['material', 'update', 'notice'];
    private bool $profilePostsReady = false;

    public function EnsureProfilePostTables(): void
    {
        if ($this->profilePostsReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ProfilePosts" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL,
                "Content" TEXT NULL, "ImageUrl" TEXT NULL, "Privacy" TEXT NOT NULL DEFAULT \'Public\', "IsEdited" INTEGER NOT NULL DEFAULT 0,
                "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ProfilePostReactions" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "PostId" INTEGER NOT NULL,
                "UserId" INTEGER NOT NULL, "Reaction" TEXT NOT NULL, "CreatedAt" TEXT NOT NULL, UNIQUE ("PostId", "UserId"))');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ProfilePostComments" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "PostId" INTEGER NOT NULL,
                "UserId" INTEGER NOT NULL, "ParentId" INTEGER NULL, "Content" TEXT NOT NULL, "IsEdited" INTEGER NOT NULL DEFAULT 0,
                "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "ProfileCommentReactions" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "CommentId" INTEGER NOT NULL,
                "UserId" INTEGER NOT NULL, "Reaction" TEXT NOT NULL, "CreatedAt" TEXT NOT NULL, UNIQUE ("CommentId", "UserId"))');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ProfilePosts` (
                `Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `Content` TEXT NULL, `ImageUrl` VARCHAR(500) NULL,
                `Privacy` VARCHAR(10) NOT NULL DEFAULT 'Public', `IsEdited` TINYINT(1) NOT NULL DEFAULT 0,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), KEY `IX_ProfilePosts_User` (`UserId`, `Id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ProfilePostReactions` (
                `Id` INT NOT NULL AUTO_INCREMENT, `PostId` INT NOT NULL, `UserId` INT NOT NULL, `Reaction` VARCHAR(10) NOT NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_PPR_PostUser` (`PostId`, `UserId`),
                CONSTRAINT `FK_PPR_Post` FOREIGN KEY (`PostId`) REFERENCES `ProfilePosts` (`Id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ProfilePostComments` (
                `Id` INT NOT NULL AUTO_INCREMENT, `PostId` INT NOT NULL, `UserId` INT NOT NULL, `ParentId` INT NULL, `Content` TEXT NOT NULL,
                `IsEdited` TINYINT(1) NOT NULL DEFAULT 0, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), KEY `IX_PPC_Post` (`PostId`, `Id`),
                CONSTRAINT `FK_PPC_Post` FOREIGN KEY (`PostId`) REFERENCES `ProfilePosts` (`Id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `ProfileCommentReactions` (
                `Id` INT NOT NULL AUTO_INCREMENT, `CommentId` INT NOT NULL, `UserId` INT NOT NULL, `Reaction` VARCHAR(10) NOT NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_PCR_CommentUser` (`CommentId`, `UserId`),
                CONSTRAINT `FK_PCR_Comment` FOREIGN KEY (`CommentId`) REFERENCES `ProfilePostComments` (`Id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        // পরে যোগ হওয়া কলাম (লাইভ টেবিলে না থাকলে নিজে থেকে যোগ হয়)
        if (!$this->columnExists('ProfilePosts', 'SharedPostId')) {
            try { $this->pdo->exec('ALTER TABLE `ProfilePosts` ADD COLUMN `SharedPostId` INT NULL'); } catch (Throwable $e) { error_log('[SharedPostId] ' . $e->getMessage()); }
        }
        // পোস্টের ধরন (post | study | question | achievement | poll) + বাড়তি তথ্য (JSON)
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        if (!$this->columnExists('ProfilePosts', 'PostType')) {
            try { $this->pdo->exec('ALTER TABLE `ProfilePosts` ADD COLUMN `PostType` ' . ($sqlite ? "TEXT NOT NULL DEFAULT 'post'" : "VARCHAR(12) NOT NULL DEFAULT 'post'")); } catch (Throwable $e) { error_log('[PostType] ' . $e->getMessage()); }
        }
        if (!$this->columnExists('ProfilePosts', 'Meta')) {
            try { $this->pdo->exec('ALTER TABLE `ProfilePosts` ADD COLUMN `Meta` TEXT NULL'); } catch (Throwable $e) { error_log('[Meta] ' . $e->getMessage()); }
        }
        // আগের Love/Haha ইত্যাদি রিয়েকশন এখন সব Like (একবারই চলে, পরে কিছু পায় না)
        try {
            $this->pdo->exec("UPDATE `ProfilePostReactions` SET `Reaction` = 'like' WHERE `Reaction` <> 'like'");
            $this->pdo->exec("UPDATE `ProfileCommentReactions` SET `Reaction` = 'like' WHERE `Reaction` <> 'like'");
        } catch (Throwable $e) { }
        $this->profilePostsReady = true;
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                foreach ($this->pdo->query('PRAGMA table_info("' . $table . '")')->fetchAll(PDO::FETCH_ASSOC) as $c) if (strcasecmp($c['name'], $column) === 0) return true;
                return false;
            }
            return (bool)$this->pdo->query("SHOW COLUMNS FROM `$table` LIKE " . $this->pdo->quote($column))->fetch();
        } catch (Throwable $e) { return true; } // যাচাই না করা গেলে ALTER চেষ্টা করি না
    }

    private const PROFILE_POST_SELECT = 'SELECT p.`Id`, p.`UserId`, p.`Content`, p.`ImageUrl`, p.`Privacy`, p.`IsEdited`, p.`CreatedAt`, p.`UpdatedAt`, p.`SharedPostId`, p.`PostType`, p.`Meta`,
            u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink`
        FROM `ProfilePosts` p LEFT JOIN `Users` u ON u.`Id` = p.`UserId`';

    private function mapProfilePost(Obj $r): Obj
    {
        $name = !is_blank($r->FullName) ? (string)$r->FullName : (string)($r->UserName ?? 'User');
        return obj([
            'Id' => (int)$r->Id, 'UserId' => (int)$r->UserId, 'Content' => (string)($r->Content ?? ''),
            'ImageUrl' => is_blank($r->ImageUrl) ? null : (string)$r->ImageUrl,
            'Privacy' => in_array($r->Privacy, self::POST_PRIVACY, true) ? $r->Privacy : 'Public',
            'IsEdited' => (bool)(int)$r->IsEdited, 'CreatedAt' => $r->CreatedAt, 'UpdatedAt' => $r->UpdatedAt,
            'AuthorName' => $name, 'AuthorUserName' => (string)($r->UserName ?? ''), 'AuthorRole' => (string)($r->Role ?? 'User'),
            'AuthorImage' => is_blank($r->ProfileImageLink) ? null : (string)$r->ProfileImageLink,
            'AuthorInitials' => initials($name, 'U'), 'TimeAgo' => time_ago($r->CreatedAt),
            'Reactions' => [], 'ReactionTotal' => 0, 'MyReaction' => null, 'Comments' => [], 'CommentCount' => 0,
            'SharedPostId' => isset($r->SharedPostId) && $r->SharedPostId !== null ? (int)$r->SharedPostId : null, 'Shared' => null, 'ShareCount' => 0,
            'PostType' => in_array($r->PostType ?? 'post', self::POST_TYPES, true) ? $r->PostType : 'post',
            'Meta' => is_array($m = json_decode((string)($r->Meta ?? ''), true)) ? $m : [], 'Poll' => null, 'IsSaved' => false,
        ]);
    }

    public function GetProfilePostById(int $id): ?Obj
    {
        try {
            $this->EnsureProfilePostTables();
            $r = $this->one(self::PROFILE_POST_SELECT . ' WHERE p.`Id` = :id', ['id' => $id]);
            return $r ? $this->mapProfilePost($r) : null;
        } catch (Throwable $e) { error_log('[GetProfilePostById] ' . $e->getMessage()); return null; }
    }

    /** কোনো দর্শক এই পোস্টটি দেখতে পারবে কিনা */
    public function CanViewProfilePost(Obj $post, ?int $viewerId, bool $viewerIsAdmin): bool
    {
        if ($viewerIsAdmin) return true;
        if ($viewerId !== null && $post->UserId === $viewerId) return true;
        if ($post->Privacy === 'Public') return true;
        if ($post->Privacy === 'Friends' && $viewerId !== null) return $this->GetFriendRelation($viewerId, $post->UserId) === 'friends';
        return false;
    }

    /**
     * কারো টাইমলাইনের পোস্ট (দর্শকের অনুমতি অনুযায়ী ফিল্টার করা), নতুন আগে।
     * $beforeId দিলে তার আগের (পুরোনো) পোস্ট — "আরো দেখুন" এর জন্য।
     */
    public function GetProfilePosts(int $ownerId, ?int $viewerId, bool $viewerIsAdmin, ?int $beforeId = null, int $limit = 10, array $types = []): array
    {
        try {
            $this->EnsureProfilePostTables();
            $where = ['p.`UserId` = :o'];
            $p = ['o' => $ownerId];
            if (!$viewerIsAdmin && $viewerId !== $ownerId) {
                $isFriend = $viewerId !== null && $this->GetFriendRelation($viewerId, $ownerId) === 'friends';
                $where[] = $isFriend ? "p.`Privacy` IN ('Public','Friends')" : "p.`Privacy` = 'Public'";
            }
            if ($beforeId) { $where[] = 'p.`Id` < :b'; $p['b'] = $beforeId; }
            $types = array_values(array_intersect($types, self::POST_TYPES));
            if ($types) $where[] = "p.`PostType` IN ('" . implode("','", $types) . "') AND p.`SharedPostId` IS NULL";
            $limit = max(1, min(30, $limit));
            $rows = $this->all(self::PROFILE_POST_SELECT . ' WHERE ' . implode(' AND ', $where) . " ORDER BY p.`Id` DESC LIMIT $limit", $p);
            return $this->hydrateProfilePosts(array_map(fn($r) => $this->mapProfilePost($r), $rows), $viewerId, $viewerIsAdmin);
        } catch (Throwable $e) { error_log('[GetProfilePosts] ' . $e->getMessage()); return []; }
    }

    public function CountProfilePosts(int $ownerId, ?int $viewerId, bool $viewerIsAdmin): int
    {
        try {
            $this->EnsureProfilePostTables();
            $sql = 'SELECT COUNT(*) FROM `ProfilePosts` WHERE `UserId` = :o';
            if (!$viewerIsAdmin && $viewerId !== $ownerId) {
                $isFriend = $viewerId !== null && $this->GetFriendRelation($viewerId, $ownerId) === 'friends';
                $sql .= $isFriend ? " AND `Privacy` IN ('Public','Friends')" : " AND `Privacy` = 'Public'";
            }
            return (int)$this->scalar($sql, ['o' => $ownerId]);
        } catch (Throwable $e) { return 0; }
    }

    /** রিয়েকশন, কমেন্ট আর আমার রিয়েকশন যোগ করে */
    public function hydrateProfilePosts(array $posts, ?int $viewerId, bool $viewerIsAdmin = false): array
    {
        if (!$posts) return [];
        $ids = array_map(fn($p) => (int)$p->Id, $posts);
        $in = implode(',', $ids);
        $byId = [];
        foreach ($posts as $p) $byId[$p->Id] = $p;

        foreach ($this->all("SELECT `PostId`, `Reaction`, COUNT(*) AS N FROM `ProfilePostReactions` WHERE `PostId` IN ($in) GROUP BY `PostId`, `Reaction`") as $r) {
            $p = $byId[(int)$r->PostId];
            $p->Reactions[$r->Reaction] = (int)$r->N;
            $p->ReactionTotal += (int)$r->N;
        }
        if ($viewerId) {
            foreach ($this->all("SELECT `PostId`, `Reaction` FROM `ProfilePostReactions` WHERE `PostId` IN ($in) AND `UserId` = :u", ['u' => $viewerId]) as $r) {
                $byId[(int)$r->PostId]->MyReaction = $r->Reaction;
            }
        }
        foreach ($posts as $p) arsort($p->Reactions);

        $comments = $this->all("SELECT c.`Id`, c.`PostId`, c.`UserId`, c.`ParentId`, c.`Content`, c.`IsEdited`, c.`CreatedAt`,
                u.`FullName`, u.`UserName`, u.`ProfileImageLink`
            FROM `ProfilePostComments` c LEFT JOIN `Users` u ON u.`Id` = c.`UserId`
            WHERE c.`PostId` IN ($in) ORDER BY c.`Id`");
        $cById = [];
        foreach ($comments as $c) $cById[(int)$c->Id] = $this->mapPostComment($c);
        if ($cById) {
            $cin = implode(',', array_keys($cById));
            foreach ($this->all("SELECT `CommentId`, `Reaction`, COUNT(*) AS N FROM `ProfileCommentReactions` WHERE `CommentId` IN ($cin) GROUP BY `CommentId`, `Reaction`") as $r) {
                $c = $cById[(int)$r->CommentId];
                $c->Reactions[$r->Reaction] = (int)$r->N;
                $c->ReactionTotal += (int)$r->N;
            }
            if ($viewerId) {
                foreach ($this->all("SELECT `CommentId`, `Reaction` FROM `ProfileCommentReactions` WHERE `CommentId` IN ($cin) AND `UserId` = :u", ['u' => $viewerId]) as $r) {
                    $cById[(int)$r->CommentId]->MyReaction = $r->Reaction;
                }
            }
        }
        foreach ($cById as $c) {
            arsort($c->Reactions);
            $post = $byId[$c->PostId] ?? null;
            if (!$post) continue;
            $post->CommentCount++;
            if ($c->ParentId && isset($cById[$c->ParentId])) $cById[$c->ParentId]->Replies[] = $c;
            elseif (!$c->ParentId) $post->Comments[] = $c;
        }

        // শেয়ার করা মূল পোস্ট (দর্শক দেখতে না পারলে Shared = false → "Content unavailable")
        $origIds = array_values(array_unique(array_filter(array_map(fn($p) => $p->SharedPostId, $posts))));
        if ($origIds) {
            $orig = [];
            foreach ($this->all(self::PROFILE_POST_SELECT . ' WHERE p.`Id` IN (' . implode(',', array_map('intval', $origIds)) . ')') as $r) {
                $o = $this->mapProfilePost($r);
                $orig[$o->Id] = $o;
            }
            foreach ($posts as $p) {
                if (!$p->SharedPostId) continue;
                $o = $orig[$p->SharedPostId] ?? null;
                $p->Shared = ($o && $this->CanViewProfilePost($o, $viewerId, $viewerIsAdmin)) ? $o : false;
            }
        }
        foreach ($this->all("SELECT `SharedPostId`, COUNT(*) AS N FROM `ProfilePosts` WHERE `SharedPostId` IN ($in) GROUP BY `SharedPostId`") as $r) {
            if (isset($byId[(int)$r->SharedPostId])) $byId[(int)$r->SharedPostId]->ShareCount = (int)$r->N;
        }
        // পোল (শেয়ার করা মূল পোস্টের পোলও)
        $pollIds = [];
        foreach ($posts as $p) {
            if ($p->PostType === 'poll') $pollIds[] = $p->Id;
            if ($p->Shared && $p->Shared->PostType === 'poll') $pollIds[] = $p->Shared->Id;
        }
        if ($pollIds) {
            $polls = $this->GetPolls(array_unique($pollIds), $viewerId);
            foreach ($posts as $p) {
                if ($p->PostType === 'poll') $p->Poll = $polls[$p->Id] ?? null;
                if ($p->Shared && $p->Shared->PostType === 'poll') $p->Shared->Poll = $polls[$p->Shared->Id] ?? null;
            }
        }
        if ($viewerId) {
            $saved = $this->SavedKeys($viewerId, 'post');
            foreach ($posts as $p) $p->IsSaved = isset($saved['post:' . $p->Id]);
        }
        return $posts;
    }

    private function mapPostComment(Obj $c): Obj
    {
        $name = !is_blank($c->FullName) ? (string)$c->FullName : (string)($c->UserName ?? 'User');
        return obj([
            'Id' => (int)$c->Id, 'PostId' => (int)$c->PostId, 'UserId' => (int)$c->UserId,
            'ParentId' => $c->ParentId !== null ? (int)$c->ParentId : null, 'Content' => (string)$c->Content,
            'IsEdited' => (bool)(int)$c->IsEdited, 'CreatedAt' => $c->CreatedAt, 'TimeAgo' => time_ago($c->CreatedAt, 'd M'),
            'AuthorName' => $name, 'AuthorImage' => is_blank($c->ProfileImageLink) ? null : (string)$c->ProfileImageLink,
            'AuthorInitials' => initials($name, 'U'), 'Reactions' => [], 'ReactionTotal' => 0, 'MyReaction' => null, 'Replies' => [],
        ]);
    }

    /** একটা কমেন্ট (রিয়েকশনসহ) — নতুন কমেন্ট রেন্ডার করার জন্য */
    public function GetPostCommentById(int $id, ?int $viewerId = null): ?Obj
    {
        try {
            $this->EnsureProfilePostTables();
            $c = $this->one('SELECT c.`Id`, c.`PostId`, c.`UserId`, c.`ParentId`, c.`Content`, c.`IsEdited`, c.`CreatedAt`,
                    u.`FullName`, u.`UserName`, u.`ProfileImageLink`
                FROM `ProfilePostComments` c LEFT JOIN `Users` u ON u.`Id` = c.`UserId` WHERE c.`Id` = :id', ['id' => $id]);
            if (!$c) return null;
            $c = $this->mapPostComment($c);
            foreach ($this->all('SELECT `Reaction`, COUNT(*) AS N FROM `ProfileCommentReactions` WHERE `CommentId` = :c GROUP BY `Reaction`', ['c' => $id]) as $r) {
                $c->Reactions[$r->Reaction] = (int)$r->N; $c->ReactionTotal += (int)$r->N;
            }
            arsort($c->Reactions);
            if ($viewerId) $c->MyReaction = $this->scalar('SELECT `Reaction` FROM `ProfileCommentReactions` WHERE `CommentId` = :c AND `UserId` = :u', ['c' => $id, 'u' => $viewerId]) ?: null;
            return $c;
        } catch (Throwable $e) { error_log('[GetPostCommentById] ' . $e->getMessage()); return null; }
    }

    public function CreateProfilePost(int $userId, string $content, ?string $imageUrl, string $privacy, ?int $sharedPostId = null, string $type = 'post', array $meta = []): int
    {
        $this->EnsureProfilePostTables();
        return $this->insert('INSERT INTO `ProfilePosts` (`UserId`, `Content`, `ImageUrl`, `Privacy`, `IsEdited`, `CreatedAt`, `SharedPostId`, `PostType`, `Meta`) VALUES (:u, :c, :i, :p, 0, :t, :s, :ty, :m)',
            ['u' => $userId, 'c' => $content, 'i' => $imageUrl, 'p' => $privacy, 't' => now(), 's' => $sharedPostId, 'ty' => $type,
             'm' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null]);
    }

    public function UpdateProfilePostMeta(int $postId, array $meta): void
    {
        $this->exec('UPDATE `ProfilePosts` SET `Meta` = :m WHERE `Id` = :id', ['m' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null, 'id' => $postId]);
    }

    public function CountRecentShares(int $userId, int $postId, int $minutes): int
    {
        $this->EnsureProfilePostTables();
        return (int)$this->scalar('SELECT COUNT(*) FROM `ProfilePosts` WHERE `UserId` = :u AND `SharedPostId` = :p AND `CreatedAt` >= :t',
            ['u' => $userId, 'p' => $postId, 't' => date('Y-m-d H:i:s', time() - $minutes * 60)]);
    }

    /** @return Obj[] কে কে এই পোস্ট শেয়ার করেছে */
    public function GetSharers(int $postId, int $limit = 200): array
    {
        try {
            $this->EnsureProfilePostTables();
            $rows = $this->all('SELECT p.`Id` AS ShareId, p.`UserId`, p.`Privacy`, u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink`
                FROM `ProfilePosts` p INNER JOIN `Users` u ON u.`Id` = p.`UserId` WHERE p.`SharedPostId` = :p ORDER BY p.`Id` DESC LIMIT ' . (int)$limit, ['p' => $postId]);
            return array_map(function ($r) {
                $name = !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName;
                return obj(['Id' => (int)$r->UserId, 'ShareId' => (int)$r->ShareId, 'Privacy' => $r->Privacy, 'Name' => $name, 'Role' => $r->Role,
                    'Image' => is_blank($r->ProfileImageLink) ? null : $r->ProfileImageLink, 'Initials' => initials($name, 'U')]);
            }, $rows);
        } catch (Throwable $e) { return []; }
    }

    // ============================================================
    // ===== কভার ফটো (UserProfileExtras) =====
    // ============================================================
    private bool $extrasReady = false;

    public function EnsureProfileExtrasTable(): void
    {
        if ($this->extrasReady) return;
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "UserProfileExtras" ("UserId" INTEGER PRIMARY KEY, "CoverImageUrl" TEXT NULL,
                "CoverPosY" INTEGER NOT NULL DEFAULT 50, "UpdatedAt" TEXT NULL)');
        } else {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `UserProfileExtras` (
                `UserId` INT NOT NULL, `CoverImageUrl` VARCHAR(500) NULL, `CoverPosY` TINYINT NOT NULL DEFAULT 50, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`UserId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->extrasReady = true;
    }

    /** @return Obj {CoverImageUrl, CoverPosY} */
    public function GetProfileExtras(int $userId): Obj
    {
        try {
            $this->EnsureProfileExtrasTable();
            $r = $this->one('SELECT `CoverImageUrl`, `CoverPosY` FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]);
        } catch (Throwable $e) { $r = null; }
        return obj(['CoverImageUrl' => $r && !is_blank($r->CoverImageUrl) ? (string)$r->CoverImageUrl : null,
            'CoverPosY' => $r ? max(0, min(100, (int)$r->CoverPosY)) : 50]);
    }

    /** কভার সেভ ($url = '' দিলে মুছে দেয়, null দিলে ছবি না বদলে শুধু পজিশন) */
    public function SaveCover(int $userId, ?string $url, int $posY): bool
    {
        $this->EnsureProfileExtrasTable();
        $posY = max(0, min(100, $posY));
        $exists = (int)$this->scalar('SELECT COUNT(*) FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]) > 0;
        if (!$exists) {
            $this->insert('INSERT INTO `UserProfileExtras` (`UserId`, `CoverImageUrl`, `CoverPosY`, `UpdatedAt`) VALUES (:u, :c, :y, :t)',
                ['u' => $userId, 'c' => $url === '' ? null : $url, 'y' => $posY, 't' => now()]);
            return true;
        }
        if ($url === null) {
            return $this->exec('UPDATE `UserProfileExtras` SET `CoverPosY` = :y, `UpdatedAt` = :t WHERE `UserId` = :u', ['y' => $posY, 't' => now(), 'u' => $userId]) >= 0;
        }
        return $this->exec('UPDATE `UserProfileExtras` SET `CoverImageUrl` = :c, `CoverPosY` = :y, `UpdatedAt` = :t WHERE `UserId` = :u',
            ['c' => $url === '' ? null : $url, 'y' => $posY, 't' => now(), 'u' => $userId]) >= 0;
    }

    // ============================================================
    // ===== @Mention (কমেন্টে কাউকে ট্যাগ করা) =====
    // কমেন্টে সেভ হয়: @[নাম|ইউজারId]
    // ============================================================

    /** মেনশন সাজেশন: পোস্টের মালিক, কমেন্টকারীরা আর আমার ফ্রেন্ডরা — নামের সাথে মিলিয়ে */
    public function MentionSuggestions(int $me, ?int $postId, string $q, int $limit = 8): array
    {
        try {
            $ids = [];
            if ($postId) {
                $this->EnsureProfilePostTables();
                $owner = $this->scalar('SELECT `UserId` FROM `ProfilePosts` WHERE `Id` = :p', ['p' => $postId]);
                if ($owner) $ids[(int)$owner] = 3;
                foreach ($this->all('SELECT DISTINCT `UserId` FROM `ProfilePostComments` WHERE `PostId` = :p', ['p' => $postId]) as $r) $ids[(int)$r->UserId] = max($ids[(int)$r->UserId] ?? 0, 2);
            }
            foreach ($this->GetFriendIds($me) as $f) $ids[$f] = max($ids[$f] ?? 0, 1);
            unset($ids[$me]);
            if (!$ids) return [];
            $q = trim($q);
            $sql = 'SELECT `Id`, `FullName`, `UserName`, `Role`, `ProfileImageLink` FROM `Users` WHERE `Id` IN (' . implode(',', array_keys($ids)) . ") AND `Role` <> 'Admin'";
            $p = [];
            if ($q !== '') {
                $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
                $sql .= ' AND (`FullName` LIKE :a OR `UserName` LIKE :b)';
                $p = ['a' => $like, 'b' => $like];
            }
            $rows = $this->all($sql . ' LIMIT 50', $p);
            usort($rows, function ($x, $y) use ($ids, $q) {
                $sx = ($ids[(int)$x->Id] ?? 0) + ($q !== '' && stripos((string)$x->FullName, $q) === 0 ? 5 : 0);
                $sy = ($ids[(int)$y->Id] ?? 0) + ($q !== '' && stripos((string)$y->FullName, $q) === 0 ? 5 : 0);
                return $sy <=> $sx ?: strcasecmp((string)$x->FullName, (string)$y->FullName);
            });
            return array_map(function ($r) {
                $name = !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName;
                return ['id' => (int)$r->Id, 'name' => $name, 'username' => (string)$r->UserName, 'role' => $r->Role === 'Teacher' ? 'Teacher' : 'Student',
                    'image' => is_blank($r->ProfileImageLink) ? null : $r->ProfileImageLink, 'initials' => initials($name, 'U')];
            }, array_slice($rows, 0, $limit));
        } catch (Throwable $e) { error_log('[MentionSuggestions] ' . $e->getMessage()); return []; }
    }

    /**
     * কমেন্টের @[নাম|id] টোকেন যাচাই করে: আসল নাম বসায়, ভুল/অ্যাডমিন id হলে সাধারণ লেখা বানায়।
     * @return array{0:string,1:int[]} [পরিষ্কার লেখা, মেনশন হওয়া ইউজার Id]
     */
    public function NormalizeMentions(string $text): array
    {
        $ids = [];
        $out = preg_replace_callback('/@\[([^\]|]{1,80})\|(\d{1,10})\]/u', function ($m) use (&$ids) {
            $u = $this->one('SELECT `Id`, `FullName`, `UserName`, `Role` FROM `Users` WHERE `Id` = :id', ['id' => (int)$m[2]]);
            if (!$u || $u->Role === 'Admin') return '@' . $m[1];
            $name = !is_blank($u->FullName) ? trim((string)$u->FullName) : (string)$u->UserName;
            $name = str_replace([']', '|'], '', $name);
            $ids[(int)$u->Id] = true;
            return '@[' . $name . '|' . (int)$u->Id . ']';
        }, $text);
        return [$out ?? $text, array_keys($ids)];
    }

    public function UpdateProfilePost(int $postId, string $content, ?string $imageUrl, string $privacy, bool $contentChanged): bool
    {
        $this->EnsureProfilePostTables();
        return $this->exec('UPDATE `ProfilePosts` SET `Content` = :c, `ImageUrl` = :i, `Privacy` = :p, `UpdatedAt` = :t'
            . ($contentChanged ? ', `IsEdited` = 1' : '') . ' WHERE `Id` = :id',
            ['c' => $content, 'i' => $imageUrl, 'p' => $privacy, 't' => now(), 'id' => $postId]) >= 0;
    }

    /** পোস্ট আর তার সব কমেন্ট / রিয়েকশন মুছে ফেলে */
    public function DeleteProfilePost(int $postId): bool
    {
        try {
            $this->EnsureProfilePostTables();
            $this->exec('DELETE FROM `ProfileCommentReactions` WHERE `CommentId` IN (SELECT `Id` FROM `ProfilePostComments` WHERE `PostId` = :p)', ['p' => $postId]);
            $this->exec('DELETE FROM `ProfilePostComments` WHERE `PostId` = :p', ['p' => $postId]);
            $this->exec('DELETE FROM `ProfilePostReactions` WHERE `PostId` = :p', ['p' => $postId]);
            $this->DeletePollOf($postId);
            try { $this->exec("DELETE FROM `SavedItems` WHERE `ItemType` = 'post' AND `ItemId` = :p", ['p' => $postId]); } catch (Throwable $e) { }
            return $this->exec('DELETE FROM `ProfilePosts` WHERE `Id` = :p', ['p' => $postId]) > 0;
        } catch (Throwable $e) { error_log('[DeleteProfilePost] ' . $e->getMessage()); return false; }
    }

    /** শেষ X মিনিটে কয়টা পোস্ট / কমেন্ট (স্প্যাম ঠেকাতে) */
    public function CountRecentProfileActivity(string $what, int $userId, int $minutes): int
    {
        $this->EnsureProfilePostTables();
        $table = $what === 'comment' ? 'ProfilePostComments' : 'ProfilePosts';
        return (int)$this->scalar("SELECT COUNT(*) FROM `$table` WHERE `UserId` = :u AND `CreatedAt` >= :t",
            ['u' => $userId, 't' => date('Y-m-d H:i:s', time() - $minutes * 60)]);
    }

    /**
     * রিয়েকশন দেওয়া / বদলানো / তুলে নেওয়া ($reaction = null হলে তুলে নেয়)।
     * @return array{prev:?string, now:?string}
     */
    private function setReaction(string $table, string $col, int $targetId, int $userId, ?string $reaction): array
    {
        $this->EnsureProfilePostTables();
        $prev = $this->scalar("SELECT `Reaction` FROM `$table` WHERE `$col` = :t AND `UserId` = :u", ['t' => $targetId, 'u' => $userId]) ?: null;
        if ($reaction === null) {
            $this->exec("DELETE FROM `$table` WHERE `$col` = :t AND `UserId` = :u", ['t' => $targetId, 'u' => $userId]);
        } elseif ($prev === null) {
            try {
                $this->insert("INSERT INTO `$table` (`$col`, `UserId`, `Reaction`, `CreatedAt`) VALUES (:t, :u, :r, :c)",
                    ['t' => $targetId, 'u' => $userId, 'r' => $reaction, 'c' => now()]);
            } catch (Throwable $e) { // একসাথে দুইবার চাপলে
                $this->exec("UPDATE `$table` SET `Reaction` = :r WHERE `$col` = :t AND `UserId` = :u", ['r' => $reaction, 't' => $targetId, 'u' => $userId]);
            }
        } elseif ($prev !== $reaction) {
            $this->exec("UPDATE `$table` SET `Reaction` = :r WHERE `$col` = :t AND `UserId` = :u", ['r' => $reaction, 't' => $targetId, 'u' => $userId]);
        }
        return ['prev' => $prev, 'now' => $reaction];
    }

    private function reactionSummary(string $table, string $col, int $targetId): array
    {
        $out = [];
        $total = 0;
        foreach ($this->all("SELECT `Reaction`, COUNT(*) AS N FROM `$table` WHERE `$col` = :t GROUP BY `Reaction`", ['t' => $targetId]) as $r) {
            $out[$r->Reaction] = (int)$r->N; $total += (int)$r->N;
        }
        arsort($out);
        return ['counts' => $out, 'total' => $total];
    }

    public function SetPostReaction(int $postId, int $userId, ?string $reaction): array
    {
        $res = $this->setReaction('ProfilePostReactions', 'PostId', $postId, $userId, $reaction);
        return $res + $this->reactionSummary('ProfilePostReactions', 'PostId', $postId);
    }

    public function SetCommentReaction(int $commentId, int $userId, ?string $reaction): array
    {
        $res = $this->setReaction('ProfileCommentReactions', 'CommentId', $commentId, $userId, $reaction);
        return $res + $this->reactionSummary('ProfileCommentReactions', 'CommentId', $commentId);
    }

    /** @return Obj[] কে কোন রিয়েক্ট দিয়েছে */
    public function GetReactors(string $type, int $targetId, int $limit = 200): array
    {
        try {
            $this->EnsureProfilePostTables();
            [$table, $col] = $type === 'comment' ? ['ProfileCommentReactions', 'CommentId'] : ['ProfilePostReactions', 'PostId'];
            $rows = $this->all("SELECT r.`UserId`, r.`Reaction`, u.`FullName`, u.`UserName`, u.`Role`, u.`ProfileImageLink`
                FROM `$table` r INNER JOIN `Users` u ON u.`Id` = r.`UserId` WHERE r.`$col` = :t ORDER BY r.`Id` DESC LIMIT " . (int)$limit, ['t' => $targetId]);
            return array_map(function ($r) {
                $name = !is_blank($r->FullName) ? (string)$r->FullName : (string)$r->UserName;
                return obj(['Id' => (int)$r->UserId, 'Name' => $name, 'Reaction' => $r->Reaction, 'Role' => $r->Role,
                    'Image' => is_blank($r->ProfileImageLink) ? null : $r->ProfileImageLink, 'Initials' => initials($name, 'U')]);
            }, $rows);
        } catch (Throwable $e) { return []; }
    }

    public function AddPostComment(int $postId, int $userId, ?int $parentId, string $content): int
    {
        $this->EnsureProfilePostTables();
        return $this->insert('INSERT INTO `ProfilePostComments` (`PostId`, `UserId`, `ParentId`, `Content`, `IsEdited`, `CreatedAt`) VALUES (:p, :u, :pa, :c, 0, :t)',
            ['p' => $postId, 'u' => $userId, 'pa' => $parentId, 'c' => $content, 't' => now()]);
    }

    public function UpdatePostComment(int $commentId, string $content): bool
    {
        $this->EnsureProfilePostTables();
        return $this->exec('UPDATE `ProfilePostComments` SET `Content` = :c, `IsEdited` = 1, `UpdatedAt` = :t WHERE `Id` = :id',
            ['c' => $content, 't' => now(), 'id' => $commentId]) >= 0;
    }

    /** কমেন্ট (আর তার রিপ্লাই) মুছে ফেলে। মোট কয়টা মুছলো তা ফেরত দেয়। */
    public function DeletePostComment(int $commentId): int
    {
        try {
            $this->EnsureProfilePostTables();
            $ids = [$commentId];
            foreach ($this->all('SELECT `Id` FROM `ProfilePostComments` WHERE `ParentId` = :p', ['p' => $commentId]) as $r) $ids[] = (int)$r->Id;
            $in = implode(',', $ids);
            $this->exec("DELETE FROM `ProfileCommentReactions` WHERE `CommentId` IN ($in)");
            return $this->exec("DELETE FROM `ProfilePostComments` WHERE `Id` IN ($in)");
        } catch (Throwable $e) { error_log('[DeletePostComment] ' . $e->getMessage()); return 0; }
    }

    /** ইউজার মুছে গেলে তার পোস্ট, কমেন্ট, রিয়েকশন মুছে ফেলা */
    public function DeleteProfileContentOfUser(int $userId): void
    {
        try {
            $this->EnsureProfilePostTables();
            foreach ($this->all('SELECT `Id`, `ImageUrl`, `Meta` FROM `ProfilePosts` WHERE `UserId` = :u', ['u' => $userId]) as $p) {
                $this->DeleteProfilePost((int)$p->Id);
                delete_upload($p->ImageUrl);
                $m = json_decode((string)($p->Meta ?? ''), true);
                if (is_array($m) && !empty($m['file']['url'])) delete_upload((string)$m['file']['url']);
            }
            foreach ($this->all('SELECT `Id` FROM `ProfilePostComments` WHERE `UserId` = :u', ['u' => $userId]) as $c) $this->DeletePostComment((int)$c->Id);
            $this->exec('DELETE FROM `ProfilePostReactions` WHERE `UserId` = :u', ['u' => $userId]);
            $this->exec('DELETE FROM `ProfileCommentReactions` WHERE `UserId` = :u', ['u' => $userId]);
            $extras = $this->GetProfileExtras($userId);
            delete_upload($extras->CoverImageUrl);
            $this->exec('DELETE FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]);
            $this->EnsureLearningTables();
            $this->exec('DELETE FROM `GoalTasks` WHERE `GoalId` IN (SELECT `Id` FROM `UserGoals` WHERE `UserId` = :u)', ['u' => $userId]);
            foreach (['UserGoals', 'SavedItems', 'UserActivityDays', 'PollVotes'] as $t) $this->exec("DELETE FROM `$t` WHERE `UserId` = :u", ['u' => $userId]);
        } catch (Throwable $e) { error_log('[DeleteProfileContentOfUser] ' . $e->getMessage()); }
    }

    public function CountPostComments(int $postId): int
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `ProfilePostComments` WHERE `PostId` = :p', ['p' => $postId]);
    }

    // ============================================================
    // ===== LEARNING PROFILE: Intro, Streak, Goals, Saved, Poll =====
    // ============================================================
    private bool $learnReady = false;

    private function isSqlite(): bool { return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'; }

    public function EnsureLearningTables(): void
    {
        if ($this->learnReady) return;
        $this->EnsureProfileExtrasTable();
        $this->EnsureProfilePostTables();
        // Intro কলাম (Bio, শহর, আগ্রহের বিষয়, প্রিয় উক্তি)
        foreach (['Bio' => 'VARCHAR(160)', 'City' => 'VARCHAR(80)', 'Subjects' => 'VARCHAR(300)', 'Quote' => 'VARCHAR(160)'] as $col => $type) {
            if (!$this->columnExists('UserProfileExtras', $col)) {
                try { $this->pdo->exec("ALTER TABLE `UserProfileExtras` ADD COLUMN `$col` " . ($this->isSqlite() ? 'TEXT' : $type) . ' NULL'); } catch (Throwable $e) { error_log("[extras $col] " . $e->getMessage()); }
            }
        }
        if ($this->isSqlite()) {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "UserActivityDays" ("UserId" INTEGER NOT NULL, "Day" TEXT NOT NULL, "Points" INTEGER NOT NULL DEFAULT 1, PRIMARY KEY ("UserId", "Day"))');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "UserGoals" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "Title" TEXT NOT NULL,
                "Category" TEXT NOT NULL DEFAULT \'Custom\', "TargetDate" TEXT NULL, "ManualProgress" INTEGER NOT NULL DEFAULT 0, "IsPrimary" INTEGER NOT NULL DEFAULT 0,
                "Status" TEXT NOT NULL DEFAULT \'Active\', "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "GoalTasks" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "GoalId" INTEGER NOT NULL, "Title" TEXT NOT NULL,
                "IsDone" INTEGER NOT NULL DEFAULT 0, "CreatedAt" TEXT NOT NULL)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "SavedItems" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "UserId" INTEGER NOT NULL, "ItemType" TEXT NOT NULL,
                "ItemId" INTEGER NOT NULL, "CreatedAt" TEXT NOT NULL, UNIQUE ("UserId", "ItemType", "ItemId"))');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PollOptions" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "PostId" INTEGER NOT NULL, "Text" TEXT NOT NULL, "SortOrder" INTEGER NOT NULL DEFAULT 0)');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS "PollVotes" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "PostId" INTEGER NOT NULL, "OptionId" INTEGER NOT NULL,
                "UserId" INTEGER NOT NULL, "CreatedAt" TEXT NOT NULL, UNIQUE ("PostId", "UserId"))');
        } else {
            $e = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `UserActivityDays` (`UserId` INT NOT NULL, `Day` DATE NOT NULL, `Points` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`UserId`, `Day`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `UserGoals` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `Title` VARCHAR(120) NOT NULL,
                `Category` VARCHAR(30) NOT NULL DEFAULT 'Custom', `TargetDate` DATE NULL, `ManualProgress` TINYINT NOT NULL DEFAULT 0, `IsPrimary` TINYINT(1) NOT NULL DEFAULT 0,
                `Status` VARCHAR(12) NOT NULL DEFAULT 'Active', `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `UpdatedAt` DATETIME NULL,
                PRIMARY KEY (`Id`), KEY `IX_UserGoals_User` (`UserId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `GoalTasks` (`Id` INT NOT NULL AUTO_INCREMENT, `GoalId` INT NOT NULL, `Title` VARCHAR(150) NOT NULL,
                `IsDone` TINYINT(1) NOT NULL DEFAULT 0, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`), KEY `IX_GoalTasks_Goal` (`GoalId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `SavedItems` (`Id` INT NOT NULL AUTO_INCREMENT, `UserId` INT NOT NULL, `ItemType` VARCHAR(12) NOT NULL,
                `ItemId` INT NOT NULL, `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`), UNIQUE KEY `UX_Saved` (`UserId`, `ItemType`, `ItemId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PollOptions` (`Id` INT NOT NULL AUTO_INCREMENT, `PostId` INT NOT NULL, `Text` VARCHAR(100) NOT NULL,
                `SortOrder` INT NOT NULL DEFAULT 0, PRIMARY KEY (`Id`), KEY `IX_PollOptions_Post` (`PostId`)) $e");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `PollVotes` (`Id` INT NOT NULL AUTO_INCREMENT, `PostId` INT NOT NULL, `OptionId` INT NOT NULL, `UserId` INT NOT NULL,
                `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`Id`), UNIQUE KEY `UX_PollVotes` (`PostId`, `UserId`), KEY `IX_PollVotes_Option` (`OptionId`)) $e");
        }
        $this->learnReady = true;
    }

    // ---------- Intro ----------
    /** @return Obj Bio, City, Subjects[], Quote (+ কভার) */
    public function GetIntro(int $userId): Obj
    {
        $out = $this->GetProfileExtras($userId);
        $out->Bio = ''; $out->City = ''; $out->Subjects = []; $out->Quote = '';
        try {
            $this->EnsureLearningTables();
            $r = $this->one('SELECT `Bio`, `City`, `Subjects`, `Quote` FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]);
            if ($r) {
                $out->Bio = (string)($r->Bio ?? ''); $out->City = (string)($r->City ?? ''); $out->Quote = (string)($r->Quote ?? '');
                $out->Subjects = array_values(array_filter(array_map('trim', explode(',', (string)($r->Subjects ?? ''))), fn($x) => $x !== ''));
            }
        } catch (Throwable $e) { error_log('[GetIntro] ' . $e->getMessage()); }
        return $out;
    }

    public function SaveIntro(int $userId, string $bio, string $city, array $subjects, string $quote): bool
    {
        $this->EnsureLearningTables();
        $exists = (int)$this->scalar('SELECT COUNT(*) FROM `UserProfileExtras` WHERE `UserId` = :u', ['u' => $userId]) > 0;
        $p = ['u' => $userId, 'b' => $bio, 'c' => $city, 's' => implode(', ', $subjects), 'q' => $quote, 't' => now()];
        if (!$exists) {
            $this->insert('INSERT INTO `UserProfileExtras` (`UserId`, `CoverPosY`, `Bio`, `City`, `Subjects`, `Quote`, `UpdatedAt`) VALUES (:u, 50, :b, :c, :s, :q, :t)', $p);
            return true;
        }
        return $this->exec('UPDATE `UserProfileExtras` SET `Bio` = :b, `City` = :c, `Subjects` = :s, `Quote` = :q, `UpdatedAt` = :t WHERE `UserId` = :u', $p) >= 0;
    }

    // ---------- Learning streak ----------
    /** আজকের দিনে অ্যাক্টিভিটি লেখা (একই দিনে বারবার হলে পয়েন্ট বাড়ে) */
    public function TouchActivity(int $userId, int $points = 1): void
    {
        try {
            $this->EnsureLearningTables();
            $day = date('Y-m-d');
            $n = $this->exec('UPDATE `UserActivityDays` SET `Points` = `Points` + :p WHERE `UserId` = :u AND `Day` = :d', ['p' => $points, 'u' => $userId, 'd' => $day]);
            if ($n === 0) {
                try { $this->insert('INSERT INTO `UserActivityDays` (`UserId`, `Day`, `Points`) VALUES (:u, :d, :p)', ['u' => $userId, 'd' => $day, 'p' => $points]); }
                catch (Throwable $e) { /* একই মুহূর্তে আরেক রিকোয়েস্ট লিখে ফেলেছে */ }
            }
        } catch (Throwable $e) { error_log('[TouchActivity] ' . $e->getMessage()); }
    }

    /** @return array<string,int> 'Y-m-d' => points (শেষ $days দিন) */
    public function GetActivityDays(int $userId, int $days = 120): array
    {
        try {
            $this->EnsureLearningTables();
            $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
            $out = [];
            foreach ($this->all('SELECT `Day`, `Points` FROM `UserActivityDays` WHERE `UserId` = :u AND `Day` >= :f', ['u' => $userId, 'f' => $from]) as $r) {
                $out[substr((string)$r->Day, 0, 10)] = (int)$r->Points;
            }
            return $out;
        } catch (Throwable $e) { return []; }
    }

    /**
     * টানা কতদিন — আজ বা গতকাল পর্যন্ত (আজ এখনো না এলে গতকালের streak ধরে রাখা হয়)।
     * @return array{current:int, best:int, last7:array<int,bool>, activeToday:bool, totalDays:int}
     */
    public function GetStreak(int $userId): array
    {
        $map = $this->GetActivityDays($userId, 400);
        $current = 0;
        $d = new DateTime('today');
        if (!isset($map[$d->format('Y-m-d')])) $d->modify('-1 day');
        while (isset($map[$d->format('Y-m-d')])) { $current++; $d->modify('-1 day'); }
        $best = 0; $run = 0; $prev = null;
        $keys = array_keys($map); sort($keys);
        foreach ($keys as $k) {
            $run = ($prev !== null && (new DateTime($prev))->modify('+1 day')->format('Y-m-d') === $k) ? $run + 1 : 1;
            $best = max($best, $run); $prev = $k;
        }
        $last7 = [];
        for ($i = 6; $i >= 0; $i--) $last7[] = isset($map[date('Y-m-d', strtotime("-$i days"))]);
        return ['current' => $current, 'best' => max($best, $current), 'last7' => $last7, 'activeToday' => isset($map[date('Y-m-d')]), 'totalDays' => count($map)];
    }

    /** অ্যাক্টিভিটি পেজের সংখ্যা */
    public function GetActivityStats(int $userId): array
    {
        try {
            $this->EnsureLearningTables();
            $q = fn($sql) => (int)$this->scalar($sql, ['u' => $userId]);
            $minutes = 0;
            foreach ($this->all("SELECT `Meta` FROM `ProfilePosts` WHERE `UserId` = :u AND `PostType` = 'study'", ['u' => $userId]) as $r) {
                $m = json_decode((string)$r->Meta, true); $minutes += (int)($m['minutes'] ?? 0);
            }
            return [
                'posts' => $q('SELECT COUNT(*) FROM `ProfilePosts` WHERE `UserId` = :u'),
                'study' => $q("SELECT COUNT(*) FROM `ProfilePosts` WHERE `UserId` = :u AND `PostType` = 'study'"),
                'questions' => $q("SELECT COUNT(*) FROM `ProfilePosts` WHERE `UserId` = :u AND `PostType` = 'question'"),
                'achievements' => $q("SELECT COUNT(*) FROM `ProfilePosts` WHERE `UserId` = :u AND `PostType` = 'achievement'"),
                'comments' => $q('SELECT COUNT(*) FROM `ProfilePostComments` WHERE `UserId` = :u'),
                'reactions' => $q('SELECT COUNT(*) FROM `ProfilePostReactions` WHERE `UserId` = :u'),
                'minutes' => $minutes,
            ];
        } catch (Throwable $e) { return ['posts' => 0, 'study' => 0, 'questions' => 0, 'achievements' => 0, 'comments' => 0, 'reactions' => 0, 'minutes' => 0]; }
    }

    // ---------- Goals ----------
    public const GOAL_CATEGORIES = ['SSC', 'HSC', 'Admission', 'University', 'IELTS', 'Subject', 'Skill', 'Custom'];

    private function mapGoal(Obj $g, array $tasks): Obj
    {
        $done = count(array_filter($tasks, fn($t) => $t->IsDone));
        $progress = $tasks ? (int)round($done / count($tasks) * 100) : max(0, min(100, (int)$g->ManualProgress));
        $daysLeft = null;
        if (!is_blank($g->TargetDate)) $daysLeft = (int)floor((strtotime(substr((string)$g->TargetDate, 0, 10)) - strtotime(date('Y-m-d'))) / 86400);
        return obj([
            'Id' => (int)$g->Id, 'UserId' => (int)$g->UserId, 'Title' => (string)$g->Title, 'Category' => (string)$g->Category,
            'TargetDate' => is_blank($g->TargetDate) ? null : substr((string)$g->TargetDate, 0, 10), 'DaysLeft' => $daysLeft,
            'ManualProgress' => (int)$g->ManualProgress, 'Progress' => $progress, 'IsPrimary' => (bool)(int)$g->IsPrimary,
            'Status' => $progress >= 100 ? 'Completed' : 'Active', 'Tasks' => $tasks, 'TaskDone' => $done, 'UsesTasks' => (bool)$tasks,
        ]);
    }

    /** @return Obj[] প্রধান লক্ষ্য আগে */
    public function GetGoals(int $userId): array
    {
        try {
            $this->EnsureLearningTables();
            $goals = $this->all('SELECT * FROM `UserGoals` WHERE `UserId` = :u ORDER BY `IsPrimary` DESC, `Id` DESC', ['u' => $userId]);
            if (!$goals) return [];
            $ids = implode(',', array_map(fn($g) => (int)$g->Id, $goals));
            $tasks = [];
            foreach ($this->all("SELECT * FROM `GoalTasks` WHERE `GoalId` IN ($ids) ORDER BY `Id`") as $t) {
                $tasks[(int)$t->GoalId][] = obj(['Id' => (int)$t->Id, 'Title' => (string)$t->Title, 'IsDone' => (bool)(int)$t->IsDone]);
            }
            return array_map(fn($g) => $this->mapGoal($g, $tasks[(int)$g->Id] ?? []), $goals);
        } catch (Throwable $e) { error_log('[GetGoals] ' . $e->getMessage()); return []; }
    }

    public function GetGoal(int $goalId): ?Obj
    {
        $this->EnsureLearningTables();
        $g = $this->one('SELECT * FROM `UserGoals` WHERE `Id` = :id', ['id' => $goalId]);
        if (!$g) return null;
        $tasks = array_map(fn($t) => obj(['Id' => (int)$t->Id, 'Title' => (string)$t->Title, 'IsDone' => (bool)(int)$t->IsDone]),
            $this->all('SELECT * FROM `GoalTasks` WHERE `GoalId` = :g ORDER BY `Id`', ['g' => $goalId]));
        return $this->mapGoal($g, $tasks);
    }

    public function CountGoals(int $userId): int
    {
        $this->EnsureLearningTables();
        return (int)$this->scalar('SELECT COUNT(*) FROM `UserGoals` WHERE `UserId` = :u', ['u' => $userId]);
    }

    public function SaveGoal(int $userId, ?int $goalId, string $title, string $category, ?string $targetDate, int $manual, bool $primary): int
    {
        $this->EnsureLearningTables();
        $manual = max(0, min(100, $manual));
        if ($primary || $this->CountGoals($userId) === 0) {
            $this->exec('UPDATE `UserGoals` SET `IsPrimary` = 0 WHERE `UserId` = :u', ['u' => $userId]);
            $primary = true;
        }
        if ($goalId) {
            $this->exec('UPDATE `UserGoals` SET `Title` = :t, `Category` = :c, `TargetDate` = :d, `ManualProgress` = :m, `IsPrimary` = :p, `UpdatedAt` = :n
                WHERE `Id` = :id AND `UserId` = :u', ['t' => $title, 'c' => $category, 'd' => $targetDate, 'm' => $manual, 'p' => $primary ? 1 : 0, 'n' => now(), 'id' => $goalId, 'u' => $userId]);
            return $goalId;
        }
        return $this->insert('INSERT INTO `UserGoals` (`UserId`, `Title`, `Category`, `TargetDate`, `ManualProgress`, `IsPrimary`, `Status`, `CreatedAt`)
            VALUES (:u, :t, :c, :d, :m, :p, \'Active\', :n)', ['u' => $userId, 't' => $title, 'c' => $category, 'd' => $targetDate, 'm' => $manual, 'p' => $primary ? 1 : 0, 'n' => now()]);
    }

    public function DeleteGoal(int $userId, int $goalId): bool
    {
        $this->EnsureLearningTables();
        $g = $this->one('SELECT `IsPrimary` FROM `UserGoals` WHERE `Id` = :id AND `UserId` = :u', ['id' => $goalId, 'u' => $userId]);
        if (!$g) return false;
        $this->exec('DELETE FROM `GoalTasks` WHERE `GoalId` = :g', ['g' => $goalId]);
        $this->exec('DELETE FROM `UserGoals` WHERE `Id` = :g', ['g' => $goalId]);
        if ((int)$g->IsPrimary) { // অন্য একটাকে প্রধান বানাই
            $next = $this->scalar('SELECT `Id` FROM `UserGoals` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 1', ['u' => $userId]);
            if ($next) $this->exec('UPDATE `UserGoals` SET `IsPrimary` = 1 WHERE `Id` = :id', ['id' => $next]);
        }
        return true;
    }

    public function AddGoalTask(int $goalId, string $title): int
    {
        $this->EnsureLearningTables();
        return $this->insert('INSERT INTO `GoalTasks` (`GoalId`, `Title`, `IsDone`, `CreatedAt`) VALUES (:g, :t, 0, :n)', ['g' => $goalId, 't' => $title, 'n' => now()]);
    }

    /** টাস্কের মালিক যাচাই করে টাস্কের GoalId ফেরত দেয় */
    public function GoalIdOfTask(int $taskId, int $userId): ?int
    {
        $this->EnsureLearningTables();
        $g = $this->scalar('SELECT t.`GoalId` FROM `GoalTasks` t INNER JOIN `UserGoals` g ON g.`Id` = t.`GoalId` WHERE t.`Id` = :t AND g.`UserId` = :u', ['t' => $taskId, 'u' => $userId]);
        return $g ? (int)$g : null;
    }

    public function SetGoalTask(int $taskId, ?bool $done, bool $delete = false): void
    {
        if ($delete) { $this->exec('DELETE FROM `GoalTasks` WHERE `Id` = :t', ['t' => $taskId]); return; }
        $this->exec('UPDATE `GoalTasks` SET `IsDone` = :d WHERE `Id` = :t', ['d' => $done ? 1 : 0, 't' => $taskId]);
    }

    public function CountGoalTasks(int $goalId): int
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM `GoalTasks` WHERE `GoalId` = :g', ['g' => $goalId]);
    }

    // ---------- Saved items ----------
    public const SAVE_TYPES = ['post', 'tutor', 'circular', 'tuition', 'notice'];

    public function IsSaved(int $userId, string $type, int $itemId): bool
    {
        $this->EnsureLearningTables();
        return (int)$this->scalar('SELECT COUNT(*) FROM `SavedItems` WHERE `UserId` = :u AND `ItemType` = :t AND `ItemId` = :i', ['u' => $userId, 't' => $type, 'i' => $itemId]) > 0;
    }

    /** @return array<string,true> "type:id" => true (পেজে অনেকগুলো বাটনের অবস্থা একবারে) */
    public function SavedKeys(int $userId, ?string $type = null): array
    {
        try {
            $this->EnsureLearningTables();
            $sql = 'SELECT `ItemType`, `ItemId` FROM `SavedItems` WHERE `UserId` = :u';
            $p = ['u' => $userId];
            if ($type) { $sql .= ' AND `ItemType` = :t'; $p['t'] = $type; }
            $out = [];
            foreach ($this->all($sql, $p) as $r) $out[$r->ItemType . ':' . (int)$r->ItemId] = true;
            return $out;
        } catch (Throwable $e) { return []; }
    }

    /** সেভ/আনসেভ টগল — নতুন অবস্থা ফেরত দেয় */
    public function ToggleSaved(int $userId, string $type, int $itemId): bool
    {
        $this->EnsureLearningTables();
        if ($this->IsSaved($userId, $type, $itemId)) {
            $this->exec('DELETE FROM `SavedItems` WHERE `UserId` = :u AND `ItemType` = :t AND `ItemId` = :i', ['u' => $userId, 't' => $type, 'i' => $itemId]);
            return false;
        }
        try { $this->insert('INSERT INTO `SavedItems` (`UserId`, `ItemType`, `ItemId`, `CreatedAt`) VALUES (:u, :t, :i, :n)', ['u' => $userId, 't' => $type, 'i' => $itemId, 'n' => now()]); }
        catch (Throwable $e) { /* already */ }
        return true;
    }

    public function CountSaved(int $userId): int
    {
        try { $this->EnsureLearningTables(); return (int)$this->scalar('SELECT COUNT(*) FROM `SavedItems` WHERE `UserId` = :u', ['u' => $userId]); }
        catch (Throwable $e) { return 0; }
    }

    /** সেভ করা জিনিসগুলো দেখানোর মতো করে (মুছে যাওয়া/দেখার অনুমতি নেই এমনগুলো "Unavailable") @return Obj[] */
    public function GetSavedItems(int $userId, bool $isAdmin = false): array
    {
        try {
            $this->EnsureLearningTables();
            $rows = $this->all('SELECT * FROM `SavedItems` WHERE `UserId` = :u ORDER BY `Id` DESC', ['u' => $userId]);
        } catch (Throwable $e) { return []; }
        $out = [];
        foreach ($rows as $r) {
            $type = (string)$r->ItemType; $id = (int)$r->ItemId;
            $it = obj(['SaveId' => (int)$r->Id, 'Type' => $type, 'ItemId' => $id, 'SavedAt' => $r->CreatedAt, 'Title' => '', 'Sub' => '', 'Image' => null, 'Link' => '#', 'Available' => true]);
            try {
                switch ($type) {
                    case 'post':
                        $p = $this->GetProfilePostById($id);
                        if (!$p || !$this->CanViewProfilePost($p, $userId, $isAdmin)) { $it->Available = false; break; }
                        $it->Title = $p->Content !== '' ? str_preview($p->Content, 90) : 'Photo post';
                        $it->Sub = $p->AuthorName . ' · ' . time_ago($p->CreatedAt);
                        $it->Image = $p->ImageUrl ?: $p->AuthorImage; $it->Link = '/Post/Details/' . $p->Id;
                        break;
                    case 'tutor':
                        $u = $this->GetUserById($id);
                        if (!$u || $u->Role !== 'Teacher') { $it->Available = false; break; }
                        $subj = (string)$this->scalar('SELECT `SubjectExpertise` FROM `TeacherRequests` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 1', ['u' => $id]);
                        $it->Title = $u->FullName ?: $u->UserName; $it->Sub = trim(($subj !== '' ? $subj . ' · ' : '') . ($u->InstitutionName ?? ''), ' ·') ?: 'Teacher';
                        $it->Image = $u->ProfileImageLink ?: null; $it->Link = '/Profile/Show/' . $id;
                        break;
                    case 'circular':
                        $c = $this->one('SELECT p.*, u.`FullName` FROM `TuitionCircularPosts` p LEFT JOIN `Users` u ON u.`Id` = p.`UserId` WHERE p.`Id` = :id', ['id' => $id]);
                        if (!$c) { $it->Available = false; break; }
                        $it->Title = str_preview($c->Content, 90);
                        $it->Sub = implode(' · ', array_filter([$c->FullName, $c->ClassLevel, $c->PreferredArea, !is_blank($c->Budget) ? '৳' . $c->Budget : null]));
                        $it->Image = $c->ImageUrl ?: null; $it->Link = '/Tuition/Circular#circular-post-' . $id;
                        break;
                    case 'tuition':
                        $t = $this->one('SELECT * FROM `TuitionPosts` WHERE `Id` = :id', ['id' => $id]);
                        if (!$t) { $it->Available = false; break; }
                        $it->Title = (string)$t->Title;
                        $it->Sub = implode(' · ', array_filter([$t->TuitionCode, $t->Area, !is_blank($t->Salary) ? '৳' . $t->Salary : null, $t->Status]));
                        $it->Link = '/Tuition/Details/' . $id;
                        break;
                    case 'notice':
                        $n = $this->one('SELECT * FROM `Notices` WHERE `Id` = :id AND `IsActive` = 1', ['id' => $id]);
                        if (!$n) { $it->Available = false; break; }
                        $it->Title = (string)$n->Title; $it->Sub = $n->Category . ' · ' . fmt_date($n->PublishedDate, 'd M Y');
                        $it->Image = !is_blank($n->ImageUrl) ? $n->ImageUrl : null; $it->Link = '/Notice/Details/' . $id;
                        break;
                    default: $it->Available = false;
                }
            } catch (Throwable $e) { $it->Available = false; }
            $out[] = $it;
        }
        return $out;
    }

    // ---------- My Tutors / My Students ----------
    /**
     * স্টুডেন্টের টিউটর = যেসব টিচারের অ্যাপ্লিকেশন স্টুডেন্ট Accept করেছে।
     * টিচারের ক্ষেত্রে উল্টো: যেসব স্টুডেন্ট তার অ্যাপ্লিকেশন Accept করেছে।
     * @return Obj[]
     */
    public function GetConnectedPeople(int $userId, bool $asTeacher): array
    {
        try {
            $this->EnsureCircularApplicationsTable();
            $sql = $asTeacher
                ? "SELECT DISTINCT p.`UserId` AS Pid FROM `TuitionCircularApplications` a INNER JOIN `TuitionCircularPosts` p ON p.`Id` = a.`PostId` WHERE a.`TeacherId` = :u AND a.`Status` = 'Accepted'"
                : "SELECT DISTINCT a.`TeacherId` AS Pid FROM `TuitionCircularApplications` a INNER JOIN `TuitionCircularPosts` p ON p.`Id` = a.`PostId` WHERE p.`UserId` = :u AND a.`Status` = 'Accepted'";
            $ids = array_map(fn($r) => (int)$r->Pid, $this->all($sql, ['u' => $userId]));
            if (!$ids) return [];
            $out = [];
            foreach ($this->all('SELECT `Id`, `FullName`, `UserName`, `Role`, `ProfileImageLink`, `InstitutionName`, `EducationLevel` FROM `Users` WHERE `Id` IN (' . implode(',', $ids) . ')') as $u) {
                $name = !is_blank($u->FullName) ? (string)$u->FullName : (string)$u->UserName;
                $sub = '';
                if (!$asTeacher) {
                    $subj = (string)$this->scalar('SELECT `SubjectExpertise` FROM `TeacherRequests` WHERE `UserId` = :u ORDER BY `Id` DESC LIMIT 1', ['u' => (int)$u->Id]);
                    $first = trim(explode(',', $subj)[0] ?? '');
                    $sub = $first !== '' ? $first . ' Tutor' : 'Tutor';
                } else {
                    $sub = !is_blank($u->EducationLevel) ? $u->EducationLevel . ' Student' : 'Student';
                }
                $out[] = obj(['Id' => (int)$u->Id, 'Name' => $name, 'Sub' => $sub, 'Image' => is_blank($u->ProfileImageLink) ? null : $u->ProfileImageLink,
                    'Initials' => initials($name, 'U'), 'Institution' => (string)($u->InstitutionName ?? '')]);
            }
            return $out;
        } catch (Throwable $e) { error_log('[GetConnectedPeople] ' . $e->getMessage()); return []; }
    }

    /** স্টুডেন্টের নিজের সার্কুলার পোস্ট (অ্যাপ্লিকেশন সংখ্যাসহ) @return Obj[] */
    public function GetMyCirculars(int $userId): array
    {
        try {
            $this->EnsureCircularApplicationsTable();
            $rows = $this->all('SELECT p.*, (SELECT COUNT(*) FROM `TuitionCircularApplications` a WHERE a.`PostId` = p.`Id`) AS Apps,
                    (SELECT COUNT(*) FROM `TuitionCircularApplications` a WHERE a.`PostId` = p.`Id` AND a.`Status` = \'Pending\') AS PendingApps
                FROM `TuitionCircularPosts` p WHERE p.`UserId` = :u ORDER BY p.`Id` DESC', ['u' => $userId]);
            return array_map(fn($r) => obj(['Id' => (int)$r->Id, 'Content' => (string)$r->Content, 'Area' => $r->PreferredArea, 'Class' => $r->ClassLevel,
                'Subjects' => $r->Subjects, 'Budget' => $r->Budget, 'Image' => $r->ImageUrl, 'CreatedAt' => $r->CreatedAt, 'Apps' => (int)$r->Apps,
                'PendingApps' => (int)$r->PendingApps, 'Likes' => (int)$r->LikeCount]), $rows);
        } catch (Throwable $e) { return []; }
    }

    // ---------- Polls ----------
    public function CreatePollOptions(int $postId, array $options): void
    {
        $this->EnsureLearningTables();
        foreach (array_values($options) as $i => $t) {
            $this->insert('INSERT INTO `PollOptions` (`PostId`, `Text`, `SortOrder`) VALUES (:p, :t, :s)', ['p' => $postId, 't' => $t, 's' => $i]);
        }
    }

    /** @return array<int, array{options: Obj[], total:int, mine:?int}> postId => poll */
    public function GetPolls(array $postIds, ?int $viewerId): array
    {
        if (!$postIds) return [];
        $this->EnsureLearningTables();
        $in = implode(',', array_map('intval', $postIds));
        $polls = [];
        foreach ($this->all("SELECT * FROM `PollOptions` WHERE `PostId` IN ($in) ORDER BY `SortOrder`, `Id`") as $o) {
            $polls[(int)$o->PostId]['options'][(int)$o->Id] = obj(['Id' => (int)$o->Id, 'Text' => (string)$o->Text, 'Votes' => 0]);
        }
        foreach ($this->all("SELECT `PostId`, `OptionId`, COUNT(*) AS N FROM `PollVotes` WHERE `PostId` IN ($in) GROUP BY `PostId`, `OptionId`") as $v) {
            $pid = (int)$v->PostId; $oid = (int)$v->OptionId;
            if (isset($polls[$pid]['options'][$oid])) { $polls[$pid]['options'][$oid]->Votes = (int)$v->N; }
        }
        $mine = [];
        if ($viewerId) foreach ($this->all("SELECT `PostId`, `OptionId` FROM `PollVotes` WHERE `PostId` IN ($in) AND `UserId` = :u", ['u' => $viewerId]) as $v) $mine[(int)$v->PostId] = (int)$v->OptionId;
        foreach ($polls as $pid => &$p) {
            $p['options'] = array_values($p['options']);
            $p['total'] = array_sum(array_map(fn($o) => $o->Votes, $p['options']));
            $p['mine'] = $mine[$pid] ?? null;
        }
        return $polls;
    }

    /** ভোট দেওয়া / বদলানো / তুলে নেওয়া ($optionId = 0) */
    public function VotePoll(int $postId, int $userId, int $optionId): bool
    {
        $this->EnsureLearningTables();
        if ($optionId === 0) { $this->exec('DELETE FROM `PollVotes` WHERE `PostId` = :p AND `UserId` = :u', ['p' => $postId, 'u' => $userId]); return true; }
        $ok = (int)$this->scalar('SELECT COUNT(*) FROM `PollOptions` WHERE `Id` = :o AND `PostId` = :p', ['o' => $optionId, 'p' => $postId]) > 0;
        if (!$ok) return false;
        $n = $this->exec('UPDATE `PollVotes` SET `OptionId` = :o WHERE `PostId` = :p AND `UserId` = :u', ['o' => $optionId, 'p' => $postId, 'u' => $userId]);
        if ($n === 0) {
            try { $this->insert('INSERT INTO `PollVotes` (`PostId`, `OptionId`, `UserId`, `CreatedAt`) VALUES (:p, :o, :u, :n)', ['p' => $postId, 'o' => $optionId, 'u' => $userId, 'n' => now()]); }
            catch (Throwable $e) { $this->exec('UPDATE `PollVotes` SET `OptionId` = :o WHERE `PostId` = :p AND `UserId` = :u', ['o' => $optionId, 'p' => $postId, 'u' => $userId]); }
        }
        return true;
    }

    public function DeletePollOf(int $postId): void
    {
        try {
            $this->EnsureLearningTables();
            $this->exec('DELETE FROM `PollVotes` WHERE `PostId` = :p', ['p' => $postId]);
            $this->exec('DELETE FROM `PollOptions` WHERE `PostId` = :p', ['p' => $postId]);
        } catch (Throwable $e) { }
    }
}
