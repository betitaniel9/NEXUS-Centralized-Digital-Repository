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

<?php if (! $canReview): ?>
    <div class="sc-card empty-panel">
        <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
        <p>Approvals are done by the Secretary, Vice President and President.</p>
    </div>
<?php elseif ($files === []): ?>
    <div class="sc-card empty-panel">
        <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
        <p>No files are waiting for your approval.</p>
    </div>
<?php else: ?>
    <div class="file-grid">
        <?php foreach ($files as $f):
            $payload = [
                'title'      => $f['title'],
                'meta'       => $f['repository'] . ' · ' . $f['yearLevel'] . ' · ' . $f['subject'],
                'next'       => $f['publishes'] ? 'It will be published in the student library right away.' : 'It will be sent to the ' . $f['nextLabel'] . ' next.',
                'approveUrl' => $f['approveUrl'],
                'returnUrl'  => $f['returnUrl'],
            ];
        ?>
            <article class="sc-card file-card">
                <div class="file-preview">
                    <span class="file-type"><?= esc($f['fileType']) ?></span>
                    <?php if ($showStage): ?>
                        <span class="pill pill-soft-amber file-stage"><?= esc($f['stage']) ?></span>
                    <?php endif; ?>
                    <i class="fa-regular <?= $f['fileType'] === 'LINK' ? 'fa-link' : 'fa-file' ?>" aria-hidden="true"></i>
                </div>
                <div class="file-info">
                    <h3><?= esc($f['title']) ?></h3>
                    <p><?= esc($f['repository']) ?> · <?= esc($f['yearLevel']) ?> · <?= esc($f['subject']) ?></p>
                    <p>Uploaded by <?= esc($f['uploader']) ?> · <?= short_date($f['submittedAt']) ?></p>
                    <p class="<?= $f['waitingDays'] > 5 ? 'text-late' : '' ?>">Waiting <?= days_label($f['waitingDays']) ?><?= $f['fileSize'] ? ' · ' . format_bytes($f['fileSize']) : '' ?></p>
                </div>
                <div class="file-actions approval-actions">
                    <a href="<?= esc($f['fileUrl'], 'attr') ?>" class="sc-btn sc-btn-outline sc-btn-sm approval-view" target="_blank" rel="noopener">
                        View File<span class="visually-hidden"> <?= esc($f['title']) ?> (new tab)</span>
                    </a>
                    <button type="button" class="sc-btn sc-btn-danger-outline sc-btn-sm" data-approval-open="returnModal"
                            data-payload="<?= esc(json_encode($payload), 'attr') ?>">Return</button>
                    <button type="button" class="sc-btn sc-btn-sm" data-approval-open="approveModal"
                            data-payload="<?= esc(json_encode($payload), 'attr') ?>"><?= $f['publishes'] ? 'Publish' : 'Approve' ?></button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- Approve -->
<dialog class="sc-modal" id="approveModal" aria-labelledby="approveTitle">
    <form method="post" data-fill-action="approveUrl">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="approveTitle">Approve file</h2>
                <p class="modal-sub" data-fill="title"></p>
            </div>
            <button type="button" class="modal-x" data-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-hint" data-fill="meta"></p>
            <p class="note note-ok"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span data-fill="next"></span></p>
            <div class="form-field">
                <label for="approveRemarks">Remarks <span class="muted">(optional)</span></label>
                <textarea id="approveRemarks" name="remarks" rows="3" maxlength="2000"></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-close>Cancel</button>
            <button type="submit" class="sc-btn">Approve</button>
        </div>
    </form>
</dialog>

<!-- Return -->
<dialog class="sc-modal" id="returnModal" aria-labelledby="returnTitle">
    <form method="post" data-fill-action="returnUrl">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="returnTitle">Return file</h2>
                <p class="modal-sub" data-fill="title"></p>
            </div>
            <button type="button" class="modal-x" data-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-hint" data-fill="meta"></p>
            <p class="note note-warn">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                The file goes back to the Technical Committee and will not be published.
            </p>
            <div class="form-field">
                <label for="returnRemarks">Why is it being returned?</label>
                <textarea id="returnRemarks" name="remarks" rows="4" maxlength="2000" required
                          placeholder="e.g. Pages 3–4 are blurry. Please rescan."></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-close>Cancel</button>
            <button type="submit" class="sc-btn sc-btn-danger">Return file</button>
        </div>
    </form>
</dialog>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Approve / Return pop-ups: fill from the card's data-payload, then open
    (function () {
        let opener = null;

        document.querySelectorAll('[data-approval-open]').forEach(btn => {
            btn.addEventListener('click', () => {
                const dialog = document.getElementById(btn.dataset.approvalOpen);
                let data = {};
                try { data = JSON.parse(btn.dataset.payload); } catch (e) { /* ignore */ }

                const form = dialog.querySelector('form');
                form.reset();
                form.action = data[form.dataset.fillAction] || '';
                dialog.querySelectorAll('[data-fill]').forEach(el => { el.textContent = data[el.dataset.fill] || ''; });
                dialog.querySelector('button[type="submit"]').textContent =
                    dialog.id === 'approveModal' && btn.textContent.trim() === 'Publish' ? 'Approve and publish' :
                    dialog.id === 'approveModal' ? 'Approve' : 'Return file';

                opener = btn;
                dialog.showModal();
                dialog.querySelector('textarea').focus();
            });
        });

        document.querySelectorAll('dialog.sc-modal').forEach(dialog => {
            dialog.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => dialog.close()));
            dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
            dialog.addEventListener('close', () => { if (opener) opener.focus(); });
            // Prevent double submits
            dialog.querySelector('form').addEventListener('submit', e => {
                const btn = e.target.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.textContent = 'Saving…';
            });
        });
    })();
</script>
<?= $this->endSection() ?>
