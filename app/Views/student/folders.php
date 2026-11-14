<?= $this->extend('layouts/student_layout') ?>
<?= $this->section('content') ?>

<?php if ($folders === []): ?>
    <div class="lib-empty">
        <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
        <p><?= esc($emptyText) ?></p>
    </div>
<?php else: ?>
    <ul class="folder-grid">
        <?php foreach ($folders as $f): ?>
            <li>
                <a href="<?= esc($f['url'], 'attr') ?>" class="folder-card <?= $f['color'] ?>">
                    <?= folder_icon() ?>
                    <span class="folder-name"><?= esc($f['name']) ?></span>
                    <span class="folder-meta"><?= esc($f['meta']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?= $this->endSection() ?>
