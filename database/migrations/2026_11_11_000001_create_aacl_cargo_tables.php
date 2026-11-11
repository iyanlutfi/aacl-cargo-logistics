<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 80)->unique()->after('id');
            $table->string('full_name', 150)->after('username');
            $table->string('role', 32)->default('driver')->after('password');
            $table->string('phone', 50)->nullable()->after('role');
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('PT. AACL Cargo & Logistics');
            $table->string('company_tagline')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone', 80)->nullable();
            $table->string('company_email', 120)->nullable();
            $table->string('cn_prefix', 20)->default('AACL');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50)->unique();
            $table->string('company_name');
            $table->string('pic_name', 120)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('default_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('fleets', function (Blueprint $table) {
            $table->id();
            $table->string('fleet_code', 50)->unique();
            $table->string('plate_number', 50)->unique();
            $table->string('vehicle_type', 100);
            $table->string('driver_name', 120)->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->string('status', 32)->default('available');
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('cn_number', 60)->unique();
            $table->string('shipment_date', 20)->index();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name');
            $table->text('shipper_address')->nullable();
            $table->text('receiver_address')->nullable();
            $table->integer('colly')->default(0);
            $table->decimal('weight_kg', 12, 2)->default(0);
            $table->decimal('volume_m3', 12, 4)->default(0);
            $table->decimal('area_m2', 12, 4)->default(0);
            $table->foreignId('fleet_id')->nullable()->constrained('fleets')->nullOnDelete();
            $table->string('fleet_name', 150)->nullable();
            $table->string('pickup_time', 100)->nullable();
            $table->decimal('service_cost', 15, 2)->default(0);
            $table->string('payment_status', 50)->default('Belum Lunas')->index();
            $table->string('status_1_at', 150)->nullable();
            $table->string('status_2_at', 150)->nullable();
            $table->string('status_3_at', 150)->nullable();
            $table->string('status_4_at', 150)->nullable();
            $table->string('status_5_at', 150)->nullable();
            $table->string('pod_receiver_name', 150)->nullable();
            $table->string('pod_received_at', 100)->nullable();
            $table->string('pod_photo_url', 255)->nullable();
            $table->longText('pod_signature_data')->nullable();
            $table->text('pod_notes')->nullable();
            $table->tinyInteger('current_stage')->default(0)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->tinyInteger('stage_number')->nullable();
            $table->string('stage_label', 150);
            $table->text('description')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_logs');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('fleets');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('company_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'full_name', 'role', 'phone']);
        });
    }
};
