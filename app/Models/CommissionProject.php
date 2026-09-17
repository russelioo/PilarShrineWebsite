<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_id',
        'title',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'lead_user_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'budget'     => 'decimal:2',
        ];
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function leadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planning'  => 'Planning',
            'ongoing'   => 'In Progress',
            'completed' => 'Completed',
            'on_hold'   => 'On Hold',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'planning'  => 'badge-warning',
            'ongoing'   => 'badge-primary',
            'completed' => 'badge-success',
            'on_hold'   => 'badge-secondary',
            'cancelled' => 'badge-danger',
            default     => 'badge-light',
        };
    }
}

