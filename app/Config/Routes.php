<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Authentication
$routes->get('login', 'Auth::login');
$routes->post('auth/login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('auth/register', 'Auth::attemptRegister');
$routes->post('logout', 'Auth::logout');

// Student side
// Student library: public, no login needed
$routes->group('student', static function (RouteCollection $routes) {
    $routes->get('/', 'Student::index');
    $routes->get('search', 'Student::search');
    $routes->get('folder/(:num)', 'Student::category/$1');
    $routes->get('folder/(:num)/(:segment)', 'Student::yearLevel/$1/$2');
    $routes->get('folder/(:num)/(:segment)/(:segment)', 'Student::subject/$1/$2/$3');
    $routes->get('files/(:num)/view', 'Student::view/$1');
    $routes->get('files/(:num)/download', 'Student::download/$1');
});

// Student Council (SC) side: Admin, Repository Manager, President, VP, Secretary, Technical Committee
$routes->group('', ['filter' => 'auth:council'], static function (RouteCollection $routes) {
    $routes->get('loading', 'Dashboard::loading');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('submissions', 'Submissions::index');
    $routes->get('approval', 'Approvals::index');
    $routes->post('approval/(:num)/approve', 'Approvals::approve/$1');
    $routes->post('approval/(:num)/return', 'Approvals::returnFile/$1');
    $routes->get('council/files/(:num)', 'Approvals::file/$1'); // any material, published or not
    $routes->get('repository', 'Materials::index');
});

// Technical Committee workspace (its Dashboard is /dashboard above, shown by role)
$routes->group('', ['filter' => 'auth:committee'], static function (RouteCollection $routes) {
    $routes->get('review', 'Committee::review');
    $routes->post('review/(:num)/accept', 'Committee::accept/$1');
    $routes->post('review/(:num)/return', 'Committee::returnFile/$1');

    $routes->get('my-sections', 'Committee::sections');
    $routes->post('my-sections/upload', 'Committee::upload');
    $routes->post('my-sections/subjects', 'Committee::addSubject'); // sections + year levels are fixed (Config\Nexus)
    $routes->post('my-sections/(:num)/update', 'Committee::update/$1');
    $routes->post('my-sections/(:num)/delete', 'Committee::delete/$1');

    $routes->get('semester-review', 'Committee::semester');
    $routes->post('semester-review/link/(:num)', 'Committee::fixLink/$1');
    $routes->post('semester-review/remove/(:num)', 'Committee::removeMaterial/$1');
    $routes->post('semester-review/section/(:num)/reviewed', 'Committee::markReviewed/$1');
});
