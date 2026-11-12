<?php
/**
 * File summary box used inside the committee dialogs.
 * With $chip (array) it prints those values; without it, committee.js fills the data-fill spans.
 *
 * @var array|null $chip ['title' => ..., 'student' => ..., 'section' => ..., 'yearLevel' => ..., 'type' => ...]
 * @var bool       $withType Show "· type" (My sections / Semester review) instead of the student name
 */
$chip     ??= null;
$withType ??= false;
$val = fn (string $k) => $chip !== null ? esc($chip[$k] ?? '') : '';
$fill = fn (string $k) => $chip === null ? ' data-fill="' . $k . '"' : '';
?>
<div class="file-chip">
    <i class="fa-regular fa-file" aria-hidden="true"></i>
    <div>
        <strong<?= $fill('title') ?>><?= $val('title') ?></strong>
        <span>
            <?php if ($withType): ?>
                <span<?= $fill('section') ?>><?= $val('section') ?></span> · <span<?= $fill('yearLevel') ?>><?= $val('yearLevel') ?></span> · <span<?= $fill('type') ?>><?= $val('type') ?></span>
            <?php else: ?>
                <span<?= $fill('student') ?>><?= $val('student') ?></span> · <span<?= $fill('section') ?>><?= $val('section') ?></span> · <span<?= $fill('yearLevel') ?>><?= $val('yearLevel') ?></span>
            <?php endif; ?>
        </span>
    </div>
</div>
