<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_category_id')
                  ->constrained('question_categories')
                  ->cascadeOnDelete();
            $table->text('text');
            $table->string('type')->default('pass_fail'); // pass_fail, text, scale
            $table->unsignedTinyInteger('weight')->default(1);
            $table->boolean('is_fatal')->default(false); // fatal fail auto-fails the evaluation
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
