<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

final class CleanupOldAudioFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $retentionDays = (int) config('ai.audio_retention_days', 30);

        if ($retentionDays === 0) {
            Log::info('Audio cleanup disabled (retention = 0)');
            return;
        }

        $cutoffTime = now()->subDays($retentionDays)->getTimestamp();
        $disk = Storage::disk('ai_public');

        try {
            $files = $disk->allFiles('ai-audio');

            $deleted = 0;
            $errors = [];

            foreach ($files as $file) {
                $lastModified = $disk->lastModified($file);

                if ($lastModified < $cutoffTime) {
                    try {
                        $disk->delete($file);
                        $deleted++;
                    } catch (\Exception $e) {
                        $errors[] = "$file: " . $e->getMessage();
                    }
                }
            }

            Log::info('Audio cleanup completed', [
                'retention_days' => $retentionDays,
                'cutoff_date' => date('Y-m-d H:i:s', $cutoffTime),
                'files_deleted' => $deleted,
                'errors' => count($errors),
                'error_details' => $errors,
            ]);
        } catch (\Exception $e) {
            Log::error('Audio cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
