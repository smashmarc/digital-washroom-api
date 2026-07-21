<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add user_id and evaluation_template_id directly to evaluations
        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('evaluation_template_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('evaluation_templates')
                  ->nullOnDelete();
        });

        // Drop FK on user_assignment_id, then the column, then the table
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['user_assignment_id']);
            $table->dropColumn('user_assignment_id');
        });

        Schema::dropIfExists('user_assignments');
    }

    public function down(): void
    {
        Schema::create('user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluation_template_id')->constrained('evaluation_templates')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreignId('user_assignment_id')
                  ->nullable()
                  ->constrained('user_assignments')
                  ->cascadeOnDelete();
            $table->dropForeign(['evaluation_template_id']);
            $table->dropColumn('evaluation_template_id');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
