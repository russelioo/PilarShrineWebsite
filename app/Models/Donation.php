<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class Donation extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id','donor_name','amount','method','payment_reference','payment_status','transaction_id','received_by','receipt_issued'];
    protected function casts(): array { return ['amount'=>'decimal:2','receipt_issued'=>'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
