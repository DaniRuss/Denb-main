<?php
// database/migrations/2024_01_01_000001_create_sub_cities_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name_am')->comment('አማርኛ ስም');
            $table->string('name_en');
            $table->integer('code')->unique();
            $table->timestamps();
        });

        Schema::create('woredas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_city_id')->constrained()->onDelete('cascade');
            $table->string('name_am');
            $table->string('name_en');
            $table->integer('code');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('woredas');
        Schema::dropIfExists('sub_cities');
    }
};
