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
        foreach (['leave_requests', 'permission_requests'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                $table->text('review_note')->nullable()->after('reviewed_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['leave_requests', 'permission_requests'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('reviewed_by');
                $table->dropColumn(['reviewed_at', 'review_note']);
            });
        }
    }
};
