<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tips', function (Blueprint $table): void {
            if (! Schema::hasColumn('tips', 'title')) {
                $table->string('title')->nullable()->after('tip_number');
            }

            if (! Schema::hasColumn('tips', 'tip_source')) {
                $table->string('tip_source')->default('public')->after('title');
            }

            if (! Schema::hasColumn('tips', 'caller_name')) {
                $table->string('caller_name')->nullable()->after('reporter_phone');
            }

            if (! Schema::hasColumn('tips', 'caller_phone')) {
                $table->string('caller_phone')->nullable()->after('caller_name');
            }

            if (! Schema::hasColumn('tips', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('tips', 'supervisor_comment')) {
                $table->text('supervisor_comment')->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('tips', 'director_comment')) {
                $table->text('director_comment')->nullable()->after('supervisor_comment');
            }

            if (! Schema::hasColumn('tips', 'investigation_status')) {
                $table->string('investigation_status')->nullable()->after('director_comment');
            }

            if (! Schema::hasColumn('tips', 'sub_city_notes')) {
                $table->text('sub_city_notes')->nullable()->after('investigation_status');
            }

            if (! Schema::hasColumn('tips', 'supervisor_reviewed_at')) {
                $table->timestamp('supervisor_reviewed_at')->nullable()->after('sub_city_notes');
            }

            if (! Schema::hasColumn('tips', 'director_reviewed_at')) {
                $table->timestamp('director_reviewed_at')->nullable()->after('supervisor_reviewed_at');
            }

            if (! Schema::hasColumn('tips', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('director_reviewed_at');
            }

            if (! Schema::hasColumn('tips', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('dispatched_at');
            }
        });

        Schema::table('tips', function (Blueprint $table): void {
            $table->index(['tip_source', 'status'], 'tips_tip_source_status_index');
            $table->index(['tip_source', 'sub_city'], 'tips_tip_source_sub_city_index');
            $table->index(['created_by'], 'tips_created_by_index');
        });
    }

    public function down(): void
    {
        Schema::table('tips', function (Blueprint $table): void {
            $table->dropIndex('tips_tip_source_status_index');
            $table->dropIndex('tips_tip_source_sub_city_index');
            $table->dropIndex('tips_created_by_index');

            if (Schema::hasColumn('tips', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            $columns = [
                'title',
                'tip_source',
                'caller_name',
                'caller_phone',
                'supervisor_comment',
                'director_comment',
                'investigation_status',
                'sub_city_notes',
                'supervisor_reviewed_at',
                'director_reviewed_at',
                'dispatched_at',
                'closed_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tips', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
