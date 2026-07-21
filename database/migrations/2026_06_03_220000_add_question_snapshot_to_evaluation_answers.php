<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_answers', function (Blueprint $table) {
            $table->json('question_snapshot')->nullable()->after('question_id');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_answers', function (Blueprint $table) {
            $table->dropColumn('question_snapshot');
        });
    }
};
