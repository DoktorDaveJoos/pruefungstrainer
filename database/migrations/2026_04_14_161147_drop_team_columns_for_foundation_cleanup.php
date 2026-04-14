<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_team_id']);
            $table->dropColumn('current_team_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('is_personal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_team_id')->nullable()->constrained('teams')->nullOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_personal')->default(false);
        });
    }
};
