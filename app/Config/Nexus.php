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

    /**
     * The library sections (categories table), same names, order and descriptions as the landing page folders.
     * The order matters: folder colours follow category_id (1 = blue … 8 = pink).
     * Created by `php spark db:seed LibraryStructureSeeder`; the Committee cannot add or rename these.
     *
     * @var array<string, string> name => description
     */
    public array $sections = [
        'Academic Reviewers'       => 'Reviewer PDFs, summary notes, practice questions and flashcards.',
        'Programming Cheat Sheets' => 'Quick guides for Python, C++ and more.',
        'Templates'                => 'Lab reports, capstone, thesis, resume and portfolio.',
        'Internship Guide'         => 'Requirements, interview tips and a portfolio checklist.',
        'Career Resources'         => 'Resume samples, LinkedIn and GitHub guides, free certifications.',
        'Learning Resources'       => 'General study materials for self paced learning.',
        'Software Repository'      => 'VS Code, GitHub Desktop, MySQL Workbench, XAMPP and Figma.',
        'Student Contributions'    => 'Reviewers and notes shared by your fellow students.',
    ];

    /**
     * Year levels (year_levels table), created by the same seeder.
     * Materials with no year level are shown under "All years".
     *
     * @var list<string>
     */
    public array $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
}
