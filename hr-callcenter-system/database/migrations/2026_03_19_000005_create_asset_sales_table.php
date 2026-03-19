<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_contact')->nullable();
            $table->decimal('sale_price', 15, 2);
            $table->date('sale_date');
            $table->foreignId('sold_by_officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_sales');
    }
};
