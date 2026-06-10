<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('is_fatal');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('fatal_failed');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('is_fatal')->default(false)->after('text');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->boolean('fatal_failed')->default(false)->after('result');
        });
    }
};
