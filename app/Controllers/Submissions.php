<?php

namespace App\Controllers;

class Submissions extends CouncilController
{
    public function index(): string
    {
        // TODO(db): load from submissions + materials + users + categories + year_levels + approvals.
        // Each item:
        // [
        //   'id'          => int,
        //   'title'       => 'Data Structure Reviewer',
        //   'student'     => 'Juan Dela Cruz',
        //   'yearLevel'   => '2nd Year',            // materials.year_level_id
        //   'repository'  => 'Academic Reviewer',   // categories.category_name
        //   'submittedAt' => '2026-11-07 10:00:00',
        //   'status'      => 'Pending Review',      // submissions.submission_status
        //   'fileUrl'     => '...',
        //   'tracking'    => [                      // built from approvals
        //       ['step' => 'Submitted', 'state' => 'done|current|todo', 'note' => 'Received Nov 7'],
        //       ...
        //   ],
        // ]
        $submissions = [];

        return $this->render('submissions/index', 'submissions', 'Submissions', 'Every material submitted and where it is now', [
            'submissions' => $submissions,
        ]);
    }
}
