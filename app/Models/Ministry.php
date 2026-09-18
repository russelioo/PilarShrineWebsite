<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ministry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'commission_id',
        'category',
        'icon',
        'description',
        'status',
        'is_public',
        'about',
        'activities',
        'meeting_schedule',
        'meeting_location',
        'coordinator_name',
        'coordinator_email',
        'coordinator_phone',
        'coordinator_user_id',
        'requirements',
        'is_accepting_members',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'activities' => 'array',
            'requirements' => 'array',
            'is_accepting_members' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(MinistryMembership::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ministry_memberships')
            ->wherePivot('status', 'approved')
            ->withPivot('joined_at', 'reviewed_at', 'status')
            ->withTimestamps();
    }

    public function coordinatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activeMembersCount(): int
    {
        return $this->memberships()->where('status', 'approved')->count();
    }

    public function pendingRequestsCount(): int
    {
        return $this->memberships()->where('status', 'pending')->count();
    }
}
