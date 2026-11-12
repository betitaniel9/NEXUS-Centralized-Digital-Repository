<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Technical Committee workspace: Review, My sections, Semester review.
 * (The committee Dashboard is served by Dashboard::index based on role.)
 *
 * Nothing here reads or writes the database yet. Every list is empty and every
 * action redirects back with a "not connected" notice. Each TODO(db) says what to load or save.
 */
class Committee extends CouncilController
{
    /** The five things a committee member must confirm before accepting a file. */
    public const CHECKLIST = [
        'Original work',
        'Handwritten by the student',
        'Not from a professor or lecturer',
        'Good quality and readable',
        'Content is accurate',
    ];

    /** Reasons offered when returning a file (saved with the remarks in submissions.rejection_reason). */
    public const RETURN_REASONS = [
        'Not original work',
        'Not handwritten',
        'From professor or lecturer',
        'Hard to read',
        'Inaccurate content',
        'Other',
    ];

    /** Material states used on My sections. */
    public const MATERIAL_STATES = [
        'active' => 'Active',
        'broken' => 'Broken link',
        'old'    => 'Old',
    ];

    // ---------------------------------------------------------------- Review

    public function review(): string
    {
        return $this->render('committee/review', 'review', 'Review', 'Check student submissions before sending accepted files to the Secretary', [
            'stats' => [
                'toReview'      => null, // pending items in this member's assigned sections
                'acceptedWeek'  => null, // accepted by this member in the last 7 days
                'returnedWeek'  => null, // returned by this member in the last 7 days
                'oldestDays'    => null,
                'oldestTitle'   => null,
            ],
            'overdueCount' => 0, // items waiting more than 5 days (shows the amber notice)

            // TODO(db): submissions at 'Pending Review' / 'Under Technical Review' in this member's sections.
            // [['id' => int, 'title' => '...', 'section' => '...', 'yearLevel' => '2nd Year', 'subject' => '...',
            //   'student' => '...', 'submittedAt' => '2026-10-31', 'daysWaiting' => int,
            //   'fileType' => 'PDF', 'fileSize' => bytes, 'fileUrl' => '...'], ...]
            'queue' => [],

            'checklist'     => self::CHECKLIST,
            'returnReasons' => self::RETURN_REASONS,

            'filters'      => $this->currentFilters(),
            'repositories' => [], // this member's assigned sections: [['id' => int, 'name' => '...'], ...]
            'yearLevels'   => [],

            // Set by accept()/returnFile() on success to show the result pop-up (see the TODOs there).
            'result' => session()->getFlashdata('result'),
        ]);
    }

    public function accept(int $submissionId): RedirectResponse
    {
        // TODO(db): require all CHECKLIST items in the POST ('checks[]'), then
        //   - set this member's approvals row (stage 'Technical Committee') to decision 'Approved', reviewed_at NOW()
        //   - set submissions.submission_status = 'Pending Secretary'
        //   - insert an activity_logs row
        // then: return redirect()->to(base_url('review'))->with('result', [
        //   'type' => 'accepted', 'title' => ..., 'student' => ..., 'section' => ..., 'yearLevel' => ...]);
        return $this->notConnected();
    }

    public function returnFile(int $submissionId): RedirectResponse
    {
        // TODO(db): validate 'reason' (one of RETURN_REASONS) and 'remarks', then
        //   - approvals row → decision 'Rejected' (or 'Revision Required'), remarks
        //   - submissions.submission_status = 'Revision Required', rejection_reason = "<reason>: <remarks>"
        // then: ->with('result', ['type' => 'returned', 'title' => ..., 'student' => ..., 'section' => ...,
        //   'yearLevel' => ..., 'reason' => ..., 'remarks' => ...]);
        return $this->notConnected();
    }

    // ---------------------------------------------------------------- My sections

    public function sections(): string
    {
        return $this->render('committee/sections', 'my-sections', 'My Sections', 'Upload and organize materials in your assigned repository sections', [
            'stats' => [
                'total'    => null,
                'sections' => null, // number of sections assigned to this member
                'active'   => null,
                'broken'   => null,
                'old'      => null,
            ],

            // TODO(db): materials in this member's sections, filtered by $filters, paged.
            // [['id' => int, 'title' => '...', 'fileName' => 'Data structures practice.pdf', 'section' => '...',
            //   'sectionId' => int, 'yearLevel' => '2nd Year', 'yearLevelId' => int, 'type' => 'Reviewer',
            //   'state' => 'active|broken|old', 'fileType' => 'PDF', 'fileSize' => bytes, 'link' => '...'], ...]
            'materials' => [],
            'page'      => 1,
            'pageCount' => 0,
            'totalRows' => 0,

            // Right column
            'semester' => null, // ['label' => '1st semester · AY 2026–2027', 'reviewed' => int, 'sections' => int,
                                //  'tasksDone' => int, 'tasksTotal' => int, 'brokenLeft' => int, 'oldLeft' => int]
            'assignedSections' => [], // [['id' => int, 'name' => '...', 'files' => int], ...]

            'filters'       => $this->currentFilters(),
            'repositories'  => [], // assigned sections for the dropdowns
            'yearLevels'    => [],
            'resourceTypes' => [], // e.g. ['Reviewer', 'Cheat sheet', 'Template'], no column for this yet
            'states'        => self::MATERIAL_STATES,
        ]);
    }

    public function upload(): RedirectResponse
    {
        // TODO(db): validate the uploaded 'file' + 'title', 'section_id', 'year_level_id', 'resource_type', 'link';
        // move the file to writable/uploads, insert into materials (and a submissions row if it needs review).
        return $this->notConnected();
    }

    public function update(int $materialId): RedirectResponse
    {
        // TODO(db): update materials (title/file_name, category_id, year_level_id, type, state);
        // if a 'replacement' file was uploaded, store it and update file_path / file_type / file_size.
        return $this->notConnected();
    }

    public function delete(int $materialId): RedirectResponse
    {
        // TODO(db): delete the material row and its stored file.
        return $this->notConnected();
    }

    // ---------------------------------------------------------------- Semester review

    public function semester(): string
    {
        return $this->render('committee/semester', 'semester-review', 'Semester Review', 'Fix links, remove old materials and confirm each section for the semester', [
            // TODO(db): no semester table yet. When added:
            // ['label' => '1st semester · AY 2026–2027', 'deadline' => '2026-11-30', 'open' => true]
            'term' => null,

            'stats' => [
                'reviewed'   => null,
                'sections'   => null,
                'brokenLeft' => null,
                'oldLeft'    => null,
                'tasksDone'  => null,
                'tasksTotal' => null,
            ],

            // [['id' => int, 'name' => 'Academic Reviewers', 'files' => int, 'state' => 'in_progress|ready|reviewed',
            //   'tasksDone' => int, 'tasksTotal' => int,
            //   'brokenLinks' => [['id' => int, 'fileName' => '...', 'yearLevel' => '...', 'link' => '...',
            //                      'problem' => '404 error', 'lastChecked' => '2026-11-08', 'done' => bool, 'doneNote' => '...'], ...],
            //   'oldMaterials' => [['id' => int, 'fileName' => '...', 'yearLevel' => '...', 'type' => '...',
            //                       'reason' => 'Outdated syllabus', 'uploaded' => '2023-06-01', 'done' => bool], ...],
            //   'needsNewMaterial' => ['done' => bool, 'note' => '...'],
            //   'checks' => [['label' => 'Verify file classification', 'note' => '...', 'done' => bool], ...],
            // ], ...]
            'sections'   => [],
            'selectedId' => (int) $this->request->getGet('section') ?: null,

            'result' => session()->getFlashdata('result'),
        ]);
    }

    public function fixLink(int $materialId): RedirectResponse
    {
        // TODO(db): validate 'link' (valid_url), save it to materials.file_path, check that it opens, mark the task done.
        return $this->notConnected();
    }

    public function removeMaterial(int $materialId): RedirectResponse
    {
        // TODO(db): remove the outdated material from the student repository and mark the task done.
        return $this->notConnected();
    }

    public function markReviewed(int $sectionId): RedirectResponse
    {
        // TODO(db): only if every task in the section is done: record the section as reviewed for this semester,
        // then ->with('result', ['type' => 'section_reviewed', 'section' => ..., 'term' => ...,
        //   'sectionTasks' => int,                       // tasks in this section
        //   'reviewed' => int, 'sections' => int,        // sections reviewed / assigned
        //   'tasksDone' => int, 'tasksTotal' => int]);   // across all assigned sections
        return $this->notConnected();
    }

    // ---------------------------------------------------------------- helpers

    private function notConnected(): RedirectResponse
    {
        return redirect()->back()->with('error', 'This action is not connected to the database yet.');
    }
}
