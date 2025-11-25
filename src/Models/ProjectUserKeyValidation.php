<?php

namespace OnaOnbir\OOLicense\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUserKeyValidation extends Model
{
    use HasUuids;

    protected $fillable = [
        'activation_id',
        'validation_type',
        'device_info',
        'ip_address',
        'user_agent',
        'request_data',
        'response_status',
        'error_code',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'device_info' => 'array',
            'request_data' => 'array',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Get the activation this validation belongs to
     */
    public function activation(): BelongsTo
    {
        return $this->belongsTo(ProjectUserKeyActivation::class, 'activation_id');
    }
}
