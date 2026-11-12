<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading | NEXUS Student Council</title>

    <!-- If JavaScript is off, still move on to the dashboard -->
    <noscript><meta http-equiv="refresh" content="1;url=<?= base_url('dashboard') ?>"></noscript>

    <link rel="stylesheet" href="<?= base_url('assets/css/council-loading.css') ?>">
</head>
<body>
    <main class="sc-loading" style="--bg: url('<?= base_url('assets/images/SC-BG.jpg') ?>')">
        <!-- Coin with the SC badge on both faces; flips slowly, then the page moves on -->
        <div class="badge-stage">
            <div class="badge-coin" id="badgeCoin">
                <div class="badge-face badge-front">
                    <img src="<?= base_url('assets/images/CICS-SC-logo.png') ?>" alt="CICS Student Council">
                </div>
                <div class="badge-face badge-back" aria-hidden="true">
                    <img src="<?= base_url('assets/images/CICS-SC-logo.png') ?>" alt="">
                </div>
            </div>
            <div class="badge-shadow" aria-hidden="true"></div>
        </div>

        <p class="visually-hidden" role="status">Loading your dashboard…</p>
    </main>

    <script>
        (function () {
            const next = <?= json_encode(base_url('dashboard')) ?>;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const coin = document.getElementById('badgeCoin');
            let done = false;

            const go = () => {
                if (done) return;
                done = true;
                document.body.classList.add('leaving');
                setTimeout(() => window.location.replace(next), 450);
            };

            if (reduce) {
                setTimeout(go, 900);
                return;
            }

            // Move on when the flip animation finishes (with a safety timeout).
            coin.addEventListener('animationend', e => {
                if (e.animationName === 'badge-flip') setTimeout(go, 250);
            });
            setTimeout(go, 5000);
        })();
    </script>
</body>
</html>
