<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class FormSubmission extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['form_id','user_id','data','status','submitted_at'];
    protected function casts(): array { return ['data'=>'array','submitted_at'=>'datetime']; }
    public function form(): BelongsTo { return $this->belongsTo(Form::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
