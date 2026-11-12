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
$routes->get('student', 'Student::index', ['filter' => 'auth']);

// Student Council (SC) side: Admin, Repository Manager, President, VP, Secretary, Technical Committee
$routes->group('', ['filter' => 'auth:council'], static function (RouteCollection $routes) {
    $routes->get('loading', 'Dashboard::loading');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('submissions', 'Submissions::index');
    $routes->get('approval', 'Approvals::index');
    $routes->get('repository', 'Materials::index');
});

// Technical Committee workspace (its Dashboard is /dashboard above, shown by role)
$routes->group('', ['filter' => 'auth:committee'], static function (RouteCollection $routes) {
    $routes->get('review', 'Committee::review');
    $routes->post('review/(:num)/accept', 'Committee::accept/$1');
    $routes->post('review/(:num)/return', 'Committee::returnFile/$1');

    $routes->get('my-sections', 'Committee::sections');
    $routes->post('my-sections/upload', 'Committee::upload');
    $routes->post('my-sections/(:num)/update', 'Committee::update/$1');
    $routes->post('my-sections/(:num)/delete', 'Committee::delete/$1');

    $routes->get('semester-review', 'Committee::semester');
    $routes->post('semester-review/link/(:num)', 'Committee::fixLink/$1');
    $routes->post('semester-review/remove/(:num)', 'Committee::removeMaterial/$1');
    $routes->post('semester-review/section/(:num)/reviewed', 'Committee::markReviewed/$1');
});
