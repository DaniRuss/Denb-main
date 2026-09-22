<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── illegal_assets (base table) ──
        Schema::create('illegal_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_type');
            $table->text('description')->nullable();
            $table->string('location_found');
            $table->date('date_confiscated');
            $table->foreignId('officer_id')->constrained('officers')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('status')->default('Registered');
            $table->timestamps();
        });

        // ── asset_handovers ──
        Schema::create('asset_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->foreignId('to_woreda_id')->nullable()->constrained('woredas')->nullOnDelete();
            $table->foreignId('handed_over_to_officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->date('handover_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('confirmation_status')->default('pending');
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // ── asset_estimations ──
        Schema::create('asset_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── asset_transfers ──
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->foreignId('from_woreda_id')->nullable()->constrained('woredas')->nullOnDelete();
            $table->foreignId('to_sub_city_id')->nullable()->constrained('sub_cities')->nullOnDelete();
            $table->string('from_storage_facility')->nullable();
            $table->string('to_storage_facility')->nullable();
            $table->foreignId('transferred_by_officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->date('transfer_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('confirmation_status')->default('pending');
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // ── asset_sales ──
        Schema::create('asset_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->decimal('sale_amount', 12, 2)->nullable();
            $table->date('sale_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── asset_disposals ──
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->string('disposal_method')->nullable();
            $table->date('disposal_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── asset_activities (history/audit log) ──
        Schema::create('asset_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_activities');
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('asset_sales');
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('asset_estimations');
        Schema::dropIfExists('asset_handovers');
        Schema::dropIfExists('illegal_assets');
    }
};
