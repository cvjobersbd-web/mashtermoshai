<?php
/**
 * ফ্রেন্ড সিস্টেম বন্ধ করা হয়েছে — এখন ইউজার শুধু অন্যের প্রোফাইল দেখতে পারে,
 * আর কে কার প্রোফাইল দেখেছে তা /Profile/Views পেজে দেখা যায় (LinkedIn এর মতো)।
 * পুরোনো /Friends লিংকগুলো যেন ভেঙে না যায়, তাই সেগুলো নতুন পেজে পাঠানো হয়।
 */
class FriendsController extends Controller
{
    public function Index(): void
    {
        redirect(current_user_id() === null ? '/Account/Login?returnUrl=' . rawurlencode('/Profile/Views') : '/Profile/Views');
    }

    /** পুরোনো পেজ/স্ক্রিপ্ট এখনো কল করলে — ফ্রেন্ড রিকোয়েস্ট আর নেই */
    public function Count(): void { json_response(['success' => true, 'count' => 0]); }
}
