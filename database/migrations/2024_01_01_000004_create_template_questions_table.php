<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_template_id')
                  ->constrained('evaluation_templates')
                  ->cascadeOnDelete();
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->unsignedTinyInteger('weight_override')->nullable(); // overrides question weight if set
            $table->timestamps();

            $table->unique(['evaluation_template_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_questions');
    }
};
