<?php
// database/migrations/2024_01_01_000009_create_uniform_inventories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('uniform_inventories', function (Blueprint $table) {
            $table->id();
            $table->enum('item_type', [
                'shirt',
                'pant',
                'jacket',
                'rain_coat',
                't_shirt',
                'hat',
                'shoe_casual',
                'shoe_leather'
            ]);
            $table->enum('gender', ['male', 'female']);
            $table->string('size'); // S, M, L, XL, XXL, XXXL or shoe size numbers
            $table->integer('quantity');
            $table->integer('minimum_stock')->default(10);
            $table->integer('maximum_stock')->default(100);
            $table->foreignId('sub_city_id')->nullable()->constrained();
            $table->date('received_date');
            $table->string('supplier')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('uniform_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->enum('item_type', [
                'shirt',
                'pant',
                'jacket',
                'rain_coat',
                't_shirt',
                'hat',
                'shoe_casual',
                'shoe_leather'
            ]);
            $table->string('size');
            $table->integer('quantity')->default(1);
            $table->date('distribution_date');
            $table->enum('distribution_type', ['new', 'replacement', 'additional']);
            $table->string('reason')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uniform_distributions');
        Schema::dropIfExists('uniform_inventories');
    }
};
