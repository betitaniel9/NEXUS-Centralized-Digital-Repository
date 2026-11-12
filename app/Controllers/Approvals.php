<?php

namespace App\Controllers;

class Approvals extends CouncilController
{
    /** Where an item goes after this role approves it. */
    private const NEXT_STEP = [
        'Technical Committee' => 'Secretary',
        'Secretary'           => 'Vice President',
        'Vice-President'      => 'President',
        'President'           => 'Publishing',
    ];

    public function index(): string
    {
        $role = session()->get('council_role');

        // TODO(db): fill from approvals (decision 'Pending' for this user's role) + submissions + materials.
        return $this->render('approvals/index', 'approval', 'Approval', 'Materials waiting for your approval', [
            'stats' => [
                'waiting'      => null, // pending items at this role's stage
                'approvedWeek' => null, // approved by this user in the last 7 days
                'oldestDays'   => null, // days the oldest pending item has waited
                'oldestTitle'  => null,
                'avgDays'      => null, // average days from arrival to this user's decision
            ],
            'nextStep' => self::NEXT_STEP[$role] ?? 'the next step',

            // [['id' => int, 'title' => '...', 'repository' => '...', 'yearLevel' => '...',
            //   'submittedAt' => '...', 'fileType' => 'PDF', 'fileUrl' => '...'], ...]
            'files' => [],

            'filters'      => $this->currentFilters(),
            'repositories' => [], // categories: [['id' => int, 'name' => '...'], ...]
            'yearLevels'   => [], // year_levels: [['id' => int, 'name' => '...'], ...]
            'fileTypes'    => [], // distinct materials.file_type: ['PDF', 'DOCX', ...]
        ]);
    }
}
