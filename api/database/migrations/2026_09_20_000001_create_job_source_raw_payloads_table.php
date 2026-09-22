<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_source_raw_payloads', function (Blueprint $table) {
            $table->id();
            $table->string('driver', 100);
            $table->string('endpoint', 500);
            $table->string('entity_type', 50);
            $table->string('external_id', 100);
            $table->jsonb('payload');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['driver', 'entity_type', 'external_id'], 'job_source_raw_payloads_unique');
            $table->index('driver');
            $table->index('entity_type');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_source_raw_payloads');
    }
};