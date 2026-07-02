<?php

namespace App\Livewire;

use App\Models\PromptVersion;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Diagnostics;
use App\Models\Evaluation;
use App\Services\RetrievalService;
use App\Services\DiagnosticsEngine;
use App\Services\LLM\MockProvider;
use App\Services\LLM\OpenRouterProvider;
use Livewire\Component;
use Illuminate\Support\Str;

class TestConsole extends Component
{
    public string $query = '';
    public $selectedPromptVersionId = '';
    public bool $useLiveModel = false;

    // Output variables
    public ?string $lastResponse = null;
    public ?int $assistantMessageId = null;
    public $retrievedChunks = [];
    public ?Diagnostics $lastDiagnostics = null;

    // Review properties (F3 Evaluation)
    public $rating = 5;
    public $flag = 'good';
    public $notes = '';
    public $reviewerName = 'Auditor';
    public bool $isReviewed = false;

    protected function rules(): array
    {
        return [
            'query' => 'required|string|min:2',
            'selectedPromptVersionId' => 'required|exists:prompt_versions,id',
        ];
    }

    public function mount()
    {
        // Default to the first approved prompt, or fallback to latest
        $approved = PromptVersion::where('status', 'approved')->orderBy('version', 'desc')->first();
        if ($approved) {
            $this->selectedPromptVersionId = $approved->id;
        } else {
            $latest = PromptVersion::orderBy('version', 'desc')->first();
            if ($latest) {
                $this->selectedPromptVersionId = $latest->id;
            }
        }
    }

    public function run(
        RetrievalService $retrievalService,
        MockProvider $mockProvider,
        OpenRouterProvider $openRouterProvider,
        DiagnosticsEngine $diagnosticsEngine
    ) {
        $this->validate();

        // Reset review fields
        $this->isReviewed = false;
        $this->rating = 5;
        $this->flag = 'good';
        $this->notes = '';

        // 1. Retrieve Matching Chunks
        $chunks = $retrievalService->retrieveTopChunks($this->query);
        $this->retrievedChunks = $chunks->toArray();

        // 2. Select Provider
        $provider = $this->useLiveModel ? $openRouterProvider : $mockProvider;

        // 3. Get prompt version
        $promptVersion = PromptVersion::findOrFail($this->selectedPromptVersionId);

        // 4. Generate response
        $llmResponse = $provider->generate($promptVersion->system_prompt, $this->query, $chunks);
        $this->lastResponse = $llmResponse->content;

        // 5. Create conversation
        $conversation = Conversation::create([
            'prompt_version_id' => $promptVersion->id,
            'title' => 'Console: ' . Str::limit($this->query, 40),
        ]);

        // User query message
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $this->query,
        ]);

        // Assistant response message
        $assistantMsg = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $llmResponse->content,
            'source_chunk_ids' => $chunks->pluck('id')->toArray(),
        ]);
        $this->assistantMessageId = $assistantMsg->id;

        // 6. Run diagnostics
        $this->lastDiagnostics = $diagnosticsEngine->run(
            $assistantMsg,
            $chunks,
            $llmResponse->latencyMs,
            $llmResponse->providerName
        );
    }

    public function submitReview(RetrievalService $retrievalService, DiagnosticsEngine $diagnosticsEngine)
    {
        if (!$this->assistantMessageId) return;

        $evaluation = Evaluation::updateOrCreate(
            ['message_id' => $this->assistantMessageId],
            [
                'reviewer_name' => $this->reviewerName ?: 'Anonymous Auditor',
                'rating' => (int) $this->rating,
                'flag' => $this->flag,
                'notes' => $this->notes,
            ]
        );

        $this->isReviewed = true;

        // Re-run diagnostics to reflect review flags (specifically for 'incorrect' review trigger)
        $message = Message::findOrFail($this->assistantMessageId);
        $message->load('evaluation');

        if ($message->diagnostics) {
            $sourceChunks = $retrievalService->retrieveTopChunks($this->query);
            $this->lastDiagnostics = $diagnosticsEngine->run(
                $message,
                $sourceChunks,
                $message->diagnostics->latency_ms,
                $message->diagnostics->provider_name
            );
        }

        session()->flash('review_message', 'Audit evaluation saved and diagnostics recalculated.');
    }

    public function render()
    {
        $promptVersions = PromptVersion::orderBy('version', 'desc')->get();

        return view('livewire.test-console', [
            'promptVersions' => $promptVersions
        ])->layout('components.layouts.app');
    }
}
