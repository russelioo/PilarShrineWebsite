<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class Announcement extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','content','image_url','priority','published_at','expires_at','is_pinned','category','created_by'];
    protected function casts(): array { return ['published_at'=>'datetime','expires_at'=>'datetime','is_pinned'=>'boolean']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    /**
     * Get array of all image URLs (supports single URL or JSON array of up to 2 photos).
     */
    public function getImageUrlsAttribute(): array
    {
        if (empty($this->image_url)) {
            return [];
        }

        $decoded = json_decode($this->image_url, true);
        if (is_array($decoded)) {
            return array_values($decoded);
        }

        return [$this->image_url];
    }

    /**
     * Get the primary image URL (first photo).
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        $urls = $this->image_urls;
        return $urls[0] ?? null;
    }
}
