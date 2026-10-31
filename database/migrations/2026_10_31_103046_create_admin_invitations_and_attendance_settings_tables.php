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
        Schema::create('admin_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable()->index();
            $table->string('token_hash')->unique();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->index(['used_at', 'expires_at']);
        });

        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('location_name')->default('LEXA Office');
            $table->decimal('office_latitude', 10, 7)->nullable();
            $table->decimal('office_longitude', 10, 7)->nullable();
            $table->string('office_address')->nullable();
            $table->unsignedInteger('radius_meters')->default(50);
            $table->json('allowed_ips')->nullable();
            $table->boolean('gps_verification_required')->default(true);
            $table->boolean('ip_verification_required')->default(true);
            $table->unsignedInteger('gps_accuracy_threshold_meters')->default(100);
            $table->unsignedInteger('late_grace_period_minutes')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
        Schema::dropIfExists('admin_invitations');
    }
};
