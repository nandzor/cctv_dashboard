<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $response = $next($request);

        // Only apply to API routes
        if ($request->is('api/*')) {
            // Add standard headers
            $response->headers->set('X-API-Version', '1.0');
            $response->headers->set('X-Request-ID', $request->header('X-Request-ID', (string) \Illuminate\Support\Str::uuid()));

            // Add performance headers if enabled
            if (config('app.performance_monitoring.include_in_headers', true)) {
                $response->headers->set('X-Query-Count', $this->getQueryCount());
                $response->headers->set('X-Memory-Usage', $this->getMemoryUsage());
                $response->headers->set('X-Execution-Time', $this->getExecutionTime());
            }

            // Add rate limit headers if available
            if ($response->headers->has('X-RateLimit-Limit')) {
                // Rate limit headers are already set by Laravel's throttle middleware
            }
        }

        return $response;
    }

    /**
     * Get query count
     */
    private function getQueryCount(): int {
        if (config('app.log_queries', false)) {
            return count(\DB::getQueryLog());
        }
        return 0;
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): string {
        $bytes = memory_get_usage();
        $mb = round($bytes / 1024 / 1024, 2);
        return $mb . 'MB';
    }

    /**
     * Get execution time
     */
    private function getExecutionTime(): string {
        if (defined('LARAVEL_START')) {
            $time = microtime(true) - LARAVEL_START;
            return round($time, 3) . 's';
        }
        return '0s';
    }
}
