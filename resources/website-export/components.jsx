/* website/components.jsx — primitives, nav, hero, stats, products, logo strip */

function Icon({ name, size = 20, stroke = 1.75 }) {
  const s = { width: size, height: size, fill: "none", stroke: "currentColor", strokeWidth: stroke, strokeLinecap: "round", strokeLinejoin: "round" };
  const P = {
    arrow: <path d="M5 12h14M13 5l7 7-7 7"/>,
    check: <polyline points="20 6 9 17 4 12"/>,
    chevron: <path d="M6 9l6 6 6-6"/>,
    users: <><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></>,
    book: <><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></>,
    grad: <><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></>,
    child: <><circle cx="12" cy="6" r="3"/><path d="M6 21v-2a6 6 0 0 1 12 0v2M9 14l-2 7M15 14l2 7"/></>,
    clipboard: <><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></>,
    calendar: <><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></>,
    credit: <><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20M6 15h4"/></>,
    megaphone: <><path d="M3 11v2a2 2 0 0 0 2 2h2l8 5V4L7 9H5a2 2 0 0 0-2 2z"/></>,
    bio: <><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6v6H9z"/></>,
    lib: <><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M10 6v11M14 6v11"/></>,
    bus: <><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 12h18M7 18v2M17 18v2"/><circle cx="8" cy="15" r="1"/><circle cx="16" cy="15" r="1"/></>,
    shield: <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>,
    cloud: <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>,
    cpu: <><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/></>,
    mail: <><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/></>,
    phone: <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92z"/>,
    pin: <><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></>,
    linkedin: <><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></>,
    twitter: <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>,
    facebook: <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>,
    yt: <><path d="M22.54 6.42A2.78 2.78 0 0 0 20.6 4.5C18.88 4 12 4 12 4s-6.88 0-8.6.46A2.78 2.78 0 0 0 1.46 6.42 29.94 29.94 0 0 0 1 11.75a29.94 29.94 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19a2.78 2.78 0 0 0 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-1.92 29.94 29.94 0 0 0 .46-5.33 29.94 29.94 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></>,
    menu: <><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></>,
    play: <polygon points="5 3 19 12 5 21 5 3"/>,
    x: <><path d="M18 6 6 18M6 6l12 12"/></>,
  };
  return <svg viewBox="0 0 24 24" {...s}>{P[name] || null}</svg>;
}

/* ---------- Mini Dashboard mock (used in hero + feature blocks) ---------- */
function MiniDash({ product = "schools" }) {
  const navItems = {
    schools: [["Dashboard", true], ["Students"], ["Report cards"], ["Attendance"], ["Fees"]],
    collegiate: [["Registrar", true], ["Programs"], ["Transcripts"], ["Timetable"], ["Finance"]],
    k12: [["Overview", true], ["Classrooms"], ["Parents"], ["Timetable"], ["Library"]],
  }[product];
  const stats = {
    schools: [["1,248", "Active students", "+34"], ["96.4%", "Attendance", "+1.2%"], ["BWP 412k", "Fees collected", "78%"]],
    collegiate: [["4,820", "Enrolled", "+214"], ["312", "Courses", "28 new"], ["BWP 38.4M", "Invoiced", "72%"]],
    k12: [["842", "Learners", "+18"], ["97.1%", "Attendance", "+0.8%"], ["BWP 198k", "Fees", "81%"]],
  }[product];
  const rows = {
    schools: [["Boitumelo Mosadi", "3R", "Paid", "ok"], ["Atang Nkhata", "1S", "Due", "warn"], ["Lesedi Moeti", "2R", "Paid", "ok"], ["Kago Tshekiso", "4A", "Overdue", "danger"]],
    collegiate: [["Boitumelo Mosadi", "BSC-COMP", "Paid", "ok"], ["Atang Nkhata", "BENG-CIV", "Partial", "warn"], ["Lesedi Moeti", "BCOM-ACC", "Paid", "ok"], ["Kago Tshekiso", "LLB", "Overdue", "danger"]],
    k12: [["Kea Tlotleng", "Grade 4", "Paid", "ok"], ["Nkosi Dube", "Grade 5", "Paid", "ok"], ["Pako Lekgowa", "Grade 3", "Due", "warn"], ["Mpho Seretse", "Grade 4", "Paid", "ok"]],
  }[product];
  return (
    <div className="mini-dash">
      <div className="mini-side">
        <div className="mini-side-brand">
          <div className="mini-tile"/>
          <b>Heritage <span>{product === "collegiate" ? "Col." : "Pro"}</span></b>
        </div>
        {navItems.map(([label, active], i) => (
          <div key={i} className={`mini-nav-item ${active ? "active" : ""}`}>
            <span className="dot"/>{label}
          </div>
        ))}
      </div>
      <div className="mini-main">
        <div className="mini-h">{product === "collegiate" ? "Registrar overview" : "Dashboard overview"}</div>
        <div className="mini-stats">
          {stats.map((s, i) => (
            <div key={i} className="mini-stat">
              <b>{s[0]}</b>
              <span>{s[1]}</span>
              <span className="badge">{s[2]}</span>
            </div>
          ))}
        </div>
        <div className="mini-chart">
          {[42,58,48,64,70,66,78,84,80,92,98,110].map((v, i) => (
            <div key={i} className="bar" style={{ height: `${(v/110)*100}%` }}/>
          ))}
        </div>
        <div className="mini-table">
          {rows.map((r, i) => (
            <div key={i} className="mini-table-row">
              <span style={{ fontWeight: 600, color: "var(--fg-1)" }}>{r[0]}</span>
              <code>{r[1]}</code>
              <span className={`pill ${r[3] === "warn" ? "warn" : r[3] === "danger" ? "danger" : ""}`}>● {r[2]}</span>
              <span style={{ color: "var(--fg-3)", textAlign: "right" }}>⋯</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

/* ---------- Nav ---------- */
function Nav() {
  return (
    <header className="nav">
      <div className="container nav-inner">
        <a href="#" className="nav-logo">
          <img src={(window.__resources && window.__resources.logo) || "../assets/heritage-logo.svg"} alt=""/>
          <span>Heritage <b>Pro</b></span>
        </a>
        <nav className="nav-links">
          <a href="#products">Products</a>
          <a href="#features">Features</a>
          <a href="#stories">Customers</a>
          <a href="#pricing">Pricing</a>
          <a href="#about">About</a>
          <a href="#faq">FAQ</a>
        </nav>
        <div className="nav-cta">
          <a href="#contact" className="btn btn-ghost">Sign in</a>
          <a href="#contact" className="btn btn-primary">Book a demo</a>
        </div>
      </div>
    </header>
  );
}

/* ---------- Hero variants ---------- */
function Hero({ variant = "split" }) {
  if (variant === "centred") return <HeroCentred/>;
  if (variant === "dark") return <HeroDark/>;
  return <HeroSplit/>;
}

function HeroSplit() {
  return (
    <section className="hero">
      <div className="container hero-inner">
        <div>
          <h1 style={{ marginTop: 0 }}>The intelligent operating system for every school, college, and institution.</h1>
          <p className="lead">Heritage Pro unifies admissions, academics, attendance, fees, communications, and reporting on a single, secure platform — built for K-12, secondary schools, and tertiary institutions.</p>
          <div className="hero-cta">
            <a href="#contact" className="btn btn-primary btn-lg">Book a demo <Icon name="arrow" size={16}/></a>
            <a href="#features" className="btn btn-secondary btn-lg"><Icon name="play" size={14}/> See the product</a>
          </div>
          <div className="hero-trust">
            <div><b>98+</b> institutions</div>
            <div><b>84,000+</b> learners</div>
            <div><b>99.95%</b> uptime</div>
          </div>
        </div>
        <div className="hero-media">
          <div className="window-chrome"><span/><span/><span/><div className="url">app.heritagepro.net</div></div>
          <MiniDash product="schools"/>
        </div>
      </div>
    </section>
  );
}

function HeroCentred() {
  return (
    <section className="hero centred">
      <div className="container hero-inner">
        <div>
          <span className="eyebrow">School management · Built in Botswana</span>
          <h1 style={{ marginTop: 20 }}>One intelligent platform for schools, colleges &amp; institutions.</h1>
          <p className="lead">From admissions to alumni, Heritage Pro centralises academic, financial, and operational workflows — so administrators spend more time on learning and less on paperwork.</p>
          <div className="hero-cta">
            <a href="#contact" className="btn btn-primary btn-lg">Book a demo <Icon name="arrow" size={16}/></a>
            <a href="#features" className="btn btn-secondary btn-lg">Explore modules</a>
          </div>
          <div className="hero-trust">
            <div><b>98+</b> institutions</div>
            <div><b>84,000+</b> learners</div>
            <div><b>99.95%</b> uptime</div>
          </div>
          <div className="hero-media" style={{ marginTop: 56 }}>
            <div className="window-chrome"><span/><span/><span/><div className="url">app.heritagepro.net</div></div>
            <MiniDash product="schools"/>
          </div>
        </div>
      </div>
    </section>
  );
}

function HeroDark() {
  return (
    <section className="hero dark">
      <div className="container hero-inner">
        <div>
          <span className="eyebrow">Heritage Pro · Education management suite</span>
          <h1 style={{ marginTop: 20 }}>Run your institution with clarity, confidence, and control.</h1>
          <p className="lead">Heritage Pro is a complete platform for academic and administrative leaders: unified records, real-time insight, and compliance-grade security across every campus.</p>
          <div className="hero-cta">
            <a href="#contact" className="btn btn-white btn-lg">Book a demo <Icon name="arrow" size={16}/></a>
            <a href="#features" className="btn btn-lg" style={{ color: "#fff", borderColor: "rgba(255,255,255,0.28)" }}>See the modules</a>
          </div>
          <div className="hero-trust">
            <div><b>98+</b> institutions</div>
            <div><b>84,000+</b> learners</div>
            <div><b>99.95%</b> uptime</div>
          </div>
        </div>
        <div className="hero-media">
          <div className="window-chrome"><span/><span/><span/><div className="url">app.heritagepro.net</div></div>
          <MiniDash product="collegiate"/>
        </div>
      </div>
    </section>
  );
}

/* ---------- Logo strip ---------- */
function LogoStrip() {
  const names = ["Francistown Senior School", "Madiba Senior School", "Nata Senior School", "Shakawe Senior School", "Sunnysands", "Swaneng Hill Senior School"];
  return (
    <section className="logo-strip">
      <div className="container">
        <div className="label">Trusted by institutions across Botswana, South Africa, Namibia &amp; Zambia</div>
        <div className="logo-row">
          {names.map(n => (
            <div key={n} className="fake-logo">
              <div className="mark">{n.split(" ").map(w => w[0]).slice(0, 2).join("")}</div>
              <span>{n}</span>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ---------- Stats ---------- */
function Stats() {
  return (
    <section className="stats">
      <div className="container">
        <div className="stats-grid">
          <div>
            <div className="stats-num">180<span>+</span></div>
            <div className="stats-label">Institutions running Heritage Pro</div>
          </div>
          <div>
            <div className="stats-num">96k<span>+</span></div>
            <div className="stats-label">Learners with live records</div>
          </div>
          <div>
            <div className="stats-num">99.95<span>%</span></div>
            <div className="stats-label">Platform uptime, measured monthly</div>
          </div>
          <div>
            <div className="stats-num">12<span>×</span></div>
            <div className="stats-label">Faster report-card turnaround</div>
          </div>
        </div>
      </div>
    </section>
  );
}

/* ---------- Products ---------- */
function Products() {
  return (
    <section id="products" className="section products">
      <div className="container">
        <div className="center" style={{ maxWidth: 720, margin: "0 auto 12px" }}>
          <span className="eyebrow">One platform, three editions</span>
          <h2 style={{ marginTop: 14 }}>Purpose-built for every stage of education.</h2>
          <p className="lead" style={{ marginTop: 16 }}>Heritage Pro ships as three editions that share the same secure core, data model, and brand — so learners, staff, and parents move seamlessly from pre-primary to graduation.</p>
        </div>
        <div className="products-grid">
          <div className="product-card schools">
            <span className="product-badge">Secondary</span>
            <div className="tile"><Icon name="book" size={28}/></div>
            <h3>Heritage Pro — Schools</h3>
            <p>For secondary schools managing admissions, report cards, BGCSE &amp; JCE workflows, houses, and parent engagement.</p>
            <ul>
              <li>Form 1 – 5 academic management</li>
              <li>Continuous &amp; examination assessment</li>
              <li>Report cards, houses &amp; prefect teams</li>
              <li>Fees ledger &amp; parent portal</li>
            </ul>
            <a href="#features" className="btn btn-secondary">Explore Schools <Icon name="arrow" size={14}/></a>
          </div>
          <div className="product-card collegiate">
            <span className="product-badge">Tertiary</span>
            <div className="tile"><Icon name="grad" size={28}/></div>
            <h3>Heritage Pro — Collegiate</h3>
            <p>For colleges, polytechnics, and universities — with semester registration, credit-weighted GPA transcripts, timetabling, and sponsor billing.</p>
            <ul>
              <li>Programme &amp; course catalogue</li>
              <li>Semester registration &amp; GPA</li>
              <li>Research, theses &amp; senate workflow</li>
              <li>DTEF, BIUST &amp; sponsor billing</li>
            </ul>
            <a href="#features" className="btn btn-secondary">Explore Collegiate <Icon name="arrow" size={14}/></a>
          </div>
          <div className="product-card k12">
            <span className="product-badge">K-12 &amp; Primary</span>
            <div className="tile"><Icon name="child" size={28}/></div>
            <h3>Heritage Pro — K-12</h3>
            <p>For pre-primary, primary, and international K-12 schools — with age-appropriate records, parent engagement, and bilingual learning logs.</p>
            <ul>
              <li>Pre-primary &amp; primary modules</li>
              <li>Standards &amp; milestone tracking</li>
              <li>Bilingual (English / Setswana) reports</li>
              <li>SMS + WhatsApp parent updates</li>
            </ul>
            <a href="#features" className="btn btn-secondary">Explore K-12 <Icon name="arrow" size={14}/></a>
          </div>
        </div>
      </div>
    </section>
  );
}

window.HPSite1 = { Icon, MiniDash, Nav, Hero, LogoStrip, Stats, Products };
