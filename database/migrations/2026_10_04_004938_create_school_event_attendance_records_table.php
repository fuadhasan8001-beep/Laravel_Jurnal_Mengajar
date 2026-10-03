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
        Schema::create('school_event_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('participant_type', 20);
            $table->string('status_once', 20)->nullable();
            $table->string('status_morning', 20)->nullable();
            $table->string('status_evening', 20)->nullable();
            foreach (['once', 'morning', 'evening'] as $session) {
                $table->timestamp($session.'_at')->nullable();
                $table->decimal($session.'_latitude', 10, 7)->nullable();
                $table->decimal($session.'_longitude', 10, 7)->nullable();
                $table->decimal($session.'_accuracy', 8, 2)->nullable();
                $table->decimal($session.'_distance', 8, 2)->nullable();
            }
            $table->text('activity_note')->nullable();
            $table->unique(['school_event_id', 'user_id']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_event_attendance_records');
    }
};
