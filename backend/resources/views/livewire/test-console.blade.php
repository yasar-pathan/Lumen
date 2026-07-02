<div class="fade-in-up">
    <!-- Page Header -->
    <div class="page-header mb-8">
        <div>
            <h1 class="page-title">AI Test Console</h1>
            <p class="page-subtitle">Experiment with test queries, evaluate prompt iterations, and audit system grounding in real-time.</p>
        </div>
    </div>

    <!-- IDE-Style Split-Pane Workspace -->
    <div style="display: grid; grid-template-columns: 340px 1fr; gap: var(--space-6); align-items: start;">
        
        <!-- Left Sidebar: Configuration Cockpit -->
        <div class="card" style="position: sticky; top: var(--space-6); display: flex; flex-direction: column; gap: var(--space-4);">
            <h2 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); border-bottom: 1px solid var(--border-default); padding-bottom: var(--space-2); margin-bottom: var(--space-2);">Execution Cockpit</h2>
            
            <form wire:submit.prevent="run" style="display: flex; flex-direction: column; gap: var(--space-4);">
                <!-- User Query Field -->
                <div class="input-group">
                    <label for="query-input">User Query</label>
                    <textarea id="query-input" wire:model.defer="query" class="textarea-control" placeholder="Ask a support question e.g. What's the refund window for domestic orders?"></textarea>
                    @error('query') <span style="color: var(--danger); font-size: var(--text-xs); margin-top: var(--space-1);">{{ $message }}</span> @enderror
                </div>

                <!-- Prompt Version Select -->
                <div class="input-group">
                    <label for="prompt-version">System Prompt Template</label>
                    <select id="prompt-version" wire:model.defer="selectedPromptVersionId" class="input-control" style="background-color: var(--bg-surface-1); height: 38px;">
                        <option value="">Select a prompt version...</option>
                        @foreach($promptVersions as $pv)
                            <option value="{{ $pv->id }}">
                                {{ $pv->name }} (v{{ $pv->version }}) [{{ strtoupper($pv->status) }}]
                            </option>
                        @endforeach
                    </select>
                    @error('selectedPromptVersionId') <span style="color: var(--danger); font-size: var(--text-xs); margin-top: var(--space-1);">{{ $message }}</span> @enderror
                </div>

                <!-- LLM Provider Switch Toggle -->
                <div style="background-color: rgba(0,0,0,0.15); border: 1px solid var(--border-default); border-radius: var(--radius-lg); padding: var(--space-3) var(--space-4); display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <span style="font-size: var(--text-sm); font-weight: 600; color: var(--text-primary);">OpenRouter Live</span>
                        <span style="font-size: 10px; color: var(--text-muted);">API charges apply</span>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="provider-toggle" wire:model="useLiveModel">
                        <span class="slider"></span>
                    </label>
                </div>

                <!-- Run Command Button -->
                <button type="submit" class="btn btn-primary w-full" style="height: 40px; font-size: var(--text-sm);" wire:loading.attr="disabled">
                    <!-- Normal text -->
                    <span wire:loading.remove style="display: inline-flex; align-items: center; gap: var(--space-2);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347c-.75.412-1.667-.13-1.667-.986V5.653Z" />
                        </svg>
                        Run Execution
                    </span>
                    <!-- Loading text -->
                    <span wire:loading style="display: inline-flex; align-items: center; gap: var(--space-2);">
                        <div class="spinner" style="width: 16px; height: 16px; border-width: 2px;"></div>
                        Analyzing...
                    </span>
                </button>
            </form>
        </div>

        <!-- Right Panel: Diagnostics Workspace -->
        <div style="display: flex; flex-direction: column; gap: var(--space-6);">
            
            <!-- Loading Shell Cover Overlay -->
            <div wire:loading.delay.longer class="card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; padding: var(--space-12); text-align: center; gap: var(--space-4);">
                <div class="spinner" style="width: 48px; height: 48px; border-width: 3px;"></div>
                <div>
                    <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md);">Executing Prompt Architecture</h3>
                    <p style="color: var(--text-secondary); font-size: var(--text-xs); margin-top: var(--space-1); max-width: 400px;">Tokenizing queries, retrieving document overlaps, querying selected model interfaces, and executing diagnostics checks...</p>
                </div>
            </div>

            <!-- Idle State Placeholder -->
            @if(!$lastResponse)
                <div wire:loading.remove class="card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; padding: var(--space-12) var(--space-6); text-align: center; border-style: dashed; border-width: 1px; background: transparent; height: 380px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: var(--space-4);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 3.546 5.974 5.974 0 0 1-2.133-1A3.75 3.75 0 0 0 12 18Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.669 8.623a8.25 8.25 0 0 1 12.924-4.724 8.25 8.25 0 0 1-12.924 4.724ZM12 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                    </svg>
                    <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary);">Awaiting Execution</h3>
                    <p style="color: var(--text-secondary); font-size: var(--text-sm); max-width: 320px; margin-top: var(--space-2);">Fill out parameters in the left cockpit and execute the run to generate responses and diagnose prompt bugs.</p>
                </div>
            @endif

            <!-- Active Output Display -->
            @if($lastResponse)
                <div wire:loading.remove style="display: flex; flex-direction: column; gap: var(--space-6);">
                    
                    <!-- Safety Alert Banner -->
                    @if($lastDiagnostics && $lastDiagnostics->safety_flag)
                        <div style="background-color: var(--danger-muted); border: 1px solid var(--danger); padding: var(--space-4) var(--space-5); border-radius: var(--radius-lg); display: flex; align-items: center; gap: var(--space-4); animation: pulseAlert 2s infinite alternate;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px; color: var(--danger); flex-shrink: 0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                            </svg>
                            <div>
                                <h4 style="font-weight: 700; color: white; font-size: var(--text-sm);">Safety Filter Triggered</h4>
                                <p style="font-size: var(--text-xs); color: var(--text-secondary); margin-top: 2px;">Restricted identifiers, unsafe syntax patterns, or possible PII-like credit strings were detected in the generated completion text.</p>
                            </div>
                        </div>
                        @style
                            @keyframes pulseAlert { from { border-color: var(--danger); } to { border-color: rgba(239, 68, 68, 0.4); } }
                        @endstyle
                    @endif

                    <!-- Assistant Response Card -->
                    <div class="card" style="border-top: 2px solid var(--accent);">
                        <div class="flex-between mb-4">
                            <span class="label">Assistant Response Completion</span>
                            
                            <!-- Groundedness Quality Pill -->
                            @if($lastDiagnostics)
                                @php
                                    $gs = $lastDiagnostics->groundedness_score;
                                    $badgeStyle = $gs >= 0.7 ? 'badge-success' : ($gs >= 0.4 ? 'badge-warning' : 'badge-danger');
                                    $badgeText = $gs >= 0.7 ? 'Grounded' : ($gs >= 0.4 ? 'Uncertain' : 'Hallucination Risk');
                                @endphp
                                <span class="badge {{ $badgeStyle }}" style="font-size: var(--text-xs); padding: var(--space-1) var(--space-3);">
                                    {{ $badgeText }}: {{ number_format($gs, 2) }}
                                </span>
                            @endif
                        </div>
                        
                        <div style="background-color: var(--bg-surface-1); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: var(--space-4); font-size: var(--text-base); line-height: 1.6; color: #f1f5f9; white-space: pre-wrap; font-family: var(--font-body);">{{ $lastResponse }}</div>
                    </div>

                    <!-- Diagnostics & Classification Summary -->
                    @if($lastDiagnostics)
                        <div class="card" style="border-left: 4px solid {{ $lastDiagnostics->root_cause === 'healthy' ? 'var(--success)' : 'var(--danger)' }};">
                            <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); margin-bottom: var(--space-4);">Diagnostics Analysis</h3>
                            
                            <div class="grid-3" style="gap: var(--space-4); margin-bottom: var(--space-4); background-color: rgba(0,0,0,0.15); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span class="label" style="font-size: 10px;">Classification</span>
                                    <span style="font-weight: 600; color: white; font-size: var(--text-sm); text-transform: capitalize;">{{ str_replace('_', ' ', $lastDiagnostics->root_cause) }}</span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span class="label" style="font-size: 10px;">Latency</span>
                                    <span style="font-weight: 600; color: white; font-size: var(--text-sm); font-family: var(--font-mono);">{{ $lastDiagnostics->latency_ms }} ms</span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span class="label" style="font-size: 10px;">Model Engine</span>
                                    <span style="font-weight: 600; color: white; font-size: var(--text-sm); text-transform: uppercase;">{{ $lastDiagnostics->provider_name }}</span>
                                </div>
                            </div>

                            @if($lastDiagnostics->suggested_fix)
                                <div style="background-color: var(--accent-muted); border: 1px solid var(--border-accent); padding: var(--space-4); border-radius: var(--radius-md); display: flex; gap: var(--space-3); align-items: start;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px; color: var(--accent); margin-top: 1px; flex-shrink: 0;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 3.546 5.974 5.974 0 0 1-2.133-1A3.75 3.75 0 0 0 12 18Z" />
                                    </svg>
                                    <div>
                                        <h5 style="font-weight: 700; color: #a5b4fc; font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Suggested Remediation Action</h5>
                                        <p style="font-size: var(--text-sm); color: var(--text-secondary); line-height: 1.5;">{{ $lastDiagnostics->suggested_fix }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Context Overview Accodion -->
                    <div class="card">
                        <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); margin-bottom: var(--space-4);">Retrieved Context Chunks</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                            @forelse($retrievedChunks as $chunk)
                                <div style="background-color: rgba(255,255,255,0.01); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: var(--space-3) var(--space-4);">
                                    <div class="flex-between mb-2">
                                        <span style="font-weight: 600; color: white; font-size: var(--text-sm);">{{ $chunk['title'] }}</span>
                                        <span class="badge badge-info" style="font-size: 10px;">Relevance: {{ number_format($chunk['relevance_score'] * 100, 0) }}%</span>
                                    </div>
                                    <p style="font-size: var(--text-xs); color: var(--text-secondary); line-height: 1.5;">{{ $chunk['content'] }}</p>
                                </div>
                            @empty
                                <div style="text-align: center; padding: var(--space-6); color: var(--text-muted); font-size: var(--text-sm); border: 1px dashed var(--border-default); border-radius: var(--radius-md);">
                                    No context chunks matched this query configuration.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Human Auditor Audit Form -->
                    <div class="card" style="border: 1px solid rgba(245, 158, 11, 0.2);">
                        <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); margin-bottom: 2px;">Audit Review Validation</h3>
                        <p style="font-size: var(--text-xs); color: var(--text-secondary); margin-bottom: var(--space-4);">Submit compliance ratings and logs feedback to optimize LLM performance.</p>
                        
                        @if (session()->has('review_message'))
                            <div style="background-color: var(--success-muted); border: 1px solid var(--success); color: white; padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-4); font-size: var(--text-sm);">
                                {{ session('review_message') }}
                            </div>
                        @endif

                        @if($isReviewed)
                            <div style="background-color: var(--bg-surface-1); border: 1px solid var(--border-default); padding: var(--space-4); border-radius: var(--radius-md); text-align: center; color: var(--text-secondary); font-weight: 500;">
                                Audit successfully saved and recorded. Quality loops updated.
                            </div>
                        @else
                            <form wire:submit.prevent="submitReview" style="display: flex; flex-direction: column; gap: var(--space-3);">
                                <div class="grid-2" style="gap: var(--space-4);">
                                    <div class="input-group" style="margin-bottom: 0;">
                                        <label for="review-rating">Quality Rating</label>
                                        <select id="review-rating" wire:model.defer="rating" class="input-control" style="background-color: var(--bg-surface-1); height: 38px;">
                                            <option value="5">⭐⭐⭐⭐⭐ (5 - Perfect)</option>
                                            <option value="4">⭐⭐⭐⭐ (4 - Good)</option>
                                            <option value="3">⭐⭐⭐ (3 - Acceptable)</option>
                                            <option value="2">⭐⭐ (2 - Minor Errors)</option>
                                            <option value="1">⭐ (1 - Factual Failure)</option>
                                        </select>
                                    </div>
                                    <div class="input-group" style="margin-bottom: 0;">
                                        <label for="review-flag">Compliance Flag</label>
                                        <select id="review-flag" wire:model.defer="flag" class="input-control" style="background-color: var(--bg-surface-1); height: 38px;">
                                            <option value="good">Good (No issues)</option>
                                            <option value="incorrect">Incorrect (Factual error)</option>
                                            <option value="hallucination">Hallucination (Fabricated details)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="input-group" style="margin-bottom: 0;">
                                    <label for="review-notes">Auditor Observation Notes</label>
                                    <input type="text" id="review-notes" wire:model.defer="notes" class="input-control" placeholder="Describe the factual discrepancies or validation issue...">
                                </div>

                                <div class="flex-between" style="margin-top: var(--space-2);">
                                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                                        <label for="reviewer-name" style="font-size: var(--text-xs); color: var(--text-muted); text-transform: uppercase;">By:</label>
                                        <input type="text" id="reviewer-name" wire:model.defer="reviewerName" class="input-control" style="padding: 0 var(--space-2); height: 28px; width: 140px; font-size: var(--text-xs);">
                                    </div>
                                    <button type="submit" class="btn btn-secondary" style="border-color: rgba(245, 158, 11, 0.4); color: white; background-color: rgba(245, 158, 11, 0.05); height: 32px; padding: 0 var(--space-4);">
                                        Submit Audit Signal
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
