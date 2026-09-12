<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class MassSchedule extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'category',
        'schedule_type',
        'day_of_week',
        'start_time',
        'end_time',
        'time_display',
        'location',
        'priest_in_charge',
        'notes',
        'is_active',
        'is_highlighted',
        'is_livestreamed',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_highlighted' => 'boolean',
            'is_livestreamed' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
    public function intentions(): HasMany { return $this->hasMany(MassIntention::class); }
    public function timeSlots(): HasMany { return $this->hasMany(TimeSlot::class); }
}
