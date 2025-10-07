<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiCredential;
use App\Helpers\ApiResponseHelper;

class ApiKeyAuth {
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response {
        $apiKey = $request->header('X-API-Key');
        $apiSecret = $request->header('X-API-Secret');

        // Check if API key is provided
        if (!$apiKey || !$apiSecret) {
            return ApiResponseHelper::unauthorized('API key and secret are required');
        }

        // Find API credential
        $credential = ApiCredential::where('api_key', $apiKey)
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return ApiResponseHelper::error('Invalid API credentials', 'INVALID_CREDENTIALS', null, 401);
        }

        // Verify API secret
        if ($credential->api_secret !== $apiSecret) {
            return ApiResponseHelper::error('Invalid API credentials', 'INVALID_CREDENTIALS', null, 401);
        }

        // Check if credential is expired
        if ($credential->isExpired()) {
            return ApiResponseHelper::error('API credentials expired', 'EXPIRED_CREDENTIALS', null, 401);
        }

        // Update last_used_at
        $credential->update(['last_used_at' => now()]);

        // Attach credential to request for later use
        $request->merge(['api_credential' => $credential]);

        return $next($request);
    }
}
