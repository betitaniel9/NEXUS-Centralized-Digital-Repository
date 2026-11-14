<?php

/**
 * Small display helpers for the Student Council (SC) pages.
 */

if (! function_exists('stat_value')) {
    /** Number for a stat card / chart label; null (no data yet) shows a dash. */
    function stat_value($value, int $decimals = 0): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return is_numeric($value) ? number_format((float) $value, $decimals) : esc($value);
    }
}

if (! function_exists('submission_step')) {
    /**
     * Maps submissions.submission_status to the label + pill colour shown in the tables.
     *
     * @return array{label: string, class: string, group: string}
     */
    function submission_step(string $status): array
    {
        return match ($status) {
            'Pending Review',
            'Under Technical Review' => ['label' => 'Committee Review',    'class' => 'pill-amber', 'group' => 'review'],
            'Pending Secretary'      => ['label' => 'Secretary',           'class' => 'pill-blue',  'group' => 'approval'],
            'Pending Vice President' => ['label' => 'Vice President',      'class' => 'pill-blue',  'group' => 'approval'],
            'Pending President'      => ['label' => 'President',           'class' => 'pill-blue',  'group' => 'approval'],
            'Approved'               => ['label' => 'Ready to Publish',    'class' => 'pill-teal',  'group' => 'approval'],
            'Published'              => ['label' => 'Published',           'class' => 'pill-green', 'group' => 'published'],
            'Rejected',
            'Revision Required'      => ['label' => 'Returned to Student', 'class' => 'pill-red',   'group' => 'returned'],
            default                  => ['label' => $status,               'class' => 'pill-gray',  'group' => 'other'],
        };
    }
}

if (! function_exists('short_date')) {
    /** '2026-11-07 10:00:00' → 'Nov 7' */
    function short_date(?string $date): string
    {
        return $date ? date('M j', strtotime($date)) : '—';
    }
}

if (! function_exists('format_bytes')) {
    /** materials.file_size total → '1.2 GB' */
    function format_bytes(?int $bytes): string
    {
        if ($bytes === null) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = 0;
        $size  = (float) $bytes;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return number_format($size, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}

if (! function_exists('week_range_label')) {
    /** Monday–Sunday of the current week, e.g. '9–15 November 2026' or '30 November – 6 December 2026'. */
    function week_range_label(?int $now = null): string
    {
        $now   = $now ?? time();
        $start = strtotime('monday this week', $now);
        $end   = strtotime('sunday this week', $now);

        if (date('Y', $start) !== date('Y', $end)) {
            return date('j F Y', $start) . ' – ' . date('j F Y', $end);
        }

        if (date('n', $start) !== date('n', $end)) {
            return date('j F', $start) . ' – ' . date('j F Y', $end);
        }

        return date('j', $start) . '–' . date('j F Y', $end);
    }
}

if (! function_exists('days_label')) {
    /** 0 → 'Today', 1 → '1 day', 8 → '8 days' */
    function days_label(?int $days): string
    {
        if ($days === null) {
            return '—';
        }

        return $days === 0 ? 'Today' : $days . ' ' . ($days === 1 ? 'day' : 'days');
    }
}

if (! function_exists('material_state')) {
    /**
     * Pill for a material's state on the committee pages.
     * 'active' | 'broken' | 'old' → ['label' => ..., 'class' => ...]
     */
    function material_state(string $state): array
    {
        return match ($state) {
            'active' => ['label' => 'Active',      'class' => 'pill-soft-green'],
            'broken' => ['label' => 'Broken link', 'class' => 'pill-soft-red'],
            'old'    => ['label' => 'Old',         'class' => 'pill-soft-amber'],
            default  => ['label' => ucfirst($state), 'class' => 'pill-gray'],
        };
    }
}

if (! function_exists('section_state')) {
    /** Semester review state of a section: 'in_progress' | 'ready' | 'reviewed' */
    function section_state(string $state): array
    {
        return match ($state) {
            'reviewed' => ['label' => 'Reviewed',        'class' => 'pill-soft-green'],
            'ready'    => ['label' => 'Ready to review', 'class' => 'pill-soft-blue'],
            default    => ['label' => 'In progress',     'class' => 'pill-soft-amber'],
        };
    }
}

if (! function_exists('partial')) {
    /**
     * Renders a partial view WITHOUT keeping its variables for later views.
     * (CodeIgniter's view() saves data by default, so an optional variable like a stat card's
     * 'unit' would otherwise leak into the next card.)
     */
    function partial(string $name, array $data = []): string
    {
        return view($name, $data, ['saveData' => false]);
    }
}

if (! function_exists('review_pill')) {
    /**
     * Where a committee upload is in the approval flow (submissions.submission_status → pill).
     * Committee → Secretary → Vice President → President → Published.
     *
     * @return array{label: string, class: string}
     */
    function review_pill(string $status): array
    {
        return match ($status) {
            'Published'              => ['label' => 'Published',             'class' => 'pill-soft-green'],
            'Pending Secretary'      => ['label' => 'With Secretary',        'class' => 'pill-soft-amber'],
            'Pending Vice President' => ['label' => 'With Vice President',   'class' => 'pill-soft-amber'],
            'Pending President'      => ['label' => 'With President',        'class' => 'pill-soft-amber'],
            'Approved'               => ['label' => 'Approved',              'class' => 'pill-soft-blue'],
            'Revision Required',
            'Rejected'               => ['label' => 'Returned',              'class' => 'pill-soft-red'],
            default                  => ['label' => $status,                 'class' => 'pill-gray'],
        };
    }
}
