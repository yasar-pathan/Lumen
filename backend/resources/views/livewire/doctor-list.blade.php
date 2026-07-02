<div class="fade-in-up">
    <!-- Page Header -->
    <div class="page-header mb-8">
        <div>
            <h1 class="page-title">Conversation Doctor</h1>
            <p class="page-subtitle">Automatic tracking of prompt hallucinations, safety policy warnings, and knowledge gaps.</p>
        </div>
    </div>

    <!-- Active Doctor Cases Stack -->
    <div style="display: flex; flex-direction: column; gap: var(--space-5);">
        @forelse($cases as $case)
            @php
                $userMsg = \App\Models\Message::where('conversation_id', $case->message->conversation_id)
                    ->where('role', 'user')
                    ->first();
                $queryText = $userMsg ? $userMsg->content : 'No user query was logged for this session.';
            @endphp
            
            <div class="card" style="border-left: 4px solid var(--danger); display: flex; justify-content: space-between; align-items: center; gap: var(--space-6);">
                <div style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3);">
                    <div style="display: flex; align-items: center; gap: var(--space-3);">
                        @php
                            $rc = $case->root_cause;
                            $badgeStyle = $rc === 'knowledge_gap' ? 'badge-warning' : 'badge-danger';
                        @endphp
                        <span class="badge {{ $badgeStyle }}" style="text-transform: capitalize;">
                            {{ str_replace('_', ' ', $rc) }}
                        </span>
                        
                        <span style="font-size: var(--text-xs); color: var(--text-muted);">
                            Detected {{ $case->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <div>
                        <span class="label">Query Trigger</span>
                        <p style="font-size: var(--text-md); font-weight: 500; color: white; margin-top: 2px;">
                            "{{ $queryText }}"
                        </p>
                    </div>

                    @if($case->suggested_fix)
                        <div style="background-color: var(--danger-muted); border: 1px solid rgba(239,68,68,0.1); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); display: flex; gap: var(--space-3); align-items: start; max-width: 700px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px; color: var(--danger); margin-top: 2px; flex-shrink: 0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <div>
                                <h5 style="font-weight: 700; color: #fca5a5; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1px;">Diagnosis Remediation Plan</h5>
                                <p style="font-size: var(--text-xs); color: var(--text-secondary); line-height: 1.5;">{{ $case->suggested_fix }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div style="flex-shrink: 0;">
                    <a href="/replay/{{ $case->message_id }}" class="btn btn-primary" style="height: 36px;">
                        Open Case Replay
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="card" style="padding: var(--space-12); text-align: center; color: var(--text-muted); border-style: dashed; border-width: 1px; background: transparent;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: var(--success); margin: 0 auto var(--space-4) auto; display: block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
                <h3 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); margin-bottom: 2px;">System Fully Healthy</h3>
                <p style="font-size: var(--text-xs); color: var(--text-secondary); max-width: 320px; margin: 0 auto;">No prompt failures, hallucinations, or context anomalies are logged in the active system dataset.</p>
            </div>
        @endforelse
    </div>
</div>
