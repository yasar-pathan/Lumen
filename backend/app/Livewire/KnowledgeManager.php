<?php

namespace App\Livewire;

use App\Models\KnowledgeChunk;
use Livewire\Component;

class KnowledgeManager extends Component
{
    public $title = '';
    public $content = '';
    public $tagsString = '';
    public $editingId = null;
    public $showForm = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];
    }

    public function mount()
    {
        // Handle pre-fill from Knowledge Gap list
        $prefillTitle = request()->query('prefill_title');
        if ($prefillTitle) {
            $this->title = ucwords($prefillTitle);
            $this->tagsString = strtolower($prefillTitle);
            $this->showForm = true;
        }
    }

    public function toggleForm()
    {
        $this->resetForm();
        $this->showForm = !$this->showForm;
    }

    public function resetForm()
    {
        $this->title = '';
        $this->content = '';
        $this->tagsString = '';
        $this->editingId = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        // Parse tags from comma-separated string
        $tags = [];
        if (!empty($this->tagsString)) {
            $tags = array_map('trim', explode(',', $this->tagsString));
            $tags = array_filter($tags); // remove empty elements
        }

        if ($this->editingId) {
            $chunk = KnowledgeChunk::findOrFail($this->editingId);
            $chunk->update([
                'title' => $this->title,
                'content' => $this->content,
                'tags' => $tags,
            ]);
            session()->flash('message', 'Knowledge chunk updated successfully.');
        } else {
            KnowledgeChunk::create([
                'title' => $this->title,
                'content' => $this->content,
                'tags' => $tags,
            ]);
            session()->flash('message', 'Knowledge chunk created successfully.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function edit(int $id)
    {
        $chunk = KnowledgeChunk::findOrFail($id);
        $this->editingId = $chunk->id;
        $this->title = $chunk->title;
        $this->content = $chunk->content;
        $this->tagsString = is_array($chunk->tags) ? implode(', ', $chunk->tags) : '';
        $this->showForm = true;
    }

    public function delete(int $id)
    {
        $chunk = KnowledgeChunk::findOrFail($id);
        $chunk->delete();
        session()->flash('message', 'Knowledge chunk deleted successfully.');
    }

    public function render()
    {
        $chunks = KnowledgeChunk::orderBy('id', 'desc')->get();

        return view('livewire.knowledge-manager', [
            'chunks' => $chunks
        ])->layout('components.layouts.app');
    }
}
