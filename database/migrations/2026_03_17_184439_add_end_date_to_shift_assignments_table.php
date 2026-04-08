<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_assignments', 'end_date')) {
                $table->date('end_date')->nullable()->after('assigned_date');
            }
        });
    }
    
    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('shift_assignments', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
};
