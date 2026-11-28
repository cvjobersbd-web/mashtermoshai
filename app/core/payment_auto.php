<?php
/**
 * EPS এ পেমেন্ট সফল হলে প্যাকেজ / কোর্স / Apply নিজে থেকেই চালু — অ্যাডমিনকে Accept করতে হয় না (অ্যাডমিন প্যানেলে শুধু হিস্টোরি)।
 * অ্যাডমিনের Accept এর মতোই কাজ করে (Subscription, কোর্সে ভর্তি, প্রকাশ ফি, রেফারেল) আর ইউজারকে নোটিফিকেশন দেয়।
 * টিচার: Teacher Verification আগেই Approve থাকলে সাথে সাথে টিচার; না থাকলে প্যাকেজ চালু থাকে, Verification Approve হলেই টিচার হয়ে যায়।
 */
function payment_auto_approve(int $id, string $note = 'EPS — পেমেন্ট সফল, নিজে থেকেই চালু'): ?Obj
{
    $db = db();
    try { $p = $db->ApprovePayment($id, 0, $note); } catch (Throwable $e) { error_log('[auto approve] ' . $e->getMessage()); return null; }
    if (!$p) return null;
    $uid = (int)$p->UserId;
    $say = function (string $title, string $msg, string $link, string $icon = 'fa-circle-check') use ($db, $uid) { try { $db->CreateNotification($uid, $title, $msg, 'success', $link, $icon); } catch (Throwable $e) { } };
    try {
        if ($p->Audience === 'Publish') {
            $c = $db->GetCourse((int)$p->CourseId);
            $kl = $c && $c->Kind === 'live' ? 'লাইভ ক্লাস' : 'কোর্স';
            $say($kl . ' প্রকাশ হয়েছে 🎉', 'প্রকাশ ফি পরিশোধ হয়েছে — “' . ($c->Title ?? '') . '” এখন সবাই দেখতে ও কিনতে পারবে।', '/Course/Build/' . (int)$p->CourseId, 'fa-rocket');
            return $p;
        }
        if (!empty($p->CourseId)) {
            $c = $db->GetCourse((int)$p->CourseId);
            $e = $db->GetEnrollment((int)$p->CourseId, $uid);
            $say('কোর্স খুলে গেছে 🎉', '“' . ($c->Title ?? $p->PackageName) . '” কোর্সে আপনি ভর্তি হয়েছেন — এখনই ক্লাস শুরু করুন।', '/Course/Learn/' . (int)$p->CourseId, 'fa-graduation-cap');
            if ($c && $e && ($c->TeacherRole ?? '') !== 'Admin') { try { $db->CreateNotification((int)$c->TeacherId, 'নতুন কোর্স বিক্রি 💰', ($p->FullName ?: 'একজন শিক্ষার্থী') . " “{$c->Title}” কিনেছেন — আপনার ভাগ " . taka((int)$e->TeacherShare) . '।', 'success', '/Course/Earnings', 'fa-sack-dollar'); } catch (Throwable $x) { } }
            return $p;
        }
        if (isset(Db::APPLY_AUDIENCES[$p->Audience])) {
            $left = $db->GetApplyCredits($uid, $p->Audience);
            $say('পেমেন্ট সফল ✅', $p->Audience === 'TApply'
                ? 'আপনার ' . taka((int)$p->Amount) . ' পরিশোধ হয়েছে — এখন Tuition Circular এর যেকোনো ' . bn_num($left) . 'টা পোস্টে Apply করতে পারবেন।'
                : 'আপনার ' . taka((int)$p->Amount) . ' পরিশোধ হয়েছে — এখন "পড়াতে চাই" পেইজে যেকোনো ' . bn_num($left) . 'জন টিচারের পোস্টে Apply করতে পারবেন।',
                $p->Audience === 'TApply' ? '/Tuition/Circular' : '/FindTutor#postFeed');
            return $p;
        }
        if ($p->Audience === 'Teacher') {
            $sub = $db->GetActiveTeacherSubscription($uid);
            $msg = "আপনার “{$p->PackageName}” প্যাকেজ (" . taka((int)$p->Amount) . ") চালু হয়েছে" . ($sub && $sub->EndsAt ? ' — ' . date('d M Y', strtotime((string)$sub->EndsAt)) . ' পর্যন্ত।' : '।');
            $u = $db->GetUserById($uid);
            $tr = $u && $u->Role === 'User' ? $db->GetLatestTeacherRequest($uid) : null;
            if ($tr && $tr->Status === 'Pending' && (int)($tr->PaymentId ?? 0) === (int)$p->Id) {
                $hadBefore = (int)$db->scalar("SELECT COUNT(*) FROM `UserSubscriptions` WHERE `UserId` = :u AND `Audience` = 'Teacher' AND (`PaymentId` IS NULL OR `PaymentId` <> :p)", ['u' => $uid, 'p' => (int)$p->Id]) > 0;
                if ($hadBefore) {   // মেয়াদ শেষে রিনিউ — আবার টিচার
                    $db->UpdateTeacherRequest((int)$tr->Id, 'Approved', 'রিনিউ — EPS পেমেন্ট');
                    $db->MakeUserTeacher($uid);
                    $msg .= ' আপনার টিচার অ্যাকাউন্ট আবার চালু হয়েছে।';
                } elseif ($db->FinalizeTeacherIfReady((int)$tr->Id)) {
                    $msg .= ' আপনার টিচার আবেদন Approve হয়েছে — এখন আপনি টিচার! 🎓';
                } else {
                    $msg .= ' অ্যাডমিন আপনার Teacher Verification যাচাই করে Approve করলেই আপনি টিচার হয়ে যাবেন।';
                }
            }
            $say('পেমেন্ট সফল ✅', $msg, '/Payment/My');
            return $p;
        }
        $say('পেমেন্ট সফল ✅', "আপনার “{$p->PackageName}” প্যাকেজ (" . taka((int)$p->Amount) . ") চালু হয়েছে।", '/Payment/My');
    } catch (Throwable $e) { error_log('[auto approve notify] ' . $e->getMessage()); }
    return $p;
}

/** EPS এ পরিশোধ হয়ে Pending থেকে যাওয়া পুরোনো পেমেন্ট (নিয়ম বদলের আগের) — সব চালু করে দেওয়া */
function payment_auto_approve_pending_eps(): int
{
    $n = 0;
    try { foreach (db()->all("SELECT `Id` FROM `PaymentRequests` WHERE `Status` = 'Pending' AND `MethodName` = 'EPS'") as $r) if (payment_auto_approve((int)$r->Id)) $n++; }
    catch (Throwable $e) { error_log('[auto approve pending eps] ' . $e->getMessage()); }
    return $n;
}
