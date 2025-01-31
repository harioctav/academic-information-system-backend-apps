<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanupTranscriptPDFs extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'transcripts:cleanup';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clean up transcript PDFs older than 1 hour';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Starting transcript PDFs cleanup...');

    // List all files recursively in transcripts directory
    $files = Storage::allFiles('transcripts');
    $this->info('Found files: ' . json_encode($files));

    $deletedCount = 0;
    foreach ($files as $file) {
      $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));
      $this->info("File: {$file} Last modified: {$lastModified}");

      if ($lastModified->diffInHours(now()) >= 1) {
        Storage::delete($file);
        $deletedCount++;
        $this->line("Deleted: {$file}");
      } else {
        $this->info("File {$file} is less than 1 hour old, skipping...");
      }
    }

    $this->info("Cleanup completed. Deleted {$deletedCount} files.");
  }
}
