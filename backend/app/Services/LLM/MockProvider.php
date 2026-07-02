<?php

namespace App\Services\LLM;

use Illuminate\Support\Collection;

class MockProvider implements LLMProviderInterface
{
    /**
     * Generate a simulated LLM response.
     *
     * @param string $systemPrompt
     * @param string $userQuery
     * @param Collection $contextChunks
     * @return LLMResponse
     */
    public function generate(string $systemPrompt, string $userQuery, Collection $contextChunks): LLMResponse
    {
        $startTime = microtime(true);

        // Simulate network latency (150ms to 400ms)
        $simulatedLatencyMs = rand(150, 400);
        usleep($simulatedLatencyMs * 1000);

        $queryLower = strtolower($userQuery);

        if ($contextChunks->isNotEmpty()) {
            // Take the first chunk as the primary source
            $primaryChunk = $contextChunks->first();
            
            // Build a plausible grounded response
            $content = "According to our document \"{$primaryChunk->title}\": " .
                       "{$primaryChunk->content} " .
                       "If you have further questions, please refer back to this document.";
        } else {
            // Fabricate a confident hallucination
            if (str_contains($queryLower, 'refund')) {
                $content = "Our international refund policy allows you to request a full refund within 60 days of the shipping date for international orders.";
            } elseif (str_contains($queryLower, 'shipping') || str_contains($queryLower, 'delivery')) {
                $content = "We deliver standard orders globally within 10-15 business days. Express shipping options are available at checkout.";
            } else {
                $content = "Yes, our platform supports that feature. You can enable it directly from your Advanced Settings pane under the integrations section.";
            }
        }

        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        return new LLMResponse(
            content: $content,
            latencyMs: max($latencyMs, $simulatedLatencyMs),
            providerName: 'mock'
        );
    }
}
