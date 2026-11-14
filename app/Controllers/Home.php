<?php

namespace App\Controllers;

use App\Models\MaterialModel;

class Home extends BaseController
{
    public function index(): string
    {
        // Landing-page folders open the matching library section (by category name), else the library home.
        $categoryUrls = [];
        try {
            foreach ((new MaterialModel())->categorySummaries() as $c) {
                $categoryUrls[$c['name']] = base_url('student/folder/' . $c['id']);
            }
        } catch (\Throwable $e) {
            // Database not reachable: folders just link to the library home.
        }

        $formUrl = config('Nexus')->notesFormUrl;

        return view('landing/index', [
            'libraryUrl'   => base_url('student'),
            'categoryUrls' => $categoryUrls,
            // "Share your notes": the Google Form if set, otherwise the library (which explains how to share).
            'shareUrl'     => $formUrl !== '' ? $formUrl : base_url('student'),
            'shareIsForm'  => $formUrl !== '',
        ]);
    }
}
