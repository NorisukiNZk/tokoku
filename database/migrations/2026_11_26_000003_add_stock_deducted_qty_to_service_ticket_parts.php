<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_ticket_parts', function (Blueprint $table) {
            $table->unsignedSmallInteger('stock_deducted_qty')->default(0)->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('service_ticket_parts', function (Blueprint $table) {
            $table->dropColumn('stock_deducted_qty');
        });
    }
};
