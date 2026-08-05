@push('page-scripts')
    <script>
        /*
         * Hero constellation: a sparse field of drifting nodes joined by hairlines,
         * drawn behind the hero copy. The pointer parts the field and draws its own
         * links in gold. Deliberately low-contrast — the headline has to stay the
         * loudest thing on the page.
         */
        (() => {
            const host = document.querySelector('[data-constellation-host]');
            const canvas = document.querySelector('[data-constellation]');

            if (!host || !canvas) {
                return;
            }

            const ctx = canvas.getContext('2d');

            if (!ctx) {
                return;
            }

            const INK = '35, 33, 96';
            const GOLD = '192, 138, 60';
            const LINK_RANGE = 132;
            const POINTER_RANGE = 178;
            const AREA_PER_NODE = 15000;
            const MIN_NODES = 24;
            const MAX_NODES = 70;

            // Opacity ceilings — the dials to turn if the field needs to be more or
            // less present. Keep them low enough that the headline still leads.
            const LINK_ALPHA = 0.2;
            const POINTER_ALPHA = 0.36;
            const NODE_ALPHA = 0.38;
            const ACCENT_ALPHA = 0.62;

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            const pointer = { x: 0, y: 0, active: false, ease: 0 };

            let width = 0;
            let height = 0;
            let nodes = [];
            let frame = null;
            let onScreen = true;

            const random = (min, max) => min + Math.random() * (max - min);

            function measure() {
                const rect = host.getBoundingClientRect();
                const dpr = Math.min(window.devicePixelRatio || 1, 2);

                width = rect.width;
                height = rect.height;
                canvas.width = Math.round(width * dpr);
                canvas.height = Math.round(height * dpr);
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                ctx.lineWidth = 1;
            }

            function seed() {
                const count = Math.max(MIN_NODES, Math.min(MAX_NODES, Math.round((width * height) / AREA_PER_NODE)));

                nodes = Array.from({ length: count }, (_, index) => {
                    const vx = random(-0.14, 0.14);
                    const vy = random(-0.14, 0.14);
                    const accent = index % 7 === 0;

                    // baseVx/baseVy is the drift the node always eases back to, so a
                    // pointer nudge decays instead of accumulating.
                    return { x: random(0, width), y: random(0, height), vx, vy, baseVx: vx, baseVy: vy, accent };
                });
            }

            function advance() {
                pointer.ease += ((pointer.active ? 1 : 0) - pointer.ease) * 0.07;

                for (const node of nodes) {
                    if (pointer.ease > 0.01) {
                        const dx = node.x - pointer.x;
                        const dy = node.y - pointer.y;
                        const distance = Math.hypot(dx, dy);

                        if (distance > 0.5 && distance < POINTER_RANGE) {
                            const push = (1 - distance / POINTER_RANGE) * 0.045 * pointer.ease;
                            node.vx += (dx / distance) * push;
                            node.vy += (dy / distance) * push;
                        }
                    }

                    node.vx += (node.baseVx - node.vx) * 0.02;
                    node.vy += (node.baseVy - node.vy) * 0.02;

                    node.x += node.vx;
                    node.y += node.vy;

                    if (node.x < -24) {
                        node.x = width + 24;
                    } else if (node.x > width + 24) {
                        node.x = -24;
                    }

                    if (node.y < -24) {
                        node.y = height + 24;
                    } else if (node.y > height + 24) {
                        node.y = -24;
                    }
                }
            }

            function draw() {
                ctx.clearRect(0, 0, width, height);

                for (let i = 0; i < nodes.length; i += 1) {
                    for (let j = i + 1; j < nodes.length; j += 1) {
                        const a = nodes[i];
                        const b = nodes[j];
                        const dx = a.x - b.x;
                        const dy = a.y - b.y;
                        const squared = dx * dx + dy * dy;

                        if (squared > LINK_RANGE * LINK_RANGE) {
                            continue;
                        }

                        const alpha = (1 - Math.sqrt(squared) / LINK_RANGE) * LINK_ALPHA;

                        ctx.strokeStyle = `rgba(${INK}, ${alpha.toFixed(3)})`;
                        ctx.beginPath();
                        ctx.moveTo(a.x, a.y);
                        ctx.lineTo(b.x, b.y);
                        ctx.stroke();
                    }
                }

                if (pointer.ease > 0.01) {
                    for (const node of nodes) {
                        const distance = Math.hypot(node.x - pointer.x, node.y - pointer.y);

                        if (distance > POINTER_RANGE) {
                            continue;
                        }

                        const alpha = (1 - distance / POINTER_RANGE) * POINTER_ALPHA * pointer.ease;

                        ctx.strokeStyle = `rgba(${GOLD}, ${alpha.toFixed(3)})`;
                        ctx.beginPath();
                        ctx.moveTo(node.x, node.y);
                        ctx.lineTo(pointer.x, pointer.y);
                        ctx.stroke();
                    }
                }

                for (const node of nodes) {
                    ctx.fillStyle = node.accent ? `rgba(${GOLD}, ${ACCENT_ALPHA})` : `rgba(${INK}, ${NODE_ALPHA})`;
                    ctx.beginPath();
                    ctx.arc(node.x, node.y, node.accent ? 2.3 : 1.7, 0, Math.PI * 2);
                    ctx.fill();
                }
            }

            function tick() {
                advance();
                draw();
                frame = window.requestAnimationFrame(tick);
            }

            function start() {
                if (frame === null && onScreen && !document.hidden && !reduceMotion.matches) {
                    frame = window.requestAnimationFrame(tick);
                }
            }

            function stop() {
                if (frame !== null) {
                    window.cancelAnimationFrame(frame);
                    frame = null;
                }
            }

            function rebuild() {
                measure();
                seed();
                draw();
            }

            rebuild();

            if (reduceMotion.matches) {
                return; // A single static frame: texture without movement.
            }

            host.addEventListener('pointermove', (event) => {
                if (event.pointerType !== 'mouse') {
                    return;
                }

                const rect = host.getBoundingClientRect();
                pointer.x = event.clientX - rect.left;
                pointer.y = event.clientY - rect.top;
                pointer.active = true;
            });

            host.addEventListener('pointerleave', () => {
                pointer.active = false;
            });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stop();
                } else {
                    start();
                }
            });

            if ('IntersectionObserver' in window) {
                new IntersectionObserver((entries) => {
                    onScreen = entries[0].isIntersecting;

                    if (onScreen) {
                        start();
                    } else {
                        stop();
                    }
                }).observe(host);
            }

            if ('ResizeObserver' in window) {
                let resizeTimer = null;

                new ResizeObserver(() => {
                    window.clearTimeout(resizeTimer);
                    resizeTimer = window.setTimeout(rebuild, 200);
                }).observe(host);
            }

            reduceMotion.addEventListener('change', (event) => {
                if (event.matches) {
                    stop();
                    rebuild();
                } else {
                    start();
                }
            });

            start();
        })();
    </script>
@endpush
