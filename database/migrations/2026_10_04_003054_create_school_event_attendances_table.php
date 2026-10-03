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
        Schema::create('school_event_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained()->cascadeOnDelete();
            foreach (['morning', 'evening'] as $period) {
                $table->timestamp($period.'_at')->nullable();
                $table->decimal($period.'_latitude', 10, 7)->nullable();
                $table->decimal($period.'_longitude', 10, 7)->nullable();
                $table->decimal($period.'_accuracy', 8, 2)->nullable();
                $table->decimal($period.'_distance', 8, 2)->nullable();
            }
            $table->unique(['school_event_id', 'guru_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_event_attendances');
    }
};
