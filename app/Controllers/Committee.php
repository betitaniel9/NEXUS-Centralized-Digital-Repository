<?php

namespace App\Controllers;

use App\Libraries\MaterialFiles;
use App\Models\MaterialModel;
use App\Models\SubmissionModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Technical Committee workspace: Review, My sections, Semester review.
 * (The committee Dashboard is served by Dashboard::index based on role.)
 *
 * My sections (list + upload) uses the database. The other pages are not connected yet:
 * their lists are empty and their actions redirect back with a "not connected" notice.
 * Each TODO(db) says what to load or save.
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
        $filters     = $this->currentFilters();
        $filters['sort'] = $this->request->getGet('sort') ?: 'newest';
        $submissions = new SubmissionModel();
        $options     = (new MaterialModel())->options();

        $all  = $submissions->materialsWithStatus();
        $rows = $submissions->materialsWithStatus($filters);
        $userId = (int) session()->get('user_id');

        $count = static fn (callable $test) => count(array_filter($all, $test));

        return $this->render('committee/sections', 'my-sections', 'My Sections', 'Upload and organize materials in your assigned repository sections', [
            // TODO(db): there is no section-assignment table yet, so every section is shown.
            'stats' => [
                'total'    => count($all),
                'sections' => null,
                'active'   => $count(static fn ($m) => $m['status'] === 'Published'),
                'review'   => $count(static fn ($m) => SubmissionModel::stageForStatus($m['status']) !== null),
                'returned' => $count(static fn ($m) => in_array($m['status'], ['Revision Required', 'Rejected'], true)),
            ],

            'materials' => array_map(static fn (array $m) => [
                'id'          => (int) $m['material_id'],
                'title'       => $m['title'],
                'fileName'    => $m['file_name'],
                'section'     => $m['category_name'],
                'sectionId'   => (int) $m['category_id'],
                'yearLevel'   => $m['year_level_name'] ?? 'All years',
                'yearLevelId' => $m['year_level_id'] !== null ? (int) $m['year_level_id'] : '',
                'subject'     => $m['subject_name'] ?? 'General',
                'type'        => '', // no resource-type column yet
                'status'      => $m['status'],
                'returnNote'  => $m['rejection_reason'],
                'mine'        => (int) $m['uploaded_by'] === $userId,
                'fileType'    => MaterialFiles::externalUrl($m) !== null ? 'link' : MaterialFiles::extension($m),
                'fileSize'    => (int) $m['file_size'],
                'fileUrl'     => base_url('council/files/' . (int) $m['material_id']),
            ], $rows),
            'page'      => 1,
            'pageCount' => 1,
            'totalRows' => count($rows),

            // Right column
            'semester' => null, // ['label' => '1st semester · AY 2026–2027', 'reviewed' => int, 'sections' => int,
                                //  'tasksDone' => int, 'tasksTotal' => int, 'brokenLeft' => int, 'oldLeft' => int]
            // No section-assignment table yet: the panel lists every section (the committee can add more).
            'assignedSections' => (new MaterialModel())->sectionsWithCounts(),

            'filters'       => $filters,
            'repositories'  => $options['repositories'],
            'yearLevels'    => $options['yearLevels'],
            'subjects'      => $options['subjects'],
            'resourceTypes' => [], // e.g. ['Reviewer', 'Cheat sheet', 'Template'], no column for this yet
            'states'        => self::UPLOAD_STATES,
            'allowedExt'    => MaterialFiles::ALLOWED_EXT,
            'maxMb'         => (int) (MaterialFiles::MAX_KB / 1024),
        ]);
    }

    /** Status filter on My sections: where each material is in the review flow. */
    public const UPLOAD_STATES = [
        'active'   => 'Published',
        'review'   => 'In review',
        'returned' => 'Returned',
    ];

    /**
     * Committee uploads a checked material (a file or a download link).
     * It goes to the Secretary next: Secretary → Vice President → President → published.
     */
    public function upload(): RedirectResponse
    {
        $file    = $this->request->getFile('file');
        $hasFile = $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
        $link    = trim((string) $this->request->getPost('link'));

        $rules = [
            'title'         => ['label' => 'Title', 'rules' => 'required|max_length[255]'],
            'section_id'    => ['label' => 'Section', 'rules' => 'required|is_natural_no_zero|is_not_unique[categories.category_id]'],
            'year_level_id' => ['label' => 'Year level', 'rules' => 'permit_empty|is_natural_no_zero|is_not_unique[year_levels.year_level_id]'],
            'subject_id'    => ['label' => 'Subject', 'rules' => 'permit_empty|is_natural_no_zero|is_not_unique[subjects.subject_id]'],
            'link'          => [
                'label'  => 'Download link',
                'rules'  => 'permit_empty|max_length[500]|valid_url_strict[https,http]',
                'errors' => ['valid_url_strict' => 'The download link must start with http:// or https://'],
            ],
        ];

        if ($hasFile) {
            $rules['file'] = [
                'label'  => 'File',
                'rules'  => 'uploaded[file]|max_size[file,' . MaterialFiles::MAX_KB . ']|ext_in[file,' . implode(',', MaterialFiles::ALLOWED_EXT) . ']',
                'errors' => [
                    'uploaded' => 'The file could not be uploaded. It may be larger than the server allows.',
                    'max_size' => 'The file is too large (max ' . (int) (MaterialFiles::MAX_KB / 1024) . ' MB).',
                    'ext_in'   => 'Only ' . strtoupper(implode(', ', MaterialFiles::ALLOWED_EXT)) . ' files can be uploaded.',
                ],
            ];
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Upload failed. ' . implode(' ', $this->validator->getErrors()));
        }

        if (! $hasFile && $link === '') {
            return redirect()->back()->withInput()
                ->with('error', 'Upload failed. Choose a file to upload (or paste a Google Drive link).');
        }

        $title  = trim((string) $this->request->getPost('title'));
        $stored = $hasFile ? MaterialFiles::store($file) : MaterialFiles::forLink($link, $title);

        $submissionId = (new SubmissionModel())->createFromCommittee([
            'title'         => $title,
            'category_id'   => (int) $this->request->getPost('section_id'),
            'year_level_id' => ((int) $this->request->getPost('year_level_id')) ?: null,
            'subject_id'    => ((int) $this->request->getPost('subject_id')) ?: null,
        ] + $stored, (int) session()->get('user_id'));

        if ($submissionId === false) {
            if ($hasFile) {
                MaterialFiles::discard($stored);
            }

            return redirect()->back()->withInput()
                ->with('error', 'Upload failed due to a server error. Please try again.');
        }

        return redirect()->to(base_url('my-sections'))
            ->with('success', '"' . $title . '" was uploaded and sent to the Secretary for approval.');
    }

    // ---------------------------------------------------------------- Subjects
    // Sections (the 8 landing-page sections) and year levels (1st–4th) are fixed in Config\Nexus and
    // created by LibraryStructureSeeder. The Committee only adds subjects.

    /** Adds a subject (subjects row), e.g. "IT 211 · Data Structures". */
    public function addSubject(): RedirectResponse
    {
        return $this->addFolder('subjects', 'Subject', [
            'name' => ['label' => 'Subject name', 'rules' => 'required|max_length[150]'],
            'code' => ['label' => 'Subject code', 'rules' => 'permit_empty|max_length[50]|is_unique[subjects.subject_code]',
                       'errors' => ['is_unique' => 'A subject with that code already exists.']],
        ], fn () => [
            'subject_name' => trim((string) $this->request->getPost('name')),
            'subject_code' => trim((string) $this->request->getPost('code')) ?: null, // NULLs don't clash in the unique key
        ]);
    }

    /**
     * @param array    $rules Validation rules for the POST
     * @param callable $row   Builds the row to insert from the POST
     */
    private function addFolder(string $table, string $label, array $rules, callable $row): RedirectResponse
    {
        // Trim before validating so "  1st year " can't slip past the uniqueness check
        $post = array_map(static fn ($v) => is_string($v) ? trim($v) : $v, $this->request->getPost());

        if (! $this->validateData($post, $rules)) {
            return redirect()->back()->withInput()
                ->with('error', $label . ' not added. ' . implode(' ', $this->validator->getErrors()));
        }

        $data = $row();
        $id   = (new MaterialModel())->addFolder($table, $data);
        if ($id === false) {
            return redirect()->back()->withInput()->with('error', $label . ' not added due to a server error.');
        }

        $name = $data['category_name'] ?? $data['year_level_name'] ?? $data['subject_name'];
        db_connect()->table('activity_logs')->insert([
            'user_id'     => (int) session()->get('user_id'),
            'action'      => 'folder_added',
            'description' => $label . ' "' . $name . '" added.',
            'entity_type' => $table,
            'entity_id'   => $id,
            'ip_address'  => $this->request->getIPAddress(),
        ]);

        return redirect()->to(base_url('my-sections'))->with('success', $label . ' "' . $name . '" was added.');
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
