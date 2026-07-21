<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename tables
        Schema::rename('question_categories', 'criteria_categories');
        Schema::rename('questions', 'criteria');
        Schema::rename('template_questions', 'template_criteria');

        // Rename columns in criteria (was questions)
        Schema::table('criteria', function (Blueprint $table) {
            $table->renameColumn('question_category_id', 'criteria_category_id');
        });

        // Rename columns in template_criteria (was template_questions)
        Schema::table('template_criteria', function (Blueprint $table) {
            $table->renameColumn('question_id', 'criteria_id');
        });

        // Rename columns in evaluation_answers
        Schema::table('evaluation_answers', function (Blueprint $table) {
            $table->renameColumn('question_id', 'criteria_id');
            $table->renameColumn('question_snapshot', 'criteria_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_answers', function (Blueprint $table) {
            $table->renameColumn('criteria_id', 'question_id');
            $table->renameColumn('criteria_snapshot', 'question_snapshot');
        });

        Schema::table('template_criteria', function (Blueprint $table) {
            $table->renameColumn('criteria_id', 'question_id');
        });

        Schema::table('criteria', function (Blueprint $table) {
            $table->renameColumn('criteria_category_id', 'question_category_id');
        });

        Schema::rename('template_criteria', 'template_questions');
        Schema::rename('criteria', 'questions');
        Schema::rename('criteria_categories', 'question_categories');
    }
};
