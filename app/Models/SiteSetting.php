<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = [
        'id', 'address', 'phone', 'email', 'office_hours',
        'facebook_url', 'youtube_url', 'tiktok_url', 'updated_by',
    ];

    public static function defaults(): array
    {
        return [
            'address' => 'Binanuahan, Pilar, Sorsogon, 4714 Philippines',
            'phone' => '0946-869-1254',
            'email' => 'olppspilarsorsogon@gmail.com',
            'office_hours' => "Monday, Wednesday – Saturday: 8:00 AM – 11:30 AM | 1:00 PM – 5:00 PM\nSunday: 8:30 AM – 12:00 NN\nTuesday and holidays: Closed",
            'facebook_url' => config('services.facebook.page_url'),
            'youtube_url' => 'https://www.youtube.com/@PilarShrineSorsogon',
            'tiktok_url' => 'https://www.tiktok.com/@PilarShrineSorsogon',
        ];
    }

    public static function current(): self
    {
        return static::query()->find(1) ?? new static(static::defaults());
    }

    public static function publicValues(): array
    {
        // Keep the public website available while a deployment's migrations run.
        if (! Schema::hasTable('site_settings')) {
            return static::defaults();
        }

        return static::current()->only(array_keys(static::defaults()));
    }
}
