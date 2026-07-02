<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class HealthScoreCalculator
{
    /**
     * Calculate the composite AI Health Score and its breakdown.
     *
     * @param int $lookbackDays
     * @return array ['score' => int, 'breakdown' => array]
     */
    public function calculate(int $lookbackDays = 7): array
    {
        $sinceDate = now()->subDays($lookbackDays);

        $results = DB::table('diagnostics')
            ->join('messages', 'diagnostics.message_id', '=', 'messages.id')
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->join('prompt_versions', 'conversations.prompt_version_id', '=', 'prompt_versions.id')
            ->where('diagnostics.created_at', '>=', $sinceDate)
            ->select([
                DB::raw('COUNT(*) as total_count'),
                DB::raw('AVG(diagnostics.groundedness_score) as avg_groundedness'),
                DB::raw('SUM(CASE WHEN diagnostics.root_cause = \'hallucination\' THEN 1 ELSE 0 END) as hallucination_count'),
                DB::raw('SUM(CASE WHEN prompt_versions.status = \'approved\' THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('AVG(diagnostics.latency_ms) as avg_latency'),
                DB::raw('SUM(CASE WHEN diagnostics.safety_flag = true THEN 1 ELSE 0 END) as safety_violation_count'),
            ])
            ->first();

        // Default empty state
        if (!$results || $results->total_count == 0) {
            return [
                'score' => 100,
                'breakdown' => [
                    'grounding_pct' => 100,
                    'hallucination_rate' => 0,
                    'governance_pct' => 100,
                    'avg_latency_ms' => 0,
                    'latency_score' => 100,
                    'safety_score' => 100,
                    'total_runs' => 0,
                ]
            ];
        }

        $totalCount = (int) $results->total_count;

        // grounding (0 - 1)
        $grounding = (float) ($results->avg_groundedness ?? 0.0);
        $groundingPct = $grounding * 100;

        // hallucination_rate (0 - 1)
        $hallucinationRate = $results->hallucination_count / $totalCount;
        $hallucinationRatePct = $hallucinationRate * 100;

        // governance_pct (0 - 1)
        $governance = $results->approved_count / $totalCount;
        $governancePct = $governance * 100;

        // avg_latency_ms
        $avgLatencyMs = (float) ($results->avg_latency ?? 0.0);
        // latency_score = clamp(100 - (avg_latency_ms - 1000) / 40, 0, 100)
        $latencyScore = 100 - (($avgLatencyMs - 1000) / 40);
        $latencyScore = (float) min(max($latencyScore, 0.0), 100.0);

        // safety_score = (1 - COUNT(safety_flag = true) / COUNT(*)) * 100
        $safetyRate = $results->safety_violation_count / $totalCount;
        $safetyScore = (1 - $safetyRate) * 100;

        // score calculation
        $score = (int) round(
            0.35 * $grounding * 100 +
            0.25 * (1 - $hallucinationRate) * 100 +
            0.15 * $governance * 100 +
            0.15 * $latencyScore +
            0.10 * $safetyScore
        );

        return [
            'score' => $score,
            'breakdown' => [
                'grounding_pct' => (int) round($groundingPct),
                'hallucination_rate' => (int) round($hallucinationRatePct),
                'governance_pct' => (int) round($governancePct),
                'avg_latency_ms' => (int) round($avgLatencyMs),
                'latency_score' => (int) round($latencyScore),
                'safety_score' => (int) round($safetyScore),
                'total_runs' => $totalCount,
            ]
        ];
    }
}
