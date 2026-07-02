<div class="fade-in-up">
    <!-- Page Header -->
    <div class="page-header mb-8">
        <div>
            <h1 class="page-title">Knowledge Base</h1>
            <p class="page-subtitle">Manage reference context documents and corporate rules parsed by the AI engine.</p>
        </div>
        <button wire:click="toggleForm" class="btn btn-primary" style="height: 38px;">
            @if($showForm)
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
                Cancel
            @else
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Document
            @endif
        </button>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('message'))
        <div style="background-color: var(--success-muted); border: 1px solid var(--success); color: white; padding: var(--space-4) var(--space-5); border-radius: var(--radius-lg); margin-bottom: var(--space-6); font-weight: 500; font-size: var(--text-sm);">
            {{ session('message') }}
        </div>
    @endif

    <!-- Form Section (Create / Edit) -->
    @if($showForm)
        <div class="card mb-6" style="border-color: var(--accent); animation: slideDown 250ms var(--ease-out) forwards;">
            <h2 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); margin-bottom: var(--space-4); border-bottom: 1px solid var(--border-default); padding-bottom: var(--space-2);">
                {{ $editingId ? 'Edit Reference Document' : 'Create Knowledge Chunk' }}
            </h2>
            
            <form wire:submit.prevent="save" style="display: flex; flex-direction: column; gap: var(--space-4);">
                <div class="input-group">
                    <label for="title">Document Title</label>
                    <input type="text" id="title" wire:model.defer="title" class="input-control" placeholder="e.g. Domestic Shipping Policy">
                    @error('title') <span style="color: var(--danger); font-size: var(--text-xs); margin-top: 2px;">{{ $message }}</span> @enderror
                </div>

                <div class="input-group">
                    <label for="content">Document Content (Reference Text)</label>
                    <textarea id="content" wire:model.defer="content" class="textarea-control" style="min-height: 120px;" placeholder="Paste standard operating reference content here..."></textarea>
                    @error('content') <span style="color: var(--danger); font-size: var(--text-xs); margin-top: 2px;">{{ $message }}</span> @enderror
                </div>

                <div class="input-group">
                    <label for="tags">Keywords / Tags (Comma-separated list)</label>
                    <input type="text" id="tags" wire:model.defer="tagsString" class="input-control" placeholder="e.g. shipping, domestic, timeline">
                </div>

                <div class="flex gap-4" style="margin-top: var(--space-2); gap: var(--space-3);">
                    <button type="submit" class="btn btn-primary" style="height: 38px;">
                        {{ $editingId ? 'Save Changes' : 'Create Document' }}
                    </button>
                    <button type="button" wire:click="toggleForm" class="btn btn-secondary" style="height: 38px;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
        @style
            @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        @endstyle
    @endif

    <!-- Knowledge Chunks List Stack -->
    <div style="display: flex; flex-direction: column; gap: var(--space-4);">
        @forelse($chunks as $chunk)
            <div class="card" style="display: flex; justify-content: space-between; gap: var(--space-6);">
                <div style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3);">
                    
                    <!-- Title and tags row -->
                    <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap;">
                        <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: white;">
                            {{ $chunk->title }}
                        </h3>
                        
                        <!-- Tags capsule chips -->
                        @if(is_array($chunk->tags))
                            @foreach($chunk->tags as $tag)
                                <span class="badge badge-neutral" style="font-size: 10px; padding: 2px 6px; border: none; background-color: rgba(255,255,255,0.03); color: var(--text-secondary); text-transform: lowercase;">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                    
                    <!-- Main Content Text -->
                    <p style="color: var(--text-secondary); line-height: 1.6; font-size: var(--text-sm); white-space: pre-line;">
                        {{ $chunk->content }}
                    </p>
                    
                    <!-- Creation timestamp -->
                    <span style="font-size: var(--text-xs); color: var(--text-faint);">
                        Created {{ $chunk->created_at->diffForHumans() }}
                    </span>
                </div>
                
                <!-- Action trigger buttons -->
                <div style="display: flex; flex-direction: column; gap: var(--space-2); flex-shrink: 0; align-self: flex-start;">
                    <button wire:click="edit({{ $chunk->id }})" class="btn btn-secondary" style="padding: 0 var(--space-3); height: 28px; font-size: var(--text-xs); font-weight: 500;">
                        Edit
                    </button>
                    <button onclick="confirm('Are you sure you want to delete this document?') || event.stopImmediatePropagation()" wire:click="delete({{ $chunk->id }})" class="btn btn-secondary" style="padding: 0 var(--space-3); height: 28px; font-size: var(--text-xs); font-weight: 500; color: var(--danger); border-color: rgba(239, 68, 68, 0.15);">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="card" style="padding: var(--space-12); text-align: center; color: var(--text-muted); border-style: dashed; border-width: 1px; background: transparent;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 40px; height: 40px; margin: 0 auto var(--space-4) auto; color: var(--text-muted); display: block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); margin-bottom: 2px;">No Reference Documents</h3>
                <p style="font-size: var(--text-xs); color: var(--text-secondary); max-width: 280px; margin: 0 auto var(--space-4) auto;">Create your first contextual reference article using the Add Document button above.</p>
            </div>
        @endforelse
    </div>
</div>
