<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unique(['evaluation_id', 'department_id']);
        });

        // Migrate existing JSON snapshot rows
        $evaluations = DB::table('evaluations')
            ->whereNotNull('department_snapshot')
            ->get(['id', 'department_snapshot']);

        foreach ($evaluations as $eval) {
            $depts = json_decode($eval->department_snapshot, true) ?? [];
            foreach ($depts as $dept) {
                if (empty($dept['id']) || empty($dept['name'])) continue;
                DB::table('evaluation_departments')->insertOrIgnore([
                    'evaluation_id' => $eval->id,
                    'department_id' => $dept['id'],
                    'name'          => $dept['name'],
                ]);
            }
        }

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('department_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->json('department_snapshot')->nullable()->after('user_id');
        });

        Schema::dropIfExists('evaluation_departments');
    }
};
