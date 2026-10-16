<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * Certificates and the dentists' diploma/ID uploads used to be written to the public disk,
 * where anyone with the URL could open them. New ones already go to the private disk; this
 * moves the existing files there too. Paths are kept, so the database needs no change.
 * Runs on deploy through deploy-hook.php, since the server has no SSH.
 */
return new class extends Migration
{
    private const DIRECTORIES = ['certificates', 'validations'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->moveFiles(from: Storage::disk('public'), to: Storage::disk('local'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->moveFiles(from: Storage::disk('local'), to: Storage::disk('public'));
    }

    private function moveFiles(FilesystemAdapter $from, FilesystemAdapter $to): void
    {
        foreach (self::DIRECTORIES as $directory) {
            foreach ($from->allFiles($directory) as $path) {
                if (! $to->exists($path)) {
                    $to->writeStream($path, $from->readStream($path));
                }

                // Only delete the original once the copy is confirmed in place
                if ($to->exists($path) && $to->size($path) === $from->size($path)) {
                    $from->delete($path);
                }
            }
        }
    }
};
