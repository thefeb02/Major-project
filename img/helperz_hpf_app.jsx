import { useState, useRef, useEffect } from "react";

// ─── HPF DATA ───────────────────────────────────────────────────────────────
const DOMAINS = [
  { id: "observing", name: "Observing", acro: "PCAREIA", icon: "ti-eye", color: "#1D9E75", bg: "#E1F5EE", dark: "#0F6E56",
    growth: ["Sensory", "Cognitive"],
    prompt: "What are you noticing right now — in your environment and within yourself?",
    subdomains: ["Perceptual","Continuous","Analytical","Reflective","External","Internal","Active"],
    tip: "Try noticing 3 things externally and 3 things internally before writing." },
  { id: "understanding", name: "Understanding", acro: "CECIARI", icon: "ti-brain", color: "#534AB7", bg: "#EEEDFE", dark: "#3C3489",
    growth: ["Cognitive", "Emotional"],
    prompt: "What meaning are you drawing from what you've observed?",
    subdomains: ["Conceptual","Empathetic","Contextual","Intuitive","Adaptive","Reflective","Interdisciplinary"],
    tip: "Empathetic understanding means asking: how might others interpret this same situation?" },
  { id: "thinking", name: "Thinking", acro: "LCCASAH", icon: "ti-bulb", color: "#BA7517", bg: "#FAEEDA", dark: "#854F0B",
    growth: ["Cognitive", "Spiritual"],
    prompt: "What ideas or solutions emerge when you reflect on this?",
    subdomains: ["Logical","Creative","Critical","Abstract","System","Analytical","Holistic"],
    tip: "Push beyond your first idea — holistic thinking looks at the whole system." },
  { id: "deciding", name: "Deciding", acro: "RIICETS", icon: "ti-git-branch", color: "#185FA5", bg: "#E6F1FB", dark: "#0C447C",
    growth: ["Cognitive", "Social"],
    prompt: "What choice will you make today that aligns with your values?",
    subdomains: ["Rational","Intuitive","Informed","Collaborative","Ethical","Timely","Strategic"],
    tip: "A good decision is ethical and timely — not just rational." },
  { id: "performing", name: "Performing", acro: "ECCASSE", icon: "ti-rocket", color: "#993C1D", bg: "#FAECE7", dark: "#712B13",
    growth: ["Sensory", "Social"],
    prompt: "What one action will you take — and how will you do it ethically?",
    subdomains: ["Efficient","Creative","Collaborative","Adaptive","Skillful","Strategic","Ethical"],
    tip: "The best action is strategic and ethical — not just efficient." },
  { id: "experiencing", name: "Experiencing", acro: "SCESCLT", icon: "ti-heart", color: "#993556", bg: "#FBEAF0", dark: "#72243E",
    growth: ["Emotional", "Spiritual"],
    prompt: "What did today's action teach you?",
    subdomains: ["Sensory","Cognitive","Emotional","Social","Cultural","Learning","Transformative"],
    tip: "Transformative experience changes how you see yourself — look for that shift." },
  { id: "repeating", name: "Repeating", acro: "IRACSHP", icon: "ti-refresh", color: "#3B6D11", bg: "#EAF3DE", dark: "#27500A",
    growth: ["Cognitive", "Purposeful"],
    prompt: "What will you carry into tomorrow's cycle?",
    subdomains: ["Iterative","Reflective","Adaptive","Continuous","Strategic","Habitual","Purposeful"],
    tip: "Purposeful repeating means consciously choosing what habits to reinforce." },
];

const GROWTH_DIMS = ["Sensory","Cognitive","Emotional","Social","Spiritual"];

const ASSESSMENT_Qs = DOMAINS.flatMap((d, di) => [
  { domain: di, q: `How often do you actively ${d.name.toLowerCase()} during your day?` },
  { domain: di, q: `How satisfied are you with your ${d.name.toLowerCase()} ability currently?` },
]);

const HPF_KB = {
  keywords: {
    "hpf|human purpose": "HPF states: Human Purpose is to observe, understand, think, decide, perform, experience, and repeat over spacetime for growth and development. Created by Ashok Upadhya, it's a universal framework that works across all cultures, ages, and beliefs.",
    "formula|equation|7d|49|5gd": "The HPF formula is [7D] × [49SD] × 6W = 5GD. That's 7 Domains × 49 Sub-Domains × 6W context (What, Where, When, Why, Who, How) = 5 Growth Dimensions (Sensory, Cognitive, Emotional, Social, Spiritual).",
    "6w|context|spacetime": "The 6W model captures spacetime context: What (the situation), Where (location), When (time/stage), Why (intention), Who (people involved), and How (approach). This makes HPF adaptive to any real-life situation.",
    "observ": "Observing is the foundation — gathering information from the world and yourself. Sub-domains: Perceptual, Continuous, Analytical, Reflective, External, Internal, Active. Growth: Sensory + Cognitive.",
    "understand": "Understanding transforms observations into meaning. Sub-domains: Conceptual, Empathetic, Contextual, Intuitive, Adaptive, Reflective, Interdisciplinary. Growth: Cognitive + Emotional.",
    "think": "Thinking generates and evaluates ideas. Sub-domains: Logical, Creative, Critical, Abstract, System, Analytical, Holistic. Growth: Cognitive + Spiritual.",
    "decid": "Deciding converts understanding into choices. Sub-domains: Rational, Intuitive, Informed, Collaborative, Ethical, Timely, Strategic. Growth: Cognitive + Social.",
    "perform": "Performing is the execution of decisions. Sub-domains: Efficient, Creative, Collaborative, Adaptive, Skillful, Strategic, Ethical. Growth: Sensory + Social.",
    "experienc": "Experiencing is the feedback from action. Sub-domains: Sensory, Cognitive, Emotional, Social, Cultural, Learning, Transformative. Growth: Emotional + Spiritual.",
    "repeat": "Repeating ensures refinement and habit formation. Sub-domains: Iterative, Reflective, Adaptive, Continuous, Strategic, Habitual, Purposeful. Growth: Cognitive + Purposeful.",
    "growth|dimension|sensory|emotional|social|spirit": "HPF tracks 5 Growth Dimensions: Sensory (body + senses), Cognitive (mind + knowledge), Emotional (feelings + empathy), Social (relationships + community), and Spiritual (purpose + meaning). Every sub-domain maps to one or more.",
    "plateau|stuck|avoid": "When you plateau in a domain, HPF suggests examining your sub-domains. For example, if stuck in Thinking, check if you're using only Logical and missing Creative or Holistic thinking. The Repeating domain is key to breaking plateaus.",
    "ashok|upadhya|creator|author": "HPF was created by Ashok Upadhya, an independent researcher who identified a universal pattern in human behavior after studying scriptures, philosophies, and modern theories across cultures.",
    "daily cycle|practice|routine": "The Daily HPF Cycle guides you through all 7 domains in sequence each day. It takes 10–20 minutes. Personalised by your goal and 6W context, it builds the habit of conscious, purposeful growth.",
    "maslow|ikigai|dharma": "HPF doesn't replace frameworks like Maslow's hierarchy, Ikigai, or Dharma — it integrates and completes them. It provides the structural engine that explains WHY those frameworks work.",
  }
};

function matchKB(q) {
  const lower = q.toLowerCase();
  for (const [keys, answer] of Object.entries(HPF_KB.keywords)) {
    if (keys.split("|").some(k => lower.includes(k))) return answer;
  }
  return null;
}

// ─── MAIN APP ────────────────────────────────────────────────────────────────
export default function HelperApp() {
  const [screen, setScreen] = useState("onboard"); // onboard|profile|goal|context|cycle|dashboard|chat
  const [user, setUser] = useState({ name: "", intent: "" });
  const [baseline, setBaseline] = useState(new Array(14).fill(3));
  const [goal, setGoal] = useState({ text: "", domains: [], timeframe: "21" });
  const [context6W, setContext6W] = useState({ what: "", where: "", when: "", why: "", who: "", how: "" });
  const [cycle, setCycle] = useState({ responses: new Array(7).fill(""), completed: new Array(7).fill(false), openIdx: 0 });
  const [cycles, setCycles] = useState([]);
  const [tasks, setTasks] = useState([]);
  const [taskInput, setTaskInput] = useState("");
  const [taskDomain, setTaskDomain] = useState("");
  const [taskFilter, setTaskFilter] = useState("all");
  const [chatMsgs, setChatMsgs] = useState([
    { role: "bot", text: "Hello! I'm your HPF Coach. Ask me anything about the Human Purpose Framework — or type a domain name like 'Observing' to learn more." }
  ]);
  const [chatInput, setChatInput] = useState("");
  const [chatLoading, setChatLoading] = useState(false);
  const [activeNav, setActiveNav] = useState("cycle");
  const chatEndRef = useRef(null);
  const [profileStep, setProfileStep] = useState(0); // 0=intro,1=assessment,2=done
  const [goalStep, setGoalStep] = useState(0);

  useEffect(() => { chatEndRef.current?.scrollIntoView({ behavior: "smooth" }); }, [chatMsgs]);

  // domain scores from baseline
  const domainScores = DOMAINS.map((_, di) => {
    const q1 = baseline[di * 2], q2 = baseline[di * 2 + 1];
    return Math.round(((q1 + q2) / 2) * 10) / 10;
  });

  const cycleProgress = cycle.completed.filter(Boolean).length;
  const completedCycles = cycles.length;
  const tasksDone = tasks.filter(t => t.done).length;
  const weakestDomain = domainScores.indexOf(Math.min(...domainScores));

  function startApp() {
    if (!user.name.trim()) return;
    setScreen("profile");
    setProfileStep(0);
  }

  function completeCycleDomain(idx) {
    const updated = { ...cycle };
    updated.completed[idx] = true;
    if (idx < 6) updated.openIdx = idx + 1;
    setCycle({ ...updated });
    if (updated.completed.every(Boolean)) {
      setTimeout(() => {
        setCycles(prev => [...prev, { date: new Date().toISOString(), responses: [...cycle.responses], scores: [...domainScores] }]);
        setCycle({ responses: new Array(7).fill(""), completed: new Array(7).fill(false), openIdx: 0 });
      }, 800);
    }
  }

  function addTask() {
    if (!taskInput.trim()) return;
    setTasks(prev => [...prev, { id: Date.now(), text: taskInput, domain: taskDomain, done: false }]);
    setTaskInput(""); setTaskDomain("");
  }

  async function sendChat() {
    const q = chatInput.trim(); if (!q) return;
    setChatInput("");
    setChatMsgs(prev => [...prev, { role: "user", text: q }]);
    setChatLoading(true);
    const local = matchKB(q);
    if (local) {
      setTimeout(() => { setChatMsgs(prev => [...prev, { role: "bot", text: local }]); setChatLoading(false); }, 600);
      return;
    }
    try {
      const res = await fetch("https://api.anthropic.com/v1/messages", {
        method: "POST", headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          model: "claude-sonnet-4-20250514", max_tokens: 1000,
          system: `You are the HPF Coach in the Helperz self-growth app. The Human Purpose Framework (HPF) by Ashok Upadhya states: Human Purpose = observe → understand → think → decide → perform → experience → repeat, over spacetime for growth. Formula: [7D]×[49SD]×6W=5GD. 7 Domains: Observing(PCAREIA), Understanding(CECIARI), Thinking(LCCASAH), Deciding(RIICETS), Performing(ECCASSE), Experiencing(SCESCLT), Repeating(IRACSHP). 5 Growth Dims: Sensory, Cognitive, Emotional, Social, Spiritual. User: ${user.name || "a seeker"}. Their goal: "${goal.text || "personal growth"}". Be warm, specific, under 100 words.`,
          messages: [{ role: "user", content: q }]
        })
      });
      const data = await res.json();
      const reply = data.content?.map(c => c.text || "").join("") || "I couldn't connect. Try again.";
      setChatMsgs(prev => [...prev, { role: "bot", text: reply }]);
    } catch {
      setChatMsgs(prev => [...prev, { role: "bot", text: matchKB(q) || "Connection issue. Ask about a specific HPF domain to get an instant answer." }]);
    }
    setChatLoading(false);
  }

  // ─── SCREENS ──────────────────────────────────────────────────────────────

  if (screen === "onboard") return (
    <div style={{ minHeight: "100vh", background: "var(--color-background-tertiary)", display: "flex", alignItems: "center", justifyContent: "center", padding: "20px" }}>
      <div style={{ maxWidth: 440, width: "100%", background: "var(--color-background-primary)", borderRadius: "var(--border-radius-lg)", border: "0.5px solid var(--color-border-tertiary)", padding: "40px 36px" }}>
        <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 8 }}>
          <div style={{ width: 36, height: 36, borderRadius: 10, background: "#E1F5EE", display: "flex", alignItems: "center", justifyContent: "center" }}>
            <i className="ti ti-plant-2" style={{ color: "#1D9E75", fontSize: 20 }} aria-hidden="true" />
          </div>
          <span style={{ fontSize: 20, fontWeight: 500, color: "var(--color-text-primary)" }}>Helperz</span>
        </div>
        <p style={{ fontSize: 13, color: "var(--color-text-secondary)", marginBottom: 32, lineHeight: 1.6 }}>
          A daily self-growth practice built on the Human Purpose Framework — observe, understand, think, decide, perform, experience, repeat.
        </p>
        <label style={{ fontSize: 12, color: "var(--color-text-secondary)", display: "block", marginBottom: 6 }}>Your name</label>
        <input value={user.name} onChange={e => setUser(u => ({ ...u, name: e.target.value }))}
          placeholder="e.g. Arjun" onKeyDown={e => e.key === "Enter" && startApp()}
          style={{ width: "100%", marginBottom: 12 }} />
        <label style={{ fontSize: 12, color: "var(--color-text-secondary)", display: "block", marginBottom: 6 }}>Why are you here?</label>
        <input value={user.intent} onChange={e => setUser(u => ({ ...u, intent: e.target.value }))}
          placeholder="e.g. I want to grow intentionally every day"
          style={{ width: "100%", marginBottom: 24 }} />
        <button onClick={startApp}
          style={{ width: "100%", padding: "10px", background: "#1D9E75", color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 14, fontFamily: "var(--font-sans)" }}>
          Begin my HPF journey →
        </button>
        <p style={{ fontSize: 11, color: "var(--color-text-tertiary)", textAlign: "center", marginTop: 16 }}>
          Framework by Ashok Upadhya · Helperz V1
        </p>
      </div>
    </div>
  );

  if (screen === "profile") return (
    <div style={{ minHeight: "100vh", background: "var(--color-background-tertiary)", display: "flex", alignItems: "center", justifyContent: "center", padding: "20px" }}>
      <div style={{ maxWidth: 540, width: "100%", background: "var(--color-background-primary)", borderRadius: "var(--border-radius-lg)", border: "0.5px solid var(--color-border-tertiary)", padding: "32px" }}>
        {profileStep === 0 && <>
          <p style={{ fontSize: 11, color: "#1D9E75", fontWeight: 500, marginBottom: 6, textTransform: "uppercase", letterSpacing: "0.06em" }}>Module A · HPF Profile</p>
          <h2 style={{ fontSize: 22, fontWeight: 500, color: "var(--color-text-primary)", marginBottom: 10 }}>Welcome, {user.name}</h2>
          <p style={{ fontSize: 14, color: "var(--color-text-secondary)", lineHeight: 1.65, marginBottom: 28 }}>
            We'll start with a quick baseline — 14 questions across the 7 HPF domains. Rate yourself honestly. This is your starting point, not a judgment.
          </p>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(120px,1fr))", gap: 8, marginBottom: 28 }}>
            {DOMAINS.map(d => (
              <div key={d.id} style={{ background: d.bg, borderRadius: "var(--border-radius-md)", padding: "10px 12px" }}>
                <i className={`ti ${d.icon}`} style={{ color: d.color, fontSize: 18 }} aria-hidden="true" />
                <div style={{ fontSize: 12, fontWeight: 500, color: d.dark, marginTop: 4 }}>{d.name}</div>
              </div>
            ))}
          </div>
          <button onClick={() => setProfileStep(1)}
            style={{ width: "100%", padding: 10, background: "#1D9E75", color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 14, fontFamily: "var(--font-sans)" }}>
            Start baseline assessment →
          </button>
        </>}
        {profileStep === 1 && <>
          <p style={{ fontSize: 11, color: "#1D9E75", fontWeight: 500, marginBottom: 4, textTransform: "uppercase", letterSpacing: "0.06em" }}>Baseline · 14 questions</p>
          <div style={{ height: 3, background: "var(--color-border-tertiary)", borderRadius: 4, marginBottom: 20, overflow: "hidden" }}>
            <div style={{ height: "100%", background: "#1D9E75", width: `${(baseline.filter((v,i) => v !== 3).length / 14 * 100)}%`, transition: "width 0.3s" }} />
          </div>
          <div style={{ display: "flex", flexDirection: "column", gap: 16, maxHeight: 420, overflowY: "auto", paddingRight: 4 }}>
            {ASSESSMENT_Qs.map((q, qi) => {
              const d = DOMAINS[q.domain];
              return (
                <div key={qi} style={{ background: "var(--color-background-secondary)", borderRadius: "var(--border-radius-md)", padding: "12px 14px" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 6, marginBottom: 8 }}>
                    <span style={{ fontSize: 10, background: d.bg, color: d.dark, padding: "2px 8px", borderRadius: 20, fontWeight: 500 }}>{d.name}</span>
                  </div>
                  <p style={{ fontSize: 13, color: "var(--color-text-primary)", marginBottom: 10, lineHeight: 1.5 }}>{q.q}</p>
                  <div style={{ display: "flex", gap: 6 }}>
                    {[1,2,3,4,5].map(v => (
                      <button key={v} onClick={() => setBaseline(prev => { const n=[...prev]; n[qi]=v; return n; })}
                        style={{ flex: 1, padding: "6px 0", borderRadius: "var(--border-radius-md)", border: `0.5px solid ${baseline[qi]===v ? d.color : "var(--color-border-secondary)"}`, background: baseline[qi]===v ? d.bg : "var(--color-background-primary)", color: baseline[qi]===v ? d.dark : "var(--color-text-secondary)", cursor: "pointer", fontSize: 13, fontFamily: "var(--font-sans)", fontWeight: baseline[qi]===v ? 500 : 400 }}>
                        {v}
                      </button>
                    ))}
                  </div>
                  <div style={{ display: "flex", justifyContent: "space-between", marginTop: 4 }}>
                    <span style={{ fontSize: 10, color: "var(--color-text-tertiary)" }}>Rarely</span>
                    <span style={{ fontSize: 10, color: "var(--color-text-tertiary)" }}>Always</span>
                  </div>
                </div>
              );
            })}
          </div>
          <button onClick={() => { setScreen("goal"); setGoalStep(0); }}
            style={{ width: "100%", marginTop: 16, padding: 10, background: "#1D9E75", color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 14, fontFamily: "var(--font-sans)" }}>
            Save baseline & set my goal →
          </button>
        </>}
      </div>
    </div>
  );

  if (screen === "goal") return (
    <div style={{ minHeight: "100vh", background: "var(--color-background-tertiary)", display: "flex", alignItems: "center", justifyContent: "center", padding: "20px" }}>
      <div style={{ maxWidth: 500, width: "100%", background: "var(--color-background-primary)", borderRadius: "var(--border-radius-lg)", border: "0.5px solid var(--color-border-tertiary)", padding: "32px" }}>
        <p style={{ fontSize: 11, color: "#534AB7", fontWeight: 500, marginBottom: 6, textTransform: "uppercase", letterSpacing: "0.06em" }}>Module B · Goal Creation</p>
        <h2 style={{ fontSize: 20, fontWeight: 500, marginBottom: 8, color: "var(--color-text-primary)" }}>What do you want to grow?</h2>
        <p style={{ fontSize: 13, color: "var(--color-text-secondary)", marginBottom: 22, lineHeight: 1.6 }}>Your growth goal anchors every daily cycle. Be specific — your HPF Coach will use this to personalise your prompts.</p>
        <label style={{ fontSize: 12, color: "var(--color-text-secondary)", display: "block", marginBottom: 6 }}>My growth goal</label>
        <textarea value={goal.text} onChange={e => setGoal(g => ({ ...g, text: e.target.value }))}
          placeholder="e.g. Develop deeper self-awareness and make more intentional decisions at work"
          style={{ width: "100%", minHeight: 80, borderRadius: "var(--border-radius-md)", border: "0.5px solid var(--color-border-secondary)", padding: "8px 10px", fontSize: 13, fontFamily: "var(--font-sans)", color: "var(--color-text-primary)", background: "var(--color-background-primary)", resize: "none", marginBottom: 18 }} />
        <label style={{ fontSize: 12, color: "var(--color-text-secondary)", display: "block", marginBottom: 8 }}>Related HPF domains (select all that apply)</label>
        <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginBottom: 20 }}>
          {DOMAINS.map(d => (
            <button key={d.id} onClick={() => setGoal(g => ({ ...g, domains: g.domains.includes(d.id) ? g.domains.filter(x => x !== d.id) : [...g.domains, d.id] }))}
              style={{ padding: "5px 12px", borderRadius: 20, border: `0.5px solid ${goal.domains.includes(d.id) ? d.color : "var(--color-border-secondary)"}`, background: goal.domains.includes(d.id) ? d.bg : "var(--color-background-primary)", color: goal.domains.includes(d.id) ? d.dark : "var(--color-text-secondary)", cursor: "pointer", fontSize: 12, fontFamily: "var(--font-sans)", fontWeight: goal.domains.includes(d.id) ? 500 : 400 }}>
              {d.name}
            </button>
          ))}
        </div>
        <label style={{ fontSize: 12, color: "var(--color-text-secondary)", display: "block", marginBottom: 8 }}>Timeframe</label>
        <div style={{ display: "flex", gap: 8, marginBottom: 24 }}>
          {["21","42","90"].map(t => (
            <button key={t} onClick={() => setGoal(g => ({ ...g, timeframe: t }))}
              style={{ flex: 1, padding: "8px 0", borderRadius: "var(--border-radius-md)", border: `0.5px solid ${goal.timeframe === t ? "#1D9E75" : "var(--color-border-secondary)"}`, background: goal.timeframe === t ? "#E1F5EE" : "var(--color-background-primary)", color: goal.timeframe === t ? "#0F6E56" : "var(--color-text-secondary)", cursor: "pointer", fontSize: 13, fontFamily: "var(--font-sans)", fontWeight: goal.timeframe === t ? 500 : 400 }}>
              {t} days
            </button>
          ))}
        </div>
        <button onClick={() => setScreen("context")}
          style={{ width: "100%", padding: 10, background: "#1D9E75", color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 14, fontFamily: "var(--font-sans)" }}>
          Set context & begin →
        </button>
      </div>
    </div>
  );

  if (screen === "context") return (
    <div style={{ minHeight: "100vh", background: "var(--color-background-tertiary)", display: "flex", alignItems: "center", justifyContent: "center", padding: "20px" }}>
      <div style={{ maxWidth: 500, width: "100%", background: "var(--color-background-primary)", borderRadius: "var(--border-radius-lg)", border: "0.5px solid var(--color-border-tertiary)", padding: "32px" }}>
        <p style={{ fontSize: 11, color: "#BA7517", fontWeight: 500, marginBottom: 6, textTransform: "uppercase", letterSpacing: "0.06em" }}>Module C · 6W Context</p>
        <h2 style={{ fontSize: 20, fontWeight: 500, marginBottom: 8, color: "var(--color-text-primary)" }}>Set today's context</h2>
        <p style={{ fontSize: 13, color: "var(--color-text-secondary)", marginBottom: 22, lineHeight: 1.6 }}>
          These 6 dimensions anchor your daily cycle to real life. Quick answers are fine — they personalise your prompts.
        </p>
        <div style={{ display: "flex", flexDirection: "column", gap: 12, marginBottom: 24 }}>
          {[["what","What situation or challenge are you focusing on today?","ti-focus-2"],
            ["where","Where are you doing this practice?","ti-map-pin"],
            ["when","What time / stage of your day is this?","ti-clock"],
            ["why","What is your deeper intention right now?","ti-heart"],
            ["who","Who else is involved in your situation?","ti-users"],
            ["how","How do you want to approach today's cycle?","ti-adjustments"]
          ].map(([key, placeholder, icon]) => (
            <div key={key} style={{ display: "flex", alignItems: "center", gap: 10 }}>
              <div style={{ width: 32, height: 32, borderRadius: "var(--border-radius-md)", background: "#FAEEDA", display: "flex", alignItems: "center", justifyContent: "center", flexShrink: 0 }}>
                <i className={`ti ${icon}`} style={{ color: "#BA7517", fontSize: 15 }} aria-hidden="true" />
              </div>
              <div style={{ flex: 1 }}>
                <span style={{ fontSize: 10, fontWeight: 500, color: "#BA7517", textTransform: "uppercase", letterSpacing: "0.06em" }}>{key.toUpperCase()}</span>
                <input value={context6W[key]} onChange={e => setContext6W(c => ({ ...c, [key]: e.target.value }))}
                  placeholder={placeholder} style={{ width: "100%", marginTop: 3, fontSize: 13 }} />
              </div>
            </div>
          ))}
        </div>
        <button onClick={() => { setScreen("app"); setActiveNav("cycle"); }}
          style={{ width: "100%", padding: 10, background: "#1D9E75", color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 14, fontFamily: "var(--font-sans)" }}>
          Start today's cycle →
        </button>
        <button onClick={() => { setScreen("app"); setActiveNav("cycle"); }}
          style={{ width: "100%", marginTop: 8, padding: 10, background: "none", color: "var(--color-text-secondary)", border: "0.5px solid var(--color-border-secondary)", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 13, fontFamily: "var(--font-sans)" }}>
          Skip for now
        </button>
      </div>
    </div>
  );

  // ─── MAIN APP SHELL ────────────────────────────────────────────────────────
  const navItems = [
    { id: "cycle", label: "Daily Cycle", icon: "ti-refresh" },
    { id: "tasks", label: "Tasks", icon: "ti-checkbox" },
    { id: "dashboard", label: "Dashboard", icon: "ti-chart-bar" },
    { id: "chat", label: "HPF Coach", icon: "ti-message-circle" },
  ];

  const filteredTasks = tasks.filter(t => taskFilter === "all" ? true : taskFilter === "done" ? t.done : !t.done);
  const pendingTasks = tasks.filter(t => !t.done).length;

  return (
    <div style={{ display: "flex", height: "100vh", background: "var(--color-background-tertiary)", fontFamily: "var(--font-sans)" }}>

      {/* SIDEBAR */}
      <div style={{ width: 220, flexShrink: 0, background: "var(--color-background-primary)", borderRight: "0.5px solid var(--color-border-tertiary)", display: "flex", flexDirection: "column" }}>
        <div style={{ padding: "20px 16px 14px", borderBottom: "0.5px solid var(--color-border-tertiary)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 10 }}>
            <div style={{ width: 30, height: 30, borderRadius: 8, background: "#E1F5EE", display: "flex", alignItems: "center", justifyContent: "center" }}>
              <i className="ti ti-plant-2" style={{ color: "#1D9E75", fontSize: 16 }} aria-hidden="true" />
            </div>
            <span style={{ fontSize: 15, fontWeight: 500, color: "var(--color-text-primary)" }}>Helperz</span>
          </div>
          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <div style={{ width: 28, height: 28, borderRadius: "50%", background: "#E1F5EE", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 12, fontWeight: 500, color: "#0F6E56" }}>
              {user.name.charAt(0).toUpperCase()}
            </div>
            <div>
              <div style={{ fontSize: 13, fontWeight: 500, color: "var(--color-text-primary)" }}>{user.name}</div>
              <div style={{ fontSize: 10, color: "var(--color-text-tertiary)" }}>Day {completedCycles + 1} · {goal.timeframe || 21}-day journey</div>
            </div>
          </div>
        </div>

        <nav style={{ flex: 1, padding: "10px 8px", display: "flex", flexDirection: "column", gap: 2 }}>
          <div style={{ fontSize: 10, letterSpacing: "0.08em", textTransform: "uppercase", color: "var(--color-text-tertiary)", padding: "8px 10px 4px", fontWeight: 500 }}>Practice</div>
          {navItems.map(n => (
            <button key={n.id} onClick={() => setActiveNav(n.id)}
              style={{ display: "flex", alignItems: "center", gap: 10, padding: "8px 10px", borderRadius: "var(--border-radius-md)", border: "none", background: activeNav === n.id ? "#E1F5EE" : "none", cursor: "pointer", width: "100%", textAlign: "left", fontSize: 13.5, color: activeNav === n.id ? "#0F6E56" : "var(--color-text-secondary)", fontWeight: activeNav === n.id ? 500 : 400, fontFamily: "var(--font-sans)", transition: "background 0.15s" }}>
              <i className={`ti ${n.icon}`} style={{ fontSize: 16 }} aria-hidden="true" />
              {n.label}
              {n.id === "tasks" && pendingTasks > 0 && <span style={{ marginLeft: "auto", fontSize: 10, background: "#1D9E75", color: "#fff", borderRadius: 10, padding: "1px 6px" }}>{pendingTasks}</span>}
            </button>
          ))}
          <div style={{ fontSize: 10, letterSpacing: "0.08em", textTransform: "uppercase", color: "var(--color-text-tertiary)", padding: "12px 10px 4px", fontWeight: 500 }}>Setup</div>
          <button onClick={() => setScreen("context")}
            style={{ display: "flex", alignItems: "center", gap: 10, padding: "8px 10px", borderRadius: "var(--border-radius-md)", border: "none", background: "none", cursor: "pointer", width: "100%", textAlign: "left", fontSize: 13.5, color: "var(--color-text-secondary)", fontFamily: "var(--font-sans)" }}>
            <i className="ti ti-adjustments" style={{ fontSize: 16 }} aria-hidden="true" /> 6W Context
          </button>
          <button onClick={() => setScreen("goal")}
            style={{ display: "flex", alignItems: "center", gap: 10, padding: "8px 10px", borderRadius: "var(--border-radius-md)", border: "none", background: "none", cursor: "pointer", width: "100%", textAlign: "left", fontSize: 13.5, color: "var(--color-text-secondary)", fontFamily: "var(--font-sans)" }}>
            <i className="ti ti-target" style={{ fontSize: 16 }} aria-hidden="true" /> My Goal
          </button>
        </nav>

        {/* streak pill */}
        <div style={{ padding: "12px 14px", borderTop: "0.5px solid var(--color-border-tertiary)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8, background: "#E1F5EE", borderRadius: "var(--border-radius-md)", padding: "8px 10px" }}>
            <i className="ti ti-flame" style={{ color: "#1D9E75", fontSize: 18 }} aria-hidden="true" />
            <div>
              <div style={{ fontSize: 13, fontWeight: 500, color: "#0F6E56" }}>{completedCycles} cycles done</div>
              <div style={{ fontSize: 10, color: "#1D9E75" }}>{Math.max(0, (goal.timeframe || 21) - completedCycles)} days remaining</div>
            </div>
          </div>
        </div>
      </div>

      {/* MAIN CONTENT */}
      <div style={{ flex: 1, display: "flex", flexDirection: "column", minWidth: 0, overflow: "hidden" }}>

        {/* ── DAILY CYCLE ── */}
        {activeNav === "cycle" && (
          <div style={{ flex: 1, display: "flex", flexDirection: "column", overflow: "hidden" }}>
            <div style={{ padding: "14px 22px", borderBottom: "0.5px solid var(--color-border-tertiary)", background: "var(--color-background-primary)", display: "flex", alignItems: "center", justifyContent: "space-between", flexShrink: 0 }}>
              <div>
                <h2 style={{ fontSize: 15, fontWeight: 500, color: "var(--color-text-primary)", margin: 0 }}>Daily HPF Cycle</h2>
                <div style={{ fontSize: 11, color: "var(--color-text-secondary)", marginTop: 2 }}>
                  {new Date().toLocaleDateString("en-US", { weekday: "long", month: "long", day: "numeric" })}
                  {goal.text && <span style={{ marginLeft: 8, color: "#1D9E75" }}>· {goal.text.slice(0,40)}{goal.text.length > 40 ? "…" : ""}</span>}
                </div>
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                <div style={{ fontSize: 12, color: "var(--color-text-secondary)" }}>{cycleProgress}/7</div>
                <div style={{ width: 100, height: 4, background: "var(--color-border-tertiary)", borderRadius: 4, overflow: "hidden" }}>
                  <div style={{ height: "100%", background: "#1D9E75", width: `${cycleProgress / 7 * 100}%`, transition: "width 0.4s" }} />
                </div>
              </div>
            </div>
            <div style={{ flex: 1, overflowY: "auto", padding: "16px 22px", display: "flex", flexDirection: "column", gap: 8 }}>
              {DOMAINS.map((d, i) => {
                const isDone = cycle.completed[i];
                const isOpen = cycle.openIdx === i || (!isDone && i === 0 && cycleProgress === 0);
                const isCurrent = !isDone && (i === 0 || cycle.completed[i - 1]);
                return (
                  <div key={d.id} style={{ background: "var(--color-background-primary)", border: `0.5px solid ${isCurrent && !isDone ? d.color : "var(--color-border-tertiary)"}`, borderRadius: "var(--border-radius-lg)", overflow: "hidden", opacity: isDone ? 0.72 : 1 }}>
                    <div onClick={() => !isDone && setCycle(c => ({ ...c, openIdx: c.openIdx === i ? -1 : i }))}
                      style={{ display: "flex", alignItems: "center", gap: 12, padding: "12px 14px", cursor: isDone ? "default" : "pointer" }}>
                      <div style={{ width: 32, height: 32, borderRadius: "50%", background: isDone ? d.bg : isCurrent ? d.color : "var(--color-background-secondary)", display: "flex", alignItems: "center", justifyContent: "center", flexShrink: 0 }}>
                        {isDone
                          ? <i className="ti ti-check" style={{ color: d.dark, fontSize: 15 }} aria-hidden="true" />
                          : <i className={`ti ${d.icon}`} style={{ color: isCurrent ? "#fff" : "var(--color-text-tertiary)", fontSize: 15 }} aria-hidden="true" />}
                      </div>
                      <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 13.5, fontWeight: 500, color: "var(--color-text-primary)" }}>{d.name}</div>
                        <div style={{ fontSize: 11, color: "var(--color-text-secondary)", marginTop: 1 }}>{d.acro} · {d.growth.join(" + ")}</div>
                      </div>
                      {!isDone && <i className={`ti ti-chevron-${isOpen ? "up" : "down"}`} style={{ fontSize: 15, color: "var(--color-text-tertiary)" }} aria-hidden="true" />}
                      {isDone && <i className="ti ti-circle-check" style={{ color: d.color, fontSize: 18 }} aria-hidden="true" />}
                    </div>
                    {(isOpen && !isDone) && (
                      <div style={{ padding: "0 14px 14px", borderTop: "0.5px solid var(--color-border-tertiary)" }}>
                        <p style={{ fontSize: 13, color: "var(--color-text-secondary)", margin: "10px 0 4px", lineHeight: 1.6, fontStyle: "italic" }}>"{d.prompt}"</p>
                        {/* inline KB tip */}
                        <div style={{ background: d.bg, borderRadius: "var(--border-radius-md)", padding: "8px 12px", marginBottom: 10, display: "flex", gap: 8, alignItems: "flex-start" }}>
                          <i className="ti ti-info-circle" style={{ color: d.color, fontSize: 14, flexShrink: 0, marginTop: 1 }} aria-hidden="true" />
                          <div>
                            <div style={{ fontSize: 11, fontWeight: 500, color: d.dark, marginBottom: 2 }}>Sub-domains: {d.subdomains.slice(0,4).join(", ")}…</div>
                            <div style={{ fontSize: 11, color: d.dark }}>{d.tip}</div>
                          </div>
                        </div>
                        <textarea value={cycle.responses[i]}
                          onChange={e => setCycle(c => { const r = [...c.responses]; r[i] = e.target.value; return { ...c, responses: r }; })}
                          placeholder="Write your reflection here…"
                          style={{ width: "100%", minHeight: 72, borderRadius: "var(--border-radius-md)", border: "0.5px solid var(--color-border-secondary)", padding: "8px 10px", fontSize: 13, fontFamily: "var(--font-sans)", color: "var(--color-text-primary)", background: "var(--color-background-primary)", resize: "none" }} />
                        <button onClick={() => completeCycleDomain(i)}
                          style={{ marginTop: 8, padding: "6px 16px", background: d.color, color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 13, fontFamily: "var(--font-sans)" }}>
                          Complete {d.name} →
                        </button>
                      </div>
                    )}
                  </div>
                );
              })}
              {cycleProgress === 7 && (
                <div style={{ background: "#E1F5EE", border: "0.5px solid #1D9E75", borderRadius: "var(--border-radius-lg)", padding: "20px", textAlign: "center" }}>
                  <i className="ti ti-confetti" style={{ color: "#1D9E75", fontSize: 32, display: "block", marginBottom: 8 }} aria-hidden="true" />
                  <div style={{ fontSize: 15, fontWeight: 500, color: "#0F6E56" }}>Cycle complete!</div>
                  <div style={{ fontSize: 13, color: "#1D9E75", marginTop: 4 }}>You've completed all 7 domains. Well done, {user.name}.</div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* ── TASKS ── */}
        {activeNav === "tasks" && (
          <div style={{ flex: 1, display: "flex", flexDirection: "column", overflow: "hidden" }}>
            <div style={{ padding: "14px 22px", borderBottom: "0.5px solid var(--color-border-tertiary)", background: "var(--color-background-primary)", display: "flex", alignItems: "center", justifyContent: "space-between", flexShrink: 0 }}>
              <h2 style={{ fontSize: 15, fontWeight: 500, color: "var(--color-text-primary)", margin: 0 }}>Task Tracker</h2>
              <span style={{ fontSize: 12, color: "var(--color-text-secondary)" }}>{pendingTasks} pending</span>
            </div>
            <div style={{ flex: 1, overflowY: "auto", padding: "16px 22px" }}>
              <div style={{ display: "flex", gap: 8, marginBottom: 14 }}>
                <input value={taskInput} onChange={e => setTaskInput(e.target.value)} onKeyDown={e => e.key === "Enter" && addTask()}
                  placeholder="Add a task…" style={{ flex: 1 }} />
                <select value={taskDomain} onChange={e => setTaskDomain(e.target.value)}
                  style={{ width: 140, fontSize: 13 }}>
                  <option value="">No domain</option>
                  {DOMAINS.map(d => <option key={d.id} value={d.name}>{d.name}</option>)}
                </select>
                <button onClick={addTask}
                  style={{ padding: "7px 14px", background: "#1D9E75", color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 13, fontFamily: "var(--font-sans)", whiteSpace: "nowrap" }}>
                  <i className="ti ti-plus" aria-hidden="true" /> Add
                </button>
              </div>
              <div style={{ display: "flex", gap: 6, marginBottom: 14 }}>
                {["all","pending","done"].map(f => (
                  <button key={f} onClick={() => setTaskFilter(f)}
                    style={{ padding: "4px 14px", borderRadius: 20, border: `0.5px solid ${taskFilter === f ? "#1D9E75" : "var(--color-border-secondary)"}`, background: taskFilter === f ? "#E1F5EE" : "none", color: taskFilter === f ? "#0F6E56" : "var(--color-text-secondary)", cursor: "pointer", fontSize: 12, fontFamily: "var(--font-sans)", fontWeight: taskFilter === f ? 500 : 400, textTransform: "capitalize" }}>
                    {f}
                  </button>
                ))}
              </div>
              {filteredTasks.length === 0
                ? <div style={{ textAlign: "center", padding: "40px 0", color: "var(--color-text-secondary)", fontSize: 13 }}>
                    <i className="ti ti-checkbox" style={{ fontSize: 32, display: "block", marginBottom: 8 }} aria-hidden="true" />
                    No {taskFilter === "all" ? "" : taskFilter} tasks yet
                  </div>
                : <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                    {filteredTasks.map(t => {
                      const dom = DOMAINS.find(d => d.name === t.domain);
                      return (
                        <div key={t.id} style={{ background: "var(--color-background-primary)", border: "0.5px solid var(--color-border-tertiary)", borderRadius: "var(--border-radius-md)", padding: "10px 12px", display: "flex", alignItems: "center", gap: 10, opacity: t.done ? 0.6 : 1 }}>
                          <input type="checkbox" checked={t.done} onChange={() => setTasks(prev => prev.map(x => x.id === t.id ? { ...x, done: !x.done } : x))}
                            style={{ accentColor: "#1D9E75", width: 15, height: 15, cursor: "pointer", flexShrink: 0 }} />
                          <span style={{ flex: 1, fontSize: 13, color: "var(--color-text-primary)", textDecoration: t.done ? "line-through" : "none" }}>{t.text}</span>
                          {dom && <span style={{ fontSize: 11, padding: "2px 8px", borderRadius: 20, background: dom.bg, color: dom.dark, fontWeight: 500, whiteSpace: "nowrap" }}>{dom.name}</span>}
                          <button onClick={() => setTasks(prev => prev.filter(x => x.id !== t.id))}
                            style={{ background: "none", border: "none", cursor: "pointer", color: "var(--color-text-tertiary)", fontSize: 15, padding: 2 }}>
                            <i className="ti ti-x" aria-hidden="true" />
                          </button>
                        </div>
                      );
                    })}
                  </div>
              }
            </div>
          </div>
        )}

        {/* ── DASHBOARD ── */}
        {activeNav === "dashboard" && (
          <div style={{ flex: 1, display: "flex", flexDirection: "column", overflow: "hidden" }}>
            <div style={{ padding: "14px 22px", borderBottom: "0.5px solid var(--color-border-tertiary)", background: "var(--color-background-primary)", flexShrink: 0 }}>
              <h2 style={{ fontSize: 15, fontWeight: 500, color: "var(--color-text-primary)", margin: 0 }}>Growth Dashboard</h2>
              <span style={{ fontSize: 11, color: "var(--color-text-secondary)" }}>Module E · {goal.timeframe || 21}-day journey</span>
            </div>
            <div style={{ flex: 1, overflowY: "auto", padding: "16px 22px" }}>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(110px,1fr))", gap: 10, marginBottom: 20 }}>
                {[
                  { label: "Cycles done", val: completedCycles, sub: `of ${goal.timeframe || 21} goal` },
                  { label: "Today's progress", val: `${cycleProgress}/7`, sub: "domains" },
                  { label: "Tasks done", val: tasksDone, sub: "completed" },
                  { label: "Growth edge", val: DOMAINS[weakestDomain].name.slice(0,10), sub: "focus domain" },
                ].map(m => (
                  <div key={m.label} style={{ background: "var(--color-background-secondary)", borderRadius: "var(--border-radius-md)", padding: "12px" }}>
                    <div style={{ fontSize: 11, color: "var(--color-text-secondary)", marginBottom: 4 }}>{m.label}</div>
                    <div style={{ fontSize: 22, fontWeight: 500, color: "var(--color-text-primary)" }}>{m.val}</div>
                    <div style={{ fontSize: 11, color: "var(--color-text-tertiary)", marginTop: 2 }}>{m.sub}</div>
                  </div>
                ))}
              </div>

              <div style={{ fontSize: 13, fontWeight: 500, color: "var(--color-text-primary)", marginBottom: 10 }}>Domain baseline scores</div>
              <div style={{ display: "flex", flexDirection: "column", gap: 8, marginBottom: 22 }}>
                {DOMAINS.map((d, i) => (
                  <div key={d.id} style={{ display: "flex", alignItems: "center", gap: 10 }}>
                    <div style={{ fontSize: 12, color: "var(--color-text-secondary)", width: 88, flexShrink: 0 }}>{d.name}</div>
                    <div style={{ flex: 1, height: 6, background: "var(--color-border-tertiary)", borderRadius: 4, overflow: "hidden" }}>
                      <div style={{ height: "100%", background: d.color, borderRadius: 4, width: `${domainScores[i] / 5 * 100}%`, transition: "width 0.6s" }} />
                    </div>
                    <div style={{ fontSize: 12, color: "var(--color-text-secondary)", width: 28, textAlign: "right" }}>{domainScores[i].toFixed(1)}</div>
                  </div>
                ))}
              </div>

              <div style={{ fontSize: 13, fontWeight: 500, color: "var(--color-text-primary)", marginBottom: 10 }}>My goal</div>
              {goal.text
                ? <div style={{ background: "#E1F5EE", borderRadius: "var(--border-radius-md)", padding: "12px 14px", fontSize: 13, color: "#0F6E56", lineHeight: 1.6, marginBottom: 16 }}>
                    {goal.text}
                    {goal.domains.length > 0 && <div style={{ marginTop: 8, display: "flex", flexWrap: "wrap", gap: 4 }}>
                      {goal.domains.map(gd => { const dom = DOMAINS.find(d => d.id === gd); return dom ? <span key={gd} style={{ fontSize: 11, background: dom.bg, color: dom.dark, padding: "2px 8px", borderRadius: 20, fontWeight: 500 }}>{dom.name}</span> : null; })}
                    </div>}
                  </div>
                : <div style={{ fontSize: 13, color: "var(--color-text-secondary)" }}>No goal set yet. <button onClick={() => setScreen("goal")} style={{ color: "#1D9E75", background: "none", border: "none", cursor: "pointer", fontSize: 13, padding: 0, fontFamily: "var(--font-sans)" }}>Set one →</button></div>
              }

              <div style={{ fontSize: 13, fontWeight: 500, color: "var(--color-text-primary)", marginBottom: 10 }}>5 Growth Dimensions</div>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(100px,1fr))", gap: 8 }}>
                {GROWTH_DIMS.map(gd => {
                  const relatedDomains = DOMAINS.filter(d => d.growth.includes(gd));
                  const avgScore = relatedDomains.reduce((s, d) => s + domainScores[DOMAINS.indexOf(d)], 0) / relatedDomains.length;
                  return (
                    <div key={gd} style={{ background: "var(--color-background-secondary)", borderRadius: "var(--border-radius-md)", padding: "10px 12px", textAlign: "center" }}>
                      <div style={{ fontSize: 11, color: "var(--color-text-secondary)", marginBottom: 4 }}>{gd}</div>
                      <div style={{ fontSize: 20, fontWeight: 500, color: "var(--color-text-primary)" }}>{avgScore.toFixed(1)}</div>
                      <div style={{ fontSize: 10, color: "var(--color-text-tertiary)" }}>/ 5.0</div>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        )}

        {/* ── HPF COACH CHAT ── */}
        {activeNav === "chat" && (
          <div style={{ flex: 1, display: "flex", flexDirection: "column", overflow: "hidden" }}>
            <div style={{ padding: "14px 22px", borderBottom: "0.5px solid var(--color-border-tertiary)", background: "var(--color-background-primary)", flexShrink: 0, display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <h2 style={{ fontSize: 15, fontWeight: 500, color: "var(--color-text-primary)", margin: 0 }}>HPF Coach</h2>
              <span style={{ fontSize: 12, color: "#1D9E75", display: "flex", alignItems: "center", gap: 4 }}>
                <i className="ti ti-circle-filled" style={{ fontSize: 8 }} aria-hidden="true" /> Online
              </span>
            </div>
            <div style={{ flex: 1, overflowY: "auto", padding: "14px 18px", display: "flex", flexDirection: "column", gap: 10 }}>
              {chatMsgs.map((m, i) => (
                <div key={i} style={{ display: "flex", flexDirection: "column", alignItems: m.role === "user" ? "flex-end" : "flex-start", maxWidth: "82%", alignSelf: m.role === "user" ? "flex-end" : "flex-start", gap: 3 }}>
                  <div style={{ padding: "9px 13px", borderRadius: 14, fontSize: 13, lineHeight: 1.6, background: m.role === "user" ? "#1D9E75" : "var(--color-background-primary)", color: m.role === "user" ? "#fff" : "var(--color-text-primary)", border: m.role === "bot" ? "0.5px solid var(--color-border-tertiary)" : "none", borderBottomRightRadius: m.role === "user" ? 4 : 14, borderBottomLeftRadius: m.role === "bot" ? 4 : 14 }}>
                    {m.text.split("\n").map((line, li) => <span key={li}>{line}{li < m.text.split("\n").length - 1 && <br />}</span>)}
                  </div>
                </div>
              ))}
              {chatLoading && (
                <div style={{ display: "flex", gap: 4, padding: "10px 13px", background: "var(--color-background-primary)", border: "0.5px solid var(--color-border-tertiary)", borderRadius: 14, borderBottomLeftRadius: 4, width: "fit-content" }}>
                  {[0, 0.2, 0.4].map((delay, i) => (
                    <div key={i} style={{ width: 6, height: 6, borderRadius: "50%", background: "var(--color-text-tertiary)", animation: `bounce 1.2s ${delay}s infinite` }} />
                  ))}
                </div>
              )}
              <div ref={chatEndRef} />
            </div>
            <div style={{ padding: "6px 14px 8px", display: "flex", flexWrap: "wrap", gap: 6 }}>
              {["What are the 7 domains?","How does HPF help me grow?","What is the 6W model?","Explain Repeating"].map(chip => (
                <button key={chip} onClick={() => { setChatInput(chip); }}
                  style={{ fontSize: 11.5, padding: "4px 10px", borderRadius: 20, border: "0.5px solid var(--color-border-secondary)", background: "var(--color-background-secondary)", color: "var(--color-text-secondary)", cursor: "pointer", fontFamily: "var(--font-sans)" }}>
                  {chip}
                </button>
              ))}
            </div>
            <div style={{ padding: "8px 14px 12px", borderTop: "0.5px solid var(--color-border-tertiary)", background: "var(--color-background-primary)", display: "flex", gap: 8, alignItems: "flex-end", flexShrink: 0 }}>
              <textarea value={chatInput} onChange={e => setChatInput(e.target.value)}
                onKeyDown={e => { if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); sendChat(); } }}
                placeholder="Ask anything about HPF…" rows={1}
                style={{ flex: 1, borderRadius: "var(--border-radius-md)", border: "0.5px solid var(--color-border-secondary)", padding: "8px 10px", fontSize: 13, fontFamily: "var(--font-sans)", color: "var(--color-text-primary)", background: "var(--color-background-primary)", resize: "none", maxHeight: 100 }} />
              <button onClick={sendChat}
                style={{ padding: "8px 14px", background: "#1D9E75", color: "#fff", border: "none", borderRadius: "var(--border-radius-md)", cursor: "pointer", fontSize: 14, flexShrink: 0 }}>
                <i className="ti ti-send" aria-hidden="true" />
              </button>
            </div>
          </div>
        )}
      </div>

      <style>{`@keyframes bounce{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-5px)}}`}</style>
    </div>
  );
}
