<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EvaluationResource;
use App\Models\Evaluation;
use App\Models\Message;
use App\Services\RetrievalService;
use App\Services\DiagnosticsEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    protected DiagnosticsEngine $diagnosticsEngine;
    protected RetrievalService $retrievalService;

    public function __construct(DiagnosticsEngine $diagnosticsEngine, RetrievalService $retrievalService)
    {
        $this->diagnosticsEngine = $diagnosticsEngine;
        $this->retrievalService = $retrievalService;
    }

    /**
     * Store or update a message evaluation.
     *
     * @param Request $request
     * @param int $messageId
     * @return JsonResponse|EvaluationResource
     */
    public function store(Request $request, int $messageId): JsonResponse|EvaluationResource
    {
        $request->validate([
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'flag' => ['required', 'string', 'in:good,incorrect,hallucination'],
            'notes' => ['nullable', 'string'],
        ]);

        $message = Message::find($messageId);

        if (!$message || $message->role !== 'assistant') {
            return response()->json([
                'error' => [
                    'message' => 'Assistant message not found.',
                    'code' => 'NOT_FOUND'
                ]
            ], 404);
        }

        // Store or update evaluation
        $evaluation = Evaluation::updateOrCreate(
            ['message_id' => $messageId],
            [
                'reviewer_name' => $request->input('reviewer_name'),
                'rating' => $request->input('rating'),
                'flag' => $request->input('flag'),
                'notes' => $request->input('notes'),
            ]
        );

        // Force reload evaluation relation on message
        $message->load('evaluation');

        // If diagnostics already exist, re-run them so root cause is updated (e.g. to prompt_instruction_issue)
        if ($message->diagnostics) {
            $userMessage = Message::where('conversation_id', $message->conversation_id)
                ->where('role', 'user')
                ->orderBy('id', 'desc')
                ->first();

            $sourceChunks = collect();
            if ($userMessage) {
                // Re-run retrieval to load matching chunks and their Jaccard relevance scores
                $sourceChunks = $this->retrievalService->retrieveTopChunks($userMessage->content);
            }

            $this->diagnosticsEngine->run(
                $message,
                $sourceChunks,
                $message->diagnostics->latency_ms,
                $message->diagnostics->provider_name
            );
        }

        return new EvaluationResource($evaluation);
    }
}
