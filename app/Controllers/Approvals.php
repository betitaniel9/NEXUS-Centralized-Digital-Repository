<?php

namespace App\Controllers;

use App\Libraries\MaterialFiles;
use App\Models\MaterialModel;
use App\Models\SubmissionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Approval page: Secretary → Vice President → President.
 * Each officer sees the files waiting at their stage and approves or returns them.
 * The President's approval publishes the file to the student library. Admin may act at any stage.
 */
class Approvals extends CouncilController
{
    public function index(): string
    {
        $roles   = session()->get('roles') ?? [];
        $stages  = SubmissionModel::stagesFor($roles);
        $model   = new SubmissionModel();
        $filters = $this->currentFilters();
        $userId  = (int) session()->get('user_id');

        $statuses = array_column($stages, 'status');
        $rows     = $model->waitingAt($statuses, $filters);
        $allRows  = $filters['q'] === '' && $filters['repository'] === '' && $filters['year_level'] === '' && $filters['file_type'] === ''
            ? $rows
            : $model->waitingAt($statuses);

        // Oldest = waited longest at its current stage (updated_at changes when the status moves).
        // waiting_days is worked out by the database so its clock / time zone is used.
        $oldest = null;
        foreach ($allRows as $r) {
            if ($oldest === null || $r['updated_at'] < $oldest['updated_at']) {
                $oldest = $r;
            }
        }

        $stats   = $model->approverStats($userId);
        $options = (new MaterialModel())->options();
        $single  = count($stages) === 1 ? reset($stages) : null;

        return $this->render('approvals/index', 'approval', 'Approval', 'Materials waiting for your approval', [
            'stats' => [
                'waiting'      => count($allRows),
                'approvedWeek' => $stats['approvedWeek'],
                'oldestDays'   => $oldest ? (int) $oldest['waiting_days'] : null,
                'oldestTitle'  => $oldest['title'] ?? null,
                'avgDays'      => $stats['avgDays'],
            ],
            'nextStep'  => $single['nextLabel'] ?? 'the next step',
            'canReview' => $stages !== [],
            'showStage' => count($stages) > 1, // Admin: label each card with its stage

            'files' => array_map(static function (array $r) {
                $stage = SubmissionModel::stageForStatus($r['submission_status']);
                $isLink = MaterialFiles::externalUrl($r) !== null;

                return [
                    'id'          => (int) $r['submission_id'],
                    'title'       => $r['title'],
                    'repository'  => $r['category_name'],
                    'yearLevel'   => $r['year_level_name'] ?? 'All years',
                    'subject'     => $r['subject_name'] ?? 'General',
                    'uploader'    => trim(($r['first_name'] ?? '') . ' ' . ($r['Last_name'] ?? '')) ?: 'Unknown',
                    'submittedAt' => $r['submitted_at'],
                    'waitingDays' => max(0, (int) $r['waiting_days']),
                    'fileType'    => $isLink ? 'LINK' : (strtoupper(MaterialFiles::extension($r)) ?: 'FILE'),
                    'fileSize'    => $isLink ? null : (int) $r['file_size'],
                    'fileUrl'     => base_url('council/files/' . (int) $r['material_id']),
                    'stage'       => $stage['stage'] ?? '',
                    'nextLabel'   => $stage['nextLabel'] ?? '',
                    'publishes'   => ($stage['next'] ?? '') === 'Published',
                    'approveUrl'  => base_url('approval/' . (int) $r['submission_id'] . '/approve'),
                    'returnUrl'   => base_url('approval/' . (int) $r['submission_id'] . '/return'),
                ];
            }, $rows),

            'filters'      => $filters,
            'repositories' => $options['repositories'],
            'yearLevels'   => $options['yearLevels'],
            'fileTypes'    => $model->fileTypesAt($statuses),
            'useModals'    => true,
        ]);
    }

    public function approve(int $submissionId): RedirectResponse
    {
        $remarks = trim((string) $this->request->getPost('remarks'));
        if (mb_strlen($remarks) > 2000) {
            return redirect()->back()->with('error', 'Remarks are too long (max 2000 characters).');
        }

        return $this->decide($submissionId, 'Approved', $remarks);
    }

    public function returnFile(int $submissionId): RedirectResponse
    {
        $remarks = trim((string) $this->request->getPost('remarks'));
        if ($remarks === '') {
            return redirect()->back()->with('error', 'Please write why the file is being returned.');
        }
        if (mb_strlen($remarks) > 2000) {
            return redirect()->back()->with('error', 'Remarks are too long (max 2000 characters).');
        }

        return $this->decide($submissionId, 'Revision Required', $remarks);
    }

    /** Opens any material (published or not) for Council members, e.g. "View File" on Approval / My sections. */
    public function file(int $materialId)
    {
        $material = (new MaterialModel())->find($materialId)
            ?? throw PageNotFoundException::forPageNotFound('That file does not exist.');

        return MaterialFiles::respond($this->response, $material, true);
    }

    private function decide(int $submissionId, string $decision, string $remarks): RedirectResponse
    {
        $model      = new SubmissionModel();
        $submission = $model->findWithDetails($submissionId);
        if ($submission === null) {
            return redirect()->to(base_url('approval'))->with('error', 'That submission no longer exists.');
        }

        // The stage it is at must be one this user may act on
        $stage = SubmissionModel::stageForStatus($submission['submission_status']);
        $mine  = SubmissionModel::stagesFor(session()->get('roles') ?? []);
        if ($stage === null || ! isset($mine[$stage['role']])) {
            return redirect()->to(base_url('approval'))
                ->with('error', 'This file is not waiting for your approval (it may already have been handled).');
        }

        $newStatus = $model->decide($submission, $stage['role'], $decision, $remarks, (int) session()->get('user_id'));
        if ($newStatus === false) {
            return redirect()->to(base_url('approval'))
                ->with('error', 'This file was already handled by someone else. The list has been refreshed.');
        }

        $title = '"' . $submission['title'] . '"';
        $message = match (true) {
            $decision !== 'Approved'   => $title . ' was returned to the Technical Committee with your remarks.',
            $newStatus === 'Published' => $title . ' is approved and now published in the student library.',
            default                    => $title . ' is approved and sent to the ' . $stage['nextLabel'] . '.',
        };

        return redirect()->to(base_url('approval'))->with('success', $message);
    }
}
