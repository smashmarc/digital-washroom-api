<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
            $table->string('room_name')->nullable()->after('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('room_name');
            $table->unsignedBigInteger('room_id')->nullable()->after('unit_id');
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }
};
