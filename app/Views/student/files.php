<?= $this->extend('layouts/student_layout') ?>

<?= $this->section('toolbar') ?>
<?php if ($files !== []): ?>
    <form class="lib-sort" method="get">
        <?php if ($q !== ''): ?>
            <input type="hidden" name="q" value="<?= esc($q, 'attr') ?>">
        <?php endif; ?>
        <label for="libSort">Sort</label>
        <select id="libSort" name="sort" data-autosubmit>
            <?php foreach ($sorts as $key => $label): ?>
                <option value="<?= $key ?>"<?= $sort === $key ? ' selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="lib-btn lib-btn-outline lib-btn-sm">Apply</button></noscript>
    </form>

    <div class="lib-view-toggle" role="group" aria-label="Layout">
        <button type="button" data-layout="list" aria-pressed="true" aria-label="List view">
            <i class="fa-solid fa-list" aria-hidden="true"></i>
        </button>
        <button type="button" data-layout="grid" aria-pressed="false" aria-label="Grid view">
            <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i>
        </button>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if ($files === []): ?>
    <?php if ($emptyText !== ''): ?>
        <div class="lib-empty">
            <i class="fa-regular <?= $q !== '' ? 'fa-face-meh' : 'fa-folder-open' ?>" aria-hidden="true"></i>
            <p><?= esc($emptyText) ?></p>
        </div>
    <?php endif; ?>
<?php else: ?>
    <section class="files is-list" data-files aria-label="Files">
        <div class="files-head" aria-hidden="true">
            <span>Name</span><span>Type</span><span>Size</span><span></span>
        </div>
        <ul>
            <?php foreach ($files as $f): ?>
                <li class="file <?= $f['color'] ?>">
                    <span class="file-badge" aria-hidden="true"><?= esc($f['badge']) ?></span>
                    <div class="file-main">
                        <span class="file-title"><?= highlight($f['title'], $q) ?></span>
                        <span class="file-path"><?= esc($f['path']) ?></span>
                    </div>
                    <span class="file-type"><span class="visually-hidden">Type: </span><?= esc($f['typeName']) ?></span>
                    <span class="file-size"><span class="visually-hidden">Size: </span><?= esc($f['size']) ?></span>
                    <div class="file-actions">
                        <?php if ($f['preview'] === 'link'): ?>
                            <a href="<?= esc($f['viewUrl'], 'attr') ?>" target="_blank" rel="noopener" class="lib-btn lib-btn-outline lib-btn-sm">
                                Open<span class="visually-hidden"> <?= esc($f['title']) ?> (new tab)</span>
                            </a>
                        <?php else: ?>
                            <button type="button" class="lib-btn lib-btn-outline lib-btn-sm" data-preview
                                    data-title="<?= esc($f['title'], 'attr') ?>"
                                    data-path="<?= esc($f['path'], 'attr') ?>"
                                    data-badge="<?= esc($f['badge'], 'attr') ?>"
                                    data-color="<?= $f['color'] ?>"
                                    data-kind="<?= $f['preview'] ?>"
                                    data-meta="<?= esc($f['typeName'] . ' · ' . $f['size'] . ' · Added ' . $f['added'], 'attr') ?>"
                                    data-view-url="<?= esc($f['viewUrl'], 'attr') ?>"
                                    data-download-url="<?= esc($f['downloadUrl'], 'attr') ?>">
                                View<span class="visually-hidden"> <?= esc($f['title']) ?></span>
                            </button>
                        <?php endif; ?>
                        <a href="<?= esc($f['downloadUrl'], 'attr') ?>" class="lib-dl"
                           <?= $f['preview'] === 'link' ? 'target="_blank" rel="noopener"' : '' ?>
                           aria-label="Download <?= esc($f['title'], 'attr') ?>">
                            <i class="fa-solid fa-download" aria-hidden="true"></i>
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<dialog class="lib-modal" id="libPreview" aria-labelledby="libPreviewTitle">
    <header class="lib-modal-head">
        <span class="file-badge" data-preview-badge aria-hidden="true"></span>
        <div class="lib-modal-titles">
            <h2 id="libPreviewTitle" data-preview-title></h2>
            <p data-preview-path></p>
        </div>
        <a href="#" class="lib-btn lib-btn-primary" data-preview-download>Download</a>
        <button type="button" class="lib-icon-btn lib-modal-close" data-preview-close aria-label="Close preview">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </header>
    <div class="lib-modal-body">
        <iframe data-preview-frame title="File preview" hidden></iframe>
        <img data-preview-image alt="" hidden>
        <div class="lib-modal-none" data-preview-none hidden>
            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
            <p>This file type can't be previewed here.</p>
            <p class="lib-muted">Download it to open it on your device.</p>
        </div>
    </div>
    <footer class="lib-modal-foot" data-preview-meta></footer>
</dialog>
<?= $this->endSection() ?>
