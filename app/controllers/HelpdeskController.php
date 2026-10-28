<?php
class HelpdeskController extends Controller
{
    private function requireAjaxLogin(): void
    {
        if (session_get('UserName') === null) json_response(['success' => false, 'message' => 'Unauthorized']);
    }

    private static function truncate(?string $text, int $max): string
    {
        $text = (string)$text;
        return mb_strlen($text) <= $max ? $text : mb_substr($text, 0, $max) . '...';
    }

    /** GET /Helpdesk?filter=all|mine|admin&search= */
    public function Index(): void
    {
        if (session_get('UserName') === null) $this->redirectToAction('Login', 'Account', ['returnUrl' => '/Helpdesk']);
        $userId = current_user_id() ?? 0;
        $filter = input_str('filter', 'all');
        if (!in_array($filter, ['all', 'mine', 'admin'], true)) $filter = 'all';
        $search = input_str('search');

        $this->view(null, $this->db->GetHelpdeskPosts($filter, $userId, $search), [
            'CurrentFilter' => $filter,
            'SearchQuery' => $search,
            'CurrentUserId' => $userId,
            'IsAdmin' => is_admin(),
        ]);
    }

    /** GET /Helpdesk/GetPostComments?postId= */
    public function GetPostComments(): void
    {
        $this->requireAjaxLogin();
        $map = fn($c) => [
            'id' => $c->Id, 'content' => $c->Content, 'mediaUrl' => $c->MediaUrl, 'mediaType' => $c->MediaType,
            'isAdminReply' => (bool)$c->IsAdminReply, 'createdAt' => to_dt($c->CreatedAt)?->format('Y-m-d\TH:i:s'),
            'userFullName' => $c->UserFullName, 'userName' => $c->UserName, 'userRole' => $c->UserRole,
            'userProfileImage' => $c->UserProfileImage, 'initials' => $c->Initials, 'timeAgo' => $c->TimeAgo,
        ];
        $comments = array_map(function ($c) use ($map) {
            $row = $map($c);
            $row['replyCount'] = $c->ReplyCount;
            $row['replies'] = array_map($map, $c->Replies);
            return $row;
        }, $this->db->GetHelpdeskComments(input_int('postId', 0)));
        json_response(['success' => true, 'comments' => $comments]);
    }

    /** POST /Helpdesk/CreatePost */
    public function CreatePost_POST(): void
    {
        $this->requireAjaxLogin();
        $userId = current_user_id() ?? 0;
        if ($userId === 0) json_response(['success' => false, 'message' => 'User not found']);
        $title = input_str('title');
        $content = input_str('content');
        if (is_blank($title)) json_response(['success' => false, 'message' => 'Title is required.']);
        if (is_blank($content)) json_response(['success' => false, 'message' => 'Content is required.']);

        $isAdmin = is_admin();
        $mediaUrl = input_str('mediaUrl');
        $mediaType = input_str('mediaType');
        $post = obj([
            'UserId' => $userId, 'Title' => trim($title), 'Content' => trim($content),
            'MediaUrl' => is_blank($mediaUrl) ? null : trim($mediaUrl),
            'MediaType' => is_blank($mediaType) ? null : trim($mediaType),
            'IsAdminPost' => $isAdmin,
        ]);
        $err = null;
        if ($this->db->CreateHelpdeskPost($post, $err)) {
            if ($isAdmin) {
                try {
                    foreach ($this->db->GetAllUsers() as $u) {
                        if ($u->Role === 'Admin') continue;
                        $this->db->CreateNotification($u->Id, '📢 New Announcement from Admin', "Admin posted: {$post->Title}", 'info', '/Helpdesk', 'fa-bullhorn');
                    }
                } catch (Throwable $e) {}
            }
            json_response(['success' => true, 'message' => 'Post created successfully!', 'postId' => $post->Id]);
        }
        json_response(['success' => false, 'message' => $err ?? 'Failed to create post.']);
    }

    /** POST /Helpdesk/UploadMedia (file) */
    public function UploadMedia_POST(): void
    {
        $this->requireAjaxLogin();
        $file = upload_file('file');
        if (!$file) json_response(['success' => false, 'message' => 'No file selected.']);

        $ext = strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));
        $imageExts = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        $videoExts = ['.mp4', '.webm', '.ogg', '.mov'];
        if (in_array($ext, $imageExts, true)) { $mediaType = 'image'; $max = 5 * 1024 * 1024; }
        elseif (in_array($ext, $videoExts, true)) { $mediaType = 'video'; $max = 50 * 1024 * 1024; }
        else json_response(['success' => false, 'message' => 'Unsupported file type.']);

        if ($file['size'] > $max) {
            json_response(['success' => false, 'message' => 'File too large. Max ' . ($mediaType === 'image' ? 5 : 50) . 'MB allowed.']);
        }
        try {
            $url = save_upload($file, 'helpdesk', 'hd_' . ticks(), $ext);
            json_response(['success' => true, 'url' => $url, 'mediaType' => $mediaType]);
        } catch (Throwable $e) {
            json_response(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
        }
    }

    /** POST /Helpdesk/AddComment */
    public function AddComment_POST(): void
    {
        $this->requireAjaxLogin();
        $userId = current_user_id() ?? 0;
        if ($userId === 0) json_response(['success' => false, 'message' => 'User not found']);
        $content = input_str('content');
        if (is_blank($content)) json_response(['success' => false, 'message' => 'Comment cannot be empty.']);

        $isAdmin = is_admin();
        $postId = input_int('postId', 0);
        $post = $this->db->GetHelpdeskPostById($postId);
        if (!$post) json_response(['success' => false, 'message' => 'Post not found.']);

        $parentId = input_int('parentCommentId', null);
        $mediaUrl = input_str('mediaUrl');
        $mediaType = input_str('mediaType');
        $comment = obj([
            'PostId' => $postId, 'UserId' => $userId, 'Content' => trim($content),
            'MediaUrl' => is_blank($mediaUrl) ? null : trim($mediaUrl),
            'MediaType' => is_blank($mediaType) ? null : trim($mediaType),
            'IsAdminReply' => $isAdmin, 'ParentCommentId' => $parentId,
        ]);
        $err = null;
        if (!$this->db->CreateHelpdeskComment($comment, $err)) json_response(['success' => false, 'message' => $err ?? 'Failed to add comment.']);

        try {
            $name = session_get('UserFullName') ?? session_get('UserName') ?? 'Someone';
            $short = self::truncate($content, 100);
            if ($isAdmin && $post->UserId !== $userId) {
                $this->db->CreateNotification($post->UserId, '💬 Admin replied to your post', "Admin commented on \"{$post->Title}\": $short", 'success', '/Helpdesk', 'fa-comment-dots');
            } elseif (!$isAdmin) {
                foreach ($this->db->GetAdmins() as $admin) {
                    $this->db->CreateNotification((int)$admin->Id, '💬 New comment on Helpdesk', "$name commented on \"{$post->Title}\": $short", 'info', '/Helpdesk', 'fa-comment');
                }
                if ($post->UserId !== $userId) {
                    $this->db->CreateNotification($post->UserId, '💬 New reply on your post', "$name replied: $short", 'info', '/Helpdesk', 'fa-reply');
                }
            }
            if ($parentId !== null) {
                $parent = null;
                foreach ($this->db->GetHelpdeskComments($postId) as $c) {
                    if ($c->Id === $parentId) { $parent = $c; break; }
                    foreach ($c->Replies as $r) if ($r->Id === $parentId) { $parent = $r; break 2; }
                }
                if ($parent && $parent->UserId !== $userId) {
                    $this->db->CreateNotification($parent->UserId, '↩️ New reply to your comment', "$name replied: $short", 'info', '/Helpdesk', 'fa-reply');
                }
            }
        } catch (Throwable $e) {}

        json_response(['success' => true, 'message' => 'Comment added successfully!', 'commentId' => $comment->Id]);
    }

    /** POST /Helpdesk/DeletePost */
    public function DeletePost_POST(): void
    {
        $this->requireAjaxLogin();
        if ($this->db->DeleteHelpdeskPost(input_int('id', 0), current_user_id() ?? 0, is_admin())) {
            json_response(['success' => true, 'message' => 'Post deleted.']);
        }
        json_response(['success' => false, 'message' => 'Failed to delete post.']);
    }

    /** POST /Helpdesk/DeleteComment */
    public function DeleteComment_POST(): void
    {
        $this->requireAjaxLogin();
        if ($this->db->DeleteHelpdeskComment(input_int('id', 0), current_user_id() ?? 0, is_admin())) {
            json_response(['success' => true, 'message' => 'Comment deleted.']);
        }
        json_response(['success' => false, 'message' => 'Failed to delete comment.']);
    }

    /** POST /Helpdesk/AcknowledgePost (Admin) */
    public function AcknowledgePost_POST(): void
    {
        if (!is_admin()) json_response(['success' => false, 'message' => 'Unauthorized']);
        if ($this->db->AcknowledgeHelpdeskPost(input_int('id', 0), true)) json_response(['success' => true, 'message' => 'Marked as acknowledged.']);
        json_response(['success' => false, 'message' => 'Failed.']);
    }

    /** POST /Helpdesk/UpdateStatus (Admin) */
    public function UpdateStatus_POST(): void
    {
        if (!is_admin()) json_response(['success' => false, 'message' => 'Unauthorized']);
        $status = input_str('status');
        if (!in_array($status, ['Open', 'Solved', 'Closed'], true)) json_response(['success' => false, 'message' => 'Invalid status.']);
        if ($this->db->UpdateHelpdeskPostStatus(input_int('id', 0), $status)) json_response(['success' => true, 'message' => 'Status updated.']);
        json_response(['success' => false, 'message' => 'Failed.']);
    }
}
