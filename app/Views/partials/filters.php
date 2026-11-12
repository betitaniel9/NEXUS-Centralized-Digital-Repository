<?php
/**
 * Filter bar for the SC list pages (Approval, Repository, Review, My sections).
 * Submits as GET (?q=&repository=&year_level=&file_type=&status=&sort=) so the controller can filter the query.
 *
 * @var array  $filters      Current values (CouncilController::currentFilters())
 * @var string $action       Page URL path, e.g. 'approval'
 * @var array  $repositories [['id' => int, 'name' => string], ...]
 * @var array  $yearLevels   [['id' => int, 'name' => string], ...]
 * @var array  $fileTypes    ['PDF', 'DOCX', ...]
 *
 * Optional:
 * @var list<string>         $fields    Which fields to show, in order (default: q, repository, year_level, file_type, sort)
 * @var array<string,string> $labels    Override a field label, e.g. ['repository' => 'Section']
 * @var array<string,string> $statuses  Options for the 'status' field: value => label
 * @var array<string,string> $sorts     Options for 'sort': value => label
 * @var list<string>|null    $assigned  Section names to list under the filters (committee "My assigned sections")
 */
$fields   ??= ['q', 'repository', 'year_level', 'file_type', 'sort'];
$labels   = ($labels ?? []) + [
    'q'          => 'Filters',
    'repository' => 'Repository',
    'year_level' => 'Year Level',
    'file_type'  => 'File Type',
    'status'     => 'Status',
    'sort'       => 'Sort By',
];
$statuses ??= [];
$sorts    ??= ['oldest' => 'Oldest First', 'newest' => 'Newest First', 'title' => 'Title (A–Z)'];
$assigned ??= null;

$sel = fn (string $key, $value) => (string) ($filters[$key] ?? '') === (string) $value ? ' selected' : '';

// "All …" option text for each dropdown
$allText = [
    'repository' => $labels['repository'] === 'Section' ? 'All assigned sections' : 'All Repository',
    'year_level' => 'All year levels',
    'file_type'  => 'All types',
    'status'     => 'All statuses',
];

// Options for each dropdown as value => label
$options = [
    'repository' => array_column($repositories ?? [], 'name', 'id'),
    'year_level' => array_column($yearLevels ?? [], 'name', 'id'),
    'file_type'  => array_combine($fileTypes ?? [], $fileTypes ?? []),
    'status'     => $statuses,
];
?>
<form class="sc-card sc-filters" method="get" action="<?= base_url($action) ?>" role="search">
    <?php foreach ($fields as $field): $id = 'f-' . $action . '-' . $field; ?>
        <?php if ($field === 'q'): ?>
            <div class="filter-field filter-search">
                <label for="<?= $id ?>"><?= esc($labels['q']) ?></label>
                <div class="search-box">
                    <input type="search" id="<?= $id ?>" name="q" placeholder="Search files…" value="<?= esc($filters['q'] ?? '') ?>">
                    <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                </div>
            </div>
        <?php elseif ($field === 'sort'): ?>
            <div class="filter-field">
                <label for="<?= $id ?>"><?= esc($labels['sort']) ?></label>
                <select id="<?= $id ?>" name="sort" data-autosubmit>
                    <?php foreach ($sorts as $value => $label): ?>
                        <option value="<?= esc($value) ?>"<?= $sel('sort', $value) ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <div class="filter-field">
                <label for="<?= $id ?>"><?= esc($labels[$field]) ?></label>
                <select id="<?= $id ?>" name="<?= $field ?>" data-autosubmit>
                    <option value=""><?= esc($allText[$field]) ?></option>
                    <?php foreach ($options[$field] as $value => $label): ?>
                        <option value="<?= esc($value) ?>"<?= $sel($field, $value) ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <a href="<?= base_url($action) ?>" class="sc-btn sc-btn-outline filter-clear">Clear Filters</a>

    <?php if ($assigned !== null): ?>
        <div class="filter-chips">
            <span class="chip">My assigned sections</span>
            <span class="chip-list"><?= $assigned === [] ? 'No sections assigned yet' : esc(implode(', ', $assigned)) ?></span>
        </div>
    <?php endif; ?>
</form>
