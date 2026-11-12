// Landing page → Login / Register (and between Login and Register):
// clicking opens the next page immediately. The click position is saved, and the next page
// reveals itself in a growing circle from that spot (see the <head> script in login.php / register.php).
(function () {
    // Only links that go to the login or register pages
    function isAuthLink(a) {
        if (!a || !a.href) return false;
        const url = new URL(a.href, location.href);
        return url.origin === location.origin && /\/(login|register)\/?$/.test(url.pathname);
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

    document.addEventListener('click', e => {
        const a = e.target.closest('a');
        if (!isAuthLink(a)) return;
        // New-tab / new-window clicks: nothing to animate
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || a.target === '_blank') return;

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
