<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class StorageCheck extends Command
{
    protected $signature = 'storage:check';
    protected $description = 'Check and setup storage directories for file uploads';

    public function handle()
    {
        $this->info('Checking storage setup...');

        // Check if storage link exists
        $publicPath = public_path('storage');
        if (!file_exists($publicPath)) {
            $this->info('Creating storage link...');
            $this->call('storage:link');
        }

        // Create and check permissions for upload directories
        $directories = [
            'app/public/products',
            'app/livewire-tmp'
        ];

        foreach ($directories as $dir) {
            $path = storage_path($dir);
            
            if (!File::exists($path)) {
                $this->info("Creating directory: {$dir}");
                File::makeDirectory($path, 0755, true);
            }

            // Check permissions
            if (!is_writable($path)) {
                $this->error("Directory not writable: {$dir}");
                $this->info('Attempting to fix permissions...');
                chmod($path, 0755);
            }

            $this->info("Directory {$dir} is ready and writable.");
        }

        // Clean up old temporary files
        $this->info('Cleaning up old temporary files...');
        $tmpPath = storage_path('app/livewire-tmp');
        if (File::exists($tmpPath)) {
            foreach (File::files($tmpPath) as $file) {
                if (now()->subHours(24)->gt(File::lastModified($file))) {
                    File::delete($file);
                }
            }
        }

        $this->info('Storage setup complete!');

        // Return directory status for logging
        return [
            'public_link_exists' => file_exists($publicPath),
            'products_dir' => [
                'exists' => File::exists(storage_path('app/public/products')),
                'writable' => is_writable(storage_path('app/public/products')),
            ],
            'tmp_dir' => [
                'exists' => File::exists(storage_path('app/livewire-tmp')),
                'writable' => is_writable(storage_path('app/livewire-tmp')),
            ]
        ];
    }
}
