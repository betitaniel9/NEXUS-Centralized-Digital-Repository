<?php
/**
 * One stat card on the SC pages.
 * @var string      $label
 * @var string      $icon   Font Awesome icon class, e.g. 'fa-folder'
 * @var string      $value  Already formatted (use stat_value())
 * @var string|null $unit   Optional word after the number, e.g. 'Days'
 * @var string      $note   Small line under the number
 */
?>
<article class="sc-card stat-card">
    <div class="stat-head">
        <h2 class="stat-label"><?= esc($label) ?></h2>
        <span class="stat-icon" aria-hidden="true"><i class="fa-solid <?= esc($icon) ?>"></i></span>
    </div>
    <p class="stat-value">
        <?= $value ?><?php if (! empty($unit) && $value !== '—'): ?> <span class="stat-unit"><?= esc($unit) ?></span><?php endif; ?>
    </p>
    <p class="stat-note"><?= esc($note) ?></p>
</article>
