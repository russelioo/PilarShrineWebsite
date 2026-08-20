<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TimeSlot extends Model {
    use HasFactory;
    protected $fillable = ['mass_schedule_id','slot_time','max_capacity','current_bookings','is_available'];
    protected function casts(): array { return ['max_capacity'=>'integer','current_bookings'=>'integer','is_available'=>'boolean']; }
    public function massSchedule(): BelongsTo { return $this->belongsTo(MassSchedule::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class, 'slot_id'); }
}
