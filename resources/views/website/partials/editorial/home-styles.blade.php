/* Heritage Pro — homepage section styles.
   Depends on the tokens and primitives in editorial/base-styles. */

/* ── Nav ───────────────────────────────────────────────────────── */

.hp-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    height: 76px;
    border-bottom: 1px solid var(--hp-line);
}

.hp-nav__group {
    display: flex;
    align-items: center;
    gap: 44px;
    min-width: 0;
}

.hp-nav__brand {
    display: inline-flex;
    align-items: center;
    gap: 11px;
    font-family: var(--hp-serif);
    font-size: 21px;
    font-weight: 600;
    letter-spacing: -0.015em;
    white-space: nowrap;
}

.hp-nav__brand img {
    width: 30px;
    height: 30px;
    flex: 0 0 auto;
}

.hp-nav__links {
    display: flex;
    gap: 28px;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: var(--hp-muted);
}

.hp-nav__links a {
    white-space: nowrap;
}

.hp-nav__links a.is-current {
    color: var(--hp-navy);
    font-weight: 600;
}

.hp-nav__actions {
    display: flex;
    align-items: center;
    gap: 22px;
}

.hp-nav__signin {
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: var(--hp-muted);
    white-space: nowrap;
}

@media (max-width: 1180px) {
    .hp-nav__group {
        gap: 28px;
    }

    .hp-nav__links {
        gap: 20px;
    }
}

@media (max-width: 1000px) {
    .hp-nav {
        height: auto;
        flex-wrap: wrap;
        padding-top: 16px;
        padding-bottom: 16px;
    }

    .hp-nav__links {
        order: 3;
        flex-basis: 100%;
        flex-wrap: wrap;
        gap: 16px 20px;
    }
}

@media (max-width: 560px) {
    .hp-nav__signin {
        display: none;
    }
}

/* ── Hero ──────────────────────────────────────────────────────── */

.hp-hero {
    position: relative;
    padding-top: var(--hp-section-gap);
    text-align: center;
    border-bottom: 1px solid var(--hp-line);
    overflow: hidden;
}

/* Constellation canvas sits behind the copy; the stage's own background
   covers the lower half of it, so the field reads as a top-of-page texture. */
.hp-hero__field {
    position: absolute;
    inset: 0;
    z-index: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.hp-hero__content,
.hp-hero__stage {
    position: relative;
    z-index: 1;
}

.hp-hero__actions {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 32px;
}

.hp-hero__lead {
    max-width: 60ch;
    margin: 24px auto 0;
    font-size: 17px;
    line-height: 1.62;
    color: var(--hp-muted);
}

.hp-hero__stage {
    margin: 64px calc(var(--hp-gutter) * -1) 0;
    padding: 44px var(--hp-gutter) 0;
    background: var(--hp-tint);
    border-top: 1px solid var(--hp-line);
}

/* ── Dashboard mock ────────────────────────────────────────────── */

.hp-board {
    max-width: 1020px;
    margin: 0 auto;
    background: var(--hp-surface);
    border: 1px solid var(--hp-line);
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    box-shadow: 0 30px 60px -40px rgba(23, 22, 20, 0.35);
    text-align: left;
    overflow: hidden;
}

.hp-board__bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--hp-line-soft);
}

.hp-board__title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 600;
}

.hp-board__dot {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: var(--hp-navy);
    flex: 0 0 auto;
}

.hp-board__tabs {
    display: flex;
    gap: 18px;
    font-size: 12px;
    color: var(--hp-muted-2);
}

.hp-board__tabs .is-active {
    color: var(--hp-navy);
    font-weight: 600;
}

.hp-board__kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    border-bottom: 1px solid var(--hp-line-soft);
}

.hp-board__kpi {
    padding: 20px;
    border-right: 1px solid var(--hp-line-soft);
}

.hp-board__kpi:last-child {
    border-right: 0;
}

.hp-board__kpi dt {
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--hp-muted-2);
}

.hp-board__kpi dd {
    margin: 6px 0 0;
    font-family: var(--hp-serif);
    font-size: 28px;
    line-height: 1.1;
}

.hp-board__note {
    font-size: 12px;
    color: var(--hp-muted-2);
}

.hp-board__note--up {
    color: var(--hp-navy);
}

.hp-board__note--flag {
    color: var(--hp-gold);
}

.hp-board__lower {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
}

.hp-board__chart {
    padding: 22px 20px 26px;
    border-right: 1px solid var(--hp-line-soft);
}

.hp-board__chart-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
}

.hp-board__chart-head strong {
    font-size: 12.5px;
    font-weight: 600;
}

.hp-board__chart-head span {
    font-size: 11px;
    color: var(--hp-muted-2);
}

.hp-board__bars {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    height: 128px;
    margin-top: 18px;
}

.hp-board__bar-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.hp-board__bar-fill {
    width: 100%;
    background: var(--hp-chart);
}

.hp-board__bar-col span {
    font-size: 11px;
    color: var(--hp-muted-2);
}

.hp-board__bar-col.is-current .hp-board__bar-fill {
    background: var(--hp-navy);
}

.hp-board__bar-col.is-current span {
    color: var(--hp-ink);
    font-weight: 600;
}

.hp-board__side {
    padding: 22px 20px 26px;
}

.hp-board__side > strong {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 14px;
}

.hp-board__flags {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin: 0;
    padding: 0;
    list-style: none;
    font-size: 12.5px;
    color: var(--hp-muted);
}

.hp-board__flags li {
    display: flex;
    gap: 10px;
}

.hp-board__flags li::before {
    content: '§';
    color: var(--hp-gold);
}

@media (max-width: 860px) {
    .hp-board__kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .hp-board__kpi:nth-child(2n) {
        border-right: 0;
    }

    .hp-board__kpi:nth-child(-n + 2) {
        border-bottom: 1px solid var(--hp-line-soft);
    }

    .hp-board__lower {
        grid-template-columns: minmax(0, 1fr);
    }

    .hp-board__chart {
        border-right: 0;
        border-bottom: 1px solid var(--hp-line-soft);
    }
}

/* ── Logo strip ────────────────────────────────────────────────── */

.hp-logos {
    padding-top: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid var(--hp-line);
    text-align: center;
}

.hp-logos__label {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--hp-muted-3);
}

/* Single-line marquee. The viewport bleeds past the section gutter to the full
   page width and fades at both edges, so names enter and leave rather than
   being clipped. */
.hp-logos__viewport {
    position: relative;
    margin: 18px calc(var(--hp-gutter) * -1) 0;
    overflow: hidden;
    -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 9%, #000 91%, transparent 100%);
    mask-image: linear-gradient(90deg, transparent 0, #000 9%, #000 91%, transparent 100%);
}

.hp-logos__track {
    display: flex;
    width: max-content;
    animation: hp-logos-scroll 38s linear infinite;
}

.hp-logos__viewport:hover .hp-logos__track,
.hp-logos__viewport:focus-within .hp-logos__track {
    animation-play-state: paused;
}

.hp-logos__set {
    display: flex;
    align-items: center;
    margin: 0;
    padding: 0;
    list-style: none;
}

.hp-logos__item {
    display: flex;
    align-items: center;
    white-space: nowrap;
}

.hp-logos__name {
    font-family: var(--hp-serif);
    font-size: 17px;
    line-height: 1.4;
    color: var(--hp-muted);
    transition: color 0.2s ease;
}

.hp-logos__item:hover .hp-logos__name {
    color: var(--hp-navy);
}

.hp-logos__sep {
    flex: 0 0 auto;
    width: 4px;
    height: 4px;
    margin: 0 32px;
    border-radius: 999px;
    background: var(--hp-gold-rule);
    opacity: 0.5;
}

@keyframes hp-logos-scroll {
    from {
        transform: translate3d(0, 0, 0);
    }

    to {
        transform: translate3d(-25%, 0, 0);
    }
}

/* No motion: drop the duplicates and show one static, scrollable row. */
@media (prefers-reduced-motion: reduce) {
    .hp-logos__viewport {
        overflow-x: auto;
        -webkit-mask-image: none;
        mask-image: none;
    }

    .hp-logos__track {
        width: 100%;
        justify-content: center;
        animation: none;
    }

    .hp-logos__set[aria-hidden='true'] {
        display: none;
    }

    .hp-logos__item:last-child .hp-logos__sep {
        display: none;
    }
}

@media (max-width: 760px) {
    .hp-logos__name {
        font-size: 15.5px;
    }

    .hp-logos__sep {
        margin: 0 22px;
    }
}

/* ── Editions ──────────────────────────────────────────────────── */

.hp-editions__grid {
    margin-top: 52px;
}

.hp-edition__title {
    margin: 12px 0 0;
    font-family: var(--hp-serif);
    font-size: 24px;
    font-weight: 600;
    letter-spacing: -0.015em;
}

.hp-edition__body {
    margin: 12px 0 22px;
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--hp-muted);
}

.hp-card--navy .hp-edition__body {
    color: var(--hp-on-navy-body);
}

.hp-editions__grid .hp-link {
    margin-top: 24px;
}

/* ── Stats ─────────────────────────────────────────────────────── */

.hp-stats {
    margin-top: var(--hp-section-gap);
    padding-top: 56px;
    padding-bottom: 56px;
}

.hp-stats__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 32px;
    text-align: center;
}

.hp-stats__item + .hp-stats__item {
    border-left: 1px solid var(--hp-line);
}

.hp-stats__value {
    font-family: var(--hp-serif);
    font-size: clamp(38px, 4.2vw, 52px);
    line-height: 1;
    letter-spacing: -0.03em;
}

.hp-stats__label {
    margin-top: 12px;
    font-size: 13.5px;
    color: var(--hp-muted-2);
}

@media (max-width: 860px) {
    .hp-stats__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 28px;
    }

    .hp-stats__item + .hp-stats__item {
        border-left: 0;
    }
}

/* ── Capability rows ───────────────────────────────────────────── */

.hp-split {
    display: grid;
    grid-template-columns: 0.95fr 1fr;
    gap: 60px;
    align-items: center;
    margin-top: 64px;
}

.hp-split + .hp-split {
    margin-top: 72px;
}

.hp-split--flip {
    grid-template-columns: 1fr 0.95fr;
}

.hp-split__copy p {
    margin: 16px 0 0;
    font-size: 15.5px;
    line-height: 1.65;
    color: var(--hp-muted);
}

.hp-points {
    display: flex;
    flex-direction: column;
    margin-top: 26px;
}

.hp-points > * {
    padding: 14px 0;
    border-top: 1px solid var(--hp-line);
    font-size: 14.5px;
    line-height: 1.5;
}

.hp-points > *:last-child {
    border-bottom: 1px solid var(--hp-line);
}

.hp-points strong {
    font-weight: 600;
}

.hp-points span {
    color: var(--hp-muted);
}

@media (max-width: 960px) {
    .hp-split,
    .hp-split--flip {
        grid-template-columns: minmax(0, 1fr);
        gap: 36px;
    }

    /* Keep the copy above its screenshot on narrow viewports. */
    .hp-split--flip .hp-split__copy {
        order: -1;
    }
}

/* ── Product panels ────────────────────────────────────────────── */

.hp-panel {
    background: var(--hp-surface);
    border: 1px solid var(--hp-line);
    border-radius: 6px;
    overflow: hidden;
}

.hp-panel__bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--hp-line-soft);
    font-size: 13px;
    font-weight: 600;
}

.hp-panel__bar em {
    font-style: normal;
    font-size: 11.5px;
    font-weight: 400;
    color: var(--hp-muted-2);
}

.hp-panel__bar em.is-flagged {
    color: var(--hp-gold);
}

.hp-panel__tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--hp-line-soft);
    font-size: 12.5px;
    color: var(--hp-muted-2);
}

.hp-panel__tabs .is-active {
    color: var(--hp-navy);
    font-weight: 600;
}

.hp-panel__body {
    padding: 20px;
}

.hp-facts {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin: 0;
}

.hp-facts--divided {
    padding-bottom: 18px;
    border-bottom: 1px solid var(--hp-line-soft);
}

.hp-facts--capped {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid var(--hp-line-soft);
}

.hp-facts dt {
    font-size: 11px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--hp-muted-2);
}

.hp-facts dd {
    margin: 4px 0 0;
    font-family: var(--hp-serif);
    font-size: 20px;
}

.hp-timeline__title {
    margin-top: 18px;
    font-size: 12.5px;
    font-weight: 600;
}

.hp-timeline {
    display: flex;
    flex-direction: column;
    margin: 12px 0 0;
    padding: 0;
    list-style: none;
}

.hp-timeline li {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 0;
    border-top: 1px solid var(--hp-line-softer);
    font-size: 13px;
}

.hp-timeline time {
    color: var(--hp-muted-2);
    white-space: nowrap;
}

/* Report card + transcript tables */
.hp-table {
    width: 100%;
    border-collapse: collapse;
}

.hp-table th {
    padding: 12px 0;
    border-bottom: 1px solid var(--hp-line-soft);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--hp-muted-2);
    text-align: left;
}

.hp-table td {
    padding: 11px 0;
    border-bottom: 1px solid var(--hp-line-softer);
    font-size: 13.5px;
}

/* Narrow columns shrink to their content; the first column absorbs the rest. */
.hp-table .hp-table__num {
    width: 1%;
    padding-left: 18px;
    white-space: nowrap;
}

.hp-table .hp-table__grade {
    font-weight: 600;
}

.hp-table .hp-table__soft {
    color: var(--hp-muted-2);
}

.hp-report__head {
    text-align: center;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--hp-line-soft);
}

.hp-report__school {
    font-family: var(--hp-serif);
    font-size: 17px;
    font-weight: 600;
}

.hp-report__kind {
    margin-top: 4px;
    font-size: 11.5px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--hp-muted-2);
}

.hp-report__total {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding-top: 14px;
    font-size: 13.5px;
}

.hp-report__total span {
    color: var(--hp-muted-2);
}

.hp-report__total strong {
    font-family: var(--hp-serif);
    font-size: 18px;
    font-weight: 400;
}

.hp-report__comment {
    margin-top: 18px;
    padding: 14px 16px;
    background: var(--hp-tint);
    border-radius: 4px;
    font-family: var(--hp-serif);
    font-size: 13.5px;
    font-style: italic;
    color: var(--hp-muted);
}

/* ── Module index ──────────────────────────────────────────────── */

.hp-modules {
    margin-top: var(--hp-section-gap);
    padding-top: 64px;
    padding-bottom: 64px;
}

.hp-modules__inner {
    display: grid;
    grid-template-columns: 380px minmax(0, 1fr);
    gap: 60px;
}

.hp-modules__inner .hp-lead {
    margin-top: 16px;
    font-size: 15.5px;
}

.hp-modules__inner .hp-link {
    margin-top: 18px;
}

.hp-modules__cols {
    column-count: 3;
    column-gap: 36px;
    font-size: 13.5px;
}

.hp-modgroup {
    break-inside: avoid;
    margin-bottom: 22px;
}

.hp-modgroup__title {
    padding-bottom: 8px;
    border-bottom: 1px solid #DCDCE8;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--hp-muted-3);
}

.hp-modgroup ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.hp-modgroup li {
    padding: 7px 0;
}

@media (max-width: 1080px) {
    .hp-modules__inner {
        grid-template-columns: minmax(0, 1fr);
        gap: 36px;
    }

    .hp-modules__cols {
        column-count: 2;
    }
}

@media (max-width: 620px) {
    .hp-modules__cols {
        column-count: 1;
    }
}

/* ── Testimonials ──────────────────────────────────────────────── */

.hp-voices {
    text-align: center;
}

.hp-voices__title {
    max-width: 34ch;
    margin-left: auto;
    margin-right: auto;
}

.hp-quote {
    max-width: 68ch;
    margin: 44px auto 0;
    font-family: var(--hp-serif);
    font-size: clamp(21px, 2.3vw, 27px);
    line-height: 1.38;
    letter-spacing: -0.014em;
}

.hp-cite {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 26px;
}

.hp-cite__avatar {
    width: 36px;
    height: 36px;
    border-radius: 999px;
    background: #E4E4EF;
    flex: 0 0 auto;
}

.hp-cite__meta {
    text-align: left;
}

.hp-cite__name {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
}

.hp-cite__role {
    display: block;
    font-size: 13px;
    color: var(--hp-muted-2);
}

.hp-voices__grid {
    margin-top: 52px;
    text-align: left;
}

.hp-pullquote {
    padding: 28px 30px;
    background: var(--hp-surface);
    border: 1px solid var(--hp-line);
    border-radius: 6px;
}

.hp-pullquote p {
    font-family: var(--hp-serif);
    font-size: 19px;
    line-height: 1.45;
}

.hp-pullquote blockquote {
    margin: 0;
}

.hp-pullquote figcaption {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--hp-line-soft);
    font-size: 13px;
}

.hp-pullquote figcaption span {
    color: var(--hp-muted-2);
}

/* ── Deployments ───────────────────────────────────────────────── */

.hp-cases {
    margin-top: 36px;
}

.hp-case__title {
    margin: 12px 0 0;
    font-family: var(--hp-serif);
    font-size: 23px;
    line-height: 1.24;
    font-weight: 600;
    letter-spacing: -0.015em;
}

.hp-case__metrics {
    display: flex;
    gap: 32px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--hp-line);
}

.hp-case__value {
    font-family: var(--hp-serif);
    font-size: 26px;
}

.hp-case__caption {
    font-size: 12px;
    color: var(--hp-muted-2);
}

/* ── Pricing ───────────────────────────────────────────────────── */

.hp-pricing {
    margin-top: var(--hp-section-gap);
    padding-top: 72px;
    padding-bottom: 72px;
}

.hp-pricing__grid {
    margin-top: 48px;
    align-items: start;
}

.hp-plan {
    padding: 34px 32px 36px;
    background: var(--hp-bg);
    border: 1px solid var(--hp-line);
    border-radius: 6px;
}

.hp-plan--featured {
    background: var(--hp-navy);
    border-color: var(--hp-navy);
    color: var(--hp-on-navy);
}

.hp-plan__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.hp-plan__name {
    font-family: var(--hp-serif);
    font-size: 18px;
    font-weight: 600;
}

.hp-plan__badge {
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--hp-gold-soft);
}

.hp-plan__price {
    margin-top: 16px;
    font-family: var(--hp-serif);
    font-size: clamp(34px, 3.6vw, 44px);
    line-height: 1.1;
    letter-spacing: -0.028em;
}

.hp-plan__unit {
    font-size: 13px;
    color: var(--hp-muted-2);
}

.hp-plan--featured .hp-plan__unit {
    color: var(--hp-on-navy-muted);
}

.hp-plan__body {
    margin: 16px 0 0;
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--hp-muted);
}

.hp-plan--featured .hp-plan__body {
    color: var(--hp-on-navy-body);
}

.hp-plan .hp-ruled {
    margin-top: 22px;
}

.hp-plan .hp-btn {
    margin-top: 26px;
    height: 46px;
    font-size: 14px;
}

/* ── FAQ ───────────────────────────────────────────────────────── */

.hp-faq__intro {
    margin-bottom: 48px;
}

.hp-faq {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 40px 56px;
}

.hp-faq p {
    margin: 10px 0 0;
    font-size: 14.5px;
    line-height: 1.65;
    color: var(--hp-muted);
}

@media (max-width: 860px) {
    .hp-faq {
        grid-template-columns: minmax(0, 1fr);
        gap: 32px;
    }
}

/* ── Journal ───────────────────────────────────────────────────── */

.hp-journal {
    margin-top: 36px;
    gap: 32px;
}

.hp-post__media {
    height: 170px;
    border-radius: 4px;
    background: var(--hp-placeholder);
    overflow: hidden;
}

.hp-post__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hp-post__media--mark {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--hp-navy);
    color: var(--hp-on-navy);
    font-family: var(--hp-serif);
    font-size: 22px;
    font-weight: 600;
    letter-spacing: -0.015em;
}

.hp-post__meta {
    margin-top: 16px;
}

.hp-post__title {
    margin: 10px 0 0;
    font-family: var(--hp-serif);
    font-size: 21px;
    line-height: 1.3;
    font-weight: 600;
    letter-spacing: -0.012em;
}

/* ── Resellers ─────────────────────────────────────────────────── */

.hp-resellers__grid {
    margin-top: 48px;
}

.hp-reseller {
    padding: 30px 30px 32px;
}

.hp-reseller__name {
    margin: 12px 0 0;
    font-family: var(--hp-serif);
    font-size: 24px;
    font-weight: 600;
    letter-spacing: -0.015em;
}

.hp-reseller .hp-ruled {
    margin-top: 20px;
}

.hp-reseller .hp-link {
    margin-top: 22px;
}

.hp-callout {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px 32px;
    margin-top: 28px;
    padding: 22px 28px;
    background: var(--hp-tint);
    border-radius: 6px;
}

.hp-callout p {
    font-size: 14.5px;
    color: var(--hp-muted);
}

.hp-callout .hp-btn {
    height: 42px;
    padding: 0 20px;
    font-size: 14px;
}

/* ── Team ──────────────────────────────────────────────────────── */

.hp-team {
    display: grid;
    grid-template-columns: 360px minmax(0, 1fr);
    gap: 60px;
}

.hp-team__counts {
    display: flex;
    margin-top: 26px;
    padding-top: 20px;
    border-top: 1px solid var(--hp-line);
}

.hp-team__count {
    flex: 1;
}

.hp-team__count strong {
    display: block;
    font-family: var(--hp-serif);
    font-size: 30px;
    font-weight: 400;
    line-height: 1;
}

.hp-team__count span {
    display: block;
    margin-top: 4px;
    font-size: 13px;
    color: var(--hp-muted-2);
}

.hp-roster {
    width: 100%;
    border-collapse: collapse;
}

.hp-roster th {
    padding-bottom: 12px;
    border-bottom: 1px solid var(--hp-ink);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--hp-muted-3);
    text-align: left;
}

.hp-roster td {
    padding: 22px 0;
    border-bottom: 1px solid var(--hp-line);
    vertical-align: baseline;
}

.hp-roster__index {
    width: 44px;
    font-family: var(--hp-mono);
    font-size: 12px;
    color: var(--hp-gold);
}

.hp-roster__name {
    font-family: var(--hp-serif);
    font-size: 25px;
    font-weight: 600;
    letter-spacing: -0.015em;
    padding-right: 24px;
}

.hp-roster__position {
    font-size: 14.5px;
    font-weight: 500;
    padding-right: 24px;
}

.hp-roster__discipline {
    font-size: 14px;
    color: var(--hp-muted);
}

@media (max-width: 960px) {
    .hp-team {
        grid-template-columns: minmax(0, 1fr);
        gap: 36px;
    }
}

@media (max-width: 620px) {
    .hp-roster thead {
        display: none;
    }

    .hp-roster tr {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        padding: 18px 0;
        border-bottom: 1px solid var(--hp-line);
    }

    .hp-roster td {
        padding: 0;
        border-bottom: 0;
    }

    .hp-roster__index {
        grid-row: span 3;
    }

    .hp-roster__name {
        font-size: 21px;
    }

    .hp-roster__position {
        margin-top: 4px;
    }
}

/* ── Demo ──────────────────────────────────────────────────────── */

.hp-demo {
    margin-top: var(--hp-section-gap);
    padding-top: 72px;
    padding-bottom: 72px;
}

.hp-demo__inner {
    display: grid;
    grid-template-columns: 1fr 1.05fr;
    gap: 64px;
    align-items: start;
}

.hp-demo__title {
    margin: 18px 0 0;
    font-family: var(--hp-serif);
    font-size: clamp(34px, 4.4vw, 46px);
    line-height: 1.08;
    letter-spacing: -0.022em;
    font-weight: 400;
}

.hp-demo__lead {
    max-width: 44ch;
}

.hp-demo .hp-ruled {
    margin-top: 32px;
    font-size: 15px;
}

.hp-demo .hp-ruled > * {
    padding: 14px 0;
}

.hp-demo__phone {
    margin-top: 28px;
    font-size: 14px;
    color: var(--hp-on-navy-body);
}

.hp-demo__phone a {
    color: var(--hp-on-navy);
    border-bottom: 1px solid rgba(251, 249, 244, 0.4);
}

/* Form */
.hp-form {
    padding: 34px 34px 36px;
    background: var(--hp-bg);
    color: var(--hp-ink);
    border-radius: 8px;
}

.hp-form__title {
    font-family: var(--hp-serif);
    font-size: 20px;
    font-weight: 600;
}

.hp-form__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 22px;
}

.hp-form__row {
    margin-top: 16px;
}

.hp-field label {
    display: block;
    margin-bottom: 7px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--hp-muted-2);
}

.hp-field input,
.hp-field select,
.hp-field textarea {
    width: 100%;
    padding: 0 14px;
    height: 44px;
    background: var(--hp-surface);
    border: 1px solid var(--hp-line);
    border-radius: 4px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.16s ease, box-shadow 0.16s ease;
}

.hp-field textarea {
    height: auto;
    padding: 12px 14px;
    resize: vertical;
}

.hp-field select {
    appearance: none;
    padding-right: 34px;
    background-image: linear-gradient(45deg, transparent 50%, var(--hp-muted-2) 50%),
        linear-gradient(135deg, var(--hp-muted-2) 50%, transparent 50%);
    background-position: calc(100% - 18px) 20px, calc(100% - 13px) 20px;
    background-size: 5px 5px, 5px 5px;
    background-repeat: no-repeat;
}

.hp-field input:focus,
.hp-field select:focus,
.hp-field textarea:focus {
    border-color: var(--hp-navy);
    box-shadow: 0 0 0 3px rgba(35, 33, 96, 0.1);
}

.hp-field input::placeholder,
.hp-field textarea::placeholder {
    color: #9C9CB2;
}

.hp-field__error {
    margin-top: 6px;
    font-size: 12px;
    line-height: 1.5;
    color: #A4243B;
}

.hp-field input.is-invalid,
.hp-field select.is-invalid,
.hp-field textarea.is-invalid {
    border-color: rgba(164, 36, 59, 0.45);
}

.hp-form .hp-btn {
    margin-top: 20px;
    height: 48px;
    font-weight: 600;
}

.hp-form__note {
    margin-top: 12px;
    font-size: 12.5px;
    color: var(--hp-muted-2);
}

.hp-alert {
    margin-bottom: 18px;
    padding: 12px 14px;
    border-radius: 4px;
    border: 1px solid rgba(35, 33, 96, 0.18);
    background: #EEEEF8;
    color: var(--hp-navy);
    font-size: 13px;
    line-height: 1.6;
}

.hp-alert--error {
    border-color: rgba(164, 36, 59, 0.22);
    background: #FBF0F2;
    color: #8C1D33;
}

@media (max-width: 960px) {
    .hp-demo__inner {
        grid-template-columns: minmax(0, 1fr);
        gap: 40px;
    }
}

@media (max-width: 560px) {
    .hp-form {
        padding: 26px 22px 28px;
    }

    .hp-form__grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

/* ── Footer ────────────────────────────────────────────────────── */

.hp-footer {
    padding-top: 56px;
    padding-bottom: 32px;
}

.hp-footer__grid {
    display: grid;
    grid-template-columns: 1.5fr repeat(4, minmax(0, 1fr));
    gap: 32px;
}

.hp-footer__brand {
    font-family: var(--hp-serif);
    font-size: 20px;
    font-weight: 600;
    letter-spacing: -0.015em;
}

.hp-footer__blurb {
    max-width: 32ch;
    margin: 14px 0 0;
    font-size: 13.5px;
    line-height: 1.6;
    color: var(--hp-muted-2);
}

.hp-footer__col {
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-size: 13.5px;
    color: var(--hp-muted);
}

.hp-footer__col .hp-label {
    margin-bottom: 2px;
}

.hp-footer__base {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px 22px;
    margin-top: 44px;
    padding-top: 20px;
    border-top: 1px solid var(--hp-line);
    font-size: 12.5px;
    color: var(--hp-muted-2);
}

.hp-footer__legal {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
}

@media (max-width: 1080px) {
    .hp-footer__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 620px) {
    .hp-footer__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 28px;
    }
}
