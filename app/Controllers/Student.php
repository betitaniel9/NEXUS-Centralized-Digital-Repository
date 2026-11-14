<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Student library: browse published materials by Category → Year level → Subject,
 * search them, preview them and download them.
 *
 * URL pieces: year 'all' = materials with no year level, subject 'general' = no subject.
 */
class Student extends BaseController
{
    protected $helpers = ['council', 'student'];

    /** Extensions that may be shown inside the page (everything else is downloaded). */
    private const INLINE_TYPES = [
        'pdf'  => 'pdf',
        'png'  => 'image',
        'jpg'  => 'image',
        'jpeg' => 'image',
        'gif'  => 'image',
        'webp' => 'image',
    ];

    private MaterialModel $materials;

    public function __construct()
    {
        $this->materials = new MaterialModel();
    }

    // ---------------------------------------------------------------- pages

    /** Library home: every category as a folder. */
    public function index(): string
    {
        $categories = $this->materials->categorySummaries();

        return $this->page('student/folders', [
            'title'       => 'All folders',
            'subtitle'    => 'Pick a section to start browsing.',
            'crumbs'      => [],
            'backUrl'     => null,
            'activeCat'   => null,
            'folders'     => array_map(fn (array $c) => [
                'url'   => base_url('student/folder/' . $c['id']),
                'name'  => $c['name'],
                'meta'  => $this->countLabel($c['files'], 'file') . ' · ' . $this->countLabel($c['folders'], 'folder'),
                'color' => $this->color($c['id']),
            ], $categories),
            'emptyText'   => 'There are no sections in the library yet.',
        ], $categories);
    }

    /** Inside a category: one folder per year level. */
    public function category(int $categoryId): string
    {
        $category   = $this->categoryOr404($categoryId);
        $categories = $this->materials->categorySummaries();
        $base       = 'student/folder/' . $categoryId . '/';

        return $this->page('student/folders', [
            'title'     => $category['category_name'],
            'subtitle'  => $category['description'] ?: 'Pick a year level.',
            'crumbs'    => [[$category['category_name'], null]],
            'backUrl'   => base_url('student'),
            'activeCat' => $categoryId,
            'folders'   => array_map(fn (array $y) => [
                'url'   => base_url($base . ($y['id'] ?: 'all')),
                'name'  => $y['name'] ?? 'All years',
                'meta'  => $this->countLabel($y['files'], 'file') . ' · ' . $this->countLabel($y['folders'], 'folder'),
                'color' => $this->color($categoryId),
            ], $this->materials->yearFolders($categoryId)),
            'emptyText' => 'Nothing has been published in this section yet.',
        ], $categories);
    }

    /** Inside a year level: one folder per subject. */
    public function yearLevel(int $categoryId, string $year): string
    {
        $category   = $this->categoryOr404($categoryId);
        [$yearId, $yearName] = $this->yearOr404($year);
        $categories = $this->materials->categorySummaries();
        $base       = 'student/folder/' . $categoryId . '/' . $year . '/';

        return $this->page('student/folders', [
            'title'     => $yearName,
            'subtitle'  => $category['category_name'],
            'crumbs'    => [
                [$category['category_name'], base_url('student/folder/' . $categoryId)],
                [$yearName, null],
            ],
            'backUrl'   => base_url('student/folder/' . $categoryId),
            'activeCat' => $categoryId,
            'folders'   => array_map(fn (array $s) => [
                'url'   => base_url($base . ($s['id'] ?: 'general')),
                'name'  => $s['name'] ?? 'General',
                'meta'  => ($s['code'] ? $s['code'] . ' · ' : '') . $this->countLabel($s['files'], 'file'),
                'color' => $this->color($categoryId),
            ], $this->materials->subjectFolders($categoryId, $yearId)),
            'emptyText' => 'Nothing has been published for this year level yet.',
        ], $categories);
    }

    /** Inside a subject: the files. */
    public function subject(int $categoryId, string $year, string $subject): string
    {
        $category = $this->categoryOr404($categoryId);
        [$yearId, $yearName]       = $this->yearOr404($year);
        [$subjectId, $subjectName] = $this->subjectOr404($subject);
        $sort       = $this->sortKey();
        $categories = $this->materials->categorySummaries();

        $files = $this->materials->files([
            'category' => $categoryId, 'year' => $yearId, 'subject' => $subjectId, 'sort' => $sort,
        ]);

        return $this->page('student/files', [
            'title'     => $subjectName,
            'subtitle'  => $category['category_name'] . ' · ' . $yearName,
            'crumbs'    => [
                [$category['category_name'], base_url('student/folder/' . $categoryId)],
                [$yearName, base_url('student/folder/' . $categoryId . '/' . $year)],
                [$subjectName, null],
            ],
            'backUrl'   => base_url('student/folder/' . $categoryId . '/' . $year),
            'activeCat' => $categoryId,
            'files'     => array_map(fn (array $f) => $this->presentFile($f), $files),
            'sort'      => $sort,
            'q'         => '',
            'emptyText' => 'No files here yet.',
        ], $categories);
    }

    public function search(): string
    {
        $q          = trim((string) $this->request->getGet('q'));
        $sort       = $this->sortKey();
        $categories = $this->materials->categorySummaries();
        $files      = $q === '' ? [] : $this->materials->files(['q' => mb_substr($q, 0, 100), 'sort' => $sort]);

        return $this->page('student/files', [
            'title'     => 'Search results',
            'subtitle'  => $q === ''
                ? 'Type something in the search bar to find notes and files.'
                : $this->countLabel(count($files), 'file') . ' match "' . $q . '"',
            'crumbs'    => [['Search', null]],
            'backUrl'   => null,
            'activeCat' => null,
            'files'     => array_map(fn (array $f) => $this->presentFile($f), $files),
            'sort'      => $sort,
            'q'         => $q,
            'emptyText' => $q === '' ? '' : 'No files match your search. Try a shorter word or a subject name.',
        ], $categories);
    }

    // ---------------------------------------------------------------- files

    /** Shows a PDF / image inside the preview pop-up. Other types are downloaded instead. */
    public function view(int $id)
    {
        $file = $this->fileOr404($id);

        if ($this->externalUrl($file) !== null) {
            return redirect()->to($this->externalUrl($file));
        }

        // Only PDFs and images are shown in the page; anything else (e.g. HTML) is always downloaded.
        $inline = isset(self::INLINE_TYPES[$this->extension($file)]);

        return $this->sendFile($file, $this->localPathOr404($file), $inline);
    }

    public function download(int $id)
    {
        $file = $this->fileOr404($id);

        if ($this->externalUrl($file) !== null) {
            return redirect()->to($this->externalUrl($file));
        }

        return $this->sendFile($file, $this->localPathOr404($file), false);
    }

    // ---------------------------------------------------------------- helpers

    private function page(string $view, array $data, array $categories): string
    {
        $roles = session()->get('roles') ?? [];

        return view($view, $data + [
            'categories'   => array_map(fn (array $c) => $c + ['color' => $this->color($c['id'])], $categories),
            'searchQuery'  => $data['q'] ?? '',
            'notesFormUrl' => config('Nexus')->notesFormUrl,
            'isLoggedIn'   => (bool) session()->get('isLoggedIn'),
            'firstName'    => (string) session()->get('first_name'),
            'lastName'     => (string) session()->get('last_name'),
            'email'        => (string) session()->get('email'),
            'isCouncil'    => UserModel::isCouncil($roles),
            'sorts'        => array_map(static fn (array $s) => $s[0], MaterialModel::SORTS),
        ]);
    }

    /** Row from MaterialModel::files() → what the file list needs. */
    private function presentFile(array $f): array
    {
        $year    = $f['year_level_name'] ?? 'All years';
        $subject = $f['subject_name'] ?? 'General';
        $ext     = $this->extension($f);
        $isLink  = $this->externalUrl($f) !== null;

        return [
            'id'       => (int) $f['material_id'],
            'title'    => $f['title'],
            'fileName' => $f['file_name'],
            'path'     => $f['category_name'] . ' / ' . $year . ' / ' . $subject,
            'badge'    => $isLink ? 'LINK' : (strtoupper($ext) ?: 'FILE'),
            'typeName' => $isLink ? 'Link' : $this->typeName($ext),
            'size'     => $isLink ? '—' : format_bytes((int) $f['file_size']),
            'added'    => $f['created_at'] ? date('M j, Y', strtotime($f['created_at'])) : '—',
            'color'    => $this->color((int) $f['category_id']),
            'preview'  => $isLink ? 'link' : (self::INLINE_TYPES[$ext] ?? 'none'),
            'viewUrl'  => base_url('student/files/' . (int) $f['material_id'] . '/view'),
            'downloadUrl' => base_url('student/files/' . (int) $f['material_id'] . '/download'),
        ];
    }

    private function categoryOr404(int $id): array
    {
        return $this->materials->findCategory($id) ?? throw PageNotFoundException::forPageNotFound('That section does not exist.');
    }

    /** @return array{0: int, 1: string} [year id (0 = all years), label] */
    private function yearOr404(string $year): array
    {
        if ($year === 'all') {
            return [0, 'All years'];
        }
        $row = ctype_digit($year) ? $this->materials->findYearLevel((int) $year) : null;
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('That year level does not exist.');
        }

        return [(int) $row['year_level_id'], $row['year_level_name']];
    }

    /** @return array{0: int, 1: string} [subject id (0 = general), label] */
    private function subjectOr404(string $subject): array
    {
        if ($subject === 'general') {
            return [0, 'General'];
        }
        $row = ctype_digit($subject) ? $this->materials->findSubject((int) $subject) : null;
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('That subject does not exist.');
        }

        return [(int) $row['subject_id'], $row['subject_name']];
    }

    private function fileOr404(int $id): array
    {
        return $this->materials->findPublished($id) ?? throw PageNotFoundException::forPageNotFound('That file is not available.');
    }

    /** The material's file_path if it is a web link (http/https), else null. */
    private function externalUrl(array $file): ?string
    {
        $path = trim((string) $file['file_path']);

        return preg_match('#^https?://#i', $path) && filter_var($path, FILTER_VALIDATE_URL) ? $path : null;
    }

    /** Resolves file_path to a real file inside writable/uploads (never outside it). */
    private function localPathOr404(array $file): string
    {
        $uploads = realpath(WRITEPATH . 'uploads');
        $stored  = str_replace('\\', '/', trim((string) $file['file_path']));

        $candidates = [
            WRITEPATH . ltrim($stored, '/'),            // e.g. "uploads/abc.pdf"
            WRITEPATH . 'uploads/' . ltrim($stored, '/'), // e.g. "abc.pdf"
            $stored,                                      // absolute path
        ];

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if ($uploads && $real && is_file($real) && str_starts_with($real, $uploads . DIRECTORY_SEPARATOR)) {
                return $real;
            }
        }

        throw PageNotFoundException::forPageNotFound('The file could not be found on the server.');
    }

    private function sendFile(array $file, string $path, bool $inline): ResponseInterface
    {
        $name = $file['file_name'] ?: basename($path);
        if (pathinfo($name, PATHINFO_EXTENSION) === '' && pathinfo($path, PATHINFO_EXTENSION) !== '') {
            $name .= '.' . pathinfo($path, PATHINFO_EXTENSION);
        }

        $response = $this->response->download($path, null)->setFileName($name);
        if ($inline) {
            $response->inline();
        }

        return $response->setHeader('X-Content-Type-Options', 'nosniff');
    }

    /** Lower-case extension from file_name, falling back to file_type ('pdf' or 'application/pdf'). */
    private function extension(array $file): string
    {
        $ext = strtolower(pathinfo((string) $file['file_name'], PATHINFO_EXTENSION));
        if ($ext !== '') {
            return $ext;
        }

        $type = strtolower(trim((string) $file['file_type']));
        if (str_contains($type, '/')) {
            return (string) (\Config\Mimes::guessExtensionFromType($type) ?? '');
        }

        return preg_replace('/[^a-z0-9]/', '', $type);
    }

    private function typeName(string $ext): string
    {
        return match ($ext) {
            'pdf'                         => 'PDF document',
            'doc', 'docx'                 => 'Word document',
            'ppt', 'pptx'                 => 'Slides',
            'xls', 'xlsx', 'csv'          => 'Spreadsheet',
            'png', 'jpg', 'jpeg', 'gif', 'webp' => 'Image',
            'zip', 'rar', '7z'            => 'Archive',
            'txt', 'md'                   => 'Text',
            ''                            => 'File',
            default                       => strtoupper($ext) . ' file',
        };
    }

    private function sortKey(): string
    {
        $sort = (string) $this->request->getGet('sort');

        return isset(MaterialModel::SORTS[$sort]) ? $sort : 'newest';
    }

    private function countLabel(int $n, string $word): string
    {
        return number_format($n) . ' ' . $word . ($n === 1 ? '' : 's');
    }

    /** Folder colour for a category, cycling through the palette in student.css. */
    private function color(int $categoryId): string
    {
        return 'c' . ((max($categoryId, 1) - 1) % 8 + 1);
    }
}
