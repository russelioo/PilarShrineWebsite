<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_id',
        'title',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'category',
        'uploaded_by',
    ];

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (! $this->file_size) {
            return '—';
        }

        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2) . ' MB';
        }

        if ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 1) . ' KB';
        }

        return $this->file_size . ' B';
    }

    public function getFileIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'ppt', 'pptx' => '📽',
            'png', 'jpg', 'jpeg', 'webp' => '🖼',
            default => '📁',
        };
    }
}

