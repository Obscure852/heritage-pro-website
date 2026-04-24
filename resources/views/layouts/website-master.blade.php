<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? config('heritage_website.meta.default_title') }}</title>
    <style>
        @include('layouts.website-base-styles')

        @media (min-width: 901px) {
          .hero .hero-inner {
            grid-template-columns: minmax(0, 1.02fr) minmax(0, 1.18fr) !important;
            gap: 52px !important;
          }

          .hero .hero-media {
            width: 100%;
            max-width: 760px;
            justify-self: end;
            transform: translateX(48px) rotate(-2.5deg);
            transform-origin: center center;
          }

          .hero .hero-media .window-chrome {
            height: 36px;
            padding: 0 16px;
          }

          .hero .hero-media .window-chrome .url {
            max-width: 360px;
            font-size: 11px;
          }

          .hero .mini-dash {
            grid-template-columns: 250px 1fr;
            gap: 20px;
            min-height: 590px;
            padding: 24px;
          }

          .hero .mini-side {
            padding: 16px;
            gap: 5px;
          }

          .hero .mini-side-brand {
            margin-bottom: 8px;
          }

          .hero .mini-nav-item {
            font-size: 11px;
            padding: 7px 10px;
          }
        }

        @media (max-width: 900px) {
          .hero .mini-dash {
            grid-template-columns: 1fr;
            min-height: auto;
          }

          #features-page-content .feature-stat-grid,
          #features-page-content .feature-mini-list {
            grid-template-columns: 1fr;
          }

          #team-page-content .team-grid {
            grid-template-columns: 1fr;
          }

          .logo-strip .fake-logo.heritage-logo-pill {
            min-width: max-content;
          }

          #customers-page-content .client-pill {
            min-width: 240px;
          }
        }

        .nav-links a.active-route {
          color: var(--brand-indigo-500) !important;
          font-weight: 700 !important;
        }

        .heritage-section-intro {
          max-width: 760px;
          margin: 0 auto 12px;
        }

        .heritage-section-intro-title {
          margin-top: 14px;
        }

        .heritage-section-intro .lead,
        #about-page-content .lead {
          max-width: 760px;
          margin-top: 16px;
          margin-left: auto;
          margin-right: auto;
        }

        .heritage-modules-intro-title {
          margin-top: 120px;
          margin-bottom: 14px;
          font-size: 31px;
        }

        .heritage-modules-intro-copy {
          max-width: 680px;
          margin: 0 auto;
        }

        #features-page-content .heritage-features-page,
        #customers-page-content .heritage-customers-page,
        #about-page-content .heritage-about-features,
        #about-page-content .heritage-about-cases {
          background: #F7F8FB;
        }

        #features-page-content .features-row {
          margin-top: 72px;
        }

        #features-page-content .heritage-window-chrome {
          height: 32px;
          padding: 0 14px;
          display: flex;
          gap: 6px;
          align-items: center;
          background: #F3F5F9;
          border-bottom: 1px solid var(--border-1);
        }

        #features-page-content .heritage-window-dot {
          width: 10px;
          height: 10px;
          border-radius: 50%;
          background: #D6DAE2;
        }

        #features-page-content .heritage-window-chrome .url {
          flex: 1;
          margin-left: 18px;
          padding: 4px 10px;
          max-width: 320px;
          border-radius: 6px;
          border: 1px solid var(--border-1);
          background: #fff;
          color: var(--fg-3);
          font-family: var(--font-mono);
          font-size: 10px;
        }

        #features-page-content .feature-surface {
          padding: 24px;
          display: grid;
          gap: 14px;
          background: linear-gradient(180deg, #FCFDFF 0%, #F3F6FF 100%);
        }

        #features-page-content .feature-panel {
          background: #fff;
          border: 1px solid var(--border-1);
          border-radius: 14px;
          padding: 16px 18px;
        }

        #features-page-content .feature-panel h4 {
          font-size: 14px;
          margin-bottom: 6px;
        }

        #features-page-content .feature-panel p {
          margin: 0;
          font-size: 12px;
          color: var(--fg-3);
          line-height: 1.6;
        }

        #features-page-content .feature-stat-grid {
          display: grid;
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: 12px;
        }

        #features-page-content .feature-stat {
          padding: 14px;
          border-radius: 12px;
          background: #fff;
          border: 1px solid var(--border-1);
        }

        #features-page-content .feature-stat b {
          display: block;
          margin-bottom: 6px;
          color: var(--brand-indigo-500);
          font-family: var(--font-display);
          font-size: 19px;
          line-height: 1;
        }

        #features-page-content .feature-stat span {
          display: block;
          color: var(--fg-3);
          font-size: 10px;
          text-transform: uppercase;
          letter-spacing: 0.08em;
        }

        #features-page-content .feature-chip-row {
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
          margin-top: 6px;
        }

        #features-page-content .feature-chip {
          padding: 8px 12px;
          border-radius: 999px;
          background: var(--brand-indigo-50);
          color: var(--brand-indigo-500);
          font-size: 11px;
          font-weight: 700;
          letter-spacing: 0.01em;
        }

        #features-page-content .feature-chip.alt {
          background: #ECFDF3;
          color: #166E49;
        }

        #features-page-content .feature-mini-list {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 12px;
        }

        .logo-strip .logo-marquee-shell,
        #customers-page-content .client-marquee {
          position: relative;
          overflow: hidden;
          margin-top: 48px;
        }

        .logo-strip .logo-marquee-shell {
          border: 0;
          border-radius: 0;
          background: transparent;
          box-shadow: none;
        }

        #customers-page-content .client-marquee {
          border: 0;
          border-radius: 0;
          background: transparent;
          box-shadow: none;
        }

        .logo-strip .logo-marquee-shell::before,
        .logo-strip .logo-marquee-shell::after,
        #customers-page-content .client-marquee::before,
        #customers-page-content .client-marquee::after {
          content: '';
          position: absolute;
          top: 0;
          bottom: 0;
          width: 60px;
          z-index: 2;
          pointer-events: none;
        }

        .logo-strip .logo-marquee-shell::before,
        #customers-page-content .client-marquee::before {
          left: 0;
          background: linear-gradient(90deg, #FFFFFF 0%, rgba(255, 255, 255, 0) 100%);
        }

        .logo-strip .logo-marquee-shell::after,
        #customers-page-content .client-marquee::after {
          right: 0;
          background: linear-gradient(270deg, #FFFFFF 0%, rgba(255, 255, 255, 0) 100%);
        }

        #customers-page-content .client-marquee::before {
          background: linear-gradient(90deg, #F7F8FB 0%, rgba(247, 248, 251, 0) 100%);
        }

        #customers-page-content .client-marquee::after {
          background: linear-gradient(270deg, #F7F8FB 0%, rgba(247, 248, 251, 0) 100%);
        }

        #customers-page-content .client-marquee-track {
          display: flex;
          align-items: center;
          gap: 18px;
          width: max-content;
          padding: 18px;
          flex-wrap: nowrap;
          justify-content: flex-start;
          animation: heritageClientMarquee 28s linear infinite;
        }

        .logo-strip .logo-row.heritage-logo-marquee-track {
          display: flex;
          align-items: center;
          gap: 36px;
          width: max-content;
          padding: 8px 0;
          flex-wrap: nowrap;
          justify-content: flex-start;
          animation: heritageClientMarquee 28s linear infinite;
        }

        #customers-page-content .client-pill {
          display: flex;
          align-items: center;
          gap: 12px;
          min-width: 280px;
          padding: 14px 18px;
          border-radius: 16px;
          border: 1px solid var(--border-1);
          background: #F7F8FB;
          white-space: nowrap;
          flex: 0 0 auto;
          opacity: 1;
        }

        .logo-strip .fake-logo.heritage-logo-pill {
          display: flex;
          align-items: center;
          gap: 12px;
          min-width: max-content;
          padding: 0;
          border: 0;
          border-radius: 0;
          background: transparent;
          white-space: nowrap;
          flex: 0 0 auto;
          opacity: 1;
        }

        #customers-page-content .client-pill {
          border: 0;
          border-radius: 0;
          background: transparent;
          box-shadow: none;
        }

        .logo-strip .fake-logo.heritage-logo-pill:hover {
          opacity: 1;
        }

        .logo-strip .fake-logo.heritage-logo-pill .mark,
        #customers-page-content .client-pill-mark {
          width: 40px;
          height: 40px;
          border-radius: 12px;
          background: var(--brand-indigo-500);
          color: #fff;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          font-family: var(--font-display);
          font-size: 12px;
          font-weight: 700;
          letter-spacing: 0.04em;
        }

        .logo-strip .fake-logo.heritage-logo-pill span,
        #customers-page-content .client-pill strong {
          display: block;
          color: var(--fg-1);
          font-size: 14px;
          line-height: 1.3;
          font-weight: 600;
        }

        #customers-page-content .client-pill span {
          display: block;
          margin-top: 3px;
          color: var(--fg-3);
          font-size: 11px;
          text-transform: uppercase;
          letter-spacing: 0.08em;
        }

        #customers-page-content .customer-showcase-grid,
        #about-page-content .cases-grid {
          margin-top: 48px;
        }

        #customers-page-content .case-card {
          min-height: 100%;
        }

        #team-page-content .team-grid {
          display: grid;
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: 24px;
          margin-top: 56px;
        }

        #team-page-content .team-card {
          display: grid;
          gap: 20px;
          padding: 26px;
          border-radius: 22px;
          border: 1px solid var(--border-1);
          background: #fff;
          box-shadow: var(--shadow-sm);
        }

        #team-page-content .team-card.lead {
          background: linear-gradient(180deg, #FCFDFF 0%, #F2F5FF 100%);
        }

        #team-page-content .team-card.ops {
          background: linear-gradient(180deg, #FCFDFD 0%, #F0FBF7 100%);
        }

        #team-page-content .team-card.dev {
          background: linear-gradient(180deg, #FFFFFF 0%, #F7F8FB 100%);
        }

        #team-page-content .team-card-head {
          display: flex;
          align-items: center;
          gap: 16px;
        }

        #team-page-content .team-avatar {
          width: 56px;
          height: 56px;
          border-radius: 18px;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          color: #fff;
          font-family: var(--font-display);
          font-size: 17px;
          font-weight: 700;
          letter-spacing: 0.04em;
          background: var(--brand-gradient);
        }

        #team-page-content .team-card.ops .team-avatar {
          background: linear-gradient(135deg, #2AA870 0%, #166E49 100%);
        }

        #team-page-content .team-card.dev .team-avatar {
          background: linear-gradient(135deg, #5964C7 0%, #363FA0 100%);
        }

        #team-page-content .team-meta span {
          display: block;
          font-size: 11px;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.08em;
          color: var(--brand-indigo-500);
          margin-bottom: 6px;
        }

        #team-page-content .team-meta h3 {
          margin: 0;
          font-size: 23px;
        }

        #team-page-content .team-card p {
          margin: 0;
          color: var(--fg-3);
          font-size: 14px;
          line-height: 1.7;
        }

        #team-page-content .team-focus {
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
        }

        #team-page-content .team-pill {
          display: inline-flex;
          align-items: center;
          padding: 8px 12px;
          border-radius: 999px;
          background: var(--bg-subtle);
          color: var(--fg-2);
          font-size: 11px;
          font-weight: 700;
          letter-spacing: 0.02em;
        }

        .faq-item {
          cursor: pointer;
        }

        .faq-q {
          width: 100%;
          background: transparent;
          border: 0;
          padding: 0;
          text-align: left;
          cursor: pointer;
        }

        .contact-feedback {
          display: grid;
          gap: 10px;
          margin-bottom: 18px;
        }

        .contact-alert {
          padding: 12px 14px;
          border-radius: 12px;
          font-size: 12px;
          line-height: 1.6;
          border: 1px solid rgba(67, 77, 176, 0.18);
          background: #eef1ff;
          color: #2f377f;
        }

        .contact-alert.error {
          border-color: rgba(193, 53, 75, 0.18);
          background: #fff1f3;
          color: #9f1239;
        }

        .form-field .field-error {
          font-size: 11px;
          color: #b42318;
          line-height: 1.5;
        }

        .form-field input.is-invalid,
        .form-field select.is-invalid,
        .form-field textarea.is-invalid {
          border-color: rgba(180, 35, 24, 0.35);
          box-shadow: 0 0 0 3px rgba(180, 35, 24, 0.08);
        }

        @keyframes heritageClientMarquee {
          from {
            transform: translateX(0);
          }

          to {
            transform: translateX(-50%);
          }
        }
    </style>
</head>
<body>
    @yield('content')

    <script>
        document.querySelectorAll('[data-faq-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const item = toggle.closest('.faq-item');
                const answer = item ? item.querySelector('.faq-a') : null;

                if (!item) {
                    return;
                }

                item.classList.toggle('open');

                if (answer) {
                    answer.hidden = !item.classList.contains('open');
                }
            });
        });

        if (document.querySelector('.contact-alert, .field-error')) {
            const contactSection = document.getElementById('contact');

            if (contactSection && window.location.hash !== '#contact') {
                window.setTimeout(() => {
                    contactSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 120);
            }
        }
    </script>
</body>
</html>
