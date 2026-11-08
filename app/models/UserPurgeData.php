<?php
/**
 * ইউজার পুরোপুরি মুছে ফেলা (শুধু Super Admin) — Users এর সারি মোছার পর তার সব ডাটা:
 * পোস্ট, কমেন্ট, লাইক, পেমেন্ট (রিসিট ফাইল সহ), প্যাকেজ, চ্যাট, রেফারেল, উইথড্র, নোটিফিকেশন, ডিভাইস, প্রোফাইল ভিউ ...
 * কোনো টেবিল না থাকলে (এখনো তৈরি হয়নি) চুপচাপ এড়িয়ে যায়।
 */
trait UserPurgeData
{
    /** টেবিল => ইউজারের Id যে কলামে থাকে */
    public const PURGE_COLS = [
            'TeacherRequests' => ['UserId'], 'TutorRequests' => ['StudentUserId', 'TeacherUserId'], 'Notifications' => ['UserId'], 'PasswordResets' => ['UserId'],
            'UserSuspensions' => ['UserId'], 'Reviews' => ['UserId'], 'TuitionApplications' => ['UserId'],
            'TuitionCircularComments' => ['UserId'], 'TuitionCircularLikes' => ['UserId'], 'TuitionCircularCommentLikes' => ['UserId'], 'TuitionCircularApplications' => ['TeacherId'],
            'TeachWantedComments' => ['UserId'], 'TeachWantedLikes' => ['UserId'], 'TeachWantedCommentLikes' => ['UserId'], 'HelpdeskComments' => ['UserId'],
            'Friendships' => ['RequesterId', 'AddresseeId'], 'ProfileViews' => ['ProfileUserId', 'ViewerId'], 'ProfileViewSeen' => ['UserId'],
            'TeacherDetails' => ['UserId'], 'UserDevices' => ['UserId'], 'ProfilePostReactions' => ['UserId'], 'ProfilePostComments' => ['UserId'],
            'ProfileCommentReactions' => ['UserId'], 'UserProfileExtras' => ['UserId'], 'UserActivityDays' => ['UserId'], 'UserGoals' => ['UserId'],
            'SavedItems' => ['UserId'], 'PollVotes' => ['UserId'], 'PaymentRequests' => ['UserId'], 'UserSubscriptions' => ['UserId'], 'EmailOtps' => ['UserId'],
            'EduPostLikes' => ['UserId'], 'EduPostComments' => ['UserId'], 'TeacherStudents' => ['TeacherId', 'StudentUserId'], 'TeacherClasses' => ['TeacherId'],
            'TeacherPayments' => ['TeacherId', 'StudentId'], 'TeacherReviews' => ['TeacherId', 'StudentUserId'], 'TeacherCertificates' => ['TeacherId'],
            'ReferralCodes' => ['UserId'], 'Referrals' => ['ReferrerId', 'ReferredUserId'], 'ReferralWithdrawals' => ['UserId'], 'PartnerSignups' => ['UserId'],
            'AdminAccess' => ['UserId'],
        ];

    /** মুছে ফেলা ইউজারের বাকি থাকা ডাটা খুঁজতে যে টেবিলগুলো দেখা হয় — বাংলা নাম */
    public const ORPHAN_LABELS = [
        'PaymentRequests' => 'পেমেন্ট', 'UserSubscriptions' => 'প্যাকেজ', 'TeacherRequests' => 'টিচার আবেদন', 'TutorRequests' => 'টিউটর রিকোয়েস্ট',
        'Referrals' => 'রেফারেল', 'ReferralCodes' => 'রেফারেল কোড', 'ReferralWithdrawals' => 'উইথড্র', 'Notifications' => 'নোটিফিকেশন',
        'TuitionCircularPosts' => 'টিউশন পোস্ট', 'TuitionCircularComments' => 'টিউশন কমেন্ট', 'TuitionCircularLikes' => 'টিউশন লাইক', 'TuitionApplications' => 'টিউশন আবেদন',
        'TuitionCircularApplications' => 'টিউশন আবেদন', 'TeachWantedPosts' => 'পড়াতে চাই পোস্ট', 'TeachWantedComments' => 'পড়াতে চাই কমেন্ট', 'TeachWantedLikes' => 'পড়াতে চাই লাইক',
        'HelpdeskPosts' => 'হেল্পডেস্ক', 'HelpdeskComments' => 'হেল্পডেস্ক কমেন্ট', 'EduPosts' => 'Edu Feed পোস্ট', 'EduPostLikes' => 'Edu Feed লাইক', 'EduPostComments' => 'Edu Feed কমেন্ট',
        'ProfilePosts' => 'প্রোফাইল পোস্ট', 'ProfilePostComments' => 'প্রোফাইল কমেন্ট', 'ProfilePostReactions' => 'রিয়্যাকশন', 'Friendships' => 'ফ্রেন্ড', 'ProfileViews' => 'প্রোফাইল ভিউ',
        'Reviews' => 'রিভিউ', 'TeacherDetails' => 'টিচারের তথ্য', 'TeacherStudents' => 'টিচারের ছাত্র', 'TeacherPayments' => 'টিচারের পেমেন্ট হিসাব', 'TeacherReviews' => 'টিচার রিভিউ',
        'TeacherCertificates' => 'সার্টিফিকেট', 'ChatMessages' => 'চ্যাট মেসেজ', 'ChatDirect' => 'চ্যাট', 'ChatGroups' => 'চ্যাট গ্রুপ', 'ChatGroupMembers' => 'গ্রুপ মেম্বার',
        'UserDevices' => 'ডিভাইস', 'PartnerSignups' => 'এজেন্সি সাইনআপ', 'SavedItems' => 'সেভ করা', 'UserGoals' => 'গোল', 'UserProfileExtras' => 'প্রোফাইল তথ্য',
        'UserActivityDays' => 'অ্যাক্টিভিটি', 'UserSuspensions' => 'সাসপেন্ড', 'PasswordResets' => 'পাসওয়ার্ড রিসেট', 'EmailOtps' => 'OTP', 'PollVotes' => 'পোল ভোট',
    ];

    /**
     * Users টেবিলে নেই (মুছে ফেলা) কিন্তু অন্য টেবিলে ডাটা রয়ে গেছে এমন ইউজার Id গুলো।
     * রিটার্ন: [Id => ['items' => [বাংলা নাম => সংখ্যা], 'total' => সারি, 'paid' => ৳, 'hint' => নাম/ইমেইল (জানা থাকলে)]]
     */
    public function FindOrphanUsers(): array
    {
        $out = [];
        $scan = self::PURGE_COLS + ['ChatMessages' => ['SenderId'], 'ChatDirect' => ['StudentId', 'TeacherId'], 'ChatGroups' => ['TeacherId'], 'ChatGroupMembers' => ['UserId'],
            'EduPosts' => ['UserId'], 'TuitionCircularPosts' => ['UserId'], 'TeachWantedPosts' => ['UserId'], 'HelpdeskPosts' => ['UserId'], 'ProfilePosts' => ['UserId']];
        foreach ($scan as $t => $cols) {
            if (!isset(self::ORPHAN_LABELS[$t])) continue;
            foreach ($cols as $c) {
                try {
                    $rows = $this->all("SELECT `$c` AS U, COUNT(*) AS N FROM `$t` WHERE `$c` IS NOT NULL AND `$c` > 0 AND `$c` NOT IN (SELECT `Id` FROM `Users`) GROUP BY `$c`");
                } catch (Throwable $e) { continue; }
                foreach ($rows as $r) {
                    $id = (int)$r->U; $lbl = self::ORPHAN_LABELS[$t];
                    $out[$id] ??= ['items' => [], 'total' => 0, 'paid' => 0, 'hint' => ''];
                    $out[$id]['items'][$lbl] = ($out[$id]['items'][$lbl] ?? 0) + (int)$r->N;
                    $out[$id]['total'] += (int)$r->N;
                }
            }
        }
        foreach ($out as $id => &$o) {
            try { $o['paid'] = (int)$this->scalar("SELECT COALESCE(SUM(`Amount`), 0) FROM `PaymentRequests` WHERE `UserId` = :u AND `Status` = 'Approved'", ['u' => $id]); } catch (Throwable $e) { }
            foreach (["SELECT `FullName` FROM `TeacherRequests` WHERE `UserId` = :u", "SELECT `Email` FROM `TeacherRequests` WHERE `UserId` = :u",
                      "SELECT `StudentName` FROM `TutorRequests` WHERE `StudentUserId` = :u", "SELECT `AccountName` FROM `ReferralWithdrawals` WHERE `UserId` = :u"] as $q) {
                try { $v = $this->scalar($q . ' LIMIT 1', ['u' => $id]); if (is_string($v) && trim($v) !== '') { $o['hint'] = trim($v); break; } } catch (Throwable $e) { }
            }
        }
        unset($o);
        krsort($out);
        return $out;
    }

    /** একটা DELETE — টেবিল/কলাম না থাকলে এড়িয়ে যায়; কতগুলো সারি মুছল */
    private function purgeExec(string $sql, array $p): int
    {
        try { return (int)$this->exec($sql, $p); } catch (Throwable $e) { return 0; }
    }

    private function purgeCol(string $sql, array $p): array
    {
        try { return array_map(fn($r) => array_values((array)$r)[0], $this->all($sql, $p)); } catch (Throwable $e) { return []; }
    }

    /** রিটার্ন: মোট কতগুলো সারি মুছেছে */
    public function PurgeUserData(int $u, array $files = []): int
    {
        $p = ['u' => $u]; $n = 0;
        $x = function (string $sql) use (&$n, $p) { $n += $this->purgeExec($sql, $p); };

        // ---- আপলোড করা ফাইল (মোছার আগে লিংকগুলো নিয়ে রাখা) ----
        foreach (['SELECT `ReceiptUrl` FROM `PaymentRequests` WHERE `UserId` = :u', 'SELECT `ImageUrl` FROM `TeacherCertificates` WHERE `TeacherId` = :u',
                  'SELECT `FileUrl` FROM `TeacherCertificates` WHERE `TeacherId` = :u', 'SELECT `MediaUrl` FROM `HelpdeskComments` WHERE `UserId` = :u',
                  'SELECT `ImageUrl` FROM `EduPosts` WHERE `UserId` = :u', 'SELECT `CoverImageLink` FROM `UserProfileExtras` WHERE `UserId` = :u'] as $q)
            foreach ($this->purgeCol($q, $p) as $f) if (is_string($f)) $files[] = $f;

        // ---- পোস্টের নিচের সব (কমেন্ট / লাইক / আবেদন) — অন্যদেরগুলোও, কারণ পোস্টই থাকছে না ----
        foreach ([['TuitionCircularPosts', ['TuitionCircularComments', 'TuitionCircularLikes', 'TuitionCircularApplications']],
                  ['TeachWantedPosts', ['TeachWantedComments', 'TeachWantedLikes']],
                  ['HelpdeskPosts', ['HelpdeskComments']],
                  ['EduPosts', ['EduPostLikes', 'EduPostComments']],
                  ['ProfilePosts', ['ProfilePostComments', 'ProfilePostReactions', 'PollOptions', 'PollVotes']]] as [$parent, $kids]) {
            foreach ($kids as $k) $x("DELETE FROM `$k` WHERE `PostId` IN (SELECT `Id` FROM `$parent` WHERE `UserId` = :u)");
            $x("DELETE FROM `$parent` WHERE `UserId` = :u");
        }
        // কমেন্টের লাইক (নিজের কমেন্টের)
        $x('DELETE FROM `TuitionCircularCommentLikes` WHERE `CommentId` IN (SELECT `Id` FROM `TuitionCircularComments` WHERE `UserId` = :u)');
        $x('DELETE FROM `TeachWantedCommentLikes` WHERE `CommentId` IN (SELECT `Id` FROM `TeachWantedComments` WHERE `UserId` = :u)');
        $x('DELETE FROM `ProfileCommentReactions` WHERE `CommentId` IN (SELECT `Id` FROM `ProfilePostComments` WHERE `UserId` = :u)');
        $x('DELETE FROM `GoalTasks` WHERE `GoalId` IN (SELECT `Id` FROM `UserGoals` WHERE `UserId` = :u)');

        // ---- চ্যাট: তার সরাসরি কথোপকথন আর তার বানানো গ্রুপ পুরোটা ----
        $x("DELETE FROM `ChatMessages` WHERE `Kind` = 'dir' AND `AppId` IN (SELECT `Id` FROM `ChatDirect` WHERE `StudentId` = :u OR `TeacherId` = :u)");
        $x("DELETE FROM `ChatClasses` WHERE `Kind` = 'dir' AND `AppId` IN (SELECT `Id` FROM `ChatDirect` WHERE `StudentId` = :u OR `TeacherId` = :u)");
        $x('DELETE FROM `ChatDirect` WHERE `StudentId` = :u OR `TeacherId` = :u');
        $x("DELETE FROM `ChatMessages` WHERE `Kind` = 'grp' AND `AppId` IN (SELECT `Id` FROM `ChatGroups` WHERE `TeacherId` = :u)");
        $x("DELETE FROM `ChatClasses` WHERE `Kind` = 'grp' AND `AppId` IN (SELECT `Id` FROM `ChatGroups` WHERE `TeacherId` = :u)");
        $x('DELETE FROM `ChatGroupMembers` WHERE `GroupId` IN (SELECT `Id` FROM `ChatGroups` WHERE `TeacherId` = :u)');
        $x('DELETE FROM `ChatGroups` WHERE `TeacherId` = :u');
        $x('DELETE FROM `ChatGroupMembers` WHERE `UserId` = :u');
        $x('DELETE FROM `ChatMessages` WHERE `SenderId` = :u');
        $x('DELETE FROM `ChatClasses` WHERE `TeacherId` = :u');

        // ---- বাকি সব টেবিল যেখানে এই ইউজার আছে ----
        foreach (self::PURGE_COLS as $t => $cols) foreach ($cols as $c) $x("DELETE FROM `$t` WHERE `$c` = :u");
        // অন্যদের "সেভ করা টিউটর" তালিকা থেকেও
        $this->purgeExec("DELETE FROM `SavedItems` WHERE `ItemType` = 'tutor' AND `ItemId` = :u", $p);

        foreach (array_unique($files) as $f) { try { delete_upload($f); } catch (Throwable $e) { } }
        return $n;
    }
}
