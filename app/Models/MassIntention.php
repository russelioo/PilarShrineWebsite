<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class MassIntention extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['mass_schedule_id','requested_by','intention_type','names','offering_amount','status','requested_date','offered_date'];
    protected function casts(): array { return ['offering_amount'=>'decimal:2','requested_date'=>'date','offered_date'=>'date']; }
    public function massSchedule(): BelongsTo { return $this->belongsTo(MassSchedule::class); }
}
