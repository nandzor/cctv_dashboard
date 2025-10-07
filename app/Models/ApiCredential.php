<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
}
