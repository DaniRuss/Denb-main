<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('illegal_assets', 'quantity')) {
            Schema::table('illegal_assets', function (Blueprint $table) {
                $table->integer('quantity')->default(1)->after('asset_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('illegal_assets', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
