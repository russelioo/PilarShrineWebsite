<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Notification extends Model {
    use HasFactory;
    protected $fillable = ['user_id','type','subject','message','sent_at','status'];
    protected function casts(): array { return ['sent_at'=>'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
