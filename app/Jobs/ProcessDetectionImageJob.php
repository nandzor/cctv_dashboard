<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ProcessDetectionImageJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60];

    public string $imagePath;
    public int $eventLogId;

    public function __construct(string $imagePath, int $eventLogId) {
        $this->imagePath = $imagePath;
        $this->eventLogId = $eventLogId;
        $this->onQueue('images');
    }

    public function handle(): void {
        try {
            if (!Storage::disk('local')->exists($this->imagePath)) {
                Log::warning('Image file not found', ['path' => $this->imagePath]);
                return;
            }

            $fullPath = Storage::disk('local')->path($this->imagePath);

            // Load image
            $image = Image::make($fullPath);

            // 1. Resize if larger than 1920x1080
            if ($image->width() > 1920 || $image->height() > 1080) {
                $image->resize(1920, 1080, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // 2. Add watermark (timestamp + branch)
            $watermarkText = now()->format('Y-m-d H:i:s') . ' | Event #' . $this->eventLogId;
            $image->text($watermarkText, 10, $image->height() - 10, function ($font) {
                $font->file(public_path('fonts/Poppins-Regular.ttf'));
                $font->size(14);
                $font->color('#ffffff');
                $font->align('left');
                $font->valign('bottom');
            });

            // 3. Optimize quality (85%)
            $image->save($fullPath, 85);

            // 4. Create thumbnail (320x240)
            $thumbnailPath = str_replace('.', '_thumb.', $this->imagePath);
            $thumbnail = Image::make($fullPath);
            $thumbnail->fit(320, 240);
            $thumbnail->save(Storage::disk('local')->path($thumbnailPath), 75);

            Log::info('Image processed successfully', [
                'original' => $this->imagePath,
                'thumbnail' => $thumbnailPath,
                'event_log_id' => $this->eventLogId,
            ]);
        } catch (\Exception $e) {
            Log::error('Image processing failed', [
                'path' => $this->imagePath,
                'event_log_id' => $this->eventLogId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void {
        Log::error('Image processing job failed permanently', [
            'path' => $this->imagePath,
            'event_log_id' => $this->eventLogId,
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array {
        return ['image', 'processing', 'event:' . $this->eventLogId];
    }
}
