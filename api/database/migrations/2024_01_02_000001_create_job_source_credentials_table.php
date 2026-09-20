<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Generic job source credentials table.
 *
 * Holds credentials for any job source provider (JobVision, LinkedIn, JobInja, ...)
 * keyed off a `provider` discriminator column and a `config` JSON column so new
 * providers can be added without schema changes or per-provider tables.
 *
 * Idempotent: the production DB already has this table (it existed before the
 * 2024_01_02 "create" migration was introduced), so guard on hasTable like the
 * 2026 job_source_credentials migration does. This allows `migrate --force`
 * (already-already-run tables) to be a no-op instead of failing with
 * "table ... already exists".
 */
class CreateJobSourceCredentialsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('job_source_credentials')) {
            Schema::create('job_source_credentials', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->index();
                $table->string('name')->nullable();
                $table->json('config')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('username')->nullable();
                $table->string('password')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamp('deactivated_at')->nullable();
                $table->timestamps();

                $table->index(['provider', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_source_credentials');
    }
}
