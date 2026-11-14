<?php
/**
 * Student library shell: top bar + folder sidebar + page heading.
 * Expects (set by Student::page()): $title, $subtitle, $crumbs [[label, url|null], ...], $backUrl,
 * $activeCat, $categories, $searchQuery, $notesFormUrl, $isLoggedIn, $firstName, $lastName, $email, $isCouncil.
 * The library is public; signed-out visitors get a "Sign in" button instead of the account menu.
 * Sections: 'toolbar' (right of the heading), 'content', 'modals'.
 */
$formAttrs = $notesFormUrl !== ''
    ? 'href="' . esc($notesFormUrl, 'attr') . '" target="_blank" rel="noopener"'
    : 'aria-disabled="true" title="The Council has not added the form link yet"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | CICS Nexus Library</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('assets/css/flash.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/student.css') ?>">
</head>
<body class="lib">
    <?= view('partials/flash') ?>

    <!-- Top bar -->
    <header class="lib-top">
        <button type="button" class="lib-icon-btn lib-menu-btn" aria-controls="libSide" aria-expanded="false" aria-label="Open folders">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <a href="<?= base_url('student') ?>" class="lib-brand">
            <img src="<?= base_url('assets/images/CICS-logo.png') ?>" alt="">
            <span><strong>CICS Nexus</strong><small>Library</small></span>
        </a>

        <form class="lib-search" action="<?= base_url('student/search') ?>" method="get" role="search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" maxlength="100"
                   placeholder="Search all notes and files" aria-label="Search all notes and files">
        </form>

        <a class="lib-btn lib-btn-primary lib-share" <?= $formAttrs ?>>
            <span>Share your notes</span>
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
        </a>

        <?php if (! $isLoggedIn): ?>
        <a href="<?= base_url('login') ?>" class="lib-btn lib-btn-outline lib-signin">
            <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i><span>Sign in</span>
        </a>
        <?php else: ?>
        <div class="lib-user">
            <button type="button" class="lib-avatar" aria-haspopup="true" aria-expanded="false" aria-controls="libUserMenu"
                    aria-label="Account menu for <?= esc(trim($firstName . ' ' . $lastName), 'attr') ?>">
                <?= esc(initials($firstName, $lastName)) ?>
            </button>
            <div class="lib-user-menu" id="libUserMenu" hidden>
                <p class="lib-user-name"><?= esc(trim($firstName . ' ' . $lastName)) ?></p>
                <p class="lib-user-email"><?= esc($email) ?></p>
                <?php if ($isCouncil): ?>
                    <a href="<?= base_url('dashboard') ?>" class="lib-user-item">
                        <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i> Council dashboard
                    </a>
                <?php endif; ?>
                <form action="<?= base_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="lib-user-item">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Log out
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </header>

    <div class="lib-shell">
        <!-- Folder sidebar -->
        <aside class="lib-side" id="libSide" aria-label="Library folders">
            <p class="lib-side-label">Library</p>
            <nav class="lib-side-nav">
                <a href="<?= base_url('student') ?>"
                   class="lib-side-item lib-side-all<?= $activeCat === null && $crumbs === [] ? ' active' : '' ?>"
                   <?= $activeCat === null && $crumbs === [] ? 'aria-current="page"' : '' ?>>
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                    <span>All folders</span>
                </a>
                <?php foreach ($categories as $c): $isActive = $activeCat === $c['id']; ?>
                    <a href="<?= base_url('student/folder/' . $c['id']) ?>"
                       class="lib-side-item <?= $c['color'] ?><?= $isActive ? ' active' : '' ?>"
                       <?= $isActive ? 'aria-current="true"' : '' ?>>
                        <?= folder_icon('folder-ico-sm') ?>
                        <span class="lib-side-name"><?= esc($c['name']) ?></span>
                        <span class="lib-side-count" aria-label="<?= $c['files'] ?> files"><?= number_format($c['files']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="lib-share-card">
                <p class="lib-share-title">Have notes to share?</p>
                <p>Send your handwritten notes through our Google Form. The Council checks them before they are added here.</p>
                <a class="lib-btn lib-btn-primary lib-btn-block" <?= $formAttrs ?>>Open Google Form</a>
            </div>
        </aside>
        <div class="lib-scrim" hidden></div>

        <main class="lib-main">
            <nav class="lib-crumbs" aria-label="Breadcrumb">
                <ol>
                    <li><a href="<?= base_url('student') ?>"<?= $crumbs === [] ? ' aria-current="page"' : '' ?>>Library</a></li>
                    <?php foreach ($crumbs as [$label, $url]): ?>
                        <li>
                            <?php if ($url !== null): ?>
                                <a href="<?= esc($url, 'attr') ?>"><?= esc($label) ?></a>
                            <?php else: ?>
                                <span aria-current="page"><?= esc($label) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <header class="lib-head">
                <?php if ($backUrl !== null): ?>
                    <a href="<?= esc($backUrl, 'attr') ?>" class="lib-back" aria-label="Back">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
                <div class="lib-head-text">
                    <h1><?= esc($title) ?></h1>
                    <?php if ($subtitle !== ''): ?>
                        <p><?= esc($subtitle) ?></p>
                    <?php endif; ?>
                </div>
                <div class="lib-toolbar"><?= $this->renderSection('toolbar') ?></div>
            </header>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <?= $this->renderSection('modals') ?>

    <script src="<?= base_url('assets/js/student.js') ?>"></script>
</body>
</html>
