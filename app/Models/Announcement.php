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
}
