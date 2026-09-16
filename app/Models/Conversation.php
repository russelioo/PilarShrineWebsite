<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at', 'is_archived', 'role')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InquiryMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(InquiryMessage::class, 'conversation_id')->latestOfMany('id');
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    public function isCommission(): bool
    {
        return $this->type === 'commission';
    }

    public function isMinistry(): bool
    {
        return $this->type === 'ministry';
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function unreadCountFor(int $userId): int
    {
        $pivot = $this->participants()->where('user_id', $userId)->first()?->pivot;
        $lastRead = $pivot?->last_read_at;

        $query = $this->messages()->where('sender_id', '!=', $userId);

        if ($lastRead) {
            $query->where('created_at', '>', $lastRead);
        }

        return $query->count();
    }

    public function getDisplayNameFor(User $user): string
    {
        if ($this->isCommission() && $this->commission) {
            return $this->commission->name;
        }

        if ($this->isMinistry() && $this->ministry) {
            return $this->ministry->name;
        }

        if ($this->title) {
            return $this->title;
        }

        // For direct conversations, show other participant's name
        $other = $this->participants()->where('user_id', '!=', $user->id)->first();

        return $other ? $other->display_name : 'Direct Message';
    }
}

