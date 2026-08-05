:root {
    --precision-porcelain: #F7F8FC;
    --precision-paper: #FFFFFF;
    --precision-midnight: #111827;
    --precision-slate: #5C667B;
    --precision-indigo: #434DB0;
    --precision-indigo-dark: #343D98;
    --precision-lime: #B7F36B;
    --precision-line: #DDE2EC;
}

body {
    background: var(--precision-porcelain);
}

.precision-container {
    width: min(100% - 64px, 1440px);
    margin-inline: auto;
}

.nav {
    background: rgba(247, 248, 252, 0.94);
    border-bottom-color: rgba(17, 24, 39, 0.08);
    backdrop-filter: blur(18px);
}

.nav .container {
    max-width: 1440px;
}

.nav-inner {
    min-height: 78px;
    padding-block: 14px;
}

.nav-logo {
    font-size: 18px;
    letter-spacing: -0.03em;
}

.nav-logo img {
    width: 36px;
    height: 36px;
}

.nav-links {
    gap: 24px;
}

.nav-links a {
    position: relative;
    color: #4B5568;
    font-size: 12px;
    font-weight: 600;
}

.nav-links a::after {
    content: '';
    position: absolute;
    right: 0;
    bottom: -8px;
    left: 0;
    height: 2px;
    background: var(--precision-indigo);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 180ms var(--ease-out);
}

.nav-links a:hover {
    text-decoration: none;
}

.nav-links a:hover::after,
.nav-links a.active-route::after {
    transform: scaleX(1);
    transform-origin: left;
}

.nav .btn {
    border-radius: 9px;
}

.nav .btn-primary {
    background: var(--precision-midnight);
}

.nav .btn-primary:hover {
    background: var(--precision-indigo);
}

.precision-hero {
    position: relative;
    overflow: hidden;
    min-height: calc(100svh - 78px);
    padding: 78px 0 92px;
    background:
        radial-gradient(circle at 79% 18%, rgba(67, 77, 176, 0.12), transparent 26%),
        var(--precision-porcelain);
}

.precision-hero-grid {
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0.45;
    background-image:
        linear-gradient(rgba(17, 24, 39, 0.045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(17, 24, 39, 0.045) 1px, transparent 1px);
    background-size: 76px 76px;
    mask-image: linear-gradient(90deg, transparent 0, #000 45%, #000 100%);
}

.precision-hero-layout {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 0.82fr) minmax(620px, 1.18fr);
    gap: clamp(52px, 5vw, 92px);
    align-items: center;
}

.precision-hero-copy {
    animation: precisionHeroEnter 720ms var(--ease-out) both;
}

.precision-signal,
.precision-kicker {
    color: var(--precision-indigo);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.precision-signal {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 24px;
}

.precision-signal span {
    position: relative;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--precision-lime);
    box-shadow: 0 0 0 5px rgba(183, 243, 107, 0.2);
}

.precision-signal span::after {
    content: '';
    position: absolute;
    inset: -5px;
    border: 1px solid rgba(75, 105, 35, 0.26);
    border-radius: inherit;
    animation: precisionPulse 2.4s ease-out infinite;
}

.precision-hero h1 {
    max-width: 680px;
    margin: 0;
    color: var(--precision-midnight);
    font-size: clamp(54px, 5vw, 78px);
    font-weight: 800;
    letter-spacing: -0.055em;
    line-height: 0.98;
}

.precision-hero-copy > p {
    max-width: 620px;
    margin: 28px 0 0;
    color: var(--precision-slate);
    font-size: 17px;
    line-height: 1.72;
}

.precision-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 32px;
}

.precision-button {
    display: inline-flex;
    min-height: 52px;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 13px 22px;
    border: 1px solid transparent;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 750;
    transition: transform 180ms var(--ease-out), box-shadow 180ms var(--ease-out), background 180ms var(--ease-out), border-color 180ms var(--ease-out);
}

.precision-button:hover {
    text-decoration: none;
    transform: translateY(-2px);
}

.precision-button-primary {
    background: var(--precision-midnight);
    color: #FFFFFF;
    box-shadow: 0 16px 32px -18px rgba(17, 24, 39, 0.8);
}

.precision-button-primary:hover {
    background: var(--precision-indigo);
    color: #FFFFFF;
    box-shadow: 0 18px 34px -16px rgba(67, 77, 176, 0.64);
}

.precision-button-secondary {
    border-color: #CDD4E0;
    background: rgba(255, 255, 255, 0.7);
    color: var(--precision-midnight);
}

.precision-button-secondary:hover {
    border-color: var(--precision-indigo);
    color: var(--precision-indigo);
}

.precision-button:focus-visible,
.precision-text-link:focus-visible,
.precision-phone:focus-within {
    outline: 3px solid rgba(67, 77, 176, 0.28);
    outline-offset: 4px;
}

.precision-proof {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    max-width: 610px;
    margin: 54px 0 0;
    padding: 22px 0 0;
    border-top: 1px solid var(--precision-line);
}

.precision-proof div {
    padding-right: 22px;
}

.precision-proof div + div {
    padding-left: 22px;
    border-left: 1px solid var(--precision-line);
}

.precision-proof dt {
    margin: 0 0 6px;
    color: var(--precision-midnight);
    font-family: var(--font-display);
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.035em;
    line-height: 1;
}

.precision-proof dd {
    margin: 0;
    color: #7A8496;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.11em;
    text-transform: uppercase;
}

.precision-product-stage {
    position: relative;
    min-width: 0;
    padding: 28px 0 54px 38px;
    animation: precisionStageEnter 820ms 80ms var(--ease-out) both;
}

.precision-product-stage::before {
    content: '';
    position: absolute;
    top: 0;
    right: -6vw;
    bottom: 20px;
    left: 0;
    border-radius: 28px 0 0 28px;
    background: linear-gradient(145deg, rgba(67, 77, 176, 0.12), rgba(255, 255, 255, 0.48));
    border: 1px solid rgba(67, 77, 176, 0.12);
}

.precision-stage-status {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    margin: 0 28px 14px 0;
    color: #687287;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.precision-stage-status span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.precision-stage-status i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--precision-lime);
    box-shadow: 0 0 0 4px rgba(183, 243, 107, 0.18);
}

.precision-dashboard-frame {
    position: relative;
    z-index: 2;
    overflow: hidden;
    min-height: 590px;
    border: 1px solid rgba(17, 24, 39, 0.12);
    border-radius: 16px;
    background: #FFFFFF;
    box-shadow: 0 42px 90px -42px rgba(17, 24, 39, 0.55), 0 18px 40px -28px rgba(67, 77, 176, 0.46);
    transform: perspective(1600px) rotateY(-2.4deg) rotateX(0.8deg);
    transform-origin: left center;
    transition: transform 360ms var(--ease-out), box-shadow 360ms var(--ease-out);
}

.precision-product-stage:hover .precision-dashboard-frame {
    transform: perspective(1600px) rotateY(-0.7deg) translateY(-4px);
    box-shadow: 0 54px 100px -44px rgba(17, 24, 39, 0.58), 0 24px 48px -30px rgba(67, 77, 176, 0.52);
}

.precision-window-chrome {
    height: 38px !important;
    background: #111827 !important;
    border-bottom: 0 !important;
}

.precision-window-chrome > span {
    width: 8px !important;
    height: 8px !important;
    background: #596274 !important;
}

.precision-window-chrome > span:first-child {
    background: var(--precision-lime) !important;
}

.precision-window-chrome .url {
    max-width: 360px !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: rgba(255, 255, 255, 0.06) !important;
    color: #AEB7C7 !important;
}

.precision-dashboard-frame .mini-dash {
    grid-template-columns: 184px minmax(0, 1fr);
    min-height: 552px;
    padding: 18px;
    gap: 18px;
}

.precision-dashboard-frame .mini-side {
    padding: 14px;
    border-radius: 12px;
    background: #111827;
}

.precision-dashboard-frame .mini-side-brand b,
.precision-dashboard-frame .mini-side-brand b span {
    color: #FFFFFF;
}

.precision-dashboard-frame .mini-tile {
    background: var(--precision-indigo);
}

.precision-dashboard-frame .mini-nav-item {
    color: #AEB7C7;
    font-size: 9px;
}

.precision-dashboard-frame .mini-nav-item.active {
    background: rgba(67, 77, 176, 0.28);
    color: #FFFFFF;
}

.precision-dashboard-frame .mini-main {
    min-width: 0;
}

.precision-dashboard-frame .mini-h {
    color: var(--precision-midnight);
    font-size: 16px;
}

.precision-dashboard-frame .mini-stat {
    border-color: #E1E6EF;
    box-shadow: none;
}

.precision-dashboard-frame .mini-chart .bar::after {
    background: var(--precision-indigo);
}

.precision-photo-anchor {
    position: absolute;
    z-index: 4;
    right: -18px;
    bottom: -4px;
    width: 196px;
    margin: 0;
    overflow: hidden;
    border: 8px solid #FFFFFF;
    border-radius: 15px;
    background: #FFFFFF;
    box-shadow: 0 22px 56px -24px rgba(17, 24, 39, 0.7);
    transform: rotate(2.2deg);
    transition: transform 260ms var(--ease-out);
}

.precision-photo-anchor:hover {
    transform: rotate(0) translateY(-5px);
}

.precision-photo-anchor img {
    width: 100%;
    height: 206px;
    object-fit: cover;
    object-position: center 47%;
    border-radius: 9px;
}

.precision-photo-anchor figcaption {
    padding: 12px 8px 8px;
}

.precision-photo-anchor figcaption span,
.precision-photo-anchor figcaption small {
    display: block;
}

.precision-photo-anchor figcaption span {
    color: var(--precision-midnight);
    font-size: 10px;
    font-weight: 750;
    line-height: 1.45;
}

.precision-photo-anchor figcaption small {
    margin-top: 4px;
    color: #8992A4;
    font-size: 8px;
}

.logo-strip {
    border-top: 1px solid #E4E8EF;
    border-bottom: 1px solid #E4E8EF;
    background: #FFFFFF;
}

.precision-story {
    padding: 126px 0;
    background: #FFFFFF;
}

.precision-story-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.12fr) minmax(430px, 0.88fr);
    gap: clamp(64px, 7vw, 118px);
    align-items: center;
}

.precision-story-photo {
    position: relative;
    margin: 0;
}

.precision-story-photo::before {
    content: '';
    position: absolute;
    top: -20px;
    right: 24px;
    left: -22px;
    height: 100%;
    border: 1px solid rgba(67, 77, 176, 0.22);
    border-radius: 18px;
}

.precision-story-photo img {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
    border-radius: 18px;
    filter: saturate(0.9) contrast(1.04);
}

.precision-story-photo figcaption {
    margin-top: 12px;
    color: #8A93A3;
    font-size: 10px;
}

.precision-story-copy h2 {
    max-width: 600px;
    margin: 18px 0 22px;
    color: var(--precision-midnight);
    font-size: clamp(40px, 4vw, 60px);
    font-weight: 800;
    letter-spacing: -0.045em;
    line-height: 1.02;
}

.precision-story-copy > p {
    max-width: 620px;
    color: #616B7D;
    font-size: 16px;
    line-height: 1.75;
}

.precision-story-list {
    margin-top: 38px;
    border-top: 1px solid var(--precision-line);
}

.precision-story-list > div {
    display: grid;
    grid-template-columns: 160px minmax(0, 1fr);
    gap: 26px;
    padding: 20px 0;
    border-bottom: 1px solid var(--precision-line);
}

.precision-story-list strong {
    color: var(--precision-midnight);
    font-size: 13px;
}

.precision-story-list span {
    color: #6F7889;
    font-size: 12px;
    line-height: 1.6;
}

.precision-text-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 30px;
    color: var(--precision-indigo);
    font-size: 13px;
    font-weight: 750;
}

.precision-text-link:hover {
    text-decoration: none;
}

.precision-text-link svg {
    transition: transform 180ms var(--ease-out);
}

.precision-text-link:hover svg {
    transform: translateX(4px);
}

.products {
    background: var(--precision-porcelain);
}

.products .container {
    max-width: 1320px;
}

.products-grid {
    gap: 0 !important;
    margin-top: 66px !important;
    border-top: 1px solid var(--precision-line);
    border-bottom: 1px solid var(--precision-line);
}

.product-card {
    border: 0 !important;
    border-right: 1px solid var(--precision-line) !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    transform: none !important;
}

.product-card:last-child {
    border-right: 0 !important;
}

.product-card:hover {
    background: #FFFFFF !important;
}

.product-card .tile {
    border-radius: 11px !important;
    background: var(--precision-midnight) !important;
}

.precision-mobile {
    position: relative;
    overflow: hidden;
    padding: 128px 0 110px;
    background: var(--precision-midnight);
    color: #FFFFFF;
}

.precision-mobile::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0.18;
    pointer-events: none;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
    background-size: 88px 88px;
    mask-image: linear-gradient(180deg, #000 0, transparent 82%);
}

.precision-mobile .precision-container {
    position: relative;
    z-index: 1;
}

.precision-mobile .precision-kicker {
    color: var(--precision-lime);
}

.precision-mobile-heading {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(420px, 0.7fr);
    gap: clamp(54px, 8vw, 124px);
    align-items: end;
}

.precision-mobile-heading h2 {
    max-width: 760px;
    margin-top: 18px;
    color: #FFFFFF;
    font-size: clamp(46px, 5vw, 72px);
    font-weight: 800;
    letter-spacing: -0.05em;
    line-height: 0.98;
}

.precision-mobile-heading p {
    max-width: 620px;
    margin: 0;
    color: #B9C0CD;
    font-size: 16px;
    line-height: 1.72;
}

.precision-mobile-benefits {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 24px;
}

.precision-mobile-benefits span {
    padding: 8px 11px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 999px;
    color: #D9DEE7;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.04em;
}

.precision-phone-rail {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: clamp(18px, 2.2vw, 34px);
    align-items: start;
    margin-top: 82px;
    padding: 0 24px 34px;
}

.precision-phone {
    position: relative;
    margin: 0;
    transition: transform 260ms var(--ease-out);
}

.precision-phone-raised {
    transform: translateY(-24px);
}

.precision-phone:hover {
    transform: translateY(-10px);
}

.precision-phone-raised:hover {
    transform: translateY(-34px);
}

.precision-phone img {
    width: 100%;
    border: 8px solid #FFFFFF;
    border-radius: 30px;
    background: #FFFFFF;
    box-shadow: 0 38px 80px -38px rgba(0, 0, 0, 0.9);
}

.precision-phone figcaption {
    margin-top: 16px;
    color: #CBD1DC;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-align: center;
    text-transform: uppercase;
}

.features {
    background: #FFFFFF;
}

.contact {
    background: linear-gradient(135deg, #111827 0%, #222B66 55%, #434DB0 100%);
}

.contact-form {
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 30px 60px -36px rgba(0, 0, 0, 0.7);
}

.footer {
    background: #0B1020;
}

html[data-theme="dark"] body {
    background: #0B1020;
}

html[data-theme="dark"] .nav {
    background: rgba(11, 16, 32, 0.94);
}

html[data-theme="dark"] .precision-hero {
    background:
        radial-gradient(circle at 79% 18%, rgba(67, 77, 176, 0.24), transparent 28%),
        #0B1020;
}

html[data-theme="dark"] .precision-hero-grid {
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
}

html[data-theme="dark"] .precision-hero h1,
html[data-theme="dark"] .precision-proof dt,
html[data-theme="dark"] .precision-story-copy h2,
html[data-theme="dark"] .precision-story-list strong {
    color: #F7F9FF;
}

html[data-theme="dark"] .precision-hero-copy > p,
html[data-theme="dark"] .precision-story-copy > p,
html[data-theme="dark"] .precision-story-list span {
    color: #AEB8CB;
}

html[data-theme="dark"] .precision-product-stage::before {
    background: linear-gradient(145deg, rgba(67, 77, 176, 0.24), rgba(18, 26, 50, 0.5));
    border-color: rgba(170, 181, 255, 0.14);
}

html[data-theme="dark"] .logo-strip,
html[data-theme="dark"] .precision-story,
html[data-theme="dark"] .features {
    background: #121A32;
}

html[data-theme="dark"] .precision-story-photo::before,
html[data-theme="dark"] .precision-story-list,
html[data-theme="dark"] .precision-story-list > div,
html[data-theme="dark"] .precision-proof,
html[data-theme="dark"] .precision-proof div + div {
    border-color: rgba(148, 163, 184, 0.2);
}

html[data-theme="dark"] .products {
    background: #0B1020;
}

html[data-theme="dark"] .product-card:hover {
    background: #121A32 !important;
}

@keyframes precisionHeroEnter {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes precisionStageEnter {
    from {
        opacity: 0;
        transform: translateX(28px) scale(0.985);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

@keyframes precisionPulse {
    0% {
        opacity: 0.7;
        transform: scale(0.7);
    }
    70%, 100% {
        opacity: 0;
        transform: scale(1.7);
    }
}

@media (max-width: 1240px) {
    .precision-hero-layout {
        grid-template-columns: 1fr;
    }

    .precision-hero-copy {
        max-width: 900px;
    }

    .precision-product-stage {
        width: min(100%, 980px);
        margin-inline: auto;
    }

    .precision-story-layout {
        grid-template-columns: minmax(0, 1fr) minmax(380px, 0.86fr);
        gap: 64px;
    }

    .precision-mobile-heading {
        grid-template-columns: 1fr 0.82fr;
        gap: 56px;
    }
}

@media (max-width: 900px) {
    .precision-container {
        width: min(100% - 40px, 720px);
    }

    .precision-hero {
        min-height: auto;
        padding: 64px 0 78px;
    }

    .precision-hero h1 {
        font-size: clamp(48px, 9vw, 66px);
    }

    .precision-product-stage {
        padding-left: 0;
    }

    .precision-product-stage::before {
        right: -24px;
        left: -24px;
        border-radius: 24px;
    }

    .precision-dashboard-frame {
        min-height: 520px;
        transform: none;
    }

    .precision-dashboard-frame .mini-dash {
        grid-template-columns: 156px minmax(0, 1fr);
        min-height: 482px;
    }

    .precision-photo-anchor {
        right: -4px;
        width: 164px;
    }

    .precision-photo-anchor img {
        height: 164px;
    }

    .precision-story {
        padding: 92px 0;
    }

    .precision-story-layout,
    .precision-mobile-heading {
        grid-template-columns: 1fr;
    }

    .precision-story-copy {
        max-width: 680px;
    }

    .precision-phone-rail {
        display: flex;
        overflow-x: auto;
        gap: 20px;
        margin-right: -20px;
        margin-left: -20px;
        padding: 24px 20px 36px;
        scroll-padding-inline: 20px;
        scroll-snap-type: x mandatory;
        scrollbar-width: thin;
    }

    .precision-phone {
        flex: 0 0 min(68vw, 310px);
        scroll-snap-align: center;
    }

    .precision-phone-raised,
    .precision-phone-raised:hover {
        transform: none;
    }

    .product-card {
        border-right: 0 !important;
        border-bottom: 1px solid var(--precision-line) !important;
    }

    .product-card:last-child {
        border-bottom: 0 !important;
    }
}

@media (max-width: 620px) {
    .precision-container {
        width: min(100% - 28px, 560px);
    }

    .precision-hero {
        padding-top: 48px;
    }

    .precision-signal {
        align-items: flex-start;
        line-height: 1.5;
    }

    .precision-hero h1 {
        font-size: clamp(41px, 12vw, 56px);
        line-height: 1.01;
    }

    .precision-hero-copy > p {
        font-size: 15px;
    }

    .precision-button {
        width: 100%;
    }

    .precision-proof {
        margin-top: 42px;
    }

    .precision-proof div,
    .precision-proof div + div {
        padding-right: 10px;
        padding-left: 10px;
    }

    .precision-proof div:first-child {
        padding-left: 0;
    }

    .precision-proof dt {
        font-size: 21px;
    }

    .precision-proof dd {
        font-size: 8px;
    }

    .precision-product-stage {
        margin-top: 18px;
        padding-bottom: 20px;
    }

    .precision-stage-status {
        margin-right: 0;
        font-size: 8px;
    }

    .precision-dashboard-frame {
        min-height: 386px;
        border-radius: 13px;
    }

    .precision-window-chrome {
        height: 32px !important;
    }

    .precision-dashboard-frame .mini-dash {
        grid-template-columns: 1fr;
        min-height: 354px;
        padding: 14px;
    }

    .precision-dashboard-frame .mini-side {
        display: none;
    }

    .precision-dashboard-frame .mini-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .precision-photo-anchor {
        display: none;
    }

    .precision-story-photo::before {
        top: -10px;
        right: 10px;
        left: -10px;
    }

    .precision-story-copy h2,
    .precision-mobile-heading h2 {
        font-size: clamp(39px, 11vw, 52px);
    }

    .precision-story-list > div {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .precision-mobile {
        padding: 92px 0 72px;
    }

    .precision-mobile-heading {
        gap: 28px;
    }

    .precision-phone-rail {
        margin-top: 44px;
    }

    .precision-phone {
        flex-basis: min(76vw, 292px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .precision-hero-copy,
    .precision-product-stage,
    .precision-signal span::after {
        animation: none;
    }

    .precision-dashboard-frame,
    .precision-photo-anchor,
    .precision-phone,
    .precision-button,
    .precision-text-link svg {
        transition: none;
    }
}
