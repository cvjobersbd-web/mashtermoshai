<?php
class NoticeController extends Controller
{
    /** GET /Notice/Index?search=&category= */
    public function Index(): void
    {
        $search = input_str('search');
        $category = input_str('category');
        $all = $this->db->GetAllNotices();

        if (!is_blank($search)) {
            $q = mb_strtolower(trim($search));
            $all = array_values(array_filter($all, fn($n) =>
                str_contains(mb_strtolower($n->Title ?? ''), $q) ||
                str_contains(mb_strtolower($n->Content ?? ''), $q) ||
                str_contains(mb_strtolower($n->Category ?? ''), $q)));
        }
        if (!is_blank($category) && $category !== 'all') {
            $all = array_values(array_filter($all, fn($n) => strcasecmp($n->Category ?? '', $category) === 0));
        }

        $announcements = array_values(array_filter($all, fn($n) => $n->IsAnnouncement));
        $news = array_values(array_filter($all, fn($n) => !$n->IsAnnouncement));
        usort($news, fn($a, $b) => strcmp((string)$b->PublishedDate, (string)$a->PublishedDate));

        $this->view('Notice', $news, [
            'Announcements' => $announcements,
            'SearchQuery' => $search,
            'SelectedCategory' => $category ?? 'all',
        ]);
    }

    /** GET /Notice/Details/{id} */
    public function Details(): void
    {
        $id = input_int('id', 0);
        $notice = $this->db->GetNoticeById($id);
        if (!$notice) {
            tempdata_set('Error', 'Notice not found.');
            $this->redirectToAction('Index');
        }
        $more = array_values(array_filter($this->db->GetAllNotices(), fn($n) => !$n->IsAnnouncement && $n->Id !== $id));
        usort($more, fn($a, $b) => strcmp((string)$b->PublishedDate, (string)$a->PublishedDate));
        $this->view(null, $notice, ['MoreNews' => array_slice($more, 0, 6)]);
    }
}
