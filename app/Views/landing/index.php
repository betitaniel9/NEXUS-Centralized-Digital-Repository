<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS Centralized Digital repository</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Google Fonts -->
     
        <!-- Space Grotesk font for a modern look -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

        <!-- MONOTON -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Monoton&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/landing_index.css') ?>">

    <!-- Turn the intro on before the page draws (skipped if the visitor prefers less motion) -->
    <script>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('intro-active');
        }
    </script>
</head>
<body>
    <!-- Intro: plays the SC/CICS trace GIF once, then fades out -->
    <div id="intro" class="intro" aria-hidden="true">
        <img id="introGif" class="intro-gif" alt=""
             data-src="<?= base_url('assets/images/SC-CICS-intro-once.gif') ?>">
        <span class="intro-skip">Click anywhere to skip</span>
    </div>

    <nav id="siteNav" class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container-fluid px-3 px-lg-5">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url('/') ?>">
                <img src="<?= base_url('assets/images/NEXUS-logo.png') ?>" alt="NEXUS logo" height="40">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#whats-inside">What's inside</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How it Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#quality-check">Quality Check</a></li>
                    <li class="nav-item"><a class="nav-link" href="#access">How to Open</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <a class="login_btn" href="<?= base_url('login') ?>">Login</a>
                    <a class="register_btn" href="<?= base_url('register') ?>">Register</a>
                </div>
            </div>
        </div>
    </nav>


    <!-- Hero with background image -->
    <section id="home" class="hero d-flex align-items-center">
        <div class="hero-bg" aria-hidden="true"></div>
        <div class="container">
            <div class="row align-items-center">
                <!-- Left side: headline -->
                <div class="col-lg-6 hero-copy" data-parallax="0.45">
                    <div class="hero-pill" data-anim="up" data-speed="0.08" style="--delay: 0.1s">
                        The CICS online library is here
                    </div>
                    <div class="hero-logos" data-anim="up" data-speed="0.16" style="--delay: 0.2s">
                        <img src="<?= base_url('assets/images/CICS-logo.png') ?>" alt="CICS logo">
                        <img src="<?= base_url('assets/images/CICS-SC-logo.png') ?>" alt="CICS Student Council logo">
                    </div>
                    <h1 class="hero-title" data-speed="-0.08">
                        <!-- Each letter is its own span so it can animate in one by one -->
                        <span class="title-letters" aria-hidden="true"><?php foreach (str_split('CICS NEXUS') as $i => $ch): ?><?= $ch === ' ' ? '<br>' : '<span class="ltr" style="--l: ' . $i . '">' . $ch . '</span>' ?><?php endforeach; ?></span>
                        <span class="visually-hidden">CICS NEXUS</span>
                        <span class="text-accent" data-anim="left" style="--delay: 0.55s">
                            Study smarter with notes from
                            <span class="text-highlight">fellow ARASOF students.</span>
                        </span>
                    </h1>

                    <div class="hero-subtitle">
                        
                        <p class="hero-trust" data-anim="fade" data-speed="0.25" style="--delay: 1.1s">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-4"/>
                            </svg>
                            Checked by your Student Council
                            <span class="hero-dot">·</span>
                            Free for every CICS student
                        </p>
                    </div>
                </div>

                <!-- Right side: sample resource cards -->
                <!-- Each card: card-layer = mouse/scroll parallax, card-float = slide-in + gentle bob -->
                <div class="col-lg-6" data-parallax="0.28">
                    <div class="resource-stack">
                        <div class="card-layer" data-depth="18"><div class="card-float">
                        <div class="resource-card card-1">
                            <div class="resource-icon icon-blue">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5V5a2 2 0 0 1 2-2h13v16H6.5A2.5 2.5 0 0 0 4 21.5z"/>
                                </svg>
                            </div>
                            <div class="resource-info">
                                <h6>Data Structures reviewer</h6>
                                <small>Academic Reviewers · 2nd year</small>
                            </div>
                            <a href="<?= base_url('login') ?>" class="resource-download" aria-label="Download">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 4v11"/><path d="M7 10l5 5 5-5"/><path d="M5 20h14"/>
                                </svg>
                            </a>
                        </div>
                        </div></div>

                        <div class="card-layer" data-depth="34"><div class="card-float">
                        <div class="resource-card card-2">
                            <div class="resource-icon icon-green">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 8l-4 4 4 4"/><path d="M16 8l4 4-4 4"/><path d="M13.5 6l-3 12"/>
                                </svg>
                            </div>
                            <div class="resource-info">
                                <h6>Python loops notes</h6>
                                <small>Cheat Sheets · 1st year</small>
                            </div>
                            <span class="resource-badge badge-green">Checked</span>
                        </div>
                        </div></div>

                        <div class="card-layer" data-depth="24"><div class="card-float">
                        <div class="resource-card card-3">
                            <div class="resource-icon icon-purple">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 9v12"/>
                                </svg>
                            </div>
                            <div class="resource-info">
                                <h6>Capstone manuscript template</h6>
                                <small>Templates · 4th year</small>
                            </div>
                            <span class="resource-badge badge-yellow">DOCX</span>
                        </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What's inside: 8 sections shown as folders that open on hover -->
    <?php
        // Each folder: title, description, colour theme, icon (SVG path data)
        $sections = [
            ['Academic Reviewers', 'Reviewer PDFs, summary notes, practice questions and flashcards.', 'blue',
                '<path d="M4 19.5V5a2 2 0 0 1 2-2h13v16H6.5A2.5 2.5 0 0 0 4 21.5z"/>'],
            ['Programming Cheat Sheets', 'Quick guides for Python, C++ and more.', 'green',
                '<path d="M8 8l-4 4 4 4"/><path d="M16 8l4 4-4 4"/><path d="M13.5 6l-3 12"/>'],
            ['Templates', 'Lab reports, capstone, thesis, resume and portfolio.', 'purple',
                '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 9v12"/>'],
            ['Internship Guide', 'Requirements, interview tips and a portfolio checklist.', 'yellow',
                '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/>'],
            ['Career Resources', 'Resume samples, LinkedIn and GitHub guides, free certifications.', 'lime',
                '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>'],
            ['Learning Resources', 'General study materials for self paced learning.', 'red',
                '<path d="M9 18h6"/><path d="M10 21h4"/><path d="M12 3a6 6 0 0 0-4 10.5c.8.8 1 1.5 1 2.5h6c0-1 .2-1.7 1-2.5A6 6 0 0 0 12 3z"/>'],
            ['Software Repository', 'VS Code, GitHub Desktop, MySQL Workbench, XAMPP and Figma.', 'slate',
                '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8"/><path d="M12 16v4"/>'],
            ['Student Contributions', 'Reviewers and notes shared by your fellow students.', 'pink',
                '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M18.5 14a6.5 6.5 0 0 1 3 6"/>'],
        ];
    ?>
    <section id="whats-inside" class="inside-section" data-bg-speed="0.35">
        <div class="container">
            <div data-anim="up" data-speed="0.15" class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-5">
                <div>
                    <p class="section-eyebrow">What's inside</p>
                    <h2 class="section-title">8 Repository for every year level</h2>
                </div>
                <a href="<?= base_url('login') ?>" class="btn-outline-pill">See all materials</a>
            </div>

            <div class="row g-4" data-stagger="0.09" data-stagger-cols="4">
                <?php foreach ($sections as $i => [$title, $desc, $theme, $icon]): ?>
                    <div class="col-sm-6 col-lg-3" data-anim="up" data-speed="<?= [0.03, 0.09, 0.15, 0.09][$i % 4] ?>">
                        <a href="<?= base_url('login') ?>" class="folder folder-<?= $theme ?>">
                            <span class="folder-back" aria-hidden="true"></span>
                            <span class="folder-paper" aria-hidden="true"></span>
                            <span class="folder-front">
                                <span class="folder-icon">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icon ?></svg>
                                </span>
                                <span class="folder-text">
                                    <span class="folder-title"><?= esc($title) ?></span>
                                    <span class="folder-desc"><?= esc($desc) ?></span>
                                </span>
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- How it works: intro on the left, 3-step vertical flow on the right -->
    <section id="how-it-works" class="how-section">
        <span class="how-watermark" data-speed-x="0.6" aria-hidden="true">NEXUS</span>

        <div class="container position-relative">
            <div class="row align-items-center gy-5">
                <!-- Left: heading, blurb, buttons -->
                <div class="col-lg-5 how-intro">
                    <p class="section-eyebrow how-eyebrow" data-anim="up">How it works</p>
                    <h2 class="how-big-title" data-speed="-0.12">
                        <span class="title-line" data-anim="up" style="--delay: 0.1s">Find it,</span><br>
                        <span class="title-line" data-anim="up" style="--delay: 0.25s">use it,</span><br>
                        <span class="title-line text-yellow" data-anim="up" style="--delay: 0.4s">share it.</span>
                    </h2>
                    <p class="how-lead" data-anim="up" data-speed="0.1" style="--delay: 0.55s">Three simple steps from a search bar to a shelf full of reviewers. No account needed for students.</p>
                    <div class="d-flex flex-wrap gap-3" data-anim="up" data-speed="0.16" style="--delay: 0.7s">
                        <a href="<?= base_url('login') ?>" class="btn-pill btn-pill-yellow">Start browsing</a>
                        <a href="<?= base_url('login') ?>" class="btn-pill btn-pill-outline">Share your notes</a>
                    </div>
                </div>

                <!-- Right: steps on a dashed line -->
                <div class="col-lg-7">
                    <ol class="how-flow list-unstyled" data-speed="-0.18">
                        <li class="how-flow-step" style="--i: 0">
                            <span class="how-circle circle-yellow">01</span>
                            <div class="how-card">
                                <h3 class="how-card-title">Find what you need</h3>
                                <p class="how-card-desc">Search or browse by section, year level and subject.</p>
                                <div class="how-search" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.5-4.5"/></svg>
                                    <span class="how-typed" data-text="Data Structures">Data Structures</span>
                                </div>
                            </div>
                        </li>

                        <li class="how-flow-step step-offset" style="--i: 1">
                            <span class="how-circle circle-white">02</span>
                            <div class="how-card">
                                <h3 class="how-card-title">View and download</h3>
                                <p class="how-card-desc">Preview any file, then download it for free.</p>
                                <div class="how-file" aria-hidden="true">
                                    <span class="how-file-type">PDF</span>
                                    <span class="how-file-name">Data Structures reviewer</span>
                                    <span class="how-file-dl">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v13"/><path d="M6 12l6 6 6-6"/></svg>
                                    </span>
                                </div>
                            </div>
                        </li>

                        <li class="how-flow-step" style="--i: 2">
                            <span class="how-circle circle-green">03</span>
                            <div class="how-card">
                                <h3 class="how-card-title">Share your own notes</h3>
                                <p class="how-card-desc">Submit your handwritten reviewers and help other students.</p>
                                <div class="how-drop" aria-hidden="true">
                                    <span>Drop your handwritten reviewer here</span>
                                    <span class="btn-pill btn-pill-yellow btn-pill-sm">Submit</span>
                                </div>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Quality check: 4 approval stages before a file goes live -->
    <?php
        $stages = [
            ['1', 'Stage 1', 'Committee',      'Checks if it is original, handwritten and accurate.'],
            ['2', 'Stage 2', 'Secretary',      'First official approval.'],
            ['3', 'Stage 3', 'Vice President', 'Second official approval.'],
            ['4', 'Stage 4', 'President',      'Final approval.'],
        ];
    ?>
    <section id="quality-check" class="qc-section" data-bg-speed="0.45">
        <div class="container">
            <div class="row align-items-center gy-5 mb-5">
                <!-- Left: sample document getting approved -->
                <div class="col-lg-6">
                    <div class="qc-doc" data-anim="left" data-speed="-0.3" aria-hidden="true">
                        <div class="qc-doc-head">
                            <span class="qc-doc-icon">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5V5a2 2 0 0 1 2-2h13v16H6.5A2.5 2.5 0 0 0 4 21.5z"/></svg>
                            </span>
                            <div>
                                <div class="qc-doc-title">Data Structures reviewer</div>
                                <div class="qc-doc-meta">Academic Reviewers · 2nd year</div>
                            </div>
                        </div>

                        <div class="qc-doc-lines">
                            <span style="width: 92%"></span>
                            <span style="width: 78%"></span>
                            <span style="width: 85%"></span>
                            <span style="width: 60%"></span>
                        </div>

                        <div class="qc-doc-checks">
                            <?php foreach ($stages as $i => [, , $name]): ?>
                                <div class="qc-doc-check" style="--i: <?= $i ?>">
                                    <span class="qc-tick">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                                    </span>
                                    <small><?= esc($name) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <span class="qc-stamp">Published</span>
                    </div>
                </div>

                <!-- Right: heading -->
                <div class="col-lg-6" data-speed="0.15" data-anim="right" style="--delay: 0.15s">
                    <p class="section-eyebrow">Quality check</p>
                    <h2 class="qc-title">Checked <span class="qc-four">4</span> times<br>before you see it</h2>
                    <p class="qc-lead">Only original, handwritten work from CICS students. No materials copied from professors or textbooks.</p>
                </div>
            </div>

            <!-- Approval stages -->
            <div class="qc-track-card" data-anim="up" data-speed="-0.1">
                <span class="qc-line" aria-hidden="true"><span class="qc-line-fill"></span></span>
                <ol class="qc-steps list-unstyled">
                    <?php foreach ($stages as $i => [$num, $label, $name, $desc]): ?>
                        <li class="qc-step" style="--i: <?= $i ?>">
                            <span class="qc-node"><?= $num ?></span>
                            <span class="qc-stage"><?= esc($label) ?></span>
                            <span class="qc-name"><?= esc($name) ?></span>
                            <span class="qc-desc"><?= esc($desc) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="qc-step qc-step-live" style="--i: 4">
                        <span class="qc-node">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                        </span>
                        <span class="qc-stage">Live</span>
                        <span class="qc-name">Published</span>
                        <span class="qc-desc">Live for every CICS student.</span>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- How to open: log-in access for the council and committees -->
    <?php
        $badges = [
            [
                'theme' => 'id-teal',
                'logo'  => 'assets/images/CICS-SC-logo.png',
                'alt'   => 'CICS Student Council logo',
                'role'  => 'Student Council',
                'key'   => 'council',
                'desc'  => 'Secretary, Vice President and President. Approve materials and monitor the system.',
            ],
            [
                'theme' => 'id-teal-light',
                'logo'  => 'assets/images/CICS-logo.png',
                'alt'   => 'CICS logo',
                'role'  => 'Committee',
                'key'   => 'committee',
                'desc'  => 'Review submissions and manage your assigned sections.',
            ],
        ];
    ?>
    <section id="access" class="access-section" data-bg-speed="0.35">
        <div class="container">
            <div class="access-panel" data-anim="zoom">
                <span class="access-ring ring-1" data-speed="0.25" aria-hidden="true"></span>
                <span class="access-ring ring-2" data-speed="-0.22" aria-hidden="true"></span>

                <div class="row align-items-center gy-5 position-relative">
                    <!-- Left: text -->
                    <div class="col-lg-5" data-anim="left" style="--delay: 0.2s">
                        <p class="section-eyebrow access-eyebrow">For the council and committees</p>
                        <h2 class="access-title" data-speed="-0.12">
                            Manage
                            <span class="access-title-logo">CICS<br>NEXUS</span>
                        </h2>
                        <p class="access-lead" data-speed="0.08">Officers and committee members log in to review submissions, approve materials and keep every section up to date.</p>
                        <div class="access-note" data-speed="0.16">
                            <span>Students do not need to log in.</span>
                            <a href="#whats-inside">Go to the library</a>
                        </div>

                        <!-- Phones only: simple log-in buttons instead of the ID cards -->
                        <div class="access-mobile-login d-md-none">
                            <a href="<?= base_url('login?role=council') ?>" class="btn-pill btn-pill-yellow">Log in as Student Council</a>
                            <a href="<?= base_url('login?role=committee') ?>" class="btn-pill btn-pill-outline">Log in as Committee</a>
                        </div>
                    </div>

                    <!-- Right: hanging ID badges (laptops and tablets only) -->
                    <div class="col-lg-7 d-none d-md-block" data-speed="-0.2">
                        <div class="badge-row">
                            <?php foreach ($badges as $i => $b): ?>
                                <div class="badge-wrap" data-anim="drop" style="--i: <?= $i ?>; --delay: <?= 0.3 + $i * 0.2 ?>s">
                                    <div class="id-badge <?= $b['theme'] ?>">
                                        <span class="badge-slot" aria-hidden="true"></span>
                                        <div class="badge-head">
                                            <span class="badge-brand">NEXUS</span>
                                            <span class="badge-kind">Access ID</span>
                                        </div>
                                        <div class="badge-body">
                                            <div class="badge-top">
                                                <div class="badge-photo">
                                                    <img src="<?= base_url($b['logo']) ?>" alt="<?= esc($b['alt']) ?>">
                                                    <span class="badge-holo" aria-hidden="true"></span>
                                                </div>
                                                <div>
                                                    <span class="badge-chip" aria-hidden="true"></span>
                                                    <span class="badge-label">Role</span>
                                                    <span class="badge-role"><?= esc($b['role']) ?></span>
                                                </div>
                                            </div>
                                            <span class="badge-label">Access</span>
                                            <p class="badge-desc"><?= esc($b['desc']) ?></p>
                                            <span class="badge-barcode" aria-hidden="true"></span>
                                            <a href="<?= base_url('login?role=' . $b['key']) ?>" class="badge-btn">Log in</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="row gy-5" data-stagger="0.1">
                <div class="col-lg-5" data-anim="up">
                    <img src="<?= base_url('assets/images/NEXUS-logo.png') ?>" alt="NEXUS" class="footer-logo">
                    <p class="footer-about">The CICS online library. Reviewers, cheat sheets, templates and more, made by students and checked by your Student Council.</p>
                    <div class="footer-seals">
                        <img src="<?= base_url('assets/images/CICS-logo.png') ?>" alt="CICS logo">
                        <img src="<?= base_url('assets/images/CICS-SC-logo.png') ?>" alt="CICS Student Council logo">
                    </div>
                </div>

                <div class="col-6 col-lg-2 offset-lg-1" data-anim="up">
                    <h3 class="footer-heading">Explore</h3>
                    <ul class="footer-links">
                        <li><a href="#whats-inside">What's inside</a></li>
                        <li><a href="#how-it-works">How it works</a></li>
                        <li><a href="#quality-check">Quality check</a></li>
                        <li><a href="#access">How to open</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2" data-anim="up">
                    <h3 class="footer-heading">Account</h3>
                    <ul class="footer-links">
                        <li><a href="<?= base_url('login') ?>">Log in</a></li>
                        <li><a href="<?= base_url('register') ?>">Register</a></li>
                    </ul>
                </div>

                <div class="col-lg-2" data-anim="up">
                    <h3 class="footer-heading">Share</h3>
                    <p class="footer-small">Have a handwritten reviewer that helped you?</p>
                    <a href="<?= base_url('login') ?>" class="btn-pill btn-pill-yellow btn-pill-sm">Share your notes</a>
                </div>
            </div>

            <div class="footer-bottom" data-anim="fade">
                <span>&copy; <?= date('Y') ?> CICS NEXUS · College of Informatics and Computing Sciences, Batangas State University ARASOF Nasugbu</span>
                <a href="#home" class="footer-top">Back to top ↑</a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS (needed for the mobile menu toggle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Intro animation -->
    <script src="<?= base_url('assets/js/intro.js') ?>"></script>

    <!-- Parallax + scroll reveal -->
    <script src="<?= base_url('assets/js/parallax.js') ?>"></script>

    <!-- Icon chaff burst when a hero card is clicked -->
    <script src="<?= base_url('assets/js/chaff.js') ?>"></script>

    <!-- Circle wipe into the Login / Register pages -->
    <script src="<?= base_url('assets/js/page-transition.js') ?>"></script>

    <!-- Navbar: see-through at the top, white once the page is scrolled -->
    <script>
        (function () {
            const nav = document.getElementById('siteNav');
            const menu = document.getElementById('mainNav');

            function updateNav() {
                const menuOpen = menu.classList.contains('show');
                nav.classList.toggle('scrolled', window.scrollY > 10 || menuOpen);
            }

            // Highlight the link for the section currently on screen
            const links = [...nav.querySelectorAll('.nav-link[href^="#"]')];
            const sections = links.map(link => document.querySelector(link.getAttribute('href')));

            function updateActive() {
                // A section counts as "current" once its top passes 40% down the screen
                const line = window.innerHeight * 0.4;
                let current = -1;
                sections.forEach((section, i) => {
                    if (section && section.getBoundingClientRect().top <= line) current = i;
                });
                // At the very bottom, light up the last link even if its section is short
                if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
                    current = sections.length - 1;
                }
                links.forEach((link, i) => {
                    const on = i === current;
                    link.classList.toggle('active', on);
                    if (on) link.setAttribute('aria-current', 'location');
                    else link.removeAttribute('aria-current');
                });
            }

            function onScroll() {
                updateNav();
                updateActive();
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', updateActive);
            // Give the mobile menu a white background while it's open
            menu.addEventListener('show.bs.collapse', () => nav.classList.add('scrolled'));
            menu.addEventListener('hidden.bs.collapse', updateNav);
            // Close the mobile menu after tapping a link
            links.forEach(link => link.addEventListener('click', () => {
                if (menu.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(menu).hide();
            }));
            onScroll();
        })();
    </script>
</body>
</html>