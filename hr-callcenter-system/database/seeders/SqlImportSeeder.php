<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SqlImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('sql/callcenter.sql');
        if (!file_exists($path)) {
            $this->command->error("SQL file not found at {$path}");
            return;
        }

        $sql = file_get_contents($path);

        // Split by semicolon, but be careful with semicolons inside strings
        $statements = preg_split('/;\s*$/m', $sql);

        $this->command->info("Starting SQL import...");

        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');

        try {
            foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;

            // Remove comments from the beginning of the statement
            $statement = preg_replace('/^(\s*--.*?\n|\s*\/\*.*?\*\/)+/s', '', $statement);
            $statement = trim($statement);

            if (empty($statement)) continue;

            // Check if it's an INSERT statement
            if (preg_match('/^INSERT INTO/i', $statement)) {
                try {
                    // SQLite specific cleaning
                    $cleanStatement = str_replace('`', '"', $statement);
                    
                    // Remove MySQL specific bits
                    $cleanStatement = preg_replace('/CHARACTER SET \w+/i', '', $cleanStatement);
                    
                    \Illuminate\Support\Facades\DB::unprepared($cleanStatement);
                    $this->command->info("Ran: " . substr($statement, 0, 50) . "...");
                } catch (\Exception $e) {
                    $this->command->warn("Failed to run statement: " . substr($statement, 0, 100) . "...");
                    $this->command->error($e->getMessage());
                }
            } else {
                $this->command->line("Skipping (not an INSERT): " . substr($statement, 0, 30) . "...");
            }
        }
        } finally {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->command->info("SQL import completed!");
    }
}
