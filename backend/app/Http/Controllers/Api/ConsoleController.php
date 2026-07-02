<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunQueryRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\DiagnosticsResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PromptVersion;
use App\Services\RetrievalService;
use App\Services\DiagnosticsEngine;
use App\Services\LLM\MockProvider;
use App\Services\LLM\OpenRouterProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ConsoleController extends Controller
{
    protected RetrievalService $retrievalService;
    protected DiagnosticsEngine $diagnosticsEngine;
    protected MockProvider $mockProvider;
    protected OpenRouterProvider $openRouterProvider;

    public function __construct(
        RetrievalService $retrievalService,
        DiagnosticsEngine $diagnosticsEngine,
        MockProvider $mockProvider,
        OpenRouterProvider $openRouterProvider
    ) {
        $this->retrievalService = $retrievalService;
        $this->diagnosticsEngine = $diagnosticsEngine;
        $this->mockProvider = $mockProvider;
        $this->openRouterProvider = $openRouterProvider;
    }

    /**
     * Run a console query and retrieve diagnostics.
     *
     * @param RunQueryRequest $request
     * @return JsonResponse
     */
    public function query(RunQueryRequest $request): JsonResponse
    {
        $promptVersionId = $request->input('prompt_version_id');
        $query = $request->input('query');
        $providerName = $request->input('provider', 'mock');

        $promptVersion = PromptVersion::findOrFail($promptVersionId);

        // 1. Retrieve matching chunks
        $sourceChunks = $this->retrievalService->retrieveTopChunks($query);

        // 2. Select LLM provider
        $provider = ($providerName === 'openrouter') ? $this->openRouterProvider : $this->mockProvider;

        // 3. Generate response
        $llmResponse = $provider->generate($promptVersion->system_prompt, $query, $sourceChunks);

        // 4. Create Conversation and Message entities
        $title = 'Console: ' . Str::limit($query, 40);
        $conversation = Conversation::create([
            'prompt_version_id' => $promptVersion->id,
            'title' => $title,
        ]);

        // User Message
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $query,
        ]);

        // Assistant Message
        $assistantMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $llmResponse->content,
            'source_chunk_ids' => $sourceChunks->pluck('id')->toArray(),
        ]);

        // 5. Run Diagnostics Engine
        $diagnostics = $this->diagnosticsEngine->run(
            $assistantMessage,
            $sourceChunks,
            $llmResponse->latencyMs,
            $llmResponse->providerName
        );

        // Load relations for response
        $assistantMessage->load(['evaluation']);

        return response()->json([
            'data' => [
                'message' => new MessageResource($assistantMessage),
                'diagnostics' => new DiagnosticsResource($diagnostics),
            ]
        ]);
    }
}
