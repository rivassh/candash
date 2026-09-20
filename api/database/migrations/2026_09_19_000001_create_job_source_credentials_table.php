<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_source_credentials')) {
            return;
        }
        Schema::create('job_source_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('name')->nullable();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('provider');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_source_credentials');
    }
};