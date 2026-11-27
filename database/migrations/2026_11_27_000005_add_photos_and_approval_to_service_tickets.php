<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_tickets', function (Blueprint $table) {
            $table->string('photo_before_path')->nullable()->after('diagnosis_notes');
            $table->string('photo_after_path')->nullable()->after('photo_before_path');
            $table->timestamp('customer_approved_at')->nullable()->after('warranty_until');
            $table->string('customer_approval_note', 255)->nullable()->after('customer_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'photo_before_path',
                'photo_after_path',
                'customer_approved_at',
                'customer_approval_note',
            ]);
        });
    }
};
