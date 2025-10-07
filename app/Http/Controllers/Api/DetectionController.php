<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDetectionRequest;
use App\Helpers\ApiResponseHelper;
use App\Helpers\StorageHelper;
use App\Jobs\ProcessDetectionJob;
use Illuminate\Support\Str;

class DetectionController extends Controller {
    /**
     * Log a new detection event (Async - 202 Accepted)
     *
     * @param StoreDetectionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreDetectionRequest $request) {
        try {
            $data = $request->validated();
            $imagePath = null;

            // Upload image if present (sync for immediate availability)
            if ($request->hasFile('image')) {
                $imageResult = StorageHelper::store(
                    $request->file('image'),
                    'local',
                    'events/' . now()->format('Y/m/d'),
                    [
                        'related_table' => 'event_logs',
                        'uploaded_by' => null, // API upload
                    ]
                );

                if ($imageResult['success']) {
                    $imagePath = $imageResult['file_path'];
                }
            }

            // Generate unique job ID
            $jobId = (string) Str::uuid();

            // Dispatch to queue (non-blocking)
            ProcessDetectionJob::dispatch(
                $data['re_id'],
                $data['branch_id'],
                $data['device_id'],
                $data['detected_count'],
                $data['detection_data'] ?? [],
                $imagePath,
                $jobId
            )->onQueue('detections');

            // Return 202 Accepted (immediate response)
            return ApiResponseHelper::accepted([
                'job_id' => $jobId,
                'status' => 'processing',
                'message' => 'Detection queued for processing',
            ], 'Detection event received and queued successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to process detection request',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get detection status by job ID
     *
     * @param string $jobId
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(string $jobId) {
        // Check if job exists in jobs table (still processing)
        $job = \DB::table('jobs')->where('id', $jobId)->first();

        if ($job) {
            return ApiResponseHelper::success([
                'job_id' => $jobId,
                'status' => 'processing',
                'attempts' => $job->attempts,
            ], 'Job is still processing');
        }

        // Check failed_jobs
        $failedJob = \DB::table('failed_jobs')->where('uuid', $jobId)->first();

        if ($failedJob) {
            return ApiResponseHelper::error(
                'Job processing failed',
                'JOB_FAILED',
                ['error' => $failedJob->exception],
                500
            );
        }

        // Job completed (not in queue tables)
        return ApiResponseHelper::success([
            'job_id' => $jobId,
            'status' => 'completed',
        ], 'Job completed successfully');
    }
}
