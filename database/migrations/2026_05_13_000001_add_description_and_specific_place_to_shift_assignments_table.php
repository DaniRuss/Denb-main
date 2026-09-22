<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_assignments', 'description')) {
                $table->text('description')->nullable()->after('block');
            }

            if (! Schema::hasColumn('shift_assignments', 'specific_place')) {
                $table->string('specific_place')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('shift_assignments', 'specific_place')) {
                $table->dropColumn('specific_place');
            }

            if (Schema::hasColumn('shift_assignments', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
