<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MatchStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_position_id')->constrained()->cascadeOnDelete();
            $table->float('total_score')->default(0);
            $table->jsonb('breakdown')->nullable();
            $table->jsonb('strengths')->nullable();
            $table->jsonb('gaps')->nullable();
            $table->enum('status', array_map(fn($c) => $c->value, MatchStatus::cases()))->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['candidate_id', 'job_position_id']);
            $table->index('status');
            $table->index('total_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};