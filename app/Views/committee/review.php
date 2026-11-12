<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>
<?php
// Data for the review panel and the dialogs (read by committee.js)
$items = [];
foreach ($queue as $i => $q) {
    $items[$i] = [
        'title'       => $q['title'],
        'student'     => $q['student'],
        'section'     => $q['section'],
        'yearLevel'   => $q['yearLevel'],
        'subject'     => $q['subject'] ?? '—',
        'submitted'   => date('j F Y', strtotime($q['submittedAt'])),
        'daysWaiting' => days_label((int) $q['daysWaiting']),
        'late'        => (int) $q['daysWaiting'] > 5,
        'fileType'    => strtoupper($q['fileType']),
        'fileSize'    => format_bytes($q['fileSize'] ?? null),
        'fileUrl'     => $q['fileUrl'] ?? null,
        'acceptUrl'   => base_url('review/' . (int) $q['id'] . '/accept'),
        'returnUrl'   => base_url('review/' . (int) $q['id'] . '/return'),
    ];
}
$checkCount = count($checklist);
?>

<section class="stat-row" aria-label="Summary">
    <?= partial('partials/stat_card', [
        'label' => 'To review', 'icon' => 'fa-folder',
        'value' => stat_value($stats['toReview']),
        'note'  => 'Across your assigned sections',
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
        'label' => 'Oldest waiting', 'icon' => 'fa-clock',
        'value' => stat_value($stats['oldestDays']), 'unit' => 'days',
        'note'  => $stats['oldestTitle'] ?? 'Nothing waiting',
    ]) ?>
</section>

<?= partial('partials/filters', [
    'action'       => 'review',
    'filters'      => $filters,
    'repositories' => $repositories,
    'yearLevels'   => $yearLevels,
    'fields'       => ['repository', 'year_level', 'sort'],
    'labels'       => ['repository' => 'Section', 'year_level' => 'Year level', 'sort' => 'Sort by'],
    'sorts'        => ['oldest' => 'Oldest first', 'newest' => 'Newest first'],
]) ?>

<?php if ($overdueCount > 0): ?>
    <p class="note note-warn note-banner">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        <?= $overdueCount ?> <?= $overdueCount === 1 ? 'file has' : 'files have' ?> waited more than 5 days. Start with the oldest submission below.
    </p>
<?php endif; ?>

<div class="review-grid">
    <!-- Submission list -->
    <section class="sc-card panel">
        <h2 class="panel-title">Submission list</h2>
        <p class="panel-sub">Showing <?= count($queue) ?> submissions · <?= ($filters['sort'] ?? '') === 'newest' ? 'Newest' : 'Oldest' ?> first</p>

        <div class="table-wrap">
            <table class="sc-table queue-table">
                <thead>
                    <tr><th>Title / section</th><th>Year level</th><th>Waiting</th></tr>
                </thead>
                <tbody>
                    <?php if ($queue === []): ?>
                        <tr><td colspan="3" class="empty-cell">No submissions are waiting for review.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($queue as $i => $q): ?>
                        <tr class="queue-row" tabindex="0" data-index="<?= $i ?>">
                            <td>
                                <span class="cell-title"><?= esc($q['title']) ?></span>
                                <span class="cell-meta"><?= esc($q['section']) ?></span>
                            </td>
                            <td><?= esc($q['yearLevel']) ?></td>
                            <td class="<?= (int) $q['daysWaiting'] > 5 ? 'text-late' : '' ?>"><?= days_label((int) $q['daysWaiting']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Review panel -->
    <section class="sc-card panel review-panel" id="reviewPanel" aria-live="polite">
        <div class="detail-empty">
            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
            <p>Select a submission to review it.</p>
        </div>

        <div class="detail-body" hidden>
            <h2 class="panel-title">Review submission</h2>
            <p class="panel-sub" data-r="progress"></p>

            <div class="review-file">
                <i class="fa-regular fa-file" aria-hidden="true"></i>
                <div>
                    <h3 class="detail-title" data-r="title"></h3>
                    <p class="detail-meta">Submitted by <span data-r="student"></span></p>
                </div>
            </div>

            <div class="review-dates">
                <span>Submitted <span data-r="submitted"></span></span>
                <span class="pill pill-soft-amber" data-r="daysWaiting"></span>
            </div>

            <dl class="fact-box">
                <div><dt>Section</dt><dd data-r="section"></dd></div>
                <div><dt>Year level</dt><dd data-r="yearLevel"></dd></div>
                <div><dt>Subject</dt><dd data-r="subject"></dd></div>
            </dl>

            <div class="review-fileline">
                <span><span data-r="fileType"></span> · <span data-r="fileSize"></span></span>
                <button type="button" class="sc-btn sc-btn-outline sc-btn-sm" data-action="preview">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i> View file
                </button>
            </div>

            <h3 class="panel-subhead">Review checklist</h3>
            <p class="panel-sub">Verify every requirement before sending to the Secretary.</p>
            <ul class="checklist">
                <?php foreach ($checklist as $n => $item): ?>
                    <li>
                        <label>
                            <input type="checkbox" class="check-item" value="<?= esc($item) ?>">
                            <span><?= esc($item) ?></span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="note note-ok" data-r="allChecked" hidden>
                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                All <?= $checkCount ?> requirements verified. This file can be sent to the Secretary.
            </p>

            <div class="review-actions">
                <button type="button" class="sc-btn sc-btn-outline" data-action="return">Return to student</button>
                <button type="button" class="sc-btn" data-action="accept" disabled>Accept and send to Secretary</button>
            </div>
        </div>
    </section>
</div>

<script type="application/json" id="reviewData"><?= json_encode(['items' => $items, 'checkCount' => $checkCount], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>

<!-- File preview -->
<dialog class="sc-modal modal-xl" id="previewModal" aria-labelledby="previewTitle">
    <div class="modal-head">
        <div>
            <span class="file-type-badge" data-fill="fileType"></span>
            <h2 id="previewTitle" data-fill="title"></h2>
            <p class="modal-sub"><span data-fill="section"></span> · <span data-fill="yearLevel"></span> · Submitted <span data-fill="submitted"></span></p>
        </div>
        <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>

    <div class="preview-body">
        <div class="preview-viewer">
            <div class="viewer-bar">
                <span class="viewer-page">Page <span data-pv="page">1</span> of <span data-pv="pages">—</span></span>
                <div class="viewer-zoom">
                    <button type="button" class="sc-btn sc-btn-outline sc-btn-xs" data-pv="zoomOut" aria-label="Zoom out">−</button>
                    <span data-pv="zoom">100%</span>
                    <button type="button" class="sc-btn sc-btn-outline sc-btn-xs" data-pv="zoomIn" aria-label="Zoom in">+</button>
                </div>
            </div>
            <div class="viewer-canvas" data-pv="stage">
                <p class="viewer-msg" data-pv="msg">Loading preview…</p>
            </div>
            <div class="viewer-nav">
                <button type="button" class="sc-btn sc-btn-outline sc-btn-sm" data-pv="prev">Previous page</button>
                <button type="button" class="sc-btn sc-btn-outline sc-btn-sm" data-pv="next">Next page</button>
            </div>
        </div>

        <aside class="preview-details">
            <h3>Details</h3>
            <dl>
                <dt>Student</dt><dd data-fill="student"></dd>
                <dt>Section</dt><dd data-fill="section"></dd>
                <dt>Year level</dt><dd data-fill="yearLevel"></dd>
                <dt>Subject</dt><dd data-fill="subject"></dd>
                <dt>Submitted</dt><dd data-fill="submitted"></dd>
                <dt>Days waiting</dt><dd data-fill="daysWaiting"></dd>
                <dt>File</dt><dd><span data-fill="fileType"></span><span data-pv="pageInfo"></span> · <span data-fill="fileSize"></span></dd>
            </dl>
            <a class="sc-btn sc-btn-outline" data-fill-href="fileUrl" download>
                <i class="fa-solid fa-download" aria-hidden="true"></i> Download file
            </a>
            <p class="note note-warn">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                Inspect all pages, then finish the <?= $checkCount ?>-item checklist in Review.
            </p>
        </aside>
    </div>

    <div class="modal-foot">
        <button type="button" class="sc-btn" data-modal-close>Close preview</button>
    </div>
</dialog>

<!-- Return file -->
<dialog class="sc-modal" id="returnModal" aria-labelledby="returnTitle">
    <form method="post" data-fill-action="returnUrl">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="returnTitle">Return file</h2>
                <p class="modal-sub">Send the file back to the student with clear feedback.</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>

        <div class="modal-body">
            <?= partial('committee/_file_chip') ?>

            <div class="form-field">
                <label for="returnReason">Reason</label>
                <select id="returnReason" name="reason" required>
                    <option value="">Choose a reason</option>
                    <?php foreach ($returnReasons as $reason): ?>
                        <option value="<?= esc($reason) ?>"><?= esc($reason) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="returnRemarks">Remarks for the student</label>
                <textarea id="returnRemarks" name="remarks" rows="4" required maxlength="1000"
                          placeholder="Explain what to fix so the student can resubmit."></textarea>
            </div>

            <p class="modal-hint"><span data-fill="student"></span> will receive your reason and remarks.</p>
        </div>

        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="sc-btn sc-btn-danger">Return file</button>
        </div>
    </form>
</dialog>

<!-- Accept file -->
<dialog class="sc-modal" id="acceptModal" aria-labelledby="acceptTitle">
    <form method="post" data-fill-action="acceptUrl">
        <?= csrf_field() ?>
        <?php foreach ($checklist as $item): ?>
            <input type="hidden" name="checks[]" value="<?= esc($item) ?>">
        <?php endforeach; ?>

        <div class="modal-head">
            <div>
                <h2 id="acceptTitle">Accept file</h2>
                <p class="modal-sub">Confirm your committee decision before sending this file.</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>

        <div class="modal-body">
            <?= partial('committee/_file_chip') ?>
            <p class="note note-ok"><i class="fa-regular fa-circle-check" aria-hidden="true"></i> All <?= $checkCount ?> review requirements are checked.</p>
            <p>Accept this material and send it to the Secretary for the next approval step?</p>
            <p class="modal-hint">This does not publish the file to the student repository.</p>
        </div>

        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="sc-btn">Accept and send to Secretary</button>
        </div>
    </form>
</dialog>

<?php if (is_array($result) && in_array($result['type'] ?? '', ['accepted', 'returned'], true)): $ok = $result['type'] === 'accepted'; ?>
    <!-- Result of the last accept / return (opens on page load) -->
    <dialog class="sc-modal" id="resultModal" data-autoopen aria-labelledby="resultTitle">
        <div class="modal-head">
            <div>
                <h2 id="resultTitle"><?= $ok ? 'You successfully accepted the file' : 'File returned to the student' ?></h2>
                <p class="modal-sub"><?= $ok ? 'The material has moved to the next approval step.' : 'Your feedback has been sent to the student.' ?></p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>

        <div class="modal-body">
            <span class="result-icon <?= $ok ? 'is-ok' : 'is-warn' ?>" aria-hidden="true">
                <i class="fa-solid <?= $ok ? 'fa-check' : 'fa-rotate-left' ?>"></i>
            </span>
            <?= partial('committee/_file_chip', ['chip' => $result]) ?>
            <?php if ($ok): ?>
                <p><?= esc($result['title']) ?> was accepted and sent to the Secretary. It is now awaiting the next approval.</p>
            <?php else: ?>
                <p><?= esc($result['title']) ?> was returned to <?= esc($result['student']) ?> for corrections.</p>
                <div class="note note-warn note-block">
                    <strong>Reason: <?= esc($result['reason']) ?></strong>
                    <span><?= esc($result['remarks']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="modal-foot">
            <button type="button" class="sc-btn" data-modal-close>Back to Review</button>
        </div>
    </dialog>
<?php endif; ?>

<?= $this->endSection() ?>
