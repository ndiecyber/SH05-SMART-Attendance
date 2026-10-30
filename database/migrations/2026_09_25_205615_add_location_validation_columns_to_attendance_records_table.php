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
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->decimal('distance_from_office', 10, 2)->nullable()->after('location');
            $table->decimal('location_accuracy', 10, 2)->nullable()->after('distance_from_office');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->dropColumn(['distance_from_office', 'location_accuracy']);
        });
    }
};
