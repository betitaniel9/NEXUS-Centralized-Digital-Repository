<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>
<?php
$tabs = [
    'all'       => 'All',
    'review'    => 'In review',
    'approval'  => 'In Approval',
    'published' => 'Published',
    'returned'  => 'Returned',
];

$counts = array_fill_keys(array_keys($tabs), 0);
$counts['all'] = count($submissions);

// Data the detail panel needs (read by council.js)
$details = [];
foreach ($submissions as $i => $s) {
    $step = submission_step($s['status']);
    $counts[$step['group']] = ($counts[$step['group']] ?? 0) + 1;

    $details[$i] = [
        'title'      => $s['title'],
        'student'    => $s['student'],
        'submitted'  => short_date($s['submittedAt']),
        'repository' => $s['repository'],
        'yearLevel'  => $s['yearLevel'],
        'stepLabel'  => $step['label'],
        'stepClass'  => $step['class'],
        'fileUrl'    => $s['fileUrl'] ?? null,
        'tracking'   => $s['tracking'] ?? [],
    ];
}
?>

<div class="tab-row" role="tablist" aria-label="Filter submissions">
    <?php foreach ($tabs as $key => $label): ?>
        <button type="button" role="tab" class="sc-tab<?= $key === 'all' ? ' active' : '' ?>"
                data-filter="<?= $key ?>" aria-selected="<?= $key === 'all' ? 'true' : 'false' ?>">
            <?= $label ?> <span class="tab-count"><?= $counts[$key] ?></span>
        </button>
    <?php endforeach; ?>
</div>

<div class="sub-grid">
    <!-- List -->
    <section class="sc-card panel" aria-label="Submissions">
        <div class="table-wrap">
            <table class="sc-table sub-table">
                <thead>
                    <tr><th>Material</th><th>Repository</th><th>Submitted</th><th>Current Step</th></tr>
                </thead>
                <tbody>
                    <?php if ($submissions === []): ?>
                        <tr><td colspan="4" class="empty-cell">No submissions yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($submissions as $i => $s): $step = submission_step($s['status']); ?>
                        <tr class="sub-row" tabindex="0" data-index="<?= $i ?>" data-group="<?= $step['group'] ?>">
                            <td>
                                <span class="cell-title"><?= esc($s['title']) ?></span>
                                <span class="cell-meta"><?= esc($s['student']) ?> · <?= esc($s['yearLevel']) ?></span>
                            </td>
                            <td><?= esc($s['repository']) ?></td>
                            <td><?= short_date($s['submittedAt']) ?></td>
                            <td><span class="pill <?= $step['class'] ?>"><?= esc($step['label']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="filter-empty" hidden><td colspan="4" class="empty-cell">Nothing in this tab.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Detail -->
    <section class="sc-card panel sub-detail" id="subDetail" aria-live="polite">
        <div class="detail-empty">
            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
            <p>Select a submission to see where it is in the approval process.</p>
        </div>

        <div class="detail-body" hidden>
            <span class="pill" data-d="step"></span>
            <h2 class="detail-title" data-d="title"></h2>
            <p class="detail-meta">Submitted by <span data-d="student"></span> on <span data-d="submitted"></span></p>

            <div class="detail-facts">
                <div><span>Section</span><strong data-d="repository"></strong></div>
                <div><span>Year Level</span><strong data-d="yearLevel"></strong></div>
            </div>

            <h3 class="panel-subhead">Tracking</h3>
            <ol class="timeline" data-d="tracking"></ol>

            <div class="detail-actions">
                <a class="sc-btn sc-btn-outline" data-d="fileUrl" target="_blank" rel="noopener">View File</a>
                <a class="sc-btn" href="<?= base_url('approval') ?>">Open in Approval</a>
            </div>
        </div>
    </section>
</div>

<script type="application/json" id="subData"><?= json_encode($details, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<?= $this->endSection() ?>
