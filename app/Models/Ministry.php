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
        'category',
        'icon',
        'description',
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
    ];

    protected function casts(): array
    {
        return [
            'activities' => 'array',
            'requirements' => 'array',
            'is_accepting_members' => 'boolean',
        ];
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

    public function activeMembersCount(): int
    {
        return $this->memberships()->where('status', 'approved')->count();
    }

    public function pendingRequestsCount(): int
    {
        return $this->memberships()->where('status', 'pending')->count();
    }
}

