<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'settings'])]
class UserPreference extends Model
{
    public const DEFAULT_SETTINGS = [
        'theme' => 'light',
        'language' => 'id',
        'date_format' => 'long',
        'time_format' => '24h',
        'timezone' => 'Asia/Jakarta',
        'sidebar_collapsed' => false,
    ];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
