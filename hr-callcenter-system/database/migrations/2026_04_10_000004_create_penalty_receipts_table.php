<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('violation_record_id')->constrained('violation_records')->restrictOnDelete();

            // Receipt identification
            $table->string('receipt_number', 50)->unique();     // unique pad number
            $table->date('issued_date');
            $table->time('issued_time')->nullable();

            // Fine details
            $table->decimal('fine_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);

            // Payment tracking (3-day rule from the document)
            $table->date('payment_deadline');                   // issued_date + 3 days
            $table->date('paid_date')->nullable();
            $table->string('payment_status', 30)->default('pending'); // pending, paid, overdue, court_filed, court_paid

            // Court escalation (double fine per document)
            $table->boolean('is_court_case')->default(false);
            $table->decimal('court_fine_amount', 12, 2)->nullable(); // double the original
            $table->date('court_filed_date')->nullable();

            // Violator refused to accept receipt (3 witness officers)
            $table->boolean('receipt_refused')->default(false);

            // Officers
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('witness_officer_1')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('witness_officer_2')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('witness_officer_3')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('receipt_number');
            $table->index('payment_status');
            $table->index('payment_deadline');
            $table->index('issued_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_receipts');
    }
};
