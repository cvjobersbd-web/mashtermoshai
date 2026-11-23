<?php
/**
 * Categories — সব টিউশন ক্যাটাগরি (/Categories) আর একটা ক্যাটাগরির পেজ (/Categories/Details/{slug})
 */
require_once APP_ROOT . '/app/core/tutor_filters.php';

class CategoriesController extends Controller
{
    public function Index(): void
    {
        $groups = array_values(array_filter($this->db->GetCategoryGroups(true), fn($g) => $g->Categories));
        // শিক্ষাব্যবস্থা অনুযায়ী (জাতীয় / ব্রিটিশ / মাদ্রাসা) — প্রতিটা ধাপে কতজন টিউটর
        $cur = tf_curricula();
        try {
            $facets = tf_facets($this->db, $this->db->GetTutorDirectory());
            foreach ($cur as $k => $c) {
                foreach ($c['stages'] as $i => $st) $cur[$k]['stages'][$i][3] = tf_count_for($facets, $st[2]);
                $cur[$k]['subjects'] = array_map(fn($s) => [$s, tf_count_for($facets, ['med' => $c['stages'][0][2]['med'] ?? [], 'sub' => [$s]])], $c['subjects']);
                $cur[$k]['total'] = tf_count_for($facets, ['med' => $c['stages'][0][2]['med'] ?? []]);
            }
        } catch (Throwable $e) { error_log('[Categories curricula] ' . $e->getMessage()); }
        $this->view(null, $groups, ['Curricula' => $cur]);
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
