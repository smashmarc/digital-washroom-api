<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_evaluation_template', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->cascadeOnDelete();
            $table->foreignId('evaluation_template_id')
                  ->constrained('evaluation_templates')
                  ->cascadeOnDelete();
            $table->primary(['department_id', 'evaluation_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_evaluation_template');
    }
};
