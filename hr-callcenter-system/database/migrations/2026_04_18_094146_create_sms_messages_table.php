<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_messages', function (Blueprint $table): void {
            $table->id();

            $table->string('to', 20);
            $table->string('raw_phone', 30)->nullable();
            $table->text('body');

            $table->string('template_key', 60)->nullable();
            $table->string('driver', 30)->default('log');

            $table->string('notifiable_type', 120)->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();

            $table->foreignId('violator_id')->nullable()->constrained('violators')->nullOnDelete();

            $table->string('status', 20)->default('queued'); // queued | sent | failed | delivered
            $table->string('provider_message_id', 120)->nullable();
            $table->text('error')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('status');
            $table->index('template_key');
            $table->index('to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
