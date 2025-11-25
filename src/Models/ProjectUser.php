<?php

namespace OnaOnbir\OOLicense\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectUser extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'name',
        'email',
        'description',
        'attributes',
        'extras',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'extras' => 'array',
        ];
    }

    /**
     * Get the project that owns this user
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all license keys for this user
     */
    public function keys(): HasMany
    {
        return $this->hasMany(ProjectUserKey::class);
    }
}
