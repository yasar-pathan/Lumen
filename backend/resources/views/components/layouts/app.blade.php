<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lumen — AI Command Center' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @livewireStyles

    <!-- Custom Premium CSS Design System -->
    <style>
        :root {
            /* --- Surfaces --- */
            --bg-root:        #09090b;
            --bg-surface-1:   #111113;        /* sidebar, top nav */
            --bg-surface-2:   #18181b;        /* cards, panels */
            --bg-surface-3:   #1f1f23;        /* elevated: modals, dropdowns */
            --bg-hover:       rgba(255, 255, 255, 0.04);
            --bg-active:      rgba(255, 255, 255, 0.06);
            
            /* --- Borders --- */
            --border-default: rgba(255, 255, 255, 0.06);
            --border-subtle:  rgba(255, 255, 255, 0.03);
            --border-accent:  rgba(99, 102, 241, 0.25);
            
            /* --- Text --- */
            --text-primary:   #fafafa;
            --text-secondary: #a1a1aa;        /* zinc-400 */
            --text-muted:     #71717a;        /* zinc-500 */
            --text-faint:     #52525b;        /* zinc-600 */
            
            /* --- Accent (Indigo) --- */
            --accent:         #6366f1;
            --accent-hover:   #5558e6;
            --accent-muted:   rgba(99, 102, 241, 0.12);
            --accent-glow:    rgba(99, 102, 241, 0.20);
            
            /* --- Semantic Status Colors --- */
            --success:        #10b981;
            --success-muted:  rgba(16, 185, 129, 0.12);
            --warning:        #f59e0b;
            --warning-muted:  rgba(245, 158, 11, 0.12);
            --danger:         #ef4444;
            --danger-muted:   rgba(239, 68, 68, 0.12);
            --info:           #06b6d4;
            --info-muted:     rgba(6, 182, 212, 0.12);
            
            /* --- Typography --- */
            --font-display:   'Outfit', system-ui, sans-serif;
            --font-body:      'Inter', system-ui, sans-serif;
            --font-mono:      'JetBrains Mono', 'Fira Code', monospace;
            
            /* --- Space --- */
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            --space-10: 2.5rem;
            --space-12: 3rem;
            
            /* --- Radii --- */
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-full: 9999px;
            
            /* --- Shadows --- */
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.4);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.4);
            --shadow-glow: 0 0 20px var(--accent-glow);
            
            /* --- Transitions --- */
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --duration-fast: 150ms;
            --duration-normal: 250ms;
            --duration-slow: 400ms;
            
            /* --- Layout --- */
            --sidebar-width: 260px;
            --sidebar-collapsed: 64px;
            --content-max-width: 1200px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-root);
            color: var(--text-primary);
            font-family: var(--font-body);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* --- Sidebar styling --- */
        aside {
            width: var(--sidebar-width);
            background-color: var(--bg-surface-1);
            border-right: 1px solid var(--border-default);
            display: flex;
            flex-direction: column;
            padding: var(--space-6) var(--space-4);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
            transition: width var(--duration-normal) var(--ease-out);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-8);
            padding-left: var(--space-2);
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--accent), var(--info));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-weight: 800;
            font-size: var(--text-md);
            color: white;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
        }

        .logo-text {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: var(--text-lg);
            background: linear-gradient(to right, #ffffff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
            list-style: none;
            flex-grow: 1;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-3);
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: var(--text-sm);
            transition: all var(--duration-fast) var(--ease-out);
            border: 1px solid transparent;
        }

        .nav-item a:hover {
            color: var(--text-primary);
            background-color: var(--bg-hover);
        }

        .nav-item.active a {
            color: #818cf8;
            background-color: var(--accent-muted);
            box-shadow: inset 2px 0 0 var(--accent);
            font-weight: 600;
        }

        .nav-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .sidebar-bottom {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
            padding: var(--space-3) var(--space-2) 0 var(--space-2);
            border-top: 1px solid var(--border-subtle);
        }

        /* --- Main layout --- */
        main {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: var(--space-10);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .main-container {
            width: 100%;
            max-width: var(--content-max-width);
            display: flex;
            flex-direction: column;
            gap: var(--space-6);
        }

        /* --- Global Component Classes --- */
        .card {
            background-color: var(--bg-surface-2);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            box-shadow: var(--shadow-sm);
            transition: all var(--duration-normal) var(--ease-out);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            border-color: var(--border-accent);
            box-shadow: var(--shadow-md), var(--shadow-glow);
        }

        .card-flush {
            background-color: var(--bg-surface-2);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        /* --- Typography --- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-4);
        }

        .page-title {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: var(--text-3xl);
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-family: var(--font-body);
            font-size: var(--text-sm);
            color: var(--text-secondary);
            margin-top: var(--space-1);
        }

        /* --- Buttons --- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            height: 36px;
            padding: 0 var(--space-4);
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: var(--text-sm);
            cursor: pointer;
            transition: all var(--duration-fast) var(--ease-out);
            border: 1px solid transparent;
            font-family: var(--font-body);
            text-decoration: none;
            user-select: none;
        }

        .btn-primary {
            background-color: var(--accent);
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: scale(0.97);
        }

        .btn-secondary {
            background-color: var(--bg-hover);
            color: var(--text-primary);
            border-color: var(--border-default);
        }

        .btn-secondary:hover {
            background-color: var(--bg-active);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .btn-secondary:active {
            transform: scale(0.97);
        }

        .btn-ghost {
            background-color: transparent;
            color: var(--text-muted);
        }

        .btn-ghost:hover {
            background-color: var(--bg-hover);
            color: var(--text-primary);
        }

        .btn-danger {
            background-color: var(--danger-muted);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background-color: var(--danger);
            color: white;
            transform: translateY(-1px);
        }

        /* --- Forms --- */
        .input-group {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
            margin-bottom: var(--space-4);
        }

        .input-group label {
            font-size: var(--text-xs);
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-control {
            background-color: var(--bg-surface-1);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            height: 36px;
            padding: 0 var(--space-3);
            color: white;
            font-family: var(--font-body);
            font-size: var(--text-sm);
            outline: none;
            transition: all var(--duration-fast) var(--ease-out);
            width: 100%;
        }

        .input-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-muted);
        }

        .input-control::placeholder {
            color: var(--text-faint);
        }

        .textarea-control {
            background-color: var(--bg-surface-1);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            padding: var(--space-3);
            color: white;
            font-family: var(--font-body);
            font-size: var(--text-sm);
            outline: none;
            transition: all var(--duration-fast) var(--ease-out);
            width: 100%;
            min-height: 100px;
            resize: vertical;
        }

        .textarea-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-muted);
        }

        .textarea-control::placeholder {
            color: var(--text-faint);
        }

        /* --- Badges --- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px var(--space-2);
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-success { background-color: var(--success-muted); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-warning { background-color: var(--warning-muted); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-danger { background-color: var(--danger-muted); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge-info { background-color: var(--info-muted); color: var(--info); border: 1px solid rgba(6, 182, 212, 0.2); }
        .badge-neutral { background-color: var(--bg-hover); color: var(--text-muted); border: 1px solid var(--border-default); }

        /* Badge status pulse indicator */
        .badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: var(--radius-full);
            background-color: currentColor;
        }

        .badge-success::before { animation: pulseGreen 1.5s ease-in-out infinite alternate; }
        .badge-danger::before { animation: pulseRed 1.5s ease-in-out infinite alternate; }
        .badge-warning::before { animation: pulseOrange 1.5s ease-in-out infinite alternate; }

        @keyframes pulseGreen { from { opacity: 1; } to { opacity: 0.35; } }
        @keyframes pulseRed { from { opacity: 1; } to { opacity: 0.35; } }
        @keyframes pulseOrange { from { opacity: 1; } to { opacity: 0.35; } }

        /* --- Tables --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--text-sm);
        }

        .data-table th {
            font-size: var(--text-xs);
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
            padding: var(--space-3) var(--space-4);
            border-bottom: 1px solid var(--border-default);
            text-align: left;
        }

        .data-table td {
            padding: var(--space-4);
            border-bottom: 1px solid var(--border-subtle);
            vertical-align: middle;
        }

        .data-table tr:hover {
            background-color: var(--bg-hover);
            transition: background-color var(--duration-fast) var(--ease-out);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* --- Loader Shimmers --- */
        .skeleton {
            background: linear-gradient(90deg, var(--bg-surface-2) 25%, var(--bg-surface-3) 50%, var(--bg-surface-2) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: var(--radius-md);
        }

        @keyframes shimmer {
            from { background-position: 200% 0; }
            to { background-position: -200% 0; }
        }

        /* --- Grid utils --- */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-6);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-6);
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Custom toggle switch styling */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            cursor: pointer;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--bg-surface-3);
            border: 1px solid var(--border-default);
            transition: var(--duration-fast);
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: var(--text-muted);
            transition: var(--duration-fast);
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--accent);
            border-color: var(--accent);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        input:checked + .slider:before {
            transform: translateX(20px);
            background-color: white;
        }

        /* Responsive Layout Behavior */
        @media (max-width: 1024px) {
            aside {
                width: var(--sidebar-collapsed);
                padding: var(--space-6) var(--space-2);
                align-items: center;
            }
            .logo-text, .nav-text, .badge-neutral {
                display: none;
            }
            .logo-container {
                justify-content: center;
                padding-left: 0;
            }
            main {
                margin-left: var(--sidebar-collapsed);
                width: calc(100% - var(--sidebar-collapsed));
                padding: var(--space-6);
            }
        }
        
        @media (max-width: 768px) {
            .grid-3, .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        /* Entrance Animation */
        .fade-in-up {
            animation: fadeInUp var(--duration-slow) var(--ease-out) forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside>
        <div class="logo-container">
            <div class="logo-icon">L</div>
            <span class="logo-text">Lumen</span>
        </div>
        
        <ul class="nav-links">
            <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                <a href="/">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('console') ? 'active' : '' }}">
                <a href="/console">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                    </svg>
                    <span class="nav-text">Test Console</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('knowledge*') ? 'active' : '' }}">
                <a href="/knowledge">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span class="nav-text">Knowledge Base</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('doctor*') ? 'active' : '' }}">
                <a href="/doctor">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                    <span class="nav-text">Conversation Doctor</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('knowledge-gaps*') ? 'active' : '' }}">
                <a href="/knowledge-gaps">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span class="nav-text">Knowledge Gaps</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-bottom">
            <span class="badge badge-info" style="align-self: flex-start;">Development</span>
            <span style="font-size: var(--text-xs); color: var(--text-faint);">Lumen Core v1.0.0</span>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main>
        <div class="main-container">
            {{ $slot }}
        </div>
    </main>

    @livewireScripts
</body>
</html>
