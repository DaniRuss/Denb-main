<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('default_duration_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('action_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('incident_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete(); // employee involved
            $table->string('incident_type', 80);
            $table->string('location', 255)->nullable();
            $table->date('incident_date');
            $table->text('description');
            $table->string('status', 30)->default('reported'); // reported, penalty_assigned, in_follow_up, closed
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'incident_date']);
            $table->index(['incident_type', 'status']);
        });

        Schema::create('penalty_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incident_report_id')->constrained('incident_reports')->cascadeOnDelete();
            $table->foreignId('penalty_type_id')->constrained('penalty_types')->restrictOnDelete();
            $table->date('assigned_date');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->string('status', 30)->default('assigned'); // assigned, completed, revoked
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['incident_report_id', 'assigned_date']);
            $table->index(['penalty_type_id', 'status']);
        });

        Schema::create('follow_up_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incident_report_id')->constrained('incident_reports')->cascadeOnDelete();
            $table->foreignId('action_type_id')->constrained('action_types')->restrictOnDelete();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 30)->default('pending'); // pending, in_progress, done
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['incident_report_id', 'status']);
            $table->index(['action_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_actions');
        Schema::dropIfExists('penalty_assignments');
        Schema::dropIfExists('incident_reports');
        Schema::dropIfExists('action_types');
        Schema::dropIfExists('penalty_types');
    }
};

