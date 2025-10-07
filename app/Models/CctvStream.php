<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class CctvStream extends Model {
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'device_id',
        'stream_name',
        'stream_url',
        'stream_username',
        'stream_password',
        'stream_protocol',
        'position',
        'resolution',
        'fps',
        'status',
        'last_checked_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'fps' => 'integer',
        'last_checked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'stream_password', // Hide password from JSON responses
    ];

    /**
     * Get the branch
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get the device
     */
    public function device() {
        return $this->belongsTo(DeviceMaster::class, 'device_id', 'device_id');
    }

    /**
     * Encrypt stream password before saving (if env enabled)
     */
    public function setStreamPasswordAttribute($value) {
        if ($value && env('ENCRYPT_STREAM_CREDENTIALS', false)) {
            $this->attributes['stream_password'] = Crypt::encryptString($value);
        } else {
            $this->attributes['stream_password'] = $value;
        }
    }

    /**
     * Decrypt stream password when accessing (if env enabled)
     */
    public function getStreamPasswordAttribute($value) {
        if ($value && env('ENCRYPT_STREAM_CREDENTIALS', false)) {
            return Crypt::decryptString($value);
        }
        return $value;
    }

    /**
     * Scope: Online streams
     */
    public function scopeOnline($query) {
        return $query->where('status', 'online');
    }

    /**
     * Scope: Offline streams
     */
    public function scopeOffline($query) {
        return $query->where('status', 'offline');
    }

    /**
     * Scope: By position
     */
    public function scopeByPosition($query, $position) {
        return $query->where('position', $position);
    }
}
