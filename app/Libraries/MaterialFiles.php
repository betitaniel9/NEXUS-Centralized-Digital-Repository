<?php

namespace App\Libraries;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Mimes;

/**
 * Storing and serving the files behind `materials` rows.
 *
 * materials.file_path is either
 *   - a web link (http/https), e.g. a Google Drive file, or
 *   - a path inside writable/uploads, e.g. "uploads/materials/1731561234_ab12cd34.pdf".
 */
class MaterialFiles
{
    /** Extensions the Committee may upload. */
    public const ALLOWED_EXT = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg'];

    /** Largest upload in KB (php.ini allows 40M per request). */
    public const MAX_KB = 25600; // 25 MB

    /** Extensions that may be shown inside the page; everything else is always downloaded. */
    public const INLINE_TYPES = [
        'pdf'  => 'pdf',
        'png'  => 'image',
        'jpg'  => 'image',
        'jpeg' => 'image',
        'gif'  => 'image',
        'webp' => 'image',
    ];

    /**
     * Moves an uploaded file into writable/uploads/materials under a random name.
     *
     * @return array{file_name: string, file_path: string, file_type: string, file_size: int}
     */
    public static function store(UploadedFile $file): array
    {
        $ext  = strtolower($file->guessExtension() ?: $file->getClientExtension());
        $size = (int) $file->getSize();
        $name = $file->getRandomName();

        $file->move(WRITEPATH . 'uploads/materials', $name);

        return [
            'file_name' => mb_substr($file->getClientName(), 0, 255),
            'file_path' => 'uploads/materials/' . $name,
            'file_type' => $ext,
            'file_size' => $size,
        ];
    }

    /** Fields for a material that is only a web link. */
    public static function forLink(string $url, string $title): array
    {
        $base = basename((string) parse_url($url, PHP_URL_PATH));

        return [
            'file_name' => mb_substr($base !== '' && $base !== '/' ? $base : $title, 0, 255),
            'file_path' => mb_substr($url, 0, 500),
            'file_type' => 'link',
            'file_size' => 0,
        ];
    }

    /** Deletes a stored file (used when saving the database row fails). */
    public static function discard(array $stored): void
    {
        $path = self::localPath($stored);
        if ($path !== null) {
            @unlink($path);
        }
    }

    /** The file_path if it is a web link (http/https), else null. */
    public static function externalUrl(array $material): ?string
    {
        $path = trim((string) ($material['file_path'] ?? ''));

        return preg_match('#^https?://#i', $path) && filter_var($path, FILTER_VALIDATE_URL) ? $path : null;
    }

    /** Resolves file_path to a real file inside writable/uploads (never outside it), or null. */
    public static function localPath(array $material): ?string
    {
        $uploads = realpath(WRITEPATH . 'uploads');
        $stored  = str_replace('\\', '/', trim((string) ($material['file_path'] ?? '')));
        if ($uploads === false || $stored === '') {
            return null;
        }

        $candidates = [
            WRITEPATH . ltrim($stored, '/'),              // "uploads/abc.pdf"
            WRITEPATH . 'uploads/' . ltrim($stored, '/'), // "abc.pdf"
            $stored,                                      // absolute path
        ];

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if ($real && is_file($real) && str_starts_with($real, $uploads . DIRECTORY_SEPARATOR)) {
                return $real;
            }
        }

        return null;
    }

    /** Lower-case extension from file_name, falling back to file_type ('pdf' or 'application/pdf'). */
    public static function extension(array $material): string
    {
        $ext = strtolower(pathinfo((string) ($material['file_name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext !== '') {
            return $ext;
        }

        $type = strtolower(trim((string) ($material['file_type'] ?? '')));
        if (str_contains($type, '/')) {
            return (string) (Mimes::guessExtensionFromType($type) ?? '');
        }

        return (string) preg_replace('/[^a-z0-9]/', '', $type);
    }

    /** 'pdf' | 'image' | 'link' | 'none': how the file can be previewed. */
    public static function previewKind(array $material): string
    {
        if (self::externalUrl($material) !== null) {
            return 'link';
        }

        return self::INLINE_TYPES[self::extension($material)] ?? 'none';
    }

    /**
     * Sends the file (or redirects to its link).
     * $inline is ignored for types that may not be shown in the page.
     *
     * @return DownloadResponse|ResponseInterface
     */
    public static function respond(ResponseInterface $response, array $material, bool $inline)
    {
        $url = self::externalUrl($material);
        if ($url !== null) {
            return redirect()->to($url);
        }

        $path = self::localPath($material);
        if ($path === null) {
            throw PageNotFoundException::forPageNotFound('The file could not be found on the server.');
        }

        $name = ($material['file_name'] ?? '') ?: basename($path);
        if (pathinfo($name, PATHINFO_EXTENSION) === '' && pathinfo($path, PATHINFO_EXTENSION) !== '') {
            $name .= '.' . pathinfo($path, PATHINFO_EXTENSION);
        }

        $download = $response->download($path, null, true)->setFileName($name); // true: real Content-Type (needed for the inline PDF preview)
        if ($inline && isset(self::INLINE_TYPES[self::extension($material)])) {
            $download->inline();
        }

        return $download->setHeader('X-Content-Type-Options', 'nosniff');
    }
}
