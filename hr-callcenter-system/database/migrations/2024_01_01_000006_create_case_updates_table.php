<?php
// database/migrations/2024_01_01_000006_create_case_updates_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('case_updates', function (Blueprint $table) {
            $table->id();
            $table->morphs('caseable'); // For both complaints and tips
            $table->foreignId('user_id')->constrained();
            $table->enum('update_type', [
                'status_change',
                'assignment',
                'investigation_note',
                'resolution',
                'public_update',
                'internal_note'
            ]);
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_public')->default(false)->comment('Visible to complainant');
            $table->boolean('notify_complainant')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('case_communications', function (Blueprint $table) {
            $table->id();
            $table->morphs('caseable');
            $table->foreignId('user_id')->constrained();
            $table->text('message');
            $table->enum('direction', ['incoming', 'outgoing']);
            $table->enum('channel', ['email', 'phone', 'portal', 'in_person']);
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('case_communications');
        Schema::dropIfExists('case_updates');
    }
};
