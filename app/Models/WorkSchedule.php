<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSchedule extends Model
{
    protected $fillable = ['user_id', 'schedule_date', 'name', 'type', 'start_time', 'end_time', 'location', 'status'];

    protected function casts(): array
    {
        return ['schedule_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
