<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class Appointment extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id','slot_id','service_type','preferred_date','preferred_time','status','notes'];
    protected function casts(): array { return ['preferred_date'=>'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function timeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class, 'slot_id'); }
}
