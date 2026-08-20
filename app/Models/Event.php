<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class Event extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','description','event_date','start_time','end_time','location','event_type','is_recurring','recurrence_rule','created_by'];
    protected function casts(): array { return ['event_date'=>'date','is_recurring'=>'boolean']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
