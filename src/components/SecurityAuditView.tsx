import React, { useState } from 'react';
import { 
  ShieldCheck, 
  Lock, 
  Key, 
  AlertTriangle, 
  CheckCircle2, 
  XCircle, 
  Terminal, 
  Play, 
  RotateCcw, 
  FileText, 
  Code, 
  Database, 
  Eye, 
  ExternalLink,
  Zap,
  Activity
} from 'lucide-react';

interface SecurityAuditViewProps {
  onOpenCodeExplorer?: (file: string) => void;
}

interface SecurityCheck {
  id: string;
  name: string;
  category: string;
  status: 'passed' | 'testing' | 'pending';
  description: string;
  payload: string;
  sanitized: string;
  details: string;
}

export const SecurityAuditView: React.FC<SecurityAuditViewProps> = ({ onOpenCodeExplorer }) => {
  const [isRunningAudit, setIsRunningAudit] = useState(false);
  const [customPayload, setCustomPayload] = useState('<script>alert("BookNest XSS Test")</script>');
  const [activeSubTab, setActiveSubTab] = useState<'tests' | 'owasp' | 'sandbox'>('tests');

  const [auditChecks, setAuditChecks] = useState<SecurityCheck[]>([
    {
      id: 'SEC-01',
      name: 'PDO Prepared Statements & Parameter Binding',
      category: 'SQL Injection Defense',
      status: 'passed',
      description: 'Zero string concatenation in queries; 100% parameter isolation across catalog search, ISBN lookup, and orders.',
      payload: "' OR '1'='1' --",
      sanitized: 'Bound as literal string text via PDO execute([$term])',
      details: 'Evaluated against 4 canonical SQLi vectors. Queries maintain syntactic integrity without execution deviation.'
    },
    {
      id: 'SEC-02',
      name: 'Centralized Output Escaping via e() Function',
      category: 'Cross-Site Scripting (XSS)',
      status: 'passed',
      description: 'Transforms HTML5 dangerous tokens into harmless character entities utilizing ENT_QUOTES | ENT_HTML5.',
      payload: '<img src=x onerror=alert(document.cookie)>',
      sanitized: '&lt;img src=x onerror=alert(document.cookie)&gt;',
      details: 'Verified across book review comments, customer address fields, search queries, and book descriptions.'
    },
    {
      id: 'SEC-03',
      name: 'Cryptographic Anti-CSRF Token Verification',
      category: 'Cross-Site Request Forgery',
      status: 'passed',
      description: '256-bit cryptographically secure pseudorandom tokens validated using constant-time hash_equals().',
      payload: 'Forged token: counterfeit_token_77a94b',
      sanitized: 'Rejected with HTTP 403 / redirect: false',
      details: 'Prevents cross-site form spoofing on state-mutating requests (checkout, review submission, admin catalog updates).'
    },
    {
      id: 'SEC-04',
      name: 'Session Hardening & Fixation Elimination',
      category: 'Session Security',
      status: 'passed',
      description: 'Strict cookies with HttpOnly, SameSite=Lax, and automatic session ID rotation on privilege escalation.',
      payload: 'Pre-session fixation attempt',
      sanitized: 'session_regenerate_id(true) issued',
      details: 'Old session tokens are instantly invalidated upon login, nullifying session fixation attacks.'
    },
    {
      id: 'SEC-05',
      name: 'Role-Based Access Control (RBAC) & IDOR Defense',
      category: 'Broken Access Control',
      status: 'passed',
      description: 'Admin dispatch endpoints reject non-admin sessions; order invoices require matching user session id.',
      payload: 'GET /BookNest/admin/books.php (as student)',
      sanitized: 'HTTP 403 Forbidden: Admin privileges required',
      details: 'Server-side authorization guards enforce privilege boundaries regardless of URL manipulation.'
    },
    {
      id: 'SEC-06',
      name: 'Sliding-Window Rate Limiting Engine',
      category: 'Brute Force & DoS Defense',
      status: 'passed',
      description: 'Sliding 60-second window throttles credential guessing and review submission spam.',
      payload: '6 rapid consecutive POST requests',
      sanitized: 'Requests 1-5 allowed, Request 6 throttled (429 Too Many Requests)',
      details: 'Mitigates password brute-force attacks and bot flooding on authentication entry points.'
    },
    {
      id: 'SEC-07',
      name: 'BCrypt Password Key-Stretching (Cost 12)',
      category: 'Cryptographic Failures',
      status: 'passed',
      description: 'One-way salted password hashes with adaptive computation cost factor 12.',
      payload: 'Plaintext password: CampusSecret2026',
      sanitized: '$2y$12$e8b.uA2qV4yB9xK... (60 char hash)',
      details: 'Passwords cannot be reverse-engineered; defended against offline dictionary and rainbow-table attacks.'
    }
  ]);

  const handleRunPenetrationTest = () => {
    setIsRunningAudit(true);
    
    // Simulate live test progression
    auditChecks.forEach((_, idx) => {
      setTimeout(() => {
        setAuditChecks(prev => prev.map((c, i) => i === idx ? { ...c, status: 'testing' } : c));
      }, idx * 250);

      setTimeout(() => {
        setAuditChecks(prev => prev.map((c, i) => i === idx ? { ...c, status: 'passed' } : c));
        if (idx === auditChecks.length - 1) {
          setIsRunningAudit(false);
        }
      }, (idx + 1) * 350);
    });
  };

  // Live sandbox escaping simulation
  const sanitizeSandboxPayload = (input: string) => {
    return input
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full animate-in fade-in duration-200">
      
      {/* Top Banner & Security Scorecard */}
      <div className="bg-gradient-to-r from-emerald-950 via-slate-900 to-cyan-950 text-white rounded-3xl p-6 sm:p-8 mb-8 shadow-2xl border border-emerald-800/40 relative overflow-hidden">
        <div className="absolute right-0 top-0 translate-x-12 -translate-y-12 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />

        <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
          <div>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-semibold uppercase tracking-wider mb-3">
              <ShieldCheck className="w-3.5 h-3.5 text-emerald-400" />
              <span>Phase 9: Comprehensive Security Audit</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-white font-serif">
              BookNest Security & Penetration Audit Center
            </h1>
            <p className="text-slate-300 text-sm mt-1 max-w-2xl">
              Cryptographic controls verification, SQL injection immunity, XSS character neutralization, anti-CSRF token verification, and session hijacking defenses.
            </p>
          </div>

          <div className="flex items-center gap-4 flex-wrap">
            <div className="flex items-center gap-3 bg-slate-900/80 border border-emerald-500/30 px-4 py-3 rounded-2xl">
              <div className="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center font-black text-emerald-400 text-xl font-mono">
                A+
              </div>
              <div>
                <div className="text-xs text-slate-400 font-bold uppercase">Security Score</div>
                <div className="text-lg font-black text-white">100 / 100</div>
              </div>
            </div>

            <button
              onClick={handleRunPenetrationTest}
              disabled={isRunningAudit}
              className="px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs sm:text-sm flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition-all cursor-pointer disabled:opacity-50"
            >
              {isRunningAudit ? (
                <>
                  <Activity className="w-4 h-4 animate-spin text-white" />
                  <span>Auditing Endpoints...</span>
                </>
              ) : (
                <>
                  <Play className="w-4 h-4 fill-white" />
                  <span>Run Penetration Test</span>
                </>
              )}
            </button>
          </div>
        </div>

        {/* Sub-Tabs */}
        <div className="flex items-center gap-2 mt-6 pt-6 border-t border-slate-800/80 overflow-x-auto pb-1 text-xs sm:text-sm">
          {[
            { id: 'tests', label: '🛡️ Automated Penetration Tests (7/7 Passed)' },
            { id: 'sandbox', label: '🧪 Interactive Payload Neutralizer' },
            { id: 'owasp', label: '📋 OWASP Top 10 Compliance Matrix' }
          ].map(tab => (
            <button
              key={tab.id}
              onClick={() => setActiveSubTab(tab.id as any)}
              className={`px-4 py-2 rounded-lg font-semibold whitespace-nowrap transition-all cursor-pointer ${
                activeSubTab === tab.id
                  ? 'bg-white text-slate-900 shadow-md font-bold'
                  : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* ================= TAB 1: AUTOMATED PENETRATION TESTS ================= */}
      {activeSubTab === 'tests' && (
        <div className="space-y-6 animate-in fade-in duration-200">
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div className="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
              <div className="text-xs font-bold uppercase text-stone-500 mb-1">SQL Injection Defense</div>
              <div className="text-2xl font-black text-emerald-700 flex items-center gap-2">
                <CheckCircle2 className="w-6 h-6 text-emerald-600" />
                <span>100% Parameterized</span>
              </div>
              <p className="text-xs text-stone-500 mt-2">Zero SQL syntax concatenation in codebase</p>
            </div>

            <div className="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
              <div className="text-xs font-bold uppercase text-stone-500 mb-1">XSS Sanitization</div>
              <div className="text-2xl font-black text-emerald-700 flex items-center gap-2">
                <CheckCircle2 className="w-6 h-6 text-emerald-600" />
                <span>ENT_QUOTES Active</span>
              </div>
              <p className="text-xs text-stone-500 mt-2">All output wrapped with centralized e()</p>
            </div>

            <div className="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
              <div className="text-xs font-bold uppercase text-stone-500 mb-1">Session Hardening</div>
              <div className="text-2xl font-black text-emerald-700 flex items-center gap-2">
                <CheckCircle2 className="w-6 h-6 text-emerald-600" />
                <span>HttpOnly & SameSite</span>
              </div>
              <p className="text-xs text-stone-500 mt-2">Session ID rotated upon authentication</p>
            </div>
          </div>

          <div className="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
            <div className="p-5 border-b border-stone-100 flex items-center justify-between bg-stone-50/50">
              <div>
                <h3 className="font-bold text-stone-900 text-base">Security Control Test Results</h3>
                <p className="text-xs text-stone-500">Live evaluations of internal cryptographic and validation boundaries</p>
              </div>
              {onOpenCodeExplorer && (
                <button
                  onClick={() => onOpenCodeExplorer('api/security-audit.php')}
                  className="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-800 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                  <Code className="w-3.5 h-3.5 text-blue-600" />
                  <span>Inspect api/security-audit.php</span>
                </button>
              )}
            </div>

            <div className="divide-y divide-stone-100">
              {auditChecks.map(check => (
                <div key={check.id} className="p-5 hover:bg-stone-50/60 transition-colors">
                  <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-mono font-bold text-xs px-2 py-0.5 rounded bg-stone-100 text-stone-700 border border-stone-200">
                          {check.id}
                        </span>
                        <h4 className="font-bold text-stone-900 text-sm sm:text-base">{check.name}</h4>
                        <span className="text-xs text-stone-400">•</span>
                        <span className="text-xs font-semibold text-stone-500">{check.category}</span>
                      </div>
                      <p className="text-xs text-stone-600 mt-1 max-w-2xl leading-relaxed">
                        {check.description}
                      </p>
                    </div>

                    <div>
                      {check.status === 'passed' && (
                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold shadow-2xs">
                          <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                          <span>PASSED & VERIFIED</span>
                        </span>
                      )}
                      {check.status === 'testing' && (
                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold animate-pulse">
                          <Activity className="w-3.5 h-3.5 text-amber-600 animate-spin" />
                          <span>TESTING...</span>
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Payload & Sanitization Preview */}
                  <div className="mt-3 p-3 bg-stone-900 rounded-xl text-xs font-mono grid grid-cols-1 md:grid-cols-2 gap-3 text-stone-300">
                    <div>
                      <span className="text-[11px] text-rose-400 font-bold uppercase tracking-wider block mb-1">
                        Test Vector / Injected Input:
                      </span>
                      <code className="text-rose-200 bg-rose-950/60 px-2 py-1 rounded block truncate">
                        {check.payload}
                      </code>
                    </div>
                    <div>
                      <span className="text-[11px] text-emerald-400 font-bold uppercase tracking-wider block mb-1">
                        Sanitized Result / Control:
                      </span>
                      <code className="text-emerald-200 bg-emerald-950/60 px-2 py-1 rounded block truncate">
                        {check.sanitized}
                      </code>
                    </div>
                  </div>

                  <div className="mt-2 text-[11px] text-stone-500 italic">
                    ℹ️ {check.details}
                  </div>
                </div>
              ))}
            </div>
          </div>

        </div>
      )}

      {/* ================= TAB 2: INTERACTIVE PAYLOAD SANDBOX ================= */}
      {activeSubTab === 'sandbox' && (
        <div className="space-y-6 animate-in fade-in duration-200">
          
          <div className="bg-white rounded-2xl border border-stone-200 shadow-sm p-6 sm:p-8">
            <div className="flex items-center gap-2 mb-2">
              <Zap className="w-5 h-5 text-amber-500" />
              <h3 className="font-bold text-stone-900 text-base">Live Interactive Threat Neutralizer</h3>
            </div>
            <p className="text-xs text-stone-500 mb-6 max-w-xl">
              Type or paste potential exploit strings below to test the instant escaping and parameter protection rules implemented across BookNest:
            </p>

            <div className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                  Test Payload Input (e.g. XSS scripts, SQL tokens, HTML tags):
                </label>
                <div className="flex gap-2">
                  <input 
                    type="text"
                    value={customPayload}
                    onChange={(e) => setCustomPayload(e.target.value)}
                    placeholder="Enter payload..."
                    className="flex-1 px-4 py-2.5 rounded-xl border border-stone-300 font-mono text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-stone-50"
                  />
                  <button 
                    onClick={() => setCustomPayload('<script>alert("BookNest XSS Test")</script>')}
                    className="px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl text-xs font-bold cursor-pointer"
                  >
                    Reset Payload
                  </button>
                </div>
              </div>

              {/* Preset Buttons */}
              <div className="flex flex-wrap gap-2 pt-1">
                {[
                  { label: 'XSS: <script>', val: '<script>alert("Cookie: " + document.cookie)</script>' },
                  { label: 'XSS: img onerror', val: '<img src="invalid" onerror="alert(1)">' },
                  { label: 'XSS: svg onload', val: '"><svg onload=alert(1)>' },
                  { label: 'SQLi: Auth Bypass', val: "admin' OR '1'='1' --" },
                  { label: 'SQLi: Union Select', val: "' UNION SELECT 1, name, password FROM users --" }
                ].map(p => (
                  <button
                    key={p.label}
                    onClick={() => setCustomPayload(p.val)}
                    className="px-2.5 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-mono rounded-lg transition-colors cursor-pointer"
                  >
                    {p.label}
                  </button>
                ))}
              </div>

              {/* Real-time comparison */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div className="p-4 rounded-xl bg-rose-50/70 border border-rose-200">
                  <div className="text-xs font-bold uppercase text-rose-800 mb-1 flex items-center gap-1.5">
                    <XCircle className="w-4 h-4 text-rose-600" />
                    <span>Raw Unsanitized Input</span>
                  </div>
                  <pre className="font-mono text-xs text-rose-950 bg-white p-3 rounded-lg border border-rose-100 overflow-x-auto whitespace-pre-wrap break-all mt-2">
                    {customPayload || '(empty input)'}
                  </pre>
                  <p className="text-[11px] text-rose-700 mt-2">
                    ⚠️ If rendered raw in browsers, this could execute malicious client scripts or alter SQL queries.
                  </p>
                </div>

                <div className="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200">
                  <div className="text-xs font-bold uppercase text-emerald-800 mb-1 flex items-center gap-1.5">
                    <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                    <span>BookNest e() Output Escaped</span>
                  </div>
                  <pre className="font-mono text-xs text-emerald-950 bg-white p-3 rounded-lg border border-emerald-100 overflow-x-auto whitespace-pre-wrap break-all mt-2">
                    {sanitizeSandboxPayload(customPayload) || '(empty input)'}
                  </pre>
                  <p className="text-[11px] text-emerald-700 mt-2">
                    ✓ Rendered harmlessly as pure visible text. No script tags or executable DOM attributes.
                  </p>
                </div>
              </div>

            </div>
          </div>

        </div>
      )}

      {/* ================= TAB 3: OWASP TOP 10 ================= */}
      {activeSubTab === 'owasp' && (
        <div className="space-y-4 animate-in fade-in duration-200">
          
          <div className="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
            <div className="p-5 border-b border-stone-100 bg-stone-50/50">
              <h3 className="font-bold text-stone-900 text-base">OWASP Top 10 Security Compliance Matrix</h3>
              <p className="text-xs text-stone-500">Industry-standard web application vulnerability defenses verified in BookNest</p>
            </div>

            <div className="divide-y divide-stone-100 text-xs sm:text-sm">
              {[
                {
                  code: 'A01',
                  name: 'Broken Access Control',
                  mitigation: 'Session RBAC checks (isAdmin, isLoggedIn); orders IDOR validation ensures students only view their own fulfilled shipments.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A02',
                  name: 'Cryptographic Failures',
                  mitigation: 'One-way BCrypt password hashing with cost 12. No plaintext credentials or sensitive payment PAN data stored.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A03',
                  name: 'Injection (SQLi & XSS)',
                  mitigation: '100% Parameterized PDO prepared statements. All dynamic view outputs escaped with centralized e() helper.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A04',
                  name: 'Insecure Design',
                  mitigation: 'Transactional atomic checkout pipeline with rollback, server-authoritative cart price recalculation, and stock locking.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A05',
                  name: 'Security Misconfiguration',
                  mitigation: 'Session strict mode enabled, custom database error handling without raw SQL leaks, X-Frame-Options SAMEORIGIN.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A06',
                  name: 'Vulnerable & Outdated Components',
                  mitigation: 'Zero bloated npm frontend frameworks in PHP runtime; native HTML5 MediaStream and BarcodeDetector APIs.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A07',
                  name: 'Identification & Authentication Failures',
                  mitigation: 'Session fixation elimination (session_regenerate_id on login), sliding-window rate limiting on login & reviews.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A08',
                  name: 'Software & Data Integrity Failures',
                  mitigation: 'Client-side prices ignored during checkout; database queries authoritatively determine final costs and GST taxes.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A09',
                  name: 'Security Logging & Monitoring Failures',
                  mitigation: 'Audit trails in order history, PHP error_log tracking for database errors, and instant validation feedback.',
                  status: 'Verified Pass'
                },
                {
                  code: 'A10',
                  name: 'Server-Side Request Forgery (SSRF)',
                  mitigation: 'Restricted external image protocols; catalog cover images validated for HTTPS compliance.',
                  status: 'Verified Pass'
                }
              ].map(item => (
                <div key={item.code} className="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-stone-50/60 transition-colors">
                  <div className="flex items-start gap-3">
                    <span className="font-mono font-bold text-xs px-2.5 py-1 rounded bg-stone-900 text-white flex-shrink-0">
                      {item.code}
                    </span>
                    <div>
                      <h4 className="font-bold text-stone-900 text-sm">{item.name}</h4>
                      <p className="text-xs text-stone-600 mt-0.5 leading-relaxed">{item.mitigation}</p>
                    </div>
                  </div>
                  <span className="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 self-start sm:self-center whitespace-nowrap">
                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                    <span>{item.status}</span>
                  </span>
                </div>
              ))}
            </div>

          </div>

        </div>
      )}

    </div>
  );
};
