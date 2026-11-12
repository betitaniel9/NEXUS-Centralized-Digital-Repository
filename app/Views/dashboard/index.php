<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>
<?php
$monthlyMax  = max([1, ...array_map(fn ($m) => max($m['submitted'], $m['published']), $monthly)]);
$pipeMax     = max([1, ...array_map(fn ($p) => (int) $p['count'], $pipeline)]);
$slowestDays = max([0, ...array_map(fn ($p) => (float) $p['avgDays'], $pipeline)]);
$topicMax    = max([1, ...array_column($topics, 'count')]);
$yearMax     = max([1, ...array_column($yearLevels, 'count')]);
$yearMin     = $yearLevels ? min(array_column($yearLevels, 'count')) : null;

$reviewTotal  = (int) $review['accepted'] + (int) $review['returned'];
$acceptedPct  = $reviewTotal ? round($review['accepted'] / $reviewTotal * 100) : null;
$returnedPct  = $reviewTotal ? 100 - $acceptedPct : null;

$plusNote = fn ($n, string $suffix, string $empty) => $n === null ? $empty : '+' . number_format($n) . ' ' . $suffix;
?>

<div class="dash-grid">
    <!-- ================= Left column ================= -->
    <div class="dash-main">
        <section class="stat-row" aria-label="Summary">
            <?= partial('partials/stat_card', [
                'label' => 'Published Materials', 'icon' => 'fa-folder',
                'value' => stat_value($stats['published']),
                'note'  => $plusNote($stats['publishedThisMonth'], 'this month', 'Live in the repository'),
            ]) ?>
            <?= partial('partials/stat_card', [
                'label' => 'New Submissions', 'icon' => 'fa-file-circle-plus',
                'value' => stat_value($stats['newSubmissions']),
                'note'  => 'Waiting for committee review',
            ]) ?>
            <?= partial('partials/stat_card', [
                'label' => 'Pending Approval', 'icon' => 'fa-clock',
                'value' => stat_value($stats['pendingApproval']),
                'note'  => 'With Secretary, VP or President',
            ]) ?>
            <?= partial('partials/stat_card', [
                'label' => 'Student Contributors', 'icon' => 'fa-user',
                'value' => stat_value($stats['contributors']),
                'note'  => $plusNote($stats['newContributors'], 'new students this month', 'Students who submitted'),
            ]) ?>
        </section>

        <!-- Submissions per month -->
        <section class="sc-card panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Submissions Per Month</h2>
                    <p class="panel-sub">Materials submitted each month and how many got published</p>
                </div>
                <ul class="chart-legend">
                    <li><span class="swatch swatch-sky"></span>Submitted</li>
                    <li><span class="swatch swatch-navy"></span>Published</li>
                </ul>
            </div>

            <?php if ($monthly === []): ?>
                <p class="empty-state"><i class="fa-regular fa-chart-bar" aria-hidden="true"></i>No submissions yet.</p>
            <?php else: ?>
                <div class="month-chart" role="img" aria-label="Submitted and published materials per month">
                    <?php foreach ($monthly as $m): ?>
                        <div class="month-col">
                            <div class="month-bars">
                                <div class="vbar vbar-sky" style="--h: <?= $m['submitted'] / $monthlyMax * 100 ?>%"><span><?= (int) $m['submitted'] ?></span></div>
                                <div class="vbar vbar-navy" style="--h: <?= $m['published'] / $monthlyMax * 100 ?>%"><span><?= (int) $m['published'] ?></span></div>
                            </div>
                            <span class="month-label"><?= esc($m['month']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Material by topic -->
        <section class="sc-card panel">
            <h2 class="panel-title">Material by Topic</h2>
            <p class="panel-sub">Published materials in each part of the repository</p>

            <?php if ($topics === []): ?>
                <p class="empty-state">No published materials yet.</p>
            <?php else: ?>
                <ul class="hbar-list">
                    <?php foreach ($topics as $t): ?>
                        <li>
                            <span class="hbar-name"><?= esc($t['name']) ?></span>
                            <span class="hbar-track"><span class="hbar-fill" style="--w: <?= $t['count'] / $topicMax * 100 ?>%"></span></span>
                            <span class="hbar-count"><?= (int) $t['count'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Waiting too long -->
        <section class="sc-card panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Waiting Too Long</h2>
                    <p class="panel-sub">Items stuck at one step for more than 5 days</p>
                </div>
                <a href="<?= base_url('approval') ?>" class="sc-btn sc-btn-sm">Go to Approval <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>

            <div class="table-wrap">
                <table class="sc-table">
                    <thead>
                        <tr><th>Material</th><th>Waiting at</th><th>Repository</th><th class="num">Days</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($waiting === []): ?>
                            <tr><td colspan="4" class="empty-cell">Nothing has been waiting more than 5 days.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($waiting as $w): $step = submission_step($w['status']); ?>
                            <tr>
                                <td><?= esc($w['title']) ?></td>
                                <td><span class="pill <?= $step['class'] ?>"><?= esc($step['label']) ?></span></td>
                                <td><?= esc($w['repository']) ?></td>
                                <td class="num"><?= (int) $w['days'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- ================= Right column ================= -->
    <div class="dash-side">
        <!-- Approval pipeline -->
        <section class="sc-card panel">
            <h2 class="panel-title">Approval Pipeline</h2>
            <p class="panel-sub">Items waiting at each step and average days spent there</p>

            <ul class="pipe-list">
                <?php foreach ($pipeline as $p):
                    $slow = $p['avgDays'] !== null && $slowestDays > 0 && (float) $p['avgDays'] === $slowestDays; ?>
                    <li class="<?= $slow ? 'is-slow' : '' ?>">
                        <div class="pipe-top">
                            <span><?= esc($p['stage']) ?></span>
                            <?php if ($p['avgDays'] !== null): ?>
                                <span class="pipe-avg">avg <?= stat_value($p['avgDays'], 1) ?> days</span>
                            <?php endif; ?>
                        </div>
                        <div class="pipe-bottom">
                            <span class="hbar-track"><span class="hbar-fill" style="--w: <?= (int) $p['count'] / $pipeMax * 100 ?>%"></span></span>
                            <span class="hbar-count"><?= stat_value($p['count']) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Materials by year level -->
        <section class="sc-card panel">
            <h2 class="panel-title">Materials by Year Level</h2>
            <p class="panel-sub">Which year levels have the most and least materials</p>

            <?php if ($yearLevels === []): ?>
                <p class="empty-state">No materials yet.</p>
            <?php else: ?>
                <div class="year-chart" role="img" aria-label="Materials per year level">
                    <?php foreach ($yearLevels as $y): ?>
                        <div class="year-col<?= count($yearLevels) > 1 && $y['count'] === $yearMin ? ' is-least' : '' ?>">
                            <div class="year-bar-area">
                                <div class="vbar" style="--h: <?= $y['count'] / $yearMax * 100 ?>%"><span><?= (int) $y['count'] ?></span></div>
                            </div>
                            <span class="month-label"><?= esc($y['name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Committee review results -->
        <section class="sc-card panel">
            <h2 class="panel-title">Committee Review Results</h2>
            <p class="panel-sub">
                <?= $reviewTotal ? 'Last 6 months, ' . number_format($reviewTotal) . ' materials checked' : 'Last 6 months' ?>
            </p>

            <div class="split-bar<?= $reviewTotal ? '' : ' is-empty' ?>">
                <?php if ($reviewTotal): ?>
                    <span class="split-ok" style="width: <?= $acceptedPct ?>%"></span>
                    <span class="split-bad" style="width: <?= $returnedPct ?>%"></span>
                <?php endif; ?>
            </div>
            <div class="split-legend">
                <span class="text-ok"><?= stat_value($review['accepted']) ?> accepted<?= $reviewTotal ? " ({$acceptedPct}%)" : '' ?></span>
                <span class="text-bad"><?= stat_value($review['returned']) ?> returned<?= $reviewTotal ? " ({$returnedPct}%)" : '' ?></span>
            </div>

            <h3 class="panel-subhead">Latest return remarks</h3>
            <?php if ($review['remarks'] === []): ?>
                <p class="empty-state empty-sm">No returned materials yet.</p>
            <?php else: ?>
                <ul class="remark-list">
                    <?php foreach ($review['remarks'] as $r): ?>
                        <li><strong><?= esc($r['material']) ?>:</strong> <?= esc($r['remark']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Recent activity -->
        <section class="sc-card panel">
            <h2 class="panel-title">Recent Activity</h2>

            <?php if ($activity === []): ?>
                <p class="empty-state empty-sm">No activity yet.</p>
            <?php else: ?>
                <ul class="activity-list">
                    <?php foreach ($activity as $a): ?>
                        <li class="act-<?= esc($a['type'] ?? 'other') ?>">
                            <p><strong><?= esc($a['actor']) ?></strong> <?= esc($a['text']) ?></p>
                            <time><?= esc($a['time']) ?></time>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>

<?= $this->endSection() ?>
