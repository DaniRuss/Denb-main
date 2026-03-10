<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('username')->nullable()->after('email');
            });
        }

        // Backfill existing users with their email as username if empty.
        DB::table('users')
            ->whereNull('username')
            ->orWhere('username', '')
            ->update(['username' => DB::raw('email')]);

        if (! $this->indexExists('users', 'users_username_unique')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('username', 'users_username_unique');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('username')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('username');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
