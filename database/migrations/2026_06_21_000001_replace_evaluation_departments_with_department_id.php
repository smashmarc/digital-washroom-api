<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('unit_id')->constrained('departments')->nullOnDelete();
        });

        Schema::dropIfExists('evaluation_departments');
    }

    public function down(): void
    {
        Schema::create('evaluation_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name');
            $table->unique(['evaluation_id', 'department_id']);
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
