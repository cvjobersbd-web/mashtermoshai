<?php
/**
 * ইউজার পুরোপুরি মুছে ফেলা (শুধু Super Admin) — Users এর সারি মোছার পর তার সব ডাটা:
 * পোস্ট, কমেন্ট, লাইক, পেমেন্ট (রিসিট ফাইল সহ), প্যাকেজ, চ্যাট, রেফারেল, উইথড্র, নোটিফিকেশন, ডিভাইস, প্রোফাইল ভিউ ...
 * কোনো টেবিল না থাকলে (এখনো তৈরি হয়নি) চুপচাপ এড়িয়ে যায়।
 */
trait UserPurgeData
{
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
        foreach ([
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
        ] as $t => $cols) foreach ($cols as $c) $x("DELETE FROM `$t` WHERE `$c` = :u");
        // অন্যদের "সেভ করা টিউটর" তালিকা থেকেও
        $this->purgeExec("DELETE FROM `SavedItems` WHERE `ItemType` = 'tutor' AND `ItemId` = :u", $p);

        foreach (array_unique($files) as $f) { try { delete_upload($f); } catch (Throwable $e) { } }
        return $n;
    }
}
