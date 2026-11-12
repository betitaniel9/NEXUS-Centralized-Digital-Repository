<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>
<?php
$ratio = fn ($a, $b) => $a === null || $b === null ? '—' : number_format($a) . ' / ' . number_format($b);

// Selected section: ?section=ID, otherwise the first one
$selected = null;
foreach ($sections as $s) {
    if ($selectedId === null || (int) $s['id'] === $selectedId) {
        $selected = $s;
        break;
    }
}

if ($selected !== null) {
    $brokenLeft = count(array_filter($selected['brokenLinks'], fn ($t) => ! $t['done']));
    $oldLeft    = count(array_filter($selected['oldMaterials'], fn ($t) => ! $t['done']));
    $addLeft    = empty($selected['needsNewMaterial']['done']) ? 1 : 0;
    $checksLeft = count(array_filter($selected['checks'], fn ($t) => ! $t['done']));
    $remaining  = $brokenLeft + $oldLeft + $addLeft + $checksLeft;
    $selState   = section_state($selected['state']);

    $remainParts = array_filter([
        $brokenLeft ? "fix {$brokenLeft} " . ($brokenLeft === 1 ? 'link' : 'links') : null,
        $oldLeft ? "remove {$oldLeft} old " . ($oldLeft === 1 ? 'material' : 'materials') : null,
        $addLeft ? 'add 1 current material' : null,
        $checksLeft ? "finish {$checksLeft} section " . ($checksLeft === 1 ? 'check' : 'checks') : null,
    ]);
}
?>

<div class="page-head">
    <div>
        <h2 class="page-head-title"><?= esc($term['label'] ?? 'Semester review') ?></h2>
        <?php if (! empty($term['deadline'])): ?>
            <p class="page-head-sub">Keep your assigned sections current before <?= date('j F Y', strtotime($term['deadline'])) ?>.</p>
        <?php else: ?>
            <p class="page-head-sub">Keep your assigned sections current for the semester.</p>
        <?php endif; ?>
    </div>
    <?php if (! empty($term['open'])): ?>
        <span class="pill pill-soft-blue">Review period open</span>
    <?php endif; ?>
</div>

<section class="stat-row" aria-label="Semester summary">
    <?= partial('partials/stat_card', [
        'label' => 'Sections reviewed', 'icon' => 'fa-list-check',
        'value' => $ratio($stats['reviewed'], $stats['sections']),
        'note'  => 'Assigned sections',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Broken links left', 'icon' => 'fa-link-slash',
        'value' => stat_value($stats['brokenLeft']),
        'note'  => 'Fix before marking reviewed',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Old materials left', 'icon' => 'fa-box-archive',
        'value' => stat_value($stats['oldLeft']),
        'note'  => 'Remove outdated resources',
    ]) ?>
    <?= partial('partials/stat_card', [
        'label' => 'Tasks done', 'icon' => 'fa-circle-check',
        'value' => $ratio($stats['tasksDone'], $stats['tasksTotal']),
        'note'  => 'Across your assigned sections',
    ]) ?>
</section>

<h2 class="section-label">Your assigned sections</h2>

<?php if ($sections === []): ?>
    <div class="sc-card empty-panel">
        <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
        <p>No sections are assigned to you yet.</p>
    </div>
<?php else: ?>
    <div class="section-cards">
        <?php foreach ($sections as $s):
            $st  = section_state($s['state']);
            $pct = $s['tasksTotal'] ? $s['tasksDone'] / $s['tasksTotal'] * 100 : 0;
            $isSel = $selected !== null && (int) $s['id'] === (int) $selected['id']; ?>
            <a href="<?= base_url('semester-review?section=' . (int) $s['id']) ?>"
               class="sc-card section-card<?= $isSel ? ' is-current' : '' ?> state-<?= esc($s['state']) ?>"<?= $isSel ? ' aria-current="true"' : '' ?>>
                <div class="section-card-head">
                    <span><i class="fa-regular fa-folder" aria-hidden="true"></i> <?= esc($s['name']) ?></span>
                    <span class="muted"><?= (int) $s['files'] ?> files</span>
                </div>
                <span class="pill <?= $st['class'] ?>"><?= $st['label'] ?></span>
                <span class="hbar-track progress"><span class="hbar-fill" style="--w: <?= $pct ?>%"></span></span>
                <span class="small-line muted"><?= (int) $s['tasksDone'] ?> of <?= (int) $s['tasksTotal'] ?> tasks done<?= $isSel ? ' · Selected section' : '' ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Tasks for the selected section -->
    <section class="sc-card panel tasks-panel">
        <h2 class="panel-title"><?= esc($selected['name']) ?> · Section tasks</h2>
        <p class="panel-sub">
            <?= $remaining === 0
                ? 'All ' . (int) $selected['tasksTotal'] . ' tasks complete. Confirm the section review when you are ready.'
                : 'Complete all ' . (int) $selected['tasksTotal'] . ' tasks before marking this section as reviewed.' ?>
        </p>

        <div class="tasks-head">
            <strong><?= (int) $selected['tasksDone'] ?> of <?= (int) $selected['tasksTotal'] ?> tasks done</strong>
            <span class="pill <?= $selState['class'] ?>"><?= $selState['label'] ?></span>
        </div>

        <div class="tasks-grid">
            <div>
                <h3 class="task-group">Broken links to fix</h3>
                <?php if ($selected['brokenLinks'] === []): ?>
                    <p class="empty-state empty-sm">No broken links in this section.</p>
                <?php endif; ?>
                <ul class="task-list">
                    <?php foreach ($selected['brokenLinks'] as $t):
                        $payload = [
                            'title'     => $t['fileName'],
                            'section'   => $selected['name'],
                            'yearLevel' => $t['yearLevel'],
                            'link'      => $t['link'],
                            'problem'   => $t['problem'],
                            'linkUrl'   => base_url('semester-review/link/' . (int) $t['id']),
                        ]; ?>
                        <li class="<?= $t['done'] ? 'is-done' : '' ?>">
                            <i class="task-mark fa-<?= $t['done'] ? 'solid fa-circle-check' : 'regular fa-circle' ?>" aria-hidden="true"></i>
                            <div>
                                <strong><?= esc($t['fileName']) ?></strong>
                                <span><?= $t['done']
                                    ? esc($t['doneNote'] ?? 'New download link verified') . ' · ' . esc($t['yearLevel'])
                                    : esc($t['problem']) . ' · Last checked ' . short_date($t['lastChecked']) . ' · ' . esc($t['yearLevel']) ?></span>
                            </div>
                            <?php if ($t['done']): ?>
                                <span class="pill pill-soft-green">Done</span>
                            <?php else: ?>
                                <button type="button" class="sc-btn sc-btn-outline sc-btn-xs" data-modal-open="fixLinkModal"
                                        data-payload="<?= esc(json_encode($payload), 'attr') ?>">Fix link</button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h3 class="task-group">Old materials to remove</h3>
                <?php if ($selected['oldMaterials'] === []): ?>
                    <p class="empty-state empty-sm">No old materials in this section.</p>
                <?php endif; ?>
                <ul class="task-list">
                    <?php foreach ($selected['oldMaterials'] as $t):
                        $payload = [
                            'title'     => $t['fileName'],
                            'section'   => $selected['name'],
                            'yearLevel' => $t['yearLevel'],
                            'type'      => $t['type'],
                            'reason'    => $t['reason'],
                            'removeUrl' => base_url('semester-review/remove/' . (int) $t['id']),
                        ]; ?>
                        <li class="<?= $t['done'] ? 'is-done' : '' ?>">
                            <i class="task-mark fa-<?= $t['done'] ? 'solid fa-circle-check' : 'regular fa-circle' ?>" aria-hidden="true"></i>
                            <div>
                                <strong><?= esc($t['fileName']) ?></strong>
                                <span><?= $t['done'] ? 'Outdated material removed' : esc($t['reason']) . ' · Uploaded ' . date('F Y', strtotime($t['uploaded'])) ?></span>
                            </div>
                            <?php if ($t['done']): ?>
                                <span class="pill pill-soft-green">Done</span>
                            <?php else: ?>
                                <button type="button" class="sc-btn sc-btn-outline sc-btn-xs" data-modal-open="removeModal"
                                        data-payload="<?= esc(json_encode($payload), 'attr') ?>">Remove</button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h3 class="task-group">Add new materials</h3>
                <ul class="task-list">
                    <li class="<?= $addLeft ? '' : 'is-done' ?>">
                        <i class="task-mark fa-<?= $addLeft ? 'regular fa-circle' : 'solid fa-circle-check' ?>" aria-hidden="true"></i>
                        <div>
                            <strong>Add a current material</strong>
                            <span><?= esc($selected['needsNewMaterial']['note'] ?? 'Upload at least 1 current material for this section.') ?></span>
                        </div>
                        <?php if ($addLeft): ?>
                            <a href="<?= base_url('my-sections') ?>" class="sc-btn sc-btn-outline sc-btn-xs">Go to My sections</a>
                        <?php else: ?>
                            <span class="pill pill-soft-green">Done</span>
                        <?php endif; ?>
                    </li>
                </ul>

                <h3 class="task-group">Section checks</h3>
                <ul class="task-list">
                    <?php foreach ($selected['checks'] as $t): ?>
                        <li class="<?= $t['done'] ? 'is-done' : '' ?>">
                            <i class="task-mark fa-<?= $t['done'] ? 'solid fa-circle-check' : 'regular fa-circle' ?>" aria-hidden="true"></i>
                            <div>
                                <strong><?= esc($t['label']) ?></strong>
                                <span><?= esc($t['note']) ?></span>
                            </div>
                            <span class="pill <?= $t['done'] ? 'pill-soft-green' : 'pill-gray' ?>"><?= $t['done'] ? 'Done' : 'To do' ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php if ($remaining > 0): ?>
            <p class="note note-warn">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                <?= $remaining ?> <?= $remaining === 1 ? 'task remains' : 'tasks remain' ?>: <?= esc(implode(', ', $remainParts)) ?>.
            </p>
        <?php elseif ($selected['state'] !== 'reviewed'): ?>
            <p class="note note-ok">
                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                All tasks are done. <?= esc($selected['name']) ?> can now be marked as reviewed.
            </p>
        <?php endif; ?>

        <div class="tasks-foot">
            <span class="muted"><?= $selected['state'] === 'reviewed' ? 'This section has been reviewed for the current semester.' : 'Marking is available only when every section task is complete.' ?></span>
            <?php if ($selected['state'] !== 'reviewed'): ?>
                <button type="button" class="sc-btn" data-modal-open="markModal"<?= $remaining > 0 ? ' disabled' : '' ?>>Mark section as reviewed</button>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>

<!-- Fix broken link -->
<dialog class="sc-modal" id="fixLinkModal" aria-labelledby="fixTitle">
    <form method="post" id="fixLinkForm" data-fill-action="linkUrl">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="fixTitle">Fix broken link</h2>
                <p class="modal-sub">Replace the unavailable download link and verify it opens.</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
            <p><strong data-fill="title"></strong><br><span class="modal-hint"><span data-fill="section"></span> · <span data-fill="yearLevel"></span></span></p>
            <div class="form-field">
                <label for="fixCurrent">Current link</label>
                <input type="text" id="fixCurrent" readonly data-fill-value="link">
            </div>
            <p class="note note-warn"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Current link returns: <span data-fill="problem"></span>.</p>
            <div class="form-field">
                <label for="fixNew">New link</label>
                <input type="url" id="fixNew" name="link" required placeholder="https://">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="button" class="sc-btn" data-step="verify">Save and verify link</button>
        </div>
    </form>
</dialog>

<!-- Confirm: save and verify link -->
<dialog class="sc-modal" id="verifyModal" aria-labelledby="verifyTitle">
    <div class="modal-head">
        <div>
            <h2 id="verifyTitle">Save and verify link</h2>
            <p class="modal-sub">Save the replacement link and verify that it opens?</p>
        </div>
        <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>
    <div class="modal-body">
        <div class="file-chip">
            <i class="fa-regular fa-file" aria-hidden="true"></i>
            <div><strong data-fill="title"></strong><span><span data-fill="section"></span> · <span data-fill="yearLevel"></span></span></div>
        </div>
        <dl class="plain-dl">
            <dt>Current link</dt><dd data-fill="link"></dd>
            <dt>New link</dt><dd data-fill="newLink"></dd>
        </dl>
        <p>The replacement link will be saved, then verification will run to check that it opens. The new link has not been verified yet.</p>
    </div>
    <div class="modal-foot">
        <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
        <button type="button" class="sc-btn" data-submit-form="fixLinkForm">Save and verify link</button>
    </div>
</dialog>

<!-- Confirm: remove old material -->
<dialog class="sc-modal" id="removeModal" aria-labelledby="removeTitle">
    <form method="post" data-fill-action="removeUrl">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="removeTitle">Remove material</h2>
                <p class="modal-sub">Remove this outdated material from the student repository?</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
            <?= partial('committee/_file_chip', ['withType' => true]) ?>
            <p class="note note-warn"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> <span data-fill="reason"></span></p>
            <p>Removing it will complete 1 task in <span data-fill="section"></span> for this semester.</p>
        </div>
        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="sc-btn sc-btn-danger">Remove material</button>
        </div>
    </form>
</dialog>

<?php if ($selected !== null && $selected['state'] !== 'reviewed'): ?>
    <!-- Confirm: mark section reviewed -->
    <dialog class="sc-modal" id="markModal" aria-labelledby="markTitle">
        <form method="post" action="<?= base_url('semester-review/section/' . (int) $selected['id'] . '/reviewed') ?>">
            <?= csrf_field() ?>
            <div class="modal-head">
                <div>
                    <h2 id="markTitle">Mark section as reviewed</h2>
                    <p class="modal-sub">Confirm that this section is ready for the current semester.</p>
                </div>
                <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <div class="file-chip file-chip-plain">
                    <div>
                        <strong><?= esc($selected['name']) ?></strong>
                        <?php if (! empty($term['label'])): ?><span><?= esc($term['label']) ?></span><?php endif; ?>
                        <span><?= (int) $selected['tasksDone'] ?> of <?= (int) $selected['tasksTotal'] ?> tasks done</span>
                    </div>
                </div>
                <p class="note note-ok"><i class="fa-regular fa-circle-check" aria-hidden="true"></i> All section tasks are complete.</p>
                <p>Mark <?= esc($selected['name']) ?> as reviewed for this semester?</p>
            </div>
            <div class="modal-foot">
                <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="sc-btn">Mark section as reviewed</button>
            </div>
        </form>
    </dialog>
<?php endif; ?>

<?php if (is_array($result) && ($result['type'] ?? '') === 'section_reviewed'): ?>
    <!-- Result (opens on page load) -->
    <dialog class="sc-modal" id="resultModal" data-autoopen aria-labelledby="resultTitle">
        <div class="modal-head">
            <div>
                <h2 id="resultTitle">Section reviewed</h2>
                <p class="modal-sub">Your semester review has been recorded.</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
            <span class="result-icon is-ok" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
            <h3 class="result-name"><?= esc($result['section']) ?></h3>
            <p>All <?= (int) $result['sectionTasks'] ?> tasks are complete. This section is now marked as reviewed<?= ! empty($result['term']) ? ' for ' . esc($result['term']) : '' ?>.</p>
            <p class="note note-ok">
                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                <?= (int) $result['reviewed'] ?> of <?= (int) $result['sections'] ?> sections reviewed · <?= (int) $result['tasksDone'] ?> of <?= (int) $result['tasksTotal'] ?> tasks done
            </p>
        </div>
        <div class="modal-foot">
            <button type="button" class="sc-btn" data-modal-close>Back to Semester review</button>
        </div>
    </dialog>
<?php endif; ?>

<?= $this->endSection() ?>
