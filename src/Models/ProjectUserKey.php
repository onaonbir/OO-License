<?php

namespace OnaOnbir\OOLicense\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectUserKey extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_user_id',
        'key',
        'key_version',
        'key_format',
        'key_metadata',
        'start_date',
        'expiry_date',
        'device_info',
        'max_devices',
        'features',
        'is_active',
        'last_validated_at',
        'validation_count',
        'attributes',
        'extras',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'expiry_date' => 'datetime',
            'last_validated_at' => 'datetime',
            'device_info' => 'array',
            'key_metadata' => 'array',
            'features' => 'array',
            'attributes' => 'array',
            'extras' => 'array',
            'is_active' => 'boolean',
            'validation_count' => 'integer',
            'max_devices' => 'integer',
        ];
    }

    /**
     * Get the project user that owns this key
     */
    public function projectUser(): BelongsTo
    {
        return $this->belongsTo(ProjectUser::class);
    }

    /**
     * Get all activations for this key
     */
    public function activations(): HasMany
    {
        return $this->hasMany(ProjectUserKeyActivation::class, 'project_user_key_id');
    }

    /**
     * Check if key is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return now()->isAfter($this->expiry_date);
    }

    /**
     * Check if key has reached max devices
     */
    public function hasReachedMaxDevices(): bool
    {
        $activeDevicesCount = $this->activations()
            ->where('is_active', true)
            ->count();

        return $activeDevicesCount >= $this->max_devices;
    }

    /**
     * Get active devices count
     */
    public function getActiveDevicesCountAttribute(): int
    {
        return $this->activations()
            ->where('is_active', true)
            ->count();
    }
}
