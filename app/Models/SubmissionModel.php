<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Model;

/**
 * Review workflow for materials uploaded by the Technical Committee:
 *
 *   Committee uploads → Pending Secretary → Pending Vice President → Pending President → Published
 *
 * Any officer can return a file instead (status 'Revision Required' + remarks).
 * Every decision is kept as a row in `approvals`; the student library only shows 'Published' files.
 */
class SubmissionModel extends Model
{
    protected $table         = 'submissions';
    protected $primaryKey    = 'submission_id';
    protected $returnType    = 'array';
    protected $allowedFields = ['material_id', 'submitted_by', 'submission_status', 'submission_notes', 'rejection_reason'];
    protected $useTimestamps = false;

    /**
     * The officer stages, keyed by roles.role_name.
     * status    = submissions.submission_status while waiting for this officer
     * stage     = approvals.approval_stage value
     * next      = status after this officer approves
     * nextLabel = shown on the Approval page ("Sent to …")
     */
    public const STAGES = [
        'Secretary' => [
            'status' => 'Pending Secretary', 'stage' => 'Secretary',
            'next' => 'Pending Vice President', 'nextLabel' => 'Vice President',
        ],
        'Vice-President' => [
            'status' => 'Pending Vice President', 'stage' => 'Vice President',
            'next' => 'Pending President', 'nextLabel' => 'President',
        ],
        'President' => [
            'status' => 'Pending President', 'stage' => 'President',
            'next' => 'Published', 'nextLabel' => 'the student library',
        ],
    ];

    /**
     * Stages this user may act on. Admin may act on all of them.
     *
     * @param list<string> $roles
     * @return array<string, array> role name => STAGES entry
     */
    public static function stagesFor(array $roles): array
    {
        if (in_array('Admin', $roles, true)) {
            return self::STAGES;
        }

        return array_intersect_key(self::STAGES, array_flip($roles));
    }

    /** STAGES entry whose 'status' matches, plus its role name under 'role'. */
    public static function stageForStatus(string $status): ?array
    {
        foreach (self::STAGES as $role => $s) {
            if ($s['status'] === $status) {
                return $s + ['role' => $role];
            }
        }

        return null;
    }

    private function roleId(string $roleName): int
    {
        $row = $this->db->table('roles')->select('role_id')->where('role_name', $roleName)->get()->getRowArray();

        return (int) ($row['role_id'] ?? 0);
    }

    private function log(int $userId, string $action, string $description, string $entity, int $entityId): void
    {
        $this->db->table('activity_logs')->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => mb_substr($description, 0, 65535),
            'entity_type' => $entity,
            'entity_id'   => $entityId,
            'ip_address'  => service('request')->getIPAddress(),
        ]);
    }

    // ---------------------------------------------------------------- Committee upload

    /**
     * Saves a material uploaded by a Technical Committee member and sends it to the Secretary.
     * The upload itself counts as the committee's technical review (an 'Approved' approvals row).
     *
     * @param array $material materials columns (title, category_id, year_level_id, subject_id, file_*)
     * @return int|false New submission_id
     */
    public function createFromCommittee(array $material, int $userId)
    {
        $db = $this->db;
        $db->transStart();

        $db->table('materials')->insert($material + ['uploaded_by' => $userId]);
        $materialId = (int) $db->insertID();

        $db->table('submissions')->insert([
            'material_id'       => $materialId,
            'submitted_by'      => $userId,
            'submission_status' => 'Pending Secretary',
            'submission_notes'  => 'Uploaded by the Technical Committee.',
        ]);
        $submissionId = (int) $db->insertID();

        $db->table('approvals')->insert([
            'submission_id'  => $submissionId,
            'approver_id'    => $userId,
            'role_id'        => $this->roleId('Technical Committee'),
            'approval_stage' => 'Technical Committee',
            'decision'       => 'Approved',
            'remarks'        => 'Uploaded and checked by the Technical Committee.',
            'reviewed_at'    => new RawSql('NOW()'), // database clock, same as the other timestamps
        ]);

        $this->log($userId, 'material_uploaded', 'Uploaded "' . $material['title'] . '" for review.', 'submission', $submissionId);

        $db->transComplete();

        return $db->transStatus() ? $submissionId : false;
    }

    // ---------------------------------------------------------------- Approval page

    /** Submissions joined with their material, names and uploader. */
    private function withDetails(): BaseBuilder
    {
        return $this->db->table('submissions sb')
            ->select('sb.submission_id, sb.submission_status, sb.submitted_at, sb.updated_at, sb.rejection_reason,
                      TIMESTAMPDIFF(DAY, sb.updated_at, NOW()) AS waiting_days,
                      m.material_id, m.title, m.file_name, m.file_path, m.file_type, m.file_size,
                      c.category_id, c.category_name, y.year_level_name, s.subject_name,
                      u.first_name, u.Last_name')
            ->join('materials m', 'm.material_id = sb.material_id')
            ->join('categories c', 'c.category_id = m.category_id')
            ->join('year_levels y', 'y.year_level_id = m.year_level_id', 'left')
            ->join('subjects s', 's.subject_id = m.subject_id', 'left')
            ->join('users u', 'u.user_id = sb.submitted_by', 'left');
    }

    /**
     * Files waiting at the given statuses, filtered like the other SC lists.
     *
     * @param list<string> $statuses
     * @param array $filters q, repository (category id), year_level (id), file_type, sort
     */
    public function waitingAt(array $statuses, array $filters = []): array
    {
        if ($statuses === []) {
            return [];
        }

        $b = $this->withDetails()->whereIn('sb.submission_status', $statuses);

        if (($filters['q'] ?? '') !== '') {
            $b->groupStart()->like('m.title', $filters['q'])->orLike('m.file_name', $filters['q'])->groupEnd();
        }
        if (ctype_digit((string) ($filters['repository'] ?? ''))) {
            $b->where('m.category_id', (int) $filters['repository']);
        }
        if (ctype_digit((string) ($filters['year_level'] ?? ''))) {
            $b->where('m.year_level_id', (int) $filters['year_level']);
        }
        if (($filters['file_type'] ?? '') !== '') {
            $b->where('m.file_type', strtolower($filters['file_type']));
        }

        match ($filters['sort'] ?? 'oldest') {
            'newest' => $b->orderBy('sb.updated_at', 'DESC'),
            'title'  => $b->orderBy('m.title', 'ASC'),
            default  => $b->orderBy('sb.updated_at', 'ASC'),
        };

        return $b->get()->getResultArray();
    }

    public function findWithDetails(int $submissionId): ?array
    {
        return $this->withDetails()->where('sb.submission_id', $submissionId)->get()->getRowArray();
    }

    /** Distinct file types among files waiting at the given statuses (for the filter dropdown). */
    public function fileTypesAt(array $statuses): array
    {
        if ($statuses === []) {
            return [];
        }

        $rows = $this->db->table('submissions sb')
            ->select('DISTINCT m.file_type', false)
            ->join('materials m', 'm.material_id = sb.material_id')
            ->whereIn('sb.submission_status', $statuses)
            ->orderBy('m.file_type')
            ->get()->getResultArray();

        return array_map('strtoupper', array_column($rows, 'file_type'));
    }

    /** Numbers for the Approval page stat cards. */
    public function approverStats(int $userId): array
    {
        $week = $this->db->table('approvals')
            ->where('approver_id', $userId)
            ->where('decision', 'Approved')
            ->where('reviewed_at >= NOW() - INTERVAL 7 DAY', null, false)
            ->countAllResults();

        // Time from the previous decision on the same submission (when it reached this officer) to this decision
        $avg = $this->db->query(
            'SELECT AVG(TIMESTAMPDIFF(SECOND,
                        (SELECT MAX(p.reviewed_at) FROM approvals p
                         WHERE p.submission_id = a.submission_id AND p.reviewed_at < a.reviewed_at),
                        a.reviewed_at)) / 86400 AS days
             FROM approvals a
             WHERE a.approver_id = ? AND a.decision = ? AND a.approval_stage <> ?',
            [$userId, 'Approved', 'Technical Committee']
        )->getRowArray();

        return [
            'approvedWeek' => $week,
            'avgDays'      => $avg['days'] !== null ? round((float) $avg['days'], 1) : null,
        ];
    }

    // ---------------------------------------------------------------- decisions

    /**
     * Records an officer's decision and moves the submission on.
     *
     * @param string $decision 'Approved' | 'Revision Required'
     * @return string|false New submission_status
     */
    public function decide(array $submission, string $roleName, string $decision, string $remarks, int $userId)
    {
        $stage = self::STAGES[$roleName] ?? null;
        if ($stage === null || $submission['submission_status'] !== $stage['status']) {
            return false;
        }

        $newStatus = $decision === 'Approved' ? $stage['next'] : 'Revision Required';

        $db = $this->db;
        $db->transBegin();

        // Only move it if it is still at this stage (e.g. two officers clicking at the same time)
        $db->table('submissions')
            ->where('submission_id', (int) $submission['submission_id'])
            ->where('submission_status', $stage['status'])
            ->update([
                'submission_status' => $newStatus,
                'rejection_reason'  => $decision === 'Approved' ? null : $remarks,
            ]);

        if ($db->affectedRows() !== 1) {
            $db->transRollback();

            return false;
        }

        $db->table('approvals')->insert([
            'submission_id'  => (int) $submission['submission_id'],
            'approver_id'    => $userId,
            'role_id'        => $this->roleId($roleName),
            'approval_stage' => $stage['stage'],
            'decision'       => $decision,
            'remarks'        => $remarks !== '' ? $remarks : null,
            'reviewed_at'    => new RawSql('NOW()'), // database clock, same as the other timestamps
        ]);

        $this->log(
            $userId,
            $decision === 'Approved' ? 'submission_approved' : 'submission_returned',
            $stage['stage'] . ($decision === 'Approved' ? ' approved "' : ' returned "') . $submission['title'] . '"'
                . ($remarks !== '' ? ': ' . $remarks : ''),
            'submission',
            (int) $submission['submission_id']
        );

        if ($db->transStatus() === false) {
            $db->transRollback();

            return false;
        }

        $db->transCommit();

        return $newStatus;
    }

    // ---------------------------------------------------------------- Committee "My sections"

    /**
     * Materials with their latest submission status (no submission = added directly = 'Published').
     *
     * @param array $filters q, repository, year_level, status ('active' | 'review' | 'returned'), sort
     */
    public function materialsWithStatus(array $filters = []): array
    {
        $latest = '(SELECT sb.submission_id FROM submissions sb WHERE sb.material_id = m.material_id
                    ORDER BY sb.submitted_at DESC, sb.submission_id DESC LIMIT 1)';

        $b = $this->db->table('materials m')
            ->select("m.material_id, m.title, m.file_name, m.file_path, m.file_type, m.file_size, m.created_at,
                      m.category_id, c.category_name, m.year_level_id, y.year_level_name, s.subject_name, m.uploaded_by,
                      COALESCE(ls.submission_status, 'Published') AS status, ls.rejection_reason", false)
            ->join('categories c', 'c.category_id = m.category_id')
            ->join('year_levels y', 'y.year_level_id = m.year_level_id', 'left')
            ->join('subjects s', 's.subject_id = m.subject_id', 'left')
            ->join('submissions ls', "ls.submission_id = {$latest}", 'left', false);

        if (($filters['q'] ?? '') !== '') {
            $b->groupStart()->like('m.title', $filters['q'])->orLike('m.file_name', $filters['q'])->groupEnd();
        }
        if (ctype_digit((string) ($filters['repository'] ?? ''))) {
            $b->where('m.category_id', (int) $filters['repository']);
        }
        if (ctype_digit((string) ($filters['year_level'] ?? ''))) {
            $b->where('m.year_level_id', (int) $filters['year_level']);
        }

        $statusSql = "COALESCE(ls.submission_status, 'Published')";
        match ($filters['status'] ?? '') {
            'active'   => $b->where("{$statusSql} = 'Published'", null, false),
            'review'   => $b->whereIn('ls.submission_status', array_column(self::STAGES, 'status')),
            'returned' => $b->whereIn('ls.submission_status', ['Revision Required', 'Rejected']),
            default    => null,
        };

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $b->orderBy('m.created_at', 'ASC'),
            'title'  => $b->orderBy('m.title', 'ASC'),
            default  => $b->orderBy('m.created_at', 'DESC')->orderBy('m.material_id', 'DESC'),
        };

        return $b->get()->getResultArray();
    }
}
