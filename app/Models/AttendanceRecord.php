<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = ['user_id', 'attendance_date', 'check_in_at', 'check_out_at', 'latitude', 'longitude', 'location', 'distance_from_office', 'location_accuracy', 'check_in_photo_path', 'check_out_photo_path', 'status', 'ip_address'];

    protected function casts(): array
    {
        return ['attendance_date' => 'date', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
