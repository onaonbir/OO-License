<?php

namespace OnaOnbir\OOLicense\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectUserKeyActivation extends Model
{
    use HasUuids;

    protected $table = 'project_user_key_activations';

    protected $fillable = [
        'project_user_key_id',
        'device_id',
        'device_info',
        'activated_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'device_info' => 'array',
            'activated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the license key this activation belongs to
     */
    public function licenseKey(): BelongsTo
    {
        return $this->belongsTo(ProjectUserKey::class, 'project_user_key_id');
    }

    /**
     * Get all validations for this activation
     */
    public function validations(): HasMany
    {
        return $this->hasMany(ProjectUserKeyValidation::class, 'activation_id');
    }

    /**
     * Get validation count
     */
    public function getValidationCountAttribute(): int
    {
        return $this->validations()->count();
    }

    /**
     * Get last validated at
     */
    public function getLastValidatedAtAttribute()
    {
        return $this->validations()->latest('validated_at')->first()?->validated_at;
    }
}
