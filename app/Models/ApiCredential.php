<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Helpers\EncryptionHelper;

class ApiCredential extends Model {
    use HasFactory;

    protected $fillable = [
        'credential_name',
        'api_key',
        'api_secret',
        'branch_id',
        'device_id',
        'permissions',
        'rate_limit',
        'expires_at',
        'last_used_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'permissions' => 'array', // JSONB
        'rate_limit' => 'integer',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'api_secret', // Hide secret from JSON responses
    ];

    /**
     * Get the decrypted API key
     */
    public function getApiKeyAttribute($value) {
        return EncryptionHelper::decrypt($value);
    }

    /**
     * Set the encrypted API key
     */
    public function setApiKeyAttribute($value) {
        $this->attributes['api_key'] = EncryptionHelper::encrypt($value);
    }

    /**
     * Get the decrypted API secret
     */
    public function getApiSecretAttribute($value) {
        return EncryptionHelper::decrypt($value);
    }

    /**
     * Set the encrypted API secret
     */
    public function setApiSecretAttribute($value) {
        $this->attributes['api_secret'] = EncryptionHelper::encrypt($value);
    }

    /**
     * Get the raw encrypted API key (for database operations)
     */
    public function getRawApiKeyAttribute() {
        return $this->getRawOriginal('api_key');
    }

    /**
     * Get the raw encrypted API secret (for database operations)
     */
    public function getRawApiSecretAttribute() {
        return $this->getRawOriginal('api_secret');
    }

    /**
     * Get the branch (if scoped)
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get the device (if scoped)
     */
    public function device() {
        return $this->belongsTo(DeviceMaster::class, 'device_id', 'device_id');
    }

    /**
     * Get the user who created this credential
     */
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get API usage summaries
     */
    public function usageSummaries() {
        return $this->hasMany(ApiUsageSummary::class, 'api_credential_id');
    }

    /**
     * Generate API key
     */
    public static function generateApiKey($prefix = 'cctv') {
        return $prefix . '_' . Str::random(32);
    }

    /**
     * Generate API secret
     */
    public static function generateApiSecret() {
        return Str::random(64);
    }

    /**
     * Check if credential is expired
     */
    public function isExpired() {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if credential is active
     */
    public function isActive() {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Scope: Active credentials
     */
    public function scopeActive($query) {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Expired credentials
     */
    public function scopeExpired($query) {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Find credential by API key (handles encrypted data)
     */
    public static function findByApiKey($apiKey) {
        // Get all active credentials and check decrypted values
        $credentials = static::active()->get();

        foreach ($credentials as $credential) {
            if ($credential->api_key === $apiKey) {
                return $credential;
            }
        }

        return null;
    }

    /**
     * Verify API credentials
     */
    public function verifyCredentials($apiKey, $apiSecret) {
        return $this->api_key === $apiKey && $this->api_secret === $apiSecret;
    }

    /**
     * Get decrypted API key for display (only show first 8 and last 4 characters)
     */
    public function getMaskedApiKeyAttribute() {
        $apiKey = $this->api_key;
        if (strlen($apiKey) <= 12) {
            return str_repeat('*', strlen($apiKey));
        }

        return substr($apiKey, 0, 8) . str_repeat('*', strlen($apiKey) - 12) . substr($apiKey, -4);
    }

    /**
     * Get decrypted API secret for display (only show first 4 and last 4 characters)
     */
    public function getMaskedApiSecretAttribute() {
        $apiSecret = $this->api_secret;
        if (strlen($apiSecret) <= 8) {
            return str_repeat('*', strlen($apiSecret));
        }

        return substr($apiSecret, 0, 4) . str_repeat('*', strlen($apiSecret) - 8) . substr($apiSecret, -4);
    }
}
