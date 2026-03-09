<?php
// database/migrations/2024_01_01_000003_create_departments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name_am');
            $table->string('name_en');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('head_of_department_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained();
            $table->string('badge_number')->unique();
            $table->string('rank');
            $table->boolean('is_available')->default(true);
            $table->integer('case_load_limit')->default(10);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('officers');
        Schema::dropIfExists('departments');
    }
};
