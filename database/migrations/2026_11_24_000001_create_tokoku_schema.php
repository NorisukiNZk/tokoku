<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('role', 20)->default('admin')->after('phone');
        });

        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('Tokoku');
            $table->string('tagline')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('whatsapp_number', 30)->default('6280000000000');
            $table->string('workshop_address')->default('[ALAMAT WORKSHOP: Ubah di Pengaturan Toko]');
            $table->string('cod_areas')->default('Ambil di workshop atau COD area lokal sesuai kesepakatan');
            $table->string('operating_hours')->default('Senin - Sabtu, 09:00 - 18:00');
            $table->unsignedInteger('default_assembly_fee')->default(150000);
            $table->boolean('is_placeholder_data')->default(true);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('component_slot', 30)->nullable()->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 60)->unique();
            $table->string('brand', 80)->nullable();
            $table->string('condition', 30)->default('new')->index();
            $table->string('warranty_info')->nullable();
            $table->text('condition_notes')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->boolean('is_negotiable')->default(false);
            $table->unsignedInteger('stock')->default(1);
            $table->string('availability_status', 20)->default('available')->index();
            $table->string('location_area', 120)->default('Workshop Tokoku');
            $table->boolean('cod_available')->default(true);
            $table->boolean('show_in_marketplace')->default(true)->index();
            $table->boolean('show_in_pc_builder')->default(false)->index();
            $table->string('primary_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('compatibility_specs')->nullable();
            $table->boolean('is_sample_data')->default(false);
            $table->timestamps();
        });

        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category_label', 60);
            $table->text('description');
            $table->string('estimated_duration', 60);
            $table->unsignedBigInteger('base_price');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->text('customer_address')->nullable();
            $table->string('device_type', 50);
            $table->string('device_brand_model', 140);
            $table->foreignId('service_package_id')->nullable()->constrained()->nullOnDelete();
            $table->text('complaint_summary');
            $table->string('visit_type', 20)->default('walk_in');
            $table->date('preferred_date')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('booked')->index();
            $table->text('diagnosis_notes')->nullable();
            $table->unsignedSmallInteger('temp_before_c')->nullable();
            $table->unsignedSmallInteger('temp_after_c')->nullable();
            $table->unsignedBigInteger('service_fee')->default(0);
            $table->unsignedBigInteger('parts_total')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->date('warranty_until')->nullable();
            $table->boolean('is_sample_data')->default(false);
            $table->timestamps();
        });

        Schema::create('service_ticket_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30);
            $table->text('note');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('service_ticket_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('part_name');
            $table->unsignedSmallInteger('qty')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();
        });

        Schema::create('pc_builds', function (Blueprint $table) {
            $table->id();
            $table->string('share_code', 20)->unique();
            $table->string('build_name');
            $table->string('customer_name')->nullable();
            $table->string('purpose_label', 80)->nullable();
            $table->json('selected_components');
            $table->unsignedSmallInteger('estimated_wattage')->default(0);
            $table->unsignedSmallInteger('recommended_psu_wattage')->default(450);
            $table->boolean('compatibility_passed')->default(true);
            $table->json('compatibility_notes')->nullable();
            $table->boolean('include_assembly_service')->default(true);
            $table->unsignedBigInteger('assembly_fee')->default(150000);
            $table->unsignedBigInteger('parts_subtotal')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_builds');
        Schema::dropIfExists('service_ticket_parts');
        Schema::dropIfExists('service_ticket_logs');
        Schema::dropIfExists('service_tickets');
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('store_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role']);
        });
    }
};
