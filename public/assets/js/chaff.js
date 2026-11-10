// Hero cards: clicking a card bursts copies of its icon outward like chaff.
(function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const COUNT = 14; // icons per burst

    const rand = (min, max) => min + Math.random() * (max - min);

    function burst(card, x, y) {
        const icon  = card.querySelector('.resource-icon svg');
        const color = getComputedStyle(card.querySelector('.resource-icon')).color;

        // Little squish on the card itself (uses `scale` so its tilt is kept)
        card.animate(
            [{ scale: 1 }, { scale: 0.95 }, { scale: 1.03 }, { scale: 1 }],
            { duration: 350, easing: 'ease-out' }
        );

        for (let i = 0; i < COUNT; i++) {
            const bit = icon.cloneNode(true);
            const size = rand(14, 28);
            bit.classList.add('chaff');
            bit.style.width = bit.style.height = size + 'px';
            bit.style.left = (x - size / 2) + 'px';
            bit.style.top  = (y - size / 2) + 'px';
            bit.style.color = color;
            document.body.appendChild(bit);

            // Fly out (mostly upward), then drift down and fade
            const angle = rand(-Math.PI, 0) + rand(-0.4, 0.4);
            const dist  = rand(70, 170);
            const dx = Math.cos(angle) * dist;
            const dy = Math.sin(angle) * dist;
            const spin = rand(-540, 540);

            bit.animate([
                { transform: 'translate(0, 0) scale(0.3) rotate(0deg)', opacity: 1 },
                { transform: `translate(${dx}px, ${dy}px) scale(1) rotate(${spin * 0.6}deg)`, opacity: 1, offset: 0.45 },
                { transform: `translate(${dx * 1.25}px, ${dy + rand(90, 160)}px) scale(0.8) rotate(${spin}deg)`, opacity: 0 }
            ], {
                duration: rand(900, 1400),
                easing: 'cubic-bezier(0.2, 0.7, 0.3, 1)',
                fill: 'forwards'
            }).onfinish = () => bit.remove();
        }
    }

    document.querySelectorAll('.resource-card').forEach(card => {
        card.addEventListener('click', e => {
            burst(card, e.clientX, e.clientY);

            // Let the download link's burst show before leaving the page
            const link = e.target.closest('a');
            if (link) {
                e.preventDefault();
                setTimeout(() => { window.location.href = link.href; }, 500);
            }
        });
    });
})();
