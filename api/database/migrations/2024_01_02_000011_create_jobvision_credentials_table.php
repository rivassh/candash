<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobvision_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->text('cookie')->nullable();
            $table->string('api_url')->default('https://employerapi.jobvision.ir');
            $table->string('account_url')->default('https://account.jobvision.ir');
            $table->json('job_post_ids')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index('is_active');
            $table->index('expire_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobvision_credentials');
    }
};