<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leads01_campaigns', 'visivel')) {
            Schema::table('leads01_campaigns', function (Blueprint $table) {
                $table->boolean('visivel')->default(false)->after('status');
                $table->index(['user_id', 'status', 'visivel']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leads01_campaigns', 'visivel')) {
            Schema::table('leads01_campaigns', function (Blueprint $table) {
                $table->dropIndex('leads01_campaigns_user_id_status_visivel_index');
                $table->dropColumn('visivel');
            });
        }
    }
};
