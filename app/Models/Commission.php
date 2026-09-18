<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'icon',
        'head_user_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value.
     * Supports both slug and numeric ID resolution.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        if (is_numeric($value)) {
            return $this->where('id', $value)
                ->orWhere('slug', (string) $value)
                ->first();
        }

        return $this->where('slug', $value)->first();
    }

    public function headUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CommissionMembership::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'commission_memberships')
            ->withPivot('id', 'position', 'is_officer', 'role', 'status', 'joined_at', 'notes')
            ->withTimestamps();
    }

    public function officers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'commission_memberships')
            ->wherePivot('is_officer', true)
            ->withPivot('id', 'position', 'is_officer', 'role', 'status', 'joined_at', 'notes')
            ->withTimestamps();
    }

    public function ministries(): HasMany
    {
        return $this->hasMany(Ministry::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(CommissionProject::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CommissionDocument::class);
    }

    public function ppcMembers(): HasMany
    {
        return $this->hasMany(PpcMember::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function activeMembersCount(): int
    {
        return $this->members()->wherePivot('status', 'active')->count();
    }

    public function getMembersCountAttribute(): int
    {
        return $this->members()->count();
    }

    public function getOfficersCountAttribute(): int
    {
        return $this->members()->wherePivot('is_officer', true)->count();
    }

    public function getProjectsCountAttribute(): int
    {
        return $this->projects()->count();
    }

    public function getDocumentsCountAttribute(): int
    {
        return $this->documents()->count();
    }

    public function getMinistriesCountAttribute(): int
    {
        if (array_key_exists('ministries_count', $this->attributes)) {
            return (int) $this->attributes['ministries_count'];
        }

        return $this->ministries()->where('status', 'active')->where('is_public', true)->count();
    }
}
