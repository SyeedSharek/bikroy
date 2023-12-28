<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DbRestoreCommand extends Command
{
    protected $signature = 'db:restore {file}';
    protected $description = 'Restore the database from a SQL dump file';

    public function handle()
    {
        $file = $this->argument('file');

        if (Storage::exists($file)) {
            $filePath = Storage::path($file);

            // Customize the database restore logic based on your database type (e.g., MySQL)
            $this->restoreDatabase($filePath);
            

            $this->info('Database restored successfully.');
        } else {
            $this->error('File not found: ' . $file);
        }
    }

    protected function restoreDatabase($filePath)
    {
        // Customize this method based on your database type and restore process
        // This example assumes MySQL
        $databaseConfig = config('database.connections.mysql');

        $command = sprintf(
            'mysql -u%s -p%s -h%s %s < %s',
            $databaseConfig['username'],
            $databaseConfig['password'],
            $databaseConfig['host'],
            $databaseConfig['database'],
            $filePath
        );

        // Execute the shell command
        exec($command);
    }
}
