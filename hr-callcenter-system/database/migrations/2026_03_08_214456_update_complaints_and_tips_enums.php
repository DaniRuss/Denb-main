<?php
// database/migrations/2026_03_08_214456_update_complaints_and_tips_enums.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('complaint_type')->change();
            $table->string('priority')->change();
            $table->string('status')->default('pending')->change();
        });

        Schema::table('tips', function (Blueprint $table) {
            $table->string('tip_type')->change();
            $table->string('urgency_level')->change();
            $table->string('status')->default('pending')->change();
        });
    }

    public function down()
    {
        // Reverting to enums is complex and usually requires raw SQL or dropping/recreating.
        // For development, keeping them as strings is safer.
    }
};
