<?php
// database/migrations/2024_01_01_000004_create_complaints_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique()->comment('Unique tracking ID');

            // Complainant Information
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('id_number')->nullable()->comment('National ID if available');
            $table->text('address')->nullable();

            // Complaint Details
            $table->enum('complaint_type', [
                'goods_confiscation',           // እቃ መንጠቅ
                'officer_misconduct',            // የሰራተኛ ስነምግባር
                'service_delivery',              // የአገልግሎት አሰጣጥ
                'uniform_issue',                  // የዩኒፎርም ችግር
                'salary_issue',                   // የደመወዝ ችግር
                'harassment',                      // ትንኮሳ
                'other'
            ]);

            $table->string('complaint_type_other')->nullable()->comment('If other is selected');
            $table->date('incident_date')->nullable();
            $table->string('incident_location')->nullable();
            $table->string('officer_name')->nullable()->comment('Name of officer involved');
            $table->string('officer_badge')->nullable()->comment('Badge number if known');

            $table->text('description');
            $table->json('attachments')->nullable()->comment('Multiple file paths');

            // Confiscation Specific Fields
            $table->string('confiscated_items')->nullable()->comment('Items confiscated');
            $table->decimal('confiscated_value', 10, 2)->nullable();
            $table->string('confiscation_reason')->nullable();
            $table->string('confiscation_location')->nullable();

            // Priority and Status
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', [
                'pending',           // በመጠባበቅ ላይ
                'under_review',      // በግምገማ ላይ
                'assigned',          // ተመድቧል
                'investigating',     // ምርመራ በሂደት ላይ
                'resolved',          // ተፈትቷል
                'closed',            // ተዘግቷል
                'reopened'           // እንደገና ተከፍቷል
            ])->default('pending');

            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('assigned_department')->nullable()->constrained('departments');
            $table->timestamp('assigned_at')->nullable();

            // Investigation
            $table->text('investigation_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');

            // Tracking
            $table->timestamp('last_viewed_by_complainant')->nullable();
            $table->integer('view_count')->default(0);

            // Anonymous Flag
            $table->boolean('is_anonymous')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('ticket_number');
            $table->index('email');
            $table->index('status');
            $table->index('priority');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
};
