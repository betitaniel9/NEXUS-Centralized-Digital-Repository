<?php

namespace App\Controllers;

class Dashboard extends CouncilController
{
    /** Badge-flip loading screen shown right after an SC member signs in. */
    public function loading(): string
    {
        // Keep the "Login successful" message for the dashboard that loads next.
        // (One key at a time: keepFlashdata([...]) does nothing if any key in the list is missing.)
        foreach (['success', 'error'] as $key) {
            if (session()->getFlashdata($key) !== null) {
                session()->keepFlashdata($key);
            }
        }

        return view('dashboard/loading');
    }

    public function index(): string
    {
        if (session()->get('council_role') === 'Technical Committee') {
            return $this->committee();
        }

        // TODO(db): fill everything below from the database.
        // null / [] means "no data yet" and the page shows a dash or an empty message.
        return $this->render('dashboard/index', 'dashboard', 'Dashboard', 'Overview of submissions and repository sections', [
            'stats' => [
                'published'          => null, // submissions with status 'Published'
                'publishedThisMonth' => null,
                'newSubmissions'     => null, // status 'Pending Review' / 'Under Technical Review'
                'pendingApproval'    => null, // status 'Pending Secretary' / 'Pending Vice President' / 'Pending President'
                'contributors'       => null, // distinct submissions.submitted_by
                'newContributors'    => null, // first submission this month
            ],

            // Per month: [['month' => 'Jun', 'submitted' => int, 'published' => int], ...]
            'monthly' => [],

            // Items at each step + average days spent there (from approvals.created_at → reviewed_at)
            'pipeline' => [
                ['stage' => 'Committee review', 'count' => null, 'avgDays' => null],
                ['stage' => 'Secretary',        'count' => null, 'avgDays' => null],
                ['stage' => 'Vice President',   'count' => null, 'avgDays' => null],
                ['stage' => 'President',        'count' => null, 'avgDays' => null],
                ['stage' => 'Ready to Publish', 'count' => null, 'avgDays' => null],
            ],

            // Published materials per category: [['name' => 'Templates', 'count' => int], ...]
            'topics' => [],

            // Materials per year level: [['name' => '1st Year', 'count' => int], ...]
            'yearLevels' => [],

            // Technical Committee decisions in the last 6 months + latest remarks on returned items
            'review' => [
                'accepted' => null,
                'returned' => null,
                'remarks'  => [], // [['material' => '...', 'remark' => '...'], ...]
            ],

            // Submissions stuck at one step for more than 5 days:
            // [['title' => '...', 'status' => 'Pending President', 'repository' => '...', 'days' => int], ...]
            'waiting' => [],

            // Latest activity_logs: [['actor' => 'President', 'text' => 'Approved SQL Quick Guide', 'time' => '1 hour ago', 'type' => 'approved|returned|other'], ...]
            'activity' => [],
        ]);
    }

    /** Technical Committee dashboard: review queue + assigned sections. */
    private function committee(): string
    {
        // TODO(db): fill from approvals / submissions / materials for this member's assigned sections.
        return $this->render('committee/dashboard', 'dashboard', 'Dashboard', 'Overview of your review queue and assigned repository sections', [
            'weekLabel' => week_range_label(),
            'stats'     => [
                'toReview'        => null,
                'overdue'         => null, // waiting more than 5 days
                'acceptedWeek'    => null,
                'returnedWeek'    => null,
                'avgReviewDays'   => null, // arrival → committee decision
                'filesInSections' => null,
                'activeFiles'     => null,
                'brokenLinks'     => null,
                'brokenSections'  => null, // how many sections have a broken link
                'oldMaterials'    => null,
                'sectionsDone'    => null, // sections reviewed this semester
                'sectionsTotal'   => null,
                'tasksDone'       => null,
                'tasksTotal'      => null,
            ],

            // Last four weeks: [['week' => '19 Oct', 'accepted' => int, 'returned' => int], ...]
            'weekly' => [],

            // Items waiting for this member, grouped by age (labels are fixed; counts come from the DB)
            'queueAge' => [
                ['label' => 'Today',            'count' => null],
                ['label' => '1 to 2 days',      'count' => null],
                ['label' => '3 to 5 days',      'count' => null],
                ['label' => 'More than 5 days', 'count' => null, 'late' => true],
            ],

            // [['name' => '...', 'files' => int, 'broken' => int, 'old' => int, 'state' => 'in_progress|ready|reviewed'], ...]
            'sections' => [],

            // From submissions.rejection_reason ("<reason>: <remarks>") this week: [['reason' => '...', 'count' => int], ...]
            'returnReasons' => [],
            'returnedCount' => null,

            // Oldest items waiting more than 5 days: [['title' => '...', 'section' => '...', 'yearLevel' => '...', 'days' => int], ...]
            'oldest' => [],

            // activity_logs for this member's sections: [['title' => 'Accepted SQL joins notes', 'note' => 'Sent to Secretary', 'time' => '35 minutes ago'], ...]
            'activity' => [],
        ]);
    }
}
