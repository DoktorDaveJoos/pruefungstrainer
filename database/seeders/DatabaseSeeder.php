<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ModuleSeeder::class,
            SampleQuestionsSeeder::class,
            BsiStandard2001QuestionsSeeder::class,
            BsiStandard2002QuestionsSeeder::class,
            BsiStandard2003QuestionsSeeder::class,
            ItGrundschutzKompendiumQuestionsSeeder::class,
        ]);
    }
}
