// Landing page motion:
// - Hero photo scrolls slower than the page; headline and cards drift at different speeds.
// - Hero cards follow the mouse at different depths; ID badges tilt under the mouse.
// - Every section has parallax layers (data-speed / data-speed-x / data-bg-speed).
// - Elements with data-anim appear when they scroll into view and disappear when they leave.
// - How it works and Quality check replay their sequences each time they come back into view.
(function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const root      = document.documentElement;
    const hero      = document.querySelector('.hero');
    const heroBg    = document.querySelector('.hero-bg');
    const layers    = document.querySelectorAll('[data-parallax]');
    const heroCopy  = document.querySelector('.hero-copy');
    const isDesktop = window.matchMedia('(min-width: 992px)');
    const isTablet  = window.matchMedia('(min-width: 768px)'); // section parallax runs from here up
    const finePointer = window.matchMedia('(pointer: fine)').matches;

    root.classList.add('anim-ready'); // CSS only hides [data-anim] once this script is running

    // ---- Hero scroll parallax ----
    let heroTicking = false;

    function onHeroScroll() {
        if (heroTicking) return;
        heroTicking = true;
        requestAnimationFrame(() => {
            const y = window.scrollY;
            const h = hero.offsetHeight;

            if (y <= h) { // only work while the hero is on screen
                heroBg.style.transform = `translate3d(0, ${y * 0.55}px, 0)`;

                if (isDesktop.matches) {
                    layers.forEach(el => {
                        el.style.transform = `translate3d(0, ${y * parseFloat(el.dataset.parallax)}px, 0)`;
                    });
                    heroCopy.style.opacity = Math.max(0, 1 - y / (h * 0.8));
                } else {
                    layers.forEach(el => { el.style.transform = ''; });
                    heroCopy.style.opacity = '';
                }
            }
            heroTicking = false;
        });
    }

    window.addEventListener('scroll', onHeroScroll, { passive: true });
    window.addEventListener('resize', onHeroScroll);
    onHeroScroll();

    // ---- Mouse parallax on the hero cards (mouse/trackpad only) ----
    if (finePointer) {
        const cards = document.querySelectorAll('.card-layer');

        hero.addEventListener('mousemove', e => {
            const r = hero.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width  - 0.5; // -0.5 .. 0.5
            const y = (e.clientY - r.top)  / r.height - 0.5;
            cards.forEach(card => {
                const d = parseFloat(card.dataset.depth);
                card.style.transform = `translate3d(${-x * d}px, ${-y * d * 0.6}px, 0)`;
            });
        });

        hero.addEventListener('mouseleave', () => {
            cards.forEach(card => { card.style.transform = ''; });
        });
    }

    // ---- ID badges: tilt in 3D and catch the light under the mouse ----
    if (finePointer) {
        document.querySelectorAll('.id-badge').forEach(badge => {
            badge.addEventListener('mousemove', e => {
                const r = badge.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width;  // 0 .. 1
                const y = (e.clientY - r.top)  / r.height;
                badge.style.transform = `rotateY(${(x - 0.5) * 18}deg) rotateX(${(0.5 - y) * 14}deg)`;
                badge.style.setProperty('--shine-x', `${x * 100}%`);
                badge.style.setProperty('--shine-y', `${y * 100}%`);
            });
            badge.addEventListener('mouseleave', () => {
                badge.style.transform = '';
            });
        });
    }

    // ---- Section parallax ----
    // data-speed="n"    → moves up/down by n × the section's distance from screen centre
    // data-speed-x="n"  → same, but sideways
    // data-bg-speed="n" → the section's background slides by n × that distance
    // Movement is written to --px / --py, which CSS adds to any scroll-in offset.
    const speedEls = document.querySelectorAll('[data-speed], [data-speed-x]');
    const bgEls    = document.querySelectorAll('[data-bg-speed]');
    let speedTicking = false;

    function distanceFromCentre(el) {
        // Measure the section, not the moving element, so movement doesn't feed back into itself
        const r = (el.closest('section, footer') || el).getBoundingClientRect();
        if (r.bottom < -200 || r.top > window.innerHeight + 200) return null; // off screen
        return (r.top + r.height / 2) - window.innerHeight / 2;
    }

    function updateSectionParallax() {
        speedTicking = false;
        const on = isTablet.matches;

        speedEls.forEach(el => {
            const d = on ? distanceFromCentre(el) : 0;
            if (d === null) return;
            if (el.dataset.speedX) {
                el.style.setProperty('--px', `${d * parseFloat(el.dataset.speedX)}px`);
            } else {
                el.style.setProperty('--py', `${d * parseFloat(el.dataset.speed)}px`);
            }
        });

        bgEls.forEach(el => {
            const d = distanceFromCentre(el);
            if (d === null) return;
            el.style.backgroundPosition = `0 ${d * parseFloat(el.dataset.bgSpeed)}px`;
        });
    }

    // ---- Whole sections ease in and out with the scroll ----
    // As a section's content comes up from the bottom it rises, sharpens and grows to full size;
    // as it leaves past the top it shrinks, fades and blurs. Tied directly to scroll position.
    const sectionBodies = [...document.querySelectorAll('section > .container, footer > .container')];
    const EDGE = 0.35; // how much of the screen height the fade happens over

    function updateSectionFades() {
        const vh = window.innerHeight;
        const blurOn = isTablet.matches; // skip blur on phones to keep scrolling smooth

        sectionBodies.forEach(el => {
            const r = el.parentElement.getBoundingClientRect();
            if (r.bottom < -50 || r.top > vh + 50) return; // off screen

            const enter = Math.min(1, Math.max(0, (vh - r.top) / (vh * EDGE)));
            const exit  = Math.min(1, Math.max(0, r.bottom / (vh * EDGE)));
            const vis   = Math.min(enter, exit);                // 0 = gone, 1 = fully in
            const ease  = 1 - Math.pow(1 - vis, 2);             // ease-out so it settles softly
            const leaving = exit < enter;                       // leaving past the top vs coming in

            if (vis >= 0.999) {
                // fully in view: clear everything (keeps glass blur effects working)
                el.style.opacity = '';
                el.style.scale = '';
                el.style.filter = '';
                el.style.transformOrigin = '';
                return;
            }

            el.style.opacity = 0.15 + 0.85 * ease;
            el.style.scale = 0.9 + 0.1 * ease;
            el.style.transformOrigin = leaving ? '50% 0%' : '50% 100%';
            el.style.filter = blurOn ? `blur(${(1 - ease) * 6}px)` : '';
        });
    }

    function onSectionScroll() {
        if (speedTicking) return;
        speedTicking = true;
        requestAnimationFrame(() => {
            updateSectionParallax();
            updateSectionFades();
        });
    }

    window.addEventListener('scroll', onSectionScroll, { passive: true });
    window.addEventListener('resize', onSectionScroll);
    updateSectionParallax();
    updateSectionFades();

    // ---- Appear / disappear on scroll ----
    // data-stagger="s" on a parent gives each child an extra s-second delay.
    // data-stagger-start="t" adds a base delay; data-stagger-cols="n" restarts the count each row of n.
    document.querySelectorAll('[data-stagger]').forEach(parent => {
        const step  = parseFloat(parent.dataset.stagger);
        const start = parseFloat(parent.dataset.staggerStart || 0);
        const cols  = parseInt(parent.dataset.staggerCols || 0, 10);
        [...parent.children].forEach((child, i) => {
            const n = cols ? i % cols : i;
            child.style.setProperty('--delay', `${start + n * step}s`);
        });
    });

    const animObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            entry.target.classList.toggle('in-view', entry.isIntersecting);
            // slide-x leaves to the left when scrolled past the top, to the right when below the screen
            if (entry.target.dataset.anim === 'slide-x') {
                entry.target.classList.toggle('exit-left', !entry.isIntersecting && entry.boundingClientRect.top < 0);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll('[data-anim]').forEach(el => animObserver.observe(el));

    // Replays a section's sequence each time it scrolls back into view
    function playOnView(section, animClass, threshold, onPlay, onReset) {
        if (!section) return;
        section.classList.add(animClass);
        new IntersectionObserver(entries => {
            const visible = entries[0].isIntersecting;
            section.classList.toggle('is-playing', visible);
            if (visible) { onPlay && onPlay(); } else { onReset && onReset(); }
        }, { threshold }).observe(section);
    }

    // ---- How it works: steps come in, search bar types ----
    const how = document.querySelector('.how-section');
    const typed = how ? how.querySelector('.how-typed') : null;
    const fullText = typed ? typed.dataset.text : '';
    let typeStart = null, typeTick = null, caretOff = null;
    if (typed) typed.textContent = '';

    function stopTyping() {
        clearTimeout(typeStart);
        clearInterval(typeTick);
        clearTimeout(caretOff);
        if (typed) {
            typed.textContent = '';
            typed.classList.remove('is-typing');
        }
    }

    function typeSearch() {
        if (!typed) return;
        stopTyping();
        typeStart = setTimeout(() => { // once step 1's card has slid in
            typed.classList.add('is-typing');
            let n = 0;
            typeTick = setInterval(() => {
                typed.textContent = fullText.slice(0, ++n);
                if (n >= fullText.length) {
                    clearInterval(typeTick);
                    caretOff = setTimeout(() => typed.classList.remove('is-typing'), 1500);
                }
            }, 85);
        }, 1100);
    }

    playOnView(how, 'how-animate', 0.3, typeSearch, stopTyping);

    // ---- Quality check: stages light up, the "4" counts up with them, then PUBLISHED is stamped ----
    const four = document.querySelector('.qc-four');
    let countTimers = [];

    function countUp() {
        if (!four) return;
        countTimers.forEach(clearTimeout);
        four.textContent = '0';
        // same timing as each stage lighting up (i × 0.45s + 0.3s)
        countTimers = [1, 2, 3, 4].map(n => setTimeout(() => {
            four.textContent = n;
            four.classList.remove('is-bumping');
            void four.offsetWidth; // restart the bump animation
            four.classList.add('is-bumping');
        }, ((n - 1) * 0.45 + 0.3) * 1000));
    }

    function resetCount() {
        countTimers.forEach(clearTimeout);
        if (four) four.textContent = '4';
    }

    playOnView(document.querySelector('.qc-section'), 'qc-animate', 0.3, countUp, resetCount);
})();
