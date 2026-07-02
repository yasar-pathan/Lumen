<?php

namespace App\Services;

use Illuminate\Support\Collection;

class GroundednessScorer
{
    protected RetrievalService $retrievalService;

    public function __construct(RetrievalService $retrievalService)
    {
        $this->retrievalService = $retrievalService;
    }

    /**
     * Compute a groundedness score for the response text based on retrieved chunks.
     *
     * @param string $responseText
     * @param Collection $sourceChunks Collection of KnowledgeChunk models
     * @return float
     */
    public function score(string $responseText, Collection $sourceChunks): float
    {
        $responseTokens = $this->retrievalService->tokenize($responseText);

        if (empty($responseTokens)) {
            return 1.0;
        }

        // Union of all tokens in retrieved source chunks
        $sourceTokens = [];
        foreach ($sourceChunks as $chunk) {
            $chunkText = $chunk->title . ' ' . $chunk->content;
            $chunkTokens = $this->retrievalService->tokenize($chunkText);
            $sourceTokens = array_merge($sourceTokens, $chunkTokens);
        }
        $sourceTokens = array_values(array_unique($sourceTokens));

        if (empty($sourceTokens)) {
            return 0.0;
        }

        $intersection = array_intersect($responseTokens, $sourceTokens);
        $score = count($intersection) / count($responseTokens);

        // Clamp between 0.0 and 1.0
        return (float) min(max($score, 0.0), 1.0);
    }
}
