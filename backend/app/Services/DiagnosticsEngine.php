<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Diagnostics;
use Illuminate\Support\Collection;

/**
 * ARCHITECTURAL PRINCIPLE:
 * Livewire components and API controllers must both delegate to the same service classes.
 * No business logic lives in a controller or a Livewire component — they call a service,
 * get a result, and render/return it. This is a deliberate architecture decision to
 * avoid duplicating scoring/diagnostic logic between the UI and the API layer.
 */
class DiagnosticsEngine
{
    protected RetrievalService $retrievalService;
    protected GroundednessScorer $groundednessScorer;
    protected RootCauseClassifier $rootCauseClassifier;

    public function __construct(
        RetrievalService $retrievalService,
        GroundednessScorer $groundednessScorer,
        RootCauseClassifier $rootCauseClassifier
    ) {
        $this->retrievalService = $retrievalService;
        $this->groundednessScorer = $groundednessScorer;
        $this->rootCauseClassifier = $rootCauseClassifier;
    }

    /**
     * Run diagnostics on an assistant message and persist the results.
     *
     * @param Message $message
     * @param Collection $sourceChunks
     * @param int $latencyMs
     * @param string $providerName
     * @return Diagnostics
     */
    public function run(Message $message, Collection $sourceChunks, int $latencyMs, string $providerName): Diagnostics
    {
        // 1. Calculate average retrieval relevance
        $retrievalRelevanceAvg = $sourceChunks->avg('relevance_score') ?? 0.0;

        // 2. Score groundedness
        $groundednessScore = $this->groundednessScorer->score($message->content, $sourceChunks);

        // Get missing terms and query token count from user query
        $userMessage = Message::where('conversation_id', $message->conversation_id)
            ->where('role', 'user')
            ->orderBy('id', 'desc')
            ->first();

        $userQuery = $userMessage ? $userMessage->content : '';
        $queryTokens = $this->retrievalService->tokenize($userQuery);
        $queryTokenCount = count($queryTokens);
        
        $missingTerms = $sourceChunks->missing_terms ?? [];

        // Check if there is an evaluation flag
        $evaluationFlag = $message->evaluation ? $message->evaluation->flag : null;

        // 3. Classify root cause
        $classification = $this->rootCauseClassifier->classify(
            $retrievalRelevanceAvg,
            $groundednessScore,
            $queryTokenCount,
            $evaluationFlag,
            $missingTerms
        );

        // 4. Run safety check
        $safetyFlag = $this->checkSafety($message->content);

        // 5. Persist diagnostics row
        return Diagnostics::updateOrCreate(
            ['message_id' => $message->id],
            [
                'retrieval_relevance_avg' => $retrievalRelevanceAvg,
                'groundedness_score' => $groundednessScore,
                'root_cause' => $classification['root_cause'],
                'suggested_fix' => $classification['suggested_fix'],
                'missing_terms' => $missingTerms,
                'latency_ms' => $latencyMs,
                'safety_flag' => $safetyFlag,
                'provider_name' => $providerName,
            ]
        );
    }

    /**
     * Run a basic safety check against a list of banned terms and PII regex patterns.
     *
     * @param string $text
     * @return bool
     */
    protected function checkSafety(string $text): bool
    {
        $textLower = strtolower($text);

        // Banned terms
        $bannedTerms = config('services.safety.banned_terms', [
            'social security number',
            'credit card number',
            'banned_word_test',
        ]);

        foreach ($bannedTerms as $term) {
            if (str_contains($textLower, strtolower($term))) {
                return true;
            }
        }

        // PII Regex Patterns
        $piiPatterns = config('services.safety.pii_patterns', [
            '/\b\d{3}-\d{2}-\d{4}\b/',      // SSN: XXX-XX-XXXX
            '/\b(?:\d[ -]*?){13,16}\b/',     // Credit card number: 13-16 digits
        ]);

        foreach ($piiPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }
}
