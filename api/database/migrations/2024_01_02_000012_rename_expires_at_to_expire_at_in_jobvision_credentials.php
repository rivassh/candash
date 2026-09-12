<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('jobvision_credentials', 'expires_at')) {
            Schema::table('jobvision_credentials', function (Blueprint $table) {
                $table->renameColumn('expires_at', 'expire_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jobvision_credentials', 'expire_at')) {
            Schema::table('jobvision_credentials', function (Blueprint $table) {
                $table->renameColumn('expire_at', 'expires_at');
            });
        }
    }
};