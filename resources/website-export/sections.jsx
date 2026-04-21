/* website/sections.jsx — features, testimonials, cases, pricing, faq, blog, contact, footer */

const { useState: useS2 } = React;

/* ---------- Features deep-dive ---------- */
function Features() {
  const { MiniDash, Icon } = window.HPSite1;
  return (
    <section id="features" className="section features">
      <div className="container">
        <div className="center" style={{ maxWidth: 720, margin: "0 auto" }}>
          <span className="eyebrow">The Heritage Pro suite</span>
          <h2 style={{ marginTop: 14 }}>Every module your institution needs — working as one.</h2>
          <p className="lead" style={{ marginTop: 16 }}>No more paying for four disconnected systems. Heritage Pro replaces your SIS, LMS, fees ledger, and parent-comms tools with one coherent platform, designed for African schools.</p>
        </div>

        <div className="features-row">
          <div className="feature-copy">
            <span className="eyebrow">Student information</span>
            <h3>A single, authoritative record for every learner.</h3>
            <p>From admission to alumni — biographic details, academic history, health notes, guardian contacts, and fee status all live in one secure profile. Audit-logged and compliance-ready.</p>
            <ul>
              <li><b>Admissions pipeline</b> — applications, waiting lists, and enrolment in one queue.</li>
              <li><b>House &amp; stream assignment</b> with live capacity tracking.</li>
              <li><b>Documents vault</b> — birth certificates, Omang, and transfer letters encrypted at rest.</li>
              <li><b>Alumni</b> records retained indefinitely with opt-in engagement.</li>
            </ul>
            <a href="#contact" className="btn btn-secondary">Request a walkthrough <Icon name="arrow" size={14}/></a>
          </div>
          <div className="feature-mock">
            <div className="window-chrome" style={{ height: 32, background: "#F3F5F9", display: "flex", gap: 6, alignItems: "center", padding: "0 14px", borderBottom: "1px solid var(--border-1)" }}>
              <span style={{ width: 10, height: 10, borderRadius: "50%", background: "#D6DAE2" }}/>
              <span style={{ width: 10, height: 10, borderRadius: "50%", background: "#D6DAE2" }}/>
              <span style={{ width: 10, height: 10, borderRadius: "50%", background: "#D6DAE2" }}/>
              <div className="url" style={{ flex: 1, marginLeft: 18, fontSize: 11, color: "var(--fg-3)", fontFamily: "var(--font-mono)", background: "#fff", padding: "4px 10px", borderRadius: 6, border: "1px solid var(--border-1)", maxWidth: 280 }}>demo.heritagepro.net</div>
            </div>
            <StudentRecordMock/>
          </div>
        </div>

        <div className="features-row reverse">
          <div className="feature-copy">
            <span className="eyebrow">Assessment &amp; reports</span>
            <h3>Generate beautiful report cards in minutes, not weeks.</h3>
            <p>Capture continuous assessment, examination scores, and teacher comments through a fast spreadsheet-like grid. Publish branded report cards in a click — BGCSE, JCE, PSLE, and international rubrics supported out of the box.</p>
            <ul>
              <li><b>Flexible grading schemes</b> — percentages, letters, grade points, or rubric-based.</li>
              <li><b>Teacher &amp; head comments</b> with AI-suggested language.</li>
              <li><b>BGCSE / JCE / Cambridge</b> templates ready-to-use.</li>
              <li><b>PDF &amp; WhatsApp delivery</b> to verified guardians.</li>
            </ul>
            <a href="#contact" className="btn btn-secondary">See sample reports <Icon name="arrow" size={14}/></a>
          </div>
          <div className="feature-mock">
            <ReportCardMock/>
          </div>
        </div>

        <div className="features-row">
          <div className="feature-copy">
            <span className="eyebrow">Fees &amp; finance</span>
            <h3>Fees, sponsors, and reconciliations — without the spreadsheets.</h3>
            <p>Heritage Pro connects to FNB, Stanbic, and ABSA Botswana for automatic bank reconciliation, and issues receipts by email, SMS, or WhatsApp. Sponsor and DTEF billing are first-class citizens.</p>
            <ul>
              <li><b>Structured invoices</b> — tuition, boarding, meals, uniforms, levies, and fines.</li>
              <li><b>Payment plans</b> with automated reminders and late-fee rules.</li>
              <li><b>Sponsor portal</b> for DTEF, BDF, and corporate bursaries.</li>
              <li><b>Bank reconciliation</b> — import CSVs or connect live feeds.</li>
            </ul>
            <a href="#contact" className="btn btn-secondary">See the fees module <Icon name="arrow" size={14}/></a>
          </div>
          <div className="feature-mock">
            <FeesMock/>
          </div>
        </div>

        <h3 className="center" style={{ marginTop: 120, marginBottom: 14, fontSize: 32 }}>Every module, working in concert.</h3>
        <p className="lead center" style={{ maxWidth: 620, margin: "0 auto" }}>A complete administrative operating system — twelve modules, one database, one login.</p>

        <div className="modules-grid">
          {[
            { icon: "users", t: "Student records", d: "Biographic, academic, health, and guardian data in one unified profile." },
            { icon: "clipboard", t: "Admissions", d: "Applications, waitlists, entrance tests, and class placement workflow." },
            { icon: "book", t: "Academics", d: "Subjects, syllabi, continuous assessment, and examination schedules." },
            { icon: "calendar", t: "Attendance", d: "Bell-schedule aware, with biometric, RFID, or manual capture." },
            { icon: "credit", t: "Fees &amp; billing", d: "Invoicing, receipts, sponsor billing, and bank reconciliation." },
            { icon: "megaphone", t: "Parent comms", d: "SMS, email, and WhatsApp broadcasts with delivery receipts." },
            { icon: "bio", t: "Report cards", d: "Branded, compliant report cards for BGCSE, JCE, and Cambridge." },
            { icon: "grad", t: "Exams &amp; grading", d: "Moderation, remark requests, and secure examination workflows." },
            { icon: "lib", t: "Library", d: "Catalogue, loans, reservations, and overdue reminders by SMS." },
            { icon: "bus", t: "Transport", d: "Route &amp; stop management, vehicle tracking, and driver rosters." },
            { icon: "shield", t: "Roles &amp; audit", d: "Granular permissions, single sign-on, and tamper-evident logs." },
            { icon: "cpu", t: "Analytics", d: "Dashboards for heads, boards, and regional offices — in real time." },
          ].map((m, i) => (
            <div key={i} className="module-tile">
              <div className="icon"><Icon name={m.icon} size={22}/></div>
              <h4 dangerouslySetInnerHTML={{ __html: m.t }}/>
              <p>{m.d}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ---------- Feature mocks ---------- */
function StudentRecordMock() {
  return (
    <div style={{ padding: 20, background: "#fff" }}>
      <div style={{ display: "flex", gap: 14, alignItems: "center", marginBottom: 20 }}>
        <div style={{ width: 56, height: 56, borderRadius: 999, background: "var(--brand-gradient)", color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "var(--font-display)", fontWeight: 700, fontSize: 18 }}>BM</div>
        <div>
          <div style={{ fontFamily: "var(--font-display)", fontSize: 18, fontWeight: 700 }}>Boitumelo Mosadi</div>
          <div style={{ fontSize: 12, color: "var(--fg-3)" }}>STU-2025-0482 · Form 3R · House: Khama · Admitted Jan 2023</div>
        </div>
        <div style={{ marginLeft: "auto", display: "flex", gap: 6 }}>
          <span style={{ padding: "4px 10px", fontSize: 11, fontWeight: 700, borderRadius: 999, background: "var(--success-50)", color: "var(--success-700)" }}>Active</span>
          <span style={{ padding: "4px 10px", fontSize: 11, fontWeight: 700, borderRadius: 999, background: "var(--success-50)", color: "var(--success-700)" }}>Paid</span>
        </div>
      </div>
      <div style={{ display: "flex", gap: 0, borderBottom: "1px solid var(--border-1)", marginBottom: 14 }}>
        {["Overview", "Academics", "Attendance", "Fees", "Health", "Documents"].map((t, i) => (
          <div key={i} style={{ padding: "8px 14px", fontSize: 12, fontWeight: 600, color: i === 0 ? "var(--brand-indigo-500)" : "var(--fg-3)", borderBottom: i === 0 ? "2px solid var(--brand-indigo-500)" : "2px solid transparent", marginBottom: -1 }}>{t}</div>
        ))}
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10, fontSize: 12 }}>
        {[["Date of birth", "14 March 2009"], ["Omang / ID", "409031214"], ["Guardian", "Keneilwe Mosadi · +267 7123 4567"], ["Address", "Plot 4820, Gaborone West"], ["Blood group", "O+"], ["Allergies", "Penicillin"]].map(([k, v], i) => (
          <div key={i} style={{ padding: "10px 12px", background: "var(--bg-subtle)", borderRadius: 8 }}>
            <div style={{ fontSize: 10, color: "var(--fg-3)", textTransform: "uppercase", letterSpacing: "0.06em", marginBottom: 3 }}>{k}</div>
            <div style={{ fontWeight: 600, color: "var(--fg-1)" }}>{v}</div>
          </div>
        ))}
      </div>
      <div style={{ marginTop: 14, padding: "10px 12px", background: "var(--brand-indigo-50)", borderRadius: 8, fontSize: 12, color: "var(--brand-indigo-700)", display: "flex", gap: 8, alignItems: "center" }}>
        <span style={{ width: 6, height: 6, borderRadius: "50%", background: "var(--brand-indigo-500)" }}/>
        Form-teacher note added 2 Mar — Boitumelo elected form prefect for Term 1.
      </div>
    </div>
  );
}

function ReportCardMock() {
  return (
    <div style={{ padding: 22, background: "#fff" }}>
      <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 14, paddingBottom: 14, borderBottom: "2px solid var(--brand-indigo-500)" }}>
        <div style={{ width: 40, height: 40, borderRadius: 8, background: "var(--brand-gradient)" }}/>
        <div>
          <div style={{ fontFamily: "var(--font-display)", fontSize: 14, fontWeight: 700 }}>Thornhill Preparatory School</div>
          <div style={{ fontSize: 10, color: "var(--fg-3)" }}>End of Term 2 Report · Form 3R · 2025</div>
        </div>
        <div style={{ marginLeft: "auto", fontSize: 10, color: "var(--fg-3)", textAlign: "right" }}>
          <div style={{ fontWeight: 700, color: "var(--fg-1)", fontSize: 11 }}>Boitumelo Mosadi</div>
          STU-2025-0482
        </div>
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr 1fr 1fr 2fr", gap: 0, fontSize: 11 }}>
        {["Subject", "CA", "Exam", "Grade", "Teacher's comment"].map((h, i) => (
          <div key={h} style={{ padding: "8px 10px", background: "var(--bg-subtle)", fontWeight: 700, color: "var(--fg-2)", textTransform: "uppercase", fontSize: 9, letterSpacing: "0.06em", borderBottom: "1px solid var(--border-1)" }}>{h}</div>
        ))}
        {[
          ["English Language", "84", "78", "A", "Articulate and consistent."],
          ["Setswana", "92", "88", "A*", "Exemplary literary analysis."],
          ["Mathematics", "76", "82", "B+", "Strong effort on geometry."],
          ["Double Science", "88", "84", "A", "Outstanding practical work."],
          ["Geography", "80", "74", "B+", "Clear structured essays."],
          ["Commerce", "85", "86", "A", "Leadership shown in groups."],
        ].map((row, i) => (
          <React.Fragment key={i}>
            {row.map((c, j) => (
              <div key={j} style={{ padding: "9px 10px", borderBottom: "1px solid var(--border-1)", color: j === 0 ? "var(--fg-1)" : "var(--fg-2)", fontWeight: j === 0 || j === 3 ? 600 : 400, fontSize: j === 4 ? 10 : 11 }}>{c}</div>
            ))}
          </React.Fragment>
        ))}
      </div>
      <div style={{ marginTop: 14, padding: "12px 14px", background: "var(--brand-indigo-50)", borderRadius: 10, display: "grid", gridTemplateColumns: "repeat(4,1fr)", gap: 10 }}>
        {[["Average", "83.5%"], ["Position", "4 of 32"], ["Attendance", "98%"], ["Conduct", "Excellent"]].map(([k, v], i) => (
          <div key={i}>
            <div style={{ fontSize: 9, color: "var(--fg-3)", textTransform: "uppercase", letterSpacing: "0.06em", marginBottom: 2 }}>{k}</div>
            <div style={{ fontFamily: "var(--font-display)", fontWeight: 700, fontSize: 15, color: "var(--brand-indigo-600)" }}>{v}</div>
          </div>
        ))}
      </div>
    </div>
  );
}

function FeesMock() {
  return (
    <div style={{ padding: 20, background: "#fff" }}>
      <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 16 }}>
        <div style={{ fontFamily: "var(--font-display)", fontSize: 16, fontWeight: 700 }}>Fees — Term 2</div>
        <span style={{ marginLeft: "auto", padding: "4px 10px", fontSize: 11, fontWeight: 700, borderRadius: 999, background: "var(--success-50)", color: "var(--success-700)" }}>● 78% collected</span>
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "repeat(3,1fr)", gap: 10, marginBottom: 16 }}>
        {[["BWP 412,400", "Collected", "var(--success-700)"], ["BWP 84,200", "Outstanding", "var(--warning-600)"], ["BWP 28,600", "Overdue", "var(--danger-700)"]].map(([v, l, c], i) => (
          <div key={i} style={{ padding: "12px 14px", border: "1px solid var(--border-1)", borderRadius: 10 }}>
            <div style={{ fontFamily: "var(--font-display)", fontSize: 16, fontWeight: 700, color: c }}>{v}</div>
            <div style={{ fontSize: 11, color: "var(--fg-3)" }}>{l}</div>
          </div>
        ))}
      </div>
      <div style={{ border: "1px solid var(--border-1)", borderRadius: 10, overflow: "hidden" }}>
        <div style={{ display: "grid", gridTemplateColumns: "2fr 1.2fr 1fr 1fr 1fr", padding: "10px 14px", background: "var(--bg-subtle)", fontSize: 10, fontWeight: 700, color: "var(--fg-2)", textTransform: "uppercase", letterSpacing: "0.06em" }}>
          <div>Student</div><div>Invoice</div><div>Due</div><div>Amount</div><div>Status</div>
        </div>
        {[
          ["Boitumelo Mosadi", "INV-482-T2", "28 Apr", "BWP 12,800", "Paid", "ok"],
          ["Atang Nkhata", "INV-215-T2", "28 Apr", "BWP 12,800", "Partial", "warn"],
          ["Lesedi Moeti", "INV-618-T2", "28 Apr", "BWP 14,200", "Paid", "ok"],
          ["Kago Tshekiso", "INV-704-T2", "14 Mar", "BWP 12,800", "Overdue", "danger"],
          ["Naledi Pilane", "INV-322-T2", "28 Apr", "BWP 12,800", "Paid", "ok"],
        ].map((r, i) => (
          <div key={i} style={{ display: "grid", gridTemplateColumns: "2fr 1.2fr 1fr 1fr 1fr", padding: "10px 14px", fontSize: 11, borderTop: "1px solid var(--border-1)", alignItems: "center" }}>
            <div style={{ fontWeight: 600, color: "var(--fg-1)" }}>{r[0]}</div>
            <div style={{ fontFamily: "var(--font-mono)", color: "var(--fg-2)", fontSize: 10 }}>{r[1]}</div>
            <div style={{ color: "var(--fg-3)" }}>{r[2]}</div>
            <div style={{ fontWeight: 600 }}>{r[3]}</div>
            <div>
              <span style={{ padding: "2px 8px", fontSize: 10, fontWeight: 700, borderRadius: 999, background: r[5] === "ok" ? "var(--success-50)" : r[5] === "warn" ? "var(--warning-50)" : "var(--danger-50)", color: r[5] === "ok" ? "var(--success-700)" : r[5] === "warn" ? "var(--warning-600)" : "var(--danger-700)" }}>● {r[4]}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

/* ---------- Testimonials ---------- */
function Testimonials() {
  return (
    <section className="section testimonials">
      <div className="container">
        <div className="center" style={{ maxWidth: 680, margin: "0 auto" }}>
          <span className="eyebrow">What school leaders say</span>
          <h2 style={{ marginTop: 14 }}>Heritage Pro has changed how we run our school.</h2>
        </div>
        <div className="testimonial-grid">
          <div className="testimonial">
            <div className="stars">★ ★ ★ ★ ★</div>
            <p>"Before Heritage Pro, report cards took us three weeks. Now I generate and send them the evening exams end. Parents appreciate it; staff are free to teach."</p>
            <div className="author">
              <div className="avatar">TM</div>
              <div><b>Tebogo Molefe</b><span>Head Teacher · Thornhill Prep</span></div>
            </div>
          </div>
          <div className="testimonial featured">
            <div className="stars">★ ★ ★ ★ ★</div>
            <p>"We moved four separate systems onto Heritage Pro in a single term. Fee collection is up 18% because parents finally see clean statements and can pay from their phones."</p>
            <div className="author">
              <div className="avatar">KD</div>
              <div><b>Keabetswe Dikgang</b><span>Bursar · St Joseph's College</span></div>
            </div>
          </div>
          <div className="testimonial">
            <div className="stars">★ ★ ★ ★ ★</div>
            <p>"The Collegiate edition handles our DTEF sponsorship billing perfectly — something our old system never managed. Support is responsive and genuinely understands the sector."</p>
            <div className="author">
              <div className="avatar">NP</div>
              <div><b>Naledi Pilane</b><span>Registrar · Gaborone Polytechnic</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

/* ---------- Case studies ---------- */
function Cases() {
  return (
    <section id="stories" className="section cases">
      <div className="container">
        <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", flexWrap: "wrap", gap: 20 }}>
          <div style={{ maxWidth: 580 }}>
            <span className="eyebrow">Customer stories</span>
            <h2 style={{ marginTop: 14 }}>Results from schools already live on Heritage Pro.</h2>
          </div>
          <a href="#" className="btn btn-secondary">All stories <span>→</span></a>
        </div>
        <div className="cases-grid">
          <div className="case-card">
            <div className="case-cover schools">
              <span className="case-tag">Secondary</span>
              <div className="case-kicker">Thornhill Prep</div>
            </div>
            <div className="case-body">
              <h4>3 weeks of report cards, reduced to 48 hours.</h4>
              <p>Thornhill replaced four legacy systems with Heritage Pro Schools and cut end-of-term admin from 21 days to 2.</p>
              <div className="case-metrics">
                <div className="case-metric"><b>-90%</b><span>Admin time</span></div>
                <div className="case-metric"><b>+18%</b><span>Parent NPS</span></div>
              </div>
            </div>
          </div>
          <div className="case-card">
            <div className="case-cover collegiate">
              <span className="case-tag">Tertiary</span>
              <div className="case-kicker">Gaborone Polytechnic</div>
            </div>
            <div className="case-body">
              <h4>DTEF billing automated across 4,800 students.</h4>
              <p>Collegiate's sponsor-billing module removed two full-time reconciliation roles from the Bursar's office.</p>
              <div className="case-metrics">
                <div className="case-metric"><b>4.8k</b><span>Students</span></div>
                <div className="case-metric"><b>BWP 38M</b><span>Invoiced / yr</span></div>
              </div>
            </div>
          </div>
          <div className="case-card">
            <div className="case-cover k12">
              <span className="case-tag">Primary</span>
              <div className="case-kicker">Platinum Academy</div>
            </div>
            <div className="case-body">
              <h4>Bilingual reports parents actually open.</h4>
              <p>Platinum's bilingual English/Setswana report cards over WhatsApp drove 92% guardian engagement in a single term.</p>
              <div className="case-metrics">
                <div className="case-metric"><b>92%</b><span>Guardian reads</span></div>
                <div className="case-metric"><b>2 days</b><span>Rollout</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

window.HPSite2 = { Features, Testimonials, Cases };
