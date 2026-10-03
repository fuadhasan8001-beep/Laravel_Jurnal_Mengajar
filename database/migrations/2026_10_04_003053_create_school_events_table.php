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
        Schema::create('school_events', function (Blueprint $table) {
            $table->id();
            $table->date('event_date')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('attendance_enabled')->default(false);
            $table->string('attendance_mode')->default('morning_evening');
            $table->time('morning_start')->nullable();
            $table->time('morning_deadline')->nullable();
            $table->time('evening_start')->nullable();
            $table->time('evening_deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_events');
    }
};
