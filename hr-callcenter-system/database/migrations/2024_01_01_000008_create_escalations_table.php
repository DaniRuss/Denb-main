<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();
            $table->foreignId('escalated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('escalated_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('status', 30)->default('open');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['complaint_id', 'level']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('escalations');
    }
};
