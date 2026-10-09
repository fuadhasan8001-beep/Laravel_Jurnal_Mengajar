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
        Schema::table('jurnals', function (Blueprint $table) {
            $table->string('learning_mode', 20)->default('tatap_muka')->after('status_guru');
            $table->foreignId('school_event_id')->nullable()->after('learning_mode')->constrained('school_events')->nullOnDelete();
        });

        Schema::table('school_events', function (Blueprint $table) {
            $table->time('early_dismissal_at')->nullable()->after('activity_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_events', function (Blueprint $table) {
            $table->dropColumn('early_dismissal_at');
        });

        Schema::table('jurnals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_event_id');
            $table->dropColumn('learning_mode');
        });
    }
};
