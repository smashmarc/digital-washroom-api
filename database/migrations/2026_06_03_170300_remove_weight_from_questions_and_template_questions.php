<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('template_questions', function (Blueprint $table) {
            $table->dropColumn('weight_override');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedTinyInteger('weight')->default(1)->after('type');
        });

        Schema::table('template_questions', function (Blueprint $table) {
            $table->unsignedTinyInteger('weight_override')->nullable()->after('order');
        });
    }
};
