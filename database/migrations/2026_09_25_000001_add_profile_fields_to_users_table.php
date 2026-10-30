<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable();
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'job_title', 'bio', 'avatar_path']);
        });
    }
};
