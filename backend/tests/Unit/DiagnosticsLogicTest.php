<?php

namespace Tests\Unit;

use App\Models\KnowledgeChunk;
use App\Services\RetrievalService;
use App\Services\GroundednessScorer;
use App\Services\RootCauseClassifier;
use App\Services\HealthScoreCalculator;
use Tests\TestCase;

class DiagnosticsLogicTest extends TestCase
{
    /**
     * Test RetrievalService tokenization and stopword removal.
     */
    public function test_retrieval_service_tokenization(): void
    {
        $retrievalService = new RetrievalService();
        $tokens = $retrievalService->tokenize("What's the domestic refund policy?");
        
        // Stops like: "what's" -> stripped punctuation to "whats" (removed as stopword or kept depending on spelling), "the" (removed)
        // Expected alphanumeric tokens remaining: "domestic", "refund", "policy"
        $this->assertContains('domestic', $tokens);
        $this->assertContains('refund', $tokens);
        $this->assertContains('policy', $tokens);
        $this->assertNotContains('the', $tokens);
    }

    /**
     * Test GroundednessScorer Jaccard-like calculations.
     */
    public function test_groundedness_scorer_math(): void
    {
        $retrievalService = new RetrievalService();
        $scorer = new GroundednessScorer($retrievalService);

        $chunk1 = new KnowledgeChunk(['title' => 'Refund', 'content' => 'domestic refund within 30 days']);
        $chunk2 = new KnowledgeChunk(['title' => 'SSO', 'content' => 'enterprise sso setup guidelines']);
        $sourceChunks = collect([$chunk1, $chunk2]);

        // Fully grounded response (all words exist in source chunks)
        $fullyGroundedResponse = "domestic refund sso setup";
        $score = $scorer->score($fullyGroundedResponse, $sourceChunks);
        $this->assertEquals(1.0, $score);

        // Partially grounded response
        $partiallyGroundedResponse = "domestic refund sso invalidwords";
        $score2 = $scorer->score($partiallyGroundedResponse, $sourceChunks);
        // Words in response: domestic, refund, sso, invalidwords (4 words). 
        // 3 of them exist in context (domestic, refund, sso).
        // Score: 3/4 = 0.75
        $this->assertEquals(0.75, $score2);

        // Completely ungrounded response
        $ungroundedResponse = "completely different topics here";
        $score3 = $scorer->score($ungroundedResponse, $sourceChunks);
        $this->assertEquals(0.0, $score3);
    }

    /**
     * Test RootCauseClassifier state machine logic.
     */
    public function test_root_cause_classifier_rules(): void
    {
        $classifier = new RootCauseClassifier();

        // 1. Ambiguous Query Rule: query token count < 3 AND retrieval relevance < 0.15
        $res1 = $classifier->classify(0.10, 1.0, 2, null);
        $this->assertEquals('ambiguous_query', $res1['root_cause']);
        $this->assertStringContainsString('short or broad', $res1['suggested_fix']);

        // 2. Knowledge Gap Rule: retrieval relevance < 0.15 (and query tokens >= 3)
        $res2 = $classifier->classify(0.10, 1.0, 5, null, ['international', 'refund']);
        $this->assertEquals('knowledge_gap', $res2['root_cause']);
        $this->assertStringContainsString('international, refund', $res2['suggested_fix']);

        // 3. Hallucination Rule: groundedness < 0.4 (relevance >= 0.15)
        $res3 = $classifier->classify(0.50, 0.35, 4, null);
        $this->assertEquals('hallucination', $res3['root_cause']);
        $this->assertStringContainsString('diverged from it', $res3['suggested_fix']);

        // 4. Prompt Instruction Issue Rule: reviewer flag is incorrect
        $res4 = $classifier->classify(0.60, 0.85, 4, 'incorrect');
        $this->assertEquals('prompt_instruction_issue', $res4['root_cause']);
        $this->assertStringContainsString('marked incorrect', $res4['suggested_fix']);

        // 5. Healthy case
        $res5 = $classifier->classify(0.80, 0.95, 4, 'good');
        $this->assertEquals('healthy', $res5['root_cause']);
        $this->assertNull($res5['suggested_fix']);
    }
}
