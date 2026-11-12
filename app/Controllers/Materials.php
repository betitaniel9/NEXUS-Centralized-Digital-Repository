<?php

namespace App\Controllers;

/** The "Repository" page of the SC side. */
class Materials extends CouncilController
{
    public function index(): string
    {
        // TODO(db): fill from materials + categories + year_levels + latest submission status.
        return $this->render('materials/index', 'repository', 'Repository', 'Upload and organize materials in the Next Cloud hub', [
            'stats' => [
                'total'        => null, // count of materials
                'repositories' => null, // count of categories that have materials
                'published'    => null, // materials whose submission is 'Published'
                'storage'      => null, // SUM(materials.file_size) in bytes
                'old'          => null, // materials uploaded more than a year ago
            ],

            // [['id' => int, 'fileName' => 'C++ Array guide.pdf', 'repository' => '...', 'yearLevel' => '1st Year',
            //   'fileType' => 'PDF', 'status' => 'Published'], ...]
            'materials' => [],

            'filters'      => $this->currentFilters(),
            'repositories' => [],
            'yearLevels'   => [],
            'fileTypes'    => [],
        ]);
    }
}
