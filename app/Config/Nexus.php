<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * NEXUS settings that are not stored in the database.
 * Each value can be overridden in .env, e.g.  nexus.notesFormUrl = https://forms.gle/...
 */
class Nexus extends BaseConfig
{
    /**
     * Google Form where students send their handwritten notes ("Share your notes").
     * Leave empty to show the buttons as unavailable.
     */
    public string $notesFormUrl = '';
}
