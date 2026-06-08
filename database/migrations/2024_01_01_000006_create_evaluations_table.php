<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_assignment_id')
                  ->constrained('user_assignments')
                  ->cascadeOnDelete();
            $table->foreignId('evaluator_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();         // calculated % score (NA-excluded)
            $table->string('result')->nullable();               // passed | failed | inconclusive
            $table->string('status')->default('draft');         // draft | submitted
            $table->boolean('fatal_failed')->default(false);    // true if a fatal question failed
            $table->text('overall_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
