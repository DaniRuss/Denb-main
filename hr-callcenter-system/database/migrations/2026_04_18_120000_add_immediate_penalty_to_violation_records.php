<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_records', function (Blueprint $table): void {
            // When true: penalty receipt is issued immediately at record creation,
            // bypassing the 3-warning accumulation rule (spec ¶91: ወዳያዉኑ እርምጃ).
            $table->boolean('immediate_penalty')->default(false)->after('repeat_offense_count');
        });
    }

    public function down(): void
    {
        Schema::table('violation_records', function (Blueprint $table): void {
            $table->dropColumn('immediate_penalty');
        });
    }
};
