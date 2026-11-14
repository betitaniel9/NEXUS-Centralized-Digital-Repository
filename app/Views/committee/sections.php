<?= $this->extend('layouts/council_layout') ?>
<?= $this->section('content') ?>
<?php
$status   = $filters['status'] ?? '';
$queryFor = fn (string $state) => base_url('my-sections') . ($state === '' ? '' : '?status=' . $state);

// Stat cards double as quick status filters
$cards = [
    ''         => ['All files', 'fa-folder',            $stats['total'],    $stats['sections'] === null ? 'In every section' : 'Across ' . number_format($stats['sections']) . ' assigned sections'],
    'active'   => ['Published', 'fa-circle-check',      $stats['active'],   'Live in the student library'],
    'review'   => ['In review', 'fa-hourglass-half',    $stats['review'],   'With the Secretary, VP or President'],
    'returned' => ['Returned',  'fa-rotate-left',       $stats['returned'], 'Sent back with remarks'],
];

$sectionNames = array_column($assignedSections, 'name');
?>

<div class="page-head">
    <div>
        <h2 class="page-head-title">Materials in your sections</h2>
        <p class="page-head-sub">
            <?= $stats['sections'] === null ? 'Materials in every section are shown. Add new subjects here when you need them.' : 'Only materials in your ' . number_format($stats['sections']) . ' assigned sections are shown.' ?>
        </p>
    </div>
    <div class="page-head-actions">
        <button type="button" class="sc-btn sc-btn-outline" data-modal-open="addSubjectModal">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Add subject
        </button>
        <button type="button" class="sc-btn" data-modal-open="uploadModal">
            <i class="fa-solid fa-upload" aria-hidden="true"></i> Upload material
        </button>
    </div>
</div>

<nav class="stat-row" aria-label="Filter by status">
    <?php foreach ($cards as $key => [$label, $icon, $value, $note]): ?>
        <a href="<?= $queryFor($key) ?>" class="stat-link<?= $status === $key ? ' is-current' : '' ?>"<?= $status === $key ? ' aria-current="true"' : '' ?>>
            <?= partial('partials/stat_card', ['label' => $label, 'icon' => $icon, 'value' => stat_value($value), 'note' => $note]) ?>
        </a>
    <?php endforeach; ?>
</nav>

<?= partial('partials/filters', [
    'action'       => 'my-sections',
    'filters'      => $filters,
    'repositories' => $repositories,
    'yearLevels'   => $yearLevels,
    'fileTypes'    => $resourceTypes,
    'statuses'     => $states,
    'fields'       => ['q', 'repository', 'year_level', 'status', 'sort'],
    'labels'       => ['q' => 'Search', 'repository' => 'Section', 'year_level' => 'Year level', 'sort' => 'Sort by'],
    'sorts'        => ['newest' => 'Newest first', 'oldest' => 'Oldest first', 'title' => 'Title (A–Z)'],
    'assigned'     => $sectionNames,
]) ?>

<div class="review-grid sections-grid">
    <!-- All materials -->
    <section class="sc-card panel">
        <h2 class="panel-title">All materials</h2>
        <p class="panel-sub">Showing <?= count($materials) ?> of <?= number_format($totalRows) ?> files</p>

        <div class="table-wrap">
            <table class="sc-table">
                <thead>
                    <tr><th>File</th><th>Section</th><th>Year level</th><th>Subject</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if ($materials === []): ?>
                        <tr><td colspan="6" class="empty-cell">No materials here yet. Use “Upload material” to add one.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($materials as $m): $st = review_pill($m['status']); ?>
                        <tr>
                            <td>
                                <span class="file-name"><i class="fa-regular <?= $m['fileType'] === 'link' ? 'fa-link' : 'fa-file' ?>" aria-hidden="true"></i><?= esc($m['title']) ?></span>
                                <?php if ($m['mine']): ?><span class="muted"> · uploaded by you</span><?php endif; ?>
                            </td>
                            <td><?= esc($m['section']) ?></td>
                            <td><?= esc($m['yearLevel']) ?></td>
                            <td><?= esc($m['subject']) ?></td>
                            <td>
                                <span class="pill <?= $st['class'] ?>"><?= esc($st['label']) ?></span>
                                <?php if ($st['label'] === 'Returned' && $m['returnNote']): ?>
                                    <p class="small-line muted return-note"><?= esc($m['returnNote']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= esc($m['fileUrl'], 'attr') ?>" class="sc-btn sc-btn-outline sc-btn-xs" target="_blank" rel="noopener">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1): ?>
            <div class="pager">
                <span>Page <?= (int) $page ?> of <?= (int) $pageCount ?></span>
                <div>
                    <a class="sc-btn sc-btn-outline sc-btn-sm<?= $page <= 1 ? ' is-disabled' : '' ?>"
                       href="<?= current_url() . '?' . http_build_query(['page' => $page - 1] + $filters) ?>">Previous</a>
                    <a class="sc-btn sc-btn-sm<?= $page >= $pageCount ? ' is-disabled' : '' ?>"
                       href="<?= current_url() . '?' . http_build_query(['page' => $page + 1] + $filters) ?>">Next</a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <div class="side-stack">
        <!-- Semester review summary -->
        <section class="sc-card panel">
            <h2 class="panel-title">Semester review summary</h2>
            <?php if ($semester === null): ?>
                <p class="empty-state empty-sm">No semester review is open.</p>
            <?php else: ?>
                <p class="panel-sub"><?= esc($semester['label']) ?></p>
                <p class="big-ratio"><?= (int) $semester['reviewed'] ?> of <?= (int) $semester['sections'] ?> reviewed</p>
                <p class="small-line"><?= (int) $semester['tasksDone'] ?> of <?= (int) $semester['tasksTotal'] ?> tasks done</p>
                <span class="hbar-track progress"><span class="hbar-fill" style="--w: <?= $semester['tasksTotal'] ? $semester['tasksDone'] / $semester['tasksTotal'] * 100 : 0 ?>%"></span></span>
                <p class="small-line muted"><?= (int) $semester['brokenLeft'] ?> broken links · <?= (int) $semester['oldLeft'] ?> old materials left</p>
            <?php endif; ?>
            <a href="<?= base_url('semester-review') ?>" class="sc-btn sc-btn-outline sc-btn-sm side-link">
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i> Go to Semester review
            </a>
        </section>

        <!-- Library structure: sections, year levels, subjects -->
        <section class="sc-card panel">
            <h2 class="panel-title">Sections</h2>
            <p class="panel-sub">The folders students browse in the library</p>
            <?php if ($assignedSections === []): ?>
                <p class="empty-state empty-sm">The sections have not been set up yet. Run <code>php spark db:seed LibraryStructureSeeder</code>.</p>
            <?php else: ?>
                <ul class="line-list">
                    <?php foreach ($assignedSections as $s): ?>
                        <li><div><strong><?= esc($s['name']) ?></strong><span><?= (int) $s['files'] ?> <?= (int) $s['files'] === 1 ? 'file' : 'files' ?></span></div></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="sc-card panel">
            <h2 class="panel-title">Subjects</h2>
            <p class="panel-sub">Folders inside each year level (1st–4th year)</p>
            <?php if ($subjects === []): ?>
                <p class="empty-state empty-sm">No subjects yet. Files without one go to “General”.</p>
            <?php else: ?>
                <p class="chip-row">
                    <?php foreach ($subjects as $s): ?><span class="chip"><?= esc($s['name']) ?></span><?php endforeach; ?>
                </p>
            <?php endif; ?>
            <button type="button" class="sc-btn sc-btn-outline sc-btn-sm side-link" data-modal-open="addSubjectModal">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Add subject
            </button>
        </section>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?php
$sectionOptions = fn (string $placeholder) => '<option value="">' . $placeholder . '</option>' . implode('', array_map(
    fn ($r) => '<option value="' . esc($r['id']) . '">' . esc($r['name']) . '</option>', $repositories));
$yearOptions = '<option value="">All years (not year-specific)</option>' . implode('', array_map(
    fn ($y) => '<option value="' . esc($y['id']) . '">' . esc($y['name']) . '</option>', $yearLevels));
$typeOptions = '<option value="">Choose type</option>' . implode('', array_map(
    fn ($t) => '<option value="' . esc($t) . '">' . esc($t) . '</option>', $resourceTypes));
$subjectOptions = '<option value="">General (no subject)</option>' . implode('', array_map(
    fn ($s) => '<option value="' . esc($s['id']) . '">' . esc($s['name']) . '</option>', $subjects));
$acceptExt = implode(',', array_map(fn ($e) => '.' . $e, $allowedExt));
$extLabel  = strtoupper(implode(', ', $allowedExt)) . ' · max ' . $maxMb . ' MB';
?>

<!-- Upload material -->
<dialog class="sc-modal" id="uploadModal" aria-labelledby="uploadTitle">
    <form method="post" action="<?= base_url('my-sections/upload') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="uploadTitle">Upload material</h2>
                <p class="modal-sub">Add a checked material. It goes to the Secretary, then the Vice President, then the President before students can see it.</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>

        <div class="modal-body">
            <div class="form-field">
                <span class="field-label">File</span>
                <div class="file-pick">
                    <i class="fa-regular fa-file" aria-hidden="true"></i>
                    <div>
                        <strong data-pick="name">No file chosen</strong>
                        <span data-pick="meta" data-pick-default="<?= esc($extLabel, 'attr') ?>"><?= esc($extLabel) ?></span>
                    </div>
                    <label class="sc-btn sc-btn-outline sc-btn-sm">
                        Choose file
                        <input type="file" name="file" class="visually-hidden"
                               accept="<?= esc($acceptExt, 'attr') ?>" data-pick-input data-fill-title="uploadTitleInput">
                    </label>
                </div>
            </div>

            <div class="form-field">
                <label for="uploadTitleInput">Title</label>
                <input type="text" id="uploadTitleInput" name="title" required maxlength="255">
            </div>

            <div class="form-field">
                <label for="uploadSection">Section</label>
                <select id="uploadSection" name="section_id" required><?= $sectionOptions('Choose section') ?></select>
                <?php if ($repositories === []): ?>
                    <p class="note note-warn"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>The sections have not been set up yet. Ask the admin to run the library seeder.</p>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="uploadYear">Year level</label>
                    <select id="uploadYear" name="year_level_id"><?= $yearOptions ?></select>
                </div>
                <div class="form-field">
                    <label for="uploadSubject">Subject</label>
                    <select id="uploadSubject" name="subject_id"><?= $subjectOptions ?></select>
                </div>
            </div>

            <div class="form-field">
                <label for="uploadLink">Google Drive link <span class="muted">(optional)</span></label>
                <input type="url" id="uploadLink" name="link" placeholder="https://drive.google.com/…" maxlength="500">
                <span class="muted field-help">Only needed if you are not choosing a file above.</span>
            </div>
        </div>

        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="sc-btn">Upload material</button>
        </div>
    </form>
</dialog>

<!-- Add subject -->
<dialog class="sc-modal" id="addSubjectModal" aria-labelledby="addSubjectTitle">
    <form method="post" action="<?= base_url('my-sections/subjects') ?>">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="addSubjectTitle">Add subject</h2>
                <p class="modal-sub">Folders inside a year level, e.g. “Data Structures”.</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-field">
                    <label for="subjectCode">Code <span class="muted">(optional)</span></label>
                    <input type="text" id="subjectCode" name="code" maxlength="50" placeholder="e.g. IT 211">
                </div>
                <div class="form-field">
                    <label for="subjectName">Subject name</label>
                    <input type="text" id="subjectName" name="name" required maxlength="150" placeholder="e.g. Data Structures">
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="sc-btn">Add subject</button>
        </div>
    </form>
</dialog>

<!-- Edit file -->
<dialog class="sc-modal" id="editModal" aria-labelledby="editTitle">
    <form method="post" id="editForm" data-fill-action="updateUrl" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="editTitle">Edit file</h2>
                <p class="modal-sub">Update the material details or replace the uploaded file.</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>

        <div class="modal-body">
            <div class="form-field">
                <label for="editName">Name</label>
                <input type="text" id="editName" name="title" required maxlength="255" data-fill-value="title">
            </div>

            <div class="form-field">
                <label for="editSection">Section</label>
                <select id="editSection" name="section_id" required data-fill-value="sectionId"><?= $sectionOptions('Choose section') ?></select>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="editYear">Year level</label>
                    <select id="editYear" name="year_level_id" data-fill-value="yearLevelId"><?= $yearOptions ?></select>
                </div>
                <div class="form-field">
                    <label for="editType">Type</label>
                    <select id="editType" name="resource_type" data-fill-value="type"><?= $typeOptions ?></select>
                </div>
            </div>

            <div class="form-field">
                <label for="editState">Status</label>
                <select id="editState" name="state" data-fill-value="state">
                    <?php foreach ($states as $value => $label): ?>
                        <option value="<?= $value ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <span class="field-label">Replace file</span>
                <div class="replace-row">
                    <span>Current <span data-fill="current"></span><span data-replace-name></span></span>
                    <label class="sc-btn sc-btn-outline sc-btn-sm">
                        Choose replacement
                        <input type="file" name="replacement" class="visually-hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.png,.jpg,.jpeg" data-replace-input>
                    </label>
                </div>
            </div>

            <button type="button" class="sc-btn sc-btn-danger-outline sc-btn-sm" data-step="delete">Delete file</button>
        </div>

        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="button" class="sc-btn" data-step="save">Save changes</button>
        </div>
    </form>
</dialog>

<!-- Confirm: save details -->
<dialog class="sc-modal" id="saveModal" aria-labelledby="saveTitle">
    <div class="modal-head">
        <div>
            <h2 id="saveTitle">Save changes</h2>
            <p class="modal-sub">Save the changes to this material's details?</p>
        </div>
        <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>
    <div class="modal-body">
        <?= partial('committee/_file_chip', ['withType' => true]) ?>
        <p class="modal-hint">Status: <strong data-fill="stateLabel"></strong></p>
        <p>The material details will be updated. The current file will remain unchanged.</p>
    </div>
    <div class="modal-foot">
        <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
        <button type="button" class="sc-btn" data-submit-form="editForm">Save changes</button>
    </div>
</dialog>

<!-- Confirm: replace file -->
<dialog class="sc-modal" id="replaceModal" aria-labelledby="replaceTitle">
    <div class="modal-head">
        <div>
            <h2 id="replaceTitle">Update material</h2>
            <p class="modal-sub">Replace the current file with the selected replacement?</p>
        </div>
        <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>
    <div class="modal-body">
        <?= partial('committee/_file_chip', ['withType' => true]) ?>
        <dl class="plain-dl">
            <dt>Current file</dt><dd data-fill="originalTitle"></dd>
            <dt>Selected replacement</dt><dd data-fill="replacement"></dd>
        </dl>
        <p>The new file will replace the current version. The material details will be retained.</p>
    </div>
    <div class="modal-foot">
        <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
        <button type="button" class="sc-btn" data-submit-form="editForm">Update material</button>
    </div>
</dialog>

<!-- Confirm: delete -->
<dialog class="sc-modal" id="deleteModal" aria-labelledby="deleteTitle">
    <form method="post" data-fill-action="deleteUrl">
        <?= csrf_field() ?>
        <div class="modal-head">
            <div>
                <h2 id="deleteTitle">Delete file</h2>
                <p class="modal-sub">Permanently delete this file from your assigned section?</p>
            </div>
            <button type="button" class="modal-x" data-modal-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
            <?= partial('committee/_file_chip', ['withType' => true]) ?>
            <p class="note note-warn">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                This action cannot be undone. Students will no longer be able to access this material.
            </p>
            <p>The file and its repository details will be deleted.</p>
        </div>
        <div class="modal-foot">
            <button type="button" class="sc-btn sc-btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="sc-btn sc-btn-danger">Delete file</button>
        </div>
    </form>
</dialog>

<?= $this->endSection() ?>
