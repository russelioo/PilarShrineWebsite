<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Donation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'donor_name',
        'email',
        'contact_number',
        'purpose',
        'amount',
        'donation_date',
        'method',
        'payment_method',
        'payment_reference',
        'reference_number',
        'payment_status',
        'status',
        'proof_of_payment',
        'notes',
        'admin_notes',
        'receipt_issued',
        'receipt_number',
        'verified_at',
        'verified_by',
        'transaction_id',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'receipt_issued' => 'boolean',
            'donation_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function setPaymentMethodAttribute($value): void
    {
        $this->attributes['method'] = $value;
    }

    public function getPaymentMethodAttribute(): ?string
    {
        return $this->attributes['method'] ?? null;
    }

    public function setReferenceNumberAttribute($value): void
    {
        $this->attributes['payment_reference'] = $value;
    }

    public function getReferenceNumberAttribute(): ?string
    {
        return $this->attributes['payment_reference'] ?? null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_verification' => 'Pending Verification',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            'receipt_ready' => 'Acknowledgment / Receipt Ready',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'pending_verification')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending_verification' => 'badge-status-pending',
            'verified' => 'badge-status-verified',
            'rejected' => 'badge-status-rejected',
            'receipt_ready' => 'badge-status-ready',
            default => 'badge-status-neutral',
        };
    }

    public function getProofUrlAttribute(): ?string
    {
        if (empty($this->proof_of_payment)) {
            return null;
        }

        if (str_starts_with($this->proof_of_payment, 'http://') || str_starts_with($this->proof_of_payment, 'https://')) {
            return $this->proof_of_payment;
        }

        return Storage::url($this->proof_of_payment);
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format((float) $this->amount, 2);
    }
}
