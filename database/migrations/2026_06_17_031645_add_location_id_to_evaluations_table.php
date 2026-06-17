<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreignId('location_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('locations')
                  ->nullOnDelete();
        });
        //uncomment if needed
        // Back-fill existing evaluations from their user's location
        // DB::statement('
        //     UPDATE evaluations e
        //     JOIN users u ON u.id = e.user_id
        //     SET e.location_id = u.location_id
        //     WHERE e.location_id IS NULL
        // ');
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};
