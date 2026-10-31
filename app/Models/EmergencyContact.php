<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'relationship', 'primary_phone', 'alternative_phone'])]
class EmergencyContact extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
