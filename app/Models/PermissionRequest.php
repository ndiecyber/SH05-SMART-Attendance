<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionRequest extends Model
{
    protected $fillable = ['user_id', 'permission_type', 'permission_date', 'start_time', 'end_time', 'reason', 'status'];

    protected function casts(): array
    {
        return ['permission_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
