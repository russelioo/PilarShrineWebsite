<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SacramentalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'record_type', 'date_performed', 'officiating_priest', 'certificate_number', 'notes', 'certificate_issued'];

    protected function casts(): array
    {
        return ['date_performed' => 'date', 'certificate_issued' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
