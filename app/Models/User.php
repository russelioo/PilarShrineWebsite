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

#[Fillable(['name', 'first_name', 'last_name', 'date_of_birth', 'country', 'region', 'province', 'municipality_city', 'barangay', 'email', 'password_hash', 'role', 'commission_id', 'phone', 'is_verified', 'email_verified_at', 'google_id', 'avatar'])]
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

    public function commission(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function commissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Commission::class, 'commission_memberships')
            ->withPivot('role', 'status', 'joined_at')
            ->withTimestamps();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function targetedAuditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'target_id')
            ->where('target_type', 'User');
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin'], true);
    }

    public function isParishPriest(): bool
    {
        return $this->role === 'parish_priest';
    }

    public function isParochialVicar(): bool
    {
        return $this->role === 'parochial_vicar';
    }

    public function isParishSecretary(): bool
    {
        return $this->role === 'parish_secretary';
    }

    public function hasParishWideAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'parish_priest', 'parochial_vicar', 'parish_secretary'], true);
    }

    public function isCommissionAdmin(): bool
    {
        return $this->role === 'commission_admin';
    }

    public function isCommissionMember(): bool
    {
        return in_array($this->role, ['commission_admin', 'commission_member', 'staff'], true);
    }

    public function canAccessCommission(?int $commissionId): bool
    {
        if ($this->hasParishWideAccess()) {
            return true;
        }

        if (! $commissionId) {
            return false;
        }

        return (int) $this->commission_id === (int) $commissionId;
    }

    public function getRoleBadgeLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin', 'admin' => 'Super Admin',
            'parish_priest' => 'Parish Priest',
            'parochial_vicar' => 'Parochial Vicar',
            'parish_secretary' => 'Parish Secretary',
            'commission_admin' => 'Commission Admin',
            'commission_member' => 'Commission Member',
            'staff' => 'Staff',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }
}
