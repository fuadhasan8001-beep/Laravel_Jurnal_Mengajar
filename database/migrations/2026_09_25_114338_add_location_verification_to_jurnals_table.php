<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnals', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('location_accuracy', 10, 2)->nullable();
            $table->decimal('location_distance', 10, 2)->nullable();
            $table->timestamp('location_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table): void {
            $table->dropColumn(['latitude', 'longitude', 'location_accuracy', 'location_distance', 'location_verified_at']);
        });
    }
};
