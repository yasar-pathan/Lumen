<div class="fade-in-up">
    <!-- Page Header -->
    <div class="page-header mb-8">
        <div>
            <h1 class="page-title">Response Replay & Debug</h1>
            <p class="page-subtitle">Timeline diagnostics audit log and comparative run workspace for session ID: {{ $message->conversation_id }}</p>
        </div>
        <a href="/" class="btn btn-secondary" style="height: 38px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px; height: 14px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Dashboard
        </a>
    </div>

    <!-- Replay with Fixed Prompt Selector Card -->
    <div class="card mb-6" style="border-color: var(--border-accent);">
        <h2 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); margin-bottom: 2px;">Replay with Fixed Prompt</h2>
        <p style="font-size: var(--text-xs); color: var(--text-secondary); margin-bottom: var(--space-4);">Select an alternative prompt version to re-run the exact same query through retrieval and generation to verify a fix.</p>
        
        <form wire:submit.prevent="runFixedPromptReplay" style="display: flex; gap: var(--space-4); align-items: flex-end; flex-wrap: wrap;">
            <div class="input-group" style="margin-bottom: 0; min-width: 320px; flex-grow: 1;">
                <label for="replay-prompt-version">Target Prompt Template</label>
                <select id="replay-prompt-version" wire:model.defer="selectedPromptVersionId" class="input-control" style="background-color: var(--bg-surface-1); height: 38px;">
                    <option value="">Select a prompt version...</option>
                    @foreach($promptVersions as $pv)
                        <option value="{{ $pv->id }}" {{ $pv->id === $message->conversation->prompt_version_id ? 'disabled' : '' }}>
                            {{ $pv->name }} (v{{ $pv->version }}) {{ $pv->id === $message->conversation->prompt_version_id ? '[CURRENTLY USED]' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="height: 38px;" wire:loading.attr="disabled">
                <span wire:loading.remove style="display: inline-flex; align-items: center; gap: var(--space-2);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L17.5 12M21 7.5H7.5" />
                    </svg>
                    Compare Side-by-Side
                </span>
                <span wire:loading style="display: inline-flex; align-items: center; gap: var(--space-2);">
                    <div class="spinner" style="width: 16px; height: 16px; border-width: 2px;"></div>
                    Comparing...
                </span>
            </button>
        </form>
        @if($errors->has('selectedPromptVersionId'))
            <span style="color: var(--danger); font-size: var(--text-xs); display: block; margin-top: var(--space-2);">
                {{ $errors->first('selectedPromptVersionId') }}
            </span>
        @endif
    </div>

    @if($fixedPromptResult)
        <!-- SIDE BY SIDE COMPARISON WORKSPACE -->
        <div class="grid-2" style="animation: fadeInUp var(--duration-normal) var(--ease-out) forwards;">
            
            <!-- LEFT PANE: ORIGINAL OUTCOME -->
            <div style="display: flex; flex-direction: column; gap: var(--space-6);">
                <div style="border-bottom: 2px solid var(--danger); padding-bottom: var(--space-2); display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h2 style="font-family: var(--font-display); font-weight: 700; font-size: var(--text-lg); color: var(--danger);">Original Outcome</h2>
                        <span style="font-size: var(--text-xs); color: var(--text-muted);">Template: {{ $message->conversation->promptVersion->name }} (v{{ $message->conversation->promptVersion->version }})</span>
                    </div>
                    <span class="badge badge-danger">Draft Run</span>
                </div>

                <!-- Query -->
                <div class="card" style="background-color: rgba(255,255,255,0.01);">
                    <span class="label">User Query</span>
                    <p style="font-weight: 600; color: white; margin-top: var(--space-2); font-size: var(--text-sm);">"{{ $userMessage->content ?? 'None' }}"</p>
                </div>

                <!-- Context Chunks -->
                <div class="card">
                    <span class="label" style="margin-bottom: var(--space-2); display: block;">Context Retrieved</span>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2);">
                        @forelse($retrievedChunks as $chunk)
                            <div style="font-size: var(--text-xs); padding: var(--space-2) var(--space-3); background-color: var(--bg-surface-1); border: 1px solid var(--border-default); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: 600; color: white;">{{ $chunk->title }}</span>
                                <span style="color: var(--info); font-weight: 500;">Score: {{ number_format(($chunk->relevance_score ?? 1.0) * 100, 0) }}%</span>
                            </div>
                        @empty
                            <span style="font-size: var(--text-sm); color: var(--text-muted); text-align: center; display: block; padding: var(--space-3); border: 1px dashed var(--border-default); border-radius: var(--radius-md);">No chunks matched the query context.</span>
                        @endforelse
                    </div>
                </div>

                <!-- Output response text -->
                <div class="card" style="background-color: rgba(239,68,68,0.02); border-color: rgba(239, 68, 68, 0.15);">
                    <div class="flex-between mb-3">
                        <span class="label">Completion Content</span>
                        @if($message->diagnostics)
                            <span class="badge {{ $message->diagnostics->groundedness_score >= 0.7 ? 'badge-success' : ($message->diagnostics->groundedness_score >= 0.4 ? 'badge-warning' : 'badge-danger') }}">
                                Groundedness: {{ number_format($message->diagnostics->groundedness_score, 2) }}
                            </span>
                        @endif
                    </div>
                    <p style="font-size: var(--text-sm); line-height: 1.6; color: #f1f5f9; white-space: pre-wrap;">{{ $message->content }}</p>
                </div>

                <!-- Diagnostics details -->
                @if($message->diagnostics)
                    <div class="card">
                        <span class="label" style="margin-bottom: var(--space-3); display: block;">Diagnostics</span>
                        <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                            <div class="flex-between" style="border-bottom: 1px solid var(--border-subtle); padding-bottom: var(--space-2);">
                                <span style="font-size: var(--text-sm); color: var(--text-secondary);">Diagnosis Class</span>
                                <span class="badge {{ $message->diagnostics->root_cause === 'healthy' ? 'badge-success' : 'badge-danger' }}" style="text-transform: capitalize;">
                                    {{ str_replace('_', ' ', $message->diagnostics->root_cause) }}
                                </span>
                            </div>
                            @if($message->diagnostics->suggested_fix)
                                <div style="font-size: var(--text-xs); color: var(--text-secondary); line-height: 1.5; background-color: var(--danger-muted); border: 1px solid rgba(239,68,68,0.1); padding: var(--space-3); border-radius: var(--radius-md);">
                                    <strong>Audit Fix:</strong> {{ $message->diagnostics->suggested_fix }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- RIGHT PANE: FIXED OUTCOME -->
            <div style="display: flex; flex-direction: column; gap: var(--space-6);">
                <div style="border-bottom: 2px solid var(--success); padding-bottom: var(--space-2); display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h2 style="font-family: var(--font-display); font-weight: 700; font-size: var(--text-lg); color: var(--success);">Fixed Outcome</h2>
                        <span style="font-size: var(--text-xs); color: var(--text-muted);">Template: {{ $fixedPromptResult['prompt_version']->name }} (v{{ $fixedPromptResult['prompt_version']->version }})</span>
                    </div>
                    <span class="badge badge-success">Recalculated</span>
                </div>

                <!-- Query -->
                <div class="card" style="background-color: rgba(255,255,255,0.01);">
                    <span class="label">User Query</span>
                    <p style="font-weight: 600; color: white; margin-top: var(--space-2); font-size: var(--text-sm);">"{{ $userMessage->content ?? 'None' }}"</p>
                </div>

                <!-- Context Chunks -->
                <div class="card">
                    <span class="label" style="margin-bottom: var(--space-2); display: block;">Context Retrieved (Re-run)</span>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2);">
                        @forelse($fixedPromptResult['chunks'] as $chunk)
                            <div style="font-size: var(--text-xs); padding: var(--space-2) var(--space-3); background-color: var(--bg-surface-1); border: 1px solid var(--border-default); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: 600; color: white;">{{ $chunk->title }}</span>
                                <span style="color: var(--info); font-weight: 500;">Score: {{ number_format(($chunk->relevance_score ?? 1.0) * 100, 0) }}%</span>
                            </div>
                        @empty
                            <span style="font-size: var(--text-sm); color: var(--text-muted); text-align: center; display: block; padding: var(--space-3); border: 1px dashed var(--border-default); border-radius: var(--radius-md);">No chunks matched the query context.</span>
                        @endforelse
                    </div>
                </div>

                <!-- Output response text -->
                <div class="card" style="background-color: rgba(16,185,129,0.02); border-color: rgba(16, 185, 129, 0.15);">
                    <div class="flex-between mb-3">
                        <span class="label">Completion Content</span>
                        <span class="badge {{ $fixedPromptResult['diagnostics']->groundedness_score >= 0.7 ? 'badge-success' : ($fixedPromptResult['diagnostics']->groundedness_score >= 0.4 ? 'badge-warning' : 'badge-danger') }}">
                            Groundedness: {{ number_format($fixedPromptResult['diagnostics']->groundedness_score, 2) }}
                        </span>
                    </div>
                    <p style="font-size: var(--text-sm); line-height: 1.6; color: #f1f5f9; white-space: pre-wrap;">{{ $fixedPromptResult['message']->content }}</p>
                </div>

                <!-- Diagnostics details -->
                <div class="card">
                    <span class="label" style="margin-bottom: var(--space-3); display: block;">Diagnostics</span>
                    <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                        <div class="flex-between" style="border-bottom: 1px solid var(--border-subtle); padding-bottom: var(--space-2);">
                            <span style="font-size: var(--text-sm); color: var(--text-secondary);">Diagnosis Class</span>
                            <span class="badge {{ $fixedPromptResult['diagnostics']->root_cause === 'healthy' ? 'badge-success' : 'badge-danger' }}" style="text-transform: capitalize;">
                                {{ str_replace('_', ' ', $fixedPromptResult['diagnostics']->root_cause) }}
                            </span>
                        </div>
                        @if($fixedPromptResult['diagnostics']->suggested_fix)
                            <div style="font-size: var(--text-xs); color: var(--text-secondary); line-height: 1.5; background-color: var(--accent-muted); border: 1px solid var(--border-accent); padding: var(--space-3); border-radius: var(--radius-md);">
                                <strong>Audit Fix:</strong> {{ $fixedPromptResult['diagnostics']->suggested_fix }}
                            </div>
                        @else
                            <div style="font-size: var(--text-xs); color: var(--success); font-weight: 600; background-color: var(--success-muted); border: 1px solid rgba(16,185,129,0.1); padding: var(--space-3); border-radius: var(--radius-md); text-align: center;">
                                Response is fully healthy and matching context validation criteria.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- STANDARD CHRONOLOGICAL TIMELINE VIEW -->
        <div style="display: flex; flex-direction: column; gap: var(--space-6); max-width: 800px; margin: 0 auto; padding-left: var(--space-8); border-left: 2px solid var(--border-default); position: relative; padding-top: var(--space-4);">
            
            <!-- Timeline Item: User Query -->
            <div style="position: relative;">
                <div style="position: absolute; left: calc(-1 * var(--space-8) - 7px); top: 5px; width: 12px; height: 12px; border-radius: var(--radius-full); background-color: var(--accent); box-shadow: 0 0 10px var(--accent-glow);"></div>
                <h3 class="label" style="margin-bottom: var(--space-2);">Step 1: User Query Input</h3>
                <div class="card" style="padding: var(--space-4) var(--space-5); font-weight: 500; font-size: var(--text-sm);">
                    "{{ $userMessage->content ?? 'None' }}"
                </div>
            </div>

            <!-- Timeline Item: Context Retrieval -->
            <div style="position: relative;">
                <div style="position: absolute; left: calc(-1 * var(--space-8) - 7px); top: 5px; width: 12px; height: 12px; border-radius: var(--radius-full); background-color: var(--info); box-shadow: 0 0 10px var(--info-muted);"></div>
                <h3 class="label" style="margin-bottom: var(--space-2);">Step 2: Context Retrieval Overlaps</h3>
                
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    @forelse($retrievedChunks as $chunk)
                        <div class="card" style="padding: var(--space-4);">
                            <div class="flex-between mb-2">
                                <span style="font-weight: 600; color: white; font-size: var(--text-sm);">{{ $chunk->title }}</span>
                                <span class="badge badge-info" style="font-size: 10px;">Relevance: {{ number_format(($chunk->relevance_score ?? 1.0) * 100, 0) }}%</span>
                            </div>
                            <p style="font-size: var(--text-xs); color: var(--text-secondary); line-height: 1.5;">{{ $chunk->content }}</p>
                        </div>
                    @empty
                        <div class="card" style="padding: var(--space-6); text-align: center; color: var(--text-muted); font-size: var(--text-sm); border-style: dashed; border-width: 1px; background: transparent;">
                            No reference knowledge article matched this user query.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Timeline Item: Prompt Config -->
            <div style="position: relative;">
                <div style="position: absolute; left: calc(-1 * var(--space-8) - 7px); top: 5px; width: 12px; height: 12px; border-radius: var(--radius-full); background-color: var(--text-faint);"></div>
                <h3 class="label" style="margin-bottom: var(--space-2);">Step 3: Prompt Template Assembly</h3>
                <div class="card">
                    <div class="flex-between mb-3">
                        <span style="font-weight: 600; color: white; font-size: var(--text-sm);">{{ $message->conversation->promptVersion->name ?? 'Default Prompt Setup' }}</span>
                        <span class="badge {{ ($message->conversation->promptVersion->status ?? '') === 'approved' ? 'badge-success' : 'badge-warning' }}" style="font-size: 10px;">
                            v{{ $message->conversation->promptVersion->version ?? '1' }} • {{ $message->conversation->promptVersion->status ?? 'Draft' }}
                        </span>
                    </div>
                    <pre style="background: var(--bg-surface-1); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: var(--space-3); font-size: var(--text-xs); color: var(--text-secondary); font-family: var(--font-mono); white-space: pre-wrap; line-height: 1.5; max-height: 200px; overflow-y: auto;">{{ $message->conversation->promptVersion->system_prompt ?? 'No system instructions found.' }}</pre>
                </div>
            </div>

            <!-- Timeline Item: Generated Completion Output -->
            <div style="position: relative;">
                <div style="position: absolute; left: calc(-1 * var(--space-8) - 7px); top: 5px; width: 12px; height: 12px; border-radius: var(--radius-full); background-color: var(--accent); box-shadow: 0 0 10px var(--accent-glow);"></div>
                <h3 class="label" style="margin-bottom: var(--space-2);">Step 4: Generated Completion Output</h3>
                <div class="card" style="border-color: rgba(255,255,255,0.08);">
                    <p style="font-size: var(--text-sm); line-height: 1.6; color: #f1f5f9; white-space: pre-wrap;">{{ $message->content }}</p>
                </div>
            </div>

            <!-- Timeline Item: Diagnostics Engine Analysis -->
            <div style="position: relative;">
                @php
                    $isHealthy = ($message->diagnostics->root_cause ?? 'healthy') === 'healthy';
                    $diagColor = $isHealthy ? 'var(--success)' : 'var(--danger)';
                    $diagGlow = $isHealthy ? 'var(--success-muted)' : 'var(--danger-muted)';
                @endphp
                <div style="position: absolute; left: calc(-1 * var(--space-8) - 7px); top: 5px; width: 12px; height: 12px; border-radius: var(--radius-full); background-color: {{ $diagColor }}; box-shadow: 0 0 10px {{ $diagGlow }};"></div>
                <h3 class="label" style="margin-bottom: var(--space-2);">Step 5: Diagnostics Analysis</h3>
                
                @if($message->diagnostics)
                    <div class="card" style="border-left: 4px solid {{ $diagColor }};">
                        <div class="grid-2" style="gap: var(--space-4); margin-bottom: var(--space-4);">
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span class="label" style="font-size: 10px;">Classification</span>
                                <span style="font-weight: 600; color: white; font-size: var(--text-sm); text-transform: capitalize;">{{ str_replace('_', ' ', $message->diagnostics->root_cause) }}</span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span class="label" style="font-size: 10px;">Groundedness</span>
                                <span style="font-weight: 600; color: white; font-size: var(--text-sm);">{{ number_format($message->diagnostics->groundedness_score, 2) }}</span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span class="label" style="font-size: 10px;">Average Relevance</span>
                                <span style="font-weight: 600; color: white; font-size: var(--text-sm);">{{ number_format($message->diagnostics->retrieval_relevance_avg, 2) }}</span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span class="label" style="font-size: 10px;">Latency & Provider</span>
                                <span style="font-weight: 600; color: white; font-size: var(--text-sm);">{{ $message->diagnostics->latency_ms }} ms ({{ strtoupper($message->diagnostics->provider_name) }})</span>
                            </div>
                        </div>

                        @if($message->diagnostics->suggested_fix)
                            <div style="background-color: var(--accent-muted); border: 1px solid var(--border-accent); padding: var(--space-4); border-radius: var(--radius-md); display: flex; gap: var(--space-3); align-items: start;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px; color: var(--accent); margin-top: 1px; flex-shrink: 0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 3.546 5.974 5.974 0 0 1-2.133-1A3.75 3.75 0 0 0 12 18Z" />
                                </svg>
                                <div>
                                    <h5 style="font-weight: 700; color: #a5b4fc; font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Diagnostics Fix Action</h5>
                                    <p style="font-size: var(--text-sm); color: var(--text-secondary); line-height: 1.5;">{{ $message->diagnostics->suggested_fix }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="card" style="padding: var(--space-4); text-align: center; color: var(--text-muted); font-size: var(--text-sm);">
                        No diagnostics logs saved for this message run.
                    </div>
                @endif
            </div>

            <!-- Timeline Item: Human Auditor Evaluation Review -->
            <div style="position: relative;">
                <div style="position: absolute; left: calc(-1 * var(--space-8) - 7px); top: 5px; width: 12px; height: 12px; border-radius: var(--radius-full); background-color: var(--warning); box-shadow: 0 0 10px var(--warning-muted);"></div>
                <h3 class="label" style="margin-bottom: var(--space-2);">Step 6: Human Auditor Validation</h3>
                
                @if($message->evaluation)
                    <div class="card" style="border-left: 4px solid var(--warning);">
                        <div class="flex-between mb-3">
                            <span style="font-weight: 600; color: white; font-size: var(--text-sm);">Reviewed by: {{ $message->evaluation->reviewer_name ?: 'Auditor User' }}</span>
                            <span class="badge {{ $message->evaluation->flag === 'good' ? 'badge-success' : 'badge-danger' }}" style="font-size: 10px;">
                                {{ $message->evaluation->flag }} ({{ $message->evaluation->rating }} / 5 Stars)
                            </span>
                        </div>
                        @if($message->evaluation->notes)
                            <p style="font-size: var(--text-xs); color: var(--text-secondary); line-height: 1.5; background-color: var(--bg-surface-1); border: 1px solid var(--border-default); padding: var(--space-3); border-radius: var(--radius-md);">
                                <strong>Observations:</strong> {{ $message->evaluation->notes }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="card" style="padding: var(--space-6); text-align: center; color: var(--text-muted); font-size: var(--text-sm); border-style: dashed; border-width: 1px; background: transparent;">
                        This response transaction has not been evaluated by an auditor. Run a query in the Test Console to log review ratings.
                    </div>
                @endif
            </div>

        </div>
    @endif
</div>
