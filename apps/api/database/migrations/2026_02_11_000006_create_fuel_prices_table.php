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
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_station_id')->constrained()->cascadeOnDelete();
            $table->string('fuel_type', 20);
            $table->decimal('price_eur_liter', 6, 3);
            $table->date('recorded_at');
            $table->timestamps();

            $table->unique(['fuel_station_id', 'fuel_type', 'recorded_at'], 'fuel_station_type_recorded_unique');
            $table->index(['fuel_type', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
