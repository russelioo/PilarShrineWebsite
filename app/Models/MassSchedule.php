<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class MassSchedule extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','day_of_week','start_time','end_time','location','priest_in_charge','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function intentions(): HasMany { return $this->hasMany(MassIntention::class); }
    public function timeSlots(): HasMany { return $this->hasMany(TimeSlot::class); }
}
