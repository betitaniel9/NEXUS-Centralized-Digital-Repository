<?php

namespace App\Controllers;

/**
 * Shared base for the Student Council (SC) pages: Dashboard, Submissions, Approval, Repository.
 * Routes using these controllers are protected by the 'auth:council' filter.
 */
abstract class CouncilController extends BaseController
{
    protected $helpers = ['council'];

    /** Filter values from the query string (?q=&repository=&year_level=&file_type=&sort=). */
    protected function currentFilters(): array
    {
        return [
            'q'          => trim((string) $this->request->getGet('q')),
            'repository' => (string) $this->request->getGet('repository'),
            'year_level' => (string) $this->request->getGet('year_level'),
            'file_type'  => (string) $this->request->getGet('file_type'),
            'status'     => (string) $this->request->getGet('status'),
            'sort'       => (string) ($this->request->getGet('sort') ?: 'oldest'),
        ];
    }

    /**
     * Renders an SC page inside layouts/council_layout.
     *
     * @param string $active    Sidebar item to highlight: dashboard | submissions | approval | repository
     * @param string $pageTitle Big title in the banner
     * @param string $pageDesc  Line under the title
     */
    protected function render(string $view, string $active, string $pageTitle, string $pageDesc, array $data = []): string
    {
        return view($view, $data + [
            'active'    => $active,
            'pageTitle' => $pageTitle,
            'pageDesc'  => $pageDesc,
            'roleName'  => session()->get('council_role') ?? 'Student Council',
            'firstName' => session()->get('first_name'),
            // Committee sidebar footer, e.g. '1st semester · AY 2026–2027'.
            // TODO(db): the schema has no semester / academic-year table yet.
            'workspaceTerm' => null,
        ]);
    }
}
