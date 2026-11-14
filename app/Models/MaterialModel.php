<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

/**
 * Learning materials.
 *
 * The student library only shows materials students are allowed to see:
 * - materials whose latest submission is 'Published', or
 * - materials with no submission at all (added directly by the Council).
 *
 * Library structure (all taken from the materials row itself):
 *   Category → Year level (NULL = "All years") → Subject (NULL = "General") → files
 * Year / subject id 0 below means "the NULL group".
 */
class MaterialModel extends Model
{
    protected $table         = 'materials';
    protected $primaryKey    = 'material_id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title', 'description', 'category_id', 'subject_id', 'year_level_id', 'folder_id',
        'uploaded_by', 'file_name', 'file_path', 'file_type', 'file_size',
    ];

    // created_at / updated_at are filled by the database itself.
    protected $useTimestamps = false;

    /** Sort keys offered on the student pages → ORDER BY. */
    public const SORTS = [
        'newest' => ['Newest',   'm.created_at DESC, m.material_id DESC'],
        'oldest' => ['Oldest',   'm.created_at ASC, m.material_id ASC'],
        'name'   => ['Name A–Z', 'm.title ASC'],
        'size'   => ['Largest',  'm.file_size DESC'],
    ];

    /** Base query: published materials with their category / year level / subject names. */
    private function published(): BaseBuilder
    {
        return $this->db->table('materials m')
            ->join('categories c', 'c.category_id = m.category_id')
            ->join('year_levels y', 'y.year_level_id = m.year_level_id', 'left')
            ->join('subjects s', 's.subject_id = m.subject_id', 'left')
            ->where(
                "COALESCE((SELECT sb.submission_status FROM submissions sb
                           WHERE sb.material_id = m.material_id
                           ORDER BY sb.submitted_at DESC, sb.submission_id DESC LIMIT 1), 'Published') = 'Published'",
                null,
                false
            );
    }

    /** Limits a query to one year level (0 = no year level) and/or subject (0 = no subject). */
    private function inGroup(BaseBuilder $b, ?int $yearId, ?int $subjectId): BaseBuilder
    {
        if ($yearId !== null) {
            $yearId === 0 ? $b->where('m.year_level_id IS NULL') : $b->where('m.year_level_id', $yearId);
        }
        if ($subjectId !== null) {
            $subjectId === 0 ? $b->where('m.subject_id IS NULL') : $b->where('m.subject_id', $subjectId);
        }

        return $b;
    }

    /**
     * Every category with its number of published files and year-level folders.
     *
     * @return list<array{id: int, name: string, description: ?string, files: int, folders: int}>
     */
    public function categorySummaries(): array
    {
        $counts = $this->published()
            ->select('m.category_id, COUNT(*) AS files, COUNT(DISTINCT COALESCE(m.year_level_id, 0)) AS folders')
            ->groupBy('m.category_id')
            ->get()->getResultArray();
        $counts = array_column($counts, null, 'category_id');

        $categories = $this->db->table('categories')
            ->orderBy('category_id')
            ->get()->getResultArray();

        return array_map(static fn (array $c) => [
            'id'          => (int) $c['category_id'],
            'name'        => $c['category_name'],
            'description' => $c['description'],
            'files'       => (int) ($counts[$c['category_id']]['files'] ?? 0),
            'folders'     => (int) ($counts[$c['category_id']]['folders'] ?? 0),
        ], $categories);
    }

    public function findCategory(int $id): ?array
    {
        return $this->db->table('categories')->where('category_id', $id)->get()->getRowArray();
    }

    public function findYearLevel(int $id): ?array
    {
        return $this->db->table('year_levels')->where('year_level_id', $id)->get()->getRowArray();
    }

    public function findSubject(int $id): ?array
    {
        return $this->db->table('subjects')->where('subject_id', $id)->get()->getRowArray();
    }

    /**
     * Year-level folders inside a category.
     *
     * @return list<array{id: int, name: ?string, files: int, folders: int}> id 0 / name null = "All years"
     */
    public function yearFolders(int $categoryId): array
    {
        $rows = $this->published()
            ->select('COALESCE(m.year_level_id, 0) AS id, y.year_level_name AS name,
                      COUNT(*) AS files, COUNT(DISTINCT COALESCE(m.subject_id, 0)) AS folders', false)
            ->where('m.category_id', $categoryId)
            ->groupBy('m.year_level_id, y.year_level_name')
            ->orderBy('m.year_level_id IS NULL', 'ASC', false)
            ->orderBy('m.year_level_id', 'ASC')
            ->get()->getResultArray();

        return array_map(static fn (array $r) => [
            'id' => (int) $r['id'], 'name' => $r['name'], 'files' => (int) $r['files'], 'folders' => (int) $r['folders'],
        ], $rows);
    }

    /**
     * Subject folders inside a category + year level.
     *
     * @return list<array{id: int, name: ?string, code: ?string, files: int}> id 0 / name null = "General"
     */
    public function subjectFolders(int $categoryId, int $yearId): array
    {
        $b = $this->published()
            ->select('COALESCE(m.subject_id, 0) AS id, s.subject_name AS name, s.subject_code AS code, COUNT(*) AS files', false)
            ->where('m.category_id', $categoryId);

        $rows = $this->inGroup($b, $yearId, null)
            ->groupBy('m.subject_id, s.subject_name, s.subject_code')
            ->orderBy('m.subject_id IS NULL', 'ASC', false)
            ->orderBy('s.subject_name', 'ASC')
            ->get()->getResultArray();

        return array_map(static fn (array $r) => [
            'id' => (int) $r['id'], 'name' => $r['name'], 'code' => $r['code'], 'files' => (int) $r['files'],
        ], $rows);
    }

    /**
     * Published files, either inside one folder or matching a search.
     *
     * @param array{category?: int, year?: int, subject?: int, q?: string, sort?: string} $opts
     */
    public function files(array $opts): array
    {
        $b = $this->published()->select(
            'm.material_id, m.title, m.description, m.file_name, m.file_path, m.file_type, m.file_size, m.created_at,
             m.category_id, c.category_name, m.year_level_id, y.year_level_name, m.subject_id, s.subject_name, s.subject_code'
        );

        if (isset($opts['category'])) {
            $b->where('m.category_id', $opts['category']);
        }
        $this->inGroup($b, $opts['year'] ?? null, $opts['subject'] ?? null);

        $q = trim($opts['q'] ?? '');
        if ($q !== '') {
            $b->groupStart()
                ->like('m.title', $q)
                ->orLike('m.file_name', $q)
                ->orLike('m.description', $q)
                ->orLike('c.category_name', $q)
                ->orLike('y.year_level_name', $q)
                ->orLike('s.subject_name', $q)
                ->orLike('s.subject_code', $q)
                ->groupEnd();
        }

        $sort = self::SORTS[$opts['sort'] ?? 'newest'] ?? self::SORTS['newest'];

        return $b->orderBy($sort[1], '', false)->get()->getResultArray();
    }

    /** One published file, or null if it does not exist or students may not see it. */
    public function findPublished(int $id): ?array
    {
        return $this->published()
            ->select('m.*')
            ->where('m.material_id', $id)
            ->get()->getRowArray();
    }
}
