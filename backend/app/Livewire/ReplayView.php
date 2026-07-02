<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\PromptVersion;
use App\Models\KnowledgeChunk;
use App\Models\Conversation;
use App\Models\Diagnostics;
use App\Services\RetrievalService;
use App\Services\DiagnosticsEngine;
use App\Services\LLM\MockProvider;
use App\Services\LLM\OpenRouterProvider;
use Livewire\Component;

class ReplayView extends Component
{
    public int $messageId;
    
    // Core Models
    public Message $message;
    public ?Message $userMessage = null;
    public $retrievedChunks = [];
    public $promptVersions = [];

    // Comparative Replay
    public $selectedPromptVersionId = '';
    public ?array $fixedPromptResult = null;

    public function mount(int $message)
    {
        $this->messageId = $message;
        $this->loadOriginalData();
        $this->promptVersions = PromptVersion::orderBy('version', 'desc')->get();
    }

    protected function loadOriginalData()
    {
        $this->message = Message::with(['diagnostics', 'evaluation', 'conversation.promptVersion'])->findOrFail($this->messageId);
        
        if ($this->message->role !== 'assistant') {
            abort(404, 'Only assistant messages can be replayed.');
        }

        $this->userMessage = Message::where('conversation_id', $this->message->conversation_id)
            ->where('role', 'user')
            ->orderBy('id', 'desc')
            ->first();

        $sourceIds = $this->message->source_chunk_ids ?? [];
        $this->retrievedChunks = KnowledgeChunk::whereIn('id', $sourceIds)->get();
    }

    public function runFixedPromptReplay(
        RetrievalService $retrievalService,
        MockProvider $mockProvider,
        OpenRouterProvider $openRouterProvider,
        DiagnosticsEngine $diagnosticsEngine
    ) {
        $this->validate([
            'selectedPromptVersionId' => 'required|exists:prompt_versions,id',
        ]);

        if (!$this->userMessage) return;

        $newPromptVersion = PromptVersion::findOrFail($this->selectedPromptVersionId);

        // 1. Re-run context retrieval
        $sourceChunks = $retrievalService->retrieveTopChunks($this->userMessage->content);

        // 2. Select LLM provider matching original provider or fallback to mock
        $originalProvider = $this->message->diagnostics ? $this->message->diagnostics->provider_name : 'mock';
        $provider = ($originalProvider === 'openrouter') ? $openRouterProvider : $mockProvider;

        // 3. Generate new response using new prompt version
        $llmResponse = $provider->generate($newPromptVersion->system_prompt, $this->userMessage->content, $sourceChunks);

        // 4. Create new conversation to hold comparative diagnostics (safely matching relations)
        $newConv = Conversation::create([
            'prompt_version_id' => $newPromptVersion->id,
            'title' => 'Replay: ' . ($this->message->conversation->title ?? 'Untitled'),
        ]);

        // Save comparative query
        Message::create([
            'conversation_id' => $newConv->id,
            'role' => 'user',
            'content' => $this->userMessage->content,
        ]);

        // Save comparative assistant response
        $newAssistantMsg = Message::create([
            'conversation_id' => $newConv->id,
            'role' => 'assistant',
            'content' => $llmResponse->content,
            'source_chunk_ids' => $sourceChunks->pluck('id')->toArray(),
        ]);

        // 5. Run diagnostics
        $newDiagnostics = $diagnosticsEngine->run(
            $newAssistantMsg,
            $sourceChunks,
            $llmResponse->latencyMs,
            $llmResponse->providerName
        );

        // Save payload for comparative side-by-side rendering
        $this->fixedPromptResult = [
            'message' => $newAssistantMsg,
            'chunks' => $sourceChunks,
            'prompt_version' => $newPromptVersion,
            'diagnostics' => $newDiagnostics,
        ];

        session()->flash('replay_message', 'Replay with fixed prompt executed successfully.');
    }

    public function render()
    {
        return view('livewire.replay-view')->layout('components.layouts.app');
    }
}
