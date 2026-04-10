<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violators', function (Blueprint $table): void {
            $table->id();

            $table->string('type', 20)->default('individual'); // individual | organization
            $table->string('full_name_am');
            $table->string('full_name_en')->nullable();

            $table->foreignId('sub_city_id')->nullable()->constrained('sub_cities')->nullOnDelete();
            $table->foreignId('woreda_id')->nullable()->constrained('woredas')->nullOnDelete();
            $table->string('specific_location')->nullable();
            $table->string('house_number', 50)->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('id_number', 50)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['sub_city_id', 'woreda_id']);
            $table->index('phone');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violators');
    }
};
