<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>
<?php
$weeklyMax = max([1, ...array_map(fn ($w) => max($w['accepted'], $w['returned']), $weekly)]);
$ageMax    = max([1, ...array_map(fn ($a) => (int) $a['count'], $queueAge)]);
$lateCount = (int) ($queueAge[3]['count'] ?? 0);

$ratio = fn ($a, $b) => $a === null || $b === null ? '—' : number_format($a) . ' / ' . number_format($b);
?>

<div class="page-head">
    <div>
        <h2 class="page-head-title">Your committee at a glance</h2>
        <p class="page-head-sub">
            <?= esc($weekLabel) ?><?= $stats['sectionsTotal'] !== null ? ' · ' . number_format($stats['sectionsTotal']) . ' assigned sections' : '' ?>
        </p>
    </div>
    <a href="<?= base_url('review') ?>" class="sc-btn"><i class="fa-solid fa-play" aria-hidden="true"></i> Start reviewing</a>
</div>

<section class="stat-row" aria-label="Review summary">
    <?= partial('partials/stat_card', [
        'label' => 'To review', 'icon' => 'fa-folder',
        'value' => stat_value($stats['toReview']),
        'note'  => $stats['overdue'] === null ? 'Files waiting for you' : number_format($stats['overdue']) . ' files overdue',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Accepted this week', 'icon' => 'fa-circle-check',
        'value' => stat_value($stats['acceptedWeek']),
        'note'  => 'Sent to Secretary',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Returned this week', 'icon' => 'fa-rotate-left',
        'value' => stat_value($stats['returnedWeek']),
        'note'  => 'Feedback sent to students',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Average review time', 'icon' => 'fa-clock',
        'value' => stat_value($stats['avgReviewDays'], 1), 'unit' => 'days',
        'note'  => 'Arrival to committee decision',
    ]) ?>
</section>

<section class="stat-row" aria-label="Section summary">
    <?= partial('partials/stat_card', [
        'label' => 'Files in my sections', 'icon' => 'fa-folder-open',
        'value' => stat_value($stats['filesInSections']),
        'note'  => $stats['activeFiles'] === null ? 'In your assigned sections' : number_format($stats['activeFiles']) . ' active materials',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Broken links', 'icon' => 'fa-link-slash',
        'value' => stat_value($stats['brokenLinks']),
        'note'  => $stats['brokenSections'] === null ? 'Links that need fixing' : 'Across ' . number_format($stats['brokenSections']) . ' sections',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Old materials', 'icon' => 'fa-box-archive',
        'value' => stat_value($stats['oldMaterials']),
        'note'  => 'To remove this semester',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Sections reviewed', 'icon' => 'fa-list-check',
        'value' => $ratio($stats['sectionsDone'], $stats['sectionsTotal']),
        'note'  => $stats['tasksDone'] === null ? 'This semester' : $stats['tasksDone'] . ' of ' . $stats['tasksTotal'] . ' tasks done',
    ]) ?>
</section>

<div class="dash-grid">
    <div class="dash-main">
        <!-- Reviews per week -->
        <section class="sc-card panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Reviews per week</h2>
                    <p class="panel-sub">Committee decisions over the last four weeks</p>
                </div>
                <ul class="chart-legend">
                    <li><span class="swatch swatch-navy"></span>Accepted</li>
                    <li><span class="swatch swatch-sky"></span>Returned</li>
                </ul>
            </div>

            <?php if ($weekly === []): ?>
                <p class="empty-state"><i class="fa-regular fa-chart-bar" aria-hidden="true"></i>No reviews yet.</p>
            <?php else: ?>
                <div class="month-chart" role="img" aria-label="Accepted and returned files per week">
                    <?php foreach ($weekly as $w): ?>
                        <div class="month-col">
                            <div class="month-bars">
                                <div class="vbar vbar-navy" style="--h: <?= $w['accepted'] / $weeklyMax * 100 ?>%"><span><?= (int) $w['accepted'] ?></span></div>
                                <div class="vbar vbar-sky" style="--h: <?= $w['returned'] / $weeklyMax * 100 ?>%"><span><?= (int) $w['returned'] ?></span></div>
                            </div>
                            <span class="month-label"><?= esc($w['week']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- My sections -->
        <section class="sc-card panel">
            <h2 class="panel-title">My sections</h2>
            <p class="panel-sub">Repository health and semester review status</p>

            <div class="table-wrap">
                <table class="sc-table">
                    <thead>
                        <tr><th>Section</th><th class="num">Files</th><th class="num">Broken</th><th class="num">Old</th><th>Semester review</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($sections === []): ?>
                            <tr><td colspan="5" class="empty-cell">No sections are assigned to you yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($sections as $s): $st = section_state($s['state']); ?>
                            <tr>
                                <td><?= esc($s['name']) ?></td>
                                <td class="num"><?= (int) $s['files'] ?></td>
                                <td class="num"><?= (int) $s['broken'] ?></td>
                                <td class="num"><?= (int) $s['old'] ?></td>
                                <td><span class="pill <?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Oldest in your queue -->
        <section class="sc-card panel">
            <h2 class="panel-title">Oldest in your queue</h2>
            <p class="panel-sub">Prioritize files waiting more than 5 days</p>

            <?php if ($oldest === []): ?>
                <p class="empty-state empty-sm">Nothing has waited more than 5 days.</p>
            <?php else: ?>
                <ul class="line-list">
                    <?php foreach ($oldest as $o): ?>
                        <li>
                            <div>
                                <strong><?= esc($o['title']) ?></strong>
                                <span><?= esc($o['section']) ?> · <?= esc($o['yearLevel']) ?></span>
                            </div>
                            <span class="pill pill-soft-amber"><?= days_label((int) $o['days']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>

    <div class="dash-side">
        <!-- Queue by age -->
        <section class="sc-card panel">
            <h2 class="panel-title">Review queue by age</h2>
            <p class="panel-sub"><?= $stats['toReview'] === null ? 'Files waiting for your decision' : number_format($stats['toReview']) . ' files waiting for your decision' ?></p>

            <ul class="age-list">
                <?php foreach ($queueAge as $a): ?>
                    <li class="<?= ! empty($a['late']) ? 'is-late' : '' ?>">
                        <span><?= esc($a['label']) ?></span>
                        <span class="hbar-track"><span class="hbar-fill" style="--w: <?= (int) $a['count'] / $ageMax * 100 ?>%"></span></span>
                        <span class="hbar-count"><?= stat_value($a['count']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($lateCount > 0): ?>
                <p class="note note-warn">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    <?= $lateCount ?> <?= $lateCount === 1 ? 'file has' : 'files have' ?> waited more than 5 days. Review these first.
                </p>
            <?php endif; ?>
        </section>

        <!-- Top return reasons -->
        <section class="sc-card panel">
            <h2 class="panel-title">Top return reasons</h2>
            <p class="panel-sub"><?= $returnedCount === null ? 'Files returned this week' : number_format($returnedCount) . ' files returned this week' ?></p>

            <?php if ($returnReasons === []): ?>
                <p class="empty-state empty-sm">No files returned this week.</p>
            <?php else: ?>
                <ul class="reason-list">
                    <?php foreach ($returnReasons as $r): ?>
                        <li><span><?= esc($r['reason']) ?></span><span class="count-badge"><?= (int) $r['count'] ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p class="panel-foot">Return reasons help students improve their next submission.</p>
        </section>

        <!-- Recent activity -->
        <section class="sc-card panel">
            <h2 class="panel-title">Recent activity</h2>
            <p class="panel-sub">Latest updates in your assigned sections</p>

            <?php if ($activity === []): ?>
                <p class="empty-state empty-sm">No activity yet.</p>
            <?php else: ?>
                <ul class="line-list">
                    <?php foreach ($activity as $a): ?>
                        <li>
                            <div>
                                <strong><?= esc($a['title']) ?></strong>
                                <span><?= esc($a['note']) ?> · <?= esc($a['time']) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>

<?= $this->endSection() ?>
