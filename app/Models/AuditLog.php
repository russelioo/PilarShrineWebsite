<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'action_category',
        'target_type',
        'target_id',
        'target_name',
        'commission_id',
        'commission_name',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    /**
     * Enforce immutability: audit logs cannot be updated once written.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new RuntimeException('Audit log entries are immutable and cannot be modified.');
    }

    /**
     * Enforce immutability: audit logs cannot be deleted.
     */
    public function delete(): ?bool
    {
        throw new RuntimeException('Audit log entries are immutable and cannot be deleted.');
    }

    /**
     * Human-friendly action badge color and label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_deactivated' => 'User Deactivated',
            'user_activated' => 'User Activated',
            'user_deleted' => 'User Deleted',
            'role_changed' => 'Role Changed',
            'commission_assigned' => 'Commission Assigned',
            'commission_changed' => 'Commission Changed',
            'password_reset' => 'Password Reset',
            'login' => 'User Login',
            'logout' => 'User Logout',
            'failed_login' => 'Failed Login Attempt',
            default => ucwords(str_replace('_', ' ', $this->action)),
        };
    }
}

