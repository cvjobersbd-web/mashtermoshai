<?php
class NotificationController extends Controller
{
    private static function timeAgoShort($dt): string
    {
        // Notification এ ৩০ দিনের বেশি হলে "dd MMM" দেখায়
        return time_ago($dt, 'd M');
    }

    /** GET /Notification/GetNotifications */
    public function GetNotifications(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'notifications' => [], 'unreadCount' => 0]);

        $list = $this->db->GetUserNotifications($userId, 20);
        $unread = $this->db->GetUnreadNotificationCount($userId);
        json_response([
            'success' => true,
            'unreadCount' => $unread,
            'notifications' => array_map(fn($n) => [
                'id' => $n->Id,
                'title' => $n->Title,
                'message' => $n->Message,
                'type' => $n->Type,
                'icon' => $n->Icon,
                'link' => $n->Link,
                'isRead' => (bool)$n->IsRead,
                'timeAgo' => self::timeAgoShort($n->CreatedAt),
                'createdAt' => fmt_date($n->CreatedAt, 'd M Y, h:i A'),
            ], $list),
        ]);
    }

    /** GET /Notification/GetUnreadCount */
    public function GetUnreadCount(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'count' => 0]);
        json_response(['success' => true, 'count' => $this->db->GetUnreadNotificationCount($userId)]);
    }

    /** POST /Notification/MarkAsRead */
    public function MarkAsRead_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Unauthorized']);
        json_response(['success' => $this->db->MarkNotificationAsRead(input_int('id', 0), $userId)]);
    }

    /** POST /Notification/MarkAllAsRead */
    public function MarkAllAsRead_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Unauthorized']);
        json_response(['success' => $this->db->MarkAllNotificationsAsRead($userId)]);
    }

    /** POST /Notification/ClearAll */
    public function ClearAll_POST(): void
    {
        $userId = current_user_id();
        if ($userId === null) json_response(['success' => false, 'message' => 'Unauthorized']);
        json_response(['success' => $this->db->ClearUserNotifications($userId)]);
    }

    /** GET /Notification/All */
    public function All(): void
    {
        $userId = current_user_id();
        if ($userId === null) $this->redirectToAction('Login', 'Account');
        $list = $this->db->GetUserNotifications($userId, 100);
        $this->db->MarkAllNotificationsAsRead($userId);
        $this->view(null, $list);
    }
}
