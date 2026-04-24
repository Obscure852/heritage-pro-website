/* website/footer.jsx — pricing, faq, blog, contact, footer, app root */

const { useState: useS3 } = React;

/* ---------- Pricing ---------- */
function Pricing() {
  const { Icon } = window.HPSite1;
  return (
    <section id="pricing" className="section pricing">
      <div className="container">
        <div className="center" style={{ maxWidth: 640, margin: "0 auto" }}>
          <span className="eyebrow">Pricing</span>
          <h2 style={{ marginTop: 14 }}>Fair, per-learner pricing — no surprise invoices.</h2>
          <p className="lead" style={{ marginTop: 16 }}>All plans include unlimited staff accounts, unlimited parent logins, WhatsApp, SMS allowances, and on-site training.</p>
        </div>
        <div className="pricing-grid">
          <div className="price-card">
            <div className="price-name">Starter</div>
            <div className="price-amount">BWP 28</div>
            <div className="price-unit">per learner / month</div>
            <p className="price-desc">For schools up to 500 learners starting with core admissions, academics, and fees.</p>
            <ul>
              <li>Student records &amp; admissions</li>
              <li>Report cards &amp; attendance</li>
              <li>Fees &amp; invoicing</li>
              <li>500 SMS / month included</li>
              <li>Email &amp; chat support</li>
            </ul>
            <a href="#contact" className="btn btn-secondary price-cta">Choose Starter</a>
          </div>
          <div className="price-card highlight">
            <div className="ribbon">Most popular</div>
            <div className="price-name">Professional</div>
            <div className="price-amount">BWP 42</div>
            <div className="price-unit">per learner / month</div>
            <p className="price-desc">For established schools that need advanced workflows, parent comms, and analytics.</p>
            <ul>
              <li>Everything in Starter</li>
              <li>Parent portal &amp; WhatsApp</li>
              <li>Library, transport &amp; health</li>
              <li>Analytics &amp; board dashboards</li>
              <li>Priority support + named CSM</li>
            </ul>
            <a href="#contact" className="btn btn-primary price-cta">Choose Professional</a>
          </div>
          <div className="price-card">
            <div className="price-name">Institution</div>
            <div className="price-amount">Custom</div>
            <div className="price-unit">for colleges &amp; groups</div>
            <p className="price-desc">For tertiary institutions and multi-campus groups with sponsor billing and SSO.</p>
            <ul>
              <li>Everything in Professional</li>
              <li>Heritage Pro Collegiate edition</li>
              <li>DTEF / sponsor billing</li>
              <li>SAML SSO &amp; custom roles</li>
              <li>Dedicated onboarding team</li>
            </ul>
            <a href="#contact" className="btn btn-secondary price-cta">Talk to sales</a>
          </div>
        </div>
      </div>
    </section>
  );
}

/* ---------- FAQ ---------- */
function FAQ() {
  const { Icon } = window.HPSite1;
  const [open, setOpen] = useS3(0);
  const items = [
    ["Is Heritage Pro compliant with Botswana Ministry of Education reporting?", "Yes. Heritage Pro generates BEC, MoESD, and Ministry-format returns out of the box. BGCSE, JCE, and PSLE workflows are certified and updated each academic year."],
    ["Can we migrate data from our old system?", "Absolutely. Our onboarding team handles CSV, Excel, and SQL migrations from most common SIS platforms at no extra cost during the first 60 days."],
    ["How does WhatsApp / SMS pricing work?", "Every plan includes a monthly SMS and WhatsApp allowance. Additional messages are billed at cost — typically BWP 0.18 per SMS and BWP 0.09 per WhatsApp message, with no markup."],
    ["Where is our data stored?", "Student data is stored in AWS af-south-1 (Cape Town), with nightly off-site backups and optional on-premise mirror for government institutions. All data remains within SADC."],
    ["Do parents need an app?", "No. Parents receive reports and notices over WhatsApp and SMS by default. A mobile-responsive parent portal is available but optional."],
    ["What happens if we cancel?", "Your data is yours. We export everything to CSV and PDF and retain a read-only archive for 90 days. No lock-in, no penalties."],
  ];
  return (
    <section id="faq" className="section faq">
      <div className="container container-narrow">
        <div className="center">
          <span className="eyebrow">Frequently asked</span>
          <h2 style={{ marginTop: 14 }}>Answers for cautious administrators.</h2>
        </div>
        <div className="faq-list">
          {items.map(([q, a], i) => (
            <div key={i} className={`faq-item ${open === i ? "open" : ""}`} onClick={() => setOpen(open === i ? -1 : i)}>
              <div className="faq-q">{q}<Icon name="chevron" size={18}/></div>
              {open === i && <div className="faq-a">{a}</div>}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ---------- Blog ---------- */
function Blog() {
  return (
    <section className="section blog">
      <div className="container">
        <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", flexWrap: "wrap", gap: 20 }}>
          <div style={{ maxWidth: 580 }}>
            <span className="eyebrow">From the Heritage Pro blog</span>
            <h2 style={{ marginTop: 14 }}>Ideas and playbooks for school leaders.</h2>
          </div>
          <a href="#" className="btn btn-secondary">All articles →</a>
        </div>
        <div className="blog-grid">
          <article className="blog-card">
            <div className="blog-cover a"/>
            <span className="blog-tag">Playbook</span>
            <h4>Cutting report-card turnaround from 3 weeks to 48 hours</h4>
            <p>A step-by-step operating rhythm for heads of department, based on a senior school reporting cycle.</p>
            <div className="blog-meta">8 min read · Apr 2025</div>
          </article>
          <article className="blog-card">
            <div className="blog-cover b"/>
            <span className="blog-tag">Policy</span>
            <h4>What the new BGCSE reporting rubric means for 2025</h4>
            <p>A plain-English guide to the Ministry's rubric refresh, and how to re-weight your assessment calendar.</p>
            <div className="blog-meta">6 min read · Mar 2025</div>
          </article>
          <article className="blog-card">
            <div className="blog-cover c"/>
            <span className="blog-tag">Product</span>
            <h4>How sponsor billing should work — DTEF, done right</h4>
            <p>Why a fee ledger built for individual payers falls over when 60% of tuition is sponsor-funded.</p>
            <div className="blog-meta">7 min read · Feb 2025</div>
          </article>
        </div>
      </div>
    </section>
  );
}

/* ---------- Contact ---------- */
function Contact() {
  const { Icon } = window.HPSite1;
  return (
    <section id="contact" className="contact">
      <div className="container contact-inner">
        <div>
          <span className="eyebrow" style={{ color: "rgba(255,255,255,0.7)" }}>Get started</span>
          <h2 style={{ marginTop: 14 }}>Book a 30-minute demo.</h2>
          <p>See Heritage Pro on your own data. Our team will tailor the walkthrough to your school, import a sample of your records, and answer every question.</p>
          <ul className="contact-list">
            <li><Icon name="pin" size={18}/><span><b>Head office</b>Plot 50371, CBD · Gaborone, Botswana</span></li>
            <li><Icon name="mail" size={18}/><span><b>Email</b>hello@heritagepro.co</span></li>
            <li><Icon name="phone" size={18}/><span><b>Phone</b>+267 390 5400</span></li>
          </ul>
        </div>
        <form className="contact-form" onSubmit={(e) => e.preventDefault()}>
          <div className="form-row">
            <div className="form-field">
              <label>Full name</label>
              <input type="text" placeholder="Tebogo Molefe"/>
            </div>
            <div className="form-field">
              <label>Your role</label>
              <input type="text" placeholder="Head Teacher"/>
            </div>
          </div>
          <div className="form-field">
            <label>Institution</label>
            <input type="text" placeholder="Francistown Senior School"/>
          </div>
          <div className="form-row">
            <div className="form-field">
              <label>Work email</label>
              <input type="email" placeholder="admin@francistown.seniorschool.info"/>
            </div>
            <div className="form-field">
              <label>Phone</label>
              <input type="tel" placeholder="+267 71 234 567"/>
            </div>
          </div>
          <div className="form-row">
            <div className="form-field">
              <label>Edition</label>
              <select>
                <option>Heritage Pro — Schools</option>
                <option>Heritage Pro — Collegiate</option>
                <option>Heritage Pro — K-12</option>
                <option>Not sure yet</option>
              </select>
            </div>
            <div className="form-field">
              <label>Number of learners</label>
              <select>
                <option>Under 200</option>
                <option>200 – 500</option>
                <option>500 – 1,500</option>
                <option>1,500 – 5,000</option>
                <option>5,000+</option>
              </select>
            </div>
          </div>
          <div className="form-field">
            <label>Anything we should know?</label>
            <textarea placeholder="Current systems, timeline, pain points…"/>
          </div>
          <button className="btn btn-primary contact-cta">Request demo <Icon name="arrow" size={14}/></button>
          <p style={{ textAlign: "center", fontSize: 12, color: "var(--fg-3)", marginTop: 12, marginBottom: 0 }}>We respond within one business day.</p>
        </form>
      </div>
    </section>
  );
}

/* ---------- Footer ---------- */
function Footer() {
  const { Icon } = window.HPSite1;
  return (
    <footer className="footer">
      <div className="container">
        <div className="footer-top">
          <div className="footer-brand">
            <a href="#" className="nav-logo"><img src={(window.__resources && window.__resources.logo) || "../assets/heritage-logo.svg"} alt=""/><span>Heritage <b>Pro</b></span></a>
            <p>The intelligent operating system for schools, colleges, and institutions across Southern Africa.</p>
            <div className="footer-social">
              <a href="#" aria-label="LinkedIn"><Icon name="linkedin" size={18}/></a>
              <a href="#" aria-label="Twitter"><Icon name="twitter" size={18}/></a>
              <a href="#" aria-label="Facebook"><Icon name="facebook" size={18}/></a>
              <a href="#" aria-label="YouTube"><Icon name="yt" size={18}/></a>
            </div>
          </div>
          <div className="footer-cols">
            <div>
              <b>Products</b>
              <a href="#">Schools</a>
              <a href="#">Collegiate</a>
              <a href="#">K-12</a>
              <a href="#">Parent portal</a>
              <a href="#">API</a>
            </div>
            <div>
              <b>Company</b>
              <a href="#">About</a>
              <a href="#">Customers</a>
              <a href="#">Careers</a>
              <a href="#">Press</a>
              <a href="#">Partners</a>
            </div>
            <div>
              <b>Resources</b>
              <a href="#">Blog</a>
              <a href="#">Playbooks</a>
              <a href="#">Help centre</a>
              <a href="#">Status</a>
              <a href="#">Changelog</a>
            </div>
            <div>
              <b>Legal</b>
              <a href="#">Privacy</a>
              <a href="#">Terms</a>
              <a href="#">Data processing</a>
              <a href="#">Security</a>
              <a href="#">Cookies</a>
            </div>
          </div>
        </div>
        <div className="footer-bottom">
          <div>© 2025 Heritage Pro (Pty) Ltd · Registered in Botswana · Reg. CO-2024/28110</div>
          <div style={{ display: "flex", gap: 20 }}>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Cookies</a>
          </div>
        </div>
      </div>
    </footer>
  );
}

/* ---------- Tweaks panel ---------- */
function TweaksPanel({ on, onClose, hero, setHero, accent, setAccent }) {
  if (!on) return null;
  return (
    <div className={`tweaks-panel on`}>
      <h5>Tweaks <span className="close" onClick={onClose}>✕</span></h5>
      <div className="tweaks-group">
        <label>Hero layout</label>
        <div className="tweaks-opts">
          {[["split", "Split — copy + screenshot"], ["centred", "Centred — copy above screenshot"], ["dark", "Dark — full-bleed gradient"]].map(([v, l]) => (
            <label key={v}><input type="radio" name="hero" checked={hero === v} onChange={() => setHero(v)}/> {l}</label>
          ))}
        </div>
      </div>
      <div className="tweaks-group">
        <label>Accent colour</label>
        <div className="tweaks-opts">
          {[["indigo", "Indigo (Heritage default)"], ["emerald", "Emerald"], ["amber", "Amber"]].map(([v, l]) => (
            <label key={v}><input type="radio" name="accent" checked={accent === v} onChange={() => setAccent(v)}/> {l}</label>
          ))}
        </div>
      </div>
    </div>
  );
}

/* ---------- App ---------- */
const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "hero": "split",
  "accent": "indigo"
}/*EDITMODE-END*/;

function App() {
  const { Nav, Hero, LogoStrip, Stats, Products } = window.HPSite1;
  const { Features, Testimonials, Cases } = window.HPSite2;
  const [hero, setHero] = useS3(TWEAK_DEFAULTS.hero);
  const [accent, setAccent] = useS3(TWEAK_DEFAULTS.accent);
  const [tweaksOn, setTweaksOn] = useS3(false);

  React.useEffect(() => {
    const root = document.documentElement;
    const map = {
      indigo: ["#434DB0", "#363FA0", "#5964C7", "#ECEEF9", "#252C6E"],
      emerald: ["#2AA870", "#166E49", "#4EC392", "#E6F5EC", "#0E4F34"],
      amber: ["#E69414", "#A8791B", "#F3B552", "#FFF3DD", "#7A5712"],
    };
    const [c500, c600, c400, c50, c900] = map[accent];
    root.style.setProperty("--brand-indigo-500", c500);
    root.style.setProperty("--brand-indigo-600", c600);
    root.style.setProperty("--brand-indigo-400", c400);
    root.style.setProperty("--brand-indigo-50", c50);
    root.style.setProperty("--brand-indigo-900", c900);
    root.style.setProperty("--brand-gradient", `linear-gradient(135deg, ${c500} 0%, ${c600} 100%)`);
    window.parent.postMessage({ type: "__edit_mode_set_keys", edits: { hero, accent } }, "*");
  }, [hero, accent]);

  React.useEffect(() => {
    function onMsg(e) {
      if (e.data && e.data.type === "__activate_edit_mode") setTweaksOn(true);
      if (e.data && e.data.type === "__deactivate_edit_mode") setTweaksOn(false);
    }
    window.addEventListener("message", onMsg);
    window.parent.postMessage({ type: "__edit_mode_available" }, "*");
    return () => window.removeEventListener("message", onMsg);
  }, []);

  return (
    <>
      <Nav/>
      <Hero variant={hero}/>
      <LogoStrip/>
      <Products/>
      <Stats/>
      <Features/>
      <Testimonials/>
      <Cases/>
      <Pricing/>
      <FAQ/>
      <Blog/>
      <Contact/>
      <Footer/>
      <TweaksPanel on={tweaksOn} onClose={() => setTweaksOn(false)} hero={hero} setHero={setHero} accent={accent} setAccent={setAccent}/>
    </>
  );
}

window.HPSite3 = { Pricing, FAQ, Blog, Contact, Footer, TweaksPanel, App };
