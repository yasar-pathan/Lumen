<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\DiagnosticsResource;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\PromptVersionResource;
use App\Http\Resources\KnowledgeChunkResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PromptVersion;
use App\Models\KnowledgeChunk;
use App\Services\RetrievalService;
use App\Services\DiagnosticsEngine;
use App\Services\LLM\MockProvider;
use App\Services\LLM\OpenRouterProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReplayController extends Controller
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
     * Get the full lifecycle payload of a message.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $message = Message::with(['diagnostics', 'evaluation', 'conversation.promptVersion'])->find($id);

        if (!$message || $message->role !== 'assistant') {
            return response()->json([
                'error' => [
                    'message' => 'Assistant message not found.',
                    'code' => 'NOT_FOUND'
                ]
            ], 404);
        }

        $sourceChunkIds = $message->source_chunk_ids ?? [];
        $chunks = KnowledgeChunk::whereIn('id', $sourceChunkIds)->get();

        return response()->json([
            'data' => [
                'message' => new MessageResource($message),
                'chunks' => KnowledgeChunkResource::collection($chunks),
                'prompt_version' => new PromptVersionResource($message->conversation->promptVersion),
                'diagnostics' => $message->diagnostics ? new DiagnosticsResource($message->diagnostics) : null,
                'evaluation' => $message->evaluation ? new EvaluationResource($message->evaluation) : null,
            ]
        ]);
    }

    /**
     * Re-run the same query against a different prompt version.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function replay(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'prompt_version_id' => ['required', 'exists:prompt_versions,id'],
        ]);

        $originalMessage = Message::with(['conversation'])->find($id);

        if (!$originalMessage || $originalMessage->role !== 'assistant') {
            return response()->json([
                'error' => [
                    'message' => 'Original assistant message not found.',
                    'code' => 'NOT_FOUND'
                ]
            ], 404);
        }

        // Find the user query for this conversation
        $userMessage = Message::where('conversation_id', $originalMessage->conversation_id)
            ->where('role', 'user')
            ->orderBy('id', 'desc')
            ->first();

        if (!$userMessage) {
            return response()->json([
                'error' => [
                    'message' => 'Original user query not found for this conversation.',
                    'code' => 'NOT_FOUND'
                ]
            ], 404);
        }

        $newPromptVersionId = $request->input('prompt_version_id');
        $newPromptVersion = PromptVersion::find($newPromptVersionId);

        // 1. Re-run retrieval with the query
        $sourceChunks = $this->retrievalService->retrieveTopChunks($userMessage->content);

        // 2. Resolve LLM provider (default to mock, check original if possible)
        $providerName = $originalMessage->diagnostics ? $originalMessage->diagnostics->provider_name : 'mock';
        $provider = ($providerName === 'openrouter') ? $this->openRouterProvider : $this->mockProvider;

        // 3. Generate response using the new prompt
        $llmResponse = $provider->generate($newPromptVersion->system_prompt, $userMessage->content, $sourceChunks);

        // 4. Create a new comparative conversation thread
        $newConversation = Conversation::create([
            'prompt_version_id' => $newPromptVersion->id,
            'title' => 'Replay: ' . ($originalMessage->conversation->title ?? 'Untitled'),
        ]);

        // Copy user query
        Message::create([
            'conversation_id' => $newConversation->id,
            'role' => 'user',
            'content' => $userMessage->content,
        ]);

        // Create new assistant message
        $newAssistantMessage = Message::create([
            'conversation_id' => $newConversation->id,
            'role' => 'assistant',
            'content' => $llmResponse->content,
            'source_chunk_ids' => $sourceChunks->pluck('id')->toArray(),
        ]);

        // 5. Run Diagnostics
        $newDiagnostics = $this->diagnosticsEngine->run(
            $newAssistantMessage,
            $sourceChunks,
            $llmResponse->latencyMs,
            $llmResponse->providerName
        );

        return response()->json([
            'data' => [
                'message' => new MessageResource($newAssistantMessage),
                'chunks' => KnowledgeChunkResource::collection($sourceChunks),
                'prompt_version' => new PromptVersionResource($newPromptVersion),
                'diagnostics' => new DiagnosticsResource($newDiagnostics),
                'evaluation' => null, // new run has no human review yet
            ]
        ]);
    }
}
