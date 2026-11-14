// Page transitions from the landing page.
//
// Login / Register (and between them): clicking opens the next page immediately. The click position is saved,
// and the next page reveals itself in a growing circle from that spot (see the <head> script in login.php / register.php).
//
// Student library (/student...): first a cursor drops from the top of the screen to the centre and "clicks",
// then the library opens in the same growing circle from the centre (see the <head> script in student_layout.php).
// Styles for the cursor are in assets/css/page-transition.css.
(function () {
    function sameOriginPath(a) {
        if (!a || !a.href) return null;
        const url = new URL(a.href, location.href);
        return url.origin === location.origin ? url.pathname : null;
    }

    // Only links that go to the login or register pages
    function isAuthLink(a) {
        const path = sameOriginPath(a);
        return path !== null && /\/(login|register)\/?$/.test(path);
    }

    // Links into the student library
    function isLibraryLink(a) {
        const path = sameOriginPath(a);
        return path !== null && /\/student(\/|$)/.test(path);
    }

    // Save where the click was, as a fraction of the screen
    function saveOrigin(x, y) {
        try {
            sessionStorage.setItem('nexusWipe', JSON.stringify({ x: x / innerWidth, y: y / innerHeight }));
        } catch (e) { /* storage blocked: the next page just appears normally */ }
    }

    // For scripts that navigate on their own (e.g. the hero card download button)
    window.nexusWipeTo = function (href, x, y) {
        saveOrigin(x, y);
        location.href = href;
    };

    const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ---- Cursor intro for the library ----------------------------------
    let playing = false;

    const CURSOR_SVG =
        '<svg viewBox="0 0 24 24" aria-hidden="true">' +
        '<path d="M3 2l17 9.2-7.4 1.6 4.3 7.9-3 1.6-4.3-7.9L4.4 19z" fill="#0C3347" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/>' +
        '</svg>';

    function playCursorThen(href) {
        if (playing) return;
        playing = true;

        // Start loading the library while the cursor moves
        const prefetch = document.createElement('link');
        prefetch.rel = 'prefetch';
        prefetch.href = href;
        document.head.appendChild(prefetch);

        const x = innerWidth / 2;
        const y = innerHeight / 2;

        const layer = document.createElement('div');
        layer.className = 'nx-cursor-layer';
        layer.setAttribute('aria-hidden', 'true');
        layer.style.setProperty('--x', x + 'px');
        layer.style.setProperty('--y', y + 'px');
        layer.innerHTML =
            '<span class="nx-ring nx-ring-fill"></span><span class="nx-ring"></span>' +
            '<span class="nx-cursor">' + CURSOR_SVG + '</span>';
        document.body.appendChild(layer);

        const cursor = layer.querySelector('.nx-cursor');
        const fill = layer.querySelector('.nx-ring-fill');
        const ring = layer.querySelector('.nx-ring:not(.nx-ring-fill)');

        requestAnimationFrame(() => layer.classList.add('is-dim'));

        // 1) Drop in from above the screen (slightly off to the side) to the centre
        const startY = -(y + 60);
        const drop = cursor.animate([
            { transform: `translate(${Math.min(140, innerWidth * 0.15)}px, ${startY}px) rotate(-14deg)`, opacity: 0 },
            { opacity: 1, offset: 0.15 },
            { transform: 'translate(0, 0) rotate(0deg)', opacity: 1 }
        ], { duration: 750, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'forwards' });

        drop.finished.then(() => {
            // 2) Click: press down + back up, with rings spreading from the tip
            const press = cursor.animate([
                { transform: 'scale(1)' },
                { transform: 'scale(0.78)', offset: 0.45 },
                { transform: 'scale(1)' }
            ], { duration: 260, easing: 'ease-out', fill: 'forwards' });

            fill.animate([
                { transform: 'scale(0.2)', opacity: 0.9 },
                { transform: 'scale(2.4)', opacity: 0 }
            ], { duration: 420, easing: 'ease-out', fill: 'forwards' });

            ring.animate([
                { transform: 'scale(0.4)', opacity: 1 },
                { transform: 'scale(4)', opacity: 0 }
            ], { duration: 520, easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)', fill: 'forwards' });

            return press.finished;
        }).then(() => {
            // 3) Open the library; it reveals itself in a circle from the click point
            window.nexusWipeTo(href, x, y);
        });

        // If the user comes back with the Back button, remove the overlay
        window.addEventListener('pageshow', function onShow(e) {
            if (!e.persisted) return;
            layer.remove();
            playing = false;
            window.removeEventListener('pageshow', onShow);
        });
    }

    // ---- Click handling ------------------------------------------------
    document.addEventListener('click', e => {
        // Another script already handles this click (e.g. chaff.js on the hero card calls nexusWipeTo itself)
        if (e.defaultPrevented) return;
        const a = e.target.closest('a');
        const toAuth = isAuthLink(a);
        const toLibrary = !toAuth && isLibraryLink(a);
        if (!toAuth && !toLibrary) return;
        // New-tab / new-window clicks: nothing to animate
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || a.target === '_blank') return;

        if (toLibrary) {
            if (reduceMotion() || !document.body.animate) return; // just follow the link
            e.preventDefault();
            playCursorThen(a.href);
            return;
        }

        // Keyboard "clicks" have no position: use the link's centre instead
        let x = e.clientX, y = e.clientY;
        if (!x && !y) {
            const r = a.getBoundingClientRect();
            x = r.left + r.width / 2;
            y = r.top + r.height / 2;
        }
        saveOrigin(x, y);
        // No preventDefault: the browser starts loading the page right away
    });
})();
