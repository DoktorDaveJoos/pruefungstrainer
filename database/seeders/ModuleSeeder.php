<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        Module::firstOrCreate(
            ['slug' => 'm2-bsi-grundschutz'],
            [
                'name' => 'M2 - BSI Grundschutz',
                'description' => 'IT-Grundschutz-Praktiker Prüfungsvorbereitung nach BSI-Standards 200-1, 200-2, 200-3 und dem IT-Grundschutz-Kompendium.',
            ],
        );
    }
}
