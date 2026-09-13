<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InquiryMessage extends Model
{
    protected $guarded = ['id'];

    public function attachments(): HasMany
    {
        return $this->hasMany(InquiryAttachment::class, 'message_id');
    }
}
