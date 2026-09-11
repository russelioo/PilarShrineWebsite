<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'last_name', 'date_of_birth', 'country', 'region', 'province', 'municipality_city', 'barangay', 'email', 'password_hash', 'role', 'phone', 'is_verified', 'email_verified_at', 'google_id', 'avatar'])]
#[Hidden(['password_hash', 'remember_token', 'reset_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password_hash' => 'hashed',
            'last_login' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function isProfileComplete(): bool
    {
        return !empty($this->first_name)
            && !empty($this->last_name)
            && !empty($this->date_of_birth)
            && !empty($this->phone)
            && !empty($this->barangay);
    }

    public function massIntentions(): HasMany
    {
        return $this->hasMany(MassIntention::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function ministryMemberships(): HasMany
    {
        return $this->hasMany(MinistryMembership::class);
    }

    public function ministries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_memberships')
            ->withPivot('status', 'joined_at', 'reviewed_at')
            ->withTimestamps();
    }

    public function activeMinistries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_memberships')
            ->wherePivot('status', 'approved')
            ->withPivot('joined_at', 'reviewed_at')
            ->withTimestamps();
    }

    public function coordinatedMinistries(): HasMany
    {
        return $this->hasMany(Ministry::class, 'coordinator_user_id');
    }

    public function getInitialsAttribute(): string
    {
        if (!empty($this->first_name) && !empty($this->last_name)) {
            return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
        }

        $parts = preg_split('/\s+/', trim($this->name ?: ''));
        if (!empty($parts[0])) {
            $second = isset($parts[1]) ? substr($parts[1], 0, 1) : '';
            return strtoupper(substr($parts[0], 0, 1) . $second);
        }

        return 'PA';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name
            ?: trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''))
            ?: 'Parishioner';
    }

    public function getAuthProviderAttribute(): string
    {
        return !empty($this->google_id) ? 'google' : 'password';
    }
}
