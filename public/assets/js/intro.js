// Landing page intro:
// Play the SC/CICS trace GIF once on a navy screen, then fade it out to reveal the page.
(function () {
    const root = document.documentElement;
    if (!root.classList.contains('intro-active')) return;

    const intro = document.getElementById('intro');
    const gif   = document.getElementById('introGif');

    const GIF_LENGTH_MS = 5280; // total length of SC-CICS-intro-once.gif
    const HOLD_MS       = 400;  // short pause on the last frame
    const FADE_MS       = 800;  // must match the transition in landing_index.css

    let finished = false;
    let timer = null;

    function done() {
        root.classList.remove('intro-active');
        intro.remove();
    }

    function fadeOut() {
        if (finished) return;
        finished = true;
        clearTimeout(timer);
        intro.classList.add('is-leaving');
        setTimeout(done, FADE_MS);
    }

    // Load the GIF into a fresh copy so it always starts from the first frame
    fetch(gif.dataset.src)
        .then(r => { if (!r.ok) throw new Error(r.status); return r.blob(); })
        .then(blob => {
            gif.onload = () => { timer = setTimeout(fadeOut, GIF_LENGTH_MS + HOLD_MS); };
            gif.src = URL.createObjectURL(blob);
        })
        .catch(done); // if the GIF can't load, just show the page

    // Click or press any key to skip
    intro.addEventListener('click', fadeOut);
    document.addEventListener('keydown', fadeOut, { once: true });
})();
