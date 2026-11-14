<?php

/**
 * Display helpers for the student library pages.
 */

if (! function_exists('folder_icon')) {
    /** Two-tone folder drawing; its colours come from the .c1–.c8 class on a parent element. */
    function folder_icon(string $class = ''): string
    {
        return '<svg class="folder-ico ' . esc($class, 'attr') . '" viewBox="0 0 48 38" aria-hidden="true" focusable="false">'
            . '<path class="folder-back" d="M5 2h13.2c.8 0 1.6.3 2.1.9L24.6 7H43a4 4 0 0 1 4 4v22a4 4 0 0 1-4 4H5a4 4 0 0 1-4-4V6a4 4 0 0 1 4-4z"/>'
            . '<path class="folder-front" d="M1 15a4 4 0 0 1 4-4h38a4 4 0 0 1 4 4v18a4 4 0 0 1-4 4H5a4 4 0 0 1-4-4z"/>'
            . '</svg>';
    }
}

if (! function_exists('highlight')) {
    /** Escapes $text and wraps every case-insensitive match of $query in <mark>. */
    function highlight(string $text, string $query): string
    {
        $query = trim($query);
        if ($query === '') {
            return esc($text);
        }

        $parts = preg_split('/(' . preg_quote($query, '/') . ')/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return esc($text);
        }

        $out = '';
        foreach ($parts as $i => $part) {
            $out .= $i % 2 === 1 ? '<mark>' . esc($part) . '</mark>' : esc($part);
        }

        return $out;
    }
}

if (! function_exists('initials')) {
    /** 'Juan', 'Dela Cruz' → 'JD' */
    function initials(string $first, string $last): string
    {
        $letters = mb_substr(trim($first), 0, 1) . mb_substr(trim($last), 0, 1);

        return mb_strtoupper($letters) ?: '?';
    }
}
