<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensasis', function (Blueprint $table): void {
            $table->uuid('group_key')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('dispensasis', function (Blueprint $table): void {
            $table->dropColumn('group_key');
        });
    }
};
