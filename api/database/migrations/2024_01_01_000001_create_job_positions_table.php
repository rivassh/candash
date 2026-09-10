<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\JobPositionStatus;
use App\Enums\JobLevel;
use App\Enums\EmploymentType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department');
            $table->enum('level', array_map(fn($c) => $c->value, JobLevel::cases()));
            $table->enum('employment_type', array_map(fn($c) => $c->value, EmploymentType::cases()));
            $table->unsignedTinyInteger('min_experience_years')->default(0);
            $table->string('education_requirements')->nullable();
            $table->text('description')->nullable();
            $table->string('external_id')->nullable()->unique();
            $table->enum('status', array_map(fn($c) => $c->value, JobPositionStatus::cases()))->default('draft');
            $table->jsonb('required_skills')->nullable();
            $table->jsonb('preferred_skills')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_positions');
    }
};