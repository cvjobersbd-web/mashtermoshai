<?php
/**
 * Categories — সব টিউশন ক্যাটাগরি (/Categories) আর একটা ক্যাটাগরির পেজ (/Categories/Details/{slug})
 */
class CategoriesController extends Controller
{
    public function Index(): void
    {
        $groups = array_values(array_filter($this->db->GetCategoryGroups(true), fn($g) => $g->Categories));
        $this->view(null, $groups);
    }

    public function Details(): void
    {
        $slug = strtolower(trim((string)input_str('id', '')));
        $c = $slug !== '' ? $this->db->GetCategoryBySlug($slug) : null;
        // বন্ধ থাকা ক্যাটাগরি শুধু অ্যাডমিন দেখতে পারে (প্রিভিউ)
        if (!$c || ((!$c->IsActive || !(int)$c->GroupActive) && !is_admin())) not_found();

        $tutors = [];
        try { $tutors = category_tutors($c, $this->db->GetTutorDirectory()); } catch (Throwable $e) { error_log('[category tutors] ' . $e->getMessage()); }
        $this->view(null, $c, [
            'Texts' => category_texts($c),
            'Tutors' => array_slice($tutors, 0, 12),
            'TutorTotal' => count($tutors),
            'Related' => $this->db->GetRelatedCategories($c, 12),
        ]);
    }
}
