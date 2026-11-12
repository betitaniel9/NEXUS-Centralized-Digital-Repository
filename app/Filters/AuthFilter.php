<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 'auth'         → must be signed in.
 * 'auth:council' → must be signed in AND have a Student Council role.
 * 'auth:committee' → must be signed in AND have the Technical Committee role.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Please sign in to continue.');
        }

        $roles = $session->get('roles') ?? [];

        if (in_array('council', (array) $arguments, true) && ! UserModel::isCouncil($roles)) {
            return redirect()->to(base_url('student'))
                ->with('error', 'That page is only for Student Council members.');
        }

        if (in_array('committee', (array) $arguments, true) && ! in_array('Technical Committee', $roles, true)) {
            return redirect()->to(base_url(UserModel::homeFor($roles)))
                ->with('error', 'That page is only for the Technical Committee.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
