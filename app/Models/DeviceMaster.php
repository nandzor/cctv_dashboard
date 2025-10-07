<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\EncryptionHelper;

class DeviceMaster extends Model {
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_name',
        'device_type',
        'branch_id',
        'url',
        'username',
        'password',
        'notes',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password', // Hide password from JSON responses
    ];

    /**
     * Get the branch this device belongs to
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get all event settings for this device
     */
    public function eventSettings() {
        return $this->hasMany(BranchEventSetting::class, 'device_id', 'device_id');
    }

    /**
     * Get all event logs for this device
     */
    public function eventLogs() {
        return $this->hasMany(EventLog::class, 'device_id', 'device_id');
    }

    /**
     * Get all CCTV streams for this device
     */
    public function cctvStreams() {
        return $this->hasMany(CctvStream::class, 'device_id', 'device_id');
    }

    /**
     * Get all Re-ID detections for this device
     */
    public function reIdDetections() {
        return $this->hasMany(ReIdBranchDetection::class, 'device_id', 'device_id');
    }

    /**
     * Get all API credentials for this device
     */
    public function apiCredentials() {
        return $this->hasMany(ApiCredential::class, 'device_id', 'device_id');
    }

    /**
     * Encrypt password before saving (if env enabled)
     */
    public function setPasswordAttribute($value) {
        if ($value && EncryptionHelper::shouldEncryptDeviceCredentials()) {
            $this->attributes['password'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Decrypt password when accessing (if env enabled)
     */
    public function getPasswordAttribute($value) {
        if ($value && EncryptionHelper::shouldEncryptDeviceCredentials()) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Encrypt username before saving (if env enabled)
     */
    public function setUsernameAttribute($value) {
        if ($value && EncryptionHelper::shouldEncryptDeviceCredentials()) {
            $this->attributes['username'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['username'] = $value;
        }
    }

    /**
     * Decrypt username when accessing (if env enabled)
     */
    public function getUsernameAttribute($value) {
        if ($value && EncryptionHelper::shouldEncryptDeviceCredentials()) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Scope: Active devices
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Inactive devices
     */
    public function scopeInactive($query) {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope: By device type
     */
    public function scopeByType($query, $type) {
        return $query->where('device_type', $type);
    }
}
