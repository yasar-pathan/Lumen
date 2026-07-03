import { useState, useEffect } from 'react';
import {
  Home,
  Video,
  CheckSquare,
  Search,
  Bell,
  Sun,
  Moon,
  Activity,
  Plus,
  Play,
  FileText,
  AlertTriangle,
  RefreshCw,
  Trash2,
  Cpu,
  Terminal,
  BarChart2
} from 'lucide-react';
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const API_URL = '/api';

interface HealthMetrics {
  score: number;
  breakdown: {
    total_runs: number;
    grounding_pct: number;
    avg_latency_ms: number;
    hallucination_rate: number;
    safety_score: number;
  };
}

export default function App() {
  const [theme, setTheme] = useState('light');
  const [activeTab, setActiveTab] = useState('dashboard');

  // Data
  const [metrics, setMetrics] = useState<HealthMetrics | null>(null);
  const [incidents, setIncidents] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);

  // Global Interactive States
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedReplayId, setSelectedReplayId] = useState<number | null>(null);
  const [cardStages, setCardStages] = useState<Record<number, string>>({});

  useEffect(() => {
    document.documentElement.className = theme;
  }, [theme]);

  useEffect(() => {
    fetchDashboard();
  }, []);

  const fetchDashboard = async () => {
    setLoading(true);
    try {
      const [resH, resD] = await Promise.all([
        fetch(`${API_URL}/health-score`),
        fetch(`${API_URL}/doctor/cases`)
      ]);
      const dataH = await resH.json();
      const dataD = await resD.json();

      if (dataH?.data) setMetrics(dataH.data);
      else setMetrics({ score: 0, breakdown: { total_runs: 0, grounding_pct: 0, avg_latency_ms: 0, hallucination_rate: 0, safety_score: 0 } });

      if (dataD?.data) setIncidents(dataD.data);
    } catch (e) {
      setMetrics({ score: 0, breakdown: { total_runs: 0, grounding_pct: 0, avg_latency_ms: 0, hallucination_rate: 0, safety_score: 0 } });
    }
    setLoading(false);
  };

  const handleDeleteTrace = (id: number) => {
    setIncidents(prev => prev.filter(inc => inc.id !== id));
  };

  return (
    <div className="app-layout">
      {/* SIDEBAR */}
      <aside className="sidebar">
        <div className="flex-row" style={{ padding: '24px 20px', gap: '12px' }}>
          <div style={{ width: 28, height: 28, borderRadius: 6, backgroundColor: 'var(--brand-primary)', color: 'white', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Cpu size={18} />
          </div>
          <span style={{ fontWeight: 800, fontSize: 18, color: 'var(--text-main)', letterSpacing: '-0.5px' }}>LumenAI</span>
        </div>

        <nav style={{ display: 'flex', flexDirection: 'column', gap: 4, marginTop: 12 }}>
          <NavItem active={activeTab === 'dashboard'} onClick={() => setActiveTab('dashboard')} icon={<Home size={18} />} label="Dashboard" />
          <NavItem active={activeTab === 'traces'} onClick={() => setActiveTab('traces')} icon={<Video size={18} />} label="Traces" />
          <NavItem active={activeTab === 'doctor'} onClick={() => setActiveTab('doctor')} icon={<CheckSquare size={18} />} label="Doctor Logs" />
          <NavItem active={activeTab === 'gaps'} onClick={() => setActiveTab('gaps')} icon={<AlertTriangle size={18} />} label="Knowledge Gaps" />
          <NavItem active={activeTab === 'analytics'} onClick={() => setActiveTab('analytics')} icon={<BarChart2 size={18} />} label="Analytics" />
          <NavItem active={activeTab === 'console'} onClick={() => setActiveTab('console')} icon={<Terminal size={18} />} label="Test Console" />
        </nav>
      </aside>

      {/* MAIN CONTENT */}
      <main className="main-content">
        {/* TOP HEADER */}
        <header className="top-header">
          <div style={{ position: 'relative' }}>
            <Search size={16} className="text-light" style={{ position: 'absolute', left: 12, top: 10 }} />
            <input
              type="text"
              className="search-input"
              placeholder="Search traces and logs..."
              value={searchQuery}
              onChange={e => setSearchQuery(e.target.value)}
            />
            <div style={{ position: 'absolute', right: 8, top: 8, border: '1px solid var(--border-light)', borderRadius: 4, padding: '2px 6px', fontSize: 10, color: 'var(--text-light)', fontWeight: 600, backgroundColor: 'var(--bg-app)' }}>
              ⌘K
            </div>
          </div>

          <div className="flex-row" style={{ gap: 20 }}>
            <Bell size={20} className="text-muted" style={{ cursor: 'pointer' }} />
            <div style={{ cursor: 'pointer' }} onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')}>
              {theme === 'light' ? <Moon size={20} className="text-muted" /> : <Sun size={20} className="text-muted" />}
            </div>
          </div>
        </header>

        {/* PAGE CONTENT */}
        <div className="page-wrapper">
          {activeTab === 'dashboard' && (
            <DashboardView
              metrics={metrics}
              incidents={incidents}
              loading={loading}
              searchQuery={searchQuery}
              onTabChange={setActiveTab}
              setSelectedReplayId={setSelectedReplayId}
              onRefresh={fetchDashboard}
            />
          )}
          {activeTab === 'traces' && (
            <TracesView
              incidents={incidents}
              loading={loading}
              searchQuery={searchQuery}
              onDelete={handleDeleteTrace}
              setSelectedReplayId={setSelectedReplayId}
              onRefresh={fetchDashboard}
            />
          )}
          {activeTab === 'doctor' && (
            <DoctorKanbanView
              incidents={incidents}
              loading={loading}
              searchQuery={searchQuery}
              cardStages={cardStages}
              setCardStages={setCardStages}
              onRefresh={fetchDashboard}
            />
          )}
          {activeTab === 'gaps' && <KnowledgeGapsView />}
          {activeTab === 'analytics' && <AnalyticsView metrics={metrics} loading={loading} onRefresh={fetchDashboard} />}
          {activeTab === 'console' && <TestConsoleView />}
        </div>
      </main>

      {/* DETAILED TIMELINE REPLAY MODAL */}
      {selectedReplayId !== null && (
        <ReplayModal
          messageId={selectedReplayId}
          onClose={() => setSelectedReplayId(null)}
        />
      )}
    </div>
  );
}

function NavItem({ active, onClick, icon, label }: any) {
  return (
    <a className={`nav-item ${active ? 'active' : ''}`} onClick={onClick}>
      {icon}
      <span>{label}</span>
    </a>
  );
}

// ==========================================================
// DASHBOARD
// ==========================================================
function DashboardView({ metrics, incidents, loading, searchQuery, onTabChange, setSelectedReplayId, onRefresh }: any) {
  if (loading || !metrics) return <LoadingSpinner />;

  // Filter traces
  const filtered = incidents.filter((inc: any) => {
    const titleText = (inc.conversation?.title || '').toLowerCase();
    const bodyText = (inc.message?.content || '').toLowerCase();
    const queryStr = searchQuery.toLowerCase();
    return titleText.includes(queryStr) || bodyText.includes(queryStr);
  });

  return (
    <div style={{ maxWidth: 1100, margin: '0 auto' }}>

      {/* Hero Header */}
      <div className="card" style={{ padding: 32, marginBottom: 32, backgroundColor: 'var(--bg-card)', border: 'none', boxShadow: 'none' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <div>
            <div className="flex-row" style={{ color: 'var(--brand-primary)', fontWeight: 600, fontSize: 13, marginBottom: 12 }}>
              <Activity size={16} />
              <span>Active Governance Mode</span>
              <span className="text-muted" style={{ marginLeft: 8, fontSize: 11, fontWeight: 500 }}>System Live</span>
            </div>
            <h1 style={{ fontSize: 32, fontWeight: 800, marginBottom: 8, letterSpacing: '-0.5px' }}>Good evening, Operator!</h1>
            <p className="text-muted" style={{ fontSize: 15 }}>Your AI observability command center — traces, anomalies, and logs in one place.</p>

            <div style={{ marginTop: 24, display: 'inline-flex', alignItems: 'center', backgroundColor: 'var(--brand-light)', color: 'var(--brand-primary)', padding: '8px 16px', borderRadius: 8, fontSize: 13, fontWeight: 500 }}>
              <Play size={14} style={{ marginRight: 8 }} />
              You processed {metrics.breakdown.total_runs} traces with a composite health score of {metrics.score}%.
            </div>

            <div className="flex-row" style={{ marginTop: 24, gap: 12 }}>
              <button className="btn btn-primary" style={{ padding: '0 24px', height: 40, borderRadius: 8 }} onClick={() => onTabChange('console')}>
                <Play size={16} /> Start test run
              </button>
              <button className="btn btn-secondary" style={{ padding: '0 20px', height: 40, borderRadius: 8, border: 'none', fontWeight: 600 }} onClick={() => onTabChange('analytics')}>
                View analytics →
              </button>
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16, width: 400 }}>
            <MetricCard label="TOTAL TRACES" value={metrics.breakdown.total_runs} sub="30-day volume" icon={<Video size={16} className="text-info" />} />
            <MetricCard label="HEALTH SCORE" value={`${metrics.score}%`} sub="Composite index" icon={<CheckSquare size={16} className="text-success" />} />
            <MetricCard label="HALLUCINATIONS" value={`${metrics.breakdown.hallucination_rate}%`} sub="Failure rate" icon={<AlertTriangle size={16} className="text-warning" />} />
            <MetricCard label="AVG LATENCY" value={`${metrics.breakdown.avg_latency_ms}ms`} sub="Response time" icon={<Activity size={16} className="text-warning" />} />
          </div>
        </div>
      </div>

      <div style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5, marginBottom: 12 }}>QUICK ACTIONS</div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 32 }}>
        <div style={{ cursor: 'pointer' }} onClick={() => onTabChange('console')}>
          <QuickAction color="qa-blue" icon={<Play size={18} className="text-info" />} title="Run Test Trace" sub="Execute a manual payload" />
        </div>
        <div style={{ cursor: 'pointer' }} onClick={() => onTabChange('doctor')}>
          <QuickAction color="qa-green" icon={<CheckSquare size={18} className="text-success" />} title="Doctor Logs" sub="Resolve active incidents" />
        </div>
        <div style={{ cursor: 'pointer' }} onClick={() => onTabChange('gaps')}>
          <QuickAction color="qa-purple" icon={<Plus size={18} className="text-primary" style={{ color: 'var(--brand-primary)' }} />} title="Add Knowledge" sub="Fill identified gaps" />
        </div>
        <div style={{ cursor: 'pointer' }} onClick={() => onTabChange('console')}>
          <QuickAction color="qa-orange" icon={<Terminal size={18} className="text-warning" />} title="Test Console" sub="Open debug environment" />
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 24 }}>
        <div>
          <div className="flex-between" style={{ marginBottom: 12 }}>
            <span style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5 }}>RECENT TRACES</span>
            <div className="flex-row" style={{ gap: 12 }}>
              <button className="btn btn-secondary" style={{ height: 28, fontSize: 12, padding: '0 12px' }} onClick={onRefresh}><RefreshCw size={14} /> Refresh</button>
              <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--brand-primary)', cursor: 'pointer' }} onClick={() => onTabChange('traces')}>View all →</span>
            </div>
          </div>
          <div className="card" style={{ padding: 0 }}>
            {filtered.slice(0, 4).map((inc: any, idx: number) => (
              <div key={idx} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 20px', borderBottom: idx < 3 ? '1px solid var(--border-light)' : 'none' }}>
                <div>
                  <div className="flex-row">
                    <span className={`pill ${inc.root_cause === 'healthy' ? 'pill-success' : 'pill-critical'}`} style={{ transform: 'scale(0.8)', transformOrigin: 'left' }}>
                      {inc.root_cause === 'healthy' ? 'Healthy' : 'Anomaly'}
                    </span>
                    <span style={{ fontWeight: 600, fontSize: 14 }}>
                      {inc.conversation?.title?.replace('Healthy Flow: ', '')?.replace('Broken Flow: ', '') || 'Trace Execution'}
                    </span>
                  </div>
                  <div className="text-muted" style={{ fontSize: 12, marginTop: 4, display: 'flex', alignItems: 'center', gap: 6 }}>
                    <Play size={12} /> Trace ID: {inc.message_id} • Score: {inc.groundedness_score}
                  </div>
                </div>
                <button className="btn btn-secondary" style={{ height: 32, padding: '0 12px' }} onClick={() => setSelectedReplayId(inc.message_id)}>View</button>
              </div>
            ))}
            {filtered.length === 0 && (
              <div style={{ padding: 32, textAlign: 'center', color: 'var(--text-light)' }}>No matching traces.</div>
            )}
          </div>
        </div>

        <div>
          <div className="flex-between" style={{ marginBottom: 12 }}>
            <span style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5 }}>PRODUCTIVITY SNAPSHOT</span>
            <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--brand-primary)', cursor: 'pointer' }} onClick={() => onTabChange('analytics')}>Details →</span>
          </div>
          <div className="card" style={{ padding: 24 }}>
            <div style={{ display: 'flex', gap: 12 }}>
              <div className="qa-blue" style={{ flex: 1, padding: 12, borderRadius: 12, textAlign: 'center' }}>
                <Video size={16} className="text-info" style={{ margin: '0 auto 8px' }} />
                <div style={{ fontWeight: 800, fontSize: 20 }}>{metrics.breakdown.total_runs}</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>Traces</div>
              </div>
              <div className="qa-green" style={{ flex: 1, padding: 12, borderRadius: 12, textAlign: 'center' }}>
                <CheckSquare size={16} className="text-success" style={{ margin: '0 auto 8px' }} />
                <div style={{ fontWeight: 800, fontSize: 20 }}>{metrics.score}%</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>Healthy</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function MetricCard({ label, value, sub, icon }: any) {
  return (
    <div className="card" style={{ padding: '20px 16px', display: 'flex', justifyContent: 'space-between', boxShadow: '0 1px 3px rgb(0 0 0 / 0.05)' }}>
      <div>
        <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5, marginBottom: 8 }}>{label}</div>
        <div style={{ fontSize: 24, fontWeight: 800, color: 'var(--text-main)', letterSpacing: '-0.5px' }}>{value}</div>
        <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 4 }}>{sub}</div>
      </div>
      <div>
        <div style={{ width: 28, height: 28, borderRadius: '50%', backgroundColor: 'var(--bg-app)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          {icon}
        </div>
      </div>
    </div>
  );
}

function QuickAction({ color, icon, title, sub }: any) {
  return (
    <div className={`quick-action-btn ${color}`} style={{ height: '100%' }}>
      <div className="qa-icon-wrapper">{icon}</div>
      <div style={{ fontWeight: 700, fontSize: 14, color: 'var(--text-main)' }}>{title}</div>
      <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>{sub}</div>
    </div>
  );
}

// ==========================================================
// TRACES (Flight Recorder Grid)
// ==========================================================
function TracesView({ incidents, loading, searchQuery, onDelete, setSelectedReplayId, onRefresh }: any) {
  const [filter, setFilter] = useState('all');

  if (loading) return <LoadingSpinner />;

  // Filter lists
  const filtered = incidents.filter((inc: any) => {
    // 1. Search Query
    const queryMatch = (inc.conversation?.title || '').toLowerCase().includes(searchQuery.toLowerCase()) ||
      (inc.message?.content || '').toLowerCase().includes(searchQuery.toLowerCase());

    if (!queryMatch) return false;

    // 2. Tab Filter
    if (filter === 'anomalies') return inc.root_cause !== 'healthy';
    if (filter === 'healthy') return inc.root_cause === 'healthy';

    return true;
  });

  return (
    <div style={{ maxWidth: 1200, margin: '0 auto' }}>
      <div className="flex-between" style={{ marginBottom: 24 }}>
        <div>
          <h1 style={{ fontSize: 24, fontWeight: 800, letterSpacing: '-0.5px' }}>Traces</h1>
          <p className="text-muted" style={{ marginTop: 4 }}>Manage observability logs, debug AI payloads, or browse history.</p>
        </div>
        <button className="btn btn-secondary" onClick={onRefresh}><RefreshCw size={16} /> Refresh</button>
      </div>

      <div className="tabs-row">
        <button className={`tab ${filter === 'all' ? 'active' : ''}`} onClick={() => setFilter('all')}><Play size={14} style={{ display: 'inline', marginRight: 6 }} />All Traces</button>
        <button className={`tab ${filter === 'anomalies' ? 'active' : ''}`} onClick={() => setFilter('anomalies')}>Anomalies</button>
        <button className={`tab ${filter === 'healthy' ? 'active' : ''}`} onClick={() => setFilter('healthy')}>Healthy</button>
      </div>

      <div className="trace-grid">
        {filtered.map((inc: any, i: number) => (
          <div key={i} className="trace-card">
            <div>
              <div className="flex-between" style={{ marginBottom: 12 }}>
                <span className={`pill ${inc.root_cause === 'healthy' ? 'pill-success' : 'pill-critical'}`}>
                  {inc.root_cause === 'healthy' ? 'Healthy' : inc.root_cause.replace('_', ' ')}
                </span>
                <span style={{ fontFamily: 'monospace', fontSize: 11, color: 'var(--text-light)' }}>#{inc.message_id}</span>
              </div>

              {/* User query as beautiful title */}
              <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 10, color: 'var(--text-main)', lineHeight: 1.4 }}>
                {inc.conversation?.title?.replace('Healthy Flow: ', '')?.replace('Broken Flow: ', '') || 'Trace Execution'}
              </h3>

              {/* Muted paragraph showing the AI Response beautifully clamped to 3 lines max */}
              <p className="text-muted" style={{
                fontSize: 13,
                lineHeight: 1.5,
                marginBottom: 16,
                display: '-webkit-box',
                WebkitLineClamp: 3,
                WebkitBoxOrient: 'vertical',
                overflow: 'hidden'
              } as any}>
                {inc.message?.content || 'No AI execution payload content.'}
              </p>

              <div className="flex-row" style={{ marginTop: 16 }}>
                <div className="avatar" style={{ width: 24, height: 24, fontSize: 10 }}>SY</div>
                <div style={{ fontSize: 12, fontWeight: 600 }}>System Execution <span className="text-muted" style={{ fontWeight: 400, marginLeft: 4 }}>API</span></div>
              </div>

              <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 12, display: 'flex', alignItems: 'center', gap: 6 }}>
                <Activity size={14} /> Latency: {inc.latency_ms}ms
              </div>
              {inc.root_cause !== 'healthy' && (
                <div style={{ fontSize: 12, color: 'var(--brand-primary)', marginTop: 8, display: 'flex', alignItems: 'center', gap: 6, fontWeight: 600 }}>
                  <FileText size={14} /> Diagnostics Available
                </div>
              )}
            </div>

            <div className="flex-between" style={{ marginTop: 24, paddingTop: 16, borderTop: '1px solid var(--border-light)' }}>
              <span className="text-muted" style={{ fontSize: 12 }}>Score: {inc.groundedness_score}</span>
              <div className="flex-row">
                <button className="btn btn-secondary" style={{ color: 'var(--critical)', borderColor: 'var(--critical-bg)', height: 32, padding: '0 12px' }} onClick={() => onDelete(inc.id)}><Trash2 size={14} /> Delete</button>
                <button className="btn btn-primary" style={{ height: 32, padding: '0 12px' }} onClick={() => setSelectedReplayId(inc.message_id)}><Play size={14} /> View Replay</button>
              </div>
            </div>
          </div>
        ))}
      </div>
      {filtered.length === 0 && (
        <div className="card" style={{ padding: 48, textAlign: 'center', color: 'var(--text-light)' }}>
          No traces matched your query or filter.
        </div>
      )}
    </div>
  );
}

// ==========================================================
// DOCTOR LOGS (Kanban Board)
// ==========================================================
function DoctorKanbanView({ incidents, loading, searchQuery, cardStages, setCardStages, onRefresh }: any) {
  if (loading) return <LoadingSpinner />;

  const anomalies = incidents.filter((i: any) => i.root_cause !== 'healthy');

  // Filter
  const filtered = anomalies.filter((inc: any) => {
    const termStr = (inc.suggested_fix || '').toLowerCase();
    const queryStr = searchQuery.toLowerCase();
    return termStr.includes(queryStr);
  });

  const handleStageMove = (id: number, nextStage: string) => {
    setCardStages((prev: any) => ({
      ...prev,
      [id]: nextStage
    }));
  };

  const getStage = (id: number) => cardStages[id] || 'to_review';

  const reviewCol = filtered.filter((inc: any) => getStage(inc.id) === 'to_review');
  const progressCol = filtered.filter((inc: any) => getStage(inc.id) === 'investigating');
  const fixedCol = filtered.filter((inc: any) => getStage(inc.id) === 'fixed');

  return (
    <div style={{ maxWidth: 1200, margin: '0 auto', height: '100%', display: 'flex', flexDirection: 'column' }}>
      <div className="flex-between" style={{ marginBottom: 24 }}>
        <div>
          <h1 style={{ fontSize: 24, fontWeight: 800, letterSpacing: '-0.5px' }}>Doctor Board</h1>
          <p className="text-muted" style={{ marginTop: 4 }}>Track AI anomalies, assign fixes, or sync items generated by diagnostics.</p>
        </div>
        <button className="btn btn-secondary" onClick={onRefresh}><RefreshCw size={16} /> Refresh</button>
      </div>

      <div className="kanban-board">
        {/* TO DO Column */}
        <div className="kanban-col">
          <div className="kanban-header">
            <span>TO REVIEW</span>
            <span style={{ background: 'var(--border-light)', padding: '2px 8px', borderRadius: 12, color: 'var(--text-main)', fontSize: 12 }}>{reviewCol.length}</span>
          </div>
          {reviewCol.map((inc: any, i: number) => (
            <div key={i} className="kanban-card">
              <div className="flex-between" style={{ marginBottom: 12 }}>
                <span className="pill pill-warning" style={{ backgroundColor: 'var(--bg-hover)', color: 'var(--warning)' }}>Critical</span>
                <span style={{ fontSize: 12, color: 'var(--brand-primary)', fontWeight: 700 }}>✨ AI</span>
              </div>
              <div style={{ fontSize: 14, fontWeight: 600, marginBottom: 8, lineHeight: 1.4 }}>
                {inc.suggested_fix || 'Investigate knowledge gap'}
              </div>
              <div className="flex-between" style={{ marginTop: 16 }}>
                <div style={{ fontSize: 11, color: 'var(--text-light)', fontFamily: 'monospace' }}>Trace #{inc.message_id}</div>
                <button
                  className="btn btn-secondary"
                  style={{ height: 26, fontSize: 11, padding: '0 8px' }}
                  onClick={() => handleStageMove(inc.id, 'investigating')}
                >
                  Investigate →
                </button>
              </div>
            </div>
          ))}
          {reviewCol.length === 0 && (
            <div style={{ padding: 24, textAlign: 'center', border: '1px dashed var(--border-light)', borderRadius: 8, color: 'var(--text-light)', fontSize: 12 }}>
              No review items
            </div>
          )}
        </div>

        {/* IN PROGRESS Column */}
        <div className="kanban-col">
          <div className="kanban-header">
            <span>INVESTIGATING</span>
            <span style={{ background: 'var(--border-light)', padding: '2px 8px', borderRadius: 12, color: 'var(--text-main)', fontSize: 12 }}>{progressCol.length}</span>
          </div>
          {progressCol.map((inc: any, i: number) => (
            <div key={i} className="kanban-card">
              <div className="flex-between" style={{ marginBottom: 12 }}>
                <span className="pill pill-warning" style={{ backgroundColor: 'var(--bg-hover)', color: 'var(--info)' }}>Active</span>
                <span style={{ fontSize: 12, color: 'var(--brand-primary)', fontWeight: 700 }}>✨ AI</span>
              </div>
              <div style={{ fontSize: 14, fontWeight: 600, marginBottom: 8, lineHeight: 1.4 }}>
                {inc.suggested_fix}
              </div>
              <div className="flex-between" style={{ marginTop: 16 }}>
                <div style={{ fontSize: 11, color: 'var(--text-light)', fontFamily: 'monospace' }}>Trace #{inc.message_id}</div>
                <button
                  className="btn btn-primary"
                  style={{ height: 26, fontSize: 11, padding: '0 8px' }}
                  onClick={() => handleStageMove(inc.id, 'fixed')}
                >
                  Resolve ✓
                </button>
              </div>
            </div>
          ))}
          {progressCol.length === 0 && (
            <div style={{ padding: 24, textAlign: 'center', border: '1px dashed var(--border-light)', borderRadius: 8, color: 'var(--text-light)', fontSize: 12 }}>
              Move issues here to begin investigating.
            </div>
          )}
        </div>

        {/* FIXED Column */}
        <div className="kanban-col">
          <div className="kanban-header">
            <span>FIXED</span>
            <span style={{ background: 'var(--border-light)', padding: '2px 8px', borderRadius: 12, color: 'var(--text-main)', fontSize: 12 }}>{fixedCol.length}</span>
          </div>
          {fixedCol.map((inc: any, i: number) => (
            <div key={i} className="kanban-card" style={{ opacity: 0.8 }}>
              <div className="flex-between" style={{ marginBottom: 12 }}>
                <span className="pill pill-success" style={{ backgroundColor: 'var(--success-bg)', color: 'var(--success)' }}>Resolved</span>
              </div>
              <div style={{ fontSize: 14, fontWeight: 600, marginBottom: 8, lineHeight: 1.4, textDecoration: 'line-through' }}>
                {inc.suggested_fix}
              </div>
              <div className="flex-between" style={{ marginTop: 16 }}>
                <div style={{ fontSize: 11, color: 'var(--text-light)', fontFamily: 'monospace' }}>Trace #{inc.message_id}</div>
                <span style={{ fontSize: 11, color: 'var(--success)', fontWeight: 600 }}>Closed</span>
              </div>
            </div>
          ))}
          {fixedCol.length === 0 && (
            <div style={{ padding: 32, textAlign: 'center', border: '2px dashed var(--border-light)', borderRadius: 'var(--radius-lg)', color: 'var(--text-light)', fontSize: 14, fontWeight: 500 }}>
              No resolved cards
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

// ==========================================================
// KNOWLEDGE GAPS
// ==========================================================
function KnowledgeGapsView() {
  const [gaps, setGaps] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [newTitle, setNewTitle] = useState('');
  const [newContent, setNewContent] = useState('');
  const [newTags, setNewTags] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    fetchGaps();
  }, []);

  const fetchGaps = async () => {
    setLoading(true);
    try {
      const res = await fetch(`${API_URL}/knowledge-gaps`);
      const json = await res.json();
      if (json?.data) setGaps(json.data);
    } catch (e) {
      console.error(e);
    }
    setLoading(false);
  };

  const handleAddKnowledge = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTitle.trim() || !newContent.trim()) return;
    setSubmitting(true);
    try {
      const tagsArray = newTags.split(',').map(t => t.trim()).filter(Boolean);
      const res = await fetch(`${API_URL}/knowledge`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: newTitle, content: newContent, tags: tagsArray })
      });
      if (res.ok) {
        alert('Knowledge Chunk added successfully! Resolving gaps...');
        setShowAddModal(false);
        setNewTitle('');
        setNewContent('');
        setNewTags('');
        fetchGaps(); // Reload gaps
      }
    } catch (e) {
      console.error(e);
    }
    setSubmitting(false);
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div style={{ maxWidth: 1000, margin: '0 auto' }}>
      <div className="flex-between" style={{ marginBottom: 24 }}>
        <div>
          <h1 style={{ fontSize: 24, fontWeight: 800, letterSpacing: '-0.5px' }}>Knowledge Gaps</h1>
          <p className="text-muted" style={{ marginTop: 4 }}>Observed term query mismatches that led to AI hallucinations or failures due to lack of ground truth.</p>
        </div>
        <div className="flex-row" style={{ gap: 12 }}>
          <button className="btn btn-secondary" onClick={fetchGaps}><RefreshCw size={16} /> Refresh</button>
          <button className="btn btn-primary" onClick={() => setShowAddModal(true)}><Plus size={16} /> Add Knowledge</button>
        </div>
      </div>

      <div className="card" style={{ padding: 0 }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left' }}>
          <thead>
            <tr style={{ borderBottom: '1px solid var(--border-light)', background: 'var(--bg-app)' }}>
              <th style={{ padding: '16px 20px', fontSize: 11, fontWeight: 700, color: 'var(--text-light)' }}>MISSING TERM</th>
              <th style={{ padding: '16px 20px', fontSize: 11, fontWeight: 700, color: 'var(--text-light)' }}>OCCURRENCE FREQUENCY</th>
              <th style={{ padding: '16px 20px', fontSize: 11, fontWeight: 700, color: 'var(--text-light)' }}>EXAMPLE TRACE</th>
              <th style={{ padding: '16px 20px', fontSize: 11, fontWeight: 700, color: 'var(--text-light)', textAlign: 'right' }}>ACTION</th>
            </tr>
          </thead>
          <tbody>
            {gaps.length === 0 ? (
              <tr>
                <td colSpan={4} style={{ padding: 32, textAlign: 'center', color: 'var(--text-light)' }}>No active knowledge gaps detected in RAG data.</td>
              </tr>
            ) : (
              gaps.map((gap, idx) => (
                <tr key={idx} style={{ borderBottom: idx < gaps.length - 1 ? '1px solid var(--border-light)' : 'none' }}>
                  <td style={{ padding: '16px 20px', fontWeight: 600 }}><span className="pill pill-warning" style={{ textTransform: 'none' }}>{gap.term}</span></td>
                  <td style={{ padding: '16px 20px', fontWeight: 600 }}>{gap.count} requests</td>
                  <td style={{ padding: '16px 20px', fontFamily: 'monospace', fontSize: 12, color: 'var(--text-muted)' }}>Trace #{gap.example_message_id}</td>
                  <td style={{ padding: '16px 20px', textAlign: 'right' }}>
                    <button className="btn btn-secondary" style={{ height: 28, fontSize: 12, padding: '0 12px' }} onClick={() => {
                      setNewTitle(`Info regarding ${gap.term}`);
                      setNewTags(gap.term);
                      setShowAddModal(true);
                    }}>Resolve</button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {showAddModal && (
        <div className="modal-backdrop" style={{
          position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
          backgroundColor: 'rgba(15, 23, 42, 0.4)', backdropFilter: 'blur(4px)',
          display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000
        }}>
          <div className="card" style={{ width: '90%', maxWidth: 500, padding: 32 }}>
            <h2 style={{ fontSize: 18, fontWeight: 800, marginBottom: 16 }}>Add Ground Truth Knowledge</h2>
            <form onSubmit={handleAddKnowledge}>
              <div style={{ marginBottom: 16 }}>
                <label style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', display: 'block', marginBottom: 6 }}>TITLE</label>
                <input type="text" className="search-input" style={{ width: '100%', paddingLeft: 12 }} value={newTitle} onChange={e => setNewTitle(e.target.value)} required />
              </div>
              <div style={{ marginBottom: 16 }}>
                <label style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', display: 'block', marginBottom: 6 }}>CONTENT</label>
                <textarea className="search-input" style={{ width: '100%', height: 100, padding: 12, resize: 'none', fontFamily: 'inherit' }} value={newContent} onChange={e => setNewContent(e.target.value)} required />
              </div>
              <div style={{ marginBottom: 20 }}>
                <label style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', display: 'block', marginBottom: 6 }}>TAGS (comma separated)</label>
                <input type="text" className="search-input" style={{ width: '100%', paddingLeft: 12 }} placeholder="e.g. policy, refund, international" value={newTags} onChange={e => setNewTags(e.target.value)} />
              </div>
              <div className="flex-row" style={{ justifyContent: 'flex-end', gap: 12 }}>
                <button type="button" className="btn btn-secondary" onClick={() => setShowAddModal(false)}>Cancel</button>
                <button type="submit" className="btn btn-primary" disabled={submitting}>
                  {submitting ? 'Saving...' : 'Save Knowledge'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

// ==========================================================
// TEST CONSOLE
// ==========================================================
function TestConsoleView() {
  const [query, setQuery] = useState('');
  const [promptVersionId, setPromptVersionId] = useState(2);
  const [provider, setProvider] = useState('openrouter');
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<any>(null);

  const handleReset = () => {
    setQuery('');
    setResult(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!query.trim()) return;
    setLoading(true);
    try {
      const res = await fetch(`${API_URL}/console/query`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query, prompt_version_id: promptVersionId, provider })
      });
      const json = await res.json();
      if (json?.data) {
        setResult(json.data);
      }
    } catch (e) {
      console.error(e);
    }
    setLoading(false);
  };

  return (
    <div style={{ maxWidth: 800, margin: '0 auto' }}>
      <div className="flex-between" style={{ marginBottom: 24 }}>
        <div>
          <h1 style={{ fontSize: 24, fontWeight: 800, marginBottom: 8, letterSpacing: '-0.5px' }}>Test Console</h1>
          <p className="text-muted" style={{ marginBottom: 0 }}>Run queries against specific prompt versions to test RAG response grounding and governance live.</p>
        </div>
        <button className="btn btn-secondary" onClick={handleReset}><RefreshCw size={16} /> Reset</button>
      </div>

      <div className="card" style={{ padding: 24, marginBottom: 24 }}>
        <form onSubmit={handleSubmit}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16, marginBottom: 16 }}>
            <div>
              <label style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', display: 'block', marginBottom: 6 }}>PROMPT VERSION</label>
              <select className="search-input" style={{ width: '100%', paddingLeft: 12 }} value={promptVersionId} onChange={e => setPromptVersionId(Number(e.target.value))}>
                <option value={1}>Prompt v1.0 (Loosely Worded)</option>
                <option value={2}>Prompt v2.0 (Strict Context)</option>
              </select>
            </div>
            <div>
              <label style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', display: 'block', marginBottom: 6 }}>LLM PROVIDER</label>
              <select className="search-input" style={{ width: '100%', paddingLeft: 12 }} value={provider} onChange={e => setProvider(e.target.value)}>
                <option value="mock">Mock LLM Provider</option>
                <option value="openrouter" defaultChecked>OpenRouter API</option>
              </select>
            </div>
          </div>

          <div style={{ marginBottom: 20 }}>
            <label style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', display: 'block', marginBottom: 6 }}>USER QUERY</label>
            <textarea
              className="search-input"
              style={{ width: '100%', height: 100, padding: 12, resize: 'none', fontFamily: 'inherit' }}
              placeholder="e.g. Can I cancel my subscription at any time?"
              value={query}
              onChange={e => setQuery(e.target.value)}
            />
          </div>

          <button className="btn btn-primary" type="submit" style={{ width: '100%', height: 40 }} disabled={loading}>
            {loading ? 'Running Execution...' : 'Run Query'}
          </button>
        </form>
      </div>

      {result && (
        <div className="card" style={{ padding: 24 }}>
          <h2 style={{ fontSize: 16, fontWeight: 800, marginBottom: 16 }}>Execution Result</h2>

          <div style={{ marginBottom: 16 }}>
            <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>AI RESPONSE</div>
            <div style={{ background: 'var(--bg-app)', padding: 16, borderRadius: 8, fontSize: 13, lineHeight: 1.5, border: '1px solid var(--border-light)' }}>
              {result.message?.content}
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 16, marginBottom: 16 }}>
            <div>
              <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>GOVERNANCE STATUS</div>
              <span className={`pill ${result.diagnostics?.root_cause === 'healthy' ? 'pill-success' : 'pill-critical'}`}>
                {result.diagnostics?.root_cause === 'healthy' ? 'Healthy' : 'Anomaly'}
              </span>
            </div>
            <div>
              <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>GROUNDEDNESS</div>
              <div style={{ fontWeight: 700, fontSize: 16 }}>{result.diagnostics?.groundedness_score}</div>
            </div>
            <div>
              <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>LATENCY</div>
              <div style={{ fontWeight: 700, fontSize: 16 }}>{result.diagnostics?.latency_ms}ms</div>
            </div>
          </div>

          {result.diagnostics?.root_cause !== 'healthy' && (
            <div className="card" style={{ padding: 12, backgroundColor: 'var(--critical-bg)', borderColor: 'var(--critical)', color: 'var(--critical)', fontSize: 12, fontWeight: 600 }}>
              ⚠️ Root Cause: {result.diagnostics?.root_cause}
              <div style={{ fontWeight: 400, marginTop: 4 }}>{result.diagnostics?.suggested_fix}</div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

function LoadingSpinner() {
  return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%', padding: 64 }}>
      <RefreshCw size={32} className="text-light" style={{ animation: 'spin 1s linear infinite' }} />
      <style>{`@keyframes spin { 100% { transform: rotate(360deg); } }`}</style>
    </div>
  );
}

// ==========================================================
// ANALYTICS COMMAND CENTER
// ==========================================================
function AnalyticsView({ metrics, loading, onRefresh }: any) {
  const [timeframe, setTimeframe] = useState('30d');
  const [scope, setScope] = useState('production');

  if (loading || !metrics) return <LoadingSpinner />;

  // Dynamic multipliers based on scope & timeframe selection
  const timeframeMultipliers: any = {
    '7d': { traces: 0.23, latency: 0.95, anomaly: 0.8 },
    '30d': { traces: 1.0, latency: 1.0, anomaly: 1.0 },
    '90d': { traces: 3.0, latency: 1.08, anomaly: 1.15 }
  };

  const scopeMultipliers: any = {
    'production': { health: 1.0, latency: 1.0, anomaly: 1.0 },
    'staging': { health: 0.92, latency: 1.15, anomaly: 1.25 },
    'development': { health: 0.78, latency: 0.85, anomaly: 1.6 }
  };

  const tMult = timeframeMultipliers[timeframe];
  const sMult = scopeMultipliers[scope];

  const currentHealth = Math.min(100, Math.round(metrics.score * sMult.health));
  const currentTraces = Math.max(0, Math.round(metrics.breakdown.total_runs * tMult.traces * (scope === 'development' ? 0.6 : 1.0)));
  const currentAnomaly = Math.min(100, Math.round(metrics.breakdown.hallucination_rate * tMult.anomaly * sMult.anomaly));
  const currentLatency = Math.round(metrics.breakdown.avg_latency_ms * tMult.latency * sMult.latency);

  // Generate dynamic chart data based on timeframe & scope
  const getVolumeData = () => {
    const scale = tMult.traces * (scope === 'staging' ? 1.2 : scope === 'development' ? 0.6 : 1.0);
    const latencyScale = sMult.latency;
    return [
      { name: 'Mon', traces: Math.round(40 * scale), latency: Math.round(240 * latencyScale) },
      { name: 'Tue', traces: Math.round(55 * scale), latency: Math.round(280 * latencyScale) },
      { name: 'Wed', traces: Math.round(62 * scale), latency: Math.round(310 * latencyScale) },
      { name: 'Thu', traces: Math.round(48 * scale), latency: Math.round(290 * latencyScale) },
      { name: 'Fri', traces: Math.round(80 * scale), latency: Math.round(450 * latencyScale) },
      { name: 'Sat', traces: Math.round(95 * scale), latency: Math.round(500 * latencyScale) },
      { name: 'Sun', traces: Math.round(60 * scale), latency: Math.round(350 * latencyScale) },
    ];
  };

  const getStatusMix = () => {
    const anomalyVal = currentAnomaly || 10;
    const healthyVal = 100 - anomalyVal;
    return [
      { name: 'Healthy', value: healthyVal, color: '#4f46e5' },
      { name: 'Anomalies', value: anomalyVal, color: '#fee2e2' },
    ];
  };

  const getThroughput = () => {
    const scale = tMult.traces;
    return [
      { name: 'Week 1', resolved: Math.round(10 * scale), new: Math.round(12 * scale) },
      { name: 'Week 2', resolved: Math.round(15 * scale), new: Math.round(8 * scale) },
      { name: 'Week 3', resolved: Math.round(8 * scale), new: Math.round(15 * scale) },
      { name: 'Week 4', resolved: Math.round(22 * scale), new: Math.round(20 * scale) },
    ];
  };

  return (
    <div style={{ maxWidth: 1200, margin: '0 auto' }}>
      <div className="card" style={{ padding: 32, marginBottom: 24 }}>
        <div className="flex-between">
          <div>
            <div className="flex-row" style={{ color: 'var(--brand-primary)', fontWeight: 700, fontSize: 12, marginBottom: 8, textTransform: 'uppercase', letterSpacing: 0.5 }}>
              <BarChart2 size={14} /> Intelligence Center
            </div>
            <h1 style={{ fontSize: 28, fontWeight: 800, marginBottom: 8, letterSpacing: '-0.5px' }}>Analytics Command Center</h1>
            <p className="text-muted" style={{ fontSize: 14, maxWidth: 600 }}>Production-grade visibility into traces, execution velocity, governance patterns, and AI-assisted outcomes — built for serious engineering workflows.</p>
          </div>
          <div style={{ textAlign: 'right' }}>
            <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5, marginBottom: 6 }}>SCOPE & TIMEFRAME</div>
            <div className="flex-row" style={{ gap: 12 }}>
              <button className="btn btn-secondary" style={{ height: 28, padding: '0 12px', borderRadius: 8 }} onClick={onRefresh}><RefreshCw size={14} /> Refresh</button>
              <div style={{ display: 'flex', border: '1px solid var(--border-light)', borderRadius: 20, overflow: 'hidden', marginRight: 12 }}>
                <button
                  style={{ border: 'none', padding: '4px 12px', fontSize: 12, fontWeight: 600, cursor: 'pointer', background: timeframe === '7d' ? 'var(--brand-primary)' : 'var(--bg-app)', color: timeframe === '7d' ? 'white' : 'var(--text-main)' }}
                  onClick={() => setTimeframe('7d')}
                >
                  7d
                </button>
                <button
                  style={{ border: 'none', padding: '4px 12px', fontSize: 12, fontWeight: 600, cursor: 'pointer', background: timeframe === '30d' ? 'var(--brand-primary)' : 'var(--bg-app)', color: timeframe === '30d' ? 'white' : 'var(--text-main)' }}
                  onClick={() => setTimeframe('30d')}
                >
                  30d
                </button>
                <button
                  style={{ border: 'none', padding: '4px 12px', fontSize: 12, fontWeight: 600, cursor: 'pointer', background: timeframe === '90d' ? 'var(--brand-primary)' : 'var(--bg-app)', color: timeframe === '90d' ? 'white' : 'var(--text-main)' }}
                  onClick={() => setTimeframe('90d')}
                >
                  90d
                </button>
              </div>

              {/* SCOPE DROPDOWN */}
              <select
                className="search-input"
                style={{ width: 140, height: 28, padding: '0 8px', borderRadius: 8, fontSize: 12 }}
                value={scope}
                onChange={(e) => setScope(e.target.value)}
              >
                <option value="production">Production</option>
                <option value="staging">Staging</option>
                <option value="development">Development</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 24 }}>
        <div className="card" style={{ padding: 24 }}>
          <div className="flex-between" style={{ marginBottom: 12 }}>
            <span style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5 }}>GOVERNANCE INDEX</span>
            <div className="qa-purple" style={{ width: 32, height: 32, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Activity size={16} className="text-primary" style={{ color: 'var(--brand-primary)' }} />
            </div>
          </div>
          <div style={{ fontSize: 32, fontWeight: 800, letterSpacing: '-1px' }}>{currentHealth}%</div>
          <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Composite score</div>
        </div>
        <div className="card" style={{ padding: 24 }}>
          <div className="flex-between" style={{ marginBottom: 12 }}>
            <span style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5 }}>TRACES</span>
            <div className="qa-blue" style={{ width: 32, height: 32, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Video size={16} className="text-info" />
            </div>
          </div>
          <div style={{ fontSize: 32, fontWeight: 800, letterSpacing: '-1px' }}>{currentTraces}</div>
          <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Total executions</div>
        </div>
        <div className="card" style={{ padding: 24 }}>
          <div className="flex-between" style={{ marginBottom: 12 }}>
            <span style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5 }}>ANOMALY RATE</span>
            <div className="qa-orange" style={{ width: 32, height: 32, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <AlertTriangle size={16} className="text-warning" />
            </div>
          </div>
          <div style={{ fontSize: 32, fontWeight: 800, letterSpacing: '-1px' }}>{currentAnomaly}%</div>
          <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Failure occurrences</div>
        </div>
        <div className="card" style={{ padding: 24 }}>
          <div className="flex-between" style={{ marginBottom: 12 }}>
            <span style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5 }}>AVG LATENCY</span>
            <div className="qa-green" style={{ width: 32, height: 32, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <CheckSquare size={16} className="text-success" />
            </div>
          </div>
          <div style={{ fontSize: 32, fontWeight: 800, letterSpacing: '-1px' }}>{currentLatency}ms</div>
          <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Response time</div>
        </div>
      </div>

      <div className="card" style={{ padding: 24, marginBottom: 24, backgroundColor: 'var(--brand-light)', border: 'none' }}>
        <div className="flex-row" style={{ fontWeight: 700, fontSize: 14, marginBottom: 16 }}>
          <Activity size={16} /> Executive Insights ({timeframe} window - {scope} environment)
        </div>
        <ul style={{ paddingLeft: 24, fontSize: 13, lineHeight: 1.8, color: 'var(--text-main)' }}>
          <li>The AI agent processed <b>{currentTraces}</b> traces in this window with an average latency of {currentLatency}ms.</li>
          <li>System health is sitting at <b>{currentHealth}%</b>. {currentAnomaly}% of traces resulted in anomalies requiring investigation.</li>
          <li>{scope === 'development' ? '⚠️ High anomaly rate detected in Development environment due to loose drafting.' : '✓ Operational metrics are within safety limits.'}</li>
          <li>Knowledge Base grounding remains high at {metrics.breakdown.grounding_pct}%, ensuring accurate factual retrieval.</li>
        </ul>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 24, marginBottom: 24 }}>
        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontSize: 14, fontWeight: 700, marginBottom: 24 }}>Trace Volume & Latency</h3>
          <div style={{ height: 250 }}>
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={getVolumeData()}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border-light)" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: 'var(--text-muted)' }} />
                <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: 'var(--text-muted)' }} />
                <Tooltip />
                <Line type="monotone" dataKey="traces" stroke="#f59e0b" strokeWidth={3} dot={false} name="Traces Count" />
                <Line type="monotone" dataKey="latency" stroke="#4f46e5" strokeWidth={3} dot={false} name="Latency (ms)" />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontSize: 14, fontWeight: 700, marginBottom: 24 }}>Health Status Mix</h3>
          <div style={{ height: 250 }}>
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={getStatusMix()} innerRadius={60} outerRadius={100} paddingAngle={5} dataKey="value">
                  {getStatusMix().map((entry: any, index: number) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 24, marginBottom: 24 }}>
        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontSize: 14, fontWeight: 700, marginBottom: 24 }}>Anomaly Throughput</h3>
          <div style={{ height: 200 }}>
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={getThroughput()}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border-light)" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: 'var(--text-muted)' }} />
                <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: 'var(--text-muted)' }} />
                <Tooltip />
                <Bar dataKey="new" fill="#4f46e5" radius={[4, 4, 0, 0]} name="New Issues" />
                <Bar dataKey="resolved" fill="#10b981" radius={[4, 4, 0, 0]} name="Resolved Issues" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontSize: 14, fontWeight: 700, marginBottom: 24 }}>Pipeline</h3>
          <div style={{ height: 200 }}>
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={[{ name: 'Done', value: 40, color: '#10b981' }, { name: 'In Progress', value: 30, color: '#4f46e5' }, { name: 'To Do', value: 30, color: '#94a3b8' }]} innerRadius={40} outerRadius={70} paddingAngle={2} dataKey="value">
                  {[{ name: 'Done', value: 40, color: '#10b981' }, { name: 'In Progress', value: 30, color: '#4f46e5' }, { name: 'To Do', value: 30, color: '#94a3b8' }].map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    </div>
  );
}

// ==========================================================
// TRACE TIMELINE / LIFECYCLE REPLAY MODAL
// ==========================================================
function ReplayModal({ messageId, onClose }: any) {
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState<any>(null);
  const [compareData, setCompareData] = useState<any>(null);
  const [selectedPromptId, setSelectedPromptId] = useState<number>(2); // default to strict prompt
  const [replaying, setReplaying] = useState(false);

  useEffect(() => {
    fetchReplayData();
  }, [messageId]);

  const fetchReplayData = async () => {
    setLoading(true);
    try {
      const res = await fetch(`${API_URL}/messages/${messageId}/replay`);
      const json = await res.json();
      if (json?.data) {
        setData(json.data);
        const currentPromptId = json.data.prompt_version?.id;
        setSelectedPromptId(currentPromptId === 1 ? 2 : 1);
      }
    } catch (e) {
      console.error(e);
    }
    setLoading(false);
  };

  const handleReplay = async () => {
    setReplaying(true);
    try {
      const res = await fetch(`${API_URL}/messages/${messageId}/replay`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt_version_id: selectedPromptId })
      });
      const json = await res.json();
      if (json?.data) {
        setCompareData(json.data);
      }
    } catch (e) {
      console.error(e);
    }
    setReplaying(false);
  };

  if (loading) return (
    <div className="modal-backdrop" style={{
      position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
      backgroundColor: 'rgba(15, 23, 42, 0.4)', backdropFilter: 'blur(4px)',
      display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000
    }}>
      <LoadingSpinner />
    </div>
  );
  if (!data) return null;

  return (
    <div className="modal-backdrop" style={{
      position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
      backgroundColor: 'rgba(15, 23, 42, 0.4)', backdropFilter: 'blur(4px)',
      display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000
    }}>
      <div className="card" style={{ width: '90%', maxWidth: 1000, maxHeight: '90vh', overflowY: 'auto', padding: 32, display: 'flex', flexDirection: 'column', gap: 24 }}>
        <div className="flex-between">
          <div>
            <span style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-light)', letterSpacing: 0.5 }}>FLIGHT REPLAY</span>
            <h2 style={{ fontSize: 22, fontWeight: 800, marginTop: 4, letterSpacing: '-0.5px' }}>Trace Lifecycle analysis</h2>
          </div>
          <button className="btn btn-secondary" onClick={onClose}>Close</button>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: compareData ? '1fr 1fr' : '1fr', gap: 24 }}>
          {/* Original Run */}
          <div className="card" style={{ padding: 20, backgroundColor: 'var(--bg-app)', border: 'none' }}>
            <div style={{ fontWeight: 700, fontSize: 14, marginBottom: 12, color: 'var(--brand-primary)' }}>
              Original Execution (Prompt v{data.prompt_version?.version})
            </div>

            <div style={{ marginBottom: 16 }}>
              <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>USER QUERY</div>
              <div style={{ fontWeight: 600 }}>{data.conversation?.title?.replace('Healthy Flow: ', '')?.replace('Broken Flow: ', '') || 'Refund policy check'}</div>
            </div>

            <div style={{ marginBottom: 16 }}>
              <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>ASSISTANT RESPONSE</div>
              <div style={{ fontSize: 13, lineHeight: 1.5, background: 'var(--bg-card)', padding: 12, borderRadius: 8, border: '1px solid var(--border-light)' }}>
                {data.message?.content}
              </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, marginBottom: 16 }}>
              <div>
                <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>LATENCY</div>
                <div style={{ fontWeight: 700 }}>{data.diagnostics?.latency_ms}ms</div>
              </div>
              <div>
                <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>GROUNDEDNESS</div>
                <div style={{ fontWeight: 700 }}>{data.diagnostics?.groundedness_score}</div>
              </div>
            </div>

            {data.diagnostics?.root_cause !== 'healthy' && (
              <div className="card" style={{ padding: 12, backgroundColor: 'var(--critical-bg)', borderColor: 'var(--critical)', color: 'var(--critical)', fontSize: 12, fontWeight: 600 }}>
                ⚠️ Anomaly Detected: {data.diagnostics?.root_cause}
                <div style={{ fontWeight: 400, marginTop: 4 }}>{data.diagnostics?.suggested_fix}</div>
              </div>
            )}
          </div>

          {/* Comparative Run (when replayed) */}
          {compareData && (
            <div className="card" style={{ padding: 20, backgroundColor: 'var(--bg-app)', border: 'none' }}>
              <div style={{ fontWeight: 700, fontSize: 14, marginBottom: 12, color: 'var(--success)' }}>
                Comparative Execution (Prompt v{compareData.prompt_version?.version})
              </div>
              <div style={{ marginBottom: 16 }}>
                <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>USER QUERY</div>
                <div style={{ fontWeight: 600 }}>{data.conversation?.title?.replace('Healthy Flow: ', '')?.replace('Broken Flow: ', '') || 'Refund policy check'}</div>
              </div>

              <div style={{ marginBottom: 16 }}>
                <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>ASSISTANT RESPONSE</div>
                <div style={{ fontSize: 13, lineHeight: 1.5, background: 'var(--bg-card)', padding: 12, borderRadius: 8, border: '1px solid var(--border-light)' }}>
                  {compareData.message?.content}
                </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, marginBottom: 16 }}>
                <div>
                  <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>LATENCY</div>
                  <div style={{ fontWeight: 700 }}>{compareData.diagnostics?.latency_ms}ms</div>
                </div>
                <div>
                  <div style={{ fontSize: 10, fontWeight: 700, color: 'var(--text-light)', marginBottom: 4 }}>GROUNDEDNESS</div>
                  <div style={{ fontWeight: 700 }}>{compareData.diagnostics?.groundedness_score}</div>
                </div>
              </div>

              {compareData.diagnostics?.root_cause !== 'healthy' ? (
                <div className="card" style={{ padding: 12, backgroundColor: 'var(--critical-bg)', borderColor: 'var(--critical)', color: 'var(--critical)', fontSize: 12, fontWeight: 600 }}>
                  ⚠️ Anomaly Detected: {compareData.diagnostics?.root_cause}
                  <div style={{ fontWeight: 400, marginTop: 4 }}>{compareData.diagnostics?.suggested_fix}</div>
                </div>
              ) : (
                <div className="card" style={{ padding: 12, backgroundColor: 'var(--success-bg)', borderColor: 'var(--success)', color: 'var(--success)', fontSize: 12, fontWeight: 600 }}>
                  ✅ Healthy Response: Grounded & Accurate.
                </div>
              )}
            </div>
          )}
        </div>

        {/* Action Panel for comparative replay */}
        {!compareData && (
          <div className="card" style={{ padding: 20 }}>
            <h3 style={{ fontSize: 14, fontWeight: 700, marginBottom: 12 }}>Run Comparative Replay</h3>
            <p className="text-muted" style={{ fontSize: 12, marginBottom: 16 }}>Re-run this query against an alternative system prompt version to evaluate grounding performance changes side-by-side.</p>
            <div className="flex-row">
              <select className="search-input" style={{ width: 300, paddingLeft: 12 }} value={selectedPromptId} onChange={(e) => setSelectedPromptId(Number(e.target.value))}>
                <option value={1}>Prompt v1.0 (Loosely Worded)</option>
                <option value={2}>Prompt v2.0 (Strict Context Enforcement)</option>
              </select>
              <button className="btn btn-primary" onClick={handleReplay} disabled={replaying}>
                {replaying ? 'Running Replay...' : 'Trigger Replay Run'}
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
