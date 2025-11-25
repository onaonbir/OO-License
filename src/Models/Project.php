<?php

namespace OnaOnbir\OOLicense\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'version',
        'description',
        'encryption_key',
        'encryption_method',
        'key_generator_class',
        'key_version',
        'secret_key',
        'is_active',
        'settings',
        'attributes',
        'extras',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'extras' => 'array',
            'settings' => 'array',
            'encryption_key' => 'encrypted',
            'secret_key' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all users for this project
     */
    public function users(): HasMany
    {
        return $this->hasMany(ProjectUser::class);
    }

    /**
     * Get default max devices from settings
     */
    public function getMaxDevicesAttribute(): int
    {
        return $this->settings['max_devices'] ?? config('oo-license.defaults.max_devices', 1);
    }

    /**
     * Get default features from settings
     */
    public function getFeaturesAttribute(): array
    {
        return $this->settings['features'] ?? config('oo-license.defaults.features', []);
    }
}
