<div class="fade-in-up">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">AI Health Dashboard</h1>
            <p class="page-subtitle">Real-time prompt quality index and safety validation metrics across active sessions.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge badge-neutral">Auto-Refreshing</span>
        </div>
    </div>

    <!-- Health Score Breakdown Grid -->
    <div class="grid-3 mb-6">
        <!-- Overall Score Radial Gauge Card -->
        <div class="card flex-col items-center" style="justify-content: center; padding: var(--space-8); border-color: var(--accent-muted); text-align: center;">
            <span style="font-family: var(--font-display); font-weight: 600; color: var(--text-secondary); font-size: var(--text-base); margin-bottom: var(--space-4); display: block;">Overall Health Index</span>
            
            @php
                $score = $scoreData['score'];
                $scoreColor = $score >= 80 ? 'var(--success)' : ($score >= 50 ? 'var(--warning)' : 'var(--danger)');
                $scoreGlow = $score >= 80 ? 'rgba(16, 185, 129, 0.15)' : ($score >= 50 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(239, 68, 68, 0.15)');
                
                // SVG Circle calculations (r=52, circumference ~327)
                $circumference = 326.7;
                $dashoffset = $circumference - ($circumference * $score) / 100;
            @endphp
            
            <!-- SVG Gauge Component -->
            <div style="position: relative; width: 140px; height: 140px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-full); background: rgba(0,0,0,0.1); box-shadow: inset 0 4px 10px rgba(0,0,0,0.3);">
                <!-- Glow ring -->
                <div style="position: absolute; width: 120px; height: 120px; border-radius: var(--radius-full); box-shadow: 0 0 30px {{ $scoreGlow }}; z-index: 0; pointer-events: none;"></div>
                
                <svg width="128" height="128" viewBox="0 0 128 128" style="transform: rotate(-90deg); z-index: 1;">
                    <!-- Track Circle -->
                    <circle cx="64" cy="64" r="52" fill="transparent" stroke="var(--border-default)" stroke-width="6"></circle>
                    <!-- Indicator Circle -->
                    <circle cx="64" cy="64" r="52" fill="transparent" stroke="{{ $scoreColor }}" stroke-width="6" 
                            stroke-dasharray="{{ $circumference }}" 
                            stroke-dashoffset="{{ $dashoffset }}" 
                            stroke-linecap="round"
                            style="transition: stroke-dashoffset 800ms var(--ease-out);"></circle>
                </svg>
                
                <!-- Inner Score Text -->
                <div style="position: absolute; display: flex; flex-direction: column; align-items: center; z-index: 2;">
                    <span style="font-family: var(--font-display); font-weight: 800; font-size: var(--text-3xl); color: var(--text-primary);">{{ $score }}</span>
                    <span style="font-size: 10px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: -2px;">Score</span>
                </div>
            </div>
            
            <span class="badge" style="margin-top: var(--space-5); color: {{ $scoreColor }}; background-color: {{ $scoreGlow }}; border-color: rgba(255,255,255,0.05);">
                @if($score >= 80) Fully Healthy @elseif($score >= 50) Review Suggested @else Critical Action Required @endif
            </span>
        </div>

        <!-- Subscore Metrics Progress Sliders -->
        <div class="card" style="grid-column: span 2; display: flex; flex-direction: column; gap: var(--space-4);">
            <h2 style="font-family: var(--font-display); font-weight: 600; font-size: var(--text-md); color: var(--text-primary); border-bottom: 1px solid var(--border-default); padding-bottom: var(--space-2); margin-bottom: var(--space-2);">Compliance Breakdown</h2>
            
            @php
                $bd = $scoreData['breakdown'];
            @endphp
            
            <!-- Grounding -->
            <div style="display: flex; flex-direction: column; gap: var(--space-1);">
                <div class="flex-between" style="font-size: var(--text-sm);">
                    <span style="font-weight: 500; color: var(--text-secondary);">Groundedness (Context Overlap)</span>
                    <span style="font-weight: 600; color: var(--text-primary);">{{ $bd['grounding_pct'] }}%</span>
                </div>
                <div style="background-color: var(--bg-surface-3); height: 6px; border-radius: var(--radius-full); overflow: hidden; border: 1px solid var(--border-subtle);">
                    <div style="background-color: var(--success); width: {{ $bd['grounding_pct'] }}%; height: 100%; border-radius: var(--radius-full); transition: width 600ms var(--ease-out);"></div>
                </div>
            </div>

            <!-- Hallucination protection -->
            <div style="display: flex; flex-direction: column; gap: var(--space-1);">
                <div class="flex-between" style="font-size: var(--text-sm);">
                    <span style="font-weight: 500; color: var(--text-secondary);">Hallucination Protection Index</span>
                    <span style="font-weight: 600; color: var(--text-primary);">{{ 100 - $bd['hallucination_rate'] }}%</span>
                </div>
                <div style="background-color: var(--bg-surface-3); height: 6px; border-radius: var(--radius-full); overflow: hidden; border: 1px solid var(--border-subtle);">
                    <div style="background-color: {{ $bd['hallucination_rate'] > 20 ? 'var(--danger)' : 'var(--success)' }}; width: {{ 100 - $bd['hallucination_rate'] }}%; height: 100%; border-radius: var(--radius-full); transition: width 600ms var(--ease-out);"></div>
                </div>
            </div>

            <!-- Prompt Governance -->
            <div style="display: flex; flex-direction: column; gap: var(--space-1);">
                <div class="flex-between" style="font-size: var(--text-sm);">
                    <span style="font-weight: 500; color: var(--text-secondary);">Prompt Governance (Approved Templates)</span>
                    <span style="font-weight: 600; color: var(--text-primary);">{{ $bd['governance_pct'] }}%</span>
                </div>
                <div style="background-color: var(--bg-surface-3); height: 6px; border-radius: var(--radius-full); overflow: hidden; border: 1px solid var(--border-subtle);">
                    <div style="background-color: var(--accent); width: {{ $bd['governance_pct'] }}%; height: 100%; border-radius: var(--radius-full); transition: width 600ms var(--ease-out);"></div>
                </div>
            </div>

            <!-- Safety compliance -->
            <div style="display: flex; flex-direction: column; gap: var(--space-1);">
                <div class="flex-between" style="font-size: var(--text-sm);">
                    <span style="font-weight: 500; color: var(--text-secondary);">Safety Compliance (PII Scans)</span>
                    <span style="font-weight: 600; color: var(--text-primary);">{{ $bd['safety_score'] }}%</span>
                </div>
                <div style="background-color: var(--bg-surface-3); height: 6px; border-radius: var(--radius-full); overflow: hidden; border: 1px solid var(--border-subtle);">
                    <div style="background-color: {{ $bd['safety_score'] < 100 ? 'var(--danger)' : 'var(--success)' }}; width: {{ $bd['safety_score'] }}%; height: 100%; border-radius: var(--radius-full); transition: width 600ms var(--ease-out);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Metric strip -->
    <div class="grid-3 mb-6" style="grid-template-columns: repeat(3, 1fr);">
        <div class="card flex-col" style="padding: var(--space-4) var(--space-5); gap: var(--space-1);">
            <span class="label">Total Audited Runs</span>
            <span style="font-family: var(--font-display); font-size: var(--text-xl); font-weight: 700; color: white;">
                {{ $bd['total_runs'] }} <span style="font-size: var(--text-xs); color: var(--text-muted); font-weight: 400; text-transform: none;">queries logged</span>
            </span>
        </div>
        <div class="card flex-col" style="padding: var(--space-4) var(--space-5); gap: var(--space-1);">
            <span class="label">Average System Latency</span>
            <span style="font-family: var(--font-display); font-size: var(--text-xl); font-weight: 700; color: white;">
                {{ $bd['avg_latency_ms'] }} <span style="font-size: var(--text-xs); color: var(--text-muted); font-weight: 400; text-transform: none;">milliseconds</span>
            </span>
        </div>
        <div class="card flex-col" style="padding: var(--space-4) var(--space-5); gap: var(--space-1);">
            <span class="label">System Status</span>
            <span style="font-family: var(--font-display); font-size: var(--text-xl); font-weight: 700; color: var(--success);">
                ONLINE <span style="font-size: var(--text-xs); color: var(--text-muted); font-weight: 400; text-transform: none;">• 100% uptime</span>
            </span>
        </div>
    </div>

    <!-- Recent Diagnostics Table -->
    <div class="card-flush">
        <div class="flex-between" style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--border-default); background-color: rgba(255,255,255,0.01);">
            <h2 style="font-family: var(--font-display); font-weight: 700; font-size: var(--text-lg); color: white;">Recent Diagnostics Log</h2>
            <span class="badge badge-neutral" style="font-size: var(--text-xs);">Total logs: {{ $bd['total_runs'] }}</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Conversation Title</th>
                        <th>Groundedness</th>
                        <th>Diagnosis Status</th>
                        <th>Latency</th>
                        <th>Model Provider</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRuns as $run)
                        @php
                            $gScore = $run->groundedness_score;
                            $gBadge = $gScore >= 0.7 ? 'badge-success' : ($gScore >= 0.4 ? 'badge-warning' : 'badge-danger');
                            
                            $rc = $run->root_cause;
                            $rcLabel = str_replace('_', ' ', $rc);
                            $rcBadge = $rc === 'healthy' ? 'badge-success' : ($rc === 'knowledge_gap' ? 'badge-warning' : 'badge-danger');
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 600; color: white;">{{ $run->message->conversation->title ?? 'Console Execution Run' }}</span>
                                    <span style="font-size: var(--text-xs); color: var(--text-muted);">
                                        {{ $run->created_at->diffForHumans() }} • Prompt v{{ $run->message->conversation->promptVersion->version ?? '1' }} ({{ $run->message->conversation->promptVersion->name ?? 'Draft' }})
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $gBadge }}">{{ number_format($gScore, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $rcBadge }}" style="text-transform: capitalize;">{{ $rcLabel }}</span>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: var(--text-xs); color: var(--text-secondary);">
                                {{ $run->latency_ms }} ms
                            </td>
                            <td>
                                <span class="badge badge-neutral" style="border: none; background-color: rgba(255,255,255,0.03); color: var(--text-secondary);">
                                    {{ strtoupper($run->provider_name) }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="/replay/{{ $run->message_id }}" class="btn btn-secondary" style="padding: 0 var(--space-3); height: 28px; font-size: var(--text-xs); font-weight: 500;">
                                    View Replay
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: var(--space-12); text-align: center; color: var(--text-muted);">
                                No diagnostics entries found. Go to the Test Console to log your first query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
