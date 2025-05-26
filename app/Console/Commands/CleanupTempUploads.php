<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupTempUploads extends Command
{
    protected $signature = 'uploads:cleanup';
    protected $description = 'Clean up temporary upload files older than 24 hours';

    public function handle()
    {
        try {
            $this->info('Starting temporary uploads cleanup...');
            
            if (Storage::exists('livewire-tmp')) {
                $count = 0;
                foreach (Storage::files('livewire-tmp') as $filePathname) {
                    if (now()->subHours(24)->gt(Storage::lastModified($filePathname))) {
                        Storage::delete($filePathname);
                        $count++;
                    }
                }
                
                $this->info("Cleaned up {$count} temporary files.");
                Log::info("Temporary uploads cleanup completed", ['files_deleted' => $count]);
            } else {
                $this->info('No temporary upload directory found.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error cleaning up temporary uploads: ' . $e->getMessage());
            Log::error('Failed to cleanup temporary uploads', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
