<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>

<!-- Filters first, then the stat cards -->
<?= partial('partials/filters', [
    'action'       => 'repository',
    'filters'      => $filters,
    'repositories' => $repositories,
    'yearLevels'   => $yearLevels,
    'fileTypes'    => $fileTypes,
]) ?>

<section class="stat-row" aria-label="Summary">
    <?= partial('partials/stat_card', [
        'label' => 'All Files', 'icon' => 'fa-folder',
        'value' => stat_value($stats['total']),
        'note'  => $stats['repositories'] === null ? 'Across all repositories' : 'Across ' . number_format($stats['repositories']) . ' repositories',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Published', 'icon' => 'fa-square-check',
        'value' => stat_value($stats['published']),
        'note'  => 'Live for students',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Storage Used', 'icon' => 'fa-hard-drive',
        'value' => format_bytes($stats['storage']),
        'note'  => 'Total size of all files',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Old Materials', 'icon' => 'fa-clock-rotate-left',
        'value' => stat_value($stats['old']),
        'note'  => 'Uploaded over a year ago',
    ]) ?>
</section>

<section class="sc-card panel">
    <h2 class="panel-title">All Materials</h2>

    <div class="table-wrap">
        <table class="sc-table repo-table">
            <thead>
                <tr><th>File</th><th>Repository</th><th>Year Level</th><th>Type</th><th>Status</th><th><span class="visually-hidden">Actions</span></th></tr>
            </thead>
            <tbody>
                <?php if ($materials === []): ?>
                    <tr><td colspan="6" class="empty-cell">No materials in the repository yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($materials as $m): $step = submission_step($m['status']); ?>
                    <tr>
                        <td><?= esc($m['fileName']) ?></td>
                        <td><?= esc($m['repository']) ?></td>
                        <td><?= esc($m['yearLevel']) ?></td>
                        <td><?= esc(strtoupper($m['fileType'])) ?></td>
                        <td><span class="pill <?= $step['class'] ?>"><?= esc($step['label']) ?></span></td>
                        <!-- TODO(db): edit page for this material -->
                        <td class="num"><button type="button" class="sc-btn sc-btn-outline sc-btn-xs" data-edit="<?= (int) $m['id'] ?>">Edit</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?= $this->endSection() ?>
