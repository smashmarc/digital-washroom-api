<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                  ->constrained('evaluations')
                  ->cascadeOnDelete();
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->cascadeOnDelete();
            $table->enum('value', ['pass', 'fail', 'na']); // na = excluded from scoring
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['evaluation_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_answers');
    }
};
