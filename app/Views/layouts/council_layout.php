<?php
/**
 * Student Council (SC) page shell: sidebar + role banner + page content.
 * Expects: $active, $pageTitle, $pageDesc, $roleName (set by CouncilController::render()).
 * Optional: $workspaceTerm (committee sidebar footer, e.g. '1st semester · AY 2026–2027').
 */
$isCommittee = $roleName === 'Technical Committee';

// Technical Committee has its own workspace; every other SC role shares the approval pages.
$nav = $isCommittee
    ? [
        'dashboard'       => ['Dashboard',       'fa-table-cells-large'],
        'review'          => ['Review',          'fa-clipboard-check'],
        'my-sections'     => ['My sections',     'fa-folder'],
        'semester-review' => ['Semester review', 'fa-list-check'],
    ]
    : [
        'dashboard'   => ['Dashboard',   'fa-table-cells-large'],
        'submissions' => ['Submissions', 'fa-file-signature'],
        'approval'    => ['Approval',    'fa-certificate'],
        'repository'  => ['Repository',  'fa-box-archive'],
    ];

$roleLabel = strtoupper($isCommittee ? 'Committee' : $roleName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> | NEXUS Student Council</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('assets/css/flash.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/council.css') ?>">
    <?php if ($isCommittee): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/committee.css') ?>">
    <?php endif; ?>
</head>
<body class="sc">
    <?= view('partials/flash') ?>

    <!-- Phone / tablet top bar -->
    <header class="sc-topbar">
        <button type="button" class="sc-menu-btn" aria-controls="scSidebar" aria-expanded="false" aria-label="Open menu">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
        <img src="<?= base_url('assets/images/CICS-SC-logo.png') ?>" alt="" class="sc-topbar-logo">
        <span class="sc-topbar-title"><?= esc($pageTitle) ?></span>
    </header>
    <div class="sc-scrim" hidden></div>

    <!-- Sidebar -->
    <aside class="sc-sidebar" id="scSidebar" style="--sidebar-bg: url('<?= base_url('assets/images/862956802_29271179719142378_5690931201079113498_n.jpg') ?>')">
        <div class="sc-sidebar-logo">
            <img src="<?= base_url('assets/images/CICS-SC-logo.png') ?>" alt="CICS Student Council">
        </div>

        <nav class="sc-nav" aria-label="Student Council">
            <?php foreach ($nav as $key => [$label, $icon]): ?>
                <a href="<?= base_url($key) ?>"
                   class="sc-nav-item<?= $active === $key ? ' active' : '' ?>"
                   <?= $active === $key ? 'aria-current="page"' : '' ?>>
                    <i class="fa-solid <?= $icon ?>" aria-hidden="true"></i>
                    <span><?= $label ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($isCommittee): ?>
            <div class="sc-workspace">
                <p class="sc-workspace-title">Committee workspace</p>
                <?php if (! empty($workspaceTerm)): ?>
                    <p class="sc-workspace-term"><?= esc($workspaceTerm) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('logout') ?>" method="post" class="sc-logout">
            <?= csrf_field() ?>
            <button type="submit" class="sc-nav-item">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                <span>Log out</span>
            </button>
        </form>
    </aside>

    <main class="sc-main">
        <!-- Role banner -->
        <header class="sc-banner">
            <div class="sc-banner-text">
                <p class="sc-banner-kicker">CICS SC · <?= esc($roleLabel) ?></p>
                <h1><?= esc(strtoupper($pageTitle)) ?></h1>
                <p class="sc-banner-desc"><?= esc($pageDesc) ?></p>
            </div>
            <div class="sc-banner-mark" aria-hidden="true">
                <span class="sc-banner-role"><?= esc($roleLabel) ?></span>
                <span class="sc-banner-college">college of informatics and computing science</span>
            </div>
        </header>

        <?= $this->renderSection('content') ?>
    </main>

    <!-- Pop-up dialogs (kept outside <main> so the page behind can be dimmed) -->
    <?= $this->renderSection('modals') ?>

    <script src="<?= base_url('assets/js/council.js') ?>"></script>
    <?php if ($isCommittee): ?>
        <script src="<?= base_url('assets/js/committee.js') ?>"></script>
    <?php endif; ?>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
