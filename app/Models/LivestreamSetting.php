<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestreamSetting extends Model
{
    protected $fillable = ['is_live', 'title', 'url', 'updated_by'];

    protected function casts(): array
    {
        return ['is_live' => 'boolean'];
    }
}
