<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Creates the fixed library structure from Config\Nexus:
 *   - the 8 sections (categories) shown on the landing page
 *   - year levels 1st–4th year
 * Subjects are added by the Technical Committee on "My sections".
 *
 * Safe to run more than once: rows that already exist (same name) are left alone.
 *   php spark db:seed LibraryStructureSeeder
 */
class LibraryStructureSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('Nexus');

        foreach ($config->sections as $name => $description) {
            $this->addIfMissing('categories', 'category_name', $name, ['description' => $description]);
        }

        foreach ($config->yearLevels as $name) {
            $this->addIfMissing('year_levels', 'year_level_name', $name);
        }
    }

    private function addIfMissing(string $table, string $nameColumn, string $name, array $extra = []): void
    {
        // The column collation ignores case, so "1st Year" matches "1st year"
        $existing = $this->db->table($table)->where($nameColumn, $name)->get()->getRowArray();

        if ($existing !== null) {
            if ($existing[$nameColumn] !== $name) {
                // Same name, different capitalisation: use the standard spelling
                $this->db->table($table)->where($nameColumn, $existing[$nameColumn])->update([$nameColumn => $name]);
                $this->say("  renamed {$table}: {$existing[$nameColumn]} → {$name}");
            } else {
                $this->say("  exists  {$table}: {$name}");
            }

            return;
        }

        $this->db->table($table)->insert([$nameColumn => $name] + $extra);
        $this->say("  added   {$table}: {$name}");
    }

    private function say(string $line): void
    {
        if (is_cli()) {
            echo $line . PHP_EOL;
        }
    }
}
