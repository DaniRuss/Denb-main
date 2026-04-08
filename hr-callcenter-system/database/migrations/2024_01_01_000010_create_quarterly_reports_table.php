<?php
// database/migrations/2024_01_01_000010_create_quarterly_reports_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('quarterly_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('year'); // 2018 ዓ.ም
            $table->enum('quarter', [1, 2, 3, 4]);
            $table->foreignId('sub_city_id')->constrained();
            $table->enum('report_type', [
                'para_military',
                'civil_employees',
                'uniform_summary',
                'training_summary',
                'complaint_summary',
                'tip_summary'
            ]);
            $table->json('data');
            $table->text('remarks')->nullable();
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->datetime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quarterly_reports');
    }
};
