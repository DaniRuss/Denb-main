<?php
// database/migrations/2024_01_01_000005_create_tips_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->string('tip_number')->unique()->comment('T-20240306-XXXX');

            // Reporter Information (Optional)
            $table->string('reporter_name')->nullable()->comment('Optional for anonymous');
            $table->string('reporter_email')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->boolean('is_anonymous')->default(true);

            // Tip Details
            $table->enum('tip_type', [
                'illegal_trade',           // ህገ-ወጥ ንግድ
                'alcohol_sales',            // ህገ-ወጥ አልኮል ሽያጭ
                'land_grabbing',            // የመሬት ወረራ
                'drug_activity',             // የአደንዛዥ እፅ ንግድ
                'counterfeit_goods',         // የሐሰት እቃዎች
                'illegal_construction',      // ህገ-ወጥ ግንባታ
                'environmental_violation',   // የአካባቢ ጥሰት
                'other'
            ]);

            $table->string('tip_type_other')->nullable();
            $table->string('location');
            $table->string('sub_city')->nullable();
            $table->string('woreda')->nullable();
            $table->string('specific_address')->nullable();
            $table->text('description');

            // Suspect Information
            $table->string('suspect_name')->nullable();
            $table->string('suspect_description')->nullable();
            $table->string('suspect_vehicle')->nullable();
            $table->string('suspect_company')->nullable();

            // Evidence
            $table->json('evidence_files')->nullable()->comment('Photos/Videos');
            $table->boolean('has_evidence')->default(false);
            $table->text('evidence_description')->nullable();

            // Urgency
            $table->enum('urgency_level', ['low', 'medium', 'high', 'immediate'])->default('medium');
            $table->boolean('is_ongoing')->default(false)->comment('Activity still happening');

            // Status
            $table->enum('status', [
                'pending',           // በመጠባበቅ ላይ
                'under_review',      // በግምገማ ላይ
                'investigating',     // ምርመራ በሂደት ላይ
                'verified',          // ተረጋግጧል
                'action_taken',       // እርምጃ ተወስዷል
                'closed',            // ተዘግቷል
                'false_report'        // የሐሰት ሪፖርት
            ])->default('pending');

            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('assigned_department')->nullable()->constrained('departments');

            // Reward Information (Optional)
            $table->boolean('eligible_for_reward')->default(false);
            $table->decimal('reward_amount', 10, 2)->nullable();
            $table->boolean('reward_claimed')->default(false);

            // Tracking
            $table->string('access_token')->nullable()->comment('For anonymous tracking');
            $table->timestamp('last_accessed')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tip_number');
            $table->index('status');
            $table->index('location');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tips');
    }
};
