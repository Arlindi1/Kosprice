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
        Schema::table('products', function (Blueprint $table): void {
            $table->string('image_key', 80)->nullable()->after('category');
            $table->string('unit_label', 80)->nullable()->after('unit');
            $table->string('brand_hint', 120)->nullable()->after('unit_label');
            $table->boolean('is_core_basket')->default(false)->after('brand_hint');
            $table->index('is_core_basket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['is_core_basket']);
            $table->dropColumn([
                'image_key',
                'unit_label',
                'brand_hint',
                'is_core_basket',
            ]);
        });
    }
};
