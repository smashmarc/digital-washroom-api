<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('frequency', ['hourly', 'every_6h', 'every_12h', 'daily', 'weekly', 'monthly']);
            $table->time('run_at')->nullable();          // time of day for daily/weekly/monthly
            $table->tinyInteger('run_day')->nullable();  // 0-6 for weekly (0=Sun), 1-31 for monthly
            $table->enum('type', ['full', 'incremental', 'schema_only'])->default('full');
            $table->integer('retention_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->enum('last_status', ['success', 'failed', 'running'])->nullable();
            $table->timestamps();
        });

        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->enum('type', ['full', 'incremental', 'schema_only'])->default('full');
            $table->enum('trigger', ['manual', 'scheduled'])->default('manual');
            $table->enum('status', ['running', 'success', 'failed'])->default('running');
            $table->string('filename')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->text('output')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('backup_schedules');
    }
};