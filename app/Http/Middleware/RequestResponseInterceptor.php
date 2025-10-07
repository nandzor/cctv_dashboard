<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiCredential;

class RequestResponseInterceptor {
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Enable query logging if configured
        if (config('app.log_queries', false)) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        // Log API requests only
        if ($request->is('api/*')) {
            $this->logApiRequest($request, $response, $startTime, $startMemory);
        }

        return $response;
    }

    /**
     * Log API request to daily file
     */
    private function logApiRequest($request, $response, $startTime, $startMemory) {
        try {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // ms
            $memoryUsage = memory_get_usage() - $startMemory;
            $queryLog = config('app.log_queries', false) ? DB::getQueryLog() : [];
            $queryCount = count($queryLog);

            $apiCredential = $this->getApiCredential($request);

            $logData = [
                'timestamp' => now()->toIso8601String(),
                'api_credential_id' => $apiCredential?->id,
                'api_key' => $apiCredential?->api_key ? substr($apiCredential->api_key, 0, 10) . '***' : null,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'request_payload' => $this->sanitizePayload($request->all()),
                'response_status' => $response->getStatusCode(),
                'response_time_ms' => (int) $executionTime,
                'query_count' => $queryCount,
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            // Write to daily log file (instant, non-blocking)
            $this->writeToDailyLogFile('api_requests', $logData);

            // Performance alerts
            if (config('app.performance_monitoring.enabled', true)) {
                $slowThreshold = config('app.performance_monitoring.slow_query_threshold', 1000);
                $memoryThreshold = config('app.performance_monitoring.high_memory_threshold', 128);

                if ($executionTime > $slowThreshold) {
                    Log::warning('Slow API request detected', [
                        'endpoint' => $request->path(),
                        'execution_time' => $executionTime . 'ms',
                        'query_count' => $queryCount,
                    ]);
                }

                if (($memoryUsage / 1024 / 1024) > $memoryThreshold) {
                    Log::warning('High memory usage detected', [
                        'endpoint' => $request->path(),
                        'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to log API request', [
                'error' => $e->getMessage(),
                'endpoint' => $request->path()
            ]);
        }
    }

    /**
     * Write to daily log file
     */
    private function writeToDailyLogFile(string $logType, array $logData): void {
        $date = now()->toDateString(); // YYYY-MM-DD
        $logPath = "logs/{$logType}/{$date}.log";

        // Convert to JSON (one line per request)
        $jsonLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // Append to file
        Storage::disk('local')->append($logPath, $jsonLine);
    }

    /**
     * Get API credential from request
     */
    private function getApiCredential($request): ?ApiCredential {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return null;
        }

        return ApiCredential::where('api_key', $apiKey)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Sanitize sensitive fields from payload
     */
    private function sanitizePayload(array $payload): array {
        $sensitiveFields = ['password', 'api_secret', 'token', 'credit_card', 'stream_password'];

        foreach ($sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '***REDACTED***';
            }
        }

        return $payload;
    }
}
