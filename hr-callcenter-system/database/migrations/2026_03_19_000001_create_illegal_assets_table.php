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
        Schema::create('illegal_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_type');
            $table->text('description')->nullable();
            $table->string('location_found');
            $table->date('date_confiscated');
            $table->foreignId('officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->enum('status', [
                'Registered', 
                'Handed Over', 
                'Estimated', 
                'Transferred', 
                'Sold', 
                'Disposed'
            ])->default('Registered');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('illegal_assets');
    }
};
