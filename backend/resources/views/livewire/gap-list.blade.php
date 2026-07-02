<div class="fade-in-up">
    <!-- Page Header -->
    <div class="page-header mb-8">
        <div>
            <h1 class="page-title">Knowledge Gaps</h1>
            <p class="page-subtitle">Missing search terms queried by users that found no matching reference context.</p>
        </div>
    </div>

    <!-- Ranked Knowledge Gaps Table -->
    <div class="card-flush">
        <div class="flex-between" style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--border-default); background-color: rgba(255,255,255,0.01);">
            <h2 style="font-family: var(--font-display); font-weight: 700; font-size: var(--text-lg); color: white;">Ranked Gaps</h2>
            <span class="badge badge-neutral" style="font-size: var(--text-xs);">Topic Analysis</span>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Rank</th>
                        <th>Missing Keyword</th>
                        <th>Frequency</th>
                        <th>Context Reference</th>
                        <th style="text-align: right;">Remediation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gaps as $index => $gap)
                        <tr>
                            <td style="font-weight: 700; color: var(--accent); font-size: var(--text-md); font-family: var(--font-display);">
                                #{{ $index + 1 }}
                            </td>
                            <td>
                                <span style="font-weight: 600; color: white; font-size: var(--text-base); text-transform: capitalize;">
                                    {{ $gap['term'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-warning" style="font-weight: 600;">
                                    Queried {{ $gap['count'] }} {{ Str::plural('time', $gap['count']) }}
                                </span>
                            </td>
                            <td>
                                @if($gap['example_message_id'])
                                    <a href="/replay/{{ $gap['example_message_id'] }}" style="color: var(--info); text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                        View Case
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 12px; height: 12px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                @else
                                    <span style="color: var(--text-faint);">—</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="/knowledge?prefill_title={{ urlencode($gap['term']) }}" class="btn btn-secondary" style="border-color: rgba(99, 102, 241, 0.3); color: white; font-weight: 600; padding: 0 var(--space-4); height: 28px; font-size: var(--text-xs); background-color: rgba(99, 102, 241, 0.05); display: inline-flex; align-items: center; gap: 4px;">
                                    Create Article
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 10px; height: 10px; transition: transform var(--duration-fast);" class="arrow-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: var(--space-12); text-align: center; color: var(--text-muted);">
                                No missing keywords detected. All user queries map successfully to contextual reference articles!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @style
            .btn-secondary:hover .arrow-icon { transform: translateX(2px); }
        @endstyle
    </div>
</div>
