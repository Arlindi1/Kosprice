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
        Schema::table('fuel_stations', function (Blueprint $table): void {
            $table->string('brand_key', 60)->default('local')->after('city_id');
            $table->index(['city_id', 'brand_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_stations', function (Blueprint $table): void {
            $table->dropIndex(['city_id', 'brand_key']);
            $table->dropColumn('brand_key');
        });
    }
};

