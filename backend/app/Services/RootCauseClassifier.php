<?php

namespace App\Services;

class RootCauseClassifier
{
    /**
     * Classify the root cause of an assistant response issue.
     *
     * @param float $retrievalRelevanceAvg
     * @param float $groundednessScore
     * @param int $queryTokenCount
     * @param string|null $evaluationFlag
     * @param array $missingTerms
     * @return array ['root_cause' => string, 'suggested_fix' => string|null]
     */
    public function classify(
        float $retrievalRelevanceAvg,
        float $groundednessScore,
        int $queryTokenCount,
        ?string $evaluationFlag,
        array $missingTerms = []
    ): array {
        // 1. Ambiguous Query Check
        if ($queryTokenCount < 3 && $retrievalRelevanceAvg < 0.15) {
            return [
                'root_cause' => 'ambiguous_query',
                'suggested_fix' => 'The query was too short or broad to retrieve relevant context. Consider adding a clarifying-question step before answering.',
            ];
        }

        // 2. Knowledge Gap Check
        if ($retrievalRelevanceAvg < 0.15) {
            $termsStr = !empty($missingTerms) ? implode(', ', $missingTerms) : 'this topic';
            return [
                'root_cause' => 'knowledge_gap',
                'suggested_fix' => "No matching knowledge found for: {$termsStr}. Add a knowledge article covering this topic.",
            ];
        }

        // 3. Hallucination Check
        if ($groundednessScore < 0.4) {
            return [
                'root_cause' => 'hallucination',
                'suggested_fix' => 'Relevant context was retrieved but the response diverged from it. Tighten the system prompt to require answers drawn only from provided context, with explicit citation.',
            ];
        }

        // 4. Prompt Instruction Issue Check
        if ($evaluationFlag === 'incorrect') {
            return [
                'root_cause' => 'prompt_instruction_issue',
                'suggested_fix' => 'Response was grounded and specific but still marked incorrect — review system prompt phrasing, tone, or output format instructions.',
            ];
        }

        // Default: Healthy
        return [
            'root_cause' => 'healthy',
            'suggested_fix' => null,
        ];
    }
}
