<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->unsignedInteger('usd_to_idr_rate')->default(16500)->after('default_assembly_fee');
            $table->timestamp('last_parts_sync_at')->nullable()->after('usd_to_idr_rate');
        });

        Schema::create('pc_catalog_parts', function (Blueprint $table) {
            $table->id();
            $table->string('external_key', 190)->unique();
            $table->string('slot', 25)->index();
            $table->string('name');
            $table->string('brand', 80)->nullable()->index();
            $table->decimal('price_usd', 10, 2);
            $table->unsignedBigInteger('estimated_price_idr')->index();
            $table->string('price_tier', 20)->default('mid')->index();
            $table->string('spec_summary')->nullable();
            $table->json('compatibility_specs')->nullable();
            $table->timestamps();

            $table->index(['slot', 'estimated_price_idr']);
            $table->index(['slot', 'price_tier', 'estimated_price_idr']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_catalog_parts');

        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['usd_to_idr_rate', 'last_parts_sync_at']);
        });
    }
};
