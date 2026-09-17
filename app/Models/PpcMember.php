<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpcMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_title',
        'commission_id',
        'term_start',
        'term_end',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'term_start' => 'date',
            'term_end' => 'date',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

