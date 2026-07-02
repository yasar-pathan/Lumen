<?php

namespace App\Services;

use App\Models\Diagnostics;
use Illuminate\Support\Collection;

class KnowledgeGapDetector
{
    /**
     * Detect the top recurring knowledge gaps.
     *
     * @param int $limit
     * @return Collection Collection of arrays with keys: term, count, example_message_id
     */
    public function topGaps(int $limit = 10): Collection
    {
        // Pull diagnostics rows where root cause is knowledge gap
        $rows = Diagnostics::where('root_cause', 'knowledge_gap')->get();

        $gaps = [];

        foreach ($rows as $row) {
            $terms = $row->missing_terms ?? [];
            foreach ($terms as $term) {
                $term = strtolower(trim($term));
                if (empty($term)) {
                    continue;
                }

                if (!isset($gaps[$term])) {
                    $gaps[$term] = [
                        'term' => $term,
                        'count' => 0,
                        'example_message_id' => $row->message_id,
                    ];
                }

                $gaps[$term]['count']++;
            }
        }

        // Sort descending by count, then alphabetically by term
        uasort($gaps, function ($a, $b) {
            if ($b['count'] === $a['count']) {
                return strcmp($a['term'], $b['term']);
            }
            return $b['count'] <=> $a['count'];
        });

        return collect(array_values($gaps))->take($limit);
    }
}
