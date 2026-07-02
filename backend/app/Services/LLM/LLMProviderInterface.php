<?php

namespace App\Services\LLM;

use Illuminate\Support\Collection;

interface LLMProviderInterface
{
    /**
     * Generate a response using the prompt and context chunks.
     *
     * @param string $systemPrompt
     * @param string $userQuery
     * @param Collection $contextChunks Collection of KnowledgeChunk models
     * @return LLMResponse
     */
    public function generate(string $systemPrompt, string $userQuery, Collection $contextChunks): LLMResponse;
}
