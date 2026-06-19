<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete()->after('id');
            $table->dropUnique(['name']);
            $table->unique(['location_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique(['location_id', 'name']);
            $table->dropConstrainedForeignId('location_id');
            $table->unique(['name']);
        });
    }
};
