<?php

namespace App\Controllers;

class Student extends BaseController
{
    public function index(): string
    {
        return view('student/student', [
            'firstName' => session()->get('first_name'),
            'lastName'  => session()->get('last_name'),
            'email'     => session()->get('email'),
            'roles'     => session()->get('roles') ?? [],
        ]);
    }
}
