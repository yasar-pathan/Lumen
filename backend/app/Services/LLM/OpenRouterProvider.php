<?php

namespace App\Services\LLM;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProvider implements LLMProviderInterface
{
    protected MockProvider $mockProvider;

    public function __construct(MockProvider $mockProvider)
    {
        $this->mockProvider = $mockProvider;
    }

    /**
     * Generate response via OpenRouter API with graceful fallback to MockProvider.
     *
     * @param string $systemPrompt
     * @param string $userQuery
     * @param Collection $contextChunks
     * @return LLMResponse
     */
    public function generate(string $systemPrompt, string $userQuery, Collection $contextChunks): LLMResponse
    {
        $apiKey = config('services.openrouter.key');
        $model = config('services.openrouter.model', 'google/gemini-2.5-flash');

        // Fall back to MockProvider if API Key is not configured
        if (empty($apiKey)) {
            Log::info('OpenRouter API key is missing. Falling back to MockProvider.');
            return $this->mockProvider->generate($systemPrompt, $userQuery, $contextChunks);
        }

        $startTime = microtime(true);

        try {
            // Join context chunk content
            $contextText = $contextChunks->map(fn($c) => "[Excerpt from {$c->title}]: {$c->content}")->implode("\n\n");
            $userContent = $userQuery . ($contextText ? "\n\nContext:\n" . $contextText : "");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(10) // 10 second timeout
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userContent],
                ],
            ]);

            if ($response->failed()) {
                throw new \Exception("OpenRouter API returned HTTP status " . $response->status() . ": " . $response->body());
            }

            $responseData = $response->json();
            $content = $responseData['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                throw new \Exception("OpenRouter API returned empty response choices.");
            }

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return new LLMResponse(
                content: $content,
                latencyMs: $latencyMs,
                providerName: 'openrouter'
            );

        } catch (\Throwable $e) {
            // FALLBACK BEHAVIOR: Catch errors and return response from MockProvider instead of throwing
            Log::error('OpenRouter request failed. Falling back to MockProvider. Error: ' . $e->getMessage());
            
            // Re-run using the mock provider
            return $this->mockProvider->generate($systemPrompt, $userQuery, $contextChunks);
        }
    }
}
