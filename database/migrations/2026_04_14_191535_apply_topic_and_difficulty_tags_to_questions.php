<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('data/question-tags.json');

        if (! is_file($path)) {
            return;
        }

        $tags = json_decode(file_get_contents($path), true);

        if (! is_array($tags)) {
            return;
        }

        foreach ($tags as $tag) {
            DB::table('questions')
                ->where('id', $tag['id'])
                ->update([
                    'topic' => $tag['topic'],
                    'difficulty' => $tag['difficulty'],
                ]);
        }
    }

    public function down(): void
    {
        DB::table('questions')->update([
            'topic' => null,
            'difficulty' => null,
        ]);
    }
};
