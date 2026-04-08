<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update Officers table
        Schema::table('officers', function (Blueprint $table) {
            if (!Schema::hasColumn('officers', 'rank_am')) {
                $table->string('rank_am', 100)->nullable()->after('rank');
            }
            if (!Schema::hasColumn('officers', 'specialization')) {
                $table->string('specialization', 100)->nullable()->after('rank_am');
            }
            if (!Schema::hasColumn('officers', 'phone')) {
                $table->string('phone', 20)->nullable()->after('specialization');
            }
            if (!Schema::hasColumn('officers', 'status')) {
                $table->enum('status', ['active', 'on_leave', 'suspended', 'retired', 'transferred'])->default('active')->after('phone');
            }
            if (!Schema::hasColumn('officers', 'date_joined')) {
                $table->date('date_joined')->nullable()->after('status');
            }
            if (!Schema::hasColumn('officers', 'notes')) {
                $table->text('notes')->nullable()->after('date_joined');
            }
        });

        // Update Uniform Inventories table
        Schema::table('uniform_inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('uniform_inventories', 'item_name')) {
                $table->string('item_name', 255)->nullable()->after('id');
            }
            if (!Schema::hasColumn('uniform_inventories', 'item_name_am')) {
                $table->string('item_name_am', 255)->nullable()->after('item_name');
            }
            if (!Schema::hasColumn('uniform_inventories', 'category')) {
                $table->string('category', 100)->default('other')->after('item_name_am');
            }
            if (!Schema::hasColumn('uniform_inventories', 'quantity_in_stock')) {
                $table->unsignedInteger('quantity_in_stock')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('uniform_inventories', 'min_stock_level')) {
                $table->unsignedInteger('min_stock_level')->default(10)->after('minimum_stock');
            }
            if (!Schema::hasColumn('uniform_inventories', 'unit')) {
                $table->string('unit', 30)->default('pieces')->after('min_stock_level');
            }
            if (!Schema::hasColumn('uniform_inventories', 'location')) {
                $table->string('location', 100)->nullable()->after('unit');
            }
            if (!Schema::hasColumn('uniform_inventories', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('uniform_inventories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('unit_cost');
            }
        });

        // Update Quarterly Reports table
        Schema::table('quarterly_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('quarterly_reports', 'title')) {
                $table->string('title', 255)->nullable()->after('id');
            }
            if (!Schema::hasColumn('quarterly_reports', 'period_start')) {
                $table->date('period_start')->nullable()->after('quarter');
            }
            if (!Schema::hasColumn('quarterly_reports', 'period_end')) {
                $table->date('period_end')->nullable()->after('period_start');
            }
            if (!Schema::hasColumn('quarterly_reports', 'total_complaints')) {
                $table->unsignedInteger('total_complaints')->default(0)->after('period_end');
            }
            if (!Schema::hasColumn('quarterly_reports', 'resolved_complaints')) {
                $table->unsignedInteger('resolved_complaints')->default(0)->after('total_complaints');
            }
            if (!Schema::hasColumn('quarterly_reports', 'pending_complaints')) {
                $table->unsignedInteger('pending_complaints')->default(0)->after('resolved_complaints');
            }
            if (!Schema::hasColumn('quarterly_reports', 'total_tips')) {
                $table->unsignedInteger('total_tips')->default(0)->after('pending_complaints');
            }
            if (!Schema::hasColumn('quarterly_reports', 'verified_tips')) {
                $table->unsignedInteger('verified_tips')->default(0)->after('total_tips');
            }
            if (!Schema::hasColumn('quarterly_reports', 'total_escalations')) {
                $table->unsignedInteger('total_escalations')->default(0)->after('verified_tips');
            }
            if (!Schema::hasColumn('quarterly_reports', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->after('total_escalations');
            }
            if (!Schema::hasColumn('quarterly_reports', 'summary')) {
                $table->text('summary')->nullable()->after('department_id');
            }
            if (!Schema::hasColumn('quarterly_reports', 'recommendations')) {
                $table->text('recommendations')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('quarterly_reports', 'status')) {
                $table->enum('status', ['draft', 'under_review', 'approved', 'published'])->default('draft')->after('recommendations');
            }
            if (!Schema::hasColumn('quarterly_reports', 'report_file')) {
                $table->string('report_file', 255)->nullable()->after('status');
            }
        });

        // Add Complaint ID to Case Assignments and Escalations if missing
        Schema::table('case_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('case_assignments', 'complaint_id')) {
                $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete()->after('id');
            }
        });

        Schema::table('escalations', function (Blueprint $table) {
            if (!Schema::hasColumn('escalations', 'complaint_id')) {
                $table->foreignId('complaint_id')->nullable()->constrained()->nullOnDelete()->after('id');
            }
        });
    }

    public function down(): void
    {
        // No need for down for this sync migration in this context
    }
};
