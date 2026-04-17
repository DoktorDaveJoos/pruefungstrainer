<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('questions:balance');
        Artisan::call('questions:seed');
        Artisan::call('exam:flag-free-tier');
    }
}
