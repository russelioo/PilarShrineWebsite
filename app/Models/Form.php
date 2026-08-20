<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Form extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','description','is_active','created_by'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function fields(): HasMany { return $this->hasMany(FormField::class); }
    public function submissions(): HasMany { return $this->hasMany(FormSubmission::class); }
}
