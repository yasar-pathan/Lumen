<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use Illuminate\Support\Collection;

class RetrievalService
{
    /**
     * Retrieve the top matching knowledge chunks for a query.
     *
     * @param string $query
     * @param int $limit
     * @return Collection Collection of KnowledgeChunk models with a dynamic 'relevance_score' attribute and 'missing_terms' property
     */
    public function retrieveTopChunks(string $query, int $limit = 3): Collection
    {
        $queryTokens = $this->tokenize($query);

        if (empty($queryTokens)) {
            $collection = collect();
            $collection->missing_terms = [];
            return $collection;
        }

        $allChunks = KnowledgeChunk::all();
        $scoredChunks = collect();

        foreach ($allChunks as $chunk) {
            $chunkText = $chunk->title . ' ' . $chunk->content;
            $chunkTokens = $this->tokenize($chunkText);

            $intersection = array_intersect($queryTokens, $chunkTokens);
            $relevance = count($intersection) / max(count($queryTokens), 1);

            if ($relevance > 0) {
                // Attach relevance score directly to the model instance
                $chunk->relevance_score = $relevance;
                $scoredChunks->push($chunk);
            }
        }

        // Sort descending by relevance and limit
        $topChunks = $scoredChunks->sortByDesc('relevance_score')->take($limit)->values();

        // Calculate unmatched query tokens (present in query but absent from all chunks in DB)
        $missingTerms = [];
        foreach ($queryTokens as $token) {
            $foundInAny = false;
            foreach ($allChunks as $chunk) {
                $chunkText = $chunk->title . ' ' . $chunk->content;
                $chunkTokens = $this->tokenize($chunkText);
                if (in_array($token, $chunkTokens)) {
                    $foundInAny = true;
                    break;
                }
            }
            if (!$foundInAny) {
                $missingTerms[] = $token;
            }
        }

        $topChunks->missing_terms = array_values(array_unique($missingTerms));

        return $topChunks;
    }

    /**
     * Tokenize text: lowercase, strip punctuation, split on whitespace, remove stopwords.
     *
     * @param string $text
     * @return array
     */
    public function tokenize(string $text): array
    {
        // Lowercase
        $text = strtolower($text);

        // Strip punctuation
        $text = preg_replace('/[^\w\s]/u', '', $text);

        // Split on whitespace
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Stopwords to exclude
        $stopwords = [
            'the', 'a', 'is', 'are', 'was', 'were', 'what', 'how', 'do', 'does', 'did',
            'to', 'for', 'of', 'in', 'on', 'with', 'an', 'at', 'by', 'from', 'about',
            'this', 'that', 'these', 'those', 'and', 'or', 'but', 'if', 'then', 'else',
            'can', 'will', 'should', 'would', 'could', 'please', 'we', 'our', 'you', 'your'
        ];

        return array_values(array_filter($words, fn($w) => !in_array($w, $stopwords)));
    }
}
