<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penalty_receipts', function (Blueprint $table): void {
            $table->decimal('authority_share', 12, 2)->default(0)->after('paid_amount');
            $table->decimal('city_finance_share', 12, 2)->default(0)->after('authority_share');
        });
    }

    public function down(): void
    {
        Schema::table('penalty_receipts', function (Blueprint $table): void {
            $table->dropColumn(['authority_share', 'city_finance_share']);
        });
    }
};
