<?php
// database/migrations/2024_01_01_000008_create_escalations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('escalation_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('level')->unique();
            $table->integer('response_time_hours')->comment('Max hours for response');
            $table->integer('resolution_time_hours')->comment('Max hours for resolution');
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('escalations', function (Blueprint $table) {
            $table->id();
            $table->morphs('caseable');
            $table->foreignId('escalated_by')->constrained('users');
            $table->foreignId('escalated_to')->constrained('users');
            $table->integer('from_level');
            $table->integer('to_level');
            $table->enum('reason', [
                'timeout',                  // ጊዜ ማለፍ
                'complexity',               // ውስብስብነት
                'sensitivity',              // ስሱነት
                'conflict_of_interest',      // የጥቅም ግጭት
                'requires_approval',         // ማረጋገጫ ያስፈልጋል
                'other'
            ]);
            $table->text('reason_details');
            $table->text('notes')->nullable();
            $table->timestamp('escalated_at');
            $table->timestamp('responded_at')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('escalations');
        Schema::dropIfExists('escalation_levels');
    }
};
