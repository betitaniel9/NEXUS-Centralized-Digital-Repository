<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>

<!-- Filters first, then the stat cards -->
<?= partial('partials/filters', [
    'action'       => 'approval',
    'filters'      => $filters,
    'repositories' => $repositories,
    'yearLevels'   => $yearLevels,
    'fileTypes'    => $fileTypes,
]) ?>

<section class="stat-row" aria-label="Summary">
    <?= partial('partials/stat_card', [
        'label' => 'Waiting for you', 'icon' => 'fa-folder',
        'value' => stat_value($stats['waiting']),
        'note'  => 'Files to review',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Approved this week', 'icon' => 'fa-square-check',
        'value' => stat_value($stats['approvedWeek']),
        'note'  => 'Sent to ' . $nextStep,
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Oldest file waiting', 'icon' => 'fa-calendar-days',
        'value' => stat_value($stats['oldestDays']), 'unit' => 'Days',
        'note'  => $stats['oldestTitle'] ?? 'Nothing waiting',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Your average time', 'icon' => 'fa-hourglass-half',
        'value' => stat_value($stats['avgDays'], 1), 'unit' => 'Days',
        'note'  => 'From arrival to approval',
    ]) ?>
</section>

<h2 class="section-label">Files to approve</h2>

<?php if ($files === []): ?>
    <div class="sc-card empty-panel">
        <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
        <p>No files are waiting for your approval.</p>
    </div>
<?php else: ?>
    <div class="file-grid">
        <?php foreach ($files as $f): ?>
            <article class="sc-card file-card">
                <div class="file-preview">
                    <span class="file-type"><?= esc(strtoupper($f['fileType'])) ?></span>
                    <i class="fa-regular fa-file" aria-hidden="true"></i>
                </div>
                <div class="file-info">
                    <h3><?= esc($f['title']) ?></h3>
                    <p><?= esc($f['repository']) ?> · <?= esc($f['yearLevel']) ?></p>
                    <p>Submitted <?= short_date($f['submittedAt']) ?></p>
                </div>
                <div class="file-actions">
                    <a href="<?= esc($f['fileUrl'] ?? '#') ?>" class="sc-btn sc-btn-outline sc-btn-sm" target="_blank" rel="noopener">View File</a>
                    <!-- TODO(db): POST the approval decision for this submission -->
                    <button type="button" class="sc-btn sc-btn-sm" data-approve="<?= (int) $f['id'] ?>">Approve</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
