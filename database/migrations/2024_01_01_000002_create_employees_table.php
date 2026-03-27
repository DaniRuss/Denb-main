<?php
// database/migrations/2024_01_01_000002_create_employees_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();

            // Name in Amharic
            $table->string('first_name_am');
            $table->string('last_name_am');
            $table->string('first_name_en')->nullable();
            $table->string('last_name_en')->nullable();

            // Personal Info
            $table->enum('gender', ['male', 'female']);
            $table->integer('age');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('emergency_contact')->nullable();

            // Location
            $table->foreignId('sub_city_id')->constrained();
            $table->foreignId('woreda_id')->constrained();
            $table->string('kebele')->nullable();
            $table->string('house_number')->nullable();

            // Employment
            $table->string('position')->comment('የስራ መደብ');
            $table->string('rank')->nullable();
            $table->enum('employee_type', [
                'para_military_officer',
                'civil_employee',
                'district_para_military'
            ]);
            $table->decimal('salary', 10, 2);
            $table->date('hire_date');
            $table->date('birth_date');
            $table->string('birthplace')->comment('የትውልድ ቦታ');

            // Education
            $table->enum('education_level', [
                'below_12',
                'complete_12',
                'certificate',
                'diploma',
                'degree',
                'masters',
                'phd'
            ]);
            $table->string('field_of_study')->nullable();
            $table->string('institution')->nullable();

            // IDs
            $table->string('national_id')->unique();
            $table->string('ethio_coder')->nullable();

            // Uniform Sizes
            $table->string('shirt_size')->nullable();
            $table->string('pant_size')->nullable();
            $table->integer('shoe_size_casual')->nullable();
            $table->integer('shoe_size_leather')->nullable();
            $table->integer('hat_size')->nullable()->comment('53-60');
            $table->string('cloth_size')->nullable();
            $table->string('rain_cloth_size')->nullable();
            $table->string('jacket_size')->nullable();
            $table->string('t_shirt_size')->nullable();

            // Training
            $table->integer('training_round')->nullable()->comment('1ኛ, 2ኛ, 3ኛ, 4ኛ, 5ኛ, 6ኛ ዙር');
            $table->date('last_training_date')->nullable();
            $table->text('training_notes')->nullable();

            // Status
            $table->enum('status', [
                'active',
                'suspended',
                'on_leave',
                'retired',
                'terminated'
            ])->default('active');
            $table->boolean('is_suspended_payment')->default(false);
            $table->text('suspension_reason')->nullable();
            $table->date('suspension_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
