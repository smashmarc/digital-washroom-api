<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('room_id')
        ->constrained('rooms')
        ->cascadeOnDelete();
    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();
    $table->text('note')->nullable(); // just make it nullable directly
    $table->tinyInteger('note_code')->default(0)
        ->comment('0=not cleaned, 1=partially cleaned, 2=fully cleaned');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
