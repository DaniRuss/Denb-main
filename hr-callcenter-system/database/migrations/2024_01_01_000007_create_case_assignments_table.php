<?php
// database/migrations/2024_01_01_000007_create_case_assignments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('case_assignments', function (Blueprint $table) {
            $table->id();
            $table->morphs('caseable');
            $table->foreignId('assigned_by')->constrained('users');
            $table->foreignId('assigned_to')->constrained('users');
            $table->foreignId('department_id')->constrained();
            $table->enum('assignment_type', ['primary', 'supporting', 'reviewer']);
            $table->text('assignment_notes')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active', 'completed', 'reassigned'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('case_assignments');
    }
};
